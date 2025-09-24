<?php
define('MCR', '4.0S');
define('EX', '4');
define('PROGNAME', 'WoCNETEngine '.MCR);
define('FEEDBACK', PROGNAME.' &copy; 2013-' . date("Y") . ' <a href="http://WorldsOfCubes.NET">WoC Team</a>');

/* Base class for objects with Show method */

Class View {

	const def_theme = 'Default';

	protected $st_subdir;

	public function View($style_subdir = '') {

		if (!$style_subdir)
			$style_subdir = false;

		$this->st_subdir = $style_subdir;
	}

	// ToDo transform output

	public function ShowPage($way, $out = false) {
		global $config;

		ob_start();

		include self::Get($way, $this->st_subdir);

		return ob_get_clean();
	}

	public static function ShowStaticPage($way, $st_subdir = false, $out = false) {
		global $config;

		ob_start();

		include self::Get($way, $st_subdir);

		return ob_get_clean();
	}

	protected function GetView($way) {

		return self::Get($way, $this->st_subdir);
	}

	public static function GetURL($way = false) {
		global $config;

		$current_st_url = empty($config['s_theme']) ? DEF_STYLE_URL : STYLE_URL.$config['s_theme'].'/';

		if (!$way)
			return $current_st_url;

		if (DEF_STYLE_URL === $current_st_url)
			return DEF_STYLE_URL.$way; else return (file_exists(MCR_STYLE.$config['s_theme'].'/'.$way) ? $current_st_url : DEF_STYLE_URL).$way;
	}

	public static function URL($way = false) {

		echo self::GetURL($way);
	}

	public static function Alert($text, $state = 'danger') {

		return "<div class=\"alert alert-$state\">$text</div>";
	}

	public static function Get($way, $base_ = false) {
		global $config;
		loadTool("template.class.php");
		$base = ($base_) ? $base_ : '';

		if (empty ($config['s_theme']))
			$theme_dir = ''; else {

			if ($config['s_theme'] === self::def_theme)
				return TemplateParser::MakeCache(MCR_STYLE.self::def_theme.'/'.$base.$way,
					str_replace('/', '', $base) . '_' . str_replace('/', '', $way) . '_' . self::def_theme);

			$theme_dir = $config['s_theme'].'/';
		}

		return TemplateParser::MakeCache(MCR_STYLE.((file_exists(MCR_STYLE.$theme_dir.$base.$way)) ? $theme_dir : self::def_theme.'/').$base.$way,
			str_replace('/', '', $base) . '_' . str_replace('/', '', $way) . '_' . $config['s_theme']);
	}

	public function arrowsGenerator($link, $curpage, $itemsnum, $per_page, $prefix = false) {
		global $config;
		if (!$prefix) { // Default arrows style

			$prefix = 'common';
			$st_subdir = 'other/';
		} else

			$st_subdir = $this->st_subdir;

		$numoflists = ceil($itemsnum / $per_page);
		$arrows = '';

		if ($numoflists > 10 and $curpage > 4) {

			$showliststart = $curpage - 4;
			$showlistend = $curpage + 5;

			if ($showliststart < 1)
				$showliststart = 1;

			if ($showlistend > $numoflists)
				$showlistend = $numoflists;
		} else {

			$showliststart = 1;

			if ($numoflists < 10)
				$showlistend = $numoflists; else                   $showlistend = 10;
		}

		ob_start();

		if ($numoflists > 1) {

			if ($curpage > 1) {

				if ($curpage - 4 > 1) {
					$var = 1;
					$text = '<<';
					include $this->Get($prefix.'_list_item.html', $st_subdir);
				}

				$var = $curpage - 1;
				$text = '<';
				include $this->Get($prefix.'_list_item.html', $st_subdir);
			}

			for ($i = $showliststart; $i <= $showlistend; $i++) {

				$var = $i;
				$text = $i;

				if ($i == $curpage)
					include $this->Get($prefix.'_list_item_selected.html', $st_subdir); else                include $this->Get($prefix.'_list_item.html', $st_subdir);
			}

			if ($curpage < $numoflists) {

				$var = $curpage + 1;
				$text = '>';
				include $this->Get($prefix.'_list_item.html', $st_subdir);

				if ($curpage + 5 < $numoflists) {
					$var = $numoflists;
					$text = '>>';
					include $this->Get($prefix.'_list_item.html', $st_subdir);
				}
			}
		}

		$arrows = ob_get_clean();

		if ($arrows) {

			ob_start();

			include $this->Get($prefix.'_list.html', $st_subdir);

			return ob_get_clean();
		}

		return '';
	}
}

