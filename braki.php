<?php
	header('Content-type: application/json');
	header('Cache-Control: no-cache, must-revalidate');
	require_once ('conf.php');
	session_start();
	if ( !isset( $_SESSION["myusername"] ) )
		exit('[]');
	require_once ('conf.php');
	
	$user_id = $_SESSION["myuser"]["id"];
//	$user_id = 60;
	function xs_fun($a,$b,$c){
		global $swieta, $user_id;
//		$bb = $b/1000 - (Date("Z",$b/1000)/1 - 7200);
		$bb = $b/1000 - (Date("Z",$b/1000)/1 - 3600);
//if ($user_id == 1) echo " a=".$a;
//if ($user_id == 1) echo " b=".$b;
//if ($user_id == 1) echo " bb=".(($bb % (24*60*60))/60/60);
//if ($user_id == 1) echo " bb=".$bb;
		if ($a == 0 && (($bb % (24*60*60))/60/60) == 22)
			$bb += 60*60;
//			return "";
//	if ($user_id == 1) echo "bb=".Date("Z",$od);
		if (isset( $swieta[Date("n",$bb)] ) && isset( $swieta[Date("n",$bb)][Date("j",$bb)]))
			return "";
		if (Date("N",$bb)/1 < 6){
			return $bb."000";	//niwelacja czasu zimowego
		}
		return "";
	}

	function s_fun_ex($a,$b,$c){
		global $swieta;
//		$bb = $b/1000 - (Date("Z",$b/1000)/1 - 7200);	//czas zimowy
		$bb = $b/1000 - (Date("Z",$b/1000)/1 - 3600);	//czas letni
//		echo (($bb % (24*60*60))/60/60)."<>";
		if ((($bb % (24*60*60))/60/60) == 21)
			$bb += 60*60;
//		if ((($bb % (24*60*60))/60/60) == 23)
//			$bb -= 60*60;
		if (isset( $swieta[Date("n",$bb)] ) && isset( $swieta[Date("n",$bb)][Date("j",$bb)]))
			return "";
		if (Date("N",$bb)/1 < 6){
			return $bb."000";	//niwelacja czasu zimowego
		}
		return "";
	}
	
	function s_fun($a,$b,$c){
		global $swieta;
		$bb = $b/1000;// - (Date("Z",$b/1000)/1 - 3600);
		$bb += 2*60*60;
		if (isset( $swieta[Date("n",$bb)] ) && isset( $swieta[Date("n",$bb)][Date("j",$bb)]))
			return "";
		if (Date("N",$bb)/1 < 6){
			return $bb."000";	//niwelacja czasu zimowego
		}
		return "";
	}

	$t = time();
	$t -= $t % (60*60*24);
//	if ($user_id == 1) echo $t;
//	$t -= Date("Z");
	$od = $t - (Date("d")/1 - 1) *(60*60*24);	//pierwszy kazdego miesiaca
	$od -= 30 *(60*60*24);
//	if ($user_id == 1) echo $od;
	$t -= (60*60*24);	//do przedwczoraj
	$do = $t;

	$od -= (60*60*2);	//korekta dla starej funkcji "dla_kazdego_dnia"
//	if ($user_id == 1) echo $do;
	
//	if ($user_id == 1) echo "od=".$od;
//	$od -= Date("Z",$od);//-Date("Z");  //korekta strefy czasowej na przełomie
//	echo Date("Z",$od);
//	echo Date("Z");

//	if ($user_id == 1) echo "od=".Date("Z",$od);
//	if ($user_id == 1) echo "  ";
//	if ($user_id == 1) echo "od=".$od;
//	if ($user_id == 1) echo "do=".$do;

	
	$mysqli = new_polacz_z_baza();
	$swieta = array();
	$query = "SELECT * FROM kart_pr_swieta;";
	$result = $mysqli->query($query);
	while($row = $result->fetch_assoc()){
		if (!isset($swieta[$row["miesiac"]]))
			$swieta[$row["miesiac"]] = array();
		$swieta[$row["miesiac"]][$row["dzien"]] = $row["opis"];
	}

	$days = dla_kazdego_dnia($od*1000,$do*1000,"s_fun");
	
 // if ($user_id == 1)	var_dump($days);
	
	$query = 
		"SELECT nazwa, dzial, day, ile FROM ("
			."SELECT * FROM ("
				."SELECT * FROM "
					."((SELECT ". implode(" as day) union (SELECT ",$days)." as day)) days, "
					."(SELECT id FROM `users` WHERE (dzial like 'TR%' OR dzial like 'RTR%' OR dzial like 'TP%')"
						." AND id = ".$user_id
					.") ids"
			.") di LEFT JOIN ("
				."SELECT user_id, data, sum(czas) as ile FROM `kart_pr_prace_all2` group by user_id, data"
			.") p ON (p.user_id=di.id and p.data = di.day)"
			."WHERE ile>540 OR ile<360 OR ile IS NULL"
		.") di2, users u2 WHERE di2.id = u2.id ORDER BY nazwa ASC, day ASC"
	.";";
	
// if ($user_id == 1) echo $query;
// exit();
	// $result = $mysqli->query($query);
	$braki = array();
	if ($result)
	while($row = $result->fetch_assoc()) {
		$braki[] = $row;
	}
	
	echo 'var braki = '.json_encode($braki).';';
?>
