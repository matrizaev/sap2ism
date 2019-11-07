<?php
	include 'orders_config.php';
	$title = 'Поиск приказов и распоряжений';
	$ref = (int)($_GET['ref']);
	if ($ref == 2)
	{
		if (isset($_GET['type']))
		{
			$type = (int)($_GET['type']);
			if (isset($_GET['ou']))
			{
				$ou = (int)($_GET['ou']);
				if (isset($_GET['dep']))
				{
					$dep = (int)($_GET['dep']);
					$path_str = '<a href="orders2.php?type='.$type.'&amp;ou='.$ou.'&amp;dep='.$dep.'">&larr;Вернуться</a>';
				}
				else
					$path_str = '<a href="orders2.php?type='.$type.'&amp;ou='.$ou.'">&larr;Вернуться</a>';
			}
			else
				$path_str = '<a href="orders2.php?type='.$type.'">&larr;Вернуться</a>';
		}
		else
			$path_str = '<a href="orders2.php">&larr;Вернуться</a>';
	}
	else
	{
		$ref = 1;
		if (isset($_GET['type']))
		{
			$type = (int)($_GET['type']);
			if (isset($_GET['ou']))
			{
				$ou = (int)($_GET['ou']);
				if (isset($_GET['year']))
				{
					$year = (int)($_GET['year']);
					$path_str = '<a href="orders.php?type='.$type.'&amp;ou='.$ou.'&amp;year='.$year.'">&larr;Вернуться</a>';
				}
				else
					$path_str = '<a href="orders.php?type='.$type.'&amp;ou='.$ou.'">&larr;Вернуться</a>';
			}
			else
				$path_str = '<a href="orders.php?type='.$type.'">&larr;Вернуться</a>';
		}
		else
			$path_str = '<a href="orders.php">&larr;Вернуться</a>';
	}
	$title = 'Поиск по приказам и распоряжениям';
	$content = '<div class="NotFound_Wrapper"><div class="NotFound">Нет содержимого</div></div>';
	$searchword = trim($_GET['searchword']);
	if (strlen($searchword) < 2)
		unset($searchword);
	if (isset($searchword))
	{
		$searchword = mysqli_real_escape_string($link, $searchword);
		$sql = 'SELECT * FROM `sap_documents` AS `docs` INNER JOIN `sap_documents_types` AS `types` ON `docs`.`doc_type` = `types`.`type_id` INNER JOIN `sap_organizations` AS `orgs` ON `docs`.`doc_org` = `orgs`.`org_id` INNER JOIN `sap_departments` AS `deps` ON (`docs`.`doc_dep` = `deps`.`dep_id` AND `docs`.`doc_org` = `deps`.`org_id`) WHERE `doc_number` LIKE "%'.$searchword.'%" OR `doc_description` LIKE "%'.$searchword.'%" OR `doc_author` LIKE "%'.$searchword.'%" ORDER BY `doc_date` DESC, `doc_number` DESC';
		$result = mysqli_query($link, $sql) or die('Ошибка запроса '.$sql);
		if (mysqli_num_rows($result) != 0)
		{
			$content = '';
			while ($row = mysqli_fetch_assoc($result))
			{
				$content .= '<table class="InnerContent" id="'.$row['doc_id'].'">';
				$content .= '<caption><a target="_top" href="/index.php?option=com_content&amp;view=article&amp;id='.($ref==2)?'11':'10'.'>'.$row['view_name'].' №'.$row['doc_number'].' от '.$row['doc_date'].'</a>';
				if ($admin_mode == true)
				{
					$content .= '&nbsp;<a onclick="DeleteElementByID(\''.$row['doc_id'].'\')" href="#" class="DeleteOrder">удалить</a>';
				}
				$content .= '</caption><tbody><tr><th class="InnerContent_th">Организация</th>';
				$content .= '<td>'.$row['org_name'].'</td></tr>';
				$content .= '<tr><th class="InnerContent_th">Описание</th>';
				$content .= '<td>'.$row['doc_description'].'</td></tr>';
				$content .= '<tr><th class="InnerContent_th">Автор</th>';
				$content .= '<td>'.$row['doc_author'].'</td></tr>';
				$content .= '<tr><th class="InnerContent_th">Отдел</th>';
				$content .= '<td>'.$row['dep_name'].'</td></tr>';	
				$sql = 'SELECT `val`.`val_id`, `val_types`.`type_name`, `docs`.`doc_number`, `docs`.`doc_type`, `docs`.`doc_org`, YEAR(`docs`.`doc_date`) AS `doc_year` FROM `sap_validity` AS `val` INNER JOIN `sap_validity_types` AS `val_types` ON `val`.`val_type` = `val_types`.`type_id` INNER JOIN `sap_documents` AS `docs` ON `val`.`val_id` = `docs`.`doc_id` WHERE `val`.`doc_id` = "'.$row['doc_id'].'"';
				$result1 = mysqli_query($link, $sql) or die('Ошибка запроса '.$sql);
				while ($row1 = mysqli_fetch_assoc($result1))
					$content .= '<tr><th class="InnerContent_th">'.$row1['type_name'].'</th><td><a href="orders.php?type='.$row1['doc_type'].'&amp;ou='.$row1['doc_org'].'&amp;year='.$row1['doc_year'].'#'.$row1['val_id'].'">Документом №'.$row1['doc_number'].'</a></td></tr>';
				$sql = 'SELECT * FROM `sap_filelist` WHERE `doc_id` = "'.$row['doc_id'].'"';
				$result1 = mysqli_query($link, $sql) or die('Ошибка запроса '.$sql);
				while ($row1 = mysqli_fetch_assoc($result1))
				{
					$file_id = $row1['doc_id'].$row1['file_id'];
					$content .= '<tr><th class="InnerContent_th">Файл</th><td class="Download"><a onclick="getFileDcount(\''.$file_id.'\')" href="http://ism.orene.ru/module/orders_download.php?id='.$file_id.'">'.$row1['file_description'].' (загружен <span id="'.$file_id.'">'.$row1['file_dcount'].'</span> раз)</a></td></tr>';
				}
				$content .= '</tbody></table>';
			}
		}
	}
	mysqli_close($link);
	$module_search_string = 'orders_search.php';
	include 'tree_content.php';
?>