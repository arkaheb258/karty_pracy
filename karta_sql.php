<?php
  session_start();
  if ( !isset( $_SESSION["myusername"] ) ){
    header("location:login.php?url=".$_SERVER["REQUEST_URI"]);
    exit;
  } else {
    if ($_SESSION['timeout'] + 10 * 60 < time()) {
      header("location:logout.php?session_timeout&url=".$_SERVER["REQUEST_URI"]);
      exit;
    }
    else $_SESSION['timeout'] = time();
  }

  require_once ('conf.php');
  if (isset($_REQUEST["kto"])){
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
    
    $kto = test_req( "kto" );
    $zlecenie = addslashes(test_req( "zlec" ));
    $del = test_req( "del", null);
    if (!$del) {
      $co = test_req( "co" );
    }
    $dni = test_req( "dni","");
    $ile = test_req( "ile" );
    $opis = addslashes(test_req( "opis" ));
    $t_ip = getIP();
    $id = test_req( "id",'');
    $zadanie = test_req( "zad","NULL");
    $table = "kart_pr_prace";
    $mysqli = new_polacz_z_baza();
    
    if ($dni != ""){
      $dzien = explode ( "," , $dni);
      $query = "";
      foreach ($dzien as $d){
        $d = $d ."000";
        if ($query != "")
          $query .= ",";
        $query .= "(\"$kto\",\"$co\",\"$ile\",$d,\"$zlecenie\",\"$opis\",\"$t_ip\")";
      }
      $query = "INSERT INTO `$table`(`user_id`,`kat_id`, `czas`, `data`, `zlecenie`, `opis`, `ip`) VALUES " .$query .";";
      if ($mysqli->query($query))
        echo json_encode(array('OK',$mysqli->insert_id,'INSERT','Zarejestrowano '.count($dzien)." dni.",0));
      else echo json_encode(array($mysqli->error,0));
      $mysqli->close();
    } else {
      $kiedy = test_req( "kiedy" );
      $t_diff = (new DateTime())->getTimestamp() - $kiedy/1000;

      // session_start();
      $suma_czasu = 0;
      //pobranie danych o sumie czasu pracy w danym dniu
      $query = "SELECT SUM( czas ) as suma FROM  `$table` WHERE data = $kiedy AND user_id = $kto";
      $result = $mysqli->query($query);
      if($row = $result->fetch_assoc())
        $suma_czasu = $row['suma'];
      
      //odjecie czasu edytowanego wpisu
      $czas_id = 0;
      $query = "SELECT czas FROM  `$table` WHERE data = $kiedy AND user_id = $kto AND id = $id";
      $result = $mysqli->query($query);
      if($result)
        if($row = $result->fetch_assoc())
          $czas_id = $row['czas'];
    
      $suma_czasu -= $czas_id;

      //pobranie danych o sumie czasu pracy w dla danego zadania
      $query = "SELECT SUM( czas )/60 as czas FROM  `$table` WHERE zadanie = $zadanie;";
      
      $result = $mysqli->query($query);
      if($row = $result->fetch_assoc()){
        $czas_wyk = $row['czas'];
      }
      
      if ($del){
        $query = "DELETE FROM `$table` WHERE `$table`.`id` = ".$id.";";
        if ($mysqli->query($query))
          echo json_encode(array('OK',$id,'DELETE','Karta skasowana',$suma_czasu));
        else echo json_encode(array($mysqli->error,0));		
      } else {
        
        $suma_czasu += $ile;
        if ($id != 'null' && $id != ''){
          $query = "UPDATE `$table` SET `user_id` = \"$kto\",`kat_id` = \"$co\", `czas` = \"$ile\", `data` = $kiedy, `zlecenie` = \"$zlecenie\", `opis` = \"$opis\", `ip` = \"$t_ip\", `zadanie` = $zadanie WHERE `$table`.`id` = ".$id.";";
          // echo $query;
          if ($mysqli->query($query)){
            if ($mysqli->affected_rows)
              echo json_encode(array('OK',$id,'UPDATE','Karta poprawiona',$suma_czasu, $czas_wyk));
            else
              echo json_encode(array('OK',$id,'UPDATE','Karta bez zmian',$suma_czasu, $czas_wyk));
          } else echo json_encode(array($mysqli->error,0));
        }
        else{
          $query = "INSERT INTO `$table`(`user_id`,`kat_id`, `czas`, `data`, `zlecenie`, `opis`, `zadanie`, `ip`) VALUES (\"$kto\",\"$co\",\"$ile\",$kiedy,\"$zlecenie\",\"$opis\",$zadanie,\"$t_ip\");";
          $czas_wyk += $ile/60;
          if ($mysqli->query($query))
            echo json_encode(array('OK',$mysqli->insert_id,'INSERT','Praca zarejestrowana',$suma_czasu, $czas_wyk));
          else echo json_encode(array($mysqli->error,0));
        }
      }
      $mysqli->close();
      $file = "log_kp.txt";
      file_put_contents($file, "data = ".date("c")."\n", FILE_APPEND | LOCK_EX);
      file_put_contents($file, "userid = ".$_SESSION["myuser"]["id"]."\n", FILE_APPEND | LOCK_EX);
      file_put_contents($file, $query ."\n", FILE_APPEND | LOCK_EX);
    }
    if (isset($_REQUEST["callback"]) || isset($_REQUEST["jsoncallback"]))
      echo ')';
  }
?>
