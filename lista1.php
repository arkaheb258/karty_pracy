<?php
session_start();
if ( !isset( $_SESSION["myusername"] ) ){
	header("location:login.php?url=".$_SERVER["REQUEST_URI"]);
	exit;
} else {
	if ($_SESSION['timeout'] + 10 * 60 < time()) {
		header("location:logout.php?session_timeout&reload&url=".$_SERVER["REQUEST_URI"]);
//		header("location:logout.php?session_timeout&url=".$_SERVER["REQUEST_URI"]);
		exit;
	}
	else $_SESSION['timeout'] = time();
}
	$dzial = $_SESSION["myuser"]["dzial"];
	$dzial = substr($dzial,0,2);
	$nr = $_SESSION["myuser"]["nr"];
	if ($nr == 913 || $nr == 914 )
		$dzial = "TP";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl" lang="pl">
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7; IE=EmulateIE9" /> 
		<link rel="stylesheet" type="text/css" href="jquery-ui.css" />
		<link rel="stylesheet" type="text/css" href="demo_table.css" />
		<style  type="text/css">
			.unselectable {
			   -moz-user-select: -moz-none;
			   -khtml-user-select: none;
			   -webkit-user-select: none;
			   -o-user-select: none;
			   user-select: none;
			}
			.ui-autocomplete.ui-widget-content { background: white; }
			.ui-widget-header { 
				border: 1px solid #000; background: #ea641f; color: #fff; font-weight: bold; 
			}
			.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default { 
				border: 1px solid #000; background: #ea641f; font-weight: bold; color: #000; 
			}
			.ui-state-hover, .ui-widget-content .ui-state-hover, .ui-widget-header .ui-state-hover, .ui-state-focus, .ui-widget-content .ui-state-focus, .ui-widget-header .ui-state-focus { 
				border: 1px solid #fff; background: #fdf5ce; font-weight: bold; color: #ea641f; 
			}
			.ui-dialog .ui-dialog-titlebar-close span{
				margin: -8px;
			}
			.yes_print_ {display: none;}
			@media print {
				body {font-size: 12px;}
				.no_print_ {display: none;}
				.yes_print_ {display: block;}
				.dataTables_wrapper {display: none;}
			}
		</style>
		<title>Karty Pracy TR</title>
	</head>
	<body>
		<div id="_header" style="float:right;" class="no_print_">		
			<div id="instrukcja">Instrukcja obsługi</div>
			<div id="logout">Wyloguj</div>
			<div id="change_pass">Zmień hasło</div>
			Zalogowano jako: <?php echo $_SESSION["myuser"]["nazwa"]; ?>
		</div>
		<br/>
		<div style="width:90%" class="no_print_">
			<div id="add">Dodaj pracę</div>
			<div id="add_l4">Uzupełnij nieobecności</div>
			<div id="s_user" style="display:none;">Lista pracowników</div>
			<div id="s_zad" style="display:none;">Zadania</div>
			<div id="s_stat">Statystyki miesiąca</div>
			<div id="cp_last" style="display:none">Kopiuj</div>
			<br/>
			<br/>
		</div>
		<table border="0" class="yes_print_" style="width:100%;">
			<tbody style="width:100%;">
				<tr><td>Dział <?php echo $dzial; ?></td></tr>
				<tr><td style="text-align: center; font-size: 2em; font-weight: bold;">KARTA PRACY za miesiąc <span id="druk_miesiac"></span> <span id="druk_rok"></span></td></tr>
			</tbody>
		</table>
		
		<table cellpadding="0" cellspacing="0" border="1" class="display unselectable" id="dane_sum"></table>
			<br/>
		<div id="dialog-form" title="Zmień hasło">
			<p class="validateTips"></p>
			<form><fieldset>
				<label for="password">Stare hasło:</label><br/>
				<input type="password" name="old_pass" id="old_pass" value="" class="text ui-widget-content ui-corner-all" /><br/>
				<label for="password">Nowe hasło:</label><br/>
				<input type="password" name="new_pass" id="new_pass" value="" class="text ui-widget-content ui-corner-all" /><br/>
				<label for="password">Powtórz nowe hasło:</label><br/>
				<input type="password" name="new_pass_2" id="new_pass_2" value="" class="text ui-widget-content ui-corner-all" />
			</fieldset></form>
		</div>		
		<table cellpadding="0" cellspacing="0" border="0" class="display unselectable no_print_" id="dane"></table>
		<div id="dialog_prace" title="Prace dnia"></div>
		<script type="text/javascript" src="jquery.min.js"></script>
		<script type="text/javascript" src="jquery-ui.min.js"></script>
		<script type="text/javascript" src="jquery.dataTables.min.js"></script>
		<script type="text/javascript" src="md5-min.js"></script>
