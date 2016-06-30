<?php
	include 'header.php';

	if ( $_SESSION["myuser"]["kart_perm"]  < 1) {
		exit("Brak uprawnień");
	}
	
	require_once ('conf.php');
	$mysqli = new_polacz_z_baza();
	if (isset($_REQUEST["usun"]) || isset($_REQUEST["dodaj"])){
		if (isset($_REQUEST["callback"])){
			$callback = trim($_REQUEST['callback']);
			echo $callback .'(';
		}
		if (isset($_REQUEST["jsoncallback"])){
			$callback = trim($_REQUEST['jsoncallback']);
			echo $callback .'(';
		}
	}
	$query_log = '';
	if (isset($_REQUEST["usun"])){
		$id = $_REQUEST["usun"];
		$query = "UPDATE `kart_pr_zadania` SET `deleted` = 1 WHERE `id`=$id;";
		if ($mysqli->query($query))
			echo json_encode(array('OK',$id,'DELETE','Zadanie usunięte'));
		else
			echo $query;
	}
	if (isset($_REQUEST["dodaj"])){
		$kier_id = $_REQUEST["kier_id" ];
		$nazwa = $_REQUEST["nazwa" ];
		$opis = $_REQUEST["opis" ];
		$aktywny = $_REQUEST["aktywny"];
		$par_id = $_REQUEST["par_id"];
		$koment = $_REQUEST["koment"];
		$prac_wykon = $_REQUEST["prac_wykon"];
		$zlec = $_REQUEST["zlec" ];
		$rbh = $_REQUEST["rbh" ];
		$json = $_REQUEST["json"];
		$termin = $_REQUEST["termin"];
		
		$id = $_REQUEST["dodaj"];
		if ($_REQUEST["dodaj"] == 0){
			$query = "INSERT INTO `kart_pr_zadania`(`user_id`,`par_id`,`nazwa`, `opis`, `aktywny`, `prac_wykon`, `rbh`, `termin`, `komentarz`, `json`) "
			."VALUES ($kier_id,\"$par_id\",\"$nazwa\",\"$opis\",\"$aktywny\",\"$prac_wykon\",$rbh,$termin,\"$koment\",'$json');";
			if ($mysqli->query($query))
				echo json_encode(array('OK',$mysqli->insert_id,'INSERT','Zadanie dodane'));
			else{
				echo json_encode(array('NOK',$mysqli->error,$query,$mysqli->error));
				// echo json_encode(array('NOK',$mysqli->error,'INSERT',$mysqli->error));
				// echo $query;
			}
		} else {
			$query = "UPDATE `kart_pr_zadania` SET "
			."`user_id`=$kier_id,`par_id`=\"$par_id\",`nazwa`=\"$nazwa\", `opis`=\"$opis\", `aktywny`=\"$aktywny\", "
			."`prac_wykon`=\"$prac_wykon\", `rbh`=$rbh, `termin`=$termin, `komentarz`=\"$koment\", `json`='$json' WHERE `id`=$id;";
			if ($mysqli->query($query))
				echo json_encode(array('OK',$id,'UPDATE','Zadanie poprawione'));
			else
				echo $query;
		}
	}
	
	if (isset($_REQUEST["usun"]) || isset($_REQUEST["dodaj"])){
		if (isset($_REQUEST["callback"]) || isset($_REQUEST["jsoncallback"]))
			echo ')';
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
			select {
				width: 20em;
			}
			input[type='checkbox'] {
			}
			input[type='text'] {
				width: 20em;
			}
			textarea{
				width: 100%;
			}
			.ui-menu { width: 150px; }
			
			.ui-widget-content {
				background: none;			
			}
      .user {
        width: 20em;
      }
      .wykon {
        width: 50px;
      }
      .limit {
        width: 3em;
      }
		</style>
  <title>Zadanie</title>
  </head>
	<body>
		<div style="float:right;font-size: 0.8em;margin-right: 1em;">Zalogowano jako: <?php echo $_SESSION["myuser"]["nazwa"]; ?></div><br/>
		<form action="zadanie.php" style="background-image:url(images/logo_km100.png);background-repeat: no-repeat; ">
			<table style="margin:auto;">
					<tr>
						<th><label for="zad">Czynność:</label></th>
						<td><input type="text" id="zad" name="zad" /></td>
					</tr>
					<tr>
						<th><label for="akt">Status:</label></th>
						<td><select id="akt" name="akt">
								<option value="1">Aktywne</option>
								<option value="0">Nieaktywne</option>
								<option value="3">Zakończone</option>
							</select><br/>
						</td>
					</tr>
					<tr>
						<th><label for="typ">Praca dla:</label></th>
						<td><select id="typ" name="typ"></select></td>
					</tr>
					<tr>
						<th><label for="pnu">PNU Projekt nr:</label></th>
						<td><select id="pnu" name="pnu"><option value=""></option></select><br/>
						</td>
					</tr>
					<tr>
						<th><label for="pnu_etap">Etap:</label></th>
						<td><select id="pnu_etap" name="pnu_etap"><option value=""></option></select></td>
					</tr>
					<tr>
						<th><label for="pnu_zad">Numer zadania:</label></th>
						<td><select id="pnu_zad" name="pnu_zad"><option value=""></option></select></td>
					</tr>
					<tr>
						<th><label for="proj">Folder:</label></th>
						<td><select id="proj" name="proj"><option value="null"></option></select></td>
					</tr>
					<tr><td colspan="3">Opis czynności:<br/><textarea id="opis" rows="6" name="opis"></textarea></td></tr>
					<tr><th>Dział wykonujący:</th></tr>
					<tr><td id="dzial_wyk"></td></tr>
					<tr>
						<th><label for="rbh">Ilość roboczogodzin:</label></th>
						<td><input type="text" id="rbh" name="rbh" /></td>
					</tr>
					<tr>
						<th><label for="termin">Termin zakończenia prac:</label></th>
						<td><input type="text" id="termin" name="termin" /></td>
					</tr>
					<tr><td colspan="3">Komentarz:<br/><textarea id="koment" rows="6" name="koment"></textarea></td></tr>
					<tr>
						<td colspan="1"></td>
						<td colspan="2" style="text-align:right;">
							<div id="del">Usuń</div>
							<div id="gotowe">Zapisz</div>
							<div id="close">Anuluj</div>
						</td>
					</tr>
					<tr><td colspan=3>
            <table id="prac">
              <tr><th colspan=3>Pracownicy</th></tr>
              <tr><th>Nazwisko i imię</th><th>rbh</th><th>limit</th></tr>
            </table>
          </td></tr>
				</table>
		</form>
	</body>
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="jquery-ui-new.min.js"></script>
	<script type="text/javascript" src="users_zad.php"></script>
	<script type="text/javascript" src="http://192.168.30.12:88/pnu.js"></script>
	<script>
		var _zad_id = <?php if (isset($_REQUEST["id"])) echo $_REQUEST["id"]; else echo 'null';?>;
		var _user_id = <?php echo $_SESSION["myuser"]["id"];?>;
		var wykon = <?php echo json_encode($wykon);?>;
    
		var new_name = <?php if (isset($_REQUEST["text"])) echo "'".$_REQUEST["text"]."'"; else echo '""';?>;
		var par_id = <?php if (isset($_REQUEST["par"])) echo $_REQUEST["par"]; else echo 'null';?>;
		var par_pnu = <?php if (isset($_REQUEST["pnu"])) echo '"'.$_REQUEST["pnu"].'"'; else echo 'null';?>;

		if (typeof console === "undefined")
			console = {log:function(){}};
		else if (typeof console.log === "undefined")
			console.log = function(){};
	
    //pobranie danych o projektach i zadaniach
    $.ajax({
      url: 'baza.php?callback=?',
      dataType: 'json',
      type: 'POST',
      data: _zad_id?{zad_id:_zad_id}:null,
      timeout: 2000
    }).success(function(obj){
      init(obj);
    }).fail( function() {
      console.log('Błąd pobrania danych');
    });

    // pobranie danych o limitach godzin i ich wykorzystaniu
    var rbh_limits = {};
    $.ajax({
      url: 'rbh.php?callback=?',
      dataType: 'json',
      type: 'POST',
      data: {zad_id:_zad_id},
      timeout: 2000
    }).success(function(obj){
      // console.log(obj);
      rbh_limits = obj;
			for (var a in rbh_limits){
				$("#user_"+a+" .wykon").text(Math.round(rbh_limits[a].used/60) + "h")
				$("#user_"+a+" .limit").val(Math.round(rbh_limits[a].max/60))
			}
    }).fail( function() {
      console.log('Błąd pobrania danych o rbh.');
    });
    
    $('#prac').append('<tr><td><label id="user_all"><input type="checkbox" value="null"><b>Wszyscy</b><br/></label></td></tr>');
    $('#user_all > input').change(function() {
      $('#prac .user_box:visible:enabled').prop('checked',$(this).prop('checked'));
    });
    
    $("#rbh").spinner({
      min: 0,
      step: 1
    }).spinner( "value", 0 );

    $( "#termin" ).datepicker();
    $( "#termin" ).datepicker( "option", "dateFormat", "yy-mm-dd");

    //dodanie listy pracownikow i zablokowanie nie-podwladnych
    // console.log(users);
    var users_by_id = [];
    var dzial_wyk_list = [];
    var to_append = '';
    for (var u in users){
      var us = users[u];
      users_by_id[us.id] = us;
      // if (us.dzial != 'DRiW')
      if (us.dzial != 'TT')
        dzial_wyk_list[us.dzial] = true;
      else continue;
      to_append += '<tr id="user_'+us.id+'" class="user cl_d_'+us.dzial.replace("-","_")+'" style="display: none;" ><td><label title="' + us.nr 
        +'"><input type="checkbox" class="user_box" name="user" value="'+us.id+'">'+us.nazwa+'</label></td><td class="wykon"></td><td><input class="limit"></td></tr>';
    }
    $('#prac').append(to_append);
    for (var u in users){
      var us = users[u];
      if (us.dzial == 'TT') continue;

      if (us.kart_perm > 0 && us.id != 1) 
        $('#user_'+us.id).addClass("cl_kierownik");
      $('#user_'+us.id+' label').data('id',us.id).dblclick(function(){
        // console.log("dbcl "+$(this).data('id'));
        window.open('sum.php?id='+$(this).data('id'));
      });
// console.log(_user_sekcja);
      if ((us.kart_perm < _user_kart_perm) && _user_id != 1) {
        if ((us.dzial.indexOf(_user_dzial) != 0) && (_user_dzial != 'DRiW')) {
          $('#user_'+us.id+' input').attr('disabled', true);
        } else if (_user_kart_perm == 1 && _user_sekcja && us.sekcja) {
          if (us.sekcja != _user_sekcja) {
            $('#user_'+us.id+' input').attr('disabled', true);
          }
        }
      }
    }
    // console.log(users_by_id);
    // console.log(dzial_wyk_list);
    
    $('.cl_kierownik').css("font-weight","Bold");

    var dzial_wyk_list_temp = []
    for (var i in dzial_wyk_list) {
      dzial_wyk_list_temp.push(i);
    }
    dzial_wyk_list = dzial_wyk_list_temp.sort();
    // console.log(dzial_wyk_list);

    for (var i in dzial_wyk_list) {
      $('#dzial_wyk').append('<label style="margin-left: 2em;" title="'+dzial_wyk_list[i]+'"><input type="checkbox" name="dzial_wyk" value="'+dzial_wyk_list[i]+'">'+dzial_wyk_list[i]+'</input></label>');
    }
    
    $('#dzial_wyk >> input').change(function() {
      var $dzial = $('.cl_d_'+$(this).val().replace("-","_"));
      if ($(this).prop('checked'))
        $dzial.show();
        // $('[class^="cl_d_'+$(this).val().replace("-","_")+'"]').show();
      else
        // console.log($dzial)
        $dzial.filter(function(idx, el){return $('.user_box:not(:checked)',el).length > 0;}).hide();
        // $('[class^="cl_d_'+$(this).val().replace("-","_")+'"]').hide();
    });
    
    function for_each_project(projekty, obj, parents, callback) {
      for (var oi in obj.children){
        var p = projekty[oi];
        if (p.aktywny != 1) continue;
        // console.log(p.nazwa, p.text);
        if (p.files == 1)
          callback(p.id, parents.concat([p.text]));
        for_each_project(projekty, obj.children[oi], parents.concat([p.v]), callback);
      }
    }
    
    //wypelnienie listy PNU
    for (var si in pnu) {
      for (var pi in pnu[si]) {
        var p = pnu[si][pi];
        // var val = si+"_"+pi;
        // if (si == 'Z')
          // val = pi;
        // $('#pnu').append('<option value=\"'+si+"_"+pi+'\" title="'+p.opis+'">'+p.nr+'</option>');
        $('#pnu').append('<option value=\"'+p.nr+'\" title="'+p.opis+'">'+p.nr+'</option>');
      }
    }

    function typ_change() {
      clear_err();
      var val = $('#typ').val();
      // console.log("typ change", val);
      if (val == 'null') {
        $('#pnu').parent().parent().hide();
        $('#pnu_zad').parent().parent().hide();
        $('#pnu_etap').parent().parent().hide();
        $('#proj').parent().parent().hide();
      } else if (val == 0) {
        $('#pnu').parent().parent().show();
        $('#pnu_zad').parent().parent().show();
        $('#pnu_etap').parent().parent().show();
        $('#proj').parent().parent().hide();
      } else {
        $('#pnu').parent().parent().hide();
        $('#pnu_zad').parent().parent().hide();
        $('#pnu_etap').parent().parent().hide();
        $('#proj').parent().parent().show();
        $('#proj').html(proj_opts[val]);
      }
    }
    
    pnu_change = function(){
      clear_err();
      // console.log("pnu_change",$('#pnu').val());
      $('#pnu_etap').empty();
      if ($('#pnu').val() == "" || $('#pnu').val() == null) {
        $('#pnu_etap').append('<option value="" title=""></option>');
        $('#pnu_zad').empty();
        $('#pnu_zad').append('<option value="" title=""></option>');
      } else {
        var si_pi = $('#pnu').val().match(/(\d*)(\w*)/);
        si_pi[0] = si_pi[2];
        if (si_pi[0] == "") si_pi[0] = "Z";
        // console.log(si_pi);
        // var si_pi = $('#pnu').val().split("_");
        for (var ei in pnu[si_pi[0]][si_pi[1]].etap) {
          if(!pnu[si_pi[0]][si_pi[1]].etap[ei]) continue;
          $('#pnu_etap').append('<option value="'+ei+'" title="">'+ei+'</option>');
        }
        pnu_etap_change();
      }
    }
    
    pnu_etap_change = function(){
      // console.log("pnu_etap_change", $('#pnu_etap').val());
      $('#pnu_zad').empty();
      var si_pi = $('#pnu').val().match(/(\d*)(\w*)/);
      si_pi[0] = si_pi[2];
      if (si_pi[0] == "") si_pi[0] = "Z";
      // var si_pi = $('#pnu').val().split("_");
      var ei = $('#pnu_etap').val();
      for (var zi in pnu[si_pi[0]][si_pi[1]].etap[ei]) {
        var z = pnu[si_pi[0]][si_pi[1]].etap[ei][zi];
        $('#pnu_zad').append('<option value="'+z.nr+'" title="'+z.nazwa+'">'+z.nr+'</option>');
      }
    }

    $('#typ').change(typ_change);
    $('#pnu').change(pnu_change);
    $('#pnu_etap').change(pnu_etap_change);

    $('input').change(clear_err).click(clear_err);
    $('#opis').change(clear_err).click(clear_err);
    $('#proj').change(clear_err).click(clear_err);
    
    function clear_err(){
      // $('input').blur(function() {$(this).css("background-color",'');});
      // $('select').blur(function() {$(this).css("background-color",'');});
      $('input').css("background-color",'');
      $('select').css("background-color",'');
      $('#opis').css("background-color",'');
      $('#typ').css("background-color",'');
      $('#proj').css("background-color",'');
      $('#pnu').css("background-color",'');
      $('#pnu_etap').css("background-color",'');
      $('#pnu_zad').css("background-color",'');
    }

    function set_pnu(zlec, callback) {
      // console.log("set_pnu", zlec);
      if (zlec == "") { if (callback) callback(); return; }
      var wzor = /^([0-9]{1,3})([A-Z]{0,4})[\/]?([0-9]{1,3})?[\/]?([0-9]{1,3})?$/;
      var zlec_t = zlec.match(wzor);
      // console.log(zlec_t);
      if (zlec_t) {
        $('#pnu').val(zlec_t[1]+zlec_t[2]);
        if (zlec_t[2] == "") zlec_t[2] = "Z";
        // if (zlec_t[2] == "RY" && zlec_t[1] == 4 && zlec_t[3] == 2) zlec_t[1] += "w2"
        // $('#pnu').val(zlec_t[2]+"_"+zlec_t[1]);
        pnu_change();
        $('#pnu_etap').val(zlec_t[3]);
        pnu_etap_change();
        $('#pnu_zad').val(zlec_t[4]);
        $('#proj').val(null);
      } else {
        console.log("set_pnu error", zlec);
      }
      if (callback) callback();
    }

    $(".limit").spinner({
      min: 0,
      step: 1
    }).spinner( "value", 0 );
    
    var proj_opts = {};

    function init(obj) {
      var o_projekty = obj.o_projekty;
      
      //wypelnienie listy projektów
      $('#typ').append('<option value="null"></option>');
      for (var pi in obj.projekty_tree.children){
        var p = o_projekty[obj.projekty_tree.children[pi].id];
        $('#typ').append('<option value="'+p.id+'">'+p.text+'</option>');
        var options = [];
        if (o_projekty[p.id].files == 1) { options.push({id:null,text:'',title:''}); }
        for_each_project(o_projekty, obj.projekty_tree.children[pi], [p.text], function(id, arr) {
          options.push({id:id,text:arr.join('->'),title:o_projekty[id].opis});
        });
        //sortowanie folderow			
        options.sort(function(a,b) {
          if (a.text > b.text) return 1;
          if (a.text < b.text) return -1;
          return 0;
        });
        options.forEach(function(item, index){options[index] = '<option value="'+item.id+'" title="'+item.title+'">'+item.text+'</option>'});
        proj_opts[p.id] = options;
      }
      
      
      if (_zad_id){
        var _zadanie = obj.o_zadania[_zad_id];
        console.log("_zadanie", _zadanie);
        $('#zad').val(_zadanie.nazwa);
        $('#akt').val(_zadanie.aktywny);
        $('#opis').val(_zadanie.opis);
        $('#forma_zlec').val(_zadanie.forma);
        $('#koment').val(_zadanie.komentarz);
        
        $('#rbh').val(_zadanie.rbh);
        if (_zadanie.termin != null){
          var temp_date = new Date();
          temp_date.setTime(_zadanie.termin);
          // console.log(temp_date);
          $('#termin').datepicker('setDate',temp_date);
        }
        var prac2 = _zadanie.prac_wykon.split("'").join('').split(',');
        for (var p in prac2) {
          if (prac2[p] == '') {continue;}
          if (!users_by_id[prac2[p]]) {continue;}
          var dzial = users_by_id[prac2[p]].dzial;
          $('#user_'+prac2[p]+' .user_box').attr('checked',true);
          $('#dzial_wyk [value='+dzial+']').attr('checked',true);
          $('.cl_d_'+dzial.replace("-","_")).show();
        }
        // $('#gotowe').text("Edytuj");
        $('#del').button().click(function(){
          if (confirm('Czy napewno chcesz skasować zadanie ?')){
            send(true);
          }
        });

        if (_zadanie.par_id > 0) {
          $('#typ').val(_zadanie.parents[_zadanie.parents.length-1]);
          if (o_projekty[_zadanie.par_id].files == 0) {
            $('#typ').attr('disabled', true);
            typ_change();
            $('#proj')
              .html('<option value="'+_zadanie.par_id+'">'+o_projekty[_zadanie.par_id].text+'</option>')
              .attr('disabled', true);
          } else {
            typ_change();
            $('#proj').val(_zadanie.par_id);
          }
        } else {
          $('#typ').val(0);
          typ_change();
          if (_zadanie.json && _zadanie.json.pnu) {
            set_pnu(_zadanie.json.pnu);
          }
        }
      } else {
        $('#del').hide();
        $('#zad').val(new_name);
        typ_change();
        if (par_pnu) {
          // console.log("par_pnu",par_pnu);
          $('#typ').val(0);
          typ_change();
          set_pnu(par_pnu);
        } else if (par_id) {
          // console.log("par_id",par_id);
          $('#typ').val(o_projekty[par_id].parents[o_projekty[par_id].parents.length-1]);
          typ_change();
          $('#proj').val(par_id);
        }
      }

    }
    
    $('#gotowe').button().click(function(){send(false);});
    $('#close').button().click(function(){window.close();});

    function send(del){
      if (del) {
        $.ajax({
          url: 'zadanie.php?callback=?',
          dataType: 'json',
          type: 'POST',
          data: {usun: _zad_id},
          timeout: 2000
        }).success(function(obj){
          if (obj && obj[0]=="OK"){
            if (window.opener)
              window.opener.location.reload();
            if (confirm(obj[3]+'\n\rCzy chcesz zamknąć to zadanie ?'))
              window.close();
          } else
            alert('Błąd skryptu.');
        }).fail( function() {
          alert('Błąd serwera kart pracy.');
        });
        return;
      }
      clear_err();
      var obj = {};
      obj.aktywny = $('#akt').val();
      obj.kier_id = _user_id;
      obj.nazwa = $('#zad').val();
      obj.opis = $('#opis').val();
      obj.koment = $('#koment').val();
      obj.json = '';
      obj.par_id = $('#proj').val();
      if ($('#typ').val() == 0) {
          obj.json = '{"pnu":"'+$('#pnu').val() + '/' + $('#pnu_etap').val().trim() + '/' + $('#pnu_zad').val().trim()+'"}';
          obj.par_id = 'NULL';
      } else {
        if (obj.par_id == "null") {
          obj.par_id = $('#typ').val();
        }
      }
      obj.rbh = $('#rbh').val()/1;
      if ($('#termin').val()){
        obj.termin = $('#termin').datepicker('getDate');
        if (obj.termin.getUTCHours() > 12) {
          obj.termin.setUTCHours(0);
          obj.termin.setDate(obj.termin.getDate() + 1);
        }
        // console.log(obj.termin);
        obj.termin = obj.termin.getTime();
      } else 
        obj.termin = 'null';
      
      obj.prac_wykon = '';
      $('.user_box:checked:visible').each(function(){
        if ($(this).val() == 'null')
          return;
        if (obj.prac_wykon != '')
          obj.prac_wykon += ",";
        obj.prac_wykon += "'"+$(this).val()+"'";
      })

      if (obj.nazwa == ''){
        $('#zad').css("background-color",'red');
        alert('Proszę wpisać nazwę');
        return;
      }

      if ($('#typ').val() == 'null'){
        $('#typ').css("background-color",'red');
        alert('Proszę wybrać typ pracy');
        return;
      } else if ($('#typ').val() == 0) {
        if (!$('#pnu').val()) {
          $('#pnu').css("background-color",'red');
          $('#pnu_etap').css("background-color",'red');
          $('#pnu_zad').css("background-color",'red');
          alert('Proszę wybrać numer, etap i zadanie');
          return;
        }
      }

      if (obj.opis == ""){
        $('#opis').css("background-color",'red');
        alert('Proszę opisać zadanie');
        return;
      }

      var rbh_limits_update = {};
      for (var u in users){
        var ri = users[u].id;
        if ((!rbh_limits[ri] && $("#user_"+ri+" .limit").val()>0)
        || (rbh_limits[ri] && rbh_limits[ri].max/60 != $("#user_"+ri+" .limit").val()))
          rbh_limits_update[ri] = $("#user_"+ri+" .limit").val()*60;
      }
      // console.log('rbh_limits_update',rbh_limits_update);
      
      obj.dodaj = 0;	
      if (_zad_id)
        obj.dodaj = _zad_id;	

console.log(obj);

      $.ajax({
        url: '?callback=?',
        dataType: 'json',
        type: 'POST',
        data: obj,
        timeout: 2000
      }).success(function(obj){
        if (obj && obj[0]=="OK"){
          if (Object.keys(rbh_limits_update).length > 0) {
            $.ajax({
              url: 'rbh.php?callback=?',
              dataType: 'json',
              type: 'POST',
              data: {update_id: _zad_id,array:rbh_limits_update},
              timeout: 2000
            }).success(function(obj){
              if (obj && obj[0]=="OK"){
                // console.log('OK');
              } else {
                console.log(obj, {update_id: _zad_id, array: rbh_limits_update});
              }
            }).fail( function() {
              alert('Błąd ustawinia limitów.');
            });
          }
          if (window.opener)
            window.opener.location.reload();
          if (obj[2] == "DELETE"){
            alert(obj[3]);
            window.close();
          }
          if (confirm(obj[3]+'\n\rCzy chcesz zamknąć to zadanie ?'))
            window.close();
        } else
          alert('Błąd skryptu.');
      }).fail( function() {
        alert('Błąd serwera kart pracy.');
      });
    };
	</script>
</html>
