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
					<tr><th>Dzień</th><td>
						<input type="text" id="data" class="datetimepicker od " style="width:15em;" name="od" /><br/>
					</td></tr>
					<tr><th>Wykonano pracę dla działu:</th><td><select id="dzial" name="dzial">
						<option value="null"></option>
					</select></td></tr>
					<tr><th><label for="czas">Czas pracy w godzinach:</label></th>
					<td><input id="czas" name="czas" /></td></tr>
					<tr><th><label for="zlec">Numer zlecenia / zamówienia:</label></th>
					<td><input type="text" id="zlec" name="zlec" /></td><td>Ostatnio wpisywane:<select id="zlec_last">
					</select></td></tr>
					<tr><th>Kategoria prac:</th></tr>
					<tr><td id="kategorie">
					</td></tr>
			<tr><td colspan="3">
				Opis prac:<br/><textarea id="opis" rows="6" name="opis"></textarea>
			</td></tr><tr>
			<td>Ostatnio wpisywane:<select id="opis_last"><option value="null"></option></select></td>
					</tr><tr><td colspan="3">
			</td></tr><tr><td colspan="1">
			</td><td colspan="2" style="text-align:right;">
				<div id="copy">Skopiuj</div>
				<div id="gotowe">Dodaj</div>
				<div id="del">Skasuj</div>
				<div id="close">Zamknij</div>
			</td></tr></table>
<!--			<input type="submit" value="Gotowe" onsubmit="return validateForm()" method="post"> -->
		</form>
	</body>
<?php
	if (isset($_REQUEST["int"])){
		echo '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>';
//		echo '<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>';
	} else {
		echo '<script type="text/javascript" src="jquery.min.js"></script>';
	}
	if (isset($_REQUEST["user_id"]) && $_SESSION["myuser"]["kart_perm"] != "0") 
		echo '<script type="text/javascript" src="/scripts/baza_karta.php?user_id='.$_REQUEST["user_id"].'"></script>';
	else 
		echo '<script type="text/javascript" src="/scripts/baza_karta.php"></script>';
?>
	<script type="text/javascript" src="jquery-ui.min.js"></script>
	<script type="text/javascript" src="/lib/jquery.ui.datepicker-pl.js"></script>
	<script type="text/javascript" src="/lib/jquery-ui-timepicker-addon.js"></script>
	<script type="text/javascript" src="/lib/globalize.js"></script>
	<script type="text/javascript" src="/lib/globalize.culture.de-DE.js"></script>

	<script type="text/javascript" src="/lib/date.js"></script>
	<script>
	
		var days_back = 1;
			
		_user_id = <?php echo $_SESSION["myuser"]["id"];?>;
		_prac_id = <?php if (isset($_REQUEST["id"])) echo $_REQUEST["id"]; else echo 'null';?>;
		_kat_id = <?php if (isset($_REQUEST["id_k"])) echo $_REQUEST["id_k"]; else echo 'null';?>;
		_dzia_id = <?php if (isset($_REQUEST["id_d"])) echo $_REQUEST["id_d"]; else echo 'null';?>;
		_copy_id = <?php if (isset($_REQUEST["copy_id"])) echo $_REQUEST["copy_id"]; else echo 'null';?>;
		var sum_user_id = <?php if (isset($_REQUEST["user_id"])) echo $_REQUEST["user_id"]; else echo 'null';?>;

		if (typeof console === "undefined")
			console = {log:function(){}};
		else if (typeof console.log === "undefined")
			console.log = function(){};
		
		if ((_user_id == 1 || _user_id == 40 || _user_id == 33) && (sum_user_id == null))
			days_back += 30;
		var today = new Date();
		if (today.getDay() == 1)
			days_back +=2;
			
		$(function() {
		
			Globalize.culture("de-DE");
			$.widget( "ui.timespinner", $.ui.spinner, {
				options: {
					// seconds
					step: 60 * 1000,
					min: +Globalize.parseDate( "00:01"),
					max: +Globalize.parseDate( "12:00"),
					// hours
					page: 60
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
//			$("#czas").spinner({
//				min: 0.25,
//				max: 12,
//				step: 0.25,
//				numberFormat: "n"		
// 		}).spinner( "value", 1 );

			$('#gotowe').button().click(function(){send(false);});
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
					
//console.log(kat2);
//console.log(k);
//console.log(kategorie[k]);
//console.log(kategorie[k]);
//				for (var k2 in kategorie[k]){
				for (var k3 in kat2){
					var k2 = kat2[k3].id;
					if (k2 == 300 && _user_id != 40 && _user_id != 33) continue;	//projekty tylko dla Marka i Janusza z TP
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
			var zlec_last = [];
			var opis_last = [];
			for (k in karty){
				var opis = karty[k].opis_p;//karty[k].
				if ($.inArray(opis, opis_last) == -1)
					opis_last.push(opis);
				if ($.inArray(karty[k].zlec, zlec_last) == -1)
					zlec_last.push(karty[k].zlec);
			}
			for (z in zlec_last)
				$('#zlec_last').append('<option>'+zlec_last[z]+'</option>');
			opis_last.sort();
			for (o in opis_last){
				var opis = opis_last[o];
				if (opis.length > 22)
					opis = opis.substring(0, 25)+"...";
				$('#opis_last').append('<option value="'+o+'" title="'+opis_last[o]+'">'+opis+'</option>');
			}
			$('#zlec_last').change(function(){
				$('#zlec').val($(this).val());
			});
			$('#opis_last').change(function(){
				$('#opis').val(opis_last[$(this).val()]);
			});
//console.log(opis_last);
//$('#zlec_last').hide();
			$('input').change(function() { clear_err(); });
			$('#czas').parent().click(function() { clear_err(); });
			$('#data').click(function() { clear_err(); });
			$('textarea').keypress(function() { clear_err(); });
			
//			var temp_date = new Date();
			if (_copy_id != null){
				var obj = find_id(_copy_id);
				if (obj){
					$('#kto_u').val('<?php echo $_SESSION["myuser"]["nazwa"];?>').attr('disabled', 'disabled');
					$("#data").datepicker({ minDate: -days_back,maxDate: 0}).datepicker('setDate', new Date());
					$('#czas').timespinner( "value",(obj.ile-(obj.ile%60))/60+":"+(obj.ile%60));
					
					$('#dzial').val(obj.id_dzial);
					$('#kat_'+obj.id_dzial).show();
					$('input[name=prace][value='+obj.kat_id+']').attr('checked','checked');
					if ($('input[name=prace][value='+obj.kat_id+']').parent().hasClass('sub'))
						$('input[name=prace][value='+obj.kat_id+']').parent().show();
					$('#opis').val(obj.opis_p);
					$('#zlec').val(obj.zlec);
				}				
			}
			
			if (_prac_id != null){
				$('#copy').button().click(function(){
					window.location.href = window.location.href.replace("id=","copy_id=");
				});
				var obj = find_id(_prac_id);
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
						$("#data").datepicker({ minDate: -days_back,maxDate: 0});
					}
					$('#kto_u').val(obj.kart_user).attr('disabled', 'disabled');
					$("#data").datepicker('setDate',temp_date);
					$('#czas').timespinner( "value",(obj.ile-(obj.ile%60))/60+":"+(obj.ile%60));
					
					$('#dzial').val(obj.id_dzial);
					$('#kat_'+obj.id_dzial).show();
					$('input[name=prace][value='+obj.kat_id+']').attr('checked','checked');
					if ($('input[name=prace][value='+obj.kat_id+']').parent().hasClass('sub'))
						$('input[name=prace][value='+obj.kat_id+']').parent().show();
					$('#opis').val(obj.opis_p);
					$('#zlec').val(obj.zlec);
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
			}
			
			if (sum_user_id != null){
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

		function send(del){
			clear_err();
			var obj = {};
			obj.id = _prac_id;
			obj.kto = _user_id;
			obj.kiedy = $("#data").datepicker('getDate').getTime();
//console.log(($('#czas').timespinner( "value") - 	+Globalize.parseDate( "00:00"))/60000);
//			obj.ile = $('#czas').val().replace(",",".")*60;
			obj.ile = ($('#czas').timespinner( "value") - +Globalize.parseDate( "00:00"))/60000;
			obj.dzial = $('#dzial').val();
			obj.co = $('input[name=prace]:checked').val();
			obj.zlec = $('#zlec').val();
			obj.opis = $('#opis').val();

			if (del){
				obj.del = del;
			} else {
				var now = new Date();
				now = now.getTime()/1000;
				
				if (obj.kiedy/1000 < now - (days_back+1)*86400 || obj.kiedy/1000 > now){
					$("#data").css("background-color",'red');
					alert("Nieprawidłowa data.");
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
						console.log(karta);
						console.log(karta.kart_id);
						if (confirm("Podana kategoria prac dla danego dnia została już wpisana. Czy chcesz dopisać do karty ?")){
							obj.id = karta.kart_id;
							obj.opis = karty[k].opis_p + "\n" + obj.opis;
							obj.ile = karty[k].ile/1 + obj.ile/1;
						} else
							return;
					}
				}
			}
					
			var temp = {data:obj.kiedy,kat_id:obj.co,zlec:obj.zlec};
//			console.log(temp);

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
//			console.log(temp);
						if (temp)
							karty.push(temp);
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