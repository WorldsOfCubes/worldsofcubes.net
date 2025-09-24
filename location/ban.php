<?php

if (empty($user) or $user->lvl() < 8) {
	accss_deny();
}
$menu->SetItemActive('ban');
$check = "";
$page = 'Блокировка доступа к добавлению проектов';
$content = '';
loadTool("pm.class.php");
if (isset($_POST['name']) or isset($_POST['reason']) or isset($_POST['temptime']) or isset($_POST['temp'])) {
	$name = InputGet('name');
	$reason = InputGet('reason');
	$time = InputGet('temptime');
	$temp = InputGet('temp', 'POST', 'int');
	$temp++;
	$ban_user = new User($name, $bd_users['login']);
	if ($name != $check AND $reason != $check AND $time != $check)
		if ($ban_user->id() and $ban_user->lvl() < 8) {
			PManager::SendNotify($ban_user, "Доступ к созданию проектов заблокирован", "Пользаватель <a href=\"go/user/profile/" . $user->name() . "\">" . $user->name() . "</a> заблокировал Вам доступ к созданию проектов.\nПричина: [b]" . $reason . "[/b]");
			$db->execute("INSERT INTO `banlist` (`admin`, `name`, `reason`, `temptime`, `type`, `time`) VALUES ('" . $user->name() . "','" . $ban_user->name() . "','" . $reason . "','" . $time . "','" . $temp . "',NOW())"
				. "ON DUPLICATE KEY UPDATE `admin`='" . $user->name() . "',`reason`='" . $reason . "',`temptime`='" . $time . "',`type`='" . $temp . "',`time`=NOW();");
			$content = '<div class="alert alert-success">Доступ пользователю ограничен</div>';
		} else $content = '<div class="alert alert-danger">Нет такого пользователя!</div>';
	else $content = '<div class="alert alert-danger">Не все поля заполнены!</div>';
}

ob_start();
include View::Get('ban.html');
$content_main = ob_get_clean();