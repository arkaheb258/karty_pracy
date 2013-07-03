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
  <title>Statystyki miesiąca</title>
  </head>
	<body style="height:100%;">
		<div style="float:right;font-size: 0.8em;margin-right: 1em;">Zalogowano jako: <?php echo $_SESSION["myuser"]["nazwa"]; ?></div><br/>
		Od: <input type="text" id="data_od" class="datetimepicker od " style="width:15em;" name="od" /><br/>
		Do: <input type="text" id="data_do" class="datetimepicker do " style="width:15em;" name="od" /><br/>
		<div>Podział według działu:</br><table cellpadding="0" cellspacing="0" border="1" class="display" style="width:90%"><tbody id="stat"><tr><th rowspan="2">Dział</th><th colspan="5">Praca wykonana dla działu</th><th rowspan="2">Suma</th></tr><tr><th>DH</th><th>DUG</th><th>DPP</th><th>DRiW</th><th>Inne</th></tr></tbody></table></div>
	</body>
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="jquery-ui.min.js"></script>
	<script type="text/javascript" src="/lib/jquery.ui.datepicker-pl.js"></script>

	<script type="text/javascript" src="/scripts/karta_stat.php"></script>
	<script>
		_user_id = <?php echo $_SESSION["myuser"]["id"];?>;
		_prac_id = <?php if (isset($_REQUEST["id"])) echo $_REQUEST["id"]; else echo 'null';?>;
		_kat_id = <?php if (isset($_REQUEST["id_k"])) echo $_REQUEST["id_k"]; else echo 'null';?>;
		_dzia_id = <?php if (isset($_REQUEST["id_d"])) echo $_REQUEST["id_d"]; else echo 'null';?>;

		if (typeof console === "undefined")
			console = {log:function(){}};
		else if (typeof console.log === "undefined")
			console.log = function(){};
		
		function get_data(){
			$.ajax({
				url: window.location.origin+'/scripts/karta_stat.php?callback=?',
				dataType: 'json',
				type: 'GET',
				data: {_od:$("#data_od").datepicker('getDate').getTime()/1000,_do:$("#data_do").datepicker('getDate').getTime()/1000},
				timeout: 1000
			}).success(function(obj){
				karta_stat = obj;
			
				$("#stat tr:gt(1)").remove();
				if (karta_stat){
					var suma_dzial= {};
					var suma_dla = {"1":0,"2":0,"3":0,"4":0,"5":0};
					var suma_dla_tr = {"1":0,"2":0,"3":0,"4":0,"5":0};
					var suma_dla_rtr = {"1":0,"2":0,"3":0,"4":0,"5":0};
					var suma_sum= 0;
					var suma_sum_tr= 0;
					var suma_sum_rtr= 0;
					var dzialy = [];
					
					for (var s in karta_stat){
						var k = karta_stat[s]
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
		//			console.log(dzialy)
	//				console.log(suma_dzial);
	//				console.log(suma_dla);
	//				console.log(suma_dla_tr);
	//				console.log(suma_dla_rtr);
					for (var d in dzialy){
						var dz = dzialy[d]
						$('#stat').append('<tr id="r_'+dz+'"><th>'+dz+'</th><td id="v_'+dz+'_1"></td><td id="v_'+dz+'_2"></td><td id="v_'+dz+'_3"></td><td id="v_'+dz+'_4"></td><td id="v_'+dz+'_5"></td><th id="s_'+dz+'"></th></tr>')
						if (dz.indexOf("RTR") == 0)
							$("#r_"+dz).addClass("RTR");
						if (dz.indexOf("TR") == 0)
							$("#r_"+dz).addClass("TR");
					}
					var dz = "RTR_SUM";
					$('#r_RTR-1').before('<tr id="r_'+dz+'"><th>'+dz+'</th><td id="v_'+dz+'_1"></td><td id="v_'+dz+'_2"></td><td id="v_'+dz+'_3"></td><td id="v_'+dz+'_4"></td><td id="v_'+dz+'_5"></td><th id="s_'+dz+'"></th></tr>')
					for (var d in suma_dla_rtr)
						$('#v_RTR_SUM_'+d).text(Math.round(suma_dla_rtr[d]/6)/10 + " ("+Math.round(100*suma_dla_rtr[d]/suma_sum_rtr)+"%)");
					
					$('')
						
					var dz = "TR_SUM";
					$('#r_TR-1').before('<tr id="r_'+dz+'"><th>'+dz+'</th><td id="v_'+dz+'_1"></td><td id="v_'+dz+'_2"></td><td id="v_'+dz+'_3"></td><td id="v_'+dz+'_4"></td><td id="v_'+dz+'_5"></td><th id="s_'+dz+'"></th></tr>')
					for (var d in suma_dla_tr)
						$('#v_TR_SUM_'+d).text(Math.round(suma_dla_tr[d]/6)/10 + " ("+Math.round(100*suma_dla_tr[d]/suma_sum_tr)+"%)");
					if (!suma_sum_tr)
						$('#r_TR_SUM').hide();
					if (!suma_sum_rtr)
						$('#r_RTR_SUM').hide();
					$('#stat').append('<tr><th>Suma</th><th id="s__1"></th><th id="s__2"></th><th id="s__3"></th><th id="s__4"></th><th id="s__5"></th><th id="s_sum"></th></tr>')
					for (var s in karta_stat){
						var k = karta_stat[s]
						var ile = Math.round(k.ile/6)/10;
						$('#v_'+k.dzial+"_"+k.dla_id).text(ile+" ("+Math.round(100*k.ile/suma_dzial[k.dzial])+"%)");
	//					console.log(ile);
	//					console.log(k);
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
					$(".RTR").hide().css("cursor","pointer").click(function(){
						$(".RTR").hide();
						$('#r_RTR_SUM').show();
					});
					$('#r_RTR_SUM').css("cursor","pointer").click(function(){
						$(".RTR").show();
						$('#r_RTR_SUM').hide();
					});
					$(".TR").hide().css("cursor","pointer").click(function(){
						$(".TR").hide();
						$('#r_TR_SUM').show();
					});
					$('#r_TR_SUM').css("cursor","pointer").click(function(){
						$(".TR").show();
						$('#r_TR_SUM').hide();
					});
					$("td").css("text-align","center");
				} else 
					$('#stat').parent().hide();
			});
		}
		
		$(function() {
			var d = new Date();
			$("#data_od").datepicker().datepicker('setDate', new Date(d.getFullYear(),d.getMonth(), 1)).change(get_data);
			$("#data_do").datepicker().datepicker('setDate', new Date(d.getFullYear(),d.getMonth()+1, 0)).change(get_data);
//			console.log(window.location.origin);
			get_data();
		});
	</script>
</html>