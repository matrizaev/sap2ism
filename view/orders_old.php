<?php
	include 'orders_config.php';
	$ref = '1';
	$sql = 'SELECT DISTINCT `orgs`.`org_id`, `orgs`.`org_name` FROM  `sap_documents` AS `docs` INNER JOIN `sap_organizations` AS `orgs` ON `orgs`.`org_id`=`docs`.`doc_org` WHERE 1 ORDER BY `orgs`.`org_id` ASC';
	$result = mysqli_query($link, $sql) or die('Ошибка запроса '.$sql);
	$ou_count = mysqli_num_rows($result);
	$ou_list = array();
	while ($row = mysqli_fetch_assoc($result))
		$ou_list[] = $row;	
	$sql = 'SELECT DISTINCT `types`.`type_id`, `types`.`type_name`, `types`.`view_name` FROM  `sap_documents` AS `docs` INNER JOIN `sap_documents_types` AS `types` ON `types`.`type_id`=`docs`.`doc_type` WHERE 1 ORDER BY `types`.`type_id` ASC';
	$result = mysqli_query($link, $sql) or die('Ошибка запроса '.$sql);
	$types_count = mysqli_num_rows($result);
	$types_list = array();
	while ($row = mysqli_fetch_assoc($result))
		$types_list[] = $row;
	$sql = 'SELECT DISTINCT YEAR(`doc_date`) AS `years` FROM  `sap_documents` WHERE 1 ORDER BY `doc_date` DESC';
	$result = mysqli_query($link, $sql) or die('Ошибка запроса '.$sql);
	$year_count = mysqli_num_rows($result);
	$year_list = array();
	while ($row = mysqli_fetch_assoc($result))
		$year_list[] = $row;	
	if (isset($_GET['type']))
	{
		$type = (int)($_GET['type']);
		foreach ($types_list as $key => $value)
		{
			if ($types_list[$key]['type_id'] == $type)
			{
				$type_name = $types_list[$key]['type_name'];
				$view_name = $types_list[$key]['view_name'];
			}
		}
		if (!isset($type_name) || ($type_name == ''))
			unset($type);
	}
	if (isset($_GET['ou']) && isset($type))
	{
		$ou = (int)($_GET['ou']);
		foreach ($ou_list as $key => $value)
		{
			if ($ou_list[$key]['org_id'] == $ou)
				$ou_name = $ou_list[$key]['org_name'];
		}
		if (!isset($ou_name) || ($ou_name == ''))
			unset($ou);
	}
	if (isset($_GET['year']) && isset($type) && isset($ou))
	{
		$year = (int)($_GET['year']);
		$year_exist = false;
		foreach ($year_list as $key => $value)
		{
			if ($year_list[$key]['years'] == $year)
				$year_exist = true;
		}
		if ($year_exist == false)
			unset($year);
	}
	$path_str = '<a href="orders.php">Приказы и распоряжения</a>&rarr;';
	if (!isset($type))
	{
		$title = 'Приказы и распоряжения';
		$content = '<table class="InnerContent"><tbody><tr><th>Категория</th><th>Описание</th><th>Категорий</th><th>Материалов</th></tr>';
		foreach ($types_list as $key => $value)
		{
			$sql = 'SELECT COUNT( `doc_id` ) AS Count FROM  `sap_documents` WHERE `doc_type` = '.$types_list[$key]['type_id'];
			$result = mysqli_query($link, $sql) or die('Ошибка запроса '.$sql);
			$row = mysqli_fetch_assoc($result);
			$content .= '<tr><td class="FolderName"><a href="orders.php?type='.$types_list[$key]['type_id'].'">'.$types_list[$key]['view_name'].'</a></td>';
			$content .= '<td class="FolderDesc">&nbsp;</td>';
			$content .= '<td class="FolderCatsLinks">'.$ou_count.'</td>';
			$content .= '<td class="FolderCatsLinks">'.$row['Count'].'</td></tr>';
			
		}
		$content .= '</tbody></table>';
	}
	else
	{
		$path_str = $path_str.'<a href="orders.php?type='.$type.'">'.$type_name.'</a>&rarr;';
		$title = $type_name;
		if (!isset($ou))
		{
			$content = '<table class="InnerContent"><tbody><tr><th>Категория</th><th>Описание</th><th>Категорий</th><th>Материалов</th></tr>';
			foreach ($ou_list as $key => $value)
			{
				$sql = 'SELECT COUNT(`doc_id`) AS Count FROM  `sap_documents` WHERE `doc_org` = "'.$ou_list[$key]['org_id'].'" AND `doc_type` = '.$type;
				$result = mysqli_query($link, $sql) or die('Ошибка запроса '.$sql);
				$row = mysqli_fetch_assoc($result);
				$content .= '<tr><td class="FolderName"><a href="orders.php?type='.$type.'&amp;ou='.$ou_list[$key]['org_id'].'">'.$ou_list[$key]['org_name'].'</a></td>';
				$content .= '<td class="FolderDesc">&nbsp;</td>';
				$content .= '<td class="FolderCatsLinks">'.$year_count.'</td>';
				$content .= '<td class="FolderCatsLinks">'.$row['Count'].'</td></tr>';
			}
			$content .= '</tbody></table>';
		}
		else
		{
			$title = $title.' '.$ou_name;
			$path_str = $path_str.'<a href="orders.php?type='.$type.'&amp;ou='.$ou.'">'.$ou_name.'</a>&rarr;';
			if (!isset($year))
			{
				$content = '<table class="InnerContent"><tbody><tr><th>Категория</th><th>Описание</th><th>Категорий</th><th>Материалов</th></tr>';
				foreach ($year_list as $key => $value)
				{
					$sql = 'SELECT COUNT(`doc_id`) AS Count FROM  `sap_documents` WHERE `doc_org` = "'.$ou.'" AND `doc_type` = '.$type.' AND YEAR(`doc_date`) = '.$year_list[$key]['years'];
					$result = mysqli_query($link, $sql) or die('Ошибка запроса '.$sql);
					$row = mysqli_fetch_assoc($result);
					$content .= '<tr><td class="FolderName"><a href="orders.php?type='.$type.'&amp;ou='.$ou.'&amp;year='.$year_list[$key]['years'].'">'.$year_list[$key]['years'].'</a></td>';
					$content .= '<td class="FolderDesc">&nbsp;</td>';
					$content .= '<td class="FolderCatsLinks">0</td>';
					$content .= '<td class="FolderCatsLinks">'.$row['Count'].'</td></tr>';
				}
				$content .= '</tbody></table>';
			}
			else
			{
				$path_str = $path_str.$year;
				$sql = 'SELECT * FROM  `sap_documents` AS `docs` INNER JOIN `sap_departments` AS `deps` ON (`docs`.`doc_org` = `deps`.`org_id` AND `docs`.`doc_dep` = `deps`.`dep_id`) WHERE `doc_org` = "'.$ou.'" AND `doc_type` = '.$type.' AND YEAR(`doc_date`) = '.$year.' ORDER BY `doc_date` DESC, `doc_number` DESC';
				$result = mysqli_query($link, $sql) or die('Ошибка запроса '.$sql);
				$count = mysqli_num_rows($result);
				if ($count == 0)
					$content = '<div class="NotFound_Wrapper"><div class="NotFound">Нет содержимого</div></div>';
				else
				{
					$content = '';
					while ($row = mysqli_fetch_assoc($result))
					{
						$content .= '<table class="InnerContent" id="'.$row['doc_id'].'"><caption>';
						$content .= '<a target="_top" href="/index.php?option=com_content&amp;view=article&amp;id=10&amp;type='.$type.'&amp;ou='.$ou.'&amp;year='.$year.'#'.$row['doc_id'].'">'.$view_name.' №'.$row['doc_number'].' от '.$row['doc_date']."</a>";
						if ($admin_mode == true)
						{
							$content .= '&nbsp;<a onclick="DeleteElementByID(\''.$row['doc_id'].'\')" href="#" class="DeleteOrder">удалить</a>';
						}
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
		}
	}
	mysqli_close($link);
	$module_search_string = 'orders_search.php';
	include 'tree_content.php';
?>