<?php
	require "db_connection.php";
	require "db_info.php";
	require "db_get.php";
	require "db_change.php";

	$html = "";

	$html .= <<<END_OF_HTML
	<ul>
		<li><a href="./">nofunc</a></li>
		<li><a href="./add.php?seikei=1">seikei</a></li>
		<li><a href="./?dbinfo=1">dbinfo</a></li>
		<li><a href="./?dbnowstruct=1">dbnowstruct</a></li>
		<li><a href="./?dbchange=1">dbchange</a></li>
		<li><a href="./?dbget=1">dbget</a></li>
		<li><a href="./?dbadd=1">dbadd</a></li>
	</ul>
END_OF_HTML;

	$json_file = "db_risou3.json";

	if(array_key_exists("seikei",$_REQUEST)) {
		$design_data = getDBInfoFromFile($json_file);

		$completed_data = addedTableInfo($design_data);

		$parts_table_rows = "";
		$parts_table_rows .= <<<PARTS
			<tr>
				<td>id</td>
				<td>name</td>
				<td>vartype</td>
				<td>size</td>
				<td>db_name</td>
				<td>col_name</td>
				<td>parent</td>
			</tr>
PARTS;
		foreach($completed_data["columns"] as $column_id => $column) {
			if(!array_key_exists("size",$column)) $column["size"] = "";
			$parts_table_rows .= <<<PARTS
				<tr>
					<td>{$column["id"]}</td>
					<td>{$column["name"]}</td>
					<td>{$column["vartype"]}</td>
					<td>{$column["size"]}</td>
					<td>{$column["db_name"]}</td>
					<td>{$column["col_name"]}</td>
					<td>{$column["parent"]}({$completed_data["classes"][$column["parent"]]["name"]})</td>
				</tr>
PARTS;
		}

		$html .= "<table border=1>".$parts_table_rows."<table>";

		$parts_table_rows = "";
		$parts_table_rows .= <<<PARTS
			<tr>
				<td>id</td>
				<td>name</td>
				<td>column_groups</td>
				<td>classes</td>
				<td>relation</td>
				<td>key_name</td>
			</tr>
PARTS;
		foreach($completed_data["classes"] as $column_id => $column) {
			$print_r = "print_r";
			$parts_table_rows .= <<<PARTS
				<tr>
					<td>{$column["id"]}</td>
					<td>{$column["name"]}</td>
					<td>{$print_r($column["column_groups"],true)}</td>
					<td>{$print_r($column["classes"],true)}</td>
					<td>{$print_r($column["relation"],true)}</td>
					<td>{$column["key_name"]}</td>
				</tr>
PARTS;
		}

		$html .= "<table border=1>".$parts_table_rows."<table>";
		$html .= "<pre>".print_r($completed_data,true)."</pre>";
	}else if(array_key_exists("dbinfo",$_REQUEST)) {
		$design_data = getDBInfoFromFile($json_file);

		$completed_data = addedTableInfo($design_data);

		$db_data = makeCompletedTableInfo($completed_data);

		$html .= "<pre style='column-count:1;column-gap:25px;'>";
		$html .= print_r($db_data,true);
		$html .= "</pre>";
	}else if(array_key_exists("dbnowstruct",$_REQUEST)) {
		$pdo = db_connect();

		$db_now_struct = db_getTableColumns($pdo,db_getTableNames($pdo));

		$html .= "<pre style='column-count:1;column-gap:25px;'>";
		$html .= print_r($db_now_struct,true);
		$html .= "</pre>";
	}else if(array_key_exists("dbchange",$_REQUEST)) {
		$pdo = db_connect();

		$db_now_struct = db_getTableColumns($pdo,db_getTableNames($pdo));

		$design_data = getDBInfoFromFile($json_file);

		$completed_data = addedTableInfo($design_data);

		$db_data = makeCompletedTableInfo($completed_data);

		db_changeDB($pdo,$db_now_struct,$db_data);
		db_addDB($pdo,$db_now_struct,$db_data);

		$html .= "<pre style='column-count:1;column-gap:25px;'>";
		$html .= print_r($db_now_struct,true);
		$html .= "</pre>";
	}else if(array_key_exists("dbget",$_REQUEST)) {
		$design_data = getDBInfoFromFile($json_file);

		$completed_data = addedTableInfo($design_data);

		$pdo = db_connect();

		$datas = array();
		/*
		$datas[] = array(
			"columns" => array(
				1,2
			),
			"limit" => 10
		);
		$datas[] = array(
			"columns" => array(
				1,3,4
			),
			"limit" => 10
		);
		*/
		foreach($datas as $req) {
			$result = db_getDataByJson($pdo,$completed_data,$req);

			$html .= "---------------data---------------<br><pre style='column-count:1;column-gap:25px;'>";
			$html .= print_r($req,true);
			$html .= "</pre>";
			$html .= "<br>---------------result---------------<br><pre style='column-count:1;column-gap:25px;'>";
			$html .= print_r($result,true);
			$html .= "</pre>";
		}
	}else if(array_key_exists("dbadd",$_REQUEST)) {
		$design_data = getDBInfoFromFile($json_file);

		$completed_data = addedTableInfo($design_data);

		$pdo = db_connect();

		$datas = array();
		/*
		$datas[] = array(
			"class" => 0,
			"values" => array(
				0 => 25,
				1 => "noname",
				2 => 125
			)
		);
		$datas[] = array(
			"class" => 1,
			"values" => array(
				3 => "noitem"
			)
		);
		$datas[] = array(
			"class" => 2,
			"values" => array(
				4 => 2
			),
			"classes" => array(
				0 => 1,
				1 => 1
			)
		);
		*/
		foreach($datas as $req) {
			$result = db_addDataWithClass($pdo,$completed_data,$req);

			$html .= "---------------data---------------<br><pre style='column-count:1;column-gap:25px;'>";
			$html .= print_r($req,true);
			$html .= "</pre>";
			$html .= "<br>---------------result---------------<br><pre style='column-count:1;column-gap:25px;'>";
			$html .= print_r($result,true);
			$html .= "</pre>";
		}
	}

	echo $html;
/*
	$pdo = db_connect();

	$table_names = db_getTableNames($pdo);

	$before_table_info = array();
*/
	//file_put_contents("db.json", json_encode($table_cols,JSON_PRETTY_PRINT));
?>
