<?php

function db_connect() {
	try{
		$dsn = 'mysql:dbname=test;host=localhost;charset=utf8mb4';
		$username = 'root';
		$password = 'admin';
		$driver_options = [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_EMULATE_PREPARES => false
		];

		$pdo = new PDO($dsn, $username, $password, $driver_options);
		return $pdo;

	}catch(PDOException $e) {
		print("error: ".$e);
		exit;
	}
}