class Item extends View {

	protected $type;
	protected $id;

	protected $db;

	public function __construct($id, $type, $db, $style_sd = false) {

		parent::View($style_sd);

		$this->id = (int)$id;
		$this->db = $db;

		$this->type = (int)$type;
	}

	public function type() {

		if (!$this->Exist())
			return false;

		return $this->type;
	}

	public function id() {

		return $this->id;
	}

	public function Exist() {

		if ($this->id)
			return true;
		return false;
	}

	public function Delete() {
		global $db;
		if (!$this->Exist())
			return false;

		$db->execute("DELETE FROM `{$this->db}` WHERE `id`='".$this->id."'");

		$this->id = false;
		return true;
	}
}

class ItemType {  // stock types

	const News = 1;
	const Comment = 2;
	const Skin = 3;
	const Server = 4;

	/** @const */
	public static $SQLConfigVar = array(

		'rcon-port',
		'rcon-serv',
		'rcon-pass',

		'next-reg-time',

		'email-verification',
		'email-verification-salt',
		'email-name',
		'email-mail',

		'json-verification-salt',

		'protection-key',
		'launcher-version',

		'latest-update-date',
		'latest-version-name',
		'latest-version-tag',
		'stable-update-date',
		'stable-version-name',
		'stable-version-tag',

		'smtp-user',
		'smtp-pass',
		'smtp-host',
		'smtp-port',
		'smtp-hello',
	);
}

Class TextBase {

	public static function HTMLDestruct($text) {

		return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
	}

	public static function HTMLRestore($text) {

		return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
	}

	public static function StringLen($text) {

		return mb_strlen($text, 'UTF-8');
	}

	public static function SQLSafe($text) {
		global $db;
		vtxtlog("Using old method TextBase::SQLSafe()");
		return $db->safe($text);
	}

	public static function CutString($text, $from = 0, $to = 255) {

		return mb_substr($text, $from, $to, 'UTF-8');
	}

	public static function CutWordWrap($text) {

		return str_replace(array("\r\n", "\n", "\r"), '', $text);
	}

	/* WordWrap - разбиение непрерывного текстового сообщения пробелами	*/

	public static function WordWrap($text, $width = 60, $break = "\n") {

		return preg_replace('#([^\s]{'.$width.'})#u', '$1'.$break, $text);
	}
}

