<?php
	include 'orders_config.php';
	$ref = '2';
	if (!isset($_GET['type']) && !isset($_GET['ou']) && !isset($_GET['dep']))
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
		$path_str = '<a href="orders2.php">Приказы и распоряжения</a>&rarr;';
		$title = 'Приказы и распоряжения';
		$content = '<table class="InnerContent"><tbody><tr><th>Категория</th><th>Описание</th><th>Категорий</th><th>Материалов</th></tr>';
		foreach ($types_list as $key => $value)
		{
			$content .= '<tr><td class="FolderName"><a href="orders2.php?type='.$types_list[$key]['type_id'].'">'.$types_list[$key]['type_name'].'</a></td>';
			$content .= '<td class="FolderDesc">&nbsp;</td>';
			$content .= '<td class="FolderCatsLinks">'.$ou_count.'</td>';
			$content .= '<td class="FolderCatsLinks">'.$types_list[$key]['Count'].'</td></tr>';
			
		}
		$content .= '</tbody></table>';
	}
	else if (!isset($_GET['ou']) && !isset($_GET['dep']) && isset($_GET['type']))
	{
		$type = (int)$_GET['type'];
		$sql = 'SELECT COUNT(`docs`.`doc_id`) AS `Count1`, COUNT(DISTINCT `docs`.`doc_dep`) AS `Count2`, `orgs`.`org_id`, `orgs`.`org_name`, `types`.`type_name` FROM  `sap_documents` AS `docs` INNER JOIN `sap_organizations` AS `orgs` ON `orgs`.`org_id`=`docs`.`doc_org` INNER JOIN `sap_documents_types` AS `types` ON `docs`.`doc_type` = `types`.`type_id` WHERE `docs`.`doc_type` = '.$type.' GROUP BY `orgs`.`org_id` ORDER BY `orgs`.`org_name` ASC';
		$result = mysqli_query($link, $sql) or die('Ошибка запроса '.$sql);
		$ou_count = mysqli_num_rows($result);
		if ($ou_count > 0)
		{
			$ou_list = array();
			while ($row = mysqli_fetch_assoc($result))
				$ou_list[] = $row;
			$path_str = '<a href="orders2.php">Приказы и распоряжения</a>&rarr;<a href="orders2.php?type='.$type.'">'.$ou_list[0]['type_name'].'</a>&rarr;';
			$title = $ou_list[0]['type_name'];
			$content = '<table class="InnerContent"><tbody><tr><th>Категория</th><th>Описание</th><th>Категорий</th><th>Материалов</th></tr>';
			foreach ($ou_list as $key => $value)
			{
				$content .= '<tr><td class="FolderName"><a href="orders2.php?type='.$type.'&amp;ou='.$ou_list[$key]['org_id'].'">'.$ou_list[$key]['org_name'].'</a></td>';
				$content .= '<td class="FolderDesc">&nbsp;</td>';
				$content .= '<td class="FolderCatsLinks">'.$ou_list[$key]['Count2'].'</td>';
				$content .= '<td class="FolderCatsLinks">'.$ou_list[$key]['Count1'].'</td></tr>';
				
			}
			$content .= '</tbody></table>';
		}
	}
	else if (!isset($_GET['dep']) && isset($_GET['ou']) && isset($_GET['type']))
	{
		$type = (int)$_GET['type'];
		$ou = (int)$_GET['ou'];
		$sql = 'SELECT COUNT(`docs`.`doc_id`) AS `Count`, `deps`.`dep_name`, `deps`.`dep_id`, `orgs`.`org_name`, `types`.`type_name` FROM `sap_documents` AS `docs` INNER JOIN `sap_departments` AS `deps` ON (`docs`.`doc_dep` = `deps`.`dep_id` AND `docs`.`doc_org` = `deps`.`org_id`) INNER JOIN `sap_organizations` AS `orgs` ON (`docs`.`doc_org` = `orgs`.`org_id`) INNER JOIN `sap_documents_types` AS `types` ON (`docs`.`doc_type` = `types`.`type_id`) WHERE `deps`.`org_id` = "'.$ou.'" AND `docs`.`doc_type` = '.$type.' GROUP BY `deps`.`dep_id` ORDER BY `deps`.`dep_name`';
		$result = mysqli_query($link, $sql) or die('Ошибка запроса '.$sql);
		$dep_count = mysqli_num_rows($result);
		if ($dep_count > 0)
		{
			$dep_list = array();
			while ($row = mysqli_fetch_assoc($result))
				$dep_list[] = $row;
			$path_str = '<a href="orders2.php">Приказы и распоряжения</a>&rarr;<a href="orders2.php?type='.$type.'">'.$dep_list[0]['type_name'].'</a>&rarr;<a href="orders2.php?type='.$type.'&amp;ou='.$ou.'">'.$dep_list[0]['org_name'].'</a>&rarr;';
			$title = $dep_list[0]['org_name'];
			$content = '<table class="InnerContent"><tbody><tr><th>Категория</th><th>Описание</th><th>Категорий</th><th>Материалов</th></tr>';
			foreach ($dep_list as $key => $value)
			{
				$content .= '<tr><td class="FolderName"><a href="orders2.php?type='.$type.'&amp;ou='.$ou.'&amp;dep='.$dep_list[$key]['dep_id'].'">'.$dep_list[$key]['dep_name'].'</a></td>';
				$content .= '<td class="FolderDesc">&nbsp;</td>';
				$content .= '<td class="FolderCatsLinks">0</td>';
				$content .= '<td class="FolderCatsLinks">'.$dep_list[$key]['Count'].'</td></tr>';
				
			}			
			$content .= '</tbody></table>';
		}
	}
	else if (isset($_GET['dep']) && isset($_GET['ou']) && isset($_GET['type']))
	{
		$type = (int)$_GET['type'];
		$ou = (int)$_GET['ou'];
		$dep = (int)$_GET['dep'];
		$sql = 'SELECT `docs`.`doc_id`, `docs`.`doc_number`, `docs`.`doc_author`, `docs`.`doc_date`, `docs`.`doc_description`, `deps`.`dep_name`, `types`.`type_name`, `types`.`view_name`, `orgs`.`org_name` FROM  `sap_documents` AS `docs` INNER JOIN `sap_departments` AS `deps` ON (`docs`.`doc_org` = `deps`.`org_id` AND `docs`.`doc_dep` = `deps`.`dep_id`) INNER JOIN `sap_documents_types` AS `types` ON (`docs`.`doc_type` = `types`.`type_id`) INNER JOIN `sap_organizations` AS `orgs` ON (`docs`.`doc_org` = `orgs`.`org_id`) WHERE `docs`.`doc_org` = "'.$ou.'" AND `docs`.`doc_type` = '.$type.' AND `docs`.`doc_dep` = '.$dep.' ORDER BY `doc_date` DESC, `doc_number` DESC';
		$result = mysqli_query($link, $sql) or die('Ошибка запроса '.$sql);
		$doc_count = mysqli_num_rows($result);
		if ($doc_count > 0)
		{
			while ($row = mysqli_fetch_assoc($result))
			{
				if (!isset($path_str))
				{
					$path_str = '<a href="orders2.php">Приказы и распоряжения</a>&rarr;<a href="orders2.php?type='.$type.'">'.$row['type_name'].'</a>&rarr;<a href="orders2.php?type='.$type.'&amp;ou='.$ou.'">'.$row['org_name'].'</a>&rarr;'.$row['dep_name'];
					$title = $row['type_name'].' '.$row['dep_name'];
				}
				$content .= '<table class="InnerContent" id="'.$row['doc_id'].'"><caption>';
				$content .= '<a target="_top" href="/index.php?option=com_content&amp;view=article&amp;id=11&amp;type='.$type.'&amp;ou='.$ou.'&amp;dep='.$dep.'#'.$row['doc_id'].'">'.$row['view_name'].' №'.$row['doc_number'].' от '.$row['doc_date'].'</a>';
				$content .= '</caption><tbody><tr><th class="InnerContent_th">Описание</th>';
				$content .= '<td>'.$row['doc_description'].'</td></tr>';
				$content .= '<tr><th class="InnerContent_th">Автор</th>';
				$content .= '<td>'.$row['doc_author'].'</td></tr>';
				$content .= '<tr><th class="InnerContent_th">Отдел</th><td>'.$row['dep_name'].'</td></tr>';
				$sql = 'SELECT `val`.`val_id`, `val_types`.`type_name`, `docs`.`doc_number`, `docs`.`doc_type`, `docs`.`doc_org`, `docs`.`doc_dep` FROM `sap_validity` AS `val` INNER JOIN `sap_validity_types` AS `val_types` ON `val`.`val_type` = `val_types`.`type_id` INNER JOIN `sap_documents` AS `docs` ON `val`.`val_id` = `docs`.`doc_id` WHERE `val`.`doc_id` = "'.$row['doc_id'].'"';
				$result1 = mysqli_query($link, $sql) or die('Ошибка запроса '.$sql);
				while ($row1 = mysqli_fetch_assoc($result1))
					$content .= '<tr><th class="InnerContent_th">'.$row1['type_name'].'</th><td><a href="orders2.php?type='.$row1['doc_type'].'&amp;ou='.$row1['doc_org'].'&amp;dep='.$row1['doc_dep'].'#'.$row1['val_id'].'">Документом №'.$row1['doc_number'].'</a></td></tr>';
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
		$path_str = '<a href="orders2.php">Приказы и распоряжения</a>&rarr;';		
	}
	mysqli_close($link);
	$module_search_string = 'orders_search.php';
	include 'tree_content.php';
?>