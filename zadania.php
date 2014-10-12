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
	$query = "SELECT * FROM `kart_pr_zadania`;";
//	echo $query;
		
	$zadania = array();
	$result = $mysqli->query($query);
	if ($result)
		while($row = $result->fetch_assoc()){
			$zadania[] = $row;
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
			textarea{
				width : 75%;
			}
			td {
				vertical-align: top;
			}
			.ui-menu { width: 150px; }
			
			.ui-widget-content {
				background: none;			
			}
		</style>
  <title>Zadania</title>
  </head>
	<body>
		<div style="float:right;font-size: 0.8em;margin-right: 1em;">Zalogowano jako: <?php echo $_SESSION["myuser"]["nazwa"]; ?></div><br/>
		<form action="zadanie.php" style="background-image:url(images/logo_km100.png);background-repeat: no-repeat; ">
			<table style="margin:auto;">
					<tr>
						<td colspan="3">
							<div id="add">Dodaj nowe zadanie</div>
						</td>
					</tr>
					<tr>
						<td>
							Proszę wybrać dział: <select id="dzial" name="dzial">
								<option value="">Wszystkie</option><option value="TP">TP</option><option value="TR-1">TR-1</option><option value="TR-2">TR-2</option><option value="TR-3">TR-3</option><option value="TR-4">TR-4</option>
							</select><br/>
						</td>
					</tr>
					<tr>
						<th>Zadania:</th>
					</tr>
					<tr>
						<td id="zlec"></td>
					</tr>
					<tr>
						<td colspan="2" style="text-align:right;">
							<div id="gotowe">Dodaj</div>
							<div id="close">Zamknij</div>
						</td>
					</tr>
				</table>
		</form>
	</body>
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="jquery-ui.min.js"></script>
	<script type="text/javascript" src="users.php"></script>
	<script>
		_user_id = <?php echo $_SESSION["myuser"]["id"];?>;
		_prac_id = '<?php if (isset($_REQUEST["id"])) echo $_REQUEST["id"]; else echo 'null';?>';
		_kat_id = <?php if (isset($_REQUEST["id_k"])) echo $_REQUEST["id_k"]; else echo 'null';?>;
		_dzia_id = <?php if (isset($_REQUEST["id_d"])) echo $_REQUEST["id_d"]; else echo 'null';?>;
		_copy_id = '<?php if (isset($_REQUEST["copy_id"])) echo $_REQUEST["copy_id"]; else echo 'null';?>';
		_zadania = <?php echo json_encode($zadania);?>;
		var sum_user_id = <?php if (isset($_REQUEST["user_id"])) echo $_REQUEST["user_id"]; else echo 'null';?>;

		if (typeof console === "undefined")
			console = {log:function(){}};
		else if (typeof console.log === "undefined")
			console.log = function(){};
			
		$(function() {
			$('#gotowe').button().hide().click(function(){send(false);});
			$('#close').button().click(function(){window.close();});
			
			$('#add').button().click(function(){
				window.open("zadanie.php");
			});
			for (var u in users){
				var us = users[u];
//				$('#prac').append('<input type="radio" id="user_'+us.id+'" name="user" value="'+us.id+'"><label for="user_'+us.id+'">'+us.nazwa+'</label><br/>');
			}
		});
		function load_zad(){
			$("#zlec").empty();
			for (var z in _zadania){
				var zad = _zadania[z];
				var _dzial = $('#dzial').val();
				if (zad.dzial_wyk.indexOf(_dzial)==-1)
					continue;
				var div = $('<div style="text-align: right; cursor: pointer;" title="'+zad.opis+'">'+zad.nazwa+'</div>').appendTo(("#zlec"));
				jQuery.data(div[0], "id", zad.id);
				div.click(function(){
					window.open("zadanie.php?id="+jQuery.data(this, "id"));
				});
			};
		}
		load_zad();
		$('#dzial').change(load_zad);

		$('#prac').click(function() { 
			
			var id = $('#prac > input:checked').val();
			$("#zlec").empty();
			for (var z in _zadania){
//				console.log(_zadania[z].kto);
//				console.log(id);
				if (_zadania[z].kto.indexOf(id)!= -1){
					var zad = _zadania[z];
					var div = $('<div style="text-align: right;" title="'+zad.opis+'">'+zad.tytul+'</div>').appendTo(("#zlec"));
					console.log(div[0]);
					console.log(zad.id);
					jQuery.data(div[0], "id", zad.id);
					div.click(function(){
						window.open("zadanie.php?id="+jQuery.data(this, "id"));
					});
					console.log(zad);
				}
			}
		});
	</script>
</html>