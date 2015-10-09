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
		$def_days_back = 30;
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
			// $kto_nr = test_req( "kto_nr" );
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
			
			$table = "wwwkop.dbo.kart_pr_prace";
			
			$conn = polacz_sql();
			
			if ($dni != ""){
				$dzien = explode ( "," , $dni);
				$query = "";
				foreach ($dzien as $d){
					$d = $d ."000";
					if ($query != "")
						$query .= ",";
					$query .= "('$kto','$co','$ile',$d,'$zlecenie','$opis','$t_ip')";
				}
				$query = "INSERT INTO $table(user_id,kat_id, czas, data, zlecenie, opis, ip) VALUES " .$query .";SELECT SCOPE_IDENTITY() as newId;";
				
				$result = sqlsrv_query($conn, $query);
				if ($result && sqlsrv_rows_affected($result) > 0) {
					sqlsrv_next_result($result); 
					$row = get_row_sql($result);
					echo json_encode(array('OK',$row["newId"],'INSERT','Zarejestrowano '.count($dzien)." dni.",0));
				} else {
					echo json_encode(sqlsrv_errors());
				}
			} else {
				$kiedy = test_req( "kiedy" );
				
				$t_diff = (new DateTime())->getTimestamp() - $kiedy/1000;

				$suma_czasu = 0;
				//pobranie danych o sumie czasu pracy w danym dniu
				$query = "SELECT SUM( czas ) as suma FROM  $table WHERE data = $kiedy AND user_id = $kto";
				$result = sqlsrv_query($conn, $query);
				if($row = get_row_sql($result))
					$suma_czasu = $row['suma'];
				
				//odjecie czasu edytowanego wpisu
				$czas_id = 0;
				$query = "SELECT czas FROM  $table WHERE data = $kiedy AND user_id = $kto AND id = $id";
				$result = sqlsrv_query($conn, $query);
				if($result)
					if($row = get_row_sql($result))
						$czas_id = $row['czas'];
			
				$suma_czasu -= $czas_id;

				//pobranie danych o sumie czasu pracy w dla danego zadania
				$query = "SELECT SUM( czas )/60 as czas FROM  $table WHERE zadanie = $zadanie;";
				$result = sqlsrv_query($conn, $query);
				if($row = get_row_sql($result)){
					$czas_wyk = $row['czas'];
				}
				
				if ($del){
					$query = "DELETE FROM $table WHERE $table.id = ".$id.";";
					$result = sqlsrv_query($conn, $query);
					if ($result && sqlsrv_rows_affected($result) == 1) {
						echo json_encode(array('OK',$id,'DELETE','Karta skasowana',$suma_czasu));
					} else {
						echo json_encode(sqlsrv_errors());
					}
				} else {
					$suma_czasu += $ile;
					if ($id != 'null' && $id != ''){
						$query = "UPDATE $table SET user_id = '$kto', kat_id = '$co', czas = '$ile', data = $kiedy, zlecenie = '$zlecenie', opis = '$opis', ip = '$t_ip', zadanie = $zadanie WHERE $table.id = ".$id.";";
						$result = sqlsrv_query($conn, $query);
						if ($result){
							if (sqlsrv_rows_affected($result) == 1) {
								echo json_encode(array('OK',$id,'UPDATE','Karta poprawiona',$suma_czasu, $czas_wyk));
							} else {
								echo json_encode(array('OK',$id,'UPDATE','Karta bez zmian',$suma_czasu, $czas_wyk));
							}
						} else {
							echo json_encode(sqlsrv_errors());
						}
					}
					else{
						$query = "INSERT INTO $table (user_id, kat_id, czas, data, zlecenie, opis, zadanie, ip) VALUES ('$kto','$co','$ile',$kiedy,'$zlecenie','$opis',$zadanie,'$t_ip');SELECT SCOPE_IDENTITY() as newId;";
						$czas_wyk += $ile/60;

						$result = sqlsrv_query($conn, $query);
						if ($result && sqlsrv_rows_affected($result) == 1) {
							sqlsrv_next_result($result); 
							$row = get_row_sql($result);
							echo json_encode(array('OK',$row["newId"],'INSERT','Praca zarejestrowana',$suma_czasu, $czas_wyk));
						} else {
							echo json_encode(sqlsrv_errors());
						}
					}
				}
				$file = "log_kp.txt";
				file_put_contents($file, "data = ".date("c")."\n", FILE_APPEND | LOCK_EX);
				file_put_contents($file, "userid = ".$_SESSION["myuser"]["id"]."\n", FILE_APPEND | LOCK_EX);
				file_put_contents($file, $query ."\n", FILE_APPEND | LOCK_EX);
			}
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
  <title>Karty pracy TR</title>
  </head>
	<body>
		<div style="float:right;font-size: 0.8em;margin-right: 1em;">Zalogowano jako: <?php echo $_SESSION["myuser"]["nazwa"]; ?></div><br/>
		<form action="baza_karta1.php" style="background-image:url(images/logo_km100.png);background-repeat: no-repeat; ">
			<table style="margin:auto; width: 75%; max-width: 800px;">
				<colgroup>
					<col width="250">
					<col width="100%">
				</colgroup>
				<tbody>
					<tr><th>Nazwisko i Imię:</th><td><input id="kto_u" name="kto_u" /></td></tr>
					<tr class="l4"><th>Od</th><td>
						<input type="text" id="data_od" class="datetimepicker od " name="od" /><br/>
					</td></tr>
					<tr class="l4"><th>Do</th><td>
						<input type="text" id="data_do" class="datetimepicker do " name="do" /><br/>
					</td></tr>
					<tr class="n_l4">
						<th>Dzień</th>
						<td><input type="text" id="data" class="datetimepicker od " name="data" /><br/></td>
					</tr>
					<tr>
						<th>Czynność:</th>
						<td><select id="zad" name="zad" style="width:100%;"><option value="null"></option></select></td>
					</tr>
					<tr>
						<th>Opis czynności:</th>
						<td><textarea type="text" id="zad_opis" name="zad_opis" rows="5" disabled style="width:100%;"></textarea></td>
					</tr>
					<tr>
						<th>Wykonano pracę dla działu:</th>
						<td><select id="dzial" name="dzial"><option value="null"></option></select></td>
					</tr>
					<tr class="n_l4">
						<th><label for="czas">Czas pracy w godzinach:</label></th>
						<td><input id="czas" name="czas" /></td>
					</tr>
					<tr class="n_l4">
						<th><label for="zlec">Numer zlecenia / zamówienia:</label></th>
						<td><input type="text" id="zlec" name="zlec" /></td>
						<td>PNU Projekt nr <select id="pnu"><option value="null"></option></select>
						<br/>Zadanie <input type="text" id="pnu_zad" name="pnu_zad" /></td>
						<td>Ostatnio wpisywane:<select id="zlec_last"></select></td>
					</tr>
					<tr><th>Kategoria prac:</th><td id="kategorie"></td></tr>
					<tr><td colspan="3">Opis:<br/><textarea id="opis" rows="6" name="opis" style="width:100%;"></textarea></td></tr>
					<tr class="n_l4"><td>Ostatnio wpisywane:<select id="opis_last"><option value="null"></option></select><br/><div id="opis_sort">Sortuj alfabetycznie</div></td></tr>
					<tr><td colspan="3"></td></tr>
					<tr>
						<td colspan="1"></td>
						<td colspan="2" style="text-align:right;">
							<div id="copy">Skopiuj</div>
							<div id="gotowe">Dodaj</div>
							<div id="del">Skasuj</div>
							<div id="close">Zamknij</div>
						</td>
					</tr>
				</tbody>
			</table>
