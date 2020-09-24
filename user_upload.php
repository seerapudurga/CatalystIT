<?php

function dataChecking($data){

	foreach($data as $record){
		
		print_r(ucfirst(strtolower($record['name'])));
		print_r(ucfirst(strtolower($record['surname'])));
		print_r(strtolower($record['email']));	
		if (!filter_var($record['email'], FILTER_VALIDATE_EMAIL)) {
			$emailErr = "Invalid email format";
			break;
		}
	}
		

}

function csvToArray($filename){

	$array = $fields = array(); $i = 0;

		try {
			$handle = fopen($filename, "r");
			if ($handle) {
				while (($row = fgetcsv($handle, 4096)) !== false) {
					if (empty($fields)) {
						$fields = $row;
						$fields = array_map('trim', $fields);
						continue;
					}
					foreach ($row as $k=>$value) {
						$array[$i][$fields[$k]] = $value;
					}
					$i++;
				}
				if (!feof($handle)) {
					echo "Error: unexpected fgets() fail\n";
				}
				//print_r($array);
				return $array;
				fclose($handle);
			}else 
				throw new Exception("Could not open the file!");
		}
		catch (Exception $e) {
			echo "Error (File: ".$e->getFile().", line ".
				  $e->getLine()."): ".$e->getMessage();
		}

}

function OpenCon($db_username,$db_password,$db_host)
 {
	$mysqli = mysqli_init();
	if (!$mysqli) {
		die('mysqli_init failed');
	}
	if (!$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5)) {
		die('Setting MYSQLI_OPT_CONNECT_TIMEOUT failed');
	}
	if (!$mysqli->real_connect($db_host, $db_username, $db_password)) {
		die('Connect Error (' . mysqli_connect_errno() . ') '
			. mysqli_connect_error());
	}else
		return $mysqli;
 }
 
 function selectDB($conn){
	$db_selected = $conn->select_db('my_db');
	if (!$db_selected) {
	  $sql = 'CREATE DATABASE my_db';
	
	  if ($conn->query($sql)) {
		  echo "Database my_db created successfully\n";
	  } else {
		  echo 'Error creating database: ' . $conn -> error . "\n";
	  }
	}
	return $conn;
}
function create_table(){
	echo "Database Username:";
	$db_username = rtrim(fgets(STDIN));
	echo "Database Password:";
	$db_password = rtrim(fgets(STDIN));
	echo "Database Hostname:";
	$db_host = rtrim(fgets(STDIN));
	$conn = OpenCon($db_username,$db_password,$db_host);
	
	
	if(empty($conn->connect_error)){
		$conn = selectDB($conn);
		$sql = "CREATE TABLE MyGuestu (
			id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			firstname VARCHAR(30) NOT NULL,
			lastname VARCHAR(30) NOT NULL,
			email VARCHAR(50),
			reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
		)";

		if ($conn->query($sql)) {
		  echo "Table MyGuests created successfully";
		} else {
		  echo "Error creating table: " . $conn->error;
		}
	}else {
		  echo 'Error Connecting to database: ' . $conn->connect_error . "\n";
	  }

}



unset($argv[0]);

var_dump($argv);
$connection = [];
	switch ($argv[1]) {
	  case "--file":
		//echo "--file";
		csvToArray($argv[2]);
		break;
	  case "--create_table":
		//echo "--create_table";
		create_table();
		break;
	  case "--dry_run":
		echo "--dry_run";
		break;
	  case "-u":
		echo "--u";
		break;
	  case "--help":
		echo "--help";
		break;
	  default:
		echo "--default";
	}

?>
