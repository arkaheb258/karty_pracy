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
			<div id="add_l4">Urlop / L4</div>
			<div id="s_user" style="display:none;">Lista pracowników</div>
			<div id="s_zad" style="display:none;">Zadania</div>
			<div id="s_stat">Statystyki miesiąca</div>
			<div id="cp_last" style="display:none">Kopiuj</div>
			<br/>
			<br/>
		</div>
		<table cellpadding="0" cellspacing="0" border="1" class="display unselectable" id="dane_sum"></table>
			<br/>
		<table cellpadding="0" cellspacing="0" border="0" class="display unselectable no_print_" id="dane"></table>
		<div id="dialog_prace" title="Prace dnia"></div>
		<script type="text/javascript" src="jquery.min.js"></script>
		<script type="text/javascript" src="jquery-ui.min.js"></script>
		<script type="text/javascript" src="jquery.dataTables.min.js"></script>
		<script type="text/javascript" src="md5-min.js"></script>
    <script type="text/javascript" src="dialog.js"></script>
<?php
	if (isset($_REQUEST["user_id"]) && $_SESSION["myuser"]["kart_perm"] != "0")  {
		if (isset($_REQUEST["year"]) && isset($_REQUEST["month"])) 
			echo '<script type="text/javascript" src="baza_karta.php?user_id='.$_REQUEST["user_id"].'&month='.$_REQUEST["month"].'&year='.$_REQUEST["year"].'"></script>';
		else	
			echo '<script type="text/javascript" src="baza_karta.php?user_id='.$_REQUEST["user_id"].'"></script>';
	} else {
		if (isset($_REQUEST["month"])){
			echo '<script type="text/javascript" src="baza_karta.php?month='.$_REQUEST["month"];
			if (isset($_REQUEST["year"])) echo '&year='.$_REQUEST["year"];
			echo '"></script>';
		} else	
			echo '<script type="text/javascript" src="baza_karta.php"></script>';
	}
?>
	<script type="text/javascript">
		var sum_user_id = <?php if (isset($_REQUEST["user_id"])) echo $_REQUEST["user_id"]; else echo 'null';?>;
		var _user_id = <?php echo $_SESSION["myuser"]["id"];?>;
		var _user_perm = <?php echo $_SESSION["myuser"]["kart_perm"];?>;
		var suser_link = "<?php if (isset($_REQUEST["user_id"]) && $_SESSION["myuser"]["kart_perm"] != "0")  echo "?user_id=".$_REQUEST["user_id"]; ?>";
