<?php
	header('Content-type: application/json');
	header('Cache-Control: no-cache, must-revalidate');
	if (isset($_REQUEST["callback"])){
		$callback = trim($_REQUEST['callback']);
		echo $callback .'(';
	}
	if (isset($_REQUEST["jsoncallback"])){
		$callback = trim($_REQUEST['jsoncallback']);
		echo $callback .'(';
	}
	require_once ('../conf.php');
//	error_reporting(0);

//var_dump($_REQUEST);
	$mysqli = new_polacz_z_baza();

	$del = test_req( "del",null);
	$id = test_req( "id",'');
	
	$table = "kart_pr_prace";
	
	if (substr($id,0,1)=='R'){
		$id = substr($id,1);
		$table = "kart_pr_prace_rtr";
	}


	$kto = test_req( "kto" );
	$zlecenie = addslashes(test_req( "zlec" ));
	$co = test_req( "co" );
	$dni = test_req( "dni","");
	$ile = test_req( "ile" );
	$opis = addslashes(test_req( "opis" ));
	$ip = getIP();

	if ($dni != ""){
		$dzien = explode ( "," , $dni);
		$query = "";
		foreach ($dzien as $d){
			$d = $d ."000";
			if ($query != "")
				$query .= ",";
			$query .= "(\"$kto\",\"$co\",\"$ile\",$d,\"$zlecenie\",\"$opis\",\"$ip\")";
		}
		$query = "INSERT INTO `$table`(`user_id`,`kat_id`, `czas`, `data`, `zlecenie`, `opis`, `ip`) VALUES " .$query .";";
//		echo $query;
		if ($mysqli->query($query))
			echo json_encode(array('OK',$mysqli->insert_id,'INSERT','Zarejestrowano '.count($dzien)." dni.",0));
		else echo json_encode(array($mysqli->error,0));
	} else {
		$kiedy = test_req( "kiedy" );
		
		$t_diff = (new DateTime())->getTimestamp() - $kiedy/1000;
		
		
		session_start();
		if ($_SESSION["myuser"]["id"] != 1 && $kto != "1" 
		&& $kto != "33" //Janusz
		&& $kto != "40" //Miziak
		&& $kto != "60" //Krystian
//		&& $kto != "52" //Skwarek
//		&& $kto != "54" //Szweda
		&& $kto != "25" //Ciesielski
		&& $kto != "59" //Kostka
		&& $t_diff > 8*24*60*60){
			echo json_encode(array('OK',0,'DELETE','Próba oszustwa została zarejestrowana',0));
		} else {
			$suma_czasu = 0;
			//pobranie danych o sumie czasu pracy w danym dniu
			$query = "SELECT SUM( czas ) as suma FROM  `$table` WHERE data = $kiedy AND user_id = $kto";
			$result = $mysqli->query($query);
			if($row = $result->fetch_assoc())
				$suma_czasu = $row['suma'];

			$czas_id = 0;
			$query = "SELECT czas FROM  `$table` WHERE data = $kiedy AND user_id = $kto AND id = $id";
			$result = $mysqli->query($query);
			if($result)
				if($row = $result->fetch_assoc())
					$czas_id = $row['czas'];
		
			$suma_czasu -= $czas_id;
			
			if ($del){
				$query = "DELETE FROM `$table` WHERE `$table`.`id` = ".$id.";";
				if ($mysqli->query($query))
					echo json_encode(array('OK',$id,'DELETE','Karta skasowana',$suma_czasu));
				else echo json_encode(array($mysqli->error,0));		
			} else {
				
				$suma_czasu += $ile;
				if ($id != 'null' && $id != ''){
					$query = "UPDATE `$table` SET `user_id` = \"$kto\",`kat_id` = \"$co\", `czas` = \"$ile\", `data` = $kiedy, `zlecenie` = \"$zlecenie\", `opis` = \"$opis\", `ip` = \"$ip\", timestamp = NULL WHERE `$table`.`id` = ".$id.";";
//					echo $query;
					if ($mysqli->query($query)){
						if ($mysqli->affected_rows)
							echo json_encode(array('OK',$id,'UPDATE','Karta poprawiona',$suma_czasu));
						else
							echo json_encode(array('OK',$id,'UPDATE','Karta bez zmian',$suma_czasu));
					} else echo json_encode(array($mysqli->error,0));
				}
				else{
					$query = "INSERT INTO `$table`(`user_id`,`kat_id`, `czas`, `data`, `zlecenie`, `opis`, `ip`) VALUES (\"$kto\",\"$co\",\"$ile\",$kiedy,\"$zlecenie\",\"$opis\",\"$ip\");";
					if ($mysqli->query($query))
						echo json_encode(array('OK',$mysqli->insert_id,'INSERT','Praca zarejestrowana',$suma_czasu));
					else echo json_encode(array($mysqli->error,0));
				}
				$file = "log.txt";
				file_put_contents($file, $query, FILE_APPEND | LOCK_EX);
			}
		}
	}
	$mysqli->close();
	if (isset($_REQUEST["callback"]) || isset($_REQUEST["jsoncallback"]))
		echo ')';
?>
