<?php 
if (empty($user) or $user->lvl() < 1) { accss_deny(); }


$num_by_page = 25;
$path = 'projects/';

if (isset($_GET['do'])) $do = $_GET['do'];
	elseif (isset($_POST['do'])) $do = $_POST['do'];
	else $do = 'list';
$menu->SetItemActive('projects');


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
		} else $info = View::Alert("Некорректный запрос");
	}
	if (isset($_GET['page'])) $page = $_GET['page'];
		elseif (isset($_POST['page'])) $page = $_POST['page'];
		else $page = 1;
	if ($page == 0) $page = 1;
	$first = ((int) $page - 1) * $num_by_page;
	$last  = (int) $num_by_page;
	$query = "SELECT * FROM `woc_projects` WHERE `user`='$player'";
	$query = $db->execute($query);
	if($db->num_rows($query)) {
		$content_list = '';
		$num = $first + 1;
		while($tmp_project = $db->fetch_assoc($query,0)) {
			ob_start();
			include View::Get('project_item_admin.html', $path);
			$content_list .= ob_get_clean();
			$num++;
		}

		$query = "SELECT COUNT(*) FROM `woc_projects` WHERE `user`='$player'";
		$result = $db->execute($query);
		$line = $db->fetch_array($result);
		$view = new View("projects/pagin_my/");
		$content_main = $view->arrowsGenerator(Rewrite::GetURL('my_projects'), $page, $line[0], $num_by_page, "pagin");
	} else $content_list = "<tr><td colspan='4' align='center'>Вы не создали ни одного проекта!</td></tr>";

	ob_start();
	include View::Get('projects_list.html', $path);
	$content_main = ob_get_clean() . $content_main;
	$page = "Список проектов";