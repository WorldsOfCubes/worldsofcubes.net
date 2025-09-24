<?php
//if (empty($user) or $user->lvl() < 1) { accss_deny(); }


$num_by_page = 25;

loadTool('profile.class.php');
if (isset($_GET['do']))
	$do = $_GET['do']; elseif (isset($_POST['do']))
	$do = $_POST['do'];
else $do = 1;
$path = 'users/';
if ($do == 'full' and (isset($_GET['name']) or isset($_POST['name']))) {
	$menu->SetItemActive('users');
	if (isset($_GET['name']))
		$name = $_GET['name']; elseif (isset($_POST['name']))
		$name = $_POST['name'];
	$pl = new User($name, $bd_users['login']);
	if (!$pl->id()) {
		include(MCR_ROOT.'/location/404.php');
	} else {
		$query = ($user and $user ->lvl() >= 8)?
			$db->execute("SELECT `woc_projects`.`name`, `woc_projects`.`url`
				FROM `woc_projects`,  `woc_projects_players`
				WHERE `woc_projects_players`.`uid` = " . $pl->id() . " AND `woc_projects`.`id` = `woc_projects_players`.`pid`
				ORDER BY  RAND()
				LIMIT 6"):
			($user)?
				$db->execute("SELECT `woc_projects`.`name`, `woc_projects`.`url`
				FROM `woc_projects`,  `woc_projects_players`
				WHERE `woc_projects_players`.`uid` = " . $pl->id() . " AND `woc_projects`.`id` = `woc_projects_players`.`pid` AND (`woc_projects`.`in_develop`=0 OR `woc_projects`.`user`='" . $user->name() . "')
				ORDER BY  RAND()
				LIMIT 6"):
				$db->execute("SELECT `woc_projects`.`name`, `woc_projects`.`url`
				FROM `woc_projects`,  `woc_projects_players`
				WHERE `woc_projects_players`.`uid` = " . $pl->id() . " AND `woc_projects`.`id` = `woc_projects_players`.`pid`  AND `woc_projects`.`in_develop`=0
				ORDER BY  RAND()
				LIMIT 6");
		$pr = ($db->num_rows($query))?"":"Пользователь не играет ни на одном проекте.";
		for ($i = 0; $i <= 5; $i++) {
			if($elem = $db->fetch_assoc($query,0)) {
				$pr .= "<li><a href=\"go/project/view/" . $elem['url'] . "\">" . $elem['name'] . "</a>";
			}
		}
		$page = lng('USER_POFILE')." - ".$name;
		$stat = $pl->getStatistic();
		ob_start();
		include View::Get('user_profile.html', $path);
		$content_main = ob_get_clean();
	}
} elseif ($do == 'banned') {
	$menu->SetItemActive('banned');
	if (isset($_GET['p'])) $p = $_GET['p'];
	elseif (isset($_POST['p'])) $p = $_POST['p'];
	else $p = 1;
	if ($p == 0) $p = 1;
	$page = lng('USERS_LIST');
	$first = ((int) $p - 1) * $num_by_page;
	$last  = (int) $p * $num_by_page;
	$query = $db->execute("SELECT * FROM `banlist` WHERE `temptime` > NOW() OR `type` = 2
				ORDER BY `time` DESC
				LIMIT $first, $num_by_page");
	$content_list = '';
	$num = $first + 1;
	ob_start();
	if ($db->num_rows($query))
		while($tmp_user = $db->fetch_assoc($query,0)) {
			include View::Get('users_ban_item.html', $path);
			$num++;
		}
	else echo "
	<td colspan='5' align='center'>Никого нет :(</td>
	";
	$content_list .= ob_get_clean();
	ob_start();
	include View::Get('users_ban_list.html', $path);
	$content_main = ob_get_clean();

	$result = BD("SELECT COUNT(*) FROM `banlist` WHERE `temptime` > NOW() OR `type` = 2");
	$line = $db->fetch_array($result);
	$view = new View("users/");
	$content_main .= $view->arrowsGenerator(Rewrite::GetURL('users/banned'), $p, $line[0], $num_by_page, "pagin");
} else {
	if ($do == 0)
		$do = 1;
	$menu->SetItemActive('users');
	$page = lng('USERS_LIST');
	$first = ((int)$do - 1) * $num_by_page;
	if(isset($_GET['search']) and strlen($_GET['search'])) {
		$where = " WHERE {$bd_users['login']} LIKE '%".$db->safe($_GET['search'])."%'";
		$search = TextBase::HTMLDestruct($db->safe($_GET['search']));
	} else $where = $search = '';
	$query = $db->execute("SELECT `{$bd_names['users']}`.`{$bd_users['id']}`,  `{$bd_names['users']}`.`verified`, `{$bd_names['users']}`.`{$bd_users['login']}`, `{$bd_names['users']}`.`{$bd_users['female']}`, `{$bd_names['users']}`.default_skin, `{$bd_names['groups']}`.name AS group_name
				FROM `{$bd_names['users']}`
				LEFT JOIN `{$bd_names['groups']}`
				ON `{$bd_names['groups']}`.id = `{$bd_names['users']}`.`{$bd_users['group']}`
				$where ORDER BY `{$bd_names['users']}`.`{$bd_users['login']}` ASC
				LIMIT $first, $num_by_page");
	if ($db->num_rows($query)) {
		$content_list = '';
		$num = $first + 1;
		while ($tmp_user = $db->fetch_assoc($query, 0)) {
			ob_start();
			include View::Get('users_item.html', $path);
			$content_list .= ob_get_clean();
			$num++;
		}

		$result = $db->execute("SELECT COUNT(*) FROM `{$bd_names['users']}`$where");
		$line = $db->fetch_array($result);
		$view = new View("users/");
		$url = (!$config['rewrite']) ? ((strlen($search)) ? "go/users/search/$search/" : "go/users/") : ((strlen($search)) ? "?mode=users&search=$search&do=" : "?mode=users&do=");

	} else $content_list = View::ShowStaticPage('no_users.html', $path);
	ob_start();
	include View::Get('users_list.html', $path);
	$content_main = ob_get_clean();
	if ($db->num_rows($query)) $content_main .= $view->arrowsGenerator($url, $do, $line[0], $num_by_page, "pagin");
}