<?php
	if (isset($_REQUEST["user_id"]) && $_SESSION["myuser"]["kart_perm"] != "0")  {
		if (isset($_REQUEST["year"]) && isset($_REQUEST["month"])) 
			echo '<script type="text/javascript" src="baza_karta1.php?user_id='.$_REQUEST["user_id"].'&month='.$_REQUEST["month"].'&year='.$_REQUEST["year"].'"></script>';
		else	
			echo '<script type="text/javascript" src="baza_karta1.php?user_id='.$_REQUEST["user_id"].'"></script>';
	} else {
		if (isset($_REQUEST["month"])){
			echo '<script type="text/javascript" src="baza_karta1.php?month='.$_REQUEST["month"];
			if (isset($_REQUEST["year"])) echo '&year='.$_REQUEST["year"];
			echo '"></script>';
		} else	
			echo '<script type="text/javascript" src="baza_karta1.php"></script>';
	}
?>
	<script type="text/javascript">
		var sum_user_id = <?php if (isset($_REQUEST["user_id"])) echo $_REQUEST["user_id"]; else echo 'null';?>;
		var _user_id = <?php echo $_SESSION["myuser"]["id"];?>;
		var _user_perm = <?php echo $_SESSION["myuser"]["kart_perm"];?>;
		
		function min_to_h(min){
			if (min <= 0)
				return "00:00";
			var m = min%60;
			var h = (min-m)/60;
			if (m < 10)
				m = "0"+m;
			if (h < 10)
				h = "0"+h;
			return h+":"+m;
		}

		function min_to_h2(min){
			if (min <= 0)
				return "0h";
			var m = min%60;
			var h = (min-m)/60;
			var out = "";
			if (h>0)
				out += h+"h ";
			if (m>0)
				out += m+"min.";
			return out;
		}
		
		if (typeof console === "undefined")
			console = {log:function(){}};
		else if (typeof console.log === "undefined")
			console.log = function(){};

		var suser_link = "<?php
	if (isset($_REQUEST["user_id"]) && $_SESSION["myuser"]["kart_perm"] != "0") 
		echo "?user_id=".$_REQUEST["user_id"];
?>";
		
//console.log(suser_link);

		$('#instrukcja').button().click(function(){
			window.open("instrukcja.pdf");
		});
		
		$('#add').button().click(function(){
			window.open("karta1.php"+suser_link);
		});
		$('#add_l4').button().click(function(){
//			window.open("karta_l4.php");
			window.open("karta1.php?l4"+suser_link.replace("?","&"));
		});
		$('#s_user').button().click(function(){
			window.open("sum1.php");
		});
		$('#s_stat').button().click(function(){
			window.open('stat1.php'+suser_link);
		});
		$('#logout').button().click(function(){
			window.open("logout.php?url=<?php echo $_SERVER["REQUEST_URI"]; ?>","_self");
		});
		$('#change_pass').button().click(function(){
			$( "#dialog-form" ).dialog( "open" );
		});
		
		$('#cp_last').button().click(function(){
			var wczoraj = [];
			for (var k in karty){
				if (karty[k].data == ostatni)			
					wczoraj.push(karty[k].prac_id);
			}
			var powczoraj = new Date();
			powczoraj.setTime(ostatni+60*60*24*1000);
			console.log("kopia z");
			// console.log(ostatni);
			console.log(new Date(ostatni));
			console.log("kopia na");
			console.log(powczoraj);
			if (powczoraj.getDay() == 6)
				powczoraj.setDate(powczoraj.getDate()+2);
			console.log("wczorajsze id");
			console.log(wczoraj);
			for (var w in wczoraj) {
				var link = "karta1.php?copy_id="+wczoraj[w];
				<?php if (isset($_REQUEST["user_id"])) echo 'link += "&user_id="+'.$_REQUEST["user_id"].';'; ?>
				link += "&add";
				link += "&day="+powczoraj.getTime();
				window.open(link);
			}
		});
		
		if(false 
			|| _user_id == 1
			|| _user_id == 54 	//Szweda
			|| _user_id == 40 	//Miziak
			|| _user_id == 33	//Janusz
		) {
			$('#cp_last').show();
		}
		
		if (sum_user_id != null && sum_user_id != _user_id && _user_id != 1){
			$('#add').hide();
			$('#add_l4').hide();
			$('#s_user').hide();
			$('#_header').hide();
		}

		$(function() {
			var old_pass = $( "#old_pass" ),
				new_pass = $( "#new_pass" ),
				new_pass_2 = $( "#new_pass_2" ),
				allFields = $( [] ).add( old_pass ).add( new_pass ).add( new_pass_2 ),
				tips = $( ".validateTips" );
	 
			function updateTips( t ) {
				tips
					.text( t )
					.addClass( "ui-state-highlight" );
				setTimeout(function() {
					tips.removeClass( "ui-state-highlight", 1500 );
				}, 500 );
			}
	 
			function checkLength( o, n, min, max ) {
				if ( o.val().length > max || o.val().length < min ) {
					o.addClass( "ui-state-error" );
					updateTips( "Długość " + n + " musi być pomiędzy " +
						min + " a " + max + "." );
					return false;
				} else {
					return true;
				}
			}
	 
			function checkRegexp( o, regexp, n ) {
				if ( !( regexp.test( o.val() ) ) ) {
					o.addClass( "ui-state-error" );
					updateTips( n );
					return false;
				} else {
					return true;
				}
			}

			function blad( o, war, tekst ) {
				if ( !war ) {
					o.addClass( "ui-state-error" );
					updateTips( tekst );
					return false;
				} else {
					return true;
				}
			}
			
			$( "#dialog-form" ).dialog({
				autoOpen: false,
				height: 400,
				width: 350,
				modal: true,
				buttons: {
					"Zmień hasło": function() {
						var bValid = true;
						allFields.removeClass( "ui-state-error" );
						bValid = bValid && blad(old_pass, (hex_md5(old_pass.val()) == '<?php echo $_SESSION["myuser"]["pass_md5"]; ?>'),"Błędne hasło.");
//						console.log(hex_md5(old_pass.val()));
						bValid = bValid && checkLength( new_pass, "hasła", 3, 26 );
						bValid = bValid && blad(new_pass_2, (new_pass.val() == new_pass_2.val()),"Hasła muszą być takie same.");
//						bValid = bValid && checkRegexp( old_pass, /^([0-9a-zA-Z])+$/, "Password field only allow : a-z 0-9" );
						if ( bValid ) {
//							alert("md5 hasła to: "+hex_md5(new_pass.val()))
							var obj = {"myusername":<?php echo $_SESSION["myuser"]["nr"];?>,"mypassword":old_pass.val(),"mynewpass":new_pass.val()};
							var ths = $( this );
							$.ajax({
								url: 'checklogin.php',
								type: 'POST',
								data: obj,
								timeout: 1000
							}).success(function(obj){
								if (obj == "OK"){
									alert('Hasło zostało zmienione.');
									ths.dialog( "close" );
									window.open("logout.php?reload","_self");
								} else
									alert(obj);
							}).fail( function() {
								alert('Błąd zmiany hasła');
//								ths.dialog( "close" );
							});
//							$( this ).dialog( "close" );
						}
					},
					"Anuluj": function() {
						$( this ).dialog( "close" );
					}
				},
				close: function() {
					allFields.val( "" ).removeClass( "ui-state-error" );
				}
			});
		 });

		var aDataSet = [];
		if (karty) for (var k in karty){
			var karta = karty[k];
//			console.log(karta);
			var start = new Date();
			start.setTime(karta.data);
<?php if (isset($_REQUEST["month"])) echo "if (start.getMonth()+1 != ".$_REQUEST["month"].") continue;";?>
			if (start.getHours() == 23)
				start.setHours(start.getHours()+1);
			var m = start.getMonth()+1; if (m<10) m = "0"+m; 
			var d = start.getDate(); if (d<10) d = "0"+d;
			var h = start.getHours(); if (h<10) h = "0"+h;
			var min = start.getMinutes(); if (min<10) min = "0"+min;
			start = start.getFullYear() + "/" + m + "/" + d;
			var dot = '';
			var t_zlec = "";
			// console.log(karta.zadanie);
			// console.log(zadania);
			if (karta.zadanie) {
				if (zadania[karta.zadanie])
					t_zlec = zadania[karta.zadanie].nazwa;
				else {
					console.log("Brak zadania "+karta.zadanie)
					if (karta.zlec) t_zlec = karta.zlec;
				}
			} else if (karta.zlec) t_zlec = karta.zlec;
			aDataSet.push([
				start,
				karta.dzial,
				karta.kat,
				min_to_h(karta.ile),
				t_zlec,
				karta.opis_p,
				karta.prac_id
			]);
		}
		
		$(document).ready(function() {
			oTable = $('#dane').dataTable( {
				"aaData": aDataSet,
				"iDisplayLength": 25,
				"aoColumns": [
//					{ "sTitle": "ID" , "sClass": "center", },
					{ "sTitle": "Data", "sWidth":"10em" ,"sClass": "center"},
					{ "sTitle": "Dział", "sWidth":"20em"},
					{ "sTitle": "Kategoria", "sWidth":"20em"},
					{ "sTitle": "Czas", "sWidth":"4em"},
//					{ "sTitle": "Zlecenie / Zamówienie", "sWidth":"10em"},
					{ "sTitle": "Zlecenie / Zadanie", "sWidth":"10em"},
					{ "sTitle": "Opis", "sWidth":"20em"}
				],
				"aaSorting": [[ 0, "desc" ]],
				"fnDrawCallback": function( oSettings ) {
					$("#dane tbody tr:not(.has_dblclick)").dblclick( function( e ) {
						var ths = $(this); 
						var nr = null;
						var i = 0;
						$("tr",$(this).parent()).each(function(){
							if ($(this)[0] == ths[0]) nr = i;
							i++;
						});
						var id = null;
						nr += oSettings._iDisplayStart;
//						console.log(oSettings._iDisplayStart);
						if (nr != null){
							var l = oSettings.aoData[oSettings.aiDisplay[nr]]._aData.length;
//							console.log(oSettings.aoData[oSettings.aiDisplay[nr]]._aData);
							id  = oSettings.aoData[oSettings.aiDisplay[nr]]._aData[l-1];
						}
//						var id = $('td:first',this).text();
<?php
	if (isset($_REQUEST["user_id"]) && $_SESSION["myuser"]["kart_perm"] != "0") 
		echo "window.open('karta1.php?user_id=".$_REQUEST["user_id"]."&id='+id);";
	else
		echo "window.open('karta1.php?id='+id);";
?>
		//				alert('dblclick id = '+id);
					}).addClass('has_dblclick').css("cursor","pointer");
					$("#dane tbody tr:not(.has_click)").click( function( e ) {
						if ( $(this).hasClass('row_selected') ) {
							$(this).removeClass('row_selected');
						}
						else {
							oTable.$('tr.row_selected').removeClass('row_selected');
							$(this).addClass('row_selected');
						}
					}).addClass('has_click');
				},
				"oLanguage":{
					"sProcessing":   "Proszę czekać...",
					"sLengthMenu":   "Pokaż _MENU_ pozycji",
					"sZeroRecords":  "Nie znaleziono żadnych pasujących indeksów",
					"sInfo":         "Pozycje od _START_ do _END_ z _TOTAL_ łącznie",
					"sInfoEmpty":    "Pozycji 0 z 0 dostępnych",
					"sInfoFiltered": "(filtrowanie spośród _MAX_ dostępnych pozycji)",
					"sInfoPostFix":  "",
					"sSearch":       "Szukaj:",
					"sUrl":          "",
					"oPaginate": {
					"sFirst":    "Pierwsza",
					"sPrevious": "Poprzednia",
					"sNext":     "Następna",
					"sLast":     "Ostatnia"
					}
				}
//				,"aoColumnDefs": [ { "bSearchable": false, "bVisible": false, "aTargets": [ 0 ] } ]
			} );	
		} );
	
		function dayOfMonth(d,di){
			var day = new Date(d.getFullYear(),d.getMonth(), di);
			return day.getDay();
		}

		var miesiac = ["Styczeń","Luty","Marzec","Kwiecień","Maj","Czerwiec","Lipiec","Sierpień","Wrzesień","Październik","Listopad","Grudzień"];

		var d = new Date();
//		console.log(d);
<?php if (isset($_REQUEST["month"])) echo "d.setMonth(".$_REQUEST["month"]."-1);"; ?>
<?php if (isset($_REQUEST["year"])) echo "d.setFullYear(".$_REQUEST["year"].");"; ?>
		var daysInMonth = new Date(d.getFullYear(),d.getMonth()+1, 0).getDate()
		$('#dane_sum').append('<tr><th rowspan=2>L.p.</th><th rowspan=2>Dzial</th><th rowspan=2>Nr zlecenia<br/>/ Zadanie</th><th rowspan=2>Treść zadania</th><th colspan='+(daysInMonth)+'>Dzień miesiąca ('+miesiac[d.getMonth()]+')</th><th rowspan=2>Suma</th></tr>');
		$("#druk_miesiac").text(miesiac[d.getMonth()].toUpperCase());
		$("#druk_rok").text(d.getFullYear());
		$('#dane_sum').append('<tr id="fr"></tr>');
		for (var di=1;di<=daysInMonth;di++){
			var th = $('<th>'+di+'</th>').appendTo('#fr');
			if (dayOfMonth(d,di) == 0)
				th.css("background-color","gray");
			if (dayOfMonth(d,di) == 6)
				th.css("background-color","lightblue");
			if(swieta[d.getMonth()+1] && swieta[d.getMonth()+1][di])
				th.css("background-color","LightPink ").attr("title",swieta[d.getMonth()+1][di]);
		}
		var suma_dni = {};
		var suma_kat = {};
		var ostatni = 0;
		var zlecenia_all = {};
		var zlec_iter = 0;
		for (var k in karty){
			var karta = karty[k];
			var temp_date = new Date();
			temp_date.setTime(karta.data);
			if (temp_date.getHours() == 23)
				temp_date.setHours(temp_date.getHours()+1);
			if (ostatni < temp_date.getTime())
				ostatni = temp_date.getTime();
			if (d.getMonth() != temp_date.getMonth()) continue;
			if (d.getFullYear() != temp_date.getFullYear()) continue;
			
			if (karta.zadanie && zadania[karta.zadanie])
				karta.zlec2 = zadania[karta.zadanie].nazwa;
			else
				karta.zlec2 = karta.zlec;
			if(zlecenia_all[karta.zlec+"_"+karta.zlec2] == undefined)
				zlecenia_all[karta.zlec+"_"+karta.zlec2] = zlec_iter++;
				
//			if(!$('#sr_'+karta.kat_id+'[title="'+karta.zlec2+'"]').length){
			if(!$('#sr_'+karta.kat_id+"_"+zlecenia_all[karta.zlec+"_"+karta.zlec2]).length){
//				console.log('#sr_'+karta.kat_id+"_"+zlecenia_all[karta.zlec+"_"+karta.zlec2]);
//				$('#dane_sum').append('<tr id="sr_'+karta.kat_id+'"><th class="lp"></th><td>'+karta.dzial+'</td><td>'+karta.zlec2+'</td><td>'+karta.kat+'</td></tr>');
				var tr = $('<tr id="sr_'+karta.kat_id+"_"+zlecenia_all[karta.zlec+"_"+karta.zlec2]+'"><th class="lp"></th><td>'+karta.dzial+'</td><td class="czyn">'+karta.zlec2+'</td><td>'+karta.kat+'</td></tr>').appendTo('#dane_sum');
				// console.log($('.czyn',tr));
				if (_user_perm > 0) {
					$('.czyn',tr).data('id_zad',karta.zadanie).dblclick(function(){
						window.open('zadania.php?id='+$(this).data('id_zad'));
						// console.log($(this).data('id_zad'));
					});
				}
// console.log(tr.attr('title'));
if (karta.zadanie){
	// console.log(karta);
	// console.log(zadania[karta.zadanie]);
	if (zadania[karta.zadanie])
	if (zadania[karta.zadanie].typ == "PNU"){
		var pnu = zadania[karta.zadanie].zlecenie.split('/');
		tr.attr('title', "PNU Projekt nr " + pnu[0] + " etap " + pnu[1] + " zadanie " + pnu[2]);
	} else if (zadania[karta.zadanie].typ == "MPK") {
		tr.attr('title', "MPK " + zadania[karta.zadanie].zlecenie);
	} else {
		tr.attr('title', zadania[karta.zadanie].zlecenie);
	}
} else 
	tr.attr('title', karta.zlec2);
				
				tr.data('id_k',karta.kat_id);
				tr.data('id_d',karta.id_dzial);
				for (var di=1;di<=daysInMonth;di++){
					var td = $('<td class="sr_d" id="sr_'+karta.kat_id+'_'+di+"_"+zlecenia_all[karta.zlec+"_"+karta.zlec2]+'"><span class="val"></span><ul class="menu" style="display:none;"></ul></td>').appendTo('#sr_'+karta.kat_id+"_"+zlecenia_all[karta.zlec+"_"+karta.zlec2]);
					if (dayOfMonth(d,di) == 0)
						td.css("background-color","gray");
					if (dayOfMonth(d,di) == 6)
						td.css("background-color","lightblue");
					if(swieta[d.getMonth()+1] && swieta[d.getMonth()+1][di])
						td.css("background-color","LightPink ").attr("title",swieta[d.getMonth()+1][di]);
				}
			}
			var temp2 = $('#sr_'+karta.kat_id+'_'+temp_date.getDate()+"_"+zlecenia_all[karta.zlec+"_"+karta.zlec2]);
			temp2.css("cursor","pointer").css("text-align","center");
			var val = $('span',temp2).text()/1;
			$('span',temp2).text(val+karta.czas/1);
			var kd = new Date();
			kd.setTime(karta.data/1+ 3*60*60*1000);
			// $('span',temp2).text(kd.getDay());
			var max_h_delay = 48;
			if (kd.getDay() == 5) max_h_delay += 48;
			// if (kd.getDay() > 3) max_h_delay + 48;
			if (karta.timestamp_diff_h > max_h_delay){
				// console.log(karta.kat_id);
				// console.log(karta.timestamp_diff_h);
				// console.log(kd);
				if ((karta.kat_id != 548)
				&&	(karta.kat_id != 545)
				&& true) {
					// console.log(karta.kat_id);
					// console.log(karta.data);
					// console.log(karta.timestamp);
					// console.log(karta.timestamp_diff_h);
					// console.log(kd);
					// console.log(kd.getDay());
					$('span',temp2).parent().css('background-color','red')
					.attr('title','wpisano: '+karta.timestamp);
					// $('span',temp2).parent().title('wpisano:');
				}
			}
			// $('span',temp2).text((karta.timestamp_diff_h*60/24).toFixed(0));

			$('.menu',temp2).append('<a href="karta1.php?<?php if (isset($_REQUEST["user_id"]) && $_SESSION["myuser"]["kart_perm"] != "0") echo "user_id=".$_REQUEST["user_id"]."&"; ?>id='+karta.prac_id+'" target="_blank">'+karta.kat+" ("+karta.opis_p+')</a><br/>');
			var list = new Array();
			if(temp2.data('id'))
				list = temp2.data('id');
			list.push(karta.prac_id);
			temp2.data('id',list);
			if (suma_dni[temp_date.getDate()]) {
				suma_dni[temp_date.getDate()] += karta.czas/1;
			} else {
				suma_dni[temp_date.getDate()] = karta.czas/1;
			}
			if (suma_kat[karta.kat_id+"_"+zlecenia_all[karta.zlec+"_"+karta.zlec2]]) {
				suma_kat[karta.kat_id+"_"+zlecenia_all[karta.zlec+"_"+karta.zlec2]] += karta.czas/1;
			} else {
				suma_kat[karta.kat_id+"_"+zlecenia_all[karta.zlec+"_"+karta.zlec2]] = karta.czas/1;
			}
		}
		$(".val").each(function(){
			var t = $(this).text()/1;
			if (t > 0)
				$(this).text(min_to_h2(t));
		});
		
		$('#dialog_prace')
		.dialog({
			autoOpen: false,
			buttons: {
				"Zamknij": function() {
					$( this ).dialog( "close" );
				}
			}			
		})
;//		.mouseleave(function(){
//			$( this ).dialog( "close" ); 
//			$(this).hide();
//		});
		$( ":data(id)" ).click(function(){
			var id = $(this).data( "id" );
			$('#dialog_prace').html($('.menu',this).html());
			$('#dialog_prace').dialog('open');//show();
//			$('.menu',this).show();
		}).mouseleave(function(){
//			$('#dialog_prace').hide();
//			$('.menu',this).hide();
		});

		$( ".sr_d" ).click(function(){
			var id = $(this).data( "id" );
			if(id){
//				$('.menu',this).show();
//				window.open('karta.php?id='+id[0]);
			} else
				window.open('karta1.php?id_k='+$(this).parent().data('id_k')+'&id_d='+$(this).parent().data('id_d'));
//console.log(id);
//			alert($(this).data( "id" ));
		});
		
		$('#dane_sum').append('<tr id="lr"><th>Suma</th><th></th><th></th><th></th></tr>');
		for (var di=1;di<=daysInMonth;di++){
			var td = $('<th id="sr_sum_'+di+'"></th>').appendTo('#lr');
			if (dayOfMonth(d,di) == 0)
				td.css("background-color","gray");
			if (dayOfMonth(d,di) == 6)
				td.css("background-color","lightblue");
			if(swieta[d.getMonth()+1] && swieta[d.getMonth()+1][di])
				td.css("background-color","LightPink ").attr("title",swieta[d.getMonth()+1][di]);
		}
		
		var suma_przep_pryw = 0;
		var suma_nadg = 0;
		var suma_nadg_sw = 0;
		
		for (var s in suma_kat){
			if (s.indexOf("558")==0)
				suma_przep_pryw += suma_kat[s];
		}
		
		
		for (var s in suma_dni){
			$('#sr_sum_'+s).text(min_to_h2(suma_dni[s]));
			if (dayOfMonth(d,s)>5 || dayOfMonth(d,s)==0){
				if (suma_dni[s] > 480)
					suma_nadg_sw += 480;
				else
					suma_nadg_sw += suma_dni[s];
			}
			if ((sum_user_id == 43)
			|| (sum_user_id == 60)){
				if (suma_dni[s]<420)
					$('#sr_sum_'+s).css("background-color","yellow");
				if (suma_dni[s]>420){
					if (suma_przep_pryw-(suma_dni[s]-420) >= 0)
						suma_przep_pryw -= (suma_dni[s]-420);
					else
						$('#sr_sum_'+s).css("background-color","yellow");
						// $('#sr_sum_'+s).css("background-color","red");
				}
			}
			else {
				if (suma_dni[s]<480)
					$('#sr_sum_'+s).css("background-color","yellow");
				if (suma_dni[s]>480){
					if (suma_przep_pryw-(suma_dni[s]-480) >= 0)
						suma_przep_pryw -= (suma_dni[s]-480);
					else {
						$('#sr_sum_'+s).css("background-color","yellow");
						// $('#sr_sum_'+s).css("background-color","red");
						suma_nadg += suma_dni[s]-480;
					}
				}
			}
		}
		
		console.log("Nieodpracowanych przepustek: " + suma_przep_pryw/60);
		console.log("Nadgodzin normalnych: " + suma_nadg/60);
		console.log("Nadgodzin świątecznych: " + suma_nadg_sw/60);
		
		var prev = new Date(),
			urlop = [],
			plan = [];
			
		for (var k in karty){
			var karta = karty[k];
			if (karta.kat_id == 545){
				prev.setTime(karta.data/1);
				if (!plan[prev.getFullYear()]) plan[prev.getFullYear()] = 0;
				if (!urlop[prev.getFullYear()]) urlop[prev.getFullYear()] = 0;
				if (prev > new Date()) {
					plan[prev.getFullYear()]++;
				} else {
					urlop[prev.getFullYear()]++;
				}
			}
		}
		prev = new Date();
		if (user_info.urlop_zalegly) {
			if (user_info.urlop_zalegly >= urlop[prev.getFullYear()]) {
				console.log("W bierzącym roku wykorzystano: " + urlop[prev.getFullYear()] + " z " + user_info.urlop_zalegly + " dni zaległego urlopu.");
				urlop[prev.getFullYear()] = 0;
			} else {
				console.log("W bierzącym roku wykorzystano: " + user_info.urlop_zalegly + " z " + user_info.urlop_zalegly + " dni zaległego urlopu.");
				urlop[prev.getFullYear()] -= user_info.urlop_zalegly;
			}
		}
		console.log("W bierzącym roku wykorzystano: " + urlop[prev.getFullYear()] + " z 26 dni urlopu.");
		console.log("Zaplanowano : " + plan[prev.getFullYear()] + " dni urlopu.");
		if ((26 - urlop[prev.getFullYear()] - plan[prev.getFullYear()]) > 0) {
			console.log("Pozostało do zaplanowania : " + (26 - urlop[prev.getFullYear()] - plan[prev.getFullYear()]) + " dni urlopu.");
		}
		
		var suma_sum = 0;
		var lp = 1;
		$('.lp').each(function(){
			$(this).text(	lp++);
		});
		for (var s in suma_kat){
			$('#sr_'+s).append('<th>'+min_to_h2(suma_kat[s])+'</th>');
			if (s.indexOf("558")!=0)
				suma_sum += suma_kat[s];
		}
		$('#lr').append('<th>'+min_to_h2(suma_sum)+'</th>');
		
		if(_user_perm>0){
			$('#s_user').show();
			$('#s_zad').button().show().click(function(){
				window.open("zadania.php");
			});
		}
	if ((new Date()).toDateString() == "Wed Apr 01 2015")
		if ((user_info.nr == 731) 
		|| (user_info.nr == 778)
		|| (user_info.nr == 771)
		// || (user_info.nr == 913)
		|| (user_info.nr == 940)
			)alert("Współczynnik premii za miesiąc Marzec: -3%");
		
		function lewe_wpisy(){
			console.log("kasowanie");
		}
		
	</script>
	</body>
</html>