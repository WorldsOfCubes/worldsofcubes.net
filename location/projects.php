<?php 
//if (empty($user) or $user->lvl() < 1) { accss_deny(); }


$num_by_page = 25;
$path = 'projects/';

if (isset($_GET['do'])) $do = $_GET['do'];
	elseif (isset($_POST['do'])) $do = $_POST['do'];
	else $do = 'list';
$menu->SetItemActive('projects');

function uploadLogo($post_name = "logo", $type = 'skin') {
	if (!POSTGood($post_name)) return "Ошибка обработки файла";

	$tmp_dir = MCRAFT.'tmp/';

	$new_file_info = POSTSafeMove($post_name, $tmp_dir);
	if (!$new_file_info) return "Ошибка: нет доступа";

	$way  = $tmp_dir.$new_file_info['tmp_name'];
	unlink($way);
	($type == 'skin') ? $this->deleteSkin() : $this->deleteCloak();
	$new_way = ($type == 'skin') ? $this->getSkinFName() : $this->getCloakFName();

	if (rename( $way, $new_way )) chmod($new_way , 0777);
	else {

		unlink($way);
		vtxtlog('[Ошибка модуля загрузки] Ошибка копирования ['.$way.'] в ['.$new_way.'] . Проверьте доступ на ЧТЕНИЕ \ ЗАПИСЬ соответствующих папок.');
		return 1611;
	}

	if ($type == 'skin') {

		if ( !strcmp($this->defaultSkinMD5(), md5_file($this->getSkinFName())) )
			$this->defaultSkinTrigger(true);
		else
			$this->defaultSkinTrigger(false);
	}

	$this->deleteBuffer();

}

switch($do){
case 'edit':
	if (empty($user) or $user->lvl() < 1) { accss_deny(); }
	if (!isset($_GET['id'])) accss_deny();
	if (isset($_GET['id'])) $id = TextBase::HTMLDestruct(TextBase::SQLSafe($_GET['id']));
	$query = $db->execute("SELECT * FROM `woc_projects` WHERE `id`=$id");
	if (!$query) accss_deny();
	$project = $db->fetch_assoc($query);
	if($user->lvl() < 8 and $project['user'] != $user->name()) accss_deny();
	if (isset($_POST['name'])) $name = TextBase::HTMLDestruct(TextBase::SQLSafe($_POST['name']));
		else $name = $project['name'];
	if (isset($_POST['url'])) $url = TextBase::HTMLDestruct(TextBase::SQLSafe($_POST['url']));
		else $url = $project['url'];
	if (isset($_POST['path'])) $path_sc = TextBase::HTMLDestruct($_POST['path']);
		else $path_sc = $project['path'];
	if (isset($_POST['description'])) $description = TextBase::HTMLDestruct($_POST['description']);
		else $description = $project['about'];
	if (isset($_POST['security_key'])) $security_key = TextBase::HTMLDestruct($_POST['security_key']);
		elseif (!strlen($project['security_key'])) $security_key = randString(16);
		else $security_key = $project['security_key'];
	if(!strlen($security_key)) $security_key = randString(16);
	$in_develop = $project['in_develop'];
	$info = '';
//	$description = nl2br($description);
	if (isset($_POST['submit'])){
//		if (!empty($_FILES['logo']['tmp_name']) ) $info = uploadLogo();
		$in_develop = InputGet('indev', 'POST', 'bool');
			if (!strlen($name) or !strlen($url) or !strlen($path_sc) or !strlen($description) or !strlen($security_key)) $info .= lng('INCOMPLETE_FORM');
		if ((mb_strlen($name, 'utf8') < 5) or (mb_strlen($name, 'utf8') > 50)) $info .= "Некорректная длина названия проекта ";
		if ((mb_strlen($url, 'utf8') < 5) or (mb_strlen($url, 'utf8') > 30)) $info .= "Некорректная длина домена ";
		if ((mb_strlen($path_sc, 'utf8') < 10) or (mb_strlen($path_sc, 'utf8') > 50)) $info .= "Некорректная длина пути до скрипта-обработчика ";
		if ((mb_strlen($description, 'utf8') < 30) or (mb_strlen($description, 'utf8') > 512)) $info .= "Некорректная длина описания проекта ";
		if ((mb_strlen($security_key, 'utf8') < 16) or (mb_strlen($name, 'utf8') > 64)) $info .= "Некорректная длина ключа безопасности ";
		if(!(strlen($info) > 0))$db->execute("UPDATE `woc_projects` SET `name`='$name', `about`='$description', `security_key`='$security_key', `url`='$url', `path`='$path_sc', `in_develop`='$in_develop' WHERE `id`=$id;")
			or $info .= $db->error();
		if((strlen($info) > 0)) $info = View::Alert($info);
			else $info = View::Alert("Проект обновлен", 'success');
	}
	ob_start();
		include View::Get('project_edit.html', $path);
	$content_main = ob_get_clean();
	
	$page = 'Редактирование проекта - ' . $name;
	break;
case 'view':
	if (isset($_GET['id'])) $id = $_GET['id'];
		elseif (isset($_POST['id'])) $id = $_POST['id'];
		else show_error("404", 'Проект не найден');
	$query = $db->execute("SELECT * FROM `woc_projects` WHERE `url`='$id'");
	if(!$db->num_rows($query)) show_error("404", 'Проект не найден');
	$project = $db->fetch_assoc($query,0);
	if($user and ($user->lvl() < 8 and $project['user'] != $user->name() and $project['in_develop']) or $project['in_develop'] and !$user) show_error("403", 'Проект скрыт');
	$query = $db->execute("SELECT `{$bd_names['users']}`.`{$bd_users['id']}`, `{$bd_names['users']}`.`verified`, `{$bd_names['users']}`.`{$bd_users['login']}`
				FROM `woc_projects_players`
				RIGHT JOIN `{$bd_names['users']}`
				ON `woc_projects_players`.`uid` = `{$bd_names['users']}`.`{$bd_users['id']}`
				WHERE `woc_projects_players`.`pid` = '{$project['id']}'
				ORDER BY  RAND()
				LIMIT 6");

	$pr = ($db->num_rows($query))?"":"Пока еще никто не играл на этом проекте :(";
	for ($i = 0; $i <= 5; $i++) {
		if($elem = $db->fetch_assoc($query,0)) {
			$ver = ($elem['verified'])? ' <div class="label label-success stt" data-toggle="tooltip" title="Подтвержденный ник"><i class="glyphicon glyphicon-ok"></i></div>':'';
			$pl[$i] = "<img src=\"skin.php?mini=" . $elem[$bd_users['id']] . "\" width=\"100%\" class=\"img-thumbnail\"><a href=\"go/user/profile/" . $elem[$bd_users['login']] . "\">" . $elem[$bd_users['login']] . $ver . "</a>";
		} else {
			$pl[$i] = '';
		}
	}

	$project['about'] = nl2br($project['about']);
	if ($user and ($user->name() == $project['user'] or $user->lvl() >= 8))
		$adm = true;
	else
		$adm = false;
	ob_start();
	if($adm)
		include View::Get('project_view_admin.html', $path);
	else
		include View::Get('project_view.html', $path);
	$content_main = ob_get_clean();
	$page = "Просмотр проекта - " . $project['name'];
	break;
case 'add':
	$page = "Добавление проекта";
	$query = $db->execute("SELECT * FROM `banlist` WHERE `name`='" . $user->name() . "' AND (`temptime`>NOW() OR `type`='2')");
	if(!$db->num_rows($query)) {
		$content_main = View::ShowStaticPage('project_add.html', $path);
	} else {
		ob_start();
		$ban = $db->fetch_array($query);
		include View::Get("project_add_banned.html", $path);
		$content_main = ob_get_clean();
	}
	break;
case 'list':
default:
	$info = "";
	if (isset($_POST['wocid'])) {
		if(isset($_POST['toggle'])) {
			$query = $db->execute("SELECT `in_develop`, `user` FROM `woc_projects` WHERE `id`={$_POST['wocid']}");
			print $db->error();
			$query = $db->fetch_assoc($query);
			if(($user and $user->lvl() >= 8) or ($user and $query['user'] == $user)) {
				$query = ($query['in_develop'] == 0)? 1:0;
				$db->execute("UPDATE `woc_projects` SET `in_develop`='$query' WHERE `id`={$_POST['wocid']};");
				$info = ($query)? View::Alert("Проект теперь виден только Вам", "success") : View::Alert("Проект теперь виден для всех", "success");
				print $db->error();
			} else $info = View::Alert("Недостаточно прав");
		} elseif(isset($_POST['delete'])) {
			$query = $db->execute("SELECT `user` FROM `woc_projects` WHERE `id`={$_POST['wocid']}");
			print $db->error();
			$query = $db->fetch_assoc($query);
			if(($user and $user->lvl() >= 8) or ($user and $query['user'] == $user)) {
				$db->execute("DELETE FROM `woc_projects` WHERE `id`={$_POST['wocid']};");
				$db->execute("DELETE FROM `woc_projects_players` WHERE  `pid`={$_POST['wocid']};");
				$info = View::Alert("Проект удален", "success");
			} else $info = View::Alert("Недостаточно прав");
		}
	else $info = View::Alert("Некорректный запрос");
	}
	if (isset($_GET['page'])) $page = $_GET['page'];
		elseif (isset($_POST['page'])) $page = $_POST['page'];
		else $page = 1;
	if ($page == 0) $page = 1;
	$first = ((int) $page - 1) * $num_by_page;
	$last  = (int) $page * $num_by_page;
	if ($user and $user->lvl() >= 8) $query = "SELECT * FROM `woc_projects` ORDER BY `name` ASC LIMIT $first, $last";
		elseif ($user) $query = "SELECT * FROM `woc_projects` WHERE (`in_develop`=0 OR `user`='$player') ORDER BY `name` ASC LIMIT $first, $last";
		else $query = "SELECT * FROM `woc_projects` WHERE `in_develop`=0 ORDER BY `name` ASC LIMIT $first, $last";
	$query = $db->execute($query);
	$content_list = '';
	$num = $first + 1;
	while($tmp_project = $db->fetch_assoc($query,0)) {
		ob_start();
			include View::Get((($user and $user->lvl() >= 8) or ($user and $tmp_project['user'] == $player))? 'project_item_admin.html':'project_item.html', $path);
		$content_list .= ob_get_clean();
		$num++;
	}
	ob_start();
	include View::Get('projects_list.html', $path);
	$content_main = ob_get_clean();

	if ($user and $user->lvl() >= 8) $query = "SELECT COUNT(*) FROM `woc_projects`";
		elseif ($user) $query = "SELECT COUNT(*) FROM `woc_projects` WHERE (`in_develop`=0 OR `user`='$player')";
		else $query = "SELECT COUNT(*) FROM `woc_projects` WHERE `in_develop`=0";
	$result = $db->execute($query);
	$line = $db->fetch_array($result);
	$view = new View("projects/pagin/");
	$content_main .= $view->arrowsGenerator(Rewrite::GetURL('projects/list'), $page, $line[0], $num_by_page, "pagin");
	$page = "Список проектов";
	break;
}