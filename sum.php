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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl" lang="pl">
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7; IE=EmulateIE9" /> 
		<link rel="stylesheet" type="text/css" href="/lib/css/kopex.css" />
		<link rel="stylesheet" type="text/css" href="demo_table.css" />
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
			body {
				position:absolute;
				top: 0px;
				bottom: 0px;
				height: 95%;
				width: 95%;
			}
		</style>
  <title>Podsumowanie</title>
  </head>
	<body style="height:100%;">
		<div style="float:right;font-size: 0.8em;margin-right: 1em;">Zalogowano jako: <?php echo $_SESSION["myuser"]["nazwa"]; ?></div><br/>
		Proszę wybrać dział: <select id="dzial" name="dzial"><option value="">Wszystkie</option></select><br/>
		Proszę wybrać pracownika: <select id="user" name="user"></select><br/>
		Proszę wybrać miesiąc: <select id="month" name="month"></select><br/>
		Proszę wybrać rok: <select id="year" name="year"></select><br/>
		<iframe src="lista.php?user_id=0" style="width:100%; height:90%;"></iframe>
	</body>
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="jquery-ui.min.js"></script>

	<script type="text/javascript" src="users.php"></script>
	<script>
		_user_id = <?php echo $_SESSION["myuser"]["id"];?>;
		_prac_id = <?php if (isset($_REQUEST["id"])) echo $_REQUEST["id"]; else echo 'null';?>;
		_kat_id = <?php if (isset($_REQUEST["id_k"])) echo $_REQUEST["id_k"]; else echo 'null';?>;
		_dzia_id = <?php if (isset($_REQUEST["id_d"])) echo $_REQUEST["id_d"]; else echo 'null';?>;
//		_filtr_dzial = "<?php if (isset($_REQUEST["dzial"])) echo $_REQUEST["dzial"]; else echo 'null';?>";

		if (typeof console === "undefined")
			console = {log:function(){}};
		else if (typeof console.log === "undefined")
			console.log = function(){};
		
		var dzialy = ['TR','RTR'];
		
		$(function() {
			function load_users(){
				$('#user').empty();
				$('#user').append('<option value="null" title=""></option>');
				for (var u in users){
					if ( users[u].dzial.indexOf('T')==-1 || users[u].dzial.indexOf('?') != -1)
						continue;
	//				if (_filtr_dzial == "null" || users[u].dzial.indexOf(_filtr_dzial) == 0)
					if ($('#dzial').val() == "null" || users[u].dzial.indexOf($('#dzial').val()) == 0)
						$('#user').append('<option value=\"'+users[u].id+'\" title="'+users[u].dzial+'">'+users[u].nazwa+' ('+users[u].dzial+')</option>');
	//				console.log(jQuery.inArray(users[u].dzial,dzialy));
					if (jQuery.inArray(users[u].dzial,dzialy) == -1)
						dzialy.push(users[u].dzial);
				}
			}
			load_users();
			dzialy.sort();
			for (d in dzialy){
				$('#dzial').append('<option value="'+dzialy[d]+'">'+dzialy[d]+'</option>');
			}
			
			
			
			var now = new Date();
			var miesiac = ["Styczeń","Luty","Marzec","Kwiecień","Maj","Czerwiec","Lipiec","Sierpień","Wrzesień","Październik","Listopad","Grudzień"];
			for (var m in miesiac)
				$('#month').append('<option value=\"'+(m/1+1)+'\">'+miesiac[m]+'</option>');
			for (var y=2013;y<=now.getFullYear();y++)
				$('#year').append('<option value=\"'+y+'\">'+y+'</option>');
			$('#year').val(now.getFullYear());
			var d = new Date();
			$('#month').val(d.getMonth()+1);
			
			function load_iframe(){
				$('iframe').attr('src','lista.php?user_id='+$('#user').val()+'&month='+$('#month').val()+'&year='+$('#year').val());
			}
			
			$('#user').change(load_iframe);
			$('#month').change(load_iframe);
			$('#year').change(load_iframe);
			
			$('#dzial').change(load_users);
		});
	</script>
</html>