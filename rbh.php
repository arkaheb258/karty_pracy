<?php
	header('Content-type: application/json');
	header('Cache-Control: no-cache, must-revalidate');
	session_start();
	if ( !isset( $_SESSION["myusername"] ) )
		exit('[]');
	require_once ('../conf.php');
//	error_reporting(0);
	$conn = new_polacz_z_baza();

  if (isset($_REQUEST["callback"])){
    $callback = trim($_REQUEST['callback']);
    echo $callback .'(';
  }
  if (isset($_REQUEST["jsoncallback"])){
    $callback = trim($_REQUEST['jsoncallback']);
    echo $callback .'(';
  }
  if (isset($_REQUEST["zad_id"])) {
    $users = array();
    $query = "SELECT user_id,used,max FROM kart_pr_limity WHERE zad_id = " .$_REQUEST["zad_id"];
    $result = $conn->query($query);
    while($row = $result->fetch_object()){
      $users[$row->user_id] = $row;
    }
    echo json_encode($users);
  }
  if (isset($_REQUEST["update_id"]) && isset($_REQUEST["array"])) {
    $zad_id = $_REQUEST["update_id"];
    $vals = array();
    foreach ($_REQUEST["array"] as $key=>$val) {
        $vals[] = "($zad_id,$key,$val)";
    }
    $query = 'INSERT INTO kart_pr_limity (zad_id, user_id, max) VALUES '. implode ( "," , $vals ).' ON DUPLICATE KEY UPDATE max=VALUES(max);';
    $result = $conn->query($query);
    if ($result)
      echo json_encode(array('OK',$zad_id,'UPDATE'));
    else 
      var_dump($query);
  }
  $conn->close();
  if (isset($_REQUEST["callback"]) || isset($_REQUEST["jsoncallback"]))
    echo ')';
?>
