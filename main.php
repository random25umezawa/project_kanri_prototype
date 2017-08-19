<?php
	require "db_connection.php";
	require "db_info.php";
	require "db_get.php";
	require "db_change.php";

	$table_info = getDBInfoFromFile("db_risou.json");

	$after_table_info = addedTableInfo($table_info);

	print_r($after_table_info);
/*
	$pdo = db_connect();

	$table_names = db_getTableNames($pdo);

	$before_table_info = array();
*/
	//file_put_contents("db.json", json_encode($table_cols,JSON_PRETTY_PRINT));
