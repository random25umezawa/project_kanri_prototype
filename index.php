<?php
	require "db_connection.php";
	require "db_info.php";
	require "db_get.php";
	require "db_change.php";

	$html = "";

	$html .= <<<END_OF_HTML
	<ul>
		<li><a href="./">nofunc</a></li>
		<li><a href="./?seikei=1">seikei</a></li>
		<li><a href="./?dbinfo=1">dbinfo</a></li>
		<li><a href="./?dbnowstruct=1">dbnowstruct</a></li>
	</ul>
END_OF_HTML;

	if(array_key_exists("seikei",$_REQUEST)) {
		$design_data = getDBInfoFromFile("db_risou.json");

		$completed_data = addedTableInfo($design_data);

		$html .= "<pre>".print_r($completed_data,true)."</pre>";
	}else if(array_key_exists("dbinfo",$_REQUEST)) {
		$design_data = getDBInfoFromFile("db_risou.json");

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
	}

	echo $html;
/*
	$pdo = db_connect();

	$table_names = db_getTableNames($pdo);

	$before_table_info = array();
*/
	//file_put_contents("db.json", json_encode($table_cols,JSON_PRETTY_PRINT));
?>