Class DB {
	private $link;
	private $method;
	private $sql_config;

	public function connect($log_script, $die = true) {
		global $config, $bd_names;
		$this->method = (isset($config['db_method'])) ? $config['db_method'] : 'mysql'; //правит совместимость со старыми версиями. Если были поставлены моды для webMCR 2.35 и более ранних версий, то ничего не упадет.
		switch ($this->method) {
			case 'mysql':
				$this->link = mysql_connect($config['db_host'].':'.$config['db_port'], $config['db_login'], $config['db_passw']);
				if (!$this->link) {
					if ($die)
						die(lng('BD_ERROR').lng('BD_AUTH_FAIL')); else return 1;
				}
				if (!mysql_select_db($config['db_name'], $this->link)) {
					if ($die)
						die(lng('BD_ERROR').lng('BD_AUTH_FAIL')); else return 2;
				}
				break;
			case 'mysqli':
			default:
				$this->link = mysqli_connect($config['db_host'], $config['db_login'], $config['db_passw'], '', $config['db_port']);

				if (!$this->link) {
					if ($die)
						die(lng('BD_ERROR').lng('BD_AUTH_FAIL')); else return 1;
				}
				if (!mysqli_select_db($this->link, $config['db_name'])) {
					if ($die)
						die(lng('BD_ERROR').lng('BD_AUTH_FAIL')); else return 2;
				}
		}
		$this->execute("SET time_zone = '".date('P')."'");
		$this->execute("SET character_set_client='utf8'");
		$this->execute("SET character_set_results='utf8'");
		$this->execute("SET collation_connection='utf8_general_ci'");
		$query = $this->execute("SELECT * FROM `{$bd_names['data']}`");
		while ($query and $temp_cfg = $this->fetch_array($query))
			$this->sql_config[$temp_cfg['property']] = $temp_cfg['value'];
		if ($log_script and $config['action_log'])
			ActionLog($log_script);
		if ($die)
			CanAccess(2);
		return 0;
	}

	public function sql_config_get($property) {
		if (!in_array($property, ItemType::$SQLConfigVar))
			return false;
		return $this->sql_config[$property];
	}

	public function sql_config_set($property, $value) {
		global $db, $bd_names;

		if (!in_array($property, ItemType::$SQLConfigVar))
			return false;
		$result = $db->execute("INSERT INTO `{$bd_names['data']}` (value,property) VALUES ('".$db->safe($value)."','".$db->safe($property)."') ON DUPLICATE KEY UPDATE `value`='".$db->safe($value)."'");
		if ($result)
			$this->sql_config[$property] = $value;
		return ($result) ? true : false;
	}

	public function execute($query, $log = true) {
		global $queries;
		$queries++;
		switch ($this->method) {
			case 'mysql':
				$result = mysql_query($query, $this->link);
				break;
			case 'mysqli':
			default:
				$result = mysqli_query($this->link, $query);
				break;
		}
		if ($log and is_bool($result) and $result == false and function_exists("vtxtlog"))
			vtxtlog('SQLError: '.$this->error().' in query ['.$query.']');
		return $result;
	}

	public function safe($text) {
		switch ($this->method) {
			case 'mysql':
				return mysql_real_escape_string($text, $this->link);
				break;
			case 'mysqli':
			default:
				return mysqli_real_escape_string($this->link, $text);
				break;
		}
	}

	public function fetch_assoc($query) {
		switch ($this->method) {
			case 'mysql':
				return mysql_fetch_assoc($query);
				break;
			case 'mysqli':
			default:
				return mysqli_fetch_assoc($query);
				break;
		}
	}

	public function fetch_array($query) {
		switch ($this->method) {
			case 'mysql':
				return mysql_fetch_array($query);
				break;
			case 'mysqli':
			default:
				return mysqli_fetch_array($query);
				break;
		}
	}

	public function num_rows($query) {
		switch ($this->method) {
			case 'mysql':
				return mysql_num_rows($query);
				break;
			case 'mysqli':
			default:
				return mysqli_num_rows($query);
				break;
		}
	}

	public function error() {
		switch ($this->method) {
			case 'mysql':
				return mysql_error($this->link);
				break;
			case 'mysqli':
			default:
				return mysqli_error($this->link);
				break;
		}
	}

	public function insert_id() {
		switch ($this->method) {
			case 'mysql':
				return mysql_insert_id($this->link);
				break;
			case 'mysqli':
			default:
				return mysqli_insert_id($this->link);
				break;
		}
	}

	public function affected_rows() {
		switch ($this->method) {
			case 'mysql':
				return mysql_affected_rows($this->link);
				break;
			case 'mysqli':
			default:
				return mysqli_affected_rows($this->link);
				break;
		}
	}

	public function fetch_row($query) {
		switch ($this->method) {
			case 'mysql':
				return mysql_fetch_row($query);
				break;
			case 'mysqli':
			default:
				return mysqli_fetch_row($query);
				break;
		}
	}

	public function result($query, $row = true) {
		switch ($this->method) {
			case 'mysql':
				return mysql_result($query, $row);
				break;
			case 'mysqli':
			default:
				$temp = $this->fetch_array($query);
				return $temp[$row];
				break;
		}
	}
}

