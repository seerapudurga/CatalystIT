<?php

	function parseFileCsv(){
		$csv = array_map('str_getcsv', file('users.csv'));
		print_r($csv);
	}
	
	parseFileCsv();


?>