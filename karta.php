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
					<tr>
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
					<tr>
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
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="jquery-ui.min.js"></script>
	<script type="text/javascript" src="jquery-ui-timepicker-addon.js"></script>
	<script type="text/javascript" src="globalize.js"></script>
	<script type="text/javascript" src="globalize.culture.de-DE.js"></script>
	<script type="text/javascript" src="pnu.js"></script>
	<script type="text/javascript" src="date.js"></script>

	<script>
    var search = location.search.substring(1);
    search = search?JSON.parse('{"' + search.replace(/&/g, '","').replace(/=/g,'":"') + '"}', function(key, value) { return key===""?value:decodeURIComponent(value) }):{};
    console.log('start', search);
    var _prac_id = search.id;
    var _copy_id = search.copy_id;
    var sum_user_id = search.user_id;
    
		_user_id = <?php echo $_SESSION["myuser"]["id"];?>;
		_user_name = '<?php echo $_SESSION["myuser"]["nazwa"];?>';
    
    $('#logged_as').text('Zalogowano jako: '+_user_name);
    
		if (typeof console === "undefined")
			console = {log:function(){}};
		else if (typeof console.log === "undefined")
			console.log = function(){};

    $.datepicker.regional['pl'] = {
      closeText: 'Zamknij',
      prevText: '&#x3c;Poprzedni',
      nextText: 'Następny&#x3e;',
      currentText: 'Dziś',
      monthNames: ['Styczeń','Luty','Marzec','Kwiecień','Maj','Czerwiec',
      'Lipiec','Sierpień','Wrzesień','Październik','Listopad','Grudzień'],
      monthNamesShort: ['Sty','Lu','Mar','Kw','Maj','Cze',
      'Lip','Sie','Wrz','Pa','Lis','Gru'],
      dayNames: ['Niedziela','Poniedzialek','Wtorek','Środa','Czwartek','Piątek','Sobota'],
      dayNamesShort: ['Nie','Pn','Wt','Śr','Czw','Pt','So'],
      dayNamesMin: ['N','Pn','Wt','Śr','Cz','Pt','So'],
      weekHeader: 'Tydz',
      dateFormat: 'yy-mm-dd',
      firstDay: 1,
      isRTL: false,
      showMonthAfterYear: false,
      yearSuffix: ''};
    $.datepicker.setDefaults($.datepicker.regional['pl']);
    
		var today = new Date();
		
		var opis_last = [];
		var opis_last_arch = [];
		
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
    
    if (typeof serv_epoch != 'undefined' && Math.abs((new Date()).getTime()/1000 - serv_epoch) > 24*60*60)
      alert("Proszę ustawić prawidłową datę.");

    $("#czas").timespinner().timespinner( "value", "01:00" );

    $('#gotowe').button().click(function(){send(false);});
    $('#close').button().click(function(){window.close();});
    $('#del').button().show().click(function(){
      if (confirm('Czy napewno chcesz skasować kartę ?')){
        send(true);
      }
    });

    var htmlToAppend = '';
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
      // $('#zad').append('<option value=\"'+z.id+'\" title="'+z.opis+'"'+_style+'>'+opis+'</option>');
      htmlToAppend += '<option value=\"'+z.id+'\" title="'+z.opis+'"'+_style+'>'+opis+'</option>';
      
//TODO: Prace inne jako bombki
if (z.par_id == 994) { console.log(z, opis); }
    }
    $('#zad').append(htmlToAppend);
    
    function zad_change(){
      if ($('#zad').val() != 'null'){
        var _zad = o_zadania[$('#zad').val()];
        $('#zad_opis').val(_zad.opis);
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

    if (_copy_id != null && _copy_id != 'null'){
      var obj = find_id(_copy_id, function(obj){
				console.log(obj);
        $('#kto_u').val(_user_name).attr('disabled', 'disabled');
        if (today.getDate() < 10) {
          $("#data").datepicker({ minDate: -30,maxDate: 0}).datepicker('setDate', new Date());
        } else {
          // console.log('-1M');
          $("#data").datepicker({ minDate: '-1M', maxDate: 0}).datepicker('setDate', new Date());
        }
        // '+1M +10D'
        $('#czas').timespinner( "value",(obj.czas-(obj.czas%60))/60+":"+(obj.czas%60));
        $('#opis').val(obj.opis);
        $('#zad').val(obj.zadanie);
        zad_change();
      });
    }
    
    if (_prac_id != null && _prac_id != 'null'){
      $('#copy').button().click(function(){
        window.location.href = window.location.href.replace("?id=","?copy_id=").replace("&id=","&copy_id=");
      });
      var obj = find_id(_prac_id, function(obj){
        // console.log(obj);
        var temp_date = new Date();
        temp_date.setTime(obj.data);
        //var temp_today = new Date();
        //console.log(temp_today.getDate() < 10, temp_date.getDate(), new Date(), temp_date, new Date() - temp_date, (new Date() - temp_date > 86400000*(30+1)));
        if (new Date() - temp_date > 86400000*(7+1)){
          $('#gotowe').hide();
          $('#del').hide();
          $("#data").datepicker();
          disable_all();
          $('#czas').timespinner('option', "max", +Globalize.parseDate( "23:00"));
        } else {
          $("#data").datepicker({ minDate: -(temp_date.getDate()+5), maxDate: 0});
        }
        $('#kto_u').val(_user_name).attr('disabled', 'disabled');
        if (temp_date.getHours() == 23)
          temp_date.setHours(temp_date.getHours()+1);
        
        $("#data").datepicker('setDate',temp_date);
        $('#czas').timespinner( "value",(obj.czas-(obj.czas%60))/60+":"+(obj.czas%60));
        
        $('#opis').val(obj.opis);
        
        $('#zad').val(obj.zadanie);
        zad_change();
        
        $('#gotowe > .ui-button-text').text( "Edytuj" );
      });
    } else {
      var temp_date = new Date();
      // console.log({temp_date.getDate()});
      $('#copy').hide();
      $("#data").datepicker({ minDate: -(temp_date.getDate()+5),maxDate: 0});
      $('#kto_u').val(_user_name).attr('disabled', 'disabled');
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

		function find_id(id, callback){
			$.ajax({
				url: 'baza_karta_new.php?callback=?',
				dataType: 'json',
				type: 'POST',
				data: {id:id, user_id:sum_user_id},
				timeout: 2000
			}).success(function(obj){
				callback(obj);
				if (obj && obj.length==1){
          callback(obj[0]);
				} else {
					alert('Błąd skryptu.');
          // window.close();
        }
			}).fail( function() {
				alert('Błąd serwera.');
        window.close();
			});
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
		
		function send(del){
			clear_err();
			var obj = {};
			obj.id = _prac_id;
			obj.kto = _user_id;
			if (sum_user_id && _user_id == 1)
				obj.kto = sum_user_id;
      obj.zlec = 'N/D';   
			obj.dzial = 4;
      obj.kiedy = $("#data").datepicker('getDate').getTime();
      obj.ile = ($('#czas').timespinner( "value") - +Globalize.parseDate( "00:00"))/60000;
			obj.co = 600;
			obj.opis = $('#opis').val();

			if (del){
				obj.del = del;
			} else {
				var now = new Date();
				now = now.getTime()/1000;
				
				var temp = null;
				
        obj.zad = $('#zad').val();
        if (!(obj.zad > 0)){
          $('#zad').css("background-color",'red');
          alert('Proszę wybrać zadanie');
          return;
        }

        if (obj.kiedy/1000 < now - (30+1)*86400 || obj.kiedy/1000 > now){
          $("#data").css("background-color",'red');
          alert("Nieprawidłowa data.");
          return;
        }

        if (obj.ile > 720 || obj.ile<=0){
          $('#czas').css("background-color",'red');
          alert('Proszę podać prawidłową ilość godzin');
          return;
        }

				temp = {data:obj.kiedy,kat_id:obj.co,zlec:obj.zlec};
				temp2 = obj;
			}
					
      $.ajax({
        url: 'karta_sql.php?callback=?',
        dataType: 'json',
        type: 'POST',
        data: obj,
        timeout: 2000
      }).success(function(obj){
        if (obj[0]=="OK"){
          if (window.opener) { window.opener.location.reload(); }
          if (obj[2] == "DELETE"){
            alert(obj[3]);
            window.close();
          }
          if (confirm(obj[3]+'\n\rCzy chcesz zamknąć kartę ?')) { window.close(); }
        } else {
          alert('Błąd skryptu.');
        }
      }).fail( function() {
        alert('Błąd serwera kart pracy.');
      });
		};
	</script>
</html>