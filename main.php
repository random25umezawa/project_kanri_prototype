<?php
	$table_cols = json_decode(file_get_contents("db.json"),true);

	//print_r($pre_tables);

try{
	$dsn = 'mysql:dbname=test;host=localhost;charset=utf8mb4';
	$username = 'root';
	$password = 'admin';
	$driver_options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_EMULATE_PREPARES => false
	];

	$pdo = new PDO($dsn, $username, $password, $driver_options);

	$tables = array();

	//$stmt = $pdo->prepare('select * from test');
	$stmt = $pdo->prepare('show tables');
	$stmt->execute();
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		//printf("%s\n",mb_convert_encoding(print_r($row,true),'SJIS'));
		$tables[] = $row['Tables_in_test'];
	}

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
			}
			foreach($col as $type => $value) {
				//
			}
		}
	}

	//print_r($table_cols);

	file_put_contents("db.json", json_encode($table_cols,JSON_PRETTY_PRINT));

}catch(PDOException $e) {
	print("error: ".$e);
}

	function getTypeAndSize($type) {
		$ret = array();
		$temp_array = explode("(",$type);
		$ret['Type'] = $temp_array[0];
		if(count($temp_array)>1) {
			$ret['Size'] = explode(")",$temp_array[1])[0];
		}
		return $ret;
	}
