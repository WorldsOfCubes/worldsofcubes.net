<?php if (!defined('MCR'))
	exit;

class Profile extends View {

	private $user;
	private $admin_mode;
	private $self_ignore;
	private $id;

	public function __construct($user_input, $style_sd = false, $form_id = 'customp', $self_ignore = false) {
		global $user;

		parent::View($style_sd);

		$this->user = false;

		if (!is_numeric($user_input) and !is_object($user_input))
			return false;

		$this->user = (is_numeric($user_input)) ? new User((int)$user_input) : $user_input;

		if (!$this->user->Exist()) {

			unset($this->user);
			$this->user = false;
			return false;
		}

		$this->admin_mode = false;
		$this->id = $form_id;

		if (!empty($user) and $user->lvl() >= 15)
			$this->admin_mode = true;
		if ($self_ignore and !empty($user) and $user->id() === $this->user->id())
			$this->self_ignore = true; else $this->self_ignore = false;
	}

	public static function TimeFrom($time, $time2 = -1) {

		$out = "";

		$cur_time = ($time2 == -1 ? date('Y-m-d H:i:s') : $time2);
		$time_sec = strtotime($cur_time) - strtotime($time);

		if ($time_sec < 0)
			return $out;
		if ($time_sec < 60)
			$out = "меньше минуты";

		$out .= (int)($time_sec / 86400);
		$out .= " д. ";
		$time_sec = $time_sec % 86400;
		$out .= (int)($time_sec / 3600);
		$out .= " ч. ";
		$time_sec = $time_sec % 3600;
		$out .= (int)($time_sec / 60);
		$out .= " мин.";

		return $out;
	}

	public function Show($modal_mode = true) {
		global $donate;
		if (!$this->user)
			return false;

		$statistic = $this->user->getStatistic();

		$main_info['name'] = $this->user->name();

		$tmpParam = $this->user->getStatisticTime('active_last');
		$main_info['active_last'] = ($tmpParam) ? self::TimeFrom($tmpParam) : 'Никогда'; // toDo show Online \ Offline	

		$main_info['skin'] = $this->user->getSkinLink(false, '&amp;', true);
		$main_info['female'] = ($this->user->isFemale()) ? 1 : 0;

		if($this->user->verified()) {
			$user_info['verified']  = array('<div class="label label-success stt" data-toggle="tooltip" data-placement="left" title="Подтвержденный ник"><i class="glyphicon glyphicon-ok"></i> Подтверждено</div>', '');
		}
		$user_info['group'] = array($this->user->getGroupName(), 'Группа');


		$tmpParam = $this->user->getStatisticTime('create_time');

		$user_info['create_time'] = array(($tmpParam) ? $tmpParam : 'Неизвестно', 'Дата регистрации');

		$user_info['comments_num'] = array((int)$statistic['comments_num'], 'Комментарии');

		$tmpParam = $this->user->email();
		$user_info['email'] = array(($tmpParam) ? $tmpParam : lng('NOT_SET'), 'Почта');

		if ($this->admin_mode && !$this->self_ignore) {
			$user_info['ip'] = array($this->user->ip(), 'IP');
		}

		ob_start();
		include $this->GetView('common_profile.html');

		return ob_get_clean();
	}
}

?>