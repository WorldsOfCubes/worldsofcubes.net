<?php

if (!defined('MCR'))
	exit;

/* User class | User group class */

Class User {
	private $db;
	private $id;
	private $pass_set;

	private $tmp;
	private $permissions;

	private $ip;
	private $name;
	private $email;

	private $money;
	private $econ;
	private $group_name;

	private $lvl;
	private $warn_lvl;
	private $group;

	private $gender;
	private $female;

	private $deadtry;
	private $vote;

	private $topics;
	private $posts;
	private $verified;

	/** @const */
	public static $date_statistic = array('create_time', 'gameplay_last', 'active_last',);

	/** @const */
	public static $int_statistic = array('comments_num', 'play_times', 'undress_times',);

	public function __construct($input, $method = false) {
		global $db, $bd_users, $bd_names, $bd_money, $config;

		$this->db = $bd_names['users'];

		if (!$method)
			$method = $bd_users['id'];
		if ($method === $bd_users['id']) {

			$input = (int)$input;
			if (!$input) {
				$this->id = false;
				return false;
			}
		}
		$add_params = ($config['p_logic'] == 'wocauth') ? "
					   `{$this->db}`.`pass_set`," : '';
		$sql = "SELECT `{$this->db}`.`{$bd_users['login']}`,$add_params
					   `{$this->db}`.`{$bd_users['id']}`,
					   `{$this->db}`.`{$bd_users['tmp']}`,
					   `{$this->db}`.`{$bd_users['ip']}`,
					   `{$this->db}`.`{$bd_users['email']}`,
					   `{$this->db}`.`{$bd_users['deadtry']}`,
					   `{$this->db}`.`{$bd_users['female']}`,
					   `{$this->db}`.`{$bd_users['group']}`,
					   `vote`,
					   `{$this->db}`.`posts`,
					   `{$this->db}`.`topics`,
					   `{$this->db}`.`verified`,
					   `{$bd_names['groups']}`.`lvl`,
					   `{$bd_names['groups']}`.`name` AS group_name
					   FROM `{$this->db}`
					   LEFT JOIN `{$bd_names['groups']}` ON `{$bd_names['groups']}`.`id`=`{$this->db}`.`{$bd_users['group']}`
					   WHERE `{$this->db}`.`".$db->safe($method)."`='".$db->safe($input)."'";
		$result = $db->execute($sql);
		if (!$result or $db->num_rows($result) != 1) {
			$this->id = false;
			return false;
		}

		$line = $db->fetch_array($result, MYSQL_ASSOC);
		$this->id = (int)$line[$bd_users['id']];
		$this->name = $line[$bd_users['login']];
		$this->pass_set = ($config['p_logic'] == 'wocauth') ? (boolean)$line['pass_set'] : true;
		$this->group = (int)$line[$bd_users['group']];
		$group_temp = new Group($this->group);
		$this->permissions = $group_temp->GetAllPermissions();

		$this->group_name = $line['group_name'];
		$this->lvl = $line['lvl'];

		$this->tmp = $line[$bd_users['tmp']];
		$this->ip = $line[$bd_users['ip']];

		$this->verified = $line['verified'];

		$this->email = $line[$bd_users['email']];
		$this->deadtry = (int)$line[$bd_users['deadtry']];
		$this->vote = (int)$line['vote'];

		$this->topics = (int)$line['topics'];
		$this->posts = (int)$line['posts'];

		/* Пол персонажа */
		$gender = $line[$bd_users['female']];

		$this->gender = (is_numeric($gender)) ? (int)$gender : (($gender == 'female' or $gender == 'male') ? (($gender == 'female') ? 1 : 0) : 10);
		$this->female = ($this->gender == 1) ? true : false;

		$this->warn_lvl = 0;
		$query = $db->execute("SELECT `percentage` FROM `warnings` WHERE `uid`={$this->id} AND `expires` > NOW()");
		while($tmp = $db->fetch_array($query))
			$this->warn_lvl += $tmp['percentage'];

		return true;
	}

	public function warn($type, $reason, $expires, $points) {
		global $db, $user;
		if (!$user) exit;
		$db->execute("INSERT INTO `warnings` (`time`, `type`, `reason`, `expires`, `uid`, `mid`, `percentage`) VALUES (NOW(), $type, '{$db->safe($reason)}', '{$db->safe($expires)}', {$this->id}, " . $user->id() . ", $points)");

		$this->warn_lvl = 0;
		$query = $db->execute("SELECT `percentage` FROM `warnings` WHERE `uid`={$this->id} AND `expires` > NOW()");
		while($tmp = $db->fetch_array($query))
			$this->warn_lvl += $tmp['percentage'];
	}

	public function activity() {
		global $db, $bd_users;

		if ($this->id)
			$db->execute("UPDATE `{$this->db}` SET `active_last`= NOW() WHERE `{$bd_users['id']}`='".$this->id."'");
	}

	public function isOnline() {

		if ($this->tmp === '0')
			return false;

		$last_active = $this->getStatisticTime('active_last');
		if (!$last_active)
			return false;

		if (time() - strtotime($last_active) > 300)
			return false;

		return true;
	}

	public function authenticate($pass) {
		global $db, $bd_users;

		if (!$this->id)
			return false;

		$result = $db->execute("SELECT `{$bd_users['password']}` FROM `{$this->db}` WHERE `{$bd_users['id']}`='".$this->id."'");
		$line = $db->fetch_array($result, MYSQL_NUM);

		$auth_info = array('pass_db' => $line[0], 'pass' => $pass, 'user_id' => $this->id, 'user_name' => $this->name);
		$test_pass = MCRAuth::checkPass($auth_info);

		if (!$test_pass) {

			$db->execute("UPDATE `{$this->db}` SET `{$bd_users['deadtry']}`= {$bd_users['deadtry']} + 1 WHERE `{$bd_users['id']}`='".$this->id."'");
			$this->deadtry++;
		}

		return ($test_pass) ? true : false;
	}

	public function login($tmp, $ip, $save = false) {
		global $db, $bd_users, $config;

		if (!$this->id)
			return false;

		$save = ($save) ? true : false;

		if ($config['p_logic'] != 'usual' and $config['p_sync'])
			MCMSAuth::login($this->id());

		$db->execute("UPDATE `{$this->db}` SET `{$bd_users['deadtry']}` = '0', `{$bd_users['tmp']}`='".$db->safe($tmp)."', `{$bd_users['ip']}`='".$db->safe($ip)."' WHERE `{$bd_users['id']}`='".$this->id."'");

		$this->tmp = $tmp;

		if (!isset($_SESSION))
			session_start();

		$_SESSION['user_id'] = $this->id();
		$_SESSION['user_name'] = $this->name();
		$_SESSION['ip'] = $this->ip();
		$_SESSION['token'] = $tmp;

		if ($save)
			if ($save) setcookie( "WoCAccountCookie", $tmp, time() + 60 * 60 * 24 * 30 * 12, '/', '.' . str_replace('beta.', '', $_SERVER['SERVER_NAME']));

		return true;
	}

	public function logout() {
		global $db, $bd_users, $config;

		if ($config['p_logic'] != 'usual' and $config['p_sync'])
			MCMSAuth::logout();

		if (!isset($_SESSION))
			session_start();
		if (isset($_SESSION))
			session_destroy();

		$this->tmp = 0;
		$db->execute("UPDATE `{$this->db}` SET `{$bd_users['tmp']}`='".$this->tmp."' WHERE `{$bd_users['id']}`='".$this->id."'");

		if (isset($_COOKIE['WoCAccountCookie']))
			setcookie("WoCAccountCookie", "", time()-3600, '/', '.' . str_replace('beta.', '', $_SERVER['SERVER_NAME']));
	}

	public function canPostComment() {
		global $db, $bd_names;

		if (!$this->getPermission('add_comm') or $this->warnLVL() >= 100)
			return false;

		if ($this->group() == 3)
			return true;

		/* Интервал по времени 1 минута */

		$result = $db->execute("SELECT id FROM `{$bd_names['comments']}` WHERE user_id='".$this->id."' AND time>NOW()-INTERVAL 1 MINUTE");
		if ($db->num_rows($result))
			return false;

		return true;
	}

	public function gameLoginConfirm() {
		global $db, $bd_users;

		if (!$this->id)
			return false;

		$db->execute("UPDATE `{$this->db}` SET gameplay_last=NOW(),play_times=play_times+1 WHERE `{$bd_users['id']}`='".$this->id."'");

		return true;
	}

	public function gameLogoutConfirm() {
		global $db, $bd_users;

		if (!$this->id)
			return false;

		$result = $db->execute("SELECT `{$bd_users['id']}` FROM `{$this->db}` WHERE `{$bd_users['server']}` IS NOT NULL and `{$bd_users['id']}`='".$this->id."'");

		if ($db->num_rows($result) == 1)
			$db->execute("UPDATE `{$this->db}` SET `{$bd_users['server']}`=NULL WHERE `{$bd_users['id']}`='".$this->id."'");

		return true;
	}

	public function gameLoginLast() {
		global $db, $bd_users;

		if (!$this->id)
			return false;

		$result = $db->execute("SELECT `gameplay_last` FROM `{$this->db}` WHERE `gameplay_last` <> '0000-00-00 00:00:00' and `{$bd_users['id']}`='".$this->id."'");

		if ($db->num_rows($result) == 1) {

			$line = $db->fetch_array($result);

			return $line['gameplay_last'];
		} else return false;
	}

	public function getStatisticTime($param) {
		global $db, $bd_users, $config;

		if (!$this->id)
			return false;

		$param = $db->safe($param);
		if (!in_array($param, self::$date_statistic))
			return false;

		if ($param === 'create_time')
			$param = $bd_users['ctime'];

		$result = $db->execute("SELECT `$param` FROM `{$this->db}` WHERE `$param`<>'0000-00-00 00:00:00' and `{$bd_users['id']}`='".$this->id."'");

		if ($db->num_rows($result) == 1) {

			$line = $db->fetch_array($result);

			if ($config['p_logic'] == 'xenforo' or $config['p_logic'] == 'ipb' or $config['p_logic'] == 'dle')
				return date('Y-m-d H:i:s', (int)$line[$param]);    // from UNIX time

			return $line[$param];
		} else return false;
	}

	public function getStatistic() {
		global $db, $bd_users;

		if (!$this->id)
			return false;

		$result = $db->execute("SELECT `".implode("`, `", self::$int_statistic)."` FROM `{$this->db}` WHERE `{$bd_users['id']}`='".$this->id."'");

		if ($db->num_rows($result) == 1)

			return $db->fetch_array($result);

		else    return false;
	}

	public function setStatistic($field_name, $var) {
		global $db, $bd_users;

		if (!$this->id)
			return false;
		if (!in_array($field_name, self::$int_statistic))
			return false;

		$field = $db->safe($field_name);

		$var = (int)$var;
		$dec = ($var < 0) ? '-' : '+';
		$var = abs($var);

		if ($var > 0)
			$sql_var = $field.$dec.$var; else  $sql_var = "'0'";

		$db->execute("UPDATE `{$this->db}` SET `".$field."`=".$sql_var." WHERE {$bd_users['id']}='".$this->id."'");

		return true;
	}

	public function getMoney() {
		return $this->money;
	}

	public function getEcon() {
		return $this->econ;
	}

	public function addMoney($num) {
		global $db, $bd_names, $bd_money;

		if (!$this->id)
			return false;
		if (!$bd_names['iconomy'])
			return false;

		if (!(float)$num)
			return $this->getMoney();

		$new_pl_money = $this->getMoney() + $num;
		if ($new_pl_money < 0)
			$new_pl_money = 0;
		$db->execute("UPDATE `{$bd_names['iconomy']}` SET `{$bd_money['bank']}`='".$db->safe($new_pl_money)."' WHERE `{$bd_money['login']}`='".$db->safe($this->name())."'");
		$this->money = $new_pl_money;
		return $new_pl_money;
	}

	public function addEcon($num) {
		global $db, $bd_names, $bd_money;

		if (!$this->id)
			return false;
		if (!$bd_names['iconomy'])
			return false;

		if (!(float)$num)
			return $this->getEcon();

		$new_pl_emoney = $this->getEcon() + $num;
		if ($new_pl_emoney < 0)
			$new_pl_emoney = 0;

		$db->execute("UPDATE `{$bd_names['iconomy']}` SET `{$bd_money['money']}`='".$db->safe($new_pl_emoney)."' WHERE `{$bd_money['login']}`='".$db->safe($this->name())."'");
		$this->econ = $new_pl_emoney;
		return $new_pl_emoney;
	}

	public function getSkinFName() {
		global $site_ways;
		return MCRAFT.$site_ways['skins'].$this->name.'.png';
	}

	public function getCloakFName() {
		global $site_ways;
		return MCRAFT.$site_ways['cloaks'].$this->name.'.png';
	}

	public function getGroupName() {
		return $this->group_name;
	}

	public function deleteSkin() {
		if (file_exists($this->getSkinFName())) {
			unlink($this->getSkinFName());
			$this->deleteBuffer();
		}
	}

	public function deleteCloak() {
		if (file_exists($this->getCloakFName())) {
			unlink($this->getCloakFName());
			$this->deleteBuffer();
		}
	}

	public function defaultSkinMD5() {

		if (!$this->id)
			return false;

		$def_dir = MCRAFT.'tmp/default_skins/';

		if ($this->isFemale())
			$default_skin_md5 = $def_dir.'md5_female.md5'; else                     $default_skin_md5 = $def_dir.'md5.md5';

		if (file_exists($default_skin_md5)) {

			$md5 = @file($default_skin_md5);
			if ($md5[0])
				return $md5[0]; else {
				vtxtlog('[action.php] error while READING md5 cache file. '.$default_skin_md5);
				return false;
			}
		}

		if ($this->isFemale())
			$default_skin = $def_dir.'Char_female.png'; else                     $default_skin = $def_dir.'Char.png';

		if (file_exists($default_skin)) {

			$md5 = md5_file($default_skin);
			if (!$md5) {
				vtxtlog('[action.php] md5 generate error. '.$default_skin);
				return false;
			}

			if ($fp = fopen($default_skin_md5, 'w')) {
				if (!fwrite($fp, $md5))
					vtxtlog('[action.php] error while SAVE cache file. '.$default_skin_md5);
				fclose($fp);
			} else  vtxtlog('[action.php] error while CREATE cache file. '.$default_skin_md5);

			return $md5;
		} else {
			vtxtlog('[action.php] default skin file missing. '.$default_skin);
			return false;
		}
	}

	public function defaultSkinTrigger($new_value = -1) { /* is player use unique skin */
		global $db, $bd_users;

		if (!$this->id)
			return false;

		if ($new_value < 0) {

			$result = $db->execute("SELECT default_skin FROM `{$this->db}` WHERE `{$bd_users['id']}`='{$this->id()}'");
			$line = $db->fetch_array($result, MYSQL_NUM);

			$trigger = (int)$line[0];

			if ($trigger == 2) {

				if (!file_exists($this->getSkinFName()))
					$trigger = 1; elseif (!strcmp($this->defaultSkinMD5(), md5_file($this->getSkinFName())))
					$trigger = 1;
				else $trigger = 0;

				$db->execute("UPDATE `{$this->db}` SET default_skin='$trigger' WHERE `{$bd_users['id']}`='{$this->id()}'");
			}
			return ($trigger) ? true : false;
		}

		$new_value = ($new_value) ? 1 : 0;

		$db->execute("UPDATE `{$this->db}` SET default_skin='$new_value' WHERE `{$bd_users['id']}`='{$this->id()}'");

		return ($new_value) ? true : false;
	}

	public function deleteBuffer() {

		$mini = MCRAFT.'tmp/skin_buffer/'.$this->name.'_Mini.png';
		$skin = MCRAFT.'tmp/skin_buffer/'.$this->name.'.png';

		if (file_exists($mini))
			unlink($mini);
		if (file_exists($skin))
			unlink($skin);
	}

	public function setDefaultSkin() {

		if (!$this->id)
			return 0;

		$this->deleteSkin();

		$default_skin = MCRAFT.'tmp/default_skins/Char'.(($this->isFemale()) ? '_female' : '').'.png';

		if (!copy($default_skin, $this->getSkinFName()))
			vtxtlog('[SetDefaultSkin] error while COPY default skin for new user.'); else $this->defaultSkinTrigger(true);

		return 1;
	}

	public function changeName($newname) {
		global $db, $bd_users, $site_ways;

		if (!$this->id)
			return 0;

		$newname = trim($newname);

		if (!preg_match("/^[a-zA-Z0-9_-]+$/", $newname))
			return 1401;

		$result = $db->execute("SELECT `{$bd_users['login']}` FROM `{$this->db}` WHERE `{$bd_users['login']}`='".$db->safe($newname)."'");

		if ($db->num_rows($result))
			return 1402;

		if ((strlen($newname) < 4) or (strlen($newname) > 15))
			return 1403;

		$db->execute("UPDATE `{$this->db}` SET `{$bd_users['login']}`='".$db->safe($newname)."' WHERE `{$bd_users['login']}`='".$db->safe($this->name)."'");
		$db->execute("UPDATE `pm` SET `reciver`='".$db->safe($newname)."' WHERE `reciver`='".$db->safe($this->name)."'");
		$db->execute("UPDATE `pm` SET `sender`='".$db->safe($newname)."' WHERE `sender`='".$db->safe($this->name)."'");

		if (!empty($_SESSION['user_name']) and $_SESSION['user_name'] == $this->name)
			$_SESSION['user_name'] = $newname;

		/* Переименование файла скина и плаща */

		$way_tmp_old = $this->getSkinFName();
		$way_tmp_new = MCRAFT.$site_ways['skins'].$newname.'.png';

		if (file_exists($way_tmp_old) and !file_exists($way_tmp_new))
			rename($way_tmp_old, $way_tmp_new);

		$way_tmp_old = $this->getCloakFName();
		$way_tmp_new = MCRAFT.$site_ways['cloaks'].$newname.'.png';

		if (file_exists($way_tmp_old) and !file_exists($way_tmp_new))
			rename($way_tmp_old, $way_tmp_new);

		$buff_mini = MCRAFT.'tmp/skin_buffer/'.$this->name.'_Mini.png';
		$buff_mini_new = MCRAFT.'tmp/skin_buffer/'.$newname.'.png';
		$buff_skin = MCRAFT.'tmp/skin_buffer/'.$this->name.'.png';
		$buff_skin_new = MCRAFT.'tmp/skin_buffer/'.$newname.'.png';

		if (file_exists($buff_mini))
			rename($buff_mini, $buff_mini_new);
		if (file_exists($buff_skin))
			rename($buff_skin, $buff_skin_new);

		$this->name = $newname;

		return 1;
	}

	public function changePassword($newpass, $repass = false, $pass = false) {
		global $db, $bd_users, $config;

		if (!$this->id)
			return 0;

		if (!is_bool($repass)) {

			if (strcmp($repass, $newpass))
				return 1504;

			$regular = "/^[a-zA-Z0-9_-]+$/";

			if (!preg_match($regular, $pass) or !preg_match($regular, $newpass))
				return 1501;

			$result = $db->execute("SELECT `{$bd_users['password']}` FROM `{$this->db}` WHERE `{$bd_users['login']}`='".$db->safe($this->name)."'");
			$line = $db->fetch_array($result, MYSQL_NUM);

			if ($line == NULL or !MCRAuth::checkPass(array('pass_db' => $line[0], 'pass' => $pass, 'user_id' => $this->id, 'user_name' => $this->name)))
				return 1502;
		}

		$minlen = 4;
		$maxlen = 15;
		$len = strlen($newpass);

		if (($len < $minlen) or ($len > $maxlen))
			return 1503;

		($config['p_logic'] == 'wocauth') ? $db->execute("UPDATE `{$this->db}` SET `{$bd_users['password']}`='".MCRAuth::createPass($newpass)."', `pass_set`=1 WHERE `{$bd_users['login']}`='".$db->safe($this->name)."'") : $db->execute("UPDATE `{$this->db}` SET `{$bd_users['password']}`='".MCRAuth::createPass($newpass)."' WHERE `{$bd_users['login']}`='".$db->safe($this->name)."'");
		$this->pass_set = true;
		return 1;
	}

	public function changeGroup($newgroup) {
		global $db, $bd_users, $bd_names;

		$newgroup = (int)$newgroup;
		if ($newgroup < 0)
			return false;
		if ($newgroup == $this->group)
			return false;

		$result = $db->execute("SELECT `name` FROM `{$bd_names['groups']}` WHERE `id`='".$db->safe($newgroup)."'");

		if (!$db->num_rows($result))
			return false;
		$result = $db->fetch_array($result);

		$db->execute("UPDATE {$this->db} SET `{$bd_users['group']}`='".$db->safe($newgroup)."' WHERE `{$bd_users['id']}`='".$this->id."'");

		$group = new Group($newgroup);
		$this->permissions = $group->GetAllPermissions();
		$db->execute("DELETE FROM `permissions_inheritance` WHERE child='".$this->name."';");
		$db->execute("INSERT INTO permissions_inheritance (id, child, parent, type, world) VALUES (NULL, '".$this->name."', '".$db->safe($group->GetPexName())."', '1', NULL)");

		$this->group_name = $result['name'];
		$this->group = $newgroup;

		return true;
	}

	public function changeGender($female) {
		global $db, $bd_users, $config;

		$female = (int)$female;

		if ($config['p_logic'] == 'xenforo')
			$isFemale = ($female == 1) ? 'female' : 'male'; else
			$isFemale = ($female == 1) ? 1 : 0;

		if ((int)$this->gender() == $female)
			return false;

		$db->execute("UPDATE {$this->db} SET `{$bd_users['female']}`='$isFemale' WHERE `{$bd_users['id']}`='".$this->id."'");

		$this->gender = $female;
		$this->female = ($female) ? true : false;

		$this->setDefaultSkin();
		return true;
	}

	public function changeEmail($email, $verification = false) {
		global $db, $bd_users;

		$email = filter_var($email, FILTER_VALIDATE_EMAIL);
		if (!$email)
			return 1901;

		if ($email === $this->email) {

			if (!$verification)
				return 1;
		} else {

			$result = $db->execute("SELECT `id` FROM {$this->db} WHERE `{$bd_users['email']}`='".$db->safe($email)."' AND `{$bd_users['id']}` != '".$this->id."' ");
			if ($db->num_rows($result))
				return 1902;
		}

		if ($verification) {

			$subject = lng('MAIL_CONFIRM').' - '.sqlConfigGet('email-name');
			$http_link = 'http://'.$_SERVER['SERVER_NAME'].BASE_URL.'register.php?id='.$this->id().'&verificate='.$this->getVerificationStr();
			$message = '<html><body><p>'.lng('MAIL_CONFIRM_MES').'. <a href="'.$http_link.'">'.lng('OPEN').'</a></p><p>'.lng('MAIL_CONFIRM_MES_END').'</p></body></html>';

			$send_result = EMail::Send($email, $subject, $message);

			if ($verification and !$send_result)
				return 1903;
		}

		if ($email != $this->email)

			$db->execute("UPDATE {$this->db} SET `{$bd_users['email']}`='".$db->safe($email)."' WHERE `{$bd_users['id']}`='".$this->id."'");

		$this->email = $email;

		return 1;
	}

	public function getSkinLink($mini = false, $amp = '&amp;', $refresh = false) {
		global $config;

		$use_def_skin = $this->defaultSkinTrigger();
		$name = $this->name();

		$female = ($this->isFemale()) ? true : false;

		$get_p = '?';

		if ($mini == true)
			$get_p .= 'm=1';

		if ($this->id() === false)
			return $get_p;

		if ($get_p !== '?')
			$get_p .= $amp;
		$way_skin = $this->getSkinFName();
		$way_cloak = $this->getCloakFName();

		if (($mini and $use_def_skin) or (!file_exists($way_cloak) and $use_def_skin))

			$get_p .= 'female='.(($female) ? '1' : '0'); else
			$get_p .= 'user_name='.$name;

		if ($refresh)
			$get_p .= $amp.'refresh='.rand(1000, 9999);

		return $get_p;
	}

	public function changeVisual($post_name, $type = 'skin') {
		global $db, $bd_users;

		if (!$this->id or !$this->getPermission(($type == 'skin') ? 'change_skin' : 'change_cloak'))
			return 1605;

		if (!POSTGood($post_name))
			return 1604;

		$tmp_dir = MCRAFT.'tmp/';

		$new_file_info = POSTSafeMove($post_name, $tmp_dir);
		if (!$new_file_info)
			return 1610;

		$way = $tmp_dir.$new_file_info['tmp_name'];

		if ((int)$this->getPermission('max_fsize') < $new_file_info['size_mb'] * 1024) {

			unlink($way);
			return 1601;
		}

		loadTool('skin.class.php');

		$new_file_ratio = ($type == 'skin') ? skinGenerator2D::isValidSkin($way) : skinGenerator2D::isValidCloak($way);
		if (!$new_file_ratio or $new_file_ratio > (int)$this->getPermission('max_ratio')) {

			unlink($way);
			return 1602;
		}

		($type == 'skin') ? $this->deleteSkin() : $this->deleteCloak();
		$new_way = ($type == 'skin') ? $this->getSkinFName() : $this->getCloakFName();

		if (rename($way, $new_way))
			chmod($new_way, 0777); else {

			unlink($way);
			vtxtlog('[Ошибка модуля загрузки] Ошибка копирования ['.$way.'] в ['.$new_way.'] . Проверьте доступ на ЧТЕНИЕ \ ЗАПИСЬ соответствующих папок.');
			return 1611;
		}

		if ($type == 'skin') {

			if (!strcmp($this->defaultSkinMD5(), md5_file($this->getSkinFName())))
				$this->defaultSkinTrigger(true); else
				$this->defaultSkinTrigger(false);
		}

		$this->deleteBuffer();

		$db->execute("UPDATE `{$this->db}` SET `undress_times`=`undress_times`+1 WHERE `{$bd_users['id']}`='".$this->id()."'");
		return 1;
	}

	public function Delete() {
		global $db, $bd_users, $bd_names;

		if (!$this->id)
			return false;

		loadTool('catalog.class.php');

		$this->deleteCloak();
		$this->deleteSkin();
		$this->deleteBuffer();

		$result = $db->execute("SELECT `id` FROM `{$bd_names['comments']}` WHERE `user_id`='".$this->id."'");
		if ($db->num_rows($result) != 0) {

			while ($line = $db->fetch_array($result, MYSQL_NUM)) {

				$comment_del = new Comments_Item($line[0]);
				$comment_del->Delete();
				unset($comment_del);
			}
		}

		$db->execute("DELETE FROM `{$this->db}` WHERE `{$bd_users['id']}`= '".$this->id()."'");

		$this->id = false;
		return true;
	}

	public function getVerificationStr() {
		if (!$this->id)
			return false;

		$salt = sqlConfigGet('email-verification-salt');

		if (!$salt) {
			$salt = randString();
			sqlConfigSet('email-verification-salt', $salt);
		}

		return md5($this->id().$salt);
	}

	public function getPermission($param) {

		if (isset($this->permissions[$param]))
			return $this->permissions[$param];
		if (!$this->id)
			return false;

		$group = new Group($this->group);
		$value = $group->GetPermission($param);

		unset($group);

		if ((int)$value == -1)
			return false;

		$this->permissions[$param] = $value;

		return $value;
	}

	public function isFemale() {
		return $this->female;
	}

	public function gender() {
		return $this->gender;
	}

	public function pass_set() {
		return $this->pass_set;
	}


	public function verified() {
		return $this->verified;
	}

	public function Exist() {
		if ($this->id)
			return true;
		return false;
	}

	public function id() {
		return $this->id;
	}

	public function voted() {
		return $this->vote;
	}

	public function lvl() {
		return $this->lvl;
	}

	public function tmp() {
		return $this->tmp;
	}

	public function ip() {
		return $this->ip;
	}

	public function topics() {
		return $this->topics;
	}

	public function posts() {
		return $this->posts;
	}

	public function warnLVL() {
		return $this->warn_lvl;
	}

	public function email() {
		return $this->email;
	}

	public function group() {
		return $this->group;
	}

	public function auth_fail_num() {
		return $this->deadtry;
	}

	public function name() {
		return $this->name;
	}
}

