<?php
	include 'orders_config.php';
	if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !(strlen($_GET['id']) == 20))
	{
		mysqli_close($link);
		exit();
	}
	$doc_id = substr($_GET['id'], 0, 18);
	$file_id = substr($_GET['id'], 18, 2);
	$sql = 'SELECT * FROM `sap_filelist` WHERE `doc_id` = "'.$doc_id.'" AND `file_id` = "'.$file_id.'" LIMIT 1';
	$result = mysqli_query($link, $sql) or die('Ошибка запроса '.$sql);
	if (mysqli_num_rows($result) == 0)
	{
		mysqli_close($link);
		exit();
	}
	$file = mysqli_fetch_assoc($result);
	$sql = 'UPDATE `sap_filelist` SET `file_dcount` = (`file_dcount` + 1) WHERE (`doc_id` = "'.$doc_id.'" AND `file_id` = "'.$file_id.'")';
	$result = mysqli_query($link, $sql) or die('Ошибка запроса '.$sql);
	$file_path = 'F:\\SAP\\'.$doc_id.$file_id.'.'.$file['file_extension'];
	$file_name = $file['file_description'].'.'.$file['file_extension'];
	if (!file_exists($file_path))
	{
		mysqli_close($link);
		exit();
	}
	$file_size = filesize($file_path);
	mysqli_close($link);
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Content-type: application/octet-stream');
	header('Content-length: '.$file_size);
	header('Content-Disposition: attachment; filename='.mb_convert_encoding(str_replace(array('"', "'", ' ', ',', '>', '<', ':', '/', '\\', '|', '?', '*'), '_', $file_name), 'Windows-1251', "UTF-8"));
	header('Content-transfer-encoding: binary');
	header('Connection: close');
	$file_content = file_get_contents($file_path);
	echo $file_content;
?>