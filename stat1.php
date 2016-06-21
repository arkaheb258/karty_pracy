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
		<div style="float:right;font-size: 0.8em;margin-right: 1em;">Zalogowano jako: <?php echo $_SESSION["myuser"]["nazwa"]; ?></div><br/>
		Dział: <select id="dzial" name="dzial">
			<option value="">Wszystkie</option>
		</select><br/>
		Od: <input type="text" id="data_od" class="datetimepicker od " style="width:15em;" name="od" /><br/>
		Do: <input type="text" id="data_do" class="datetimepicker do " style="width:15em;" name="od" /><br/>
		<div>Podział według działu:</br><table cellpadding="0" cellspacing="0" border="1" class="display" style="width:90%"><tbody id="stat"><tr><th rowspan="2">Dział</th><th colspan="5">Praca wykonana dla działu</th><th rowspan="2">Urlop</th><th rowspan="2">L4</th><th rowspan="2">Suma</th></tr><tr><th>DH</th><th>DUG</th><th>DPP</th><th>DRiW</th><th>Inne</th></tr></tbody></table></div>
		<div id="pie" style="width:66%; margin: auto;"><img id="pie_pr" src="" alt="" /><img id="pie_lu" src="" alt="" /></div>
		<div>Podział według projektu:</br><table cellpadding="0" cellspacing="0" border="1" class="display" style="width:90%"><tbody id="proj"><tr><th rowspan="2">Zlecenie / Zamówienie</th><th colspan="5">Praca wykonana dla działu</th><th rowspan="2">Suma</th></tr><tr><th>DH</th><th>DUG</th><th>DPP</th><th>DRiW</th><th>Inne</th></tr></tbody></table></div>
		<div>Podział na osoby:</br><table cellpadding="0" cellspacing="0" border="1" class="display" style="width:90%">
			<colgroup>
				<col style="width: 150px;"></col>
				<col style="width: 100px;"></col>
				<col style="width: 100px;"></col>
				<col style="width: 100px;"></col>
				<col style="width: 50%;"></col>
				<col style="width: 100px;"></col>
				<col style="width: 100px;"></col>
			</colgroup>		
			<thead>
				<tr><th onclick="sort_osob(0)">Zadanie</th><th onclick="sort_osob(1)">Zlecenie</th><th onclick="sort_osob(2)">Data</th><th onclick="sort_osob(3)">Kto</th><th onclick="sort_osob(4)">Co</th><th onclick="sort_osob(5)">rbh</th><th onclick="sort_osob(6)">Termin</th></tr>
			</thead>
			<tbody id="stat_osob">
			</tbody>
		</table></div>
		<div id="braki"></div>
		<div id="nadgodziny"></div>
		<div id="braki_rtr"></div>
		<div id="nadgodziny_rtr"></div>
	</body>
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="jquery-ui.min.js"></script>
	<script type="text/javascript" src="jquery.ui.datepicker-pl.js"></script>
	<script>
		_user_id = <?php echo $_SESSION["myuser"]["id"];?>;
		_prac_id = <?php if (isset($_REQUEST["id"])) echo $_REQUEST["id"]; else echo 'null';?>;
		_kat_id = <?php if (isset($_REQUEST["id_k"])) echo $_REQUEST["id_k"]; else echo 'null';?>;
		_dzia_id = <?php if (isset($_REQUEST["id_d"])) echo $_REQUEST["id_d"]; else echo 'null';?>;
		_dzial = <?php if (isset($_REQUEST["dzial"])) echo "'".$_REQUEST["dzial"]."'"; else echo 'null';?>;
		var sum_user_id = <?php if (isset($_REQUEST["user_id"])) echo $_REQUEST["user_id"]; else echo 'null';?>;

		if (typeof console === "undefined")
			console = {log:function(){}};
		else if (typeof console.log === "undefined")
			console.log = function(){};
		
		var dzialy = ['TR','RTR','TP','TRIN'];
		for (d in dzialy){
			$('#dzial').append('<option value="'+dzialy[d]+'">'+dzialy[d]+'</option>');
		}
		$('#dzial').change(load_users);

		function usun_znaki(str){
			return str.replace(/ /gi,'_').replace(/\//gi,'_').replace(/\(/gi,'_').replace(/\)/gi,'_').replace(/;/gi,'_').replace(/,/gi,'_').replace(/"/gi,'_').replace(/'/gi,'_').replace(/\./gi,'_');
//						return str.split(' ').join('_');
		}
		
		function load_users(){
			_dzial = $('#dzial').val()+"%";
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

		function sort_osob(col) {
			var $tbody = $('#stat_osob');
			$tbody.find('tr').sort(function(a,b){ 
				var tda = $(a).find('td:eq('+col+')').text(); // can replace 1 with the column you want to sort on
				var tdb = $(b).find('td:eq('+col+')').text(); // this will sort on the second column
						// if a < b return 1
				return tda > tdb ? 1 
					   // else if a > b return -1
					   : tda < tdb ? -1 
					   // else they are equal - return 0    
					   : 0;           
			}).appendTo($tbody);
		}
		
		function get_data(){
			var temp_data = {_od:$("#data_od").datepicker('getDate').getTime()/1000,_do:$("#data_do").datepicker('getDate').getTime()/1000,user_id:sum_user_id};
			if (_dzial)
				temp_data.dzial = _dzial;
			$.ajax({
//				url: window.location.origin+'/scripts/karta_stat.php?callback=?',
				url: 'karta_stat21.php?callback=?',
				dataType: 'json',
				type: 'GET',
				data: temp_data,
				timeout: 30000
			}).success(function(obj){
				karta_stat = obj["st_dzial"];
			
				$("#stat tr:gt(1)").remove();
				if (karta_stat){
					var suma_dzial= {};
					var suma_dla =		{"1":0,"2":0,"3":0,"4":0,"5":0,"5U":0,"5L":0,"5A":0};
					var suma_dla_tr =	{"1":0,"2":0,"3":0,"4":0,"5":0,"5U":0,"5L":0,"5A":0};
					var suma_dla_rtr =	{"1":0,"2":0,"3":0,"4":0,"5":0,"5U":0,"5L":0,"5A":0};
					var suma_sum= 0;
					var suma_sum_tr= 0;
					var suma_sum_rtr= 0;
					var dzialy = [];
					var projekty = [];
					
					//var 
					suma_ifs = 0;
					suma_awaria = 0;
					
					for (var s in karta_stat){
						var k = karta_stat[s]
						if (k.dla_id.indexOf("IFS") > 0){
//							console.log(k.dla_id + ": " + k.ile);
							suma_ifs += k.ile/1;
							k.dla_id = k.dla_id[0];
						}
						if (k.dzial.indexOf("RTR") == 0){
							suma_dla_rtr[k.dla_id] += k.ile/1;
							suma_sum_rtr += k.ile/1;
						}
						if (k.dzial.indexOf("TR") == 0){
							suma_dla_tr[k.dla_id] += k.ile/1;
							suma_sum_tr += k.ile/1;
						}
						suma_dla[k.dla_id] += k.ile/1;
						suma_sum += k.ile/1;
						if ($.inArray(k.dzial, dzialy) == -1){
							dzialy.push(k.dzial);
							suma_dzial[k.dzial] = k.ile/1;
						} else
							suma_dzial[k.dzial] += k.ile/1;
					}			
					// console.log(dzialy)
					// console.log(suma_dzial);
					// console.log(suma_dla);
					// console.log(suma_dla_tr);
					// console.log(suma_dla_rtr);
					$('#pie_pr').attr('src','../scripts/pie2.php?vals='+suma_dla[1]+'_'+suma_dla[2]+'_'+suma_dla[3]+'_'+suma_dla[4]+'_'+(suma_dla[5]+suma_dla['5L']+suma_dla['5U']+suma_dla['5A'])+'&kolory=0F0_FF0_F00_00F_F0F&legenda=DH_DUG_DPP_DRiW_Inne');
					$('#pie_lu').attr('src','../scripts/pie2.php?vals='+
					(suma_sum-(suma_dla['5U']+suma_dla['5L']+suma_dla['5A']))
					+'_'+suma_dla['5U']+'_'+suma_dla['5L']+'_'+suma_dla['5A']+
					'&kolory=0F0_FF0_F00_F0F&legenda=Praca_Urlop_L4_Awaria');
//					console.log(suma_dla);

					// dodanie awarii sprzetu di innych
					suma_dla[5] += suma_dla['5A'];
					suma_dla['5A'] = 0;
					suma_dla_tr[5] += suma_dla_tr['5A'];
					suma_dla_tr['5A'] = 0;
					suma_dla_rtr[5] += suma_dla_rtr['5A'];
					suma_dla_rtr['5A'] = 0;

					
					$('#pie').show();
					for (var d in dzialy){
						var dz = dzialy[d]
						$('#stat').append('<tr id="r_'+dz+'"><th>'+dz+'</th><td id="v_'+dz+'_1"></td><td id="v_'+dz+'_2"></td><td id="v_'+dz+'_3"></td><td id="v_'+dz+'_4"></td><td id="v_'+dz+'_5"></td><td id="v_'+dz+'_5U"></td><td id="v_'+dz+'_5L"></td><th id="s_'+dz+'"></th></tr>')
						if (dz.indexOf("RTR") == 0)
							$("#r_"+dz).addClass("RTR");
						if (dz.indexOf("TR") == 0)
							$("#r_"+dz).addClass("TR");
					}
					var dz = "RTR_SUM";
					$('#r_RTR-1').before('<tr id="r_'+dz+'"><th>'+dz+'</th><td id="v_'+dz+'_1"></td><td id="v_'+dz+'_2"></td><td id="v_'+dz+'_3"></td><td id="v_'+dz+'_4"></td><td id="v_'+dz+'_5"></td><td id="v_'+dz+'_5U"></td><td id="v_'+dz+'_5L"></td><th id="s_'+dz+'"></th></tr>')
					for (var d in suma_dla_rtr)
						$('#v_RTR_SUM_'+d).text(Math.round(suma_dla_rtr[d]/6)/10 + " ("+Math.round(100*suma_dla_rtr[d]/suma_sum_rtr)+"%)");
					
//					$('')
						
					var dz = "TR_SUM";
					$('#r_TR-1').before('<tr id="r_'+dz+'"><th>'+dz+'</th><td id="v_'+dz+'_1"></td><td id="v_'+dz+'_2"></td><td id="v_'+dz+'_3"></td><td id="v_'+dz+'_4"></td><td id="v_'+dz+'_5"></td><td id="v_'+dz+'_5U"></td><td id="v_'+dz+'_5L"><th id="s_'+dz+'"></th></tr>')
					for (var d in suma_dla_tr)
						$('#v_TR_SUM_'+d).text(Math.round(suma_dla_tr[d]/6)/10 + " ("+Math.round(100*suma_dla_tr[d]/suma_sum_tr)+"%)");
					if (!suma_sum_tr)
						$('#r_TR_SUM').hide();
					if (!suma_sum_rtr)
						$('#r_RTR_SUM').hide();
					$('#stat').append('<tr><th>Suma</th><th id="s__1"></th><th id="s__2"></th><th id="s__3"></th><th id="s__4"></th><th id="s__5"></th><th id="s__5U"></th><th id="s__5L"></th><th id="s_sum"></th></tr>')
					for (var s in karta_stat){
						var k = karta_stat[s]
						var ile = Math.round(k.ile/6)/10;
						$('#v_'+k.dzial+"_"+k.dla_id).text(ile+" ("+Math.round(100*k.ile/suma_dzial[k.dzial])+"%)");
					}			
					//suma prawa
					for (var d in suma_dzial)
						$('#s_'+d).text(Math.round(suma_dzial[d]/6)/10);// + " ("+Math.round(100*suma_dzial[d]/suma_sum)+"%)");
					//suma dolna
					for (var d in suma_dla)
						$('#s__'+d).text(Math.round(suma_dla[d]/6)/10 + " ("+Math.round(100*suma_dla[d]/suma_sum)+"%)");
					$('#s_sum').text(Math.round(suma_sum/6)/10);
					$('#s_TR_SUM').text(Math.round(suma_sum_tr/6)/10);
					$('#s_RTR_SUM').text(Math.round(suma_sum_rtr/6)/10);
					// $(".RTR").hide();
					// $(".RTR th").css("cursor","pointer").click(function(){
						// $(".RTR").hide();
						// $('#r_RTR_SUM').show();
					// });
					// $('#r_RTR_SUM th').css("cursor","pointer").click(function(){
						// $(".RTR").show();
						// $('#r_RTR_SUM').hide();
					// });
					$(".TR").hide();
					$(".TR th").css("cursor","pointer").click(function(){
						$(".TR").hide();
						$('#r_TR_SUM').show();
					});
					$('#r_TR_SUM th').css("cursor","pointer").click(function(){
						$(".TR").show();
						$('#r_TR_SUM').hide();
					});
					$("td").css("text-align","center");
				} else 
					$('#stat').parent().hide();

				karta_stat = obj["st_proj"];
			
				$("#proj tr:gt(1)").remove();
				if (karta_stat){
					var suma_proj= {};
					var projekty = [];
					
					for (var s in karta_stat){
						var k = karta_stat[s]
//						console.log(k);
						if ($.inArray(k.zlecenie, projekty) == -1){
							projekty.push(k.zlecenie);
							// var dz = usun_znaki(k.zlecenie);
							// $('#proj').append('<tr id="p_'+dz+'"><th>'+k.zlecenie+'</th><td id="pv_'+dz+'_1"></td><td id="pv_'+dz+'_2"></td><td id="pv_'+dz+'_3"></td><td id="pv_'+dz+'_4"></td><td id="pv_'+dz+'_5"></td><th id="ps_'+dz+'"></th></tr>');
							suma_proj[k.zlecenie] = k.ile/1;
						} else
							suma_proj[k.zlecenie] += k.ile/1;
					}
					
					for (var d in projekty){
						var dz = usun_znaki(projekty[d]);
						$('#proj').append('<tr id="p_'+dz+'"><th>'+projekty[d]+'</th><td id="pv_'+dz+'_1"></td><td id="pv_'+dz+'_2"></td><td id="pv_'+dz+'_3"></td><td id="pv_'+dz+'_4"></td><td id="pv_'+dz+'_5"></td><th id="ps_'+dz+'"></th></tr>')
					}
					$('#p_ > th:eq(0)').text("-- Nie określono --");
					
					for (var s in karta_stat){
						var k = karta_stat[s]
//						console.log(k);
						var ile = Math.round(k.ile/6)/10;
						$('#pv_'+usun_znaki(k.zlecenie)+"_"+k.dla_id).text(ile+" ("+Math.round(100*k.ile/suma_proj[k.zlecenie])+"%)");
					}
					for (var d in suma_proj)
						$('#ps_'+usun_znaki(d)).text(Math.round(suma_proj[d]/6)/10);// + " ("+Math.round(100*suma_dzial[d]/suma_sum)+"%)");
					var $table = $('#proj > tr:gt(1)');
					$table.sort(function(a, b){
						// console.log(a);
//						console.log($('th:last',a).text()/1);
						// console.log(b);
//						console.log($('th:last',b).text()/1+);
						// if (($('th:last',a).text()/1 <= $('th:last',b).text()/1) ? 1 : -1)
							// console.log($('th:last',a).text()/1 + " < " + $('th:last',b).text()/1);
						// else
							// console.log($('th:last',a).text()/1 + " > " + $('th:last',b).text()/1);
						return (($('th:last',a).text()/1 <= $('th:last',b).text()/1) ? 1 : -1);
					});
//					console.log($table);
					$.each($table, function(index, row){
						$('#proj').append(row);
					});				
					
					
				}
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
				
				$("#braki").empty();
				$("#braki_rtr").empty();
				$("#nadgodziny").empty();
				swieta = obj["swieta"];
				karta_stat = obj["braki"];
//console.log(karta_stat);
				if (karta_stat){
					for (var b in karta_stat){
						var d = new Date();
						d.setTime(karta_stat[b].day);
						if (!karta_stat[b].ile){
//							console.log(d);
//							console.log(karta_stat[b]);
							if(!swieta[d.getMonth()+1] || (swieta[d.getMonth()+1] && !swieta[d.getMonth()+1][d.getDate()])){
								// if (karta_stat[b].dzial[0] == 'R')
									// $("#braki_rtr").append(karta_stat[b].nazwa+": "+(d.getFullYear()+"/"+(d.getMonth()+1)+"/"+d.getDate())+"</br>");
								// else {
									// console.log(karta_stat[b].nazwa + karta_stat[b].user_id);
									// console.log(karta_stat[b]);
									// if ((karta_stat[b].nazwa == "Jacek Szymendera" && (d.getTime() <= 1418947200000))
									// || (karta_stat[b].nazwa == "Magiera Wojciech" && (d.getTime() <= 1391990400000))
									// ){
//										console.log(d);
										// console.log(d.getTime());
										// console.log(karta_stat[b].nazwa);
										//nowo przyjety
									// } else {
										$("#braki").append('<span class="">' + karta_stat[b].nazwa+": "+(d.getFullYear()+"/"+(d.getMonth()+1)+"/"+d.getDate())+"</span></br>");
//										console.log(karta_stat[b].nazwa);
									// }
								// }
							}
						}
						else if (karta_stat[b].ile > 480){
							// if (karta_stat[b].dzial[0] == 'R')
								// $("#nadgodziny_rtr").append(karta_stat[b].nazwa+": "+(d.getFullYear()+"/"+(d.getMonth()+1)+"/"+d.getDate())+"</br>");
							// else
								$("#nadgodziny").append(karta_stat[b].nazwa+": "+(d.getFullYear()+"/"+(d.getMonth()+1)+"/"+d.getDate())+"</br>");
						}
//						console.log(karta_stat[b]);
					}
//					console.log($("#braki").text());
					if ($("#braki").text() != "")
						$("#braki").prepend("<br/><b>Brakujące wpisy TR:</b><br/>");
					if ($("#nadgodziny").text() != "")
						$("#nadgodziny").prepend("<br/><b>Nadgodziny TR:</b><br/>");
					// if ($("#braki_rtr").text() != "")
						// $("#braki_rtr").prepend("<br/><b>Brakujące wpisy RTR:</b><br/>");
					// if ($("#nadgodziny_rtr").text() != "")
						// $("#nadgodziny_rtr").prepend("<br/><b>Nadgodziny RTR:</b><br/>");
				}

			});
		}
		
		$(function() {
			$('#pie').hide();
			var d = new Date();
			var month = <?php if (isset($_REQUEST["month"])) echo $_REQUEST["month"]; else echo 'null';?>;
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