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
	$mysqli = new_polacz_z_baza();
	if (isset($_REQUEST["dodaj"])){
		if (isset($_REQUEST["callback"])){
			$callback = trim($_REQUEST['callback']);
			echo $callback .'(';
		}
		if (isset($_REQUEST["jsoncallback"])){
			$callback = trim($_REQUEST['jsoncallback']);
			echo $callback .'(';
		}
		
		$kier_id = $_REQUEST["kier_id" ];
		$co = $_REQUEST["co" ];
		$forma = $_REQUEST["forma" ];
		$opis = $_REQUEST["opis" ];
		$koment = $_REQUEST["koment"];
		$dzial_wyk = $_REQUEST["dzial_wyk"];
		$dzial_zlec = $_REQUEST["dzial_zlec"]/1;
		$prac_wykon = $_REQUEST["prac_wykon"];
		$zlec = $_REQUEST["zlec" ];
		$rbh = $_REQUEST["rbh" ];
		$termin = $_REQUEST["termin"];
		
		$id = $_REQUEST["dodaj"];
		if ($_REQUEST["dodaj"] == 0){
			$query = "INSERT INTO `kart_pr_zadania`(`user_id`,`nazwa`, `forma`, `opis`, `dzial_wyk`, `dzial_zlec`, `prac_wykon`, `zlecenie`, `rbh`, `termin`, `komentarz`) VALUES (\"$kier_id\",\"$co\",\"$forma\",\"$opis\",\"$dzial_wyk\",$dzial_zlec,\"$prac_wykon\",\"$zlec\",$rbh,$termin,\"$koment\");";
			if ($mysqli->query($query))
				echo json_encode(array('OK',$mysqli->insert_id,'INSERT','Zadanie dodane'));
			else{
				echo json_encode(array('OK',$mysqli->error,'INSERT',$mysqli->error));
//				echo $query;
			}
		} else {
			$query = "UPDATE `kart_pr_zadania` SET `user_id`=\"$kier_id\",`nazwa`=\"$co\", `forma`=\"$forma\", `opis`=\"$opis\", `dzial_wyk`=\"$dzial_wyk\", `prac_wykon`=\"$prac_wykon\", `rbh`=$rbh, `termin`=$termin, `dzial_zlec`=$dzial_zlec, `zlecenie`=\"$zlec\", `komentarz`=\"$koment\" WHERE `id`=$id;";
			if ($mysqli->query($query))
				echo json_encode(array('OK',$id,'UPDATE','Zadanie poprawione'));
			else
				echo $query;
		}
//		echo $query;
//		var_dump($_REQUEST);
		
//		if(!$result)
//			echo json_encode($mysqli->error);
//		else
//			echo json_encode($mysqli->affected_rows);
			
		if (isset($_REQUEST["callback"]) || isset($_REQUEST["jsoncallback"]))
			echo ')';
		exit;
	}
	$zadania = array();
	if (isset($_REQUEST["id"])){
		$query = "SELECT * FROM `kart_pr_zadania` WHERE id = ".$_REQUEST["id"].";";
			
		$result = $mysqli->query($query);
		if ($result)
			while($row = $result->fetch_assoc()){
				$zadania[] = $row;
			}
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
			select {
				width: 20em;
			}
			input[type='checkbox'] {
			}
			input[type='text'] {
				width: 20em;
			}
			textarea{
				width: 100%;
			}
			.ui-menu { width: 150px; }
			
			.ui-widget-content {
				background: none;			
			}
		</style>
  <title>Zadanie</title>
  </head>
	<body>
		<div style="float:right;font-size: 0.8em;margin-right: 1em;">Zalogowano jako: <?php echo $_SESSION["myuser"]["nazwa"]; ?></div><br/>
		<form action="zadanie.php" style="background-image:url(images/logo_km100.png);background-repeat: no-repeat; ">
			<table style="margin:auto;">
					<tr class="n_l4">
						<th><label for="zad">Zadanie:</label></th>
						<td><input type="text" id="zad" name="zad" /></td>
					</tr>
					<tr class="n_l4">
						<th><label for="zlec">Numer zlecenia / zamówienia:</label></th>
						<td><input type="text" id="zlec" name="zlec" /></td>
					</tr>
					<tr><td colspan="3">Opis:<br/><textarea id="opis" rows="6" name="opis"></textarea></td></tr>
					<tr>
						<th>Zlecone przez dział:</th>
						<td><select id="dzial_zlec" name="dzial_zlec"><option value="null"></option></select></td>
					</tr>
					<tr>
						<th>Forma zlecenia:</th>
						<td><select id="forma_zlec" name="forma_zlec"><option value="ustnie">Ustnie</option><option value="e-mail">E-mail</option><option value="telefon">Telefonicznie</option></select></td>
					</tr>
					<tr>
						<th>Dział wykonujący:</th>
					</tr>
					<tr>
						<td id="dzial_wyk">
							<label style="margin-left: 2em;" title="TP"><input type="checkbox" name="dzial_wyk" value="TP">TP</input></label>
							<label style="margin-left: 2em;" title="TR-1"><input type="checkbox" name="dzial_wyk" value="TR-1">TR-1</input></label>
							<label style="margin-left: 2em;" title="TR-2"><input type="checkbox" name="dzial_wyk" value="TR-2">TR-2</input></label>
							<label style="margin-left: 2em;" title="TR-3"><input type="checkbox" name="dzial_wyk" value="TR-3">TR-3</input></label>
							<label style="margin-left: 2em;" title="TR-4"><input type="checkbox" name="dzial_wyk" value="TR-4">TR-4</input></label>
						</td>
					</tr>
					<tr>
						<th><label for="rbh">Ilość roboczogodzin:</label></th>
						<td><input type="text" id="rbh" name="rbh" /></td>
					</tr>
					<tr>
						<th><label for="termin">Termin zakończenia prac:</label></th>
						<td><input type="text" id="termin" name="termin" /></td>
					</tr>
					<tr><td colspan="3">Komentarz:<br/><textarea id="koment" rows="6" name="koment"></textarea></td></tr>
					<tr>
						<td colspan="1"></td>
						<td colspan="2" style="text-align:right;">
							<div id="gotowe">Dodaj</div>
							<div id="close">Zamknij</div>
						</td>
					</tr>
					<tr>
						<th>Pracownicy:</th>
					</tr>
					<tr>
						<td id="prac"></td>
					</tr>
				</table>
		</form>
	</body>
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="jquery-ui.min.js"></script>
	<script type="text/javascript" src="jquery.ui.datepicker-pl.js"></script>
	<script type="text/javascript" src="baza_karta.php"></script>
	<script type="text/javascript" src="users.php"></script>
	<script>
		_user_kart_perm = <?php echo $_SESSION["myuser"]["kart_perm"];?>;
		_user_id = <?php echo $_SESSION["myuser"]["id"];?>;
		_zad_id = <?php if (isset($_REQUEST["id"])) echo $_REQUEST["id"]; else echo 'null';?>;
//		_kat_id = <?php if (isset($_REQUEST["id_k"])) echo $_REQUEST["id_k"]; else echo 'null';?>;
//		_dzia_id = <?php if (isset($_REQUEST["id_d"])) echo $_REQUEST["id_d"]; else echo 'null';?>;
//		_copy_id = '<?php if (isset($_REQUEST["copy_id"])) echo $_REQUEST["copy_id"]; else echo 'null';?>';
		var sum_user_id = <?php if (isset($_REQUEST["user_id"])) echo $_REQUEST["user_id"]; else echo 'null';?>;

		if (typeof console === "undefined")
			console = {log:function(){}};
		else if (typeof console.log === "undefined")
			console.log = function(){};
			
		$(function() {
			
			for (var d in dzialy){
				if (d<5){
//					$('#dzial').append('<label style="margin-left: 2em;" title="'+dzialy[d].opis+'"><input type="checkbox" name="dzialy" value="'+d+'" title="'+dzialy[d].opis+'">'+dzialy[d].nazwa+'</label>');
					$('#dzial_zlec').append('<option value=\"'+d+'\" title="'+dzialy[d].opis+'">'+dzialy[d].nazwa+'</option>');
				}
			}
			
			$('#prac').append('<label id="user_all"><input type="checkbox" value="null"><b>Wszyscy</b><br/></label>');
			$('#user_all > input').change(function() {
				$('#prac >> input:visible').prop('checked',$(this).prop('checked'));
			});
			
			$("#rbh").spinner({
				min: 0,
				step: 1
			}).spinner( "value", 0 );

			$("#termin").datepicker();

			
			for (var u in users){
				var us = users[u];
				if (-1 == users[u].dzial.indexOf('RTR'))
					$('#prac').append('<label id="user_'+us.id+'" class="cl_d_'+us.dzial.replace("-","_")+'" style="display: none;"><input type="checkbox" name="user" value="'+us.id+'">'+us.nazwa+'<br/></label>');
//				console.log(us);
//				console.log(_user_id);
				if (us.kart_perm >= _user_kart_perm && _user_id != us.id)
					$('#user_'+us.id+' input').attr('disabled', true);
			}
				
			$('#dzial_wyk >> input').change(function() {
				if ($(this).prop('checked'))
					$('.cl_d_'+$(this).val().replace("-","_")).show();
				else
					$('.cl_d_'+$(this).val()	.replace("-","_")).hide();
			});
			
			$('input').change(function() { clear_err(); }).click(function() { clear_err(); });
			$('#opis').change(function() { clear_err(); }).click(function() { clear_err(); });
			$('#prac').change(function() { clear_err(); }).click(function() { clear_err(); });
			$('#dzial').change(function() { clear_err(); }).click(function() { clear_err(); });
			$('#dzial_zlec').change(function() { clear_err(); }).click(function() { clear_err(); });
			function clear_err(){
				$('input').blur(function() {$(this).css("background-color",'');});
				$('select').blur(function() {$(this).css("background-color",'');});
				$('input').css("background-color",'');
				$('select').css("background-color",'');
				$('#prac').css("background-color",'');
				$('#dzial_wyk').css("background-color",'');
				$('#opis').css("background-color",'');
			}
			if (_zad_id){
				var _zadanie = <?php if ($zadania) echo json_encode($zadania[0]); else echo 'null';?>;
				console.log(_zadanie);
				$('#zad').val(_zadanie.nazwa);
				$('#zlec').val(_zadanie.zlecenie);
				$('#opis').val(_zadanie.opis);
				$('#forma_zlec').val(_zadanie.forma);
				$('#koment').val(_zadanie.komentarz);
				$('#dzial_zlec').val(_zadanie.dzial_zlec);
				
				$('#rbh').val(_zadanie.rbh);
				if (_zadanie.termin != null){
					var temp_date = new Date();
					temp_date.setTime(_zadanie.termin);
					$('#termin').datepicker('setDate',temp_date);
				}
//					$('#termin').val(_zadanie.termin);
				
	//			_zadanie.dzial
//				$('#dzial >> input [value="'+_zadanie.dzial+'"]').attr('checked',true);
				
				$('#dzial_wyk >> input').each(function(){
					if (_zadanie.dzial_wyk.indexOf("'"+$(this).val()+"'") != -1){
						$(this).attr('checked',true);
						$('.cl_d_'+$(this).val().replace("-","_")).show();
					}
				});
				var prac2 = _zadanie.prac_wykon.split("'").join('').split(',');
				for (var p in prac2)
					$('#user_'+prac2[p]+' > input').attr('checked',true);
				$('#gotowe').text("Edytuj");
			} else {
				$('#dzial >> input').each(function(){
					$(this).attr('checked',true);
				});
			}

			$('#gotowe').button().click(function(){send(false);});
			$('#close').button().click(function(){window.close();});
			
			function send(del){
				clear_err();
				var obj = {};
				obj.kier_id = _user_id;
				obj.co = $('#zad').val();
				obj.opis = $('#opis').val();
				obj.forma = $('#forma_zlec').val();
				obj.dzial_zlec = $('#dzial_zlec').val();
				obj.koment = $('#koment').val();
				obj.zlec = $('#zlec').val();
				obj.dzial_wyk = '';
				obj.rbh = $('#rbh').val()/1;
				if ($('#termin').val())
					obj.termin = $('#termin').datepicker('getDate').getTime();
				else 
					obj.termin = 'null';
				
				$('#dzial_wyk >> input:checked').each(function(){
					if (obj.dzial_wyk != '')
						obj.dzial_wyk += ",";
//					obj.dzial += $(this).val();
					obj.dzial_wyk += "'"+$(this).val()+"'";
//					console.log($(this).attr('title'));
				});
				obj.prac_wykon = '';
				$('#prac >> input:checked:visible').each(function(){
					if ($(this).val() == 'null')
						return;
					if (obj.prac_wykon != '')
						obj.prac_wykon += ",";
					obj.prac_wykon += "'"+$(this).val()+"'";
				})
				
//				console.log(obj.prac_wykon);
//				return;
				
				if (obj.co == ''){
					$('#zad').css("background-color",'red');
					alert('Proszę wpisać nazwę');
					return;
				}
				
				if (obj.opis == ""){
					$('#opis').css("background-color",'red');
					alert('Proszę opisać zadanie');
					return;
				}

				if (obj.dzial_wyk == ''){
					$('#dzial_wyk').css("background-color",'red');
					alert('Proszę wybrać przynajmniej jeden dział');
					return;
				}
				

				if (!obj.dzial_zlec || obj.dzial_zlec == '' || obj.dzial_zlec == 'null'){
					$('#dzial_zlec').css("background-color",'red');
					alert('Proszę wybrać dział');
					return;
				}

				// if (obj.kto == ''){
					// $('#prac').css("background-color",'red');
					// alert('Proszę wybrać przynajmniej jednego pracownika');
					// return;
				// }

				obj.dodaj = 0;	
				if (_zad_id)
					obj.dodaj = _zad_id;	
console.log(obj);
//	return;
					$.ajax({
						url: 'zadanie.php?callback=?',
						dataType: 'json',
						type: 'POST',
						data: obj,
						timeout: 2000
					}).success(function(obj){
						if (obj && obj[0]=="OK"){
							if (window.opener)
								window.opener.location.reload();
							if (confirm(obj[3]+'\n\rCzy chcesz zamknąć to zadanie ?'))
								window.close();
						} else
							alert('Błąd skryptu.');
					}).fail( function() {
						alert('Błąd serwera kart pracy.');
					});
			};
		});
	</script>
</html>