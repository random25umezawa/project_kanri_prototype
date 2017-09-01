<?php
//
function getDBInfoFromFile($filename) {
	$table_cols = json_decode(file_get_contents($filename),true);
	return $table_cols;
}

//親子情報やキーなどの必要な情報を付加する
function addedTableInfo($design_data) {
	//カラム情報のidをキーとする
	$columns = array();
	foreach($design_data["columns"] as $column_data) {
		$columns[$column_data["id"]] = $column_data;
	}

	//クラス情報のidをキーとする
	$classes = array();
	foreach($design_data["classes"] as $class_data) {
		$class_id = $class_data["id"];
		$classes[$class_id] = $class_data;
		$classes[$class_id]["classes"] = array();
		$classes[$class_id]["relation"] = array();
		if(!array_key_exists("classes",$class_data)) $class_data["classes"] = array();
		if(!array_key_exists("relation",$class_data)) $class_data["relation"] = array();
		if(!array_key_exists("column_group",$classes[$class_id])) $classes[$class_id]["column_group"] = array();

		//複合クラスの親に子情報を追加して,クラス名を複合名にする
		foreach($class_data["relation"] as $related_class_id) {
			$classes[$class_id]["relation"][$related_class_id] = $related_class_id;
		}
		foreach($class_data["classes"] as $parent_class_id) {
			$classes[$class_id]["classes"][$parent_class_id] = $parent_class_id;
			$classes[$class_id]["relation"][$parent_class_id] = $parent_class_id;
		}
	}
	foreach($classes as $class_id => $val) {
		if($classes[$class_id]["classes"]) $classes[$class_id]["name"] = getMixedClassName($classes,$classes[$class_id]["classes"]);
		$classes[$class_id]["key_name"] = $classes[$class_id]["name"]."_id";
	}

		//カラムの親情報とカラム名を補完する
	foreach($classes as $class_id => $val) {
		$column_group = $classes[$class_id]["column_group"];
		$table_name = getDBNameFromColumnGroups($classes[$class_id]["name"],$columns,$column_group);
		foreach($column_group as $column_id) {
			$columns[$column_id]["table_name"] = $table_name;
			$columns[$column_id]["col_name"] = $classes[$class_id]["name"]."_".$columns[$column_id]["name"];
			$columns[$column_id]["parent"] = $class_id;
		}
		$classes[$class_id]["table_name"] = $table_name;
	}

	$completed_data = array();
	$completed_data["columns"] = $columns;
	$completed_data["classes"] = $classes;

	return $completed_data;
}

//実際に作るテーブル情報(補完されたDB名やカラム名付き)を作る
function makeCompletedTableInfo($completed_data) {
	$db_data = array();
	$classes = $completed_data["classes"];
	$columns = $completed_data["columns"];
	//各クラス
	foreach($classes as $class) {
		//各カラムグループ
		$column_group = $class["column_group"];

		$table_name = getDBNameFromColumnGroups($class["name"],$columns,$column_group);
		$db_data[$table_name] = array();
		$db_data[$table_name]["column"] = array();
		$db_data[$table_name]["unique"] = array();

		$db_columns = array();
		$db_uniques = array();

		//キー値
		if($class["classes"]) {
			//複数キー
			$unique = array();
			foreach($class["classes"] as $class_id) {
				$db_column = array(
					"col_name" => $classes[$class_id]["key_name"],
					"vartype" => "int",
				);
				$unique[] = $db_column["col_name"];
				$db_columns[] = $db_column;
			}
			$db_uniques[] = $unique;
		}else {
			//キー１つ
			$db_column = array(
				"col_name" => $class["key_name"],
				"vartype" => "int",
				"extra" => "auto_increment",
				"key" => "PRI"
			);
			if(array_key_exists("ai",$class)) {
				$db_column["key"] = "";
				$db_uniques[] = array($db_column["col_name"]);
			}
			$db_columns[] = $db_column;
		}

		foreach($column_group as $column_id) {
			$db_columns[] = $columns[$column_id];
		}
		//各カラムグループ内各カラム
		foreach($db_columns as $column) {
			$db_column = array();

			$db_column["Type"] = $column["vartype"];
			$db_column["Field"] = $column["col_name"];
			$db_column["Null"] = "NO";
			$db_column["Key"] = "";
			$db_column["Default"] = null;
			$db_column["Extra"] = "";
			$db_column["Size"] = 0;
			if(array_key_exists("size",$column)) $db_column["Size"] = $column["size"];
			if(array_key_exists("default",$column)) $db_column["Default"] = $column["default"];
			if(array_key_exists("extra",$column)) $db_column["Extra"] = $column["extra"];
			if(array_key_exists("key",$column)) $db_column["Key"] = $column["key"];

			$db_data[$table_name]["column"][$column["col_name"]] = $db_column;
		}
		$db_data[$table_name]["unique"] = $db_uniques;
	}
	return $db_data;
}

function getMixedClassName($classes,$class_ids) {
	$class_names = array();
	foreach($class_ids as $class_id => $flag) {
		$class_names[$class_id] = $classes[$class_id]["name"];
	}
	return implode("_",$class_names);
}

function getDBNameFromColumnGroups($classname, $columns, $column_group) {
	$table_name = $classname;
	foreach($column_group as $column_group_id) {
		$table_name .= "_".$columns[$column_group_id]["name"];
	}
	return $table_name;
}