Class EMail {
	const ENCODE = 'utf-8';

	public static function Send($mail_to, $subject, $message) {
		global $config;

		$headers = array();
		$headers[] = "Reply-To: ".sqlConfigGet('email-mail');
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-Type: text/html; charset=\"".self::ENCODE."\"";
		$headers[] = "Content-Transfer-Encoding: 8bit";
		$headers[] = "From: \"".sqlConfigGet('email-name')."\" <".sqlConfigGet('email-mail').">";
		$headers[] = "To: ".$mail_to." <".$mail_to.">";
		$headers[] = "X-Priority: 3";
		$headers[] = "X-Mailer: PHP/".phpversion();

		$headers = implode("\r\n", $headers);

		if (!$config['smtp_tls'])
			$subject = '=?'.self::ENCODE.'?B?'.base64_encode($subject).'?=';

		return ($config['smtp']) ? self::smtpmail($mail_to, $subject, $message, $headers) : mail($mail_to, $subject, $message, $headers);
	}

	private static function smtpmail($mail_to, $subject, $message, $headers) {
		global $config;
		$smtp_user = sqlConfigGet('smtp-user');
		$smtp_pass = sqlConfigGet('smtp-pass');
		$smtp_host = sqlConfigGet('smtp-host');
		$smtp_port = (int)sqlConfigGet('smtp-port');
		$smtp_hello = sqlConfigGet('smtp-hello');
		if ($config['smtp_tls']) {
			loadTool("mail.class.php");
			$m = new Mail;
			$m->From(sqlConfigGet('email-name').';'.sqlConfigGet('email-mail'));
			$m->To($mail_to);
			$m->Subject($subject);
			$m->Body($message);
			$m->Priority(3);
			$m->smtp_on("ssl://".$smtp_host, $smtp_user, $smtp_pass, $smtp_port);
			return $m->Send();
		} else {
			$send = "Date: ".date("D, d M Y H:i:s")." UT\r\n";
			$send .= "Subject: {$subject}\r\n";
			$send .= $headers."\r\n\r\n".$message."\r\n";

			if (!$socket = @fsockopen($smtp_host, $smtp_port, $errno, $errstr, 10)) {
				vtxtlog('[SMPT] '.$errno." | ".$errstr);
				return false;
			}

			stream_set_timeout($socket, 10);

			if (!self::server_action($socket, false, "220") or !self::server_action($socket, $smtp_hello." ".$smtp_host."\r\n", "250", 'Приветствие сервера недоступно')
			)
				return false;

			if (!empty($smtp_user))
				if (!self::server_action($socket, "AUTH LOGIN\r\n", "334", 'Нет ответа авторизации') or !self::server_action($socket, base64_encode($smtp_user)."\r\n", "334", 'Неверный логин авторизации') or !self::server_action($socket, base64_encode($smtp_pass)."\r\n", "235", 'Неверный пароль авторизации')
				)
					return false;

			if (!self::server_action($socket, "MAIL FROM: <".$smtp_user.">\r\n", "250", 'Ошибка MAIL FROM') or !self::server_action($socket, "RCPT TO: <".$mail_to.">\r\n", "250", 'Ошибка RCPT TO') or !self::server_action($socket, "DATA\r\n", "354", 'Ошибка DATA') or !self::server_action($socket, $send."\r\n.\r\n", "250", 'Ошибка сообщения')
			)
				return false;

			self::server_action($socket, "QUIT\r\n");
			return true;
		}
	}

	private static function server_action($socket, $command = false, $correct_response = false, $error_mess = false, $line = __LINE__) {

		if ($command)
			fputs($socket, $command);
		if ($correct_response) {

			$server_response = '';
			while (substr($server_response, 3, 1) != ' ') {
				if ($server_response = fgets($socket, 256))
					continue;

				if ($error_mess)
					vtxtlog('[SMPT] '.$error_mess.' Line: '.$line);
				return false;
			}
			$code = substr($server_response, 0, 3);
			if ($code == $correct_response)
				return true;
		}

		if ($error_mess)
			vtxtlog('[SMPT] '.$error_mess.' | Code: '.$code.' Line: '.$line);
		fclose($socket);

		if ($correct_response)
			return false;
		return true;
	}
}

