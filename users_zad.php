<?php
	header('Content-type: application/json');
	header('Cache-Control: no-cache, must-revalidate');
	session_start();
	require_once ('../conf.php');
//	error_reporting(0);
	$mysqli = new_polacz_z_baza();
	
	$query = "SELECT kart_perm,dzial,sekcja FROM users WHERE id = ".$_SESSION["myuser"]["id"].";";
	$result = $mysqli->query($query);
	$perm = 0;
	$dzial = "";
	if ($row = $result->fetch_assoc()) {
		$perm = $row["kart_perm"];
		$dzial = $row["dzial"];
		$sekcja = $row["sekcja"];
	}
	
	
	// if ($perm == "1") {
		// if ($sekcja)
			// $query = "SELECT id,nazwa,dzial,sekcja,kart_perm FROM users WHERE (dzial LIKE '".$dzial."%' AND sekcja LIKE '".$sekcja."%' AND (kart_perm <= ".$perm." OR id=1 OR id=40 OR id = ".$_SESSION["myuser"]["id"]."))";
		// else
			// $query = "SELECT id,nazwa,dzial,kart_perm FROM users WHERE (dzial LIKE '".$dzial."%' AND (kart_perm <= ".$perm." OR id=1 OR id=40 OR id = ".$_SESSION["myuser"]["id"]."))";
		// $query .= " OR (kart_perm >= ".$perm." AND id<>1)";
	// } else if ($perm == "2")
		// $query = "SELECT id,nazwa,dzial,kart_perm FROM users WHERE kart_perm <= ".$perm." OR id=1 OR id=40 OR id = ".$_SESSION["myuser"]["id"]."";
	// else if ($perm == "3")
	$query = "SELECT id,nr,nazwa,dzial,sekcja,kart_perm FROM users WHERE 1";
	// $query = "SELECT id,nr,nazwa,dzial,sekcja,kart_perm FROM users WHERE dzial NOT LIKE '?%' AND dzial NOT LIKE 'TT%'";
	// else
		// $query = "SELECT id,nazwa,dzial FROM users WHERE id=".$_SESSION["myuser"]["id"];
	$query .= " AND dzial not like 'TT%' ";
	$query .= " AND dzial not like '?%' ORDER BY nazwa;";
	$result = $mysqli->query($query);
	$users = array();
	while($row = $result->fetch_assoc()) $users[] = $row;
	echo 'var users = '.json_encode($users).';';
	echo '_user_kart_perm = '.$perm.';';
	echo '_user_dzial = "'.$dzial.'";';
	echo '_user_sekcja = "'.$sekcja.'";';
?>
