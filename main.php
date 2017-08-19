<?php
	require "db_connection.php";
	require "db_info.php";
	require "db_get.php";
	require "db_change.php";

	$design_data = getDBInfoFromFile("db_risou.json");

	$completed_data = addedTableInfo($design_data);

	$db_data = makeCompletedTableInfo($completed_data);

	print_r($db_data);
/*
	$pdo = db_connect();

	$table_names = db_getTableNames($pdo);

	$before_table_info = array();
*/
	//file_put_contents("db.json", json_encode($table_cols,JSON_PRETTY_PRINT));
