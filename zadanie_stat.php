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
	$zadanie = null;
	$wykon = array();
	if (isset($_REQUEST["id"])){
		$query = "SELECT * FROM `kart_pr_zadania` WHERE id = ".$_REQUEST["id"].";";
			
		$result = $mysqli->query($query);
		if ($result)
			while($row = $result->fetch_assoc()){
				$zadanie = $row;
			}

		$query = "SELECT user_id, kat_id, SUM( czas ) /60 AS czas, nazwa FROM `kart_pr_prace` LEFT JOIN kart_pr_kat ON kart_pr_prace.kat_id=kart_pr_kat.id WHERE zadanie = ".$_REQUEST["id"]." GROUP BY user_id, kat_id;";
		$result = $mysqli->query($query);
		if ($result) {
			while($row = $result->fetch_assoc()){
				$wykon[] = $row;
			}
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
						<th><label for="rbh">Ilość roboczogodzin:</label></th>
						<td><input type="text" id="rbh" name="rbh" /></td>
					</tr>
					<tr>
						<th><label for="termin">Termin zakończenia prac:</label></th>
						<td><input type="text" id="termin" name="termin" /></td>
					</tr>
					<tr>
						<td colspan="1"></td>
						<td colspan="2" style="text-align:right;">
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
		var wykon = <?php echo json_encode($wykon);?>;

		if (typeof console === "undefined")
			console = {log:function(){}};
		else if (typeof console.log === "undefined")
			console.log = function(){};
			
		$(function() {
			var act_users = [];
			for (var w in wykon){
				act_users[wykon[w].user_id] = 1;
			}
//			console.log(act_users);
			
			for (var u in users){
				var us = users[u];
				if (act_users[us.id])
					$('#prac').append('<label id="user_'+us.id+'" class="cl_d_'+us.dzial.replace("-","_")+'"><table><tbody><tr><td><b>'+us.nazwa+'</b></td><td><b><span>0</span>h</b></td></tr></tbody></table><br/></label>');
			}
			
			for (var w in wykon){
				$('#user_'+wykon[w].user_id+' tbody').append('<tr><td>'+wykon[w].nazwa+'</td><td>'+(wykon[w].czas/1)+' h</td></tr>');
			}
			for (var w in wykon){
//				console.log(wykon[w]);
//				$('#user_'+wykon[w].user_id).show();
				var u_sum = $('#user_'+wykon[w].user_id+' span').text();
				u_sum /= 1;
				u_sum += wykon[w].czas/1;
				$('#user_'+wykon[w].user_id+' span').text(' ' + u_sum);
			}

			if (_zad_id){
				var _zadanie = <?php if ($zadanie) echo json_encode($zadanie); else echo 'null';?>;
//				console.log(_zadanie);
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

			$('#close').button().click(function(){window.close();});
		});
	</script>
</html>