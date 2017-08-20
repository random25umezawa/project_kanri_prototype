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
		if(!array_key_exists("classes",$classes[$class_id])) $classes[$class_id]["classes"] = array();
		$classes[$class_id]["children"] = array();

		//複合クラスの親に子情報を追加して,クラス名を複合名にする
		foreach($classes[$class_id]["classes"] as $parent_class_id) {
			$classes[$parent_class_id]["children"][$class_id] = $class_id;
		}
		if($classes[$class_id]["classes"]) $classes[$class_id]["name"] = getMixedClassName($classes,$classes[$class_id]["classes"]);
		$classes[$class_id]["key_name"] = $classes[$class_id]["name"]."_id";

		//カラムの親情報とカラム名を補完する
		foreach($classes[$class_id]["column_groups"] as $column_group) {
			$db_name = getDBNameFromColumnGroups($classes[$class_id]["name"],$columns,$column_group);
			foreach($column_group as $column_id) {
				$columns[$column_id]["db_name"] = $db_name;
				$columns[$column_id]["col_name"] = $classes[$class_id]["name"]."_".$columns[$column_id]["name"];
			}
		}
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
		foreach($class["column_groups"] as $column_group) {
			$db_name = getDBNameFromColumnGroups($class["name"],$columns,$column_group);
			$db_data[$db_name] = array();
			$db_data[$db_name]["column"] = array();

			$db_columns = array();

			//キー値
			if($class["classes"]) {
				//複数キー
				foreach($class["classes"] as $class_id) {
					$db_columns[] = array(
						"col_name" => $classes[$class_id]["key_name"],
						"vartype" => "int",
					);
				}
			}else {
				//キー１つ
				$db_columns[] = array(
					"col_name" => $class["key_name"],
					"vartype" => "int",
					"extra" => "auto_increment",
					"key" => "PRI"
				);
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

				$db_data[$db_name]["column"][$column["col_name"]] = $db_column;
			}
		}
	}
	return $db_data;
}

function getMixedClassName($classes,$class_ids) {
	$class_names = array();
	foreach($class_ids as $class_id) {
		$class_names[$class_id] = $classes[$class_id]["name"];
	}
	return implode("_",$class_names);
}

function getDBNameFromColumnGroups($classname, $columns, $column_group) {
	$db_name = $classname;
	foreach($column_group as $column_group_id) {
		$db_name .= "_".$columns[$column_group_id]["name"];
	}
	return $db_name;
}

function getSQLInfo($completed_data, $column_ids) {

}
