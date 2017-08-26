<?php
	require "db_connection.php";
	require "db_info.php";
	require "db_get.php";
	require "db_change.php";

	$json_file = "db_risou3.json";

	$design_data = getDBInfoFromFile($json_file);
	$completed_data = addedTableInfo($design_data);

	$class_id = $_REQUEST["class_id"];
	$label_id = $completed_data["classes"][$class_id]["label"];

	$columns = array();
	$key_name = $completed_data["classes"][$class_id]["key_name"];
	$col_name = $completed_data["columns"][$label_id]["col_name"];
	$columns[] = $key_name;
	$columns[] = $col_name;
	$db_name = $completed_data["columns"][$label_id]["db_name"];

	$pdo = db_connect();

	$sql = sprintf("SELECT %s from %s", implode(",",$columns), $db_name);
	$stmt = $pdo->query($sql);
	$return_array = array();
	while($ret = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$return_array[$ret[$key_name]] = $ret[$col_name];
	}

	echo json_encode($return_array);
?>
