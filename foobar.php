<?php 

$output = array();
$y = 0;
echo("\n");
for($i=1;$i<=100;$i++){
	if($i%15 == 0){
		//print_r("foobar");
		$y = "foobar";
	}else if($i%5 == 0){
		//print_r("bar");
		$y = "bar";
	}
	else if($i%3 == 0){
		//print_r("foo");
		$y = "foo";
	}else{
		//print_r($i);
		$y = $i;
	}	
	echo(' ');
	array_push($output, $y);
	//$output[] = $i;
}
print_r($output);
echo("\n");

?>