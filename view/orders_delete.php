<?php
	include 'orders_config.php';
	if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !(strlen($_GET['id']) == 18))
	{
		mysqli_close($link);
		echo 'FALSE';
		exit();
	}
	$doc_id = $_GET['id'];
	$sql = 'DELETE FROM `sap_documents` WHERE `doc_id` = "'.$doc_id.'"';
	$result = mysqli_query($link, $sql) or die('FALSE');
	$old_path = getcwd(); // Save the current directory
    chdir('sap/');
    $mask = $doc_id.'*';
	array_map( 'unlink', glob( $mask ) );
    chdir($old_path); // Restore the old working directory 
	echo 'TRUE';
	mysqli_close($link);
?>