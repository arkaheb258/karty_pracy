<?php
session_start();
if ( !isset( $_SESSION["myusername"] ) ){
	header("location:/scripts/login.php?url=".$_SERVER["REQUEST_URI"]);
	exit;
} else {
	if ($_SESSION['timeout'] + 10 * 60 < time()) {
		header("location:/scripts/logout.php?session_timeout&url=".$_SERVER["REQUEST_URI"]);
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
		<link rel="stylesheet" type="text/css" href="/lib/css/jquery-ui.css" />
		<link rel="stylesheet" type="text/css" href="../raporty_tr/demo_table.css" />
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
				border: 1px solid #000; background: #ea641f url(images/ui-bg_gloss-wave_35_f6a828_500x100.png) 50% 50% repeat-x; color: #fff; font-weight: bold; 
			}
			.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default { 
				border: 1px solid #000; background: #ea641f url(images/ui-bg_glass_100_f6f6f6_1x400.png) 50% 50% repeat-x; font-weight: bold; color: #000; 
			}
			.ui-state-hover, .ui-widget-content .ui-state-hover, .ui-widget-header .ui-state-hover, .ui-state-focus, .ui-widget-content .ui-state-focus, .ui-widget-header .ui-state-focus { 
				border: 1px solid #fff; background: #fdf5ce url(images/ui-bg_glass_100_fdf5ce_1x400.png) 50% 50% repeat-x; font-weight: bold; color: #ea641f; 
			}
		</style>
		<title>Karty Pracy TR</title>
	</head>
	<body>
		<div id="_header" style="float:right;">		
			<div id="instrukcja">Instrukcja obsługi</div>
			<div id="logout">Wyloguj</div>
			<div id="change_pass">Zmień hasło</div>
			Zalogowano jako: <?php echo $_SESSION["myuser"]["nazwa"]; ?>
		</div>
		<table cellpadding="0" cellspacing="0" border="0" class="display unselectable" id="dane"></table>
		<br/>
		<div style="width:90%">
			<div id="add">Dodaj pracę</div>
			<div id="add_l4">Uzupełnij nieobecności</div>
<?php
		if($_SESSION["myuser"]["kart_perm"] != "0" && !isset($_REQUEST["user_id"]))
			echo '<div id="s_user">Lista parcowników</div>';
?>
<?php
//		if($_SESSION["myuser"]["kart_perm"] == "3" && !isset($_REQUEST["user_id"]))
//			echo '<div id="s_stat">Statystyki miesiąca</div>';
?>
			<div id="s_stat">Statystyki miesiąca</div>
			<br/>
			<br/>
		</div>
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
		<table cellpadding="0" cellspacing="0" border="1" class="display unselectable" id="dane_sum"></table>
		<div id="dialog_prace" title="Prace dnia"></div>
<?php
	if (isset($_REQUEST["int"])){
		echo '
			<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
			<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
			<script type="text/javascript" src="//datatables.net/release-datatables/media/js/jquery.dataTables.min.js"></script>
			<script type="text/javascript" src="//pajhome.org.uk/crypt/md5/2.2/md5-min.js"></script>
			';
	} else {
		echo '
			<script type="text/javascript" src="/lib/jquery.min.js"></script>
			<script type="text/javascript" src="/lib/jquery-ui.min.js"></script>
			<script type="text/javascript" src="/lib/jquery.dataTables.min.js"></script>
			<script type="text/javascript" src="/lib/md5-min.js"></script>
			';
	}
	if (isset($_REQUEST["user_id"]) && $_SESSION["myuser"]["kart_perm"] != "0") 
		echo '<script type="text/javascript" src="/scripts/baza_karta.php?user_id='.$_REQUEST["user_id"].'"></script>';
	else 
		echo '<script type="text/javascript" src="/scripts/baza_karta.php"></script>';

?>
	<script type="text/javascript">
		var sum_user_id = <?php if (isset($_REQUEST["user_id"])) echo $_REQUEST["user_id"]; else echo 'null';?>;
	
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

		$('#instrukcja').button().click(function(){
			window.open("instrukcja.pdf");
		});
		
		$('#add').button().click(function(){
			window.open("karta.php");
		});
		$('#add_l4').button().click(function(){
			window.open("karta_l4.php");
		});
		$('#s_user').button().click(function(){
			window.open("sum.php");
		});
		$('#s_stat').button().click(function(){
			window.open("stat.php");
		});
		$('#logout').button().click(function(){
			window.open("/scripts/logout.php?url=<?php echo $_SERVER["REQUEST_URI"]; ?>","_self");
		});
		$('#change_pass').button().click(function(){
			$( "#dialog-form" ).dialog( "open" );
		});
		
		if (sum_user_id != null){
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
								url: 'scripts/checklogin.php',
								type: 'POST',
								data: obj,
								timeout: 1000
							}).success(function(obj){
								if (obj == "OK"){
									alert('Hasło zostało zmienione.');
									ths.dialog( "close" );
									window.open("/scripts/logout.php?reload","_self");
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
		for (var k in karty){
			var karta = karty[k];
//			console.log(karta);
			var start = new Date();
			start.setTime(karta.data);
			var m = start.getMonth()+1; if (m<10) m = "0"+m; 
			var d = start.getDate(); if (d<10) d = "0"+d;
			var h = start.getHours(); if (h<10) h = "0"+h;
			var min = start.getMinutes(); if (min<10) min = "0"+min;
			start = start.getFullYear() + "/" + m + "/" + d;
			var dot = '';
			aDataSet.push([
				start,
				karta.dzial,
				karta.kat,
				min_to_h(karta.ile),
				karta.zlec,
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
					{ "sTitle": "Zlecenie / Zamówienie", "sWidth":"10em"},
					{ "sTitle": "Opis", "sWidth":"20em"}
				],
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
		echo "window.open('karta.php?user_id=".$_REQUEST["user_id"]."&id='+id);";
	else
		echo "window.open('karta.php?id='+id);";
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
		var daysInMonth = new Date(d.getFullYear(),d.getMonth()+1, 0).getDate()
		$('#dane_sum').append('<tr><th rowspan=2>L.p.</th><th rowspan=2>Dzial</th><th rowspan=2>Nr zlecenia</th><th rowspan=2>Treść zadania</th><th colspan='+(daysInMonth)+'>Dzień miesiąca ('+miesiac[d.getMonth()]+')</th><th rowspan=2>Suma</th></tr>');
		$('#dane_sum').append('<tr id="fr"></tr>');
		for (var di=1;di<=daysInMonth;di++){
			var th = $('<th>'+di+'</th>').appendTo('#fr');
			if (dayOfMonth(d,di) == 0)
				th.css("background-color","gray");
			if (dayOfMonth(d,di) == 6)
				th.css("background-color","lightblue");
		}
		var suma_dni = {};
		var suma_kat = {};
		var zlecenia_all = {};
		var zlec_iter = 0;
		for (var k in karty){
			var karta = karty[k];
			if(zlecenia_all["_"+karta.zlec] == undefined)
				zlecenia_all["_"+karta.zlec] = zlec_iter++;
				
//console.log(karta);
//console.log(zlec_iter);
//console.log(zlecenia_all);
			var temp_date = new Date();
			temp_date.setTime(karta.data);
			if (d.getMonth() != temp_date.getMonth()) continue;
//			if(!$('#sr_'+karta.kat_id+'[title="'+karta.zlec+'"]').length){
			if(!$('#sr_'+karta.kat_id+"_"+zlecenia_all["_"+karta.zlec]).length){
//				console.log('#sr_'+karta.kat_id+"_"+zlecenia_all["_"+karta.zlec]);
//				$('#dane_sum').append('<tr id="sr_'+karta.kat_id+'"><th class="lp"></th><td>'+karta.dzial+'</td><td>'+karta.zlec+'</td><td>'+karta.kat+'</td></tr>');
				var tr = $('<tr id="sr_'+karta.kat_id+"_"+zlecenia_all["_"+karta.zlec]+'" title="'+karta.zlec+'"><th class="lp"></th><td>'+karta.dzial+'</td><td>'+karta.zlec+'</td><td>'+karta.kat+'</td></tr>').appendTo('#dane_sum');
				tr.data('id_k',karta.kat_id);
				tr.data('id_d',karta.id_dzial);
				for (var di=1;di<=daysInMonth;di++){
					var td = $('<td class="sr_d" id="sr_'+karta.kat_id+'_'+di+"_"+zlecenia_all["_"+karta.zlec]+'"><span class="val"></span><ul class="menu" style="display:none;"></ul></td>').appendTo('#sr_'+karta.kat_id+"_"+zlecenia_all["_"+karta.zlec]);
					if (dayOfMonth(d,di) == 0)
						td.css("background-color","gray");
					if (dayOfMonth(d,di) == 6)
						td.css("background-color","lightblue");
				}
			}
			var temp2 = $('#sr_'+karta.kat_id+'_'+temp_date.getDate()+"_"+zlecenia_all["_"+karta.zlec]);
			temp2.css("cursor","pointer").css("text-align","center");
			var val = $('span',temp2).text()/1;
			$('span',temp2).text(val+karta.czas/1);

			$('.menu',temp2).append('<a href="karta.php?<?php if (isset($_REQUEST["user_id"]) && $_SESSION["myuser"]["kart_perm"] != "0") echo "user_id=".$_REQUEST["user_id"]."&"; ?>id='+karta.prac_id+'" target="_blank">'+karta.kat+" ("+karta.opis_p+')</a><br/>');
			var list = new Array();
			if(temp2.data('id'))
				list = temp2.data('id');
			list.push(karta.prac_id);
			temp2.data('id',list);
			if (suma_dni[temp_date.getDate()])
				suma_dni[temp_date.getDate()] += karta.czas/1;
			else
				suma_dni[temp_date.getDate()] = karta.czas/1;
			if (suma_kat[karta.kat_id+"_"+zlecenia_all["_"+karta.zlec]])
				suma_kat[karta.kat_id+"_"+zlecenia_all["_"+karta.zlec]] += karta.czas/1;
			else
				suma_kat[karta.kat_id+"_"+zlecenia_all["_"+karta.zlec]] = karta.czas/1;
//			}
		}
		$(".val").each(function(){
			var t = $(this).text()/1;
			if (t > 0)
				$(this).text(min_to_h2(t));
		});
//console.log(zlecenia_all);
//console.log('x');
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
			console.log('y');
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
				window.open('karta.php?id_k='+$(this).parent().data('id_k')+'&id_d='+$(this).parent().data('id_d'));
//console.log(id);
//			alert($(this).data( "id" ));
		});
		
//		console.log(suma_kat);
		$('#dane_sum').append('<tr id="lr"><th>Suma</th><th></th><th></th><th></th></tr>');
		for (var di=1;di<=daysInMonth;di++){
			var td = $('<th id="sr_sum_'+di+'"></th>').appendTo('#lr');
//			console.log(dayOfMonth(d,di));
			if (dayOfMonth(d,di) == 0)
				td.css("background-color","gray");
			if (dayOfMonth(d,di) == 6)
				td.css("background-color","lightblue");
		}
		for (var s in suma_dni){
			$('#sr_sum_'+s).text(min_to_h2(suma_dni[s]));
			if (suma_dni[s]<480)
				$('#sr_sum_'+s).css("background-color","yellow");
			if (suma_dni[s]>480)
				$('#sr_sum_'+s).css("background-color","red");
		}
		var suma_sum = 0;
		var lp = 1;
		$('.lp').each(function(){
			$(this).text(	lp++);
		});
		for (var s in suma_kat){
			$('#sr_'+s).append('<th>'+min_to_h2(suma_kat[s])+'</th>');
			suma_sum += suma_kat[s];
		}
		$('#lr').append('<th>'+min_to_h2(suma_sum)+'</th>');
			
	</script>
	</body>
</html>