Class Message {

	/*	 
	 Comment - Валидация короткого сообщения, для хранения в БД и вывода на странице

	 Обрезать до 255 символов
	 Расформировать HTML
	 Заменить все переносы строк на <br>
	 Удалить оставшиеся символы переноса строки
	 
	*/

	public static function Comment($text) {
		global $db;
		$text = trim($text);
		$text = TextBase::HTMLDestruct($text);
		$text = preg_replace('/(\\R{2})\\R++/Usi', '$1', $text);
		$text = nl2br($text);
		$text = TextBase::CutWordWrap($text);
		$text = TextBase::CutString($text);

		return $db->safe($text);
	}

	/*
	
	 RestoreCom - Привести короткое сообщение в редактируемый вид
	
	*/

	public static function RestoreCom($string) {

		return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
	}

	// TODO BBEncode

	public static function BBDecode($text) {

		$text = preg_replace("/\[b\](.*)\[\/b\]/Usi", "<b>\\1</b>", $text);
		$text = preg_replace("/\[u\](.*)\[\/u\]/Usi", "<u>\\1</u>", $text);
		$text = preg_replace("/\[i\](.*)\[\/i\]/Usi", "<i>\\1</i>", $text);
		$text = preg_replace("/\[color=(\#[0-9A-F]{6}|[a-z]+)\](.*)\[\/color\]/Usi", "<span style=\"color:\\1\">\\2</span>", $text);
		$text = preg_replace("/\[url=(?:&#039;|&quot;)http:\/\/([^<]+)(?:&#039;|&quot;)](.*)\[\/url]/Usi", "<a href=\"http://\\1\">\\2</a>", $text, 3);

		$tmp = $text;

		while (strcmp($text = preg_replace("/\[quote=(?:&#039;|&quot;)(.*)(?:&#039;|&quot;)\](.+?)\[\/quote\]/Uis", "<div class=\"comment-quote\"><div class=\"comment-quote-a\">\\1 сказал(a):</div><div class=\"comment-quote-c\">\\2</div></div>", $tmp), $tmp) != 0)
			$tmp = $text;

		while (strcmp($text = preg_replace("/\[spoiler=(.*)\](.*)\[\/spoiler]/Usi", "<div class=\"panel-group\"><div class=\"panel panel-default\"><div class=\"panel-heading\"><a class=\"spoiler-tlink\" data-toggle=\"collapse\" href=\"\"><strong>\\1</strong></a></div><div class=\"spoiler-ttext panel-collapse collapse\"><div class=\"panel-body\">\\2</div></div></div></div>", $tmp), $tmp) != 0)
			$tmp = $text;

		return $text;
	}
}

class ItemLike {
	private $id;
	private $type;
	private $user_id;

	private $bd_content;
	private $db;

	public function ItemLike($item_type, $item_id, $user_id) {
		global $bd_names;

		$this->id = false;
		$this->bd_content = false;

		switch ($item_type) {
			case ItemType::News:
				$this->bd_content = $bd_names['news'];
				break;
			case ItemType::Comment:
				$this->bd_content = $bd_names['comments'];
				break;
			case ItemType::Skin:

				if (array_key_exists('sp_skins', $bd_names))

					$this->bd_content = $bd_names['sp_skins'];

				break;
			default:
				return false;
				break;
		}

		$this->db = $bd_names['likes'];

		$this->id = (int)$item_id;
		$this->type = (int)$item_type;
		$this->user_id = (int)$user_id;
	}

	public function Like($dislike = false) {
		global $db;
		if (!$this->bd_content)
			return 0;

		$var = (!$dislike) ? 1 : -1;

		$result = $db->execute("SELECT `var` FROM `{$this->db}` WHERE `user_id` = '".$this->user_id."' AND `item_id` = '".$this->id."' AND `item_type` = '".$this->type."'");

		if (!$db->num_rows($result)) {

			$db->execute("INSERT INTO `{$this->db}` (`user_id`, `item_id`, `item_type`, `var`) VALUES ('".$this->user_id."', '".$this->id."', '".$this->type."', '".$var."')");

			if (!$dislike)
				$db->execute("UPDATE `{$this->bd_content}` SET `likes` = `likes` + 1 WHERE `id` = '".$this->id."'"); else
				$db->execute("UPDATE `{$this->bd_content}` SET `dislikes` = `dislikes` + 1 WHERE `id` = '".$this->id."'");

			return 1;
		} else {

			$line = $db->fetch_array($result, MYSQL_NUM);

			if ((int)$line[0] == (int)$var)
				return 0;

			$db->execute("UPDATE `{$this->db}` SET `var` = '".$var."' WHERE `user_id` = '".$this->user_id."' AND `item_id` = '".$this->id."' AND `item_type` = '".$this->type."'");

			if (!$dislike)
				$db->execute("UPDATE `{$this->bd_content}` SET `likes` = `likes` + 1, `dislikes` = `dislikes` - 1  WHERE `id` = '".$this->id."'"); else
				$db->execute("UPDATE `{$this->bd_content}` SET `likes` = `likes` - 1, `dislikes` = `dislikes` + 1 WHERE `id` = '".$this->id."'");

			return 2;
		}
	}
}

