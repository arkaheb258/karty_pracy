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
function test_req( $req , $default = "nulll"){
  if (isset($_REQUEST[$req]))
    return trim($_REQUEST[$req]);
  if (!$default || $default != "nulll") {
    return $default;
  };
  echo 'brak parametru \"' .$req .'\"';
  exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl" lang="pl">
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7; IE=EmulateIE9" /> 
		<link rel="stylesheet" type="text/css" href="kopex.css" />
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
	<body style="background-image:url(images/logo_km100.png);background-repeat: no-repeat; ">
		<div id="logged_as" style="float:right;font-size: 0.8em;margin-right: 1em;"></div><br/>
		
			<table style="margin:auto; width: 75%; max-width: 800px;">
				<colgroup>
					<col width="250">
					<col width="75%">
				</colgroup>
				<tbody>
					<tr><th>Nazwisko i Imię:</th><td><input id="kto_u" name="kto_u" /></td></tr>
					<tr class="l4"><th>Od</th><td>
						<input type="text" id="data_od" class="datetimepicker od " name="od" /><br/>
					</td></tr>
					<tr class="l4"><th>Do</th><td>
						<input type="text" id="data_do" class="datetimepicker do " name="do" /><br/>
					</td></tr>
					<tr class="n_l4">
						<th>Dzień</th>
						<td><input type="text" id="data" class="datetimepicker od " name="data" /><br/></td>
					</tr>
					<tr>
						<th>Czynność:</th>
						<td><select id="zad" name="zad" style="width:100%;"><option value="null"></option></select></td>
					</tr>
					<tr>
						<th>Opis czynności:</th>
						<td><textarea type="text" id="zad_opis" name="zad_opis" rows="5" disabled style="width:100%;"></textarea></td>
					</tr>
					<tr class="n_l4">
						<th><label for="czas">Czas pracy w godzinach:</label></th>
						<td><input id="czas" name="czas" /></td>
					</tr>
					<tr><th>Kategoria prac:</th><td id="kategorie"></td></tr>
					<tr><td colspan="3">Opis:<br/><textarea id="opis" rows="6" name="opis" style="width:100%;"></textarea></td></tr>
					<tr><td colspan="3"></td></tr>
					<tr>
						<td colspan="1"></td>
						<td colspan="2" style="text-align:right;">
							<div id="copy">Skopiuj</div>
							<div id="gotowe">Dodaj</div>
							<div id="del">Skasuj</div>
							<div id="close">Zamknij</div>
						</td>
					</tr>
				</tbody>
			</table>
	</body>
	<script type="text/javascript" src="baza_obj.php?user_id=<?php echo test_req( "user_id",$_SESSION["myuser"]["id"]); ?>"></script>
	<script type="text/javascript" src="baza_karta.php?user_id=<?php echo test_req( "user_id",$_SESSION["myuser"]["id"]); ?>"></script>
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="jquery-ui.min.js"></script>
	<script type="text/javascript" src="jquery.ui.datepicker-pl.js"></script>
	<script type="text/javascript" src="jquery-ui-timepicker-addon.js"></script>
	<script type="text/javascript" src="globalize.js"></script>
	<script type="text/javascript" src="globalize.culture.de-DE.js"></script>
	<script type="text/javascript" src="pnu.js"></script>

	<script type="text/javascript" src="date.js"></script>
	<script>
		_user_id = <?php echo $_SESSION["myuser"]["id"];?>;
		_user_name = '<?php echo $_SESSION["myuser"]["nazwa"];?>';
		_prac_id = '<?php if (isset($_REQUEST["id"])) echo $_REQUEST["id"]; else echo 'null';?>';
		_copy_id = '<?php if (isset($_REQUEST["copy_id"])) echo $_REQUEST["copy_id"]; else echo 'null';?>';
		_l4 = <?php if (isset($_REQUEST["l4"])) echo "true"; else echo 'null';?>;
		var sum_user_id = <?php if (isset($_REQUEST["user_id"])) echo $_REQUEST["user_id"]; else echo 'null';?>;

    $('#logged_as').text('Zalogowano jako: '+_user_name);
    
		var days_back = 30;
			
		if (typeof console === "undefined")
			console = {log:function(){}};
		else if (typeof console.log === "undefined")
			console.log = function(){};
			
		var today = new Date();
		
		var opis_last = [];
		var opis_last_arch = [];
		
    $("#copy").after('<div id="urlop" style="display:none;">Karta urlopowa</div>')

    if (!_l4){
      $(".l4").hide();
    } else {
      $(".n_l4").hide();
    }
  
    Globalize.culture("de-DE");
    $.widget( "ui.timespinner", $.ui.spinner, {
      options: {
        step: 15 * 60 * 1000,
        min: +Globalize.parseDate( "00:15"),
        max: +Globalize.parseDate( "12:00"),
        page: 4
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
    
    if (Math.abs((new Date()).getTime()/1000 - serv_epoch) > 24*60*60)
      alert("Proszę ustawić prawidłową datę.");

    $("#czas").timespinner().timespinner( "value", "01:00" );

    $('#gotowe').button().click(function(){send(false);});
    $('#urlop').button().click(function(){send(false,true);});
    $('#close').button().click(function(){window.close();});
    $('#del').button().show().click(function(){
      if (confirm('Czy napewno chcesz skasować kartę ?')){
        send(true);
      }
    });

    for (var zi in o_zadania){
      var z = o_zadania[zi];
      var opis = z.nazwa;
      for (var pi in z.parents) {
        opis = o_projekty[z.parents[pi]].nazwa + ' -> ' + opis;
      }
      if (z.json && z.json.rbh > 0)
        opis += ' (limit = ' + z.json.rbh +' rbh)';
      var _style = '';
      // oznaczenie zadań z przekroczonym terminem
      if (z.termin && (z.termin < ((new Date()).getTime() - 24*60*60*1000))){
        _style = ' style="color:brown;"';
      }
      $('#zad').append('<option value=\"'+z.id+'\" title="'+z.opis+'"'+_style+'>'+opis+'</option>');
if (z.par_id == 994) { console.log(z, opis); }
    }
    
    function zad_change(){
      if ($('#zad').val() != 'null'){
        var _zad = zadania[$('#zad').val()];
        $('#zad_opis').val(_zad.opis);
        console.log(_zad);
        if (_zad.id == 504) { 
          $('#urlop').show(); 
        } else {
          $('#urlop').hide();
        }
      } else {
        $('#zad_opis').val('');
      }
    };
    $('#zad').change(zad_change);
    
    $('input').change(function() { clear_err(); });
    $('select').click(function() { clear_err(); });
    $('#czas').parent().click(function() { clear_err(); });
    $('#data').click(function() { clear_err(); });
    $('textarea').keypress(function() { clear_err(); });
    
    $("#data_od").datepicker({minDate: -(days_back+30), maxDate: (days_back+30)}).datepicker('setDate', new Date()).click(function() { clear_err(); });
    $("#data_do").datepicker({minDate: -(days_back+30), maxDate: (days_back+30)}).datepicker('setDate', new Date()).click(function() { clear_err(); });

    if (_copy_id != null && _copy_id != 'null'){
      var obj = find_id(_copy_id, karty);
//				console.log(obj);
      if (obj){
        $('#kto_u').val('<?php echo $_SESSION["myuser"]["nazwa"];?>').attr('disabled', 'disabled');
        if (today.getDate() < 10) {
          $("#data").datepicker({ minDate: -days_back,maxDate: 0}).datepicker('setDate', new Date());
        } else {
          console.log('-1M');
          $("#data").datepicker({ minDate: '-1M', maxDate: 0}).datepicker('setDate', new Date());
        }
        // '+1M +10D'
        $('#czas').timespinner( "value",(obj.ile-(obj.ile%60))/60+":"+(obj.ile%60));
        $('#opis').val(obj.opis_p);
        $('#zad').val(obj.zad);
        zad_change();
        <?php if (isset($_REQUEST["day"])) echo 'var xday = new Date(); xday.setTime('.$_REQUEST["day"].'); $("#data").datepicker("setDate", xday);';?>
        <?php if (isset($_REQUEST["add"])) echo '$("#gotowe").click();';?>
      }				
    }
    
    if (_prac_id != null && _prac_id != 'null'){
      $('#copy').button().click(function(){
        window.location.href = window.location.href.replace("?id=","?copy_id=").replace("&id=","&copy_id=");
      });
      var obj = find_id(_prac_id, karty);
      console.log(obj);
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
          $("#data").datepicker({ minDate: -days_back, maxDate: 0});
        }
        $('#kto_u').val(obj.kart_user).attr('disabled', 'disabled');
        if (temp_date.getHours() == 23)
          temp_date.setHours(temp_date.getHours()+1);
        
        $("#data").datepicker('setDate',temp_date);
        $('#czas').timespinner( "value",(obj.ile-(obj.ile%60))/60+":"+(obj.ile%60));
        
        $('#dzial').val(obj.id_dzial);
        $('#kat_'+obj.id_dzial).show();
        $('input[name=prace][value='+obj.kat_id+']').attr('checked','checked');
        if ($('input[name=prace][value='+obj.kat_id+']').parent().hasClass('sub'))
          $('input[name=prace][value='+obj.kat_id+']').parent().show();
        $('#opis').val(obj.opis_p);
        $('#zlec').val(obj.zlec);
        
        $('#zad').val(obj.zad);
        zad_change();
        
        if (obj.kat_id > 430 && obj.kat_id < 450){
          if (obj.zlec.indexOf("PNU Projekt nr ") == 0){
            // console.log(obj.zlec.substring(15));
            if (!obj.zad) {
              var t_pnu = obj.zlec.substring(15).split('/');
              $("#pnu").val(t_pnu[0]);
              $("#pnu_zad").val(t_pnu[1]);
            }
            $("#pnu").parent().show();
            $("#zlec").parent().hide();
          }					
        }
        $('#gotowe > .ui-button-text').text( "Edytuj" );
      } else {
        alert("Brak karty o podanym ID");
        window.close();
      }
    } else {
      $('#copy').hide();
      $("#data").datepicker({ minDate: -days_back,maxDate: 0});
      $('#kto_u').val('<?php echo $_SESSION["myuser"]["nazwa"];?>').attr('disabled', 'disabled');
      $("#data").datepicker('setDate', new Date());
      <?php if (isset($_REQUEST["day"])) echo 'var xday = new Date(); xday.setTime('.$_REQUEST["day"].'); $("#data").datepicker("setDate", xday);';?>
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
      $("#opis_last").prop('disabled', 'disabled');
      $("#pnu").prop('disabled', 'disabled');
    }
    
    if (sum_user_id != null && _user_id != 1 && sum_user_id != _user_id){
      $('#copy').hide();
      $('#gotowe').hide();
      $('#del').hide();
      disable_all();
    }

		function clear_err(){
			$('input').blur(function() {$(this).css("background-color",'');});
			$('select').blur(function() {$(this).css("background-color",'');});
			$('input').css("background-color",'');
			$('select').css("background-color",'');
			$('#kategorie').css("background-color",'');
			$('#opis').css("background-color",'');
		}

		function find_id(id, _karty){
			for (k in _karty)
				if (_karty[k].prac_id == id)
					return _karty[k];
			return null;
		}

		function min_to_h2(min){
			if (min <= 0)
				return "0h";
			var m = min%60;
			var h = (min-m)/60;
			var out = "";
			if (h>0){
				out += h+"h";
				if (m>0)
				out += " ";
			}
			if (m>0)
				out += m+"min.";
			return out;
		}
		
		function send(del, urlop){
// console.log(user_info);
			clear_err();
			var obj = {};
			obj.id = _prac_id;
			obj.kto = _user_id;
			if (sum_user_id && _user_id == 1)
				obj.kto = sum_user_id;
      obj.zlec = 'N/D';   
			obj.dzial = 4;
			if (!_l4){
				obj.kiedy = $("#data").datepicker('getDate').getTime();
				obj.ile = ($('#czas').timespinner( "value") - +Globalize.parseDate( "00:00"))/60000;
			} else {
				obj.od_kiedy = $("#data_od").datepicker('getDate').getTime();
				obj.do_kiedy = $("#data_do").datepicker('getDate').getTime();
			}
			obj.co = 600;
			obj.opis = $('#opis').val();

			if (del){
				obj.del = del;
			} else {
				var now = new Date();
				now = now.getTime()/1000;
				
				var temp = null;
				
				if (_l4){
					if (obj.od_kiedy > obj.do_kiedy){
						$('#data_od').css("background-color",'red');
						$('#data_do').css("background-color",'red');
						alert('Proszę podać prawidłowy przedział czasu');
						return;
					}
					if ((obj.kto == 43)
					|| (obj.kto == 60)){
						obj.ile = 420;
					} else
						obj.ile = 480;
					obj.zlec = "";
					var out="";
					console.log(obj);
					if (!urlop)
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
	
						if(!swieta[temp_date.getMonth()+1] || !swieta[temp_date.getMonth()+1][temp_date.getDate()] || obj.od_kiedy == obj.do_kiedy)
						if((temp_date.getDay() > 0 && temp_date.getDay() < 6) || obj.od_kiedy == obj.do_kiedy){
							if (out != "")
								out+=",";
							out+=(d/1000);
						}
					}
					obj.dni = out;
				} else {
					obj.zad = $('#zad').val();
          if (!(obj.zad > 0)){
            $('#zad').css("background-color",'red');
            alert('Proszę wybrać zadanie');
            return;
          }

					if (obj.kiedy/1000 < now - (days_back+1)*86400 || obj.kiedy/1000 > now){
						$("#data").css("background-color",'red');
						alert("Nieprawidłowa data.");
						return;
					}

					if (obj.ile > 720 || obj.ile<=0){
						$('#czas').css("background-color",'red');
						alert('Proszę podać prawidłową ilość godzin');
						return;
					}
				}

				temp = {data:obj.kiedy,kat_id:obj.co,zlec:obj.zlec};
				temp2 = obj;
			}
					
			if (urlop)
				window.open("urlop_n.php?opis="+temp2.opis+"&od="+temp2.od_kiedy/1000+"&do="+temp2.do_kiedy/1000+"&id="+sum_user_id,"_self");
			else
				$.ajax({
					url: 'karta_sql.php?callback=?',
					dataType: 'json',
					type: 'POST',
					data: obj,
					timeout: 2000
				}).success(function(obj){
					if (obj[0]=="OK"){
						if (window.opener)
							window.opener.location.reload();
						if (obj[2] == "DELETE"){
							alert(obj[3]);
							window.close();
						}
            if (<?php if (isset($_REQUEST["add"])) echo "true"; else echo "false";?>) {
							open(location, '_self').close();
						} else if (confirm(obj[3]+'\n\rCzy chcesz zamknąć kartę ?'))
							window.close();
					} else
						alert('Błąd skryptu.');
				}).fail( function() {
					alert('Błąd serwera kart pracy.');
				});
		};
	</script>
</html>