<?php

class PManager {
	public static function CheckNew() {
		global $db, $pm_count, $user;
		if (!$user)
			return 0;
		if (!isset($pm_count)) {
			$pm_count = $db->execute("SELECT COUNT(*) FROM `pm` WHERE `reciver` = '".$user->name()."' AND `viewed`=0");
			$pm_count = $db->fetch_array($pm_count);
			$pm_count = $pm_count[0];
		}
		return $pm_count;
	}

	public static function SendNotify(User $user, $topic, $text) {
		global $db;
		return self::SendPM(sqlConfigGet('email-name'), $user, $topic, $text);
	}

	public static function SendPM($from, User $to, $topic, $text) {
		global $db;
		$query = $db->execute("INSERT INTO `pm` (`date`, `sender`, `reciver`, `topic`, `text`) VALUES (NOW(), '".$db->safe($from)."', '".$db->safe($to->name())."', '".$db->safe($topic)."', '".$db->safe($text)."');");
		if(!$query)
			return false;
		EMail::Send($to->email(), "Получено новое ЛС", "<html><body><p>Здравствуйте, " . $to->name() . ". Вам прижло новое личное сообщение.</p><p>Прочитать его можно <a href='http://{$_SERVER['HTTP_HOST']}/go/pm/view/". $db->insert_id() . "'>Здесь</a></p><p>С уважением,<br /><b>команда проекта WorldsOfCubes</b></p></body></html>");
		return true;
	}
} 