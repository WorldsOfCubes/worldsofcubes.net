<?php
if (!defined('MCR'))
	exit;
if (empty($user) or $user->lvl() <= 0) {
	accss_deny();
}

/* Default vars */
$page = lng('PAGE_OPTIONS');

$prefix = 'profile/';
$message = '';
$user_img_get = $user->getSkinLink().'&amp;refresh='.rand(1000, 9999);
$menu->SetItemActive('options');

if ($user->group() == 4 or !$user->email() or $user->gender() > 1) {

	loadTool('ajax.php');
	$html_info = '';

	if (CaptchaCheck(0, false)) {

		if (isset($_POST['female']) and $user->gender() > 1)

			$user->changeGender((!(int)$_POST['female']) ? 0 : 1);

		if (!empty($_POST['email'])) {

			$send_result = $user->changeEmail($_POST['email'], true);

			if ($send_result == 1)
				$html_info = lng('REG_CONFIRM_INFO'); elseif ($send_result == 1902)
				$html_info = lng('AUTH_EXIST_EMAIL');
			else $html_info = lng('MAIL_FAIL');
		}
	} elseif (isset($_POST['antibot']))
		$html_info = lng('CAPTCHA_FAIL');

	if ($user->group() == 4 or !$user->email() or $user->gender() > 1) {

		ob_start();

		include View::Get('cp_form.html', $prefix);

		if ($user->group() == 4 or !$user->email())
			include View::Get('profile_email.html', $prefix);

		if ($user->gender() > 1)
			include View::Get('profile_gender.html', $prefix);

		include View::Get('cp_form_footer.html', $prefix);

		$content_main .= ob_get_clean();
	}
}

if ($user->group() != 4) {
	ob_start();

	if ($user->getPermission('change_skin'))
		include View::Get('profile_skin.html', $prefix);
	if ($user->getPermission('change_skin') and !$user->defaultSkinTrigger())
		include View::Get('profile_del_skin.html', $prefix);
	if ($user->getPermission('change_cloak')) {
		include View::Get('profile_cloak.html', $prefix);
	} else include View::Get('profile_cloak_buy.html', $prefix);
	if ($user->getPermission('change_cloak') and file_exists($user->getCloakFName()))
		include View::Get('profile_del_cloak.html', $prefix);
	if ($user->getPermission('change_login'))
		include View::Get('profile_nik.html', $prefix);
	if ($user->getPermission('change_pass'))
		include View::Get((!$user->pass_set()) ? 'profile_pass_noold.html' : 'profile_pass.html', $prefix);

	$profile_inputs = ob_get_clean();

	$trusted_projects = $db->execute("SELECT * FROM `woc_projects`, `woc_projects_players` WHERE `uid`=$player_id AND `hide_dialog`=1 AND `pid`=`woc_projects`.`id`");
	ob_start();
	if(!$db->num_rows($trusted_projects))
		include View::Get('profile_trusted_empty.html', $prefix);
	else
		while ($trusted_project = $db->fetch_array($trusted_projects)) {
//			var_dump($trusted_project);
			include View::Get('profile_trusted_project.html', $prefix);
		}
	$profile_trusted_projects = ob_get_clean();

	ob_start();
	if ($user->getPermission('change_prefix'))
		include View::Get('profile_prefix.html', $prefix); else include View::Get('profile_prefix_buy.html', $prefix);
	$profile_prefix = ob_get_clean();
	ob_start();
	if ($user->lvl() == 6)
		include View::Get('profile_donate_buttons_premium.html', $prefix); elseif ($user->lvl() == 5)
		include View::Get('profile_donate_buttons_vip.html', $prefix);
	elseif ($user->lvl() == 2)
		include View::Get('profile_donate_buttons_default.html', $prefix);
	$profile_donate_btns = ob_get_clean();

	loadTool('profile.class.php');
	$user_profile = new Profile($user, 'other/', 'profile', true);
	$profile_info = $user_profile->Show(false);

	ob_start();
	include View::Get('profile.html', $prefix);

	$content_main .= ob_get_clean();
}