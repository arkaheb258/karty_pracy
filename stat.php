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
		<link rel="stylesheet" type="text/css" href="kopex.css" />
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
			
			#loading {
				position: absolute;
				width: 100%;
				height: 100%;
				text-align: center;
				background: white;
				opacity: 0.75;				
			}
			#loading > span {
				top: 50%;
				position: relative;
				font-size: 3em;
			}
			
		</style>
  <title>Statystyki miesiąca</title>
  </head>
	<body style="height:100%;">
		<div id="loading"><span>Ładowanie danych</span></div>
		Dział: <select id="dzial" name="dzial">
			<option value="">Wszystkie</option>
		</select><br/>
		Od: <input type="text" id="data_od" class="datetimepicker od " style="width:15em;" name="od" /><br/>
		Do: <input type="text" id="data_do" class="datetimepicker do " style="width:15em;" name="od" /><br/>
		<div>Podział według działu:</br><table cellpadding="0" cellspacing="0" border="1" class="display" style="width:90%"><tbody id="stat">
    <tr><th rowspan="2">Dział</th><th colspan="4">Praca wykonana dla działu</th><th rowspan="2">Urlop</th><th rowspan="2">L4</th><th rowspan="2">Suma</th></tr>
    <tr id="prace_dla"></tr></tbody></table></div>
		<div id="pie" style="width:66%; margin: auto;"><img id="pie_pr" src="" alt="" /><img id="pie_lu" src="" alt="" /></div>
		<div>Podział według projektu:</br><table cellpadding="0" cellspacing="0" border="1" class="display" style="width:90%"><tbody id="proj">
    <tr><th style="width:50%;">Zadanie</th><th>Pracownicy</th><th>RBH</th></tbody></table></div>
	</body>
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="jquery-ui.min.js"></script>
	<script type="text/javascript" src="jquery.ui.datepicker-pl.js"></script>
	<script>
    var search = location.search.substring(1);
    search = search?JSON.parse('{"' + search.replace(/&/g, '","').replace(/=/g,'":"') + '"}', function(key, value) { return key===""?value:decodeURIComponent(value) }):{};

		if (typeof console === "undefined")
			console = {log:function(){}};
		else if (typeof console.log === "undefined")
			console.log = function(){};
		
		var dzialy = ['TR','RTR','TP'];
		for (d in dzialy){
			$('#dzial').append('<option value="'+dzialy[d]+'">'+dzialy[d]+'</option>');
		}
    if (search.dzial) $('#dzial').val(search.dzial);
		$('#dzial').change(load_users);

		function usun_znaki(str){
			return str.replace(/ /gi,'_').replace(/\//gi,'_').replace(/\(/gi,'_').replace(/\)/gi,'_').replace(/;/gi,'_').replace(/,/gi,'_').replace(/"/gi,'_').replace(/'/gi,'_').replace(/\./gi,'_');
//						return str.split(' ').join('_');
		}
		
		function load_users(){
			search.dzial = $('#dzial').val();
			get_data();
		}
		
		$.ajaxSetup({
			beforeSend:function(){
				// show gif here, eg:
				$("#loading").show();
			},
			complete:function(){
				// hide gif here, eg:
				$("#loading").hide();
			}
		});

    function lastOfArray(arr) {
      return arr[arr.length - 1];
    }
    
		function get_data(){
      $.ajax({
        url: 'baza.php?stat=1&proj=1&callback=?',
        dataType: 'json',
        type: 'POST',
        data: {_od:$("#data_od").datepicker('getDate').getTime(),_do:$("#data_do").datepicker('getDate').getTime()},
        timeout: 2000
      }).success(function(obj){
        init(obj);
      }).fail( function() {
        alert('Błąd pobrania danych');
        console.log('Błąd pobrania danych');
      });
		}
      
      function init(obj) {
        console.log(obj);
        var dzialy = {};
        var dla = {};
        var zadania = {};
        for (var si in obj.stat) {
          if (obj.stat.hasOwnProperty(si)) {
            var s = obj.stat[si];
            if(search.dzial && s.dzial.search(search.dzial) == -1) {
              //console.log(s.dzial, search.dzial);
              continue;
            }
            if (s.id == 9) s.dzial = 'RTR';
            if (s.id == 51) s.dzial = 'TR-1';
            if (!dzialy[s.dzial]) {dzialy[s.dzial] = {PNU:0, Urlop:0, L4:0};}
            var z = obj.o_zadania[s.zad];
            // console.log(s);
            if (z.id == 504) {
              // dla['Urlop'] = 1;
              dzialy[s.dzial]['Urlop'] += s.ile;
            } else if (z.id == 507) {
              // dla['L4'] = 1;
              dzialy[s.dzial]['L4'] += s.ile;
            } else if (z.id == 1955) {  //przepustka prywatna
              // dla['L4'] = 1;
              // dzialy[s.dzial]['L4'] += s.ile;
            } else {
              if (z.parents) {
                var p = obj.o_projekty[lastOfArray(z.parents)];
                dla[p.text] = 1;
                // console.log(s.ile, p.text, s.dzial);
                if (!dzialy[s.dzial][p.text]) {dzialy[s.dzial][p.text] = 0;}
                dzialy[s.dzial][p.text] += s.ile;
              } else {
                // console.log(s.ile, 'PNU '+z.json.pnu, s.dzial);
                dla.PNU = 1;
                dzialy[s.dzial].PNU += s.ile;
              }
              // console.log(z);
              zadania[s.zad] = zadania[s.zad] || {id:s.zad, suma:0, nazwa:z.nazwa, osoby:{}};
              zadania[s.zad].suma += s.ile;
              zadania[s.zad].osoby[s.id] = s.nazwa;
            }
          }
        }
        dla = Object.keys(dla).map(function (key) {return key});
        dla.sort();
        var dzialy_list = Object.keys(dzialy).map(function (key) {return key});
        dzialy_list.sort();
        $('#prace_dla').empty();
        $("#stat > tr:nth-child(1) > th:nth-child(2)").attr("colspan",dla.length);
        for (var i in dla) {
          $('#prace_dla').append('<th>'+dla[i]+'</th>');
        }
				$("#stat tr:gt(1)").remove();
        var col_sum = [];
        var j = 0;
        for (var i in dzialy_list) {
          var row_sum = 0;
          var row = '<tr>';
          row += '<th>' + dzialy_list[i] + '</th>';
          for (j in dla) {
            if (!col_sum[j]) col_sum[j] = 0;
            col_sum[j] += (dzialy[dzialy_list[i]][dla[j]] || 0);
            row_sum += dzialy[dzialy_list[i]][dla[j]] || 0;
            row += '<td>' + (dzialy[dzialy_list[i]][dla[j]] || '') + '</td>';
          }
          if (!col_sum['U']) col_sum['U'] = 0;
          col_sum['U'] += (dzialy[dzialy_list[i]]['Urlop'] || 0);
          row += '<td>' + (dzialy[dzialy_list[i]]['Urlop'] || '') + '</td>';
          row_sum += dzialy[dzialy_list[i]]['Urlop'] || 0;
          if (!col_sum['L']) col_sum['L'] = 0;
          col_sum['L'] += (dzialy[dzialy_list[i]]['L4'] || 0);
          row += '<td>' + (dzialy[dzialy_list[i]]['L4'] || '') + '</td>';
          row_sum += dzialy[dzialy_list[i]]['L4'] || 0;
          row += '<th>' + (row_sum || 0) + '</th>';
          row += '</th></tr>';
          $("#stat").append(row);
        }
        $("#stat td").css('text-align', 'center');
        var sum_row = '<tr><th>Suma</th>';
        var sum_all = 0;
        var pie_vals = [];
        for (var i in col_sum) {
          if (i >= 0) { pie_vals[i] = col_sum[i]; }
          sum_all += col_sum[i];
          sum_row += '<th>'+col_sum[i]+'</th>';
        }
        // console.log(pie_vals, pie_vals.join('_'), dla.join('_'));
        sum_row += '<th>'+sum_all+'</th></tr>';
        $("#stat").append(sum_row);
				
        $('#pie_pr').attr('src','../scripts/pie2.php?vals='+pie_vals.join('_')+'&kolory=0F0_FF0_F00_00F_F0F_0FF&legenda='+dla.join('_'));
        $('#pie_lu').attr('src','../scripts/pie2.php?vals='+(sum_all-(col_sum['U']+col_sum['L']))+'_'+col_sum['U']+'_'+col_sum['L']+'&kolory=0F0_FF0_F00_F0F&legenda=Praca_Urlop_L4');
        $('#pie').show();

				$("#proj tr:gt(0)").remove();
        zadania = Object.keys(zadania).map(function (key) {return zadania[key]});
        zadania.sort(function(a,b) { if (a.suma < b.suma ) return 1; if (a.suma > b.suma ) return -1; return 0;} );
        console.log(zadania[0]);
        var rows = '';
        for (var i in zadania) {
          var z = zadania[i];
          var osoby = Object.keys(z.osoby).map(function (key) {return z.osoby[key]});;
          osoby.sort();
          // console.log(osoby);
          rows += '<tr id="zad_'+z.id+'"><th>'+z.nazwa+'</th><td>'+osoby.join('</br>')+'</td><td>'+z.suma+'</td></tr>';
        }
        $("#proj").append(rows);


        return;
			
				$("#stat_osob tr:gt(0)").remove();
				karta_stat = obj["st_osob"];
				if (karta_stat){
					for (var s in karta_stat){
						var k = karta_stat[s]
						// console.log(k.termin);
						// console.log(k.termin?(new Date(k.termin/1)).toISOString().substring(0,10):'');
						$('#stat_osob').append('<tr style="'+(k.termin&&k.termin<k.kiedy?'color:red':'')+'"><td>'+k.zadanie+'</td><td>'+k.zlecenie+'</td><td>'+(new Date(k.kiedy/1)).toISOString().substring(0,10)+'</td><td>'+k.kto+'</td><td>'+k.opis+'</td><td>'+Math.round(100*k.ile/60)/100+' h</td><td>'+(k.termin?(new Date(k.termin/1)).toISOString().substring(0,10):'')+'</td></tr>')
						// var ile = Math.round(k.ile/6)/10;
						// $('#pv_'+usun_znaki(k.zlecenie)+"_"+k.dla_id).text(ile+" ("+Math.round(100*k.ile/suma_proj[k.zlecenie])+"%)");
					}
				}
			}
		
		$(function() {
			$('#pie').hide();
			var d = new Date();
			var month = search.month;
//			console.log(month);
			if (month){
				$("#data_od").datepicker().datepicker('setDate', new Date(d.getFullYear(),month-1, 1)).change(get_data);
				$("#data_do").datepicker().datepicker('setDate', new Date(d.getFullYear(),month, 0)).change(get_data);
			} else {
				$("#data_od").datepicker().datepicker('setDate', new Date(d.getFullYear(),d.getMonth(), 1)).change(get_data);
				$("#data_do").datepicker().datepicker('setDate', new Date(d.getFullYear(),d.getMonth()+1, 0)).change(get_data);
			}
//			console.log(window.location.origin);
			get_data();
		});
	</script>
</html>