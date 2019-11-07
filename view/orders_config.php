<?php
	$module_name = '«Приказы и распоряжения»';
	$dbhost = 'localhost';
	$admin_mode = false;
	$dbuser = 'root';
	$dbpass = '5Gjy$BUe';
	$base = 'sap2ism';
	$link = mysqli_init();
	mysqli_options($link, MYSQLI_INIT_COMMAND, "SET AUTOCOMMIT=1");
	mysqli_options($link, MYSQLI_OPT_CONNECT_TIMEOUT, 5);
	mysqli_real_connect($link, $dbhost, $dbuser, $dbpass, $base);
	if (mysqli_connect_errno())
	{
		echo 'Connect failed: <br>'.mysqli_connect_error();
		exit();
	};
	mysqli_set_charset($link, 'utf8');
?>