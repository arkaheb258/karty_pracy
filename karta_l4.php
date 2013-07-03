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
		<form action="baza_karta.php" style="background-image:url(/images/logo_km100.png);background-repeat: no-repeat; ">
			<table style="margin:auto;">
					<tr><th>Nazwisko i Imię:</th><td><input id="kto_u" name="kto_u" /></td></tr>
					<tr><th>Od</th><td>
						<input type="text" id="data_od" class="datetimepicker od " style="width:15em;" name="od" /><br/>
					</td></tr>
					<tr><th>Do</th><td>
						<input type="text" id="data_do" class="datetimepicker do " style="width:15em;" name="od" /><br/>
					</td></tr>
					<tr><th>Wykonano pracę dla działu:</th><td><select id="dzial" name="dzial">
						<option value="null"></option>
					</select></td></tr>
				<tr><th>Kategoria prac:</th></tr>
					<tr><td id="kategorie">
					</td></tr>
			<tr><td colspan="3">
				Opis:<br/><textarea id="opis" rows="6" name="opis"></textarea>
			</td></tr><tr><td colspan="3">
			</td></tr><tr><td colspan="1">
			</td><td colspan="2" style="text-align:right;">
				<div id="gotowe">Dodaj</div>
				<div id="close">Zamknij</div>
			</td></tr></table>
<!--			<input type="submit" value="Gotowe" onsubmit="return validateForm()" method="post"> -->
		</form>
	</body>
<?php
	if (isset($_REQUEST["int"])){
		echo '
			<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
			<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
			';
	} else {
		echo '
			<script type="text/javascript" src="/lib/jquery.min.js"></script>
			<script type="text/javascript" src="/lib/jquery-ui.min.js"></script>
			';
	}
?>
	<script type="text/javascript" src="/lib/jquery.ui.datepicker-pl.js"></script>
	<script type="text/javascript" src="/lib/date.js"></script>
	<script type="text/javascript" src="/scripts/baza_karta.php"></script>
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
			$('#gotowe').button().click(function(){send(false);});
			$('#close').button().click(function(){window.close();});

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
			$('#kto_u').val('<?php echo $_SESSION["myuser"]["nazwa"];?>').attr('disabled', 'disabled');
			
			for (var d in dzialy){
				$('#dzial').append('<option value=\"'+d+'\" title="'+dzialy[d].opis+'">'+dzialy[d].nazwa+'</option>');
				$('#kategorie').append('<div id="kat_'+d+'" class="kategorie"></div>');
			}
			$('.kategorie').hide();
			$('#dzial').change(function() {
				clear_err();
				$('.kategorie').hide();
				$('#kat_'+$("#dzial option:selected").val()).show();
				$("#kategorie input:checked").attr('checked',null);
//				alert($("#dzial option:selected").val());
			});
			
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
				for (var k3 in kat2){
					var k2 = kat2[k3].id;
					if (!(kategorie[k][k2].long_time/1)) continue;
					if (kategorie[k][k2].ma_podgr){
						$('#kat_'+k).append('<div id="kat_'+k+'_'+kategorie[k][k2].id+'"><button>Rozwiń</button><span class="def" title="'+kategorie[k][k2].opis+'">'+kategorie[k][k2].nazwa+'</span><div class="sub" style="margin-left: 2em"></div></div>');
						$('#kat_'+k+'_'+kategorie[k][k2].id+' > .sub').hide();
						$('#kat_'+k+'_'+kategorie[k][k2].id+' > button').button({icons: {primary: "ui-icon-circle-plus"},text: false}).click(function(event){
							event.preventDefault();
							$('.sub',$(this).parent()).toggle();
						});
						for (var k3 in kategorie[k][k2].ma_podgr){
							$('#kat_'+k+'_'+kategorie[k][k2].id+' > .sub').append('<input type="radio" id="kat_'+k+'_'+k3+'" name="prace" value="'+k3+'" style="width:2em;" /><label for="kat_'+k+'_'+k3+'">'+kategorie[k][k2].ma_podgr[k3].nazwa+'</label><br/>');
						}
					}
					else
						$('#kat_'+k).append('<input type="radio" id="kat_'+k+'_'+kategorie[k][k2].id+'" name="prace" value="'+kategorie[k][k2].id+'" style="width:2em;" /><label for="kat_'+k+'_'+kategorie[k][k2].id+'" title="'+kategorie[k][k2].opis+'">'+kategorie[k][k2].nazwa+'</label><br/>');
				}
			}
			$('input').change(function() { clear_err(); });
			$('#dzial').click(function() { clear_err(); });
			$('textarea').keypress(function() { clear_err(); });
			
			
			$("#data_od").datepicker({minDate: -30, maxDate: 30}).datepicker('setDate', new Date()).click(function() { clear_err(); });
			$("#data_do").datepicker({minDate: -30, maxDate: 30}).datepicker('setDate', new Date()).click(function() { clear_err(); });
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
				if (karty[k].prac_id == _prac_id)
					return karty[k];
			return null;
		}

		function send(del){
			clear_err();
			var obj = {};
			obj.id = _prac_id;
			obj.kto = _user_id;
			obj.dzial = $('#dzial').val();
			obj.od_kiedy = $("#data_od").datepicker('getDate').getTime();
			obj.do_kiedy = $("#data_do").datepicker('getDate').getTime();
			obj.co = $('input[name=prace]:checked').val();
			obj.opis = $('#opis').val();

			if (obj.od_kiedy > obj.do_kiedy){
				$('#data_od').css("background-color",'red');
				$('#data_do').css("background-color",'red');
				alert('Proszę podać prawidłowy przedział czasu');
				return;
			}

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

			var out="";
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
				if(temp_date.getDay() > 0 && temp_date.getDay() < 6){
					if (out != "")
						out+=",";
					out+=(d/1000);
				}
			}
			obj.ile = 480;
			obj.zlec = "";
			obj.dni = out;
//			console.log(obj);
//return;
				$.ajax({
					url: 'http://192.168.34.17:88/scripts/karta_pr.php?callback=?',
					dataType: 'json',
					type: 'POST',
					data: obj,
					timeout: 1000
				}).success(function(obj){
					if (obj[0]=="OK"){
						window.opener.location.reload();
						alert(obj[3]);
						if (obj[4] > 480)
							alert('Dzienny czas pracy przekracza 8h.')
						if (obj[2] == "DELETE")
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