Class Group extends TextBase {
	private $db;
	private $id;
	private $pavailable;

	public function Group($id = false, $addon = false) {
		global $bd_names;

		$this->db = $bd_names['groups'];
		$this->id = (int)$id;
		$this->pavailable = array("change_skin", "change_pass", "lvl", "change_cloak", "change_prefix", "change_login", "max_fsize", "max_ratio", "add_news", "adm_comm", "add_comm");

		if (isset($bd_names['sp_skins'])) {

			$this->pavailable[] = "sp_upload";
			$this->pavailable[] = "sp_change";
		}
		if (is_array($addon))
			$this->pavailable = array_merge($this->pavailable, $addon);
	}

	public function GetPermission($param) {
		global $db;
		if (!$this->id)
			return -1;
		if (!in_array($param, $this->pavailable))
			return -1;

		$result = $db->execute("SELECT `$param` FROM `{$this->db}` WHERE `id`='".$this->id."'");

		if ($db->num_rows($result) == 1) {

			$line = $db->fetch_array($result, MYSQL_NUM);
			$value = (int)$line[0];

			if ($param != 'max_fsize' and $param != 'max_ratio' and $param != 'lvl')

				$value = ($line[0]) ? true : false;
			return $value;
		} else return -1;
	}

	public function GetAllPermissions() {
		global $db;
		$sql_names = null;

		for ($i = 0; $i < sizeof($this->pavailable); $i++)
			($sql_names) ? $sql_names .= ",`{$this->pavailable[$i]}`" : $sql_names .= "`{$this->pavailable[$i]}`";

		$result = $db->execute("SELECT $sql_names FROM `{$this->db}` WHERE `id`='".$this->id."'");
		return $db->fetch_array($result, MYSQL_ASSOC);
	}

	public function Exist() {
		global $db;
		if (!$this->id)
			return false;

		$result = $db->execute("SELECT COUNT(*) FROM `{$this->db}` WHERE `id`='".$this->id."'");
		$num = $db->fetch_array($result, MYSQL_NUM);

		if ($num[0])
			return true;

		$this->id = false;
		return false;
	}

	public function Create($name, $pex_name, &$permissions) {
		global $db;
		if ($this->id)
			return false;

		if (!$name or !TextBase::StringLen($name))
			return false;

		$result = $db->execute("SELECT COUNT(*) FROM `{$this->db}` WHERE `name`='".$db->safe($name)."'");
		$num = $db->fetch_array($result, MYSQL_NUM);
		if ($num[0])
			return false;

		$sql_names = null;
		$sql_vars = null;

		foreach ($permissions as $key => $value) {

			if (!in_array($key, $this->pavailable))
				continue;

			if ($key != 'max_fsize' and $key != 'max_ratio' and $key != 'lvl')
				$value = ($value) ? 1 : 0; else                $value = $db->safe((int)$value);

			if ($sql_names)
				$sql_names .= ",`$key`"; else $sql_names .= "`$key`";
			if ($sql_vars)
				$sql_vars .= ",'$value'"; else $sql_vars .= "'$value'";
		}

		$result = $db->execute("INSERT INTO `{$this->db}` (`name`, `pex_name`,$sql_names) values ('".$db->safe($name)."','".$db->safe($pex_name)."',$sql_vars)");
		if ($result and $db->affected_rows())
			$this->id = $db->insert_id(); else return false;

		return true;
	}

	public function GetName() {
		global $db;
		$result = $db->execute("SELECT `name` FROM `{$this->db}` WHERE `id`='".$this->id."'");

		if ($db->num_rows($result) != 1)
			return false;
		$line = $db->fetch_array($result, MYSQL_NUM);

		return $line[0];
	}

	public function GetPexName() {
		global $db;
		$result = $db->execute("SELECT `pex_name` FROM `{$this->db}` WHERE `id`='".$this->id."'");

		if ($db->num_rows($result) != 1) {
			return false;
		}
		$line = $db->fetch_array($result, MYSQL_NUM);
		return $line[0];
	}

	public function IsSystem() {
		global $db;
		$result = $db->execute("SELECT `system` FROM `{$this->db}` WHERE `id`='".$this->id."'");

		if ($db->num_rows($result) != 1)
			return false;
		$line = $db->fetch_array($result, MYSQL_NUM);

		return ($line[0]) ? true : false;
	}

	public function Edit($name, $pex_name, &$permissions) {
		global $db;
		if (!$this->id)
			return false;
		if (!$name or !TextBase::StringLen($name))
			return false;

		$result = $db->execute("SELECT COUNT(*) FROM `{$this->db}` WHERE `name`='".$db->safe($name)."' and `id`!='".$this->id."'");
		$num = $db->fetch_array($result, MYSQL_NUM);
		if ($num[0])
			return false;

		$sql = null;

		for ($i = 0; $i < sizeof($this->pavailable); $i++) {

			$key = $this->pavailable[$i];

			if (isset($permissions[$key])) {

				if ($key != 'max_fsize' and $key != 'max_ratio' and $key != 'lvl')
					$value = ($permissions[$key]) ? 1 : 0; else                $value = $db->safe((int)$permissions[$key]);
			} else $value = 0;

			$sql .= ",`$key`='$value'";
		}

		if (!$sql)
			$sql = '';

		$result = $db->execute("UPDATE `{$this->db}` SET `name`='".$db->safe($name)."'$sql WHERE `id`='".$this->id."'");
		$result = $db->execute("UPDATE `{$this->db}` SET `pex_name`='".$db->safe($pex_name)."'$sql WHERE `id`='".$this->id."'");
		if ($result and $db->affected_rows())
			return true;

		return true;
	}

	public function Delete() {
		global $db, $bd_names;

		if (!$this->id)
			return false;
		if ($this->IsSystem())
			return false;

		$result = $db->execute("SELECT `id` FROM `{$bd_names['users']}` WHERE `group`='".$this->id."'");
		if ($db->num_rows($result) != 0) {

			while ($line = $db->fetch_array($result, MYSQL_NUM)) {

				$user_del = new User($line[0]);
				$user_del->Delete();
				unset($user_del);
			}
		}

		$result = $db->execute("DELETE FROM `{$this->db}` WHERE `id` = '".$this->id."' and `system` = '0'");

		$this->id = false;
		if ($result and $db->affected_rows())
			return true;

		return false;
	}
}

Class GroupManager {

	public static function GetList($selected) {
		global $db, $bd_names;

		$result = $db->execute("SELECT `id`, `name` FROM `{$bd_names['groups']}` ORDER BY `name` DESC LIMIT 0,90");
		$group_list = '';

		while ($line = $db->fetch_array($result, MYSQL_ASSOC))
			$group_list .= '<option value="'.$line['id'].'" '.(($selected == $line['id']) ? 'selected' : '').'>'.$line['name'].'</option>';

		return $group_list;
	}

	public static function GetNameByID($id) {

		if (!$id or $id < 0)
			return 'Удаленный';

		$grp_item = new Group($id);
		$grp_name = $grp_item->GetName();

		unset($grp_item);

		if (!$grp_name)
			return 'Удаленный'; else return $grp_name;
	}
}

?>