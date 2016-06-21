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
			
			$table = "kart_pr_prace";
			
			if (substr($id,0,1)=='R'){
				$id = substr($id,1);
				$table = "kart_pr_prace_rtr";
			}
			
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
		//		echo $query;
				if ($mysqli->query($query))
					echo json_encode(array('OK',$mysqli->insert_id,'INSERT','Zarejestrowano '.count($dzien)." dni.",0));
				else echo json_encode(array($mysqli->error,0));
			} else {
				$kiedy = test_req( "kiedy" );
				
				$t_diff = (new DateTime())->getTimestamp() - $kiedy/1000;

        {
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
					// $query = "SELECT SUM( czas )/60 as czas FROM  `$table` WHERE zadanie = $zadanie AND user_id = $kto";
					$query = "SELECT SUM( czas )/60 as czas FROM  `$table` WHERE zadanie = $zadanie;";
					// $query = "SELECT z.rbh, SUM( p.czas ) /60 AS czas FROM  `kart_pr_prace` p LEFT JOIN  `kart_pr_zadania` z ON z.id = p.zadanie WHERE zadanie =1";
					
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
							// $query = "UPDATE `$table` SET `user_id` = \"$kto\",`kat_id` = \"$co\", `czas` = \"$ile\", `data` = $kiedy, `zlecenie` = \"$zlecenie\", `opis` = \"$opis\", `ip` = \"$t_ip\", `zadanie` = $zadanie, timestamp = NULL WHERE `$table`.`id` = ".$id.";";
							$query = "UPDATE `$table` SET `user_id` = \"$kto\",`kat_id` = \"$co\", `czas` = \"$ile\", `data` = $kiedy, `zlecenie` = \"$zlecenie\", `opis` = \"$opis\", `ip` = \"$t_ip\", `zadanie` = $zadanie WHERE `$table`.`id` = ".$id.";";
		//					echo $query;
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
					$file = "log_kp.txt";
					file_put_contents($file, "data = ".date("c")."\n", FILE_APPEND | LOCK_EX);
					file_put_contents($file, "userid = ".$_SESSION["myuser"]["id"]."\n", FILE_APPEND | LOCK_EX);
					file_put_contents($file, $query ."\n", FILE_APPEND | LOCK_EX);
				}
			}
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
  <title>Karty pracy TR</title>
  </head>
	<body>
		<div style="float:right;font-size: 0.8em;margin-right: 1em;">Zalogowano jako: <?php echo $_SESSION["myuser"]["nazwa"]; ?></div><br/>
		<form action="baza_karta.php" style="background-image:url(images/logo_km100.png);background-repeat: no-repeat; ">
			<table style="margin:auto; width: 75%; max-width: 800px;">
				<colgroup>
					<col width="250">
					<col width="100%">
				</colgroup>
				<tbody>
					<tr><th>Nazwisko i Imię:</th><td><input id="kto_u" name="kto_u" /></td></tr>
					<tr>
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
						<th><label for="czas">Czas pracy w godzinach:</label></th>
						<td><input id="czas" name="czas" /></td>
					</tr>
					<tr><th>Kategoria prac:</th><td id="kategorie"></td></tr>
					<tr><td colspan="3">Opis:<br/><textarea id="opis" rows="6" name="opis" style="width:100%;"></textarea></td></tr>
					<tr><td>Ostatnio wpisywane:<select id="opis_last"><option value="null"></option></select><br/><div id="opis_sort">Sortuj alfabetycznie</div></td></tr>
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
		</form>
	</body>
<?php echo '<script type="text/javascript" src="baza_obj.php?user_id='.$_SESSION["myuser"]["id"].'"></script>'; ?>
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="jquery-ui.min.js"></script>
	<script type="text/javascript" src="jquery.ui.datepicker-pl.js"></script>
	<script type="text/javascript" src="jquery-ui-timepicker-addon.js"></script>
	<script type="text/javascript" src="globalize.js"></script>
	<script type="text/javascript" src="globalize.culture.de-DE.js"></script>

	<script type="text/javascript" src="date.js"></script>
	<script>
	
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
			
		var zlec_last = [];
		var opis_last = [];
		var opis_last_arch = [];
		
		$(function() {
		
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
			
			$("#czas").timespinner().timespinner( "value", "01:00" );
			$('#gotowe').button().click(function(){send(false);});
			$('#urlop').button().click(function(){send(false,true);});
			$('#close').button().click(function(){window.close();});
			$('#del').button().show().click(function(){
				if (confirm('Czy napewno chcesz skasować kartę ?')){
					send(true);
				}
			});
      
      function for_all_leafs(tree, path, callback) {
        for (var c in tree.children){
          for_all_leafs(tree.children[c], path.concat(c), callback);
        }
        for (var l in tree.leafs){
          callback(tree.leafs[l], path.concat(l));
        }
      }
      
      // for_all_leafs(projekty_tree, [], function(ob, path){
        // console.log(ob, path);
      // });
      // if (false)
      for_all_leafs(projekty_tree, [], function(ob_nr, path){
        var ob = zadania[ob_nr];
        console.log(ob_nr, ob, path);
        var t_title = ob.nazwa;
        if (ob.par_id == 994) ;//prace inne
        else if (ob.par_id == 989) ;//nieobecnosci
        else {
          // console.log(ob, path);
					var opis = '';
          for (var i in path) {
            opis += path[i].nazwa + ' -> ';
          }
          opis += ob.nazwa;
					var _style = '';
					if (ob.termin && (ob.termin < ((new Date()).getTime() - 24*60*60*1000))){
            _style = ' style="color:brown;"';
					}
					if (ob.rbh > 0)
            opis += ' (limit = ' + ob.rbh +' rbh)';
          $('#zad').append('<option value=\"'+ob.id+'\" title="'+ob.opis+'"'+_style+'>'+opis+'</option>');
        }
      })

			function zad_change(){
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
						$('#kat_4_401').hide();

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
			function dzial_change(){
				clear_err();
				$('#kat_5').show();
				$('#kat_'+$("#dzial option:selected").val()).show();
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
			
			var karty = [];
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
			
			$("#data_od").datepicker({minDate: -30, maxDate: 30}).datepicker('setDate', new Date()).click(function() { clear_err(); });
			$("#data_do").datepicker({minDate: -30, maxDate: 30}).datepicker('setDate', new Date()).click(function() { clear_err(); });


//			var temp_date = new Date();
			if (_copy_id != null && _copy_id != 'null'){
				var obj = find_id(_copy_id);
//				console.log(obj);
				if (obj){
					$('#kto_u').val('<?php echo $_SESSION["myuser"]["nazwa"];?>').attr('disabled', 'disabled');
					if (today.getDate() < 10) {
						$("#data").datepicker({ minDate: -30,maxDate: 0}).datepicker('setDate', new Date());
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
					if (new Date() - temp_date > 86400000*(30+1)){
						$('#gotowe').hide();
						$('#del').hide();
						$("#data").datepicker();
						disable_all();
						$('#czas').timespinner('option', "max", +Globalize.parseDate( "23:00"));
					} else {
						$("#data").datepicker({ minDate: -30, maxDate: 0});
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
				$("#data").datepicker({ minDate: -30,maxDate: 0});
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
					if (obj.kiedy/1000 < now - (30+1)*86400 || obj.kiedy/1000 > now){
						$("#data").css("background-color",'red');
						alert("Nieprawidłowa data.");
						return;
					}

					if (obj.ile > 720 || obj.ile<=0){
						$('#czas').css("background-color",'red');
						alert('Proszę podać prawidłową ilość godzin');
						return;
					}
/*					
					if (obj.zlec.indexOf("900") == 0 || obj.zlec.indexOf("121") == 0){
						$('#zlec').css("background-color",'red');
						alert('Numer zamówienia / komisji proszę podawać w formacie "01H900xxxx"');
						return;				
					}				

					if (obj.zlec.indexOf("93-40283") == 0 || obj.zlec.indexOf("95-80308") == 0 ){
						$('#zlec').css("background-color",'red');
						alert('Numer zamówienia dla kotwiarki to "01H9004259" lub "01H9004260"');
						return;				
					}				

					if (obj.zlec.indexOf("SC_ANA") == 0){
						$('#zlec').css("background-color",'red');
						alert('Numer zamówienia dla SHUTTLE CAR to "01H9004249"');
						return;				
					}				

					if (obj.zlec.indexOf("94-80010") == 0){
						$('#zlec').css("background-color",'red');
						alert('KSW-800 należy do "PNU Projekt nr 63"');
						return;				
					}				

					if (obj.zlec.indexOf("ikrus") != -1){
						$('#zlec').css("background-color",'red');
						alert('GUŁ-500 należy do "PNU Projekt nr 71"');
						return;				
					}	

					if (obj.co > 430 && obj.co < 450){
						if ($("#pnu").val() == "null"){
							$('#pnu').css("background-color",'red');
							alert('Proszę wybrać numer projektu');
							return;						
						} else {
							obj.zlec = "PNU Projekt nr "+$("#pnu").val();
							if ($("#pnu_zad").val() > 0) obj.zlec += '/' + $('#pnu_zad').val().trim();
							$('#zlec').val(obj.zlec);
						}
	//					alert('Proszę wpisać nazwę projektu w formacie "PNU Projekt nr xx"');
	//					return;				
					} else if (obj.zlec.indexOf("PNU") == 0 && obj.co != 451){
						$('#zlec').css("background-color",'red');
						alert('Dla PNU proszę wybrać kategorię "DRiW / Projekty rozwojowe wg. PNU"');
						return;				
					}
					
					if (obj.co > 400 && obj.zlec.indexOf("01H900") == 0){
						$('#dzial').css("background-color",'red');
						alert('Prace pod konkretną komisję / zamówienie proszę wpisywać w dziale DH');
						return;				
					}
*/
					
					if (obj.opis == "" && obj.dzial != 5){
						$('#opis').css("background-color",'red');
						alert('Proszę opisać wykonane prace');
						return;
					}

					if (!_prac_id)
					for (var k in karty){
						var karta = karty[k];
						if (obj.kiedy == karta.data/1 && obj.co == karta.kat_id && obj.zlec == karta.zlec){
							console.log(karta);
//							console.log(karta.kart_id);
							console.log(min_to_h2(obj.ile));

							console.log((obj.ile-(obj.ile%60))/60+":"+(obj.ile%60));
							console.log((karty[k].ile-(karty[k].ile%60))/60+":"+(karty[k].ile%60));
							if (confirm("Podana kategoria prac dla danego dnia została już wpisana. Czy chcesz dopisać do karty ?")){
								obj.id = karta.kart_id;
	//console.log(karta);
	//console.log(obj);
	//console.log(karty[k]);			
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
					
//			console.log(temp);
			// console.log(obj);
			// if (urlop)
				// console.log("urlop");
			// else 
				// console.log("nie urlop");

//return;
// console.log(obj);
// console.log(del);
			if (urlop)
				window.open("urlop_n.php?opis="+temp2.opis+"&od="+temp2.od_kiedy/1000+"&do="+temp2.do_kiedy/1000+"&id="+sum_user_id,"_self");
			else
				$.ajax({
//					url: 'http://192.168.34.17:88/scripts/karta_pr.php?callback=?',
					url: 'karta.php?callback=?',
					dataType: 'json',
					type: 'POST',
					data: obj,
					timeout: 2000
				}).success(function(obj){
					if (obj[0]=="OK"){
						if (window.opener)
							window.opener.location.reload();
			// console.log(temp);
						if (temp)
							karty.push(temp);
						if ($('#zad').val() && zadania[$('#zad').val()] && (zadania[$('#zad').val()].rbh > 0)){
							// console.log(zadania[$('#zad').val()].rbh/1);
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
								window.open("urlop_n.php?opis="+temp2.opis+"&od="+temp2.od_kiedy/1000+"&do="+temp2.do_kiedy/1000,"_self");
								// window.open("urlop.php?opis="+temp2.opis+"&od="+temp2.od_kiedy/1000+"&do="+temp2.do_kiedy/1000+"&id="+_prac_id,"_self");
							else
								window.open("urlop_n.php?opis="+temp2.opis+"&od="+temp2.kiedy/1000+"&do="+temp2.kiedy/1000,"_self");
								// window.open("urlop.php?opis="+temp2.opis+"&od="+temp2.kiedy/1000+"&do="+temp2.kiedy/1000+"&id="+_prac_id,"_self");
						}
						else if (<?php if (isset($_REQUEST["add"])) echo "true"; else echo "false";?>) {
							open(location, '_self').close();
						} else if (confirm(obj[3]+'\n\rCzy chcesz zamknąć kartę ?'))
							window.close();
//							window.open("lista.php","_self");
//						else
//							window.open("karta.php","_self");
//							window.open("karta.php?id="+obj[1],"_self");
					} else
						alert('Błąd skryptu.');
				}).fail( function() {
					alert('Błąd serwera kart pracy.');
				});
		};
	</script>
</html>