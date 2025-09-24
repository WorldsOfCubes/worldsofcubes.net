<?php
/* WEB-APP : WebMCR (С) 2013 NC22 | License : GPLv3 */


if (empty($_POST['method']) and empty($_GET['method']))
	exit;
$method = (isset($_POST['method'])) ? $_POST['method'] : $_GET['method'];

switch ($method) {
	case 'comment':
	case 'del_com':
	case 'profile':
	case 'prefix':
	case 'restore':
	case 'project_add':
	case 'trust_delete':
	case 'trust_full_cleanup':
	case 'load_info':
	case 'upload':
	case 'like':
	case 'delete_file':

		require('./system.php');
		loadTool('ajax.php');
		loadTool('user.class.php');
		if ($method == 'upload' or $method == 'delete_file')
			loadTool('upload.class.php'); elseif ($method == 'profile')
			loadTool('skin.class.php');
		elseif ($method == 'restore' and $config['p_logic'] != 'usual' and $config['p_logic'] != 'xauth' and $config['p_logic'] != 'authme'
		)

			aExit(1, 'Change password is not available');

		$db = new DB();
		$db->connect('action_' . $method);
		MCRAuth::userLoad();

		break;
	case 'download':

		require('./system.php');
		loadTool('upload.class.php');

		$db = new DB();
		$db->connect('action_download');


		break;
	default:
		exit;
		break;
}

switch ($method) {
	case 'upload': // TODO Список последних добавленых файлов

		if (empty($user) or $user->lvl() < 15)
			break;

		$file = new File(false, 'other/');
		$id_rewrite = (isset($_POST['nf_delete'])) ? true : false;
		$id_word = (!empty($_POST['nf_id_word'])) ? $_POST['nf_id_word'] : false;

		$result = $file->Create('new_file', $user->id(), $id_word, $id_rewrite);
		$error = '';

		switch ($result) {
			case 1:
				$error = lng('UPLOAD_FAIL') . '. ( ' . lng('UPLOAD_FORMATS') . ' - jpg, png, zip, rar, exe, jar, pdf, doc, txt )';
				break;
			case 3:
				$error = lng('INCORRECT') . '. (' . lng('TXT_ID') . ')';
				break;
			case 4:
				$error = lng('TXT_ID_EXIST');
				break;
			case 2:
			case 5:
				$error = lng('UPLOAD_FAIL');
				break;
			case 6:
				$error = lng('DEL_FAIL');
				break;
			case 7:
				$error = lng('FILE_EXIST');
				break;
		}

		if ($result > 0 and $result != 7)
			aExit($result, $error);

		$file_info = $file->getInfo();

		$ajax_message['file_id'] = $file_info['id'];
		$ajax_message['file_name'] = $file_info['name'];
		$ajax_message['file_size'] = $file_info['size'];

		$ajax_message['file_html'] = $file->Show();

		aExit($result, $error);
		break;
	case 'like':

		if (empty($_POST['type']) or empty($_POST['id']) or !isset($_POST['dislike']))
			break;
		if (empty($user)) {

			aExit(3, 'Like not authed');
			break;
		}

		$id = (int)$_POST['id'];
		$type = (int)$_POST['type'];
		$dislike = ((int)$_POST['dislike']) ? true : false;
		$item = null;

		if ($type == ItemType::News) {

			loadTool('catalog.class.php');

			$item = new News_Item($id);
		} elseif ($type == ItemType::Skin and isset($bd_names['sp_skins'])) {

			loadTool('skinposer.class.php');

			$item = new SPItem($id);
		}

		if ($item)
			aExit((int)$item->Like($dislike), 'Like');
		break;
	case 'download':

		if (empty($_GET['file']))
			break;

		$file = new File($_GET['file']);
		if (!$file->Download())
			header("Location: " . BASE_URL . "index.php?mode=404");
		break;
	case 'delete_file':

		if (empty($_POST['file']))
			break;
		if (empty($user) or $user->lvl() < 15)
			break;

		$file = new File((int)$_POST['file']);
		if ($file->Delete())
			aExit(0); else aExit(1);
		break;
	case 'trust_delete':
		if (!$user or $user->lvl() < 2) aExit(3, "Вы не авторизованы или не подтвердили e-mail");
		if (!isset($_POST['pid']) and !isset($_POST['pid'])) aExit(4, "Запрос сформирован некорректно!");
		($db->execute("UPDATE `woc_projects_players` SET `hide_dialog`=0 WHERE `uid`=" . $user->id() . " AND `pid`=" . $db->safe((int) (isset($_POST['pid'])) ? $_POST['pid'] : $_GET['pid'])))?
			aExit(0, ''):
			aExit(1, 'Произошла ошибка SQL');
		break;
	case 'trust_full_cleanup':
		if (!$user or $user->lvl() < 2) aExit(3, "Вы не авторизованы или не подтвердили e-mail");
		($db->execute("UPDATE `woc_projects_players` SET `hide_dialog`=0 WHERE `uid`=" . $user->id()))?
			aExit(0, ''):
			aExit(1, 'Произошла ошибка SQL');
		aExit(4,'Что-то не так');
		break;
	case 'project_add':
		if (!$user or $user->lvl() < 2) aExit(3, "Вы не аторизованы или не подтвердили e-mail");
		$query = $db->execute("SELECT * FROM `banlist` WHERE `name`='" . $user->name() . "' AND (`temptime`>NOW() OR `type`='2')");
		if (!$db->num_rows($query)) {
			if (isset($_POST['proj_url']) and isset($_POST['proj_name']) and isset($_POST['proj_description'])) {
				$check = '';
				$proj_name = InputGet('proj_name');
				$proj_url = InputGet('proj_url');
				$proj_description = InputGet('proj_description');
				if ($proj_name != $check && $proj_description != $check) {
					$security_key = randString(16);
					if ((mb_strlen($proj_url, 'utf8') >= 5) && (mb_strlen($proj_url, 'utf8') <= 50) && (mb_strlen($proj_name, 'utf8') >= 5) && (mb_strlen($proj_name, 'utf8') <= 50) && (mb_strlen($proj_description, 'utf8') >= 30) && (mb_strlen($proj_description, 'utf8') <= 512)) {
						$db->execute("INSERT INTO `woc_projects` (`user`, `name`, `about`, `time`, `url`, `security_key`, `path`)"
									."VALUES ('" . $user->name() . "','" . TextBase::SQLSafe($proj_name) . "','" . TextBase::SQLSafe($proj_description) . "',NOW(), '" . $db->safe($proj_url) . "', '$security_key', 'http://" . $db->safe($proj_url) . "/woc/auth.php');");
						$ajax_message['security-key'] = $security_key;
						aExit(0, $db->insert_id());
					} else {
						aExit(1,'Некорректрая длина значений полей!');
					}
				} else {
					aExit(2,'Не все поля заполнены!');
				}
			}
		} else {
			ob_start();
			$ban = $db->fetch_array($query);
			aExit(3, "Доступ к добавлению проектов заблокирован " . (($ban['type']==2)?'навсегда':'до '.$ban['temptime']) . " пользователем <a href=\"go/user/profile/{$ban['admin']}\">{$ban['admin']}</a>, причина - " . $ban['reason']);
		}
		break;
	case 'restore':

		if (empty($_POST['email']))
			aExit(1, lng('INCOMPLETE_FORM'));

		CaptchaCheck(2);

		$email = $_POST['email'];

		$result = $db->execute("SELECT `{$bd_users['id']}` FROM `{$bd_names['users']}` WHERE `{$bd_users['email']}`='" . $db->safe($email) . "'");
		if (!$db->num_rows($result))
			aExit(3, lng('RESTORE_NOT_EXIST'));

		$line = $db->fetch_array($result, MYSQL_NUM);

		$restore_user = new User($line[0]);

		$new_pass = randString(8);

		$subject = lng('RESTORE_TITLE') . ' - ' . sqlConfigGet('email-name');
		$message = '<html><body><p>' . lng('RESTORE_TITLE') . '. ' . lng('RESTORE_NEW') . ' ' . lng('LOGIN') . ': ' . $restore_user->name() . '. ' . lng('PASS') . ': ' . $new_pass . '</p></body></html>';

		if (!EMail::Send($email, $subject, $message))
			aExit(4, lng('MAIL_FAIL'));

		if ($restore_user->changePassword($new_pass) != 1)
			aExit(5, '');

		aExit(0, lng('RESTORE_COMPLETE'));

		break;
	case 'comment':

		if (empty($user) or empty($_POST['comment']) or empty($_POST['item_id']) or empty($_POST['item_type']) or empty($_POST['antibot']))
			aExit(1, lng('MESS_FAIL'));

		loadTool('comment.class.php');

		$comments_item = new Comments_Item(false, 'news/comments/');

		$item_type = (int)$_POST['item_type'];
		$item_id = (int)$_POST['item_id'];

		$comments_item->aCreate($_POST['comment'], $user, $item_id, $item_type);

		break;
	case 'del_com':

		if (empty($user) or empty($_POST['item_id']))
			aExit(1);

		loadTool('comment.class.php');

		$comments_item = new Comments_Item((int)$_POST['item_id']);

		if (!$user->getPermission('adm_comm') and $comments_item->GetAuthorID() != $user->id())
			aExit(1);

		if ($comments_item->Delete())
			aExit(0); else aExit(1);

		break;
	case 'load_info':

		if (empty($_POST['id']))
			aExit(1, 'Empty POST param ID');

		loadTool('profile.class.php');

		$user_profile = new Profile((int)$_POST['id'], 'other/');
		$ajax_message['player_info'] = $user_profile->Show();

		aExit(0);

		break;
	case 'profile':

		$ajax_message = array(
			'code' => 0,
			'message' => 'profile',
			'name' => '',
			'group' => '',
			'id' => '',
			'skin' => 0,
			'cloak' => 0,
			'skin_link' => '?none'
		);

		$rcodes = null;

		if (empty($user) or $user->lvl() <= 0)
			aExit(1);

		$mod_user = $user;

		if ($user->lvl() >= 15 and !empty($_POST['user_id']))
			$mod_user = new User((int)$_POST['user_id']);

		if (!$mod_user->id())
			aExit(2, lng('USER_NOT_EXIST'));

		if ($user->lvl() >= 15) {

			if (isset($_POST['new_group'])) {

				if ($mod_user->changeGroup((int)$_POST['new_group']))
					$rcodes[] = 1;
			}
			if (!empty($_POST['new_money'])) {

				if ($mod_user->addMoney($_POST['new_money']))
					$rcodes[] = 1;
			}
			if (!empty($_POST['new_econ'])) {

				if ($mod_user->addEcon($_POST['new_econ']))
					$rcodes[] = 1;
			}
			if (isset($_POST['new_gender'])) {

				$newgender = (!(int)$_POST['new_gender']) ? 0 : 1;
				if ($mod_user->changeGender($newgender))
					$rcodes[] = 1;
			}
			if (isset($_POST['new_verified'])) {

				$newverified = ($_POST['new_verified']) ? 0 : 1;
				if (!$db->execute("UPDATE `{$bd_names['users']}` SET `verified`='{$db->safe($newverified)}' WHERE `{$bd_users['id']}`='" . $mod_user->id() . "'"))
					$rcodes[] = 0;
			}
			if (!empty($_POST['new_email']))

				$rcodes[] = $mod_user->changeEmail($_POST['new_email']);
		}

		if (!empty($_POST['new_login']))
			$rcodes[] = $mod_user->changeName($_POST['new_login']);
		if (!empty($_POST['new_password'])) {

			$oldpass = (!empty($_POST['old_password'])) ? $_POST['old_password'] : '';
			$newpass = $_POST['new_password'];
			$newrepass = (!empty($_POST['new_repassword'])) ? $_POST['new_repassword'] : '';

			if (($user->lvl() >= 15 and !empty($_POST['user_id'])) or !$mod_user->pass_set())
				$rcodes[] = $mod_user->changePassword($newpass); else                    $rcodes[] = $mod_user->changePassword($newpass, $newrepass, $oldpass);
		}

		if (empty($_FILES['new_skin']['tmp_name']) and !empty($_POST['new_delete_skin']) and !$mod_user->defaultSkinTrigger() and $user->getPermission('change_skin'))
			$rcodes[] = $mod_user->setDefaultSkin();

		if (empty($_FILES['new_cloak']['tmp_name']) and !empty($_POST['new_delete_cloak']) and $user->getPermission('change_cloak')) {
			$mod_user->deleteCloak();
			$rcodes[] = 1;
		}
		if (!empty($_FILES['new_skin']['tmp_name']))
			$rcodes[] = (int)$mod_user->changeVisual('new_skin', 'skin');

		if (!empty($_FILES['new_cloak']['tmp_name']))
			$rcodes[] = (int)$mod_user->changeVisual('new_cloak', 'cloak') . '1';

		$message = '';
		$rnum = sizeof($rcodes);

		for ($i = 0; $i < $rnum; $i++) {

			$modifed = true;

			switch ((int)$rcodes[$i]) {
				case 0 :
					$message .= 'error';
					break;
				case 1401 :
					$message .= lng('INCORRECT') . '. (' . lng('LOGIN') . ')';
					break;
				case 1402 :
					$message .= lng('AUTH_EXIST_LOGIN');
					break;
				case 1403 :
					$message .= lng('INCORRECT_LEN') . '. (' . lng('LOGIN') . ')';
					break;
				case 1501 :
					$message .= lng('INCORRECT') . '. (' . lng('PASS') . ')';
					break;
				case 1502 :
					$message .= lng('CURPASS_FAIL');
					break;
				case 1503 :
					$message .= lng('INCORRECT_LEN') . '. (' . lng('PASS') . ')';
					break;
				case 1504 :
					$message .= lng('REPASSVSPASS');
					break;
				case 1601 :
					$message .= lng('MAX_FILE_SIZE') . ' ' . $user->getPermission('max_fsize') . lng('KB') . ' ( ' . lng('SKIN_UPLOAD') . ' )';
					break;
				case 16011 :
					$message .= lng('MAX_FILE_SIZE') . ' ' . $user->getPermission('max_fsize') . lng('KB') . ' ( ' . lng('CLOAK_UPLOAD') . ' )';
					break;
				case 1602 :
					$tmpm = $user->getPermission('max_ratio');
					$message .= lng('MAX_FILE_RATIO') . ' ' . (62 * $tmpm) . "x" . (32 * $tmpm) . ' ( ' . lng('SKIN_UPLOAD') . ' )';
					unset($tmpm);
					break;
				case 16021 :
					$tmpm = $user->getPermission('max_ratio');
					$message .= lng('MAX_FILE_RATIO') . ' ' . (22 * $tmpm) . "x" . (17 * $tmpm) . lng('OR') . ' ' . (62 * $tmpm) . "x" . (32 * $tmpm) . ' ( ' . lng('CLOAK_UPLOAD') . ' )';
					unset($tmpm);
					break;
				case 1604 :
					$message .= lng('UPLOAD_FAIL') . ' ( ' . lng('SKIN_UPLOAD') . ' ) ' . lng('UPLOAD_FORMATS') . ' - .png';
					break;
				case 16041 :
					$message .= lng('UPLOAD_FAIL') . ' ( ' . lng('CLOAK_UPLOAD') . ' ) ' . lng('UPLOAD_FORMATS') . ' - .png';
					break;
				case 1605 :
					$message .= lng('PERMISSION_FAIL');
					break;
				case 16051 :
					$message .= lng('PERMISSION_FAIL');
					break;
				case 1610 :
					$message .= lng('UPLOAD_FAIL');
				case 16101 :
				case 1611 :
				case 16111 :
					break;
				case 1901 :
					$message .= lng('INCORRECT') . '. (' . lng('EMAIL') . ')';
					break;
				case 1902 :
					$message .= lng('AUTH_EXIST_EMAIL');
					break;
				default :
					$modifed = false;
					break;
			}

			if ($modifed)
				$message .= "\n";
		}

		$ajax_message['name'] = $mod_user->name();
		$ajax_message['group'] = $mod_user->getGroupName();
		$ajax_message['id'] = $mod_user->id();

		$ajax_message['skin_link'] = $mod_user->getSkinLink(false, '&', true);
		$ajax_message['mskin_link'] = $mod_user->getSkinLink(true, '&', true);

		if (file_exists($mod_user->getCloakFName()))
			$ajax_message['cloak'] = 1;
		if ($mod_user->defaultSkinTrigger())
			$ajax_message['skin'] = 1;

		if ($message)
			aExit(2, $message); // some bad news
		elseif (!$rnum)
			aExit(100, $message); //nothing changed
		else aExit(0, lng('PROFILE_COMPLITE'));

		break;
	case 'prefix':
		if (!$user->getPermission('change_prefix')) {
			aExit(4, "Ух ты какой хакер! Я не ожидал тебя тут увидеть :P");
		}
		if (!$_POST['textcolor'] or !$_POST['nickcolor'] or !$_POST['prefcolor'])
			aExit(1, "Ошибка отправки формы!");
		$pref = (isset($_POST['pref']) && strlen($_POST['pref'])) ? $_POST['pref'] : false;
		$textcolor = $_POST['textcolor'];
		$nickcolor = $_POST['nickcolor'];
		$prefcolor = $_POST['prefcolor'];
		$d = '[';
		$f = '] ';
		$g = '';

		if ($pref)
			$fineprefix = $db->safe($prefcolor . $d . $pref . $f . $nickcolor); else $fineprefix = $db->safe($nickcolor);
		$suffix = $db->safe($textcolor . $g);
		$db->execute("DELETE FROM permissions_entity WHERE name='" . $user->name() . "'");
		$db->execute("INSERT INTO permissions_entity VALUES (NULL, '" . $user->name() . "', '1', '$fineprefix', '$suffix', '0')") or aExit(4, ('Не удалось соединиться: ' . $db->error() . " Сообщите полный текст этого сообщения администраторам для устранения ошибки."));
		aExit(0, 'Теперь ваш ник будет отображаться в новом цвете');
		break;
}