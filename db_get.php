<?php

function db_getTableNames($pdo) {
	$tables = array();

	$stmt = $pdo->prepare('show tables');
	$stmt->execute();
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$tables[] = $row['Tables_in_test'];
	}
	return $tables;
}

function db_getTableColumns($pdo, $tables) {
	$pre_table_cols = array();

	foreach($tables as $table_name) {
		$pre_table_cols[$table_name] = array();
		$pre_table_cols[$table_name]["column"] = array();
		$stmt = $pdo->prepare(sprintf('desc %s',$table_name));
		$stmt->execute();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$temp_types = getTypeAndSize($row['Type']);
			$temp_types += $row;
			$pre_table_cols[$table_name]["column"][$row['Field']] = $temp_types;
		}
	}
	return $pre_table_cols;
}

function db_getDataByJson($pdo,$completed_data,$req) {
	$sql_info = array();
	$sql_info["limit"] = $req["limit"];
	$sql_info["tables"] = array();
	$sql_info["classes"] = array();
	$parent_graph = array();
	foreach($req["columns"] as $column_id) {
		$column = $completed_data["columns"][$column_id];
		$table_name = $completed_data["columns"][$column_id]["db_name"];
		//取り出すカラム名を列挙
		if(!array_key_exists($table_name,$sql_info["tables"])) $sql_info["tables"][$table_name] = array();
		$sql_info["tables"][$table_name][$column_id] = $column["col_name"];

		//クラスの親子関係の双方向グラフ
		$parent_id = $column["parent"];
		if(!array_key_exists($parent_id,$parent_graph))$parent_graph[$parent_id] = array("parent"=>array(),"children"=>array());
		foreach($completed_data["classes"][$parent_id]["classes"] as $class_id) {
			if(!array_key_exists($class_id,$parent_graph))$parent_graph[$class_id] = array("parent"=>array(),"children"=>array());
			$parent_graph[$class_id]["children"][$parent_id] = $parent_id;
			$parent_graph[$parent_id]["parent"][$class_id] = $class_id;
		}
		//子から順にチェック済みにしながらクエリ投げる
		//とりあえず親のid絞らない場合
		$checked = array();
		foreach($parent_graph as $class_id => $node) {
		}
	}
	$sql_info["classes"] = $parent_graph;
	return $sql_info;
}
