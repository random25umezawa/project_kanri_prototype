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
			//printf("%s\n",mb_convert_encoding(print_r($row,true),'SJIS'));
		}
		/*
		$stmt = $pdo->prepare(sprintf('show create table %s',$table_name));
		$stmt->execute();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			printf("%s\n",mb_convert_encoding(print_r($row,true),'SJIS'));
		}
		*/
	}
	return $pre_table_cols;
}
