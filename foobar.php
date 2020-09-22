<?php 

$output = array();
echo("\n"); 				
for($i=1;$i<=100;$i++){

	if($i%15 == 0){        //Since both 3 and 5 are prime 3*5=15. So divisible by 3 and 5 are divisible by 15. 
		$y = "foobar";
	}
	else if($i%5 == 0){   //checking if divisible by 3.
		$y = "bar";
	}
	else if($i%3 == 0){    //checking if divisible by 3.
		$y = "foo";
	}else{					
		$y = $i;		
	}		
	
	array_push($output, $y); //Pushing the output into an array

}

	$comma_separated = implode(", ", $output);		//output as a string separated with comma 
	print_r($comma_separated);						
echo("\n");

?>
