<?php
	header('Content-type: application/json');
	header('Cache-Control: no-cache, must-revalidate');
	session_start();
	if ( !isset( $_SESSION["myusername"] ) )
		exit('[]');
	require_once ('conf.php');
//	error_reporting(0);
	$mysqli = new_polacz_z_baza();
	
	$od = "1371420000";
	$do = null;
	$user_id = 0;
	
	if (isset($_REQUEST["_od"]))
		$od = $_REQUEST["_od"];
	if (isset($_REQUEST["_do"]))
		$do = $_REQUEST["_do"];
	
	$query = "SELECT kart_perm, dzial FROM users WHERE id = ".$_SESSION["myuser"]["id"].";";
	$result = $mysqli->query($query);
	$perm = 0;
	$dzial = "";
	if ($row = $result->fetch_assoc()) {
		$perm = $row["kart_perm"];
		$dzial = $row["dzial"];
		$user_id = $_SESSION["myuser"]["id"];
	}

	if (isset($_REQUEST["user_id"]))
		if ($_REQUEST["user_id"]){
			$query = "SELECT kart_perm, dzial FROM users WHERE id = ".$_REQUEST["user_id"].";";
			$result = $mysqli->query($query);
			if ($row = $result->fetch_assoc()){
				if ($perm > 2)
					$dzial = $row["dzial"];
				$user_id = $_REQUEST["user_id"];
				$perm = 0;
			}
		}

	$dzial_2 = '%';
	if (isset($_REQUEST["dzial"]))
		$dzial_2 = $_REQUEST["dzial"];
	
//	if ($perm == "3"){
//	$query_1 = "SELECT dzial, if (k.nazwa = 'L4' or k.nazwa = 'Urlop',concat(d.id,'LU'),d.id) as dla_id, d.nazwa as dla, sum(czas) as ile FROM kart_pr_prace_all2 p, kart_pr_kat k, kart_pr_dzial d, users u where k.id_dzial = d.id and u.id = p.user_id and p.kat_id = k.id";
	// $query_1 = "SELECT dzial, IF( k.nazwa =  'L4', CONCAT( d.id,  'L' ), IF( k.nazwa =  'Urlop', CONCAT( d.id,  'U' ), IF( k.nazwa =  'IFS', CONCAT( d.id,  'IFS' ),d.id ))) AS dla_id, d.nazwa as dla, sum(czas) as ile FROM kart_pr_prace_all2 p, kart_pr_kat k, kart_pr_dzial d, users u where k.id_dzial = d.id and u.id = p.user_id and p.kat_id = k.id";
	// $query_1 = "SELECT dzial, IF( k.nazwa =  'L4', CONCAT( d.id,  'L' ), IF( k.nazwa =  'Urlop', CONCAT( d.id,  'U' ), IF( k.nazwa =  'IFS', CONCAT( d.id,  'IFS' ),IF( k.nazwa like 'Awaria%', CONCAT( d.id,  'A' ),d.id )))) AS dla_id, d.nazwa as dla, sum(czas) as ile FROM kart_pr_prace_all2 p, kart_pr_kat k, kart_pr_dzial d, users u where k.id_dzial = d.id and u.id = p.user_id and p.kat_id = k.id";
	$query_1 = "SELECT dzial, IF( k.nazwa =  'L4', CONCAT( d.id,  'L' ), IF( k.nazwa =  'Urlop', CONCAT( d.id,  'U' ), IF( k.nazwa like 'Awaria%', CONCAT( d.id,  'A' ),d.id ))) AS dla_id, d.nazwa as dla, sum(czas) as ile FROM kart_pr_prace_all2 p, kart_pr_kat k, kart_pr_dzial d, users u where k.id_dzial = d.id and u.id = p.user_id and p.kat_id = k.id";

	$daty = " data >= ".$od."000 ";
	if ($do)
		$daty .= " AND data <= ".($do+60*60*24)."000";
	
//najnowsze	$query_2 = "SELECT if(p.zlecenie='',if (z.nazwa is null,'',z.nazwa),p.zlecenie) as zlecenie, d.id as dla_id, d.nazwa as dla, sum(czas) as ile FROM kart_pr_prace_all2 p left join kart_pr_zadania z ON (p.zadanie = z.id), kart_pr_kat k, kart_pr_dzial d, users u where k.id_dzial = d.id and u.id = p.user_id and p.kat_id = k.id";
//	$query_2 = "SELECT if(p.zlecenie='',z.nazwa,p.zlecenie), d.id as dla_id, d.nazwa as dla, sum(czas) as ile FROM kart_pr_prace_all2 p left join kart_pr_zadania z ON (p.zadanie = z.id), kart_pr_kat k, kart_pr_dzial d, users u where k.id_dzial = d.id and u.id = p.user_id and p.kat_id = k.id";
//	$query_2 = "SELECT if(p.zlecenie='',if (z.nazwa is null,if (kat_id = 556,'Awaria sprzetu',if (kat_id = 547,'Zarzadzanie',if (kat_id = 557,'Delegacje',''))),z.nazwa),IF( z.typ =  'PNU', CONCAT(  'PNU Projekt nr ', p.zlecenie ) , p.zlecenie )) as zlecenie, d.id as dla_id, d.nazwa as dla, sum(czas) as ile FROM kart_pr_prace_all2 p left join kart_pr_zadania z ON (p.zadanie = z.id), kart_pr_kat k, kart_pr_dzial d, users u where k.id_dzial = d.id and u.id = p.user_id and p.kat_id = k.id";
//$query_2 = "SELECT if (p.zadanie is null, if(p.zlecenie='', if (z.nazwa is null, if (kat_id = 556,'Awaria sprzetu', if (kat_id = 547,'Zarzadzanie', if (kat_id = 557,'Delegacje',''))), z.nazwa), p.zlecenie), if (z.typ = 'PNU', CONCAT('PNU Projekt nr ', z.zlecenie), z.zlecenie)) as zlecenie, d.id as dla_id, d.nazwa as dla, sum(czas) as ile FROM kart_pr_prace_all2 p left join kart_pr_zadania z ON (p.zadanie = z.id), kart_pr_kat k, kart_pr_dzial d, users u where k.id_dzial = d.id and u.id = p.user_id and p.kat_id = k.id";

				// IF( z.typ = 'PNU', CONCAT(  'PNU Projekt nr ', z.zlecenie ) , z.nazwa ) ) AS zlecenie, 
/*
$query_2 = "SELECT IF( p.zadanie IS NULL ,
				IF( p.zlecenie =  '', IF( z.nazwa IS NULL , IF( kat_id =556,  'Awaria sprzetu', IF( kat_id =547,  'Zarzadzanie', IF( kat_id =557,  'Delegacje',  '' ) ) ) , z.nazwa ) , p.zlecenie ), 
				IF( z.typ = 'PNU', 
					CONCAT(  'PNU Projekt nr ', z.zlecenie ), 
					IF( z.typ = 'MPK', z.nazwa, z.zlecenie )
				) ) AS zlecenie, 
			d.id AS dla_id, d.nazwa AS dla, SUM( czas ) AS ile, z.id as czynnosc, z.typ as typ
FROM ( SELECT * FROM kart_pr_prace_all2 WHERE ".$daty.") p
LEFT JOIN kart_pr_zadania z ON ( p.zadanie = z.id ) 
LEFT JOIN kart_pr_kat k ON ( p.kat_id = k.id ) 
LEFT JOIN kart_pr_dzial d ON ( k.id_dzial = d.id ) 
LEFT JOIN users u ON ( u.id = p.user_id ) 
WHERE TRUE ";
*/

$query_2 = "SELECT IF( z.typ =  'PNU', CONCAT(  'PNU Projekt nr ', z.zlecenie ) , IF( z.typ =  'MPK', z.nazwa, z.zlecenie ) ) AS zlecenie, d.id AS dla_id, d.nazwa AS dla, SUM( czas ) AS ile, z.id AS czynnosc, z.typ AS typ
FROM ( SELECT * FROM kart_pr_prace_all2 WHERE ".$daty." AND zadanie IS NOT NULL ) p
LEFT JOIN kart_pr_zadania z ON ( p.zadanie = z.id ) 
LEFT JOIN kart_pr_kat k ON ( p.kat_id = k.id ) 
LEFT JOIN kart_pr_dzial d ON ( k.id_dzial = d.id ) 
LEFT JOIN users u ON ( u.id = p.user_id ) 
WHERE TRUE ";

$query_3 = "SELECT z.nazwa AS zadanie, z.zlecenie AS zlecenie, u.nazwa AS kto, p.opis AS opis, z.rbh AS plan, z.termin AS termin, p.czas AS ile, p.data AS kiedy
FROM ( SELECT * FROM kart_pr_prace_all2 WHERE ".$daty." AND zadanie IS NOT NULL AND kat_id <> 558 AND kat_id <> 548 AND kat_id <> 545 ) p
LEFT JOIN kart_pr_zadania z ON ( p.zadanie = z.id ) 
LEFT JOIN users u ON ( u.id = p.user_id )
WHERE TRUE ";

//" AND data >= 1425164400000 AND data <= 1427756400000 AND dzial <> 'TUG' AND dzial <> 'DYR' AND dzial <> 'DRiW' AND dzial NOT LIKE '?%' AND dzial NOT LIKE 'DH%' AND dzial like '%' AND k.nazwa <> 'L4' AND k.nazwa <> 'Urlop' GROUP BY zlecenie, id_dzial ORDER BY `zlecenie` DESC;";
//*/
	
	$query = "";
	if ($perm == "0")
		$query .= " AND u.id = ".$user_id;
	
	if ($perm == "1" || $perm == "2")
		$query .= " AND u.dzial LIKE '".$dzial."%'";
	$query .= " AND dzial <> 'TUG' AND dzial <> 'DYR' AND dzial <> 'DRiW' AND dzial NOT LIKE '?%' AND dzial NOT LIKE 'DH%'";
	$query .= " AND dzial like '$dzial_2'";

$query_3 .= $query . " ORDER by kiedy, zlecenie, kto";

//	$query_2 .= $query." AND k.nazwa <> 'L4' AND k.nazwa <> 'Urlop' GROUP BY p.zlecenie, z.id, id_dzial ORDER BY `ile` DESC;";
	$query_2 .= $query." AND k.nazwa <> 'L4' AND k.nazwa <> 'Urlop' "
	." GROUP BY zlecenie, id_dzial ORDER BY `ile` DESC;";
	$query .= " AND ".$daty;

	$query_1 .= $query." GROUP BY u.dzial, dla_id;";
	
if ($_SESSION["myusername"] == "913"){
// echo $query_1;
// echo $query_2;
// exit($query_3);
// exit;
}

	
	$result = $mysqli->query($query_1);
	$stat = array();
	while($row = $result->fetch_assoc()) $stat[] = $row;

	$result = $mysqli->query($query_2);
	$st_proj = array();
	while($row = $result->fetch_assoc()) $st_proj[] = $row;

	$result = $mysqli->query($query_3);
	$st_osob = array();
	while($row = $result->fetch_assoc()) $st_osob[] = $row;
	
	$out = array();
	$out["st_dzial"] = $stat;
	$out["st_proj"] = $st_proj;
	$out["st_osob"] = $st_osob;
		
	$days = "";
	function s_fun_old($a,$b,$c){
		global $swieta;
		if (isset( $swieta[Date("n",$b/1000)] ) && isset( $swieta[Date("n",$b/1000)][Date("j",$b/1000)]))
			return "";
		if (Date("N",$b/1000)/1 < 6 && $a != -1){
//			echo $a." ".$b." ".$c."\n";
			return ($b/1000 - (Date("Z",$b/1000)/1 - 7200))."000";	//niwelacja czasu zimowego
		}
		return "";
	}
	
	function s_fun($a,$b,$c){
		global $swieta;
		$bb = $b/1000;// - (Date("Z",$b/1000)/1 - 3600);
//		$bb += 1*60*60;
//		$bb += 2*60*60;
		if (isset( $swieta[Date("n",$bb)] ) && isset( $swieta[Date("n",$bb)][Date("j",$bb)]))
			return "";
		if (Date("N",$bb)/1 < 6){
			return $bb."000";	//niwelacja czasu zimowego
		}
		return "";
	}
	
	$t = time();
	$t -= $t % (60*60*24);
	$t -= Date("Z");
	$t -= (60*60*24);	//do przedwczoraj
	$do = $t;
	
if ($_SESSION["myusername"] == "913"){
	// var_dump($od);
	// var_dump($do);
//	var_dump($days);
}
	
	$swieta = array();
	$query = "SELECT * FROM kart_pr_swieta;";
	$result = $mysqli->query($query);
	while($row = $result->fetch_assoc()){
		if (!isset($swieta[$row["miesiac"]]))
			$swieta[$row["miesiac"]] = array();
		$swieta[$row["miesiac"]][$row["dzien"]] = $row["opis"];
	}
	$out["swieta"] = $swieta;

	$days = dla_kazdego_dnia_new($od*1000,$do*1000,"s_fun");
//	$days = dla_kazdego_dnia(1371420000000,($t - Date("Z"))*1000,"s_fun");
//if ($_SESSION["myusername"])
if ($_SESSION["myusername"] == "913"){
//	var_dump($od);
//	var_dump($do);
//	var_dump($days);
} else {
}
	$query = 
		"SELECT nazwa, dzial, day, ile FROM ("
	.		"SELECT * FROM ("
				."SELECT * FROM "
					."((SELECT ". implode(" as day) union (SELECT ",$days)." as day)) days, "
					."(SELECT id FROM `users` WHERE (dzial like 'TR%' OR dzial like 'RTR%' OR dzial like 'TP%')";

	if ($perm == "0")
		$query .= " AND id = ".$user_id;
	
	if ($perm == "1" || $perm == "2")
		$query .= " AND dzial LIKE '".$dzial."%'";
		
	$query .= " AND dzial like '$dzial_2'";
		$query .=""			
					.") ids"
			.") di LEFT JOIN ("
				."SELECT user_id, data, sum(czas) as ile FROM `kart_pr_prace_all2` group by user_id, data"
			.") p ON (p.user_id=di.id and p.data = di.day)"
			."WHERE ile>540 OR ile<240 OR ile IS NULL"
		.") di2, users u2 WHERE di2.id = u2.id ORDER BY nazwa ASC, day ASC"
	.";";
	
if ($_SESSION["myusername"] == "913"){
	 // echo $query;
}
	$result = $mysqli->query($query);
	$braki = array();
	if ($result)
	while($row = $result->fetch_assoc()) {
//		var_dump($row);
		if ($row["nazwa"] != "Rolak Robert")	//zwolniony
			$braki[] = $row;
	}
	$out["braki"] = $braki;

	
	if (isset($_REQUEST["callback"])){
		$callback = trim($_REQUEST['callback']);
		echo $callback .'('.json_encode($out).')';
	} else if (isset($_REQUEST["jsoncallback"])){
		$callback = trim($_REQUEST['jsoncallback']);
		echo $callback .'('.json_encode($out).')';
	} else
		echo 'var karta_stat = '.json_encode($out).';';
//exit;
echo "
/*
";
//SELECT dzial, if (k.nazwa = 'L4' or k.nazwa = 'Urlop',concat(d.id,'LU'),d.id) as dla_id, d.nazwa as dla, sum(czas) as ile FROM kart_pr_prace_all2 p, kart_pr_kat k, kart_pr_dzial d, users u where k.id_dzial = d.id and u.id = p.user_id and p.kat_id = k.id AND data >= 1375308000000 AND data <= 1377900000000 AND dzial <> 'TUG' AND dzial <> 'DYR' AND dzial <> 'DRiW' GROUP BY u.dzial, dla_id;
//	echo $query;
//	var_dump($days);
//	var_dump($swieta);
//var_dump($braki);
echo "
*/";
?>