<!--			<input type="submit" value="Gotowe" onsubmit="return validateForm()" method="post"> -->
		</form>
	</body>
<?php
	if (isset($_REQUEST["user_id"]) && $_SESSION["myuser"]["kart_perm"] != "0") 
		echo '<script type="text/javascript" src="baza_karta1.php?user_id='.$_REQUEST["user_id"].'"></script>';
	else 
		echo '<script type="text/javascript" src="baza_karta1.php"></script>';
?>
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="jquery-ui.min.js"></script>
	<script type="text/javascript" src="jquery.ui.datepicker-pl.js"></script>
	<script type="text/javascript" src="jquery-ui-timepicker-addon.js"></script>
	<script type="text/javascript" src="globalize.js"></script>
	<script type="text/javascript" src="globalize.culture.de-DE.js"></script>
	<script type="text/javascript" src="pnu.js"></script>

	<script type="text/javascript" src="date.js"></script>
	<script>
	
		var days_back = <?php echo $def_days_back;?>;
			
		_user_id = <?php echo $_SESSION["myuser"]["id"];?>;
		_prac_id = '<?php if (isset($_REQUEST["id"])) echo $_REQUEST["id"]; else echo 'null';?>';
		_kat_id = <?php if (isset($_REQUEST["id_k"])) echo $_REQUEST["id_k"]; else echo 'null';?>;
		_dzia_id = <?php if (isset($_REQUEST["id_d"])) echo $_REQUEST["id_d"]; else echo 'null';?>;
		_copy_id = '<?php if (isset($_REQUEST["copy_id"])) echo $_REQUEST["copy_id"]; else echo 'null';?>';
		_l4 = <?php if (isset($_REQUEST["l4"])) echo "true"; else echo 'null';?>;
		var sum_user_id = <?php if (isset($_REQUEST["user_id"])) echo $_REQUEST["user_id"]; else echo 'null';?>;

		if (typeof console === "undefined")
			console = {log:function(){}};
		else if (typeof console.log === "undefined")
			console.log = function(){};
			
		// if (( false 
			// || _user_id == 53 	//Suiski
			// || _user_id == 60 	//Krystian
			// || _user_id == 28 	//Drejka
			// || _user_id == 52 	//Skwarek
			// || _user_id == 54 	//Szweda
			// || _user_id == 25 	//Ciesielski
			// || _user_id == 59 	//Kostka
			// || _user_id == 51 	//Andrzej Skrzypiec
			// || _user_id == 27	//Ćwiklicki Zbigniew
			// || _user_id == 40 	//Marek Miziak
			// || _user_id == 33	//Piotr Janusz
			// ) && (sum_user_id == null))
			// days_back += 20;
		if (_user_id == 1){	days_back += 340; }
