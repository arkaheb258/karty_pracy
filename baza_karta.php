<?php
	$times = array(microtime(true));
	header('Content-type: application/json');
	header('Cache-Control: no-cache, must-revalidate');
	session_start();
	if ( !isset( $_SESSION["myusername"] ) )
		exit('[]');
	require_once ('conf.php');
//	error_reporting(0);
	$mysqli = new_polacz_z_baza();

array_push($times,microtime(true));	
/*	$query = "SELECT nazwa FROM users WHERE 1;";
	$result = $mysqli->query($query);
	$users = array();
	while($row = $result->fetch_assoc()) $users[] = $row['nazwa'];
	echo 'var users = '.json_encode($users).';';
*/

	$date = new DateTime();
	echo 'var serv_epoch = '.$date->getTimestamp().';';

	// $query = "SELECT *, data, p.czas as ile, p.zadanie as zad, d.nazwa as dzial, k.nazwa as kat, p.opis as opis_p, p.id as kart_id, u.nazwa as kart_user, p.id as prac_id, p.zlecenie as zlec FROM kart_pr_prace_all p , users u, kart_pr_kat k, kart_pr_dzial d WHERE k.id = p.kat_id AND k.id_dzial = d.id AND p.user_id = u.id AND p.user_id = ";
//(UNIX_TIMESTAMP(p.timestamp)*1000 - p.data)/(1000*60*60) as timestamp_diff_h
	$query = "SELECT 
kat_id, id_dzial, id_kat, czas, zadanie, data, p.timestamp,
p.czas as ile, p.zadanie as zad, d.nazwa as dzial, k.nazwa as kat, p.opis as opis_p, p.id as kart_id, u.nazwa as kart_user, p.id as prac_id, coalesce(z.zlecenie, p.zlecenie) as zlec,
(UNIX_TIMESTAMP(substring(p.timestamp,1,10))*1000 - p.data)/(1000*60*60) as timestamp_diff_h
FROM kart_pr_prace_all p
LEFT JOIN users u ON p.user_id = u.id
LEFT JOIN kart_pr_kat k ON k.id = p.kat_id
LEFT JOIN kart_pr_dzial d ON k.id_dzial = d.id
LEFT JOIN kart_pr_zadania z ON p.zadanie = z.id
WHERE p.user_id = ";
//	FROM kart_pr_prace_all p , users u, kart_pr_kat k, kart_pr_dzial d WHERE k.id = p.kat_id AND k.id_dzial = d.id AND p.user_id = u.id AND p.user_id = ";
	if (isset($_REQUEST["user_id"]) && $_SESSION["myuser"]["kart_perm"] != "0") 
		$query .= $_REQUEST["user_id"];
	else
		$query .= $_SESSION["myuserid"];
//and p.data > 1391554800000
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
		$query .= ' ORDER BY `data`DESC';
	} else {
		$query .= ' ORDER BY `data`DESC';
		if (!isset($_REQUEST["user_id"])) 
			$query .= ' limit 200';
	}
	
//	$query .= ' ORDER BY `data`DESC';
//	$query .= ' ORDER BY `id_dzial`,`kat_id`;';

	$query .= ';';
	// echo $query;
	// exit;
	$result = $mysqli->query($query);
	$karty = array();
	 if ($result)
		while($row = $result->fetch_assoc()){
			 $karty[] = $row;
			// $ids[] = $row["rap_id"];
		}
	echo 'var karty = '.json_encode($karty).';';
array_push($times,microtime(true));

//	$query = "SELECT z.id, z.user_id, z.nazwa, z.opis, z.aktywny, z.zlecenie, z.dzial_zlec, z.dzial_wyk, z.komentarz, z.timestamp FROM  kart_pr_zadania z, users u WHERE z.dzial_wyk like concat(\"%'\",u.dzial,\"'%\") AND u.id = ";
	// $query = "SELECT z.id, z.user_id, z.nazwa, z.opis, z.aktywny, z.zlecenie, z.dzial_zlec, z.dzial_wyk, z.komentarz, z.timestamp FROM  kart_pr_zadania z, users u WHERE z.prac_wykon like concat(\"%'\",u.id,\"'%\") AND u.id = ";
	// $query = "SELECT z.* FROM  kart_pr_zadania z, users u WHERE z.prac_wykon like concat(\"%'\",u.id,\"'%\") AND u.id = ";
	$query = "SELECT z.* FROM  kart_pr_zadania z, users u, kart_pr_prace p WHERE (z.prac_wykon like concat(\"%'\",u.id,\"'%\") or p.zadanie = z.id) and p.user_id = u.id AND u.id = ";
  
  $q_u_id = $_SESSION["myuserid"];
  
	if (isset($_REQUEST["user_id"]) && $_SESSION["myuser"]["kart_perm"] != "0") {
		$query .= $_REQUEST["user_id"];
    $q_u_id = $_REQUEST["user_id"];
	} else {
		$query .= $_SESSION["myuserid"];
  }
  $query .= " GROUP BY id;";

	$new_query = "( SELECT z.* FROM  kart_pr_zadania z WHERE z.prac_wykon like concat(\"%'\",".$q_u_id.",\"'%\") )";
	$new_query .= "UNION ( SELECT z.* FROM  kart_pr_zadania z, kart_pr_prace p WHERE p.zadanie = z.id and p.user_id = ".$q_u_id." );";
  $query = $new_query;
  
  // exit($query);
	// echo $query;
	//	$query = "SELECT * FROM  kart_pr_zadania WHERE 1;";
//	$query .= ' ORDER BY `data`DESC;';
//	$query .= ' ORDER BY `id_dzial`,`kat_id`;';
//	echo $query;
	$result = $mysqli->query($query);
	$zadania = array();
	 if ($result)
		while($row = $result->fetch_assoc()){
			 $zadania[$row["id"]] = $row;
		}
	echo 'var zadania = '.json_encode($zadania).';';
array_push($times,microtime(true));

	$query = "SELECT * FROM  kart_pr_projekty";
	$result = $mysqli->query($query);
	$foldery = array();
	 if ($result)
		while($row = $result->fetch_assoc()){
			 $foldery[$row["id"]] = $row;
		}
	echo 'var foldery = '.json_encode($foldery).';';

	$dzialy = array();
	$kategorie = array();
	$query = "SELECT * FROM kart_pr_dzial;";
	$result = $mysqli->query($query);
	while($row = $result->fetch_assoc()){
		$dzialy[$row['id']] = $row;
//		$kategorie[] = array();
		$kategorie[$row['id']] = array();
	}
	echo 'var dzialy = '.json_encode($dzialy).';';

	$query = "SELECT * FROM kart_pr_kat;";
	$result = $mysqli->query($query);
	while($row = $result->fetch_assoc()){
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
/*	
	$query = "SELECT * FROM `kart_pr_pnu`;";
	$result = $mysqli->query($query);
	$pnu = array();
	while($row = $result->fetch_assoc()){
		$pnu[$row["nr"]] = $row;
	}
	echo 'var pnu = '.json_encode($pnu).';';
*/
	$query = "SELECT * FROM `users` WHERE id = ";
	if (isset($_REQUEST["user_id"]) && $_SESSION["myuser"]["kart_perm"] != "0") 
		$query .= $_REQUEST["user_id"];
	else
		$query .= $_SESSION["myuserid"];
	$result = $mysqli->query($query);
	$user_info = '???';
	if($result && $row = $result->fetch_assoc()){
		$user_info = $row;
	}
	echo 'var user_info = '.json_encode($user_info).';';
array_push($times,microtime(true));	

	echo '/*';
	var_dump($times);
	// var_dump($new_query);
	echo '*/';
	
	$mysqli->close();
?>
