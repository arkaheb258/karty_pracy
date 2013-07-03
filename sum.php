<?php
session_start();
//var_dump($_SESSION["myuser"]["nazwa"]);
//exit;
if ( !isset( $_SESSION["myusername"] ) ){
	header("location:/scripts/login.php?url=".$_SERVER["REQUEST_URI"]);
	exit;
} else {
	if ($_SESSION['timeout'] + 10 * 60 < time()) {
		header("location:/scripts/logout.php?session_timeout");
		exit;
	}
	else $_SESSION['timeout'] = time();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl" lang="pl">
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7; IE=EmulateIE9" /> 
		<link rel="stylesheet" type="text/css" href="/lib/css/kopex.css" />
		<link rel="stylesheet" type="text/css" href="../raporty_tr/demo_table.css" />
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
  <title>Podsumowanie</title>
  </head>
	<body style="height:100%;">
		<div style="float:right;font-size: 0.8em;margin-right: 1em;">Zalogowano jako: <?php echo $_SESSION["myuser"]["nazwa"]; ?></div><br/>
		Proszę wybrać pracownika:
		<select id="user" name="user">
			<option value="null"></option>
		</select><br/>
		<iframe src="lista.php?user_id=0" style="width:100%; height:90%;"></iframe>
	</body>
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="jquery-ui.min.js"></script>

	<script type="text/javascript" src="/scripts/users.php"></script>
	<script>
		_user_id = <?php echo $_SESSION["myuser"]["id"];?>;
		_prac_id = <?php if (isset($_REQUEST["id"])) echo $_REQUEST["id"]; else echo 'null';?>;
		_kat_id = <?php if (isset($_REQUEST["id_k"])) echo $_REQUEST["id_k"]; else echo 'null';?>;
		_dzia_id = <?php if (isset($_REQUEST["id_d"])) echo $_REQUEST["id_d"]; else echo 'null';?>;

		if (typeof console === "undefined")
			console = {log:function(){}};
		else if (typeof console.log === "undefined")
			console.log = function(){};
		
		$(function() {
			for (var u in users){
				$('#user').append('<option value=\"'+users[u].id+'\" title="'+users[u].nazwa+'">'+users[u].nazwa+' ('+users[u].dzial+')</option>');
			}
			$('#user').change(function() {
				$('iframe').attr('src','lista.php?user_id='+$(this).val());
			});
		});
	</script>
</html>