//		if ("<?php echo $_SESSION["myuser"]["dzial"];?>" == "TR-1")
//			$("#zad").parent().parent().show();
			
		var today = new Date();
		
		var prev = new Date();
		for (var i=1; i<=days_back;i++){
			prev.setDate(today.getDate() - i);
			if (prev.getDay() == 0 || prev.getDay() == 6)
				days_back++;
			else if(swieta[prev.getMonth()+1] && swieta[prev.getMonth()+1][prev.getDate()])
				days_back++;
//			else continue;
//			console.log(free_days);
//			console.log(prev);
		}
		
		// prev.setHours(0);
		// prev.setMinutes(0);
		// prev.setSeconds(0);
		// prev.setMilliseconds(0);
		
		var urlop_2014 = 0;
		var urlop_2013 = 0;
		var urlop = 0;
		for (var k in karty){
			var karta = karty[k];
			if (karta.kat_id == 545){
				prev.setTime(karta.data/1);
				if (prev.getFullYear() == today.getFullYear() && prev.getTime() < today.getTime())
					urlop++;
				if (prev.getFullYear() == 2013)
					urlop_2013++;
				if (prev.getFullYear() == 2014)
					urlop_2014++;
			}
		}
		console.log("W bierzącym roku wykorzystano: "+urlop+" dni urlopu.");
//		console.log("W 2013 roku wykorzystano: "+urlop_2013+" dni urlopu.");

		var zlec_last = [];
		var opis_last = [];
		var opis_last_arch = [];
		
		$(function() {
			if (!_l4){
				$(".l4").hide();
			} else {
				$(".n_l4").hide();
				$("#copy").after('<div id="urlop" style="display:none;">Karta urlopowa</div>')
			}
		
			Globalize.culture("de-DE");
			$.widget( "ui.timespinner", $.ui.spinner, {
				options: {
					// seconds
					step: 15 * 60 * 1000,
					min: +Globalize.parseDate( "00:15"),
					max: +Globalize.parseDate( "12:00"),
					// hours
					page: 4
				},

				_parse: function( value ) {
					if ( typeof value === "string" ) {
						// already a timestamp
						if ( Number( value ) == value ) {
						  return Number( value );
						}
						return +Globalize.parseDate( value );
					}
					return value;
				},

				_format: function( value ) {
					return Globalize.format( new Date(value), "t" );
				}
			});
			
			if (Math.abs((new Date()).getTime()/1000 - serv_epoch) > 24*60*60)
				alert("Proszę ustawić prawidłową datę.");

			$("#czas").timespinner().timespinner( "value", "01:00" );
//			$("#czas").parent().css("width","100%");
//			$("#czas").spinner({
//				min: 0.25,
//				max: 12,
//				step: 0.25,
//				numberFormat: "n"
// 		}).spinner( "value", 1 );

			$('#gotowe').button().click(function(){send(false);});
			$('#urlop').button().click(function(){send(false,true);});
			$('#close').button().click(function(){window.close();});
			$('#del').button().show().click(function(){
				if (confirm('Czy napewno chcesz skasować kartę ?')){
					send(true);
				}
			});

//			$("#data").datepicker({ minDate: -1,maxDate: 0});
//			$("#data").datepicker();
			// _od.datetimepicker({
				// timeText :  "Czas",
				// hourText : "godz.",
				// minuteText :  "min.",
				// currentText :  "Teraz",
				// closeText :  "Gotowe"
			// });
//			_od.datetimepicker('setDate', new Date());
			zadania_sort = [];
			for (var z in zadania){
				zadania_sort.push(zadania[z]);
			}
			zadania_sort.sort(function(a,b) { if (a.nazwa > b.nazwa ) return 1; if (a.nazwa < b.nazwa ) return -1; return 0;} );
			// for (var z in zadania){
				// if (zadania[z].termin && (zadania[z].termin < ((new Date()).getTime() - 24*60*60*1000)))
					// continue;
				// if ((zadania[z].aktywny == "1") && (zadania[z].deleted == 0))
					// $('#zad').append('<option value=\"'+zadania[z].id+'\" title="'+zadania[z].opis+'">'+zadania[z].nazwa+'</option>');
			// }
			for (var z in zadania_sort){
				var sort_z = zadania_sort[z];
				// console.log(sort_z);
				// ukrycie zadań z przekroczonym terminem
				// if (sort_z.termin && (sort_z.termin < ((new Date()).getTime() - 24*60*60*1000))){
					// console.log(sort_z);
					// continue;
				// }
				if ((sort_z.aktywny == "1") && (sort_z.deleted == 0)){
					var t_title = sort_z.nazwa;
					var t_par_id = sort_z.par_id;
					if (!t_par_id) {
						t_title = sort_z.typ + ' ' + sort_z.zlecenie + " -> " + t_title;
					} else {
						do {
							t_title = foldery[t_par_id].nazwa + " -> " + t_title;
							t_par_id = foldery[t_par_id].par_id;
						} while (t_par_id > 3);
						// t_title = foldery[t_par_id].nazwa + " -> " + t_title;
					}
					// t_title = zadania[t_par_id].nazwa + " -> " + t_title;
					console.log(sort_z.nazwa, t_par_id, t_title);

					// var opis = sort_z.nazwa+' ('+sort_z.typ+' '+sort_z.zlecenie+')';
					var opis = t_title;
					if (sort_z.rbh > 0)
						opis += ' (limit = ' + sort_z.rbh +' rbh)';
					// oznaczenie zadań z przekroczonym terminem
					console.log(sort_z);
					
					
					if (sort_z.termin && (sort_z.termin < ((new Date()).getTime() - 24*60*60*1000))){
						$('#zad').append('<option value=\"'+sort_z.id+'\" title="'+sort_z.opis+'" style="color:brown;">'+opis+'</option>');
					} else {
						$('#zad').append('<option value=\"'+sort_z.id+'\" title="'+sort_z.opis+'">'+opis+'</option>');
					}
				} else {
					// console.log(sort_z);
				}
				// console.log(sort_z.termin);
				// zadania[$('#zad').val()].rbh)
			}

			// for (var z in zadania_sort){console.log(zadania_sort[z]);}
			
			function zad_change(){
//				console.log($(this).val());
//				console.log(zadania[$(this).val()]);
				var temp_dz = $('#dzial').val();
				if ($('#zad').val() != 'null'){
					var _zad = zadania[$('#zad').val()];
					console.log(_zad);
					$('#zad_opis').val(_zad.opis);
					$('#dzial').val(_zad.dzial_zlec).prop('disabled',true);
					$('#zlec').val(_zad.zlecenie).prop('disabled',true);
					$('#zlec_last').parent().hide();
					// if ($('#zlec').val().indexOf("PNU Projekt nr ") == 0){
					if (_zad.typ == "PNU"){
//						console.log("test");
						$('#kat_4_401').hide();
//						$('#kat_4_inne > *').hide();
//						$('#kat_4_451').show();
//						$("label[for='kat_4_451']").show();

						$('#kat_4_454').hide();
						$("label[for='kat_4_454']").hide();
						$('#kat_4_456').hide();
						$("label[for='kat_4_456']").hide();

						$('#kat_4_402').show();
						$("#pnu").parent().show();
						$("#pnu_zad").parent().show();
						$("#zlec").parent().hide();
						var t_pnu = _zad.zlecenie.split('/');
						$("#pnu").val(t_pnu[0]).prop('disabled', 'disabled');
						if (t_pnu[1]) {
							$("#pnu_zad").val(t_pnu[1]).prop('disabled', 'disabled');
						}
					} else {
						$('#kat_4_401').show();
						$('#kat_4_inne > *').show();
						$('#kat_4_402').hide();
						$("#pnu").parent().hide();
						$("#pnu_zad").parent().hide();
						$("#zlec").parent().show();
						$("#pnu").prop('disabled', false);
						$("#pnu_zad").prop('disabled', false);
					}
				} else {
					$('#zad_opis').val('');
					$('#dzial').prop('disabled',false);
					$('#zlec').prop('disabled',false);
					$('#zlec_last').parent().show();
					$('#kat_4_401').show();
					$('#kat_4_402').show();
					$('#kat_4_inne > *').show();
					$("#pnu").parent().hide();
					$("#pnu_zad").parent().hide();
					$("#zlec").parent().show();
					$("#pnu").prop('disabled', false);
					$("#pnu_zad").prop('disabled', false);
				}
				if (temp_dz != $('#dzial').val())
					dzial_change();
			};
			$('#zad').change(zad_change);
			for (var d in dzialy){
				$('#dzial').append('<option value=\"'+d+'\" title="'+dzialy[d].opis+'">'+dzialy[d].nazwa+'</option>');
				$('#kategorie').append('<div id="kat_'+d+'" class="kategorie"></div>');
			}
			$('.kategorie').hide();
			function dzial_change(){
				clear_err();
				$('.kategorie').hide();
				$('#kat_5').show();
				$('#kat_'+$("#dzial option:selected").val()).show();
				$("#kategorie input:checked").attr('checked',null);
				if ($("#dzial option:selected").val() == 5) {
					$('#urlop').show();
					$('#kat_5_545').show();
					$("label[for='kat_5_545']").show();
					$('#kat_5_548').show();
					$("label[for='kat_5_548']").show();
					$('#kat_5_558').show();
					$("label[for='kat_5_558']").show();
//					console.log("urlop");//.show();)
				} else {
					$('#urlop').hide();
					$('#kat_5_545').hide();
					$("label[for='kat_5_545']").hide();
					$('#kat_5_548').hide();
					$("label[for='kat_5_548']").hide();
					$('#kat_5_558').hide();
					$("label[for='kat_5_558']").hide();
//					console.log("nie urlop");//.show();)
				}
				if (!_l4){	//ukrycie urlopu i L4
					$("#kat_5_545").hide();
					$("label[for='kat_5_545']").hide();	
					$("#kat_5_548").hide();
					$("label[for='kat_5_548']").hide();
				}
				
//				alert($("#dzial option:selected").val());
			}
			$('#dzial').change(dzial_change);
			
			$("#pnu").parent().hide();
			$("#pnu_zad").parent().hide();
			for (var p in pnu){
				$("#pnu").append('<option value=\"'+p+'\" title="'+pnu[p].opis+'">'+pnu[p].nr+'</option>');
			}
			
			for (var k in kategorie){
				var kat2 = [];
				for (var k2 in kategorie[k])
				//sortowanie po nazwach kategorii
					kat2.push(kategorie[k][k2]);
					kat2.sort(function(a,b) { 
						if ((a.ma_podgr && b.ma_podgr) || (!a.ma_podgr && !b.ma_podgr))
							return a.nazwa > b.nazwa;
						else if (a.ma_podgr)
							 return false;							
					} );
					
//console.log(kat2);
//console.log(k);
//console.log(kategorie[k]);
//console.log(kategorie[k]);
//				for (var k2 in kategorie[k]){

				for (var k3 in kat2){
					var k2 = kat2[k3].id;
					if (_l4 && !(kategorie[k][k2].long_time/1)) continue;
					if (k2 == 450 && _user_id != 40 && _user_id != 33) continue;	//projekty tylko dla Marka i Janusza z TP
					if (kategorie[k][k2].ma_podgr){
						$('#kat_'+k).append('<div id="kat_'+k+'_'+kategorie[k][k2].id+'"><button>Rozwiń</button><span class="def" title="'+kategorie[k][k2].opis+'">'+kategorie[k][k2].nazwa+'</span><div class="sub" style="margin-left: 2em"></div></div>');
						$('#kat_'+k+'_'+kategorie[k][k2].id+' > .sub').hide();
						$('#kat_'+k+'_'+kategorie[k][k2].id+' > button').button({icons: {primary: "ui-icon-circle-plus"},text: false}).click(function(event){
						var icons = $(this).button( "option", "icons" );
						if (icons.primary == "ui-icon-circle-plus")
							$(this).button( "option", "icons", { primary: "ui-icon-circle-minus"});
						else
							$(this).button( "option", "icons", { primary: "ui-icon-circle-plus"});
							event.preventDefault();
							$('.sub',$(this).parent()).toggle();
						});
						for (var k3 in kategorie[k][k2].ma_podgr){
							$('#kat_'+k+'_'+kategorie[k][k2].id+' > .sub').append('<input type="radio" id="kat_'+k+'_'+k3+'" name="prace" value="'+k3+'" style="width:2em;" /><label for="kat_'+k+'_'+k3+'">'+kategorie[k][k2].ma_podgr[k3].nazwa+'<br/></label>');
						}
					}
					else {
						if (!$('#kat_'+k+'_inne').length)
							$('#kat_'+k).append('<br/><div id="kat_'+k+'_inne"></div>');
						$('#kat_'+k+'_inne').append('<input type="radio" id="kat_'+k+'_'+kategorie[k][k2].id+'" name="prace" value="'+kategorie[k][k2].id+'" style="width:2em;" /><label for="kat_'+k+'_'+kategorie[k][k2].id+'" title="'+kategorie[k][k2].opis+'">'+kategorie[k][k2].nazwa+'<br/></label>');
					}
				}
				if (k==5 && <?php if ($_SESSION["myuser"]["id"] == 38) { echo 'true'; } else { echo 'false'; }?>) {
					$('#kat_'+k+'_inne').append('<input type="radio" id="kat_'+k+'_600" name="prace" value="600" style="width:2em;" /><label for="kat_'+k+'_600" title="Czytanie Koranu">Czytanie Koranu</label><br/>');
				}
			}
			for (k in karty){
				var opis = karty[k].opis_p;//karty[k].
				if (opis != '' && $.inArray(opis, opis_last) == -1)
					opis_last.push(opis);
				if ($.inArray(karty[k].zlec, zlec_last) == -1)
					zlec_last.push(karty[k].zlec);
			}
			zlec_last.sort();
			for (z in zlec_last)
				$('#zlec_last').append('<option>'+zlec_last[z]+'</option>');
			
			opis_last_arch = opis_last.slice(0);

			// if ((user_info.dzial == "TR-1" && user_info.sekcja == "AU") 
			if ((user_info.dzial == "TR-1") 
			|| (user_info.dzial == "TR-2")
			|| (user_info.dzial == "TR-3")
			|| (user_info.dzial == "TP")
			|| (true)
			|| (user_info.id == 1)
			){
				if (!_l4)
					$("#dzial").parent().parent().hide();
				$("#zlec").parent().parent().hide();
			}

			
			$('#opis_sort').button().click(function(){
				$('#opis_last').empty();
//				$('#opis_last').append('<option value="null"></option>');
				opis_last = opis_last_arch.slice(0)
				if ($('#opis_sort').button( "option", "label") == "Sortuj alfabetycznie"){
					opis_last.sort();
					$('#opis_sort').button( "option", "label","Sortuj wg czasu");
				} else
					$('#opis_sort').button( "option", "label","Sortuj alfabetycznie");
				for (o in opis_last){
					var opis = opis_last[o];
					if (opis.length > 22)
						opis = opis.substring(0, 25)+"...";
					$('#opis_last').append('<option value="'+o+'" title="'+opis_last[o]+'">'+opis+'</option>');
				}
			});
			
			for (o in opis_last){
				var opis = opis_last[o];
				if (opis.length > 22)
					opis = opis.substring(0, 25)+"...";
				$('#opis_last').append('<option value="'+o+'" title="'+opis_last[o]+'">'+opis+'</option>');
			}

			$('#zlec_last').change(function(){
//				console.log("#zlec_last");
				var opis = $(this).val();
				$('#zlec').val(opis);
//				console.log(opis);
				if (opis.indexOf("PNU Projekt nr ") == 0)
					$("#pnu").val(opis.substring(15));
				else
					$("#pnu").val("null");
			});
			$('#opis_last').change(function(){
				$('#opis').val(opis_last[$(this).val()]);
			});
//console.log(opis_last);
//$('#zlec_last').hide();
			$('input').change(function() { clear_err(); });
			$('select').click(function() { clear_err(); });
			$('input[name=prace]').change(function() { 
				if( $(this).val() > 430 && $(this).val() < 450) {
					$("#pnu").parent().show();
					$("#zlec").parent().hide();
				} else {
					$("#pnu").parent().hide();
					$("#zlec").parent().show();
				}
			});
			$('#czas').parent().click(function() { clear_err(); });
			$('#data').click(function() { clear_err(); });
			$('textarea').keypress(function() { clear_err(); });
			
			$("#data_od").datepicker({minDate: -(days_back+30), maxDate: (days_back+30)}).datepicker('setDate', new Date()).click(function() { clear_err(); });
			$("#data_do").datepicker({minDate: -(days_back+30), maxDate: (days_back+30)}).datepicker('setDate', new Date()).click(function() { clear_err(); });


//			var temp_date = new Date();
			if (_copy_id != null && _copy_id != 'null'){
				var obj = find_id(_copy_id);
//				console.log(obj);
				if (obj){
					$('#kto_u').val('<?php echo $_SESSION["myuser"]["nazwa"];?>').attr('disabled', 'disabled');
					if (today.getDate() < 10) {
						$("#data").datepicker({ minDate: -days_back,maxDate: 0}).datepicker('setDate', new Date());
					} else {
						console.log('-1M');
						$("#data").datepicker({ minDate: '-1M', maxDate: 0}).datepicker('setDate', new Date());
					}
					// '+1M +10D'
					$('#czas').timespinner( "value",(obj.ile-(obj.ile%60))/60+":"+(obj.ile%60));
					
					$('#dzial').val(obj.id_dzial);
					$('#kat_'+obj.id_dzial).show();
					$('input[name=prace][value='+obj.kat_id+']').attr('checked','checked');
					if ($('input[name=prace][value='+obj.kat_id+']').parent().hasClass('sub'))
						$('input[name=prace][value='+obj.kat_id+']').parent().show();
					$('#opis').val(obj.opis_p);
					$('#zlec').val(obj.zlec);
					
					$('#zad').val(obj.zad);
					zad_change();
					if (obj.kat_id > 430 && obj.kat_id < 450){
						if (obj.zlec.indexOf("PNU Projekt nr ") == 0){
							// console.log(obj.zlec.substring(15));
							if (!obj.zad) {
								var t_pnu = obj.zlec.substring(15).split('/');
								$("#pnu").val(t_pnu[0]);
								$("#pnu_zad").val(t_pnu[1]);
							}
							$("#pnu").parent().show();
							$("#zlec").parent().hide();
						}					
					}
					<?php if (isset($_REQUEST["day"])) echo 'var xday = new Date(); xday.setTime('.$_REQUEST["day"].'); $("#data").datepicker("setDate", xday);';?>
					<?php if (isset($_REQUEST["add"])) echo '$("#gotowe").click();';?>
				}				
			}
			
			if (_prac_id != null && _prac_id != 'null'){
				$('#copy').button().click(function(){
					window.location.href = window.location.href.replace("?id=","?copy_id=").replace("&id=","&copy_id=");
				});
				var obj = find_id(_prac_id);
				console.log(obj);
				if (obj){
					var temp_date = new Date();
					temp_date.setTime(obj.data);
					if (new Date() - temp_date > 86400000*(days_back+1)){
						$('#gotowe').hide();
						$('#del').hide();
						$("#data").datepicker();
						disable_all();
						$('#czas').timespinner('option', "max", +Globalize.parseDate( "23:00"));
					} else {
						$("#data").datepicker({ minDate: -days_back, maxDate: 0});
					}
					$('#kto_u').val(obj.kart_user).attr('disabled', 'disabled');
					if (temp_date.getHours() == 23)
						temp_date.setHours(temp_date.getHours()+1);
					
					$("#data").datepicker('setDate',temp_date);
					$('#czas').timespinner( "value",(obj.ile-(obj.ile%60))/60+":"+(obj.ile%60));
					
					$('#dzial').val(obj.id_dzial);
					$('#kat_'+obj.id_dzial).show();
					$('input[name=prace][value='+obj.kat_id+']').attr('checked','checked');
					if ($('input[name=prace][value='+obj.kat_id+']').parent().hasClass('sub'))
						$('input[name=prace][value='+obj.kat_id+']').parent().show();
					$('#opis').val(obj.opis_p);
					$('#zlec').val(obj.zlec);
					
					$('#zad').val(obj.zad);
					zad_change();
					
					if (obj.kat_id > 430 && obj.kat_id < 450){
						if (obj.zlec.indexOf("PNU Projekt nr ") == 0){
							// console.log(obj.zlec.substring(15));
							if (!obj.zad) {
								var t_pnu = obj.zlec.substring(15).split('/');
								$("#pnu").val(t_pnu[0]);
								$("#pnu_zad").val(t_pnu[1]);
							}
							$("#pnu").parent().show();
							$("#zlec").parent().hide();
						}					
					}
					$('#gotowe > .ui-button-text').text( "Edytuj" );
				} else {
					alert("Brak karty o podanym ID");
					window.close();
				}
			} else {
				$('#copy').hide();
				$("#data").datepicker({ minDate: -days_back,maxDate: 0});
				$('#kto_u').val('<?php echo $_SESSION["myuser"]["nazwa"];?>').attr('disabled', 'disabled');
				if (_kat_id && _dzia_id){
					$('#dzial').val(_dzia_id);
					$('#kat_'+_dzia_id).show();
					$('input[name=prace][value='+_kat_id+']').attr('checked','checked');
					if ($('input[name=prace][value='+_kat_id+']').parent().hasClass('sub'))
						$('input[name=prace][value='+_kat_id+']').parent().show();
				}
				$("#data").datepicker('setDate', new Date());
				<?php if (isset($_REQUEST["day"])) echo 'var xday = new Date(); xday.setTime('.$_REQUEST["day"].'); $("#data").datepicker("setDate", xday);';?>
				$('#del').hide();
			}
			
			function disable_all(){
				$("#czas").timespinner("disable");
				$("#data").prop('disabled', 'disabled');
				$("#dzial").prop('disabled', 'disabled');
				$("textarea").prop('disabled', 'disabled');
				$("input").each(function(){
					$(this).prop('disabled','disabled');
				});
				$("#zlec_last").prop('disabled', 'disabled');
				$("#opis_last").prop('disabled', 'disabled');
				$("#pnu").prop('disabled', 'disabled');
			}
			
//		console.log(sum_user_id);
//		console.log(_user_id);
//		console.log(_prac_id);
			if (sum_user_id && _user_id == 1 && karty[0])
				$('#kto_u').val(karty[0].kart_user).attr('disabled', 'disabled');
			
			if (sum_user_id != null && _user_id != 1 && sum_user_id != _user_id){
				$('#copy').hide();
				$('#gotowe').hide();
				$('#del').hide();
				disable_all();
			}

		});


		function clear_err(){
			$('input').blur(function() {$(this).css("background-color",'');});
			$('select').blur(function() {$(this).css("background-color",'');});
			$('input').css("background-color",'');
			$('select').css("background-color",'');
			$('#kategorie').css("background-color",'');
			$('#opis').css("background-color",'');
		}

		function find_id(id){
			for (k in karty)
				if (karty[k].prac_id == id)
					return karty[k];
			return null;
		}

		function min_to_h2(min){
			if (min <= 0)
				return "0h";
			var m = min%60;
			var h = (min-m)/60;
			var out = "";
			if (h>0){
				out += h+"h";
				if (m>0)
				out += " ";
			}
			if (m>0)
				out += m+"min.";
			return out;
		}
		
// console.log(user_info);
		function send(del, urlop){
// console.log(user_info);
			clear_err();
			var obj = {};
			obj.id = _prac_id;
			obj.kto = _user_id;
			if (sum_user_id && _user_id == 1)
				obj.kto = sum_user_id;
			obj.dzial = $('#dzial').val();
			if (!_l4){
				obj.kiedy = $("#data").datepicker('getDate').getTime();
//console.log(($('#czas').timespinner( "value") - 	+Globalize.parseDate( "00:00"))/60000);
//			obj.ile = $('#czas').val().replace(",",".")*60;
				obj.ile = ($('#czas').timespinner( "value") - +Globalize.parseDate( "00:00"))/60000;
				obj.zlec = $('#zlec').val();
			} else {
				obj.od_kiedy = $("#data_od").datepicker('getDate').getTime();
				obj.do_kiedy = $("#data_do").datepicker('getDate').getTime();
			}
			obj.co = $('input[name=prace]:checked').val();
			obj.opis = $('#opis').val();

			if (del){
				obj.del = del;
			} else {
				var now = new Date();
				now = now.getTime()/1000;
				
				if (obj.dzial == null || obj.dzial == "" || obj.dzial == "null"){
					$('#dzial').css("background-color",'red');
					alert('Proszę wybrać dział');
					return;
				}
				
				if (!obj.co){
					$('#kategorie').css("background-color",'red');
					alert('Proszę wybrać kategorię prac');
					return;
				}
				
				var temp = null;
				
				if (_l4){
					if (obj.od_kiedy > obj.do_kiedy){
						$('#data_od').css("background-color",'red');
						$('#data_do').css("background-color",'red');
						alert('Proszę podać prawidłowy przedział czasu');
						return;
					}
					if ((obj.kto == 43)
					|| (obj.kto == 60)){
						obj.ile = 420;
					} else
						obj.ile = 480;
					obj.zlec = "";
					var out="";
					console.log(obj);
					if (!urlop)
					for (var d=obj.od_kiedy; d<=obj.do_kiedy; d+=86400000){
						for (var k in karty){
							var karta = karty[k];
							//dodać sprawdzenie czy w danych dniach nie występują już jakieś roboty
							if (d == karta.data/1){
								alert("W podanym przedziale czasu zadeklarowano już pracę.");
								return;
							}
						}
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
				} else {
					obj.zad = $('#zad').val();
					// if ()
					// console.log(user_info.dzial);
					// if ((user_info.dzial == "TR-1" && user_info.sekcja == "AU")
					if ((user_info.dzial == "TR-1") 
					|| (user_info.dzial == "TR-2")
					|| (user_info.dzial == "TR-3")
					|| (user_info.dzial == "TP")
					|| (true)
					|| (user_info.id == 9)	//Marcińczyk
					|| (user_info.id == 1)
					){
						if (!(obj.zad > 0)){
							$('#zad').css("background-color",'red');
							alert('Proszę wybrać zadanie');
							return;
						}
					}
					if (obj.kiedy/1000 < now - (days_back+1)*86400 || obj.kiedy/1000 > now){
						$("#data").css("background-color",'red');
						alert("Nieprawidłowa data.");
						return;
					}

					if (obj.ile > 720 || obj.ile<=0){
						$('#czas').css("background-color",'red');
						alert('Proszę podać prawidłową ilość godzin');
						return;
					}
					
					if (obj.opis == "" && obj.dzial != 5){
						$('#opis').css("background-color",'red');
						alert('Proszę opisać wykonane prace');
						return;
					}

					if (!_prac_id)
					for (var k in karty){
						var karta = karty[k];
						if (obj.kiedy == karta.data/1 && obj.co == karta.kat_id && obj.zlec == karta.zlec){
							// console.log(karta);
							// console.log(karta.kart_id);
							// console.log(min_to_h2(obj.ile));

							// console.log((obj.ile-(obj.ile%60))/60+":"+(obj.ile%60));
							// console.log((karty[k].ile-(karty[k].ile%60))/60+":"+(karty[k].ile%60));
							if (confirm("Podana kategoria prac dla danego dnia została już wpisana. Czy chcesz dopisać do karty ?")){
								obj.id = karta.kart_id;
								if (karty[k].opis_p && karty[k].opis_p != obj.opis)
									obj.opis = karty[k].opis_p + " (" + min_to_h2(karty[k].ile) + ")\n" + obj.opis + " (" + min_to_h2(obj.ile) + ")";
								obj.ile = karty[k].ile/1 + obj.ile/1;
							} else
								return;
						}
					}
				}

				temp = {data:obj.kiedy,kat_id:obj.co,zlec:obj.zlec};
				temp2 = obj;
			}
					
			if (urlop)
				window.open("urlop.php?opis="+temp2.opis+"&od="+temp2.od_kiedy/1000+"&do="+temp2.do_kiedy/1000+"&id="+sum_user_id,"_self");
			else
				$.ajax({
					url: 'karta1.php?callback=?',
					dataType: 'json',
					type: 'POST',
					data: obj,
					timeout: 2000
				}).success(function(obj){
					if (obj[0]=="OK"){
						if (window.opener)
							window.opener.location.reload();
						if (temp)
							karty.push(temp);
						if ($('#zad').val() && zadania[$('#zad').val()] && (zadania[$('#zad').val()].rbh > 0)){
							console.log(obj);
							if (zadania[$('#zad').val()].rbh/1 < obj[5]/1) {
								alert('Czas przeznaczony na to zadanie został przekroczony o ' + (obj[5] - zadania[$('#zad').val()].rbh).toFixed(2) + 'h.')
							}
						}
						if (obj[4] > 480)
							alert('Dzienny czas pracy przekracza 8h.')
						if (obj[2] == "DELETE"){
							alert(obj[3]);
							window.close();
						}
						if (temp2.co == 545 && confirm( obj[3]+'\n\rCzy chcesz wydrukować kartę urlopową ?')){
							if (temp2.od_kiedy && temp2.do_kiedy)
								window.open("urlop.php?opis="+temp2.opis+"&od="+temp2.od_kiedy/1000+"&do="+temp2.do_kiedy/1000,"_self");
							else
								window.open("urlop.php?opis="+temp2.opis+"&od="+temp2.kiedy/1000+"&do="+temp2.kiedy/1000,"_self");
						}
						else if (<?php if (isset($_REQUEST["add"])) echo "true"; else echo "false";?>) {
							open(location, '_self').close();
						} else if (confirm(obj[3]+'\n\rCzy chcesz zamknąć kartę ?'))
							window.close();
					} else
						alert('Błąd skryptu.');
				}).fail( function() {
					alert('Błąd serwera kart pracy.');
				});
		};
	</script>
</html>