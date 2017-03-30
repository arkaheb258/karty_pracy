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
  if (isset($_REQUEST["dni"])){
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
    
    $kto = test_req( "kto", $_SESSION["myuser"]["id"]);
    $dni = test_req( "dni");
    $t_ip = getIP();
    $zadanie = test_req( "zadanie","NULL");
    $mysqli = new_polacz_z_baza();
    
    $dzien = explode ( "," , $dni);
    $query = "";
    foreach ($dzien as $d){
      $d = $d ."000";
      if ($query != "")
        $query .= ",";
      $query .= "(\"$kto\",480,$d,\"$zadanie\",\"$t_ip\")";
    }
    $query = "INSERT INTO `kart_pr_prace`(`user_id`,`czas`, `data`, `zadanie`, `ip`) VALUES " .$query .";";
    // echo $query;
    // if (false)
    if ($mysqli->query($query))
      echo json_encode(array('OK',$mysqli->insert_id,'INSERT','Zarejestrowano '.count($dzien)." dni.",0));
    else echo json_encode(array($mysqli->error,0));
    $mysqli->close();
    if (isset($_REQUEST["callback"]) || isset($_REQUEST["jsoncallback"]))
      echo ')';
    exit;
  }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl" lang="pl">
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7; IE=EmulateIE9" /> 
		<link rel="stylesheet" type="text/css" href="kopex.css" />
		<link rel="stylesheet" href="style.css" />
		<style>
			table{
				background-color:#fff;
			}
			.ui-autocomplete.ui-widget-content { background: white; }
			input {
				width: 20em;
			}
			select {
				width: 20em;
			}
			textarea{
				width : 75%;
			}
			.ui-menu { width: 150px; }
			
			.ui-widget-content {
				background: none;			
			}
		</style>
  <title>Urlop / L4</title>
  </head>
	<body style="background-image:url(images/logo_km100.png);background-repeat: no-repeat; ">
			<table style="margin:auto; width: 75%; max-width: 800px;">
				<colgroup>
					<col width="250">
					<col width="75%">
				</colgroup>
				<tbody>
					<tr><th>Od</th><td>
						<input type="text" id="data_od" class="datetimepicker od " name="od" /><br/>
					</td></tr>
					<tr><th>Do</th><td>
						<input type="text" id="data_do" class="datetimepicker do " name="do" /><br/>
					</td></tr>
					<tr>
						<th>Czynność:</th>
						<td><select id="zad" name="zad">
              <option value="null"></option>
              <option value="504">Urlop wypoczynkowy</option>
              <option value="507">L4</option>
            </select></td>
					</tr>
					<tr>
						<td></td>
						<td>
              <div id="urlop">Karta urlopowa</div>
							<div id="gotowe">Dodaj</div>
							<div id="close">Anuluj</div>
						</td>
					</tr>
				</tbody>
			</table>
	</body>
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="jquery-ui.min.js"></script>
	<script type="text/javascript" src="jquery.ui.datepicker-pl.js"></script>
	<script type="text/javascript" src="dialog.js"></script>
	<script type="text/javascript" src="swieta.php"></script>
	<script>
		if (typeof console === "undefined")
			console = {log:function(){}};
		else if (typeof console.log === "undefined")
			console.log = function(){};
			
    $.datepicker.regional['pl'] = {
      closeText: 'Zamknij',
      prevText: '&#x3c;Poprzedni',
      nextText: 'Następny&#x3e;',
      currentText: 'Dziś',
      monthNames: ['Styczeń','Luty','Marzec','Kwiecień','Maj','Czerwiec',
      'Lipiec','Sierpień','Wrzesień','Październik','Listopad','Grudzień'],
      monthNamesShort: ['Sty','Lu','Mar','Kw','Maj','Cze',
      'Lip','Sie','Wrz','Pa','Lis','Gru'],
      dayNames: ['Niedziela','Poniedzialek','Wtorek','Środa','Czwartek','Piątek','Sobota'],
      dayNamesShort: ['Nie','Pn','Wt','Śr','Czw','Pt','So'],
      dayNamesMin: ['N','Pn','Wt','Śr','Cz','Pt','So'],
      weekHeader: 'Tydz',
      dateFormat: 'yy-mm-dd',
      firstDay: 1,
      isRTL: false,
      showMonthAfterYear: false,
      yearSuffix: ''};
    $.datepicker.setDefaults($.datepicker.regional['pl']);

		var today = new Date();
    
    $('#gotowe').button().click(function(){send();});
    $('#urlop').button().click(function(){
      var obj = read();
      if (obj) {
        window.open("urlop_n.php?od="+obj.od_kiedy/1000+"&do="+obj.do_kiedy/1000,"_self");
      }
    });
    $('#close').button().click(function(){window.close();});
    
    $("#data_od").datepicker({minDate: -30, maxDate: 30}).datepicker('setDate', new Date());
    $("#data_do").datepicker({minDate: -30, maxDate: 30}).datepicker('setDate', new Date());

		function read(){
			var obj = {
        zadanie : $("#zad").val(),
        od_kiedy : $("#data_od").datepicker('getDate').getTime(),
        do_kiedy : $("#data_do").datepicker('getDate').getTime()
      };

      if (!obj.zadanie || obj.zadanie == 'null'){
        alert('Proszę wybra czynność');
        return null;
      }
      if (obj.od_kiedy > obj.do_kiedy){
        alert('Proszę podać prawidłowy przedział czasu');
        return null;
      }
      var out="";
      for (var d=obj.od_kiedy; d<=obj.do_kiedy; d+=86400000){
        var temp_date = new Date();
        temp_date.setTime(d);
        if(!swieta[temp_date.getMonth()+1] || !swieta[temp_date.getMonth()+1][temp_date.getDate()] || obj.od_kiedy == obj.do_kiedy)
        if((temp_date.getDay() > 0 && temp_date.getDay() < 6) || obj.od_kiedy == obj.do_kiedy){
          if (out != "")
            out+=",";
          out+=(d/1000);
        }
      }
      obj.dni = out;
      return obj;
    }
      
		function send(){
      var obj = read();
      console.log(obj);
      if (obj)
      $.ajax({
        url: 'karta_l4.php?callback=?',
        dataType: 'json',
        type: 'POST',
        data: obj,
        timeout: 2000
      }).success(function(obj){
        if (obj[0]=="OK"){
          if (window.opener)
            window.opener.location.reload();
          if (confirm(obj[3]+'\n\rCzy chcesz zamknąć kartę ?'))
            window.close();
        } else
          alert('Błąd skryptu.');
      }).fail( function() {
        alert('Błąd serwera kart pracy.');
      });
		};
	</script>
</html>