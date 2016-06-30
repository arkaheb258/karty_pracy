<?php
	require_once ('conf.php');
//	error_reporting(0);
	$mysqli = new_polacz_z_baza();

	$date = new DateTime();
	$swieta = array();
	$query = "SELECT * FROM kart_pr_swieta;";
	if (isset($_REQUEST["year"])){
		$query = "SELECT * FROM kart_pr_swieta WHERE rok = ".$_REQUEST["year"]." OR rok IS NULL;";
	} else {
		$query = "SELECT * FROM kart_pr_swieta WHERE rok = ".$date->format('Y')." OR rok IS NULL;";
	}
	$result = $mysqli->query($query);
	
	if ($result)
	while($row = $result->fetch_assoc()){
		if (!isset($swieta[$row["miesiac"]]))
			$swieta[$row["miesiac"]] = array();
		$swieta[$row["miesiac"]][$row["dzien"]] = $row["opis"];
	}
	echo 'var swieta = '.json_encode($swieta).';';
	$mysqli->close();
?>