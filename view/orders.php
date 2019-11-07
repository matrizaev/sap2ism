<?php
	include 'orders_config.php';
	$ref = '1';
	if (!isset($_GET['type']) && !isset($_GET['ou']) && !isset($_GET['year']))
	{
		$sql = 'SELECT COUNT(`docs`.`doc_id`) AS `Count`, `types`.`type_id`, `types`.`type_name`, `types`.`view_name` FROM  `sap_documents` AS `docs` INNER JOIN `sap_documents_types` AS `types` ON `types`.`type_id`=`docs`.`doc_type` WHERE 1 GROUP BY `types`.`type_id` ORDER BY `types`.`type_id` ASC';
		$result = mysqli_query($link, $sql) or die('Ошибка запроса '.$sql);
		$types_list = array();
		while ($row = mysqli_fetch_assoc($result))
			$types_list[] = $row;
		$sql = 'SELECT COUNT(DISTINCT `doc_org` ) AS Count FROM  `sap_documents` WHERE 1';
		$result = mysqli_query($link, $sql) or die('Ошибка запроса '.$sql);
		$row = mysqli_fetch_assoc($result);
		$ou_count = $row['Count'];
		$path_str = '<a href="orders.php">Приказы и распоряжения</a>&rarr;';
		$title = 'Приказы и распоряжения';
		$content = '<table class="InnerContent"><tbody><tr><th>Категория</th><th>Описание</th><th>Категорий</th><th>Материалов</th></tr>';
		foreach ($types_list as $key => $value)
		{
			$content .= '<tr><td class="FolderName"><a href="orders.php?type='.$types_list[$key]['type_id'].'">'.$types_list[$key]['type_name'].'</a></td>';
			$content .= '<td class="FolderDesc">&nbsp;</td>';
			$content .= '<td class="FolderCatsLinks">'.$ou_count.'</td>';
			$content .= '<td class="FolderCatsLinks">'.$types_list[$key]['Count'].'</td></tr>';
			
		}
		$content .= '</tbody></table>';
	}
	else if (!isset($_GET['ou']) && !isset($_GET['year']) && isset($_GET['type']))
	{
		$type = (int)$_GET['type'];
		$sql = 'SELECT COUNT(`docs`.`doc_id`) AS `Count1`, COUNT(DISTINCT YEAR(`docs`.`doc_date`)) AS `Count2`, `orgs`.`org_id`, `orgs`.`org_name`, `types`.`type_name` FROM  `sap_documents` AS `docs` INNER JOIN `sap_organizations` AS `orgs` ON `orgs`.`org_id`=`docs`.`doc_org` INNER JOIN `sap_documents_types` AS `types` ON `docs`.`doc_type` = `types`.`type_id` WHERE `docs`.`doc_type` = '.$type.' GROUP BY `orgs`.`org_id` ORDER BY `orgs`.`org_name` ASC';
		$result = mysqli_query($link, $sql) or die('Ошибка запроса '.$sql);
		$ou_count = mysqli_num_rows($result);
		if ($ou_count > 0)
		{
			$ou_list = array();
			while ($row = mysqli_fetch_assoc($result))
				$ou_list[] = $row;
			$path_str = '<a href="orders.php">Приказы и распоряжения</a>&rarr;<a href="orders.php?type='.$type.'">'.$ou_list[0]['type_name'].'</a>&rarr;';
			$title = $ou_list[0]['type_name'];
			$content = '<table class="InnerContent"><tbody><tr><th>Категория</th><th>Описание</th><th>Категорий</th><th>Материалов</th></tr>';
			foreach ($ou_list as $key => $value)
			{
				$content .= '<tr><td class="FolderName"><a href="orders.php?type='.$type.'&amp;ou='.$ou_list[$key]['org_id'].'">'.$ou_list[$key]['org_name'].'</a></td>';
				$content .= '<td class="FolderDesc">&nbsp;</td>';
				$content .= '<td class="FolderCatsLinks">'.$ou_list[$key]['Count2'].'</td>';
				$content .= '<td class="FolderCatsLinks">'.$ou_list[$key]['Count1'].'</td></tr>';
				
			}
			$content .= '</tbody></table>';
		}
	}
	else if (!isset($_GET['year']) && isset($_GET['ou']) && isset($_GET['type']))
	{
		$type = (int)$_GET['type'];
		$ou = (int)$_GET['ou'];
		$sql = 'SELECT COUNT(`docs`.`doc_id`) AS `Count`, `orgs`.`org_name`, `types`.`type_name`, YEAR(`docs`.`doc_date`) AS `doc_year` FROM `sap_documents` AS `docs` INNER JOIN `sap_organizations` AS `orgs` ON (`docs`.`doc_org` = `orgs`.`org_id`) INNER JOIN `sap_documents_types` AS `types` ON (`docs`.`doc_type` = `types`.`type_id`) WHERE `docs`.`doc_org` = "'.$ou.'" AND `docs`.`doc_type` = '.$type.' GROUP BY YEAR(`docs`.`doc_date`) ORDER BY `docs`.`doc_date` DESC';
		$result = mysqli_query($link, $sql) or die('Ошибка запроса '.$sql);
		$year_count = mysqli_num_rows($result);
		if ($year_count > 0)
		{
			$year_list = array();
			while ($row = mysqli_fetch_assoc($result))
				$year_list[] = $row;
			$path_str = '<a href="orders.php">Приказы и распоряжения</a>&rarr;<a href="orders.php?type='.$type.'">'.$year_list[0]['type_name'].'</a>&rarr;<a href="orders.php?type='.$type.'&amp;ou='.$ou.'">'.$year_list[0]['org_name'].'</a>&rarr;';
			$title = $year_list[0]['org_name'];
			$content = '<table class="InnerContent"><tbody><tr><th>Категория</th><th>Описание</th><th>Категорий</th><th>Материалов</th></tr>';
			foreach ($year_list as $key => $value)
			{
				$content .= '<tr><td class="FolderName"><a href="orders.php?type='.$type.'&amp;ou='.$ou.'&amp;year='.$year_list[$key]['doc_year'].'">'.$year_list[$key]['doc_year'].'</a></td>';
				$content .= '<td class="FolderDesc">&nbsp;</td>';
				$content .= '<td class="FolderCatsLinks">0</td>';
				$content .= '<td class="FolderCatsLinks">'.$year_list[$key]['Count'].'</td></tr>';
				
			}			
			$content .= '</tbody></table>';
		}
	}
	else if (isset($_GET['year']) && isset($_GET['ou']) && isset($_GET['type']))
	{
		$type = (int)$_GET['type'];
		$ou = (int)$_GET['ou'];
		$year = (int)$_GET['year'];
		$sql = 'SELECT `docs`.`doc_id`, `docs`.`doc_number`, `docs`.`doc_author`, `docs`.`doc_date`, `docs`.`doc_description`, `deps`.`dep_name`, `types`.`type_name`, `types`.`view_name`, `orgs`.`org_name` FROM  `sap_documents` AS `docs` INNER JOIN `sap_departments` AS `deps` ON (`docs`.`doc_org` = `deps`.`org_id` AND `docs`.`doc_dep` = `deps`.`dep_id`) INNER JOIN `sap_documents_types` AS `types` ON (`docs`.`doc_type` = `types`.`type_id`) INNER JOIN `sap_organizations` AS `orgs` ON (`docs`.`doc_org` = `orgs`.`org_id`) WHERE `docs`.`doc_org` = "'.$ou.'" AND `docs`.`doc_type` = '.$type.' AND YEAR(`docs`.`doc_date`) = '.$year.' ORDER BY `doc_date` DESC, `doc_number` DESC';
		$result = mysqli_query($link, $sql) or die('Ошибка запроса '.$sql);
		$doc_count = mysqli_num_rows($result);
		if ($doc_count > 0)
		{
			while ($row = mysqli_fetch_assoc($result))
			{
				if (!isset($path_str))
				{
					$path_str = '<a href="orders.php">Приказы и распоряжения</a>&rarr;<a href="orders.php?type='.$type.'">'.$row['type_name'].'</a>&rarr;<a href="orders.php?type='.$type.'&amp;ou='.$ou.'">'.$row['org_name'].'</a>&rarr;'.$year;
					$title = $row['type_name'].' '.$row['org_name'];
				}
				$content .= '<table class="InnerContent" id="'.$row['doc_id'].'"><caption>';
				$content .= '<a target="_top" href="/index.php?option=com_content&amp;view=article&amp;id=10&amp;type='.$type.'&amp;ou='.$ou.'&amp;year='.$year.'#'.$row['doc_id'].'">'.$row['view_name'].' №'.$row['doc_number'].' от '.$row['doc_date'].'</a>';
				$content .= '</caption><tbody><tr><th class="InnerContent_th">Описание</th>';
				$content .= '<td>'.$row['doc_description'].'</td></tr>';
				$content .= '<tr><th class="InnerContent_th">Автор</th>';
				$content .= '<td>'.$row['doc_author'].'</td></tr>';
				$content .= '<tr><th class="InnerContent_th">Отдел</th><td>'.$row['dep_name'].'</td></tr>';
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
	if (!isset($content))
	{
		$content = '<div class="NotFound_Wrapper"><div class="NotFound">Нет содержимого</div></div>';
		$title = 'Приказы и распоряжения';
		$path_str = '<a href="orders.php">Приказы и распоряжения</a>&rarr;';		
	}
	mysqli_close($link);
	$module_search_string = 'orders_search.php';
	include 'tree_content.php';
?>