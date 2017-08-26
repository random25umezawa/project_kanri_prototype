<?php
	require "db_connection.php";
	require "db_info.php";
	require "db_get.php";
	require "db_change.php";

	$html = "";

	$html .= <<<END_OF_HTML
	<ul>
		<li><a href="./?seikei=1">seikei</a></li>
		<li><a href="./?dbchange=1">dbchange</a></li>
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
				<td>default</td>
				<td>db_name</td>
				<td>col_name</td>
				<td>parent</td>
			</tr>
PARTS;
		foreach($completed_data["columns"] as $column_id => $column) {
			if(!array_key_exists("size",$column)) $column["size"] = "";
			if(!array_key_exists("default",$column)) $column["default"] = "";
			$parts_table_rows .= <<<PARTS
				<tr>
					<td>{$column["id"]}</td>
					<td>{$column["name"]}</td>
					<td>{$column["vartype"]}</td>
					<td>{$column["size"]}</td>
					<td>{$column["default"]}</td>
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
				<td>-</td>
			</tr>
PARTS;
		foreach($completed_data["classes"] as $column_id => $column) {
			$print_r = "json_encode";
			$parts_table_rows .= <<<PARTS
				<tr>
					<td>{$column["id"]}</td>
					<td>{$column["name"]}</td>
					<td>{$print_r($column["column_groups"],true)}</td>
					<td>{$print_r($column["classes"],true)}</td>
					<td>{$print_r($column["relation"],true)}</td>
					<td>{$column["key_name"]}</td>
					<td><button onclick="plus({$column["id"]})">plus</button></td>
				</tr>
PARTS;
		}

		$html .= "<table border=1>".$parts_table_rows."<table>";
		$html .= <<<JAVASCRIPT
		<script>
			var completed_data = {$print_r($completed_data,true)};
		</script>
JAVASCRIPT;
		//$html .= "<pre>".print_r($completed_data,true)."</pre>";
	}
	if(array_key_exists("dbadd",$_REQUEST)) {
		$design_data = getDBInfoFromFile($json_file);
		$completed_data = addedTableInfo($design_data);
		$pdo = db_connect();
		$datas = array();
		foreach($datas as $req) {
			$result = db_addDataWithClass($pdo,$completed_data,$req);
		}
	}

	echo $html;
?>

<script src="jquery.min.js"></script>

<div id="plusarea">
	<form id="plusform">
	</form>
	<button id="ajaxadd">ajaxadd</button>
</div>
<button id="test">test</button>

<script>
console.log(completed_data);
	pulldowns = {};
	var form = $("#plusform");
	function plus(class_id) {
		form.html("");
		form.append($(`<input type="hidden" name="baseclass" value="${class_id}">`));
		for(var parent_class_id in completed_data.classes[class_id].classes) {
			console.log("class",parent_class_id);
			form.append(`${completed_data.classes[class_id].name} : `);
			var pulldown = $(`<select name="class_${parent_class_id}">`);
			form.append(pulldown);
			fillPullDown(pulldown,parent_class_id);
		}
		for(var group_key in completed_data.classes[class_id].column_groups) {
			var column_group = completed_data.classes[class_id].column_groups[group_key];
			for(var column_id of column_group) {
				console.log("key",column_id);
				var column = completed_data["columns"][column_id];
				if(!column["default"]) {
					form.append(`${column.col_name} : <input type="text" name="column_${column.id}">`);
				}
			}
		}
	}
	$(document).ready(function() {
		$("#ajaxadd").on("click",function() {
			var form = $("#plusform");
			var data = {};
			var columns = {};
			var classes = {};
			for(var i = 0; i < form[0].elements.length; i++) {
				/*
				var _val = form[0].elements[i];
				data[_val.name]=_val.value;
				*/
				var _val = form[0].elements[i];
				if(_val.name.indexOf("baseclass")>=0) {
					data["class_id"]=_val.value;
				}else if(_val.name.indexOf("column_")>=0) {
					columns[_val.name.split("_")[1]] = _val.value;
				}else if(_val.name.indexOf("class_")>=0) {
					classes[_val.name.split("_")[1]] = _val.value;
				}
			}
			data["columns"] = columns;
			data["classes"] = classes;
			console.log(data);
			$.ajax({
				type:"POST",
				url:"ajax_add.php",
				dateType:"json",
				data:data,
				success: function(data,dataType) {
					console.log(data,dataType);
				},
				error: function(e) {
					console.log(e);
				}
			})
		});

		$("#test").on("click",function() {
			$.ajax({
				type:"POST",
				url:"ajax_pulldown.php",
				dateType:"json",
				data:{
					"class_id":0,
					"label_id":0
				},
				success: function(data,dataType) {
					console.log(data,dataType);
				},
				error: function(e) {
					console.log(e);
				}
			})
		});
	});

	function fillPullDown(pulldown,parent_class_id) {
		$.ajax({
			type:"POST",
			url:"ajax_pulldown.php",
			dateType:"json",
			data:{
				"class_id":parent_class_id
			},
			success: function(data,dataType) {
				console.log(data,dataType);
				data = JSON.parse(data);
				for(var key in data) {
					pulldown.append($("<option>").html(data[key]).val(key));
				}
			},
			error: function(e) {
				console.log(e);
			}
		})
	}
</script>
