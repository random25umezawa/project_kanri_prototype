<?php

function getTypeAndSize($type) {
	$ret = array();
	$temp_array = explode("(",$type);
	$ret['Type'] = $temp_array[0];
	if(count($temp_array)>1) {
		$ret['Size'] = explode(")",$temp_array[1])[0];
	}
	return $ret;
}


function db_changeDB($pdo,$pre_table_cols,$table_cols) {
	//henkou
	foreach($pre_table_cols as $table_name => $cols) {
		if(!array_key_exists($table_name,$table_cols)) {
			//drop table
			$pdo->query(sprintf('drop table %s',$table_name));
			print("delete table ".$table_name);
			continue;
		}
		foreach($cols["column"] as $col_name => $col) {
			print($col_name.PHP_EOL);
			if(!array_key_exists($col_name,$table_cols[$table_name]["column"])) {
				//delete column
				$pdo->query(sprintf('alter table %s drop %s',$table_name,$col_name));
				print("delete column ".$col_name);
				continue;
			}
			foreach($col as $type => $value) {
				//
			}
		}
	}
}

function db_addDB($pdo,$pre_table_cols,$table_cols) {
	//tuika
	foreach($table_cols as $table_name => $cols) {
		if(!array_key_exists($table_name,$pre_table_cols)) {
			//add table
			$col_arr = array();
			if(count($cols["column"])>0) {
				foreach($cols["column"] as $col_name => $col) {
					$col_str = $col_name;
					$col_type = $col["Type"];
					if(array_key_exists("Size",$col)) {
						$col_type .= "(".$col["Size"].")";
					}
					$col_arr[] = sprintf("%s %s",$col_str,$col_type);
				}
			}else {
				continue;
			}
			$query = sprintf('create table %s(%s)',$table_name,implode(",",$col_arr));
			print($query);
			$pdo->query($query);
			continue;
		}
		foreach($cols["column"] as $col_name => $col) {
			print($col_name.PHP_EOL);
			if(!array_key_exists($col_name,$pre_table_cols[$table_name]["column"])) {
				//add column
				$col_type = $col["Type"];
				if(array_key_exists("Size",$col)) {
					$col_type .= "(".$col["Size"].")";
				}
				$query = sprintf('alter table %s add %s %s',$table_name,$col_name,$col_type);
				print($query.PHP_EOL);
				$pdo->query($query);
				continue;
			}else {
				/*
				print("------------------");
				print($col_name);
				var_dump($col["Key"]);
				var_dump($pre_table_cols[$table_name]["column"][$col_name]["Key"]);
				*/
				if($col["Key"]=="PRI" && $pre_table_cols[$table_name]["column"][$col_name]["Key"]=="") {
					$query = sprintf('alter table %s add primary key (%s)',$table_name,$col_name);
					$pdo->query($query);
					print($col_name."<br>");
				}
				$col_type = $col["Type"];
				if(array_key_exists("Size",$col)) {
					$col_type .= "(".$col["Size"].")";
				}
				if($col["Extra"]=="auto_increment" && $pre_table_cols[$table_name]["column"][$col_name]["Extra"]=="") {
					$query = sprintf('alter table %s change %s %s %s not null auto_increment',$table_name,$col_name,$col_name,$col_type);
					$pdo->query($query);
				}
			}
			foreach($col as $type => $value) {
				//
			}
		}
	}
}
