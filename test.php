<?php 
	$input_line = '01H9001234123';
	$input_line = '01H9001234';
	// $input_line = '37H20150523';
	preg_match("/^([0-9]{2}H[0-9]{7,8})$/", $input_line, $output_array);
	var_dump(count($output_array));
?>