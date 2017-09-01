<?php
	require "db_connection.php";
	require "db_info.php";
	require "db_get.php";
	require "db_change.php";

	$json_file = "db_risou3.json";

	$design_data = getDBInfoFromFile($json_file);
	$completed_data = addedTableInfo($design_data);

	$class_id = $_REQUEST["class_id"];

	$tables = array();

	if(array_key_exists("columns",$_REQUEST)) {
		$columns = $_REQUEST["columns"];
		foreach($columns as $column_id => $value) {
			$column_name = $completed_data["columns"][$column_id]["col_name"];
			if($completed_data["columns"][$column_id]["parent"] == $class_id) {
				$table_name = $completed_data["columns"][$column_id]["table_name"];
				if(!array_key_exists($table_name,$tables)) $tables[$table_name] = array();
				$tables[$table_name][$column_name] = $value;
			}
		}
	}else {
		$_REQUEST["columns"] = array();
	}
	if(array_key_exists("classes",$_REQUEST)) {
		$classes = $_REQUEST["classes"];
		foreach($classes as $class_id => $value) {
			$column_name = $completed_data["classes"][$class_id]["key_name"];
			if($completed_data["classes"][$class_id]["parent"] == $class_id) {
				$table_name = $completed_data["columns"][$column_id]["table_name"];
				if(!array_key_exists($table_name,$tables)) $tables[$table_name] = array();
				$tables[$table_name][$column_name] = $value;
			}
		}
	}

	$pdo = db_connect();
	foreach($tables as $table_name => $table) {
		$column_names = [];
		$values = [];
		foreach($table as $column_name => $value) {
			$column_names[] = $column_name;
			$values[] = "'".$value."'";
		}
		$sql = sprintf("INSERT INTO %s (%s) VALUES (%s)", $table_name, implode(",",$column_names), implode(",",$values));
		print($sql);
		$pdo->query($sql);
	}

	echo json_encode($_REQUEST);
?>
