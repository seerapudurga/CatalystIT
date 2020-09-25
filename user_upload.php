<?php



/****    	Database Functions 		  *****/

//Database Connection .

function OpenCon($db_username,$db_password,$db_host)
 {
	$mysqli = mysqli_init();  //initialising MySQLi
	if (!$mysqli) {
		die('mysqli_init failed');
	}
	if (!$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5)) {
		die('Setting MYSQLI_OPT_CONNECT_TIMEOUT failed');
	}
	if (!$mysqli->real_connect($db_host, $db_username, $db_password)) {  //if the connection fails returning error
		die('Connect Error (' . mysqli_connect_errno() . ') '
			. mysqli_connect_error());
	}else
		return $mysqli;
 }
 
 
 //Checking if Database Exists. If not Creating a new DB called Users .
 function selectDB($conn){
	$db_selected = $conn->select_db('catalyst_user');
	if (!$db_selected) {
	  $sql = 'CREATE DATABASE catalyst_user';
	
	  if ($conn->query($sql)) {
		  echo "Database created successfully\n";
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
		$sql = "CREATE TABLE IF NOT EXISTS `users` (
			id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			name VARCHAR(30) NOT NULL,
			surname VARCHAR(30) NOT NULL,
			email VARCHAR(50),
			reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
		)";

		if ($conn->query($sql)) {
		  echo "Table created successfully\n";
	  } else if ($conn->error) {
		  echo "Error creating table: " . $conn->error;
		}
		return $conn;
	}else {
		  echo 'Error Connecting to database: ' . $conn->connect_error . "\n";
	  }

}


function insertData($conn, $records){

	$dbrecord = [];
	$i=0;
	foreach($records as $record){
		$name = $conn->real_escape_string($record['name']);
		$surname = $conn->real_escape_string($record['surname']);
		$sql = "INSERT INTO users (name, surname, email)
		VALUES ('$name', '$surname', '$record[email]')";

		if ($conn->query($sql) === TRUE) {
			$dbrecord[$i++] = $record['name'].', '.$record['surname'].','.$record['email'];
		  continue;
		} else {
		  echo "Error: " . $sql . "<br>" . $conn->error;
		}
		
	}
	return $dbrecord;
}

function dataChecking($data){

	$valid_records = [];
	$emailErr = false;
	foreach($data as $record){
			
		$record['name'] = ucfirst(strtolower(trim($record['name'])));
		$record['surname'] = ucfirst(strtolower(trim($record['surname'])));
		$record['email'] = strtolower(trim($record['email']));
		if ((!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $record['email']))) {
			$emailErr = "Invalid email format:  ".$record['email'];
			break;
		}else{
			$valid_records[] = $record;
		}
	}
	return array('valid_records'=>$valid_records,'error'=>$emailErr);
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
				  
			
		}
		return array('error'=>$e->getMessage());

}


function helpPrint()
{

	echo "\n"."--file [csv filename] \t Name of the CSV to be parsed";
	echo "\n"."--create_table \t\t Creates MySql table";
	echo "\n"."--dry_run \t\t Run with --file directive (--dry_run --file [csv filename])";
	echo "\n"."-u \t\t\t This MySQL username";
	echo "\n"."-p \t\t\t MySQL password";
	echo "\n"."-h \t\t\t MySQL host";
	echo "\n"."--help \t\t\t help";

}
function filedirective($filename){
	$result_arr = csvToArray($filename);
	
		
		if(count($result_arr['valid_records'])>0){
			$conn = create_table();
			if($conn->error == ""){
				$data = insertData($conn, $result_arr['valid_records']);
				if(!empty($data)){
					echo '--------------------------------';
					echo "\nRecords Inserted in Database:";
					echo "\n";
					print_r(implode("\n",$data));
				}
				
			}else{
				echo "Error: ".$conn->error;
			}
		} 

		if(array_key_exists('error', $result_arr)){
			echo "\n\nError Occurred: ".$result_arr['error'];
		}
		
}

function dryRun($filename){
	$result_arr = csvToArray($filename);
		if(array_key_exists('error', $result_arr)){
			echo "\nError Occurred: ".$result_arr['error'];
		} else{
			echo "Records are validated. No errors found";
		}
	
}

/**********		Execution Starts from here		***********/

	switch ($argv[1]) {
	
	  case "--file":
		filedirective($argv[2]);
		break;
		
	  case "--create_table":
		$conn = create_table();
		if($conn->error == ""){
			echo 'Command Executed Successfully';
		}
		break;
		
	  case "--dry_run":
		dryRun($argv[3]);
		break;
		
	  case "--help":
		helpPrint();
		break;
		
	  default:
		helpPrint();
	}



?>