Class Menu extends View {
	private $menu_items;
	private $menu_fname;

	public function Menu($style_sd = false, $auto_load = true, $mfile = 'instruments/menu_items.php') {
		global $config;

		parent::View($style_sd);

		$this->menu_fname = $mfile;

		if ($auto_load) {
			$menu_items = array();
			require(MCR_ROOT.$this->menu_fname);

			$this->menu_items = $menu_items;
		} else $this->menu_items = array(0 => array(), 1 => array());
	}

	private static function array_insert_before(&$array, $var, $key_name) {

		$index = array_search($key_name, array_keys($array));
		if ($index === false)
			return false;

		$part_array = array_splice($array, 0, $index);
		$array = array_merge($part_array, $var, $array);
		return true;
	}

	private function SaveMenu() {

		$txt = "<?php if (!defined('MCR')) exit;".PHP_EOL;
		$txt .= '$menu_items = '.var_export($this->menu_items, true).';'.PHP_EOL;

		$result = file_put_contents(MCR_ROOT.$this->menu_fname, $txt);

		return (is_bool($result) and $result == false) ? false : true;
	}

	private function ShowItem(&$item) {

		$button_name = $item['name'];
		$button_url = $item['url'];

		$button_class = ($item['active']) ? 'active' : 'not_active';

		$button_links = (isset($item['inner_html'])) ? $item['inner_html'] : '';

		$type = ($button_links) ? 'menu_dropdown_item' : 'menu_item';

		ob_start();
		include $this->GetView('menu/'.$type.'.html');

		return ob_get_clean();
	}

	public function DeleteItem($menu, $key) {

		$menu_id = 1;
		if ($menu == 'left')
			$menu_id = 0;

		$index = array_search($key, array_keys($this->menu_items[$menu_id]));
		if ($index === false)
			return false;

		array_splice($this->menu_items[$menu_id], $index, 1);
		return $this->SaveMenu();
	}

	/* TODO -- add config trigger cheker */

	public function Show() {
		global $user, $config;

		$menu_content = '';
		$html_menu = '';

		if (!empty($user))
			$user_lvl = $user->lvl(); else $user_lvl = 0;

		for ($i = 0; $i < 2; $i++) {

			if (!sizeof($this->menu_items[$i]))
				continue;

			foreach ($this->menu_items[$i] as $key => $value) {

				$this->menu_items[$i][$key]['access'] = true;

				if ($user_lvl < $value['lvl'])

					$this->menu_items[$i][$key]['access'] = false;

				elseif (array_key_exists('config', $value) and $value['config'] != -1 and array_key_exists($value['config'], $config) and is_bool($config[$value['config']]) and !$config[$value['config']]
				)

					$this->menu_items[$i][$key]['access'] = false;

				elseif ($value['permission'] != -1)

					if (!empty($user) and !$user->getPermission($value['permission']))

						$this->menu_items[$i][$key]['access'] = false;

				if ($value['parent_id'] <= -1 or !$this->menu_items[$i][$key]['access'])
					continue;

				$this->menu_items[$i][$value['parent_id']]['inner_html'] .= $this->ShowItem($value);

				if ($value['active'] and $value['parent_id'] > -1)

					$this->menu_items[$i][$value['parent_id']]['active'] = true;
			}

			foreach ($this->menu_items[$i] as $key => $value) {

				if ($value['parent_id'] > -1 or ($value['parent_id'] == -2 and !$value['inner_html']) or !$value['access'])
					continue;

				$menu_content .= $this->ShowItem($value, 'menu/menu_item');
			}

			$menu_align = ($i == 1) ? 'navbar-right' : 'navbar-left';

			ob_start();
			include $this->GetView('menu/menu.html');

			$html_menu .= ob_get_clean();

			$menu_content = '';
			unset($key, $value);
		}

		return $html_menu;
	}

	public function SaveItem($id, $menu, $info, $insert_before = false) {

		$menu_id = 1;
		if ($menu == 'left')
			$menu_id = 0;

		if (!is_array($info) or !$info['name'] or !is_int($info['lvl']) or (is_int($info['parent_id']) and $info['parent_id'] != -1) or (isset($info['config']) and is_int($info['config']) and $info['config'] != -1) or (is_int($info['permission']) and $info['permission'] != -1)
		)

			return false;

		for ($i = 0; $i < 2; $i++)

			if (array_key_exists($id, $this->menu_items[$i]))
				return false;

		$new_item = array(

			'name' => $info['name'], 'url' => $info['url'], 'parent_id' => ($info['parent_id']) ? $info['parent_id'] : -1, 'lvl' => (is_int($info['parent_id'])) - 1, 'permission' => $info['permission'], 'config' => (isset($info['config'])) ? $info['config'] : -1, 'active' => (isset($info['active'])) ? $info['active'] : false, 'inner_html' => '',);

		if ($insert_before) {

			if (!self::array_insert_before($this->menu_items[$menu_id], array($id => $new_item), $insert_before))

				$this->menu_items[$menu_id][$id] = $new_item;
		} else {

			$this->menu_items[$menu_id][$id] = $new_item;
		}

		return $this->SaveMenu();
	}

	public function AddItem($name, $url, $active = false, $menu = 'left') {

		for ($i = 0; $i < 2; $i++)

			foreach ($this->menu_items[$i] as $key => $value)

				if ($value['name'] == $name)
					return $key;

		$menu_id = 1;
		if ($menu == 'left')
			$menu_id = 0;

		$new_key = sizeof($this->menu_items[$menu_id]);

		$this->menu_items[$menu_id][$new_key] = array(

			'name' => $name, 'url' => $url, 'parent_id' => -1, 'lvl' => -1, 'permission' => -1, 'active' => $active, 'inner_html' => '',);

		return $new_key;
	}

	public function IsItemExists($item_key) {

		if (array_key_exists($item_key, $this->menu_items[0]))
			$menu_id = 0; elseif (array_key_exists($item_key, $this->menu_items[1]))
			$menu_id = 1;
		else return false;

		return $menu_id;
	}

	public function SetItemActive($item_key) {
		global $menu_items;

		$menu_id = $this->IsItemExists($item_key);
		if ($menu_id === false)
			return false;

		$this->menu_items[$menu_id][$item_key]['active'] = true;
		return true;
	}
}

Class Rewrite {

	private static function IsOn() {
		global $config;

		return ($config['rewrite']) ? true : false;
	}

	public static function GetURL($url_data, $get_params = array('mode', 'do'), $check_rewrite = true, $amp = '&amp;') {

		$str = '';
		$is_arr = (is_array($url_data)) ? true : false;

		if ($check_rewrite and self::IsOn()) {

			if ($is_arr) {

				foreach ($url_data as $key => $value)

					$str .= $value.'/';
			} else $str .= 'go/'.$url_data.'/';
		} else {

			if ($is_arr) {

				$first = true;

				foreach ($get_params as $key => $value) {

					if (!$value)
						continue;
					if ($first) {
						$str .= '?';
						$first = false;
					} else $str .= $amp;
					$str .= $value.'='.$url_data[$key];
				}
			} else $str .= '?'.$get_params[0].'='.$url_data;
		}

		return $str;
	}
}