<?php
	require_once ('conf.php');
	$mysqli = new_polacz_z_baza();
	$query = "SELECT max(timestamp) as t FROM `kart_pr_prace_rtr`";
	$result = $mysqli->query($query);
	$last = '';
	while($row = $result->fetch_assoc()){
		$last = urlencode($row["t"]);
	}
	$limit = 200;
	$rtr = "http://192.168.112.151/karta_pracy/dump2.php?limit=$limit&timestamp=$last";
//	$ctx = stream_context_create(array( 'http' => array( 'timeout' => 10, 'content' => $body ) ) ); 
//	echo file_get_contents($rtr, 0, $ctx);
	$sql = file_get_contents($rtr);
	$query = str_replace('kart_pr_prace','kart_pr_prace_rtr',$sql);
	$query = substr($query,strpos($query,"REPLACE"));
//	echo ">".$rtr."<";
//	echo $query;
//	exit;
	$result = $mysqli->query($query);
	if (isset($_REQUEST["callback"])){
		$callback = trim($_REQUEST['callback']);
		echo $callback .'(';
	}
	if (isset($_REQUEST["jsoncallback"])){
		$callback = trim($_REQUEST['jsoncallback']);
		echo $callback .'(';
	}
	if(!$result)
		echo json_encode($mysqli->error);
	else
		echo json_encode($mysqli->affected_rows);
	if (isset($_REQUEST["callback"]) || isset($_REQUEST["jsoncallback"]))
		echo ')';
?>
