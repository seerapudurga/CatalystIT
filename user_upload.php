<?php

function insertData($conn, $records){

	foreach($records as $record){
		$name = $conn->real_escape_string($record['name']);
		$surname = $conn->real_escape_string($record['surname']);
		$sql = "INSERT INTO guess (name, surname, email)
		VALUES ('$name', '$surname', '$record[email]')";

		if ($conn->query($sql) === TRUE) {
		  continue;
		} else {
		  echo "Error: " . $sql . "<br>" . $conn->error;
		}
	}
}

function dataChecking($data){

	$valid_records = [];
	$emailErr = false;
	foreach($data as $record){
			
		$record['name'] = ucfirst(strtolower(trim($record['name'])));
		$record['surname'] = ucfirst(strtolower(trim($record['surname'])));
		$record['email'] = strtolower(trim($record['email']));
		$x = $record['email'];
		if ((!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $x))) {
			$emailErr = "Invalid email format:  ".$record['email'];
			break;
		}else{
			$valid_records[] = $record;
		}
	}
	return array('valid_records'=>$valid_records,'emailErr'=>$emailErr);
	print_r($valid_records);
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
				$validation_result = dataChecking($array);
				return $validation_result;
				fclose($handle);
			}else 
				throw new Exception("Could not open the file!");
		}
		catch (Exception $e) {
			echo "Error (File: ".$e->getFile().", line ".
				  $e->getLine()."): \n".$e->getMessage();
				  
			return false;
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
		$sql = "CREATE TABLE IF NOT EXISTS `guess` (
			id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			name VARCHAR(30) NOT NULL,
			surname VARCHAR(30) NOT NULL,
			email VARCHAR(50),
			reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
		)";

		if ($conn->error) {
		  echo "Error creating table: " . $conn->error;
		}
		return $conn;
	}else {
		  echo 'Error Connecting to database: ' . $conn->connect_error . "\n";
	  }

}

function helpPrint()
{
	/*$help_data = Array(
		'--file' = > 'Name of the CSV to be parsed',
		'--create_table' = > 'Creates MySql table',
		'--dry_run' = > 'Run with --file directive to run the script but not insert into the DB',
		'--help' = > 'Help',
		'-u' = > 'MySql username',
		'-p' = > 'MySql password',
		'-h' = > 'MySql host'
		);
*/
		
	echo "\n"."--file [csv filename] \t Name of the CSV to be parsed";
	echo "\n"."--create_table \t\t Creates MySql table";
	echo "\n"."--dry_run \t\t Run with --file directive (--dry_run --file [csv filename])";
	echo "\n"."-u \t\t\t This MySQL username";
	echo "\n"."-p \t\t\t MySQL password";
	echo "\n"."-h \t\t\t MySQL host";
	echo "\n"."--help \t\t\t help";

}



unset($argv[0]);

$connection = [];
	switch ($argv[1]) {
	  case "--file":
		$result_arr = csvToArray($argv[2]);
		if($result_arr){
			$conn = create_table();
			if($conn->error == ""){
				$data = insertData($conn, $result_arr['valid_records']);
			}else{
				echo "Error: ".$conn->error;
			}
		}
		break;
	  case "--create_table":
		$conn = create_table();
		break;
	  case "--dry_run":
		$result_arr = csvToArray($argv[3]);
		if($result_arr['emailErr']){
			echo $result_arr['emailErr'];
		}else{
			echo "Records are validated. No errors found";
		}
		break;
	  case "-u":
		echo "--u";
		break;
	  case "--help":
		helpPrint();
		break;
	  default:
		helpPrint();
	}

?>