console.log(suser_link);
		
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
		
		function dayOfMonth(d,di){
			var day = new Date(d.getFullYear(),d.getMonth(), di);
			return day.getDay();
		}

		var miesiac = ["Styczeń","Luty","Marzec","Kwiecień","Maj","Czerwiec","Lipiec","Sierpień","Wrzesień","Październik","Listopad","Grudzień"];
		if (typeof console === "undefined") console = {log:function(){}}; else if (typeof console.log === "undefined") console.log = function(){};

		$('#instrukcja').button().click(function(){ window.open("instrukcja.pdf"); });
		
		$('#add').button().click(function(){ window.open("karta.php"+suser_link); });
		$('#add_l4').button().click(function(){ window.open("karta_l4.php"+suser_link); });
		$('#s_user').button().click(function(){ window.open("sum.php"); });
		$('#s_stat').button().hide().click(function(){ window.open('stat.php'+suser_link); });
		$('#logout').button().click(function(){ window.open("logout.php?url=<?php echo $_SERVER["REQUEST_URI"]; ?>","_self"); });
		$('#change_pass').button().click(function(){ $( "#dialog-form" ).dialog( "open" ); });
    
    $(dialog_pass.html('dialog-form')).appendTo('body').dialog(dialog_pass.obj('dialog-form','<?php echo $_SESSION["myuser"]["nr"];?>', '<?php echo $_SESSION["myuser"]["pass_md5"]; ?>'));      
		
		$('#cp_last').button().click(function(){
				var link = "baza_obj.php?sql=call%20copy_day("+_user_id+",0,0)";
        console.log(link);
        $.ajax({
          url: link,
          type: 'POST',
          timeout: 1000
        }).success(function(obj){
          window.location.reload();
        }).fail( function() {
          alert('Błąd kopiowania');
        });
		});
		
		if (_user_id == 54 	//Szweda
			|| _user_id == 40 	//Miziak
			|| _user_id == 33	//Janusz
		) { $('#cp_last').show(); }
		
		if (sum_user_id != null && sum_user_id != _user_id && _user_id != 1){
			$('#add').hide();
			$('#add_l4').hide();
			$('#s_user').hide();
			$('#_header').hide();
		}

    //dolna tablica z pracami
		var aDataSet = [];
		if (karty) for (var k in karty){
			var karta = karty[k];
      // console.log(karta);
			var start = new Date();
			start.setTime(karta.data);
      // console.log(start);
			if (start.getUTCHours() == 23) start.setHours(start.getHours()+1);
			if (start.getUTCHours() == 22) start.setHours(start.getHours()+2);
      // console.log(start, start.toISOString().split('T')[0]);
			aDataSet.push([
				start.toISOString().split('T')[0],
				zadania[karta.zadanie].nazwa,
				min_to_h(karta.ile),
				karta.opis_p,
				karta.prac_id
			]);
		}
		$(document).ready(function() {
			oTable = $('#dane').dataTable( {
				"aaData": aDataSet,
				"iDisplayLength": 25,
				"aoColumns": [
					{ "sTitle": "Data", "sWidth":"10em" ,"sClass": "center"},
					{ "sTitle": "Zadanie", "sWidth":"10em"},
					{ "sTitle": "Czas", "sWidth":"4em" ,"sClass": "center"},
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
						if (nr != null){
							var l = oSettings.aoData[oSettings.aiDisplay[nr]]._aData.length;
							id  = oSettings.aoData[oSettings.aiDisplay[nr]]._aData[l-1];
						}
            window.open('karta.php?id='+id+suser_link.replace('?','&'));
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
			} );	
		} );

		var d = new Date();
//		console.log(d);
<?php if (isset($_REQUEST["month"])) echo "d.setMonth(".$_REQUEST["month"]."-1);"; ?>
<?php if (isset($_REQUEST["year"])) echo "d.setFullYear(".$_REQUEST["year"].");"; ?>
		var daysInMonth = new Date(d.getFullYear(),d.getMonth()+1, 0).getDate()
		$('#dane_sum').append('<tr><th rowspan=2>L.p.</th><th rowspan=2>Zadanie</th><th colspan='+(daysInMonth)+'>Dzień miesiąca ('+miesiac[d.getMonth()]+')</th><th rowspan=2>Suma</th></tr>');

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
		var zlecenia_all = {};
		var zlec_iter = 0;
		for (var k in karty){
			var karta = karty[k];
			var temp_date = new Date();
			temp_date.setTime(karta.data);
			if (temp_date.getHours() == 23)
				temp_date.setHours(temp_date.getHours()+1);
			if (d.getMonth() != temp_date.getMonth()) continue;
			if (d.getFullYear() != temp_date.getFullYear()) continue;
			
      karta.zlec2 = zadania[karta.zadanie].nazwa;
			if(zlecenia_all[karta.zlec2] == undefined)
				zlecenia_all[karta.zlec2] = zlec_iter++;
			if(!$('#sr_'+zlecenia_all[karta.zlec2]).length){
				var tr = $('<tr id="sr_'+zlecenia_all[karta.zlec2]+'"><th class="lp"></th><td class="czyn">'+karta.zlec2+'</td></tr>').appendTo('#dane_sum');
				if (_user_perm > 0) {
					$('.czyn',tr).data('id_zad',karta.zadanie).dblclick(function(){
						window.open('zadania.php?id='+$(this).data('id_zad'));
					});
				}
        if (karta.zadanie){
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
				
				for (var di=1;di<=daysInMonth;di++){
					var td = $('<td class="sr_d" id="sr_'+di+"_"+zlecenia_all[karta.zlec2]+'"><span class="val"></span><ul class="menu" style="display:none;"></ul></td>').appendTo('#sr_'+zlecenia_all[karta.zlec2]);
					if (dayOfMonth(d,di) == 0)
						td.css("background-color","gray");
					if (dayOfMonth(d,di) == 6)
						td.css("background-color","lightblue");
					if(swieta[d.getMonth()+1] && swieta[d.getMonth()+1][di])
						td.css("background-color","LightPink ").attr("title",swieta[d.getMonth()+1][di]);
				}
			}
			var temp2 = $('#sr_'+temp_date.getDate()+"_"+zlecenia_all[karta.zlec2]);
			temp2.css("cursor","pointer").css("text-align","center");
			var val = $('span',temp2).text()/1;
			$('span',temp2).text(val+karta.czas/1);
			var kd = new Date();
			kd.setTime(karta.data/1+ 3*60*60*1000);

			$('.menu',temp2).append('<a href="karta.php?<?php if (isset($_REQUEST["user_id"]) && $_SESSION["myuser"]["kart_perm"] != "0") echo "user_id=".$_REQUEST["user_id"]."&"; ?>id='+karta.prac_id+'" target="_blank">'+karta.opis_p+'</a><br/>');
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
			if (suma_kat[zlecenia_all[karta.zlec2]]) {
				suma_kat[zlecenia_all[karta.zlec2]] += karta.czas/1;
			} else {
				suma_kat[zlecenia_all[karta.zlec2]] = karta.czas/1;
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
		});
		$( ":data(id)" ).click(function(){
			var id = $(this).data( "id" );
			$('#dialog_prace').html($('.menu',this).html());
			$('#dialog_prace').dialog('open');
		});

		$('#dane_sum').append('<tr id="lr"><th>Suma</th><th></th></tr>');
		for (var di=1;di<=daysInMonth;di++){
			var td = $('<th id="sr_sum_'+di+'"></th>').appendTo('#lr');
			if (dayOfMonth(d,di) == 0)
				td.css("background-color","gray");
			if (dayOfMonth(d,di) == 6)
				td.css("background-color","lightblue");
			if(swieta[d.getMonth()+1] && swieta[d.getMonth()+1][di])
				td.css("background-color","LightPink ").attr("title",swieta[d.getMonth()+1][di]);
		}

		for (var s in suma_dni){
			$('#sr_sum_'+s).text(min_to_h2(suma_dni[s]));
      if (suma_dni[s]<480)
        $('#sr_sum_'+s).css("background-color","yellow");
      if (suma_dni[s]>480){
        $('#sr_sum_'+s).css("background-color","yellow");
      }
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
		
	</script>
	</body>
</html>