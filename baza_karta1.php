<?php
	$times = array(microtime(true));
	header('Content-type: application/json');
	header('Cache-Control: no-cache, must-revalidate');
	session_start();
	if ( !isset( $_SESSION["myusername"] ) )
		exit('[]');
	require_once ('conf.php');
//	error_reporting(0);

	$conn = polacz_sql();
	
	array_push($times,microtime(true));	
	$date = new DateTime();
echo 'var serv_epoch = '.$date->getTimestamp().';';

// (UNIX_TIMESTAMP(substring(p.timestamp,1,10))*1000 - p.data)/(1000*60*60) as timestamp_diff_h
	
	$query = "SELECT 
kat_id, id_dzial, id_kat, czas, zadanie, data, convert(nvarchar(MAX), p.timestamp, 120) as timestamp,
p.czas as ile, p.zadanie as zad, d.nazwa as dzial, k.nazwa as kat, p.opis as opis_p, p.id as kart_id, u.nazwa as kart_user, p.id as prac_id, coalesce(z.zlecenie, p.zlecenie) as zlec,
DATEDIFF ( ss , '01-01-1970 00:00:00' , p.timestamp )-(p.data/1000) as timestamp_diff_h
FROM wwwkop.dbo.kart_pr_prace p
LEFT JOIN wwwkop.dbo.users u ON p.user_id = u.id
LEFT JOIN wwwkop.dbo.kart_pr_kat k ON k.id = p.kat_id
LEFT JOIN wwwkop.dbo.kart_pr_dzial d ON k.id_dzial = d.id
LEFT JOIN wwwkop.dbo.kart_pr_zadania z ON p.zadanie = z.id
WHERE p.user_id = ";
	if (isset($_REQUEST["user_id"]) && $_SESSION["myuser"]["kart_perm"] != "0") 
		$query .= $_REQUEST["user_id"];
	else
		$query .= $_SESSION["myuserid"];

	$show_start = new DateTime();
	$show_stop = new DateTime();
	if (isset($_REQUEST["month"]) && isset($_REQUEST["year"])){
		$show_start->setDate($_REQUEST["year"], $_REQUEST["month"], 0)->setTime(0,0,0);
		$show_stop->setDate($_REQUEST["year"], $_REQUEST["month"]+1, 1)->setTime(0,0,0);
		$query .= ' AND p.data > '.$show_start->getTimestamp().'000';
		$query .= ' AND p.data < '.$show_stop->getTimestamp().'000';
		// echo 'var test_data = '.$show_start->format('Y-m-d').';';
		// echo 'var test_data2 = '.$show_start->getTimestamp().';';
		// echo 'var test_data3 = '.$show_stop->format('Y-m-d').';';
		// echo 'var test_data4 = '.$show_stop->getTimestamp().';';
		$query .= ' ORDER BY data DESC';
	} else {
		$query .= ' ORDER BY data DESC';
	}
	if (isset($_REQUEST["month"]) && isset($_REQUEST["year"])){
	} else {
		$query = str_replace("SELECT", "SELECT TOP 200", $query);
	}

	$query .= ';';
	$result = sqlsrv_query($conn, $query);
	$karty = array();
	if ($result)
		while($row1 = sqlsrv_fetch_object ( $result )){
			$row = array();
			foreach ($row1 as $key => $value) {
				if ($key == "timestamp_diff_h")
					$row[$key] = round($value/3600);
				else
					$row[$key] = $value;
				// $row[$key] = $row[$key].'';
			}
			$karty[] = $row;
			// var_dump($row);
		}
echo 'var karty = '.json_encode($karty).';';
	array_push($times,microtime(true));

	$query = "SELECT z.* FROM  wwwkop.dbo.kart_pr_zadania z, wwwkop.dbo.users u WHERE z.prac_wykon like ('%'+CHAR(39)+CAST(u.id as varchar(10))+CHAR(39)+'%') ESCAPE '\' AND u.id = ";
	// $query = "SELECT z.* FROM  kart_pr_zadania z, users u WHERE z.prac_wykon like concat(\"%'\",u.id,\"'%\") AND u.id = ";
	if (isset($_REQUEST["user_id"]) && $_SESSION["myuser"]["kart_perm"] != "0") 
		$query .= $_REQUEST["user_id"];
	else
		$query .= $_SESSION["myuserid"];
// exit($query);
	// echo $query;
	//	$query = "SELECT * FROM  kart_pr_zadania WHERE 1;";
//	$query .= ' ORDER BY `data`DESC;';
//	$query .= ' ORDER BY `id_dzial`,`kat_id`;';
//	echo $query;
	$result = sqlsrv_query($conn, $query);
	$zadania = array();
	 if ($result)
		while($row1 = sqlsrv_fetch_object ( $result )){
			$row = array();
			foreach ($row1 as $key => $value) {
				if ($key == "timestamp")
					;
				else {
					$row[$key] = $value;
					// $row[$key] = $row[$key].'';
				}
			}
			$zadania[$row["id"]] = $row;
		}
echo 'var zadania = '.json_encode($zadania).';';
// exit;
	$query = "SELECT * FROM  wwwkop.dbo.kart_pr_projekty";
	$result = sqlsrv_query($conn, $query);
	$foldery = array();
	if ($result)
		while($row1 = sqlsrv_fetch_object ( $result )){
			$row = array();
			// var_dump($row1);
			foreach ($row1 as $key => $value) {
				if ($key == "timestamp")
					;
				else {
					$row[$key] = $value;
					// $row[$key] = $row[$key].'';
				}
			}
			 $foldery[$row["id"]] = $row;
		}
echo 'var foldery = '.json_encode($foldery).';';
	$dzialy = array();
	$kategorie = array();
	$query = "SELECT * FROM wwwkop.dbo.kart_pr_dzial;";
	$result = sqlsrv_query($conn, $query);
	while($row1 = sqlsrv_fetch_object ( $result )){
		$row = array();
		// var_dump($row1);
		foreach ($row1 as $key => $value) {
			if ($key == "timestamp")
				;
			else {
				$row[$key] = $value;
				// $row[$key] = $row[$key].'';
			}
		}
		$dzialy[$row['id']] = $row;
//		$kategorie[] = array();
		$kategorie[$row['id']] = array();
	}
echo 'var dzialy = '.json_encode($dzialy).';';

	$query = "SELECT * FROM wwwkop.dbo.kart_pr_kat;";
	$result = sqlsrv_query($conn, $query);
	while($row1 = sqlsrv_fetch_object ( $result )){
		$row = array();
		// var_dump($row1);
		foreach ($row1 as $key => $value) {
			if ($key == "timestamp")
				;
			else {
				$row[$key] = $value;
				// $row[$key] = $row[$key].'';
			}
		}
		if ($row['id_kat'])
			$kategorie[$row['id_dzial']][$row['id_kat']]['ma_podgr'][$row['id']] = $row;
		else{
			$kategorie[$row['id_dzial']][$row['id']] = $row;	
			if ($row['ma_podgr'])
				$kategorie[$row['id_dzial']][$row['id']]['ma_podgr'] = array();
			else
				$kategorie[$row['id_dzial']][$row['id']]['ma_podgr'] = null;
		}
	}
echo 'var kategorie = '.json_encode($kategorie).';';

	$swieta = array();
	$query = "SELECT * FROM wwwkop.dbo.kart_pr_swieta";
	if (isset($_REQUEST["year"])){
		$query .= " WHERE rok = ".$_REQUEST["year"]." OR rok IS NULL;";
	} else {
		$query .= " WHERE rok = ".$date->format('Y')." OR rok IS NULL;";
	}
	$result = sqlsrv_query($conn, $query);
	
	if ($result)
		while($row1 = sqlsrv_fetch_object ( $result )){
			$row = array();
			// var_dump($row1);
			foreach ($row1 as $key => $value) {
				if ($key == "timestamp")
					;
				else {
					$row[$key] = $value;
					// $row[$key] = $row[$key].'';
				}
			}
			if (!isset($swieta[$row["miesiac"]]))
				$swieta[$row["miesiac"]] = array();
			$swieta[$row["miesiac"]][$row["dzien"]] = $row["opis"];
		}
echo 'var swieta = '.json_encode($swieta).';';
	$query = "SELECT * FROM wwwkop.dbo.users WHERE id = ";
	if (isset($_REQUEST["user_id"]) && $_SESSION["myuser"]["kart_perm"] != "0") 
		$query .= $_REQUEST["user_id"];
	else
		$query .= $_SESSION["myuserid"];
	$result = sqlsrv_query($conn, $query);
	$user_info = '???';
	if($result && $row = sqlsrv_fetch_object ( $result )){
		$user_info = $row;
	}
echo 'var user_info = '.json_encode($user_info).';';
exit;
	array_push($times,microtime(true));	

	echo '/*';
	var_dump($times);
	echo '*/';
?>
