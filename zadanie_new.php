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
		// $query = "DELETE FROM `kart_pr_zadania` WHERE `id`=$id;";
		$query = "UPDATE `kart_pr_zadania` SET `deleted` = 1 WHERE `id`=$id;";
		if ($mysqli->query($query))
			echo json_encode(array('OK',$id,'DELETE','Zadanie usunięte'));
		else
			echo $query;
		$query_log .= $query ."\n";
		$query = "INSERT INTO `logi`(`kto`, `co`) VALUES (".$_SESSION["myuser"]["id"].", 'usuniecie (id=$id)')";
		$mysqli->query($query);
	}
	if (isset($_REQUEST["dodaj"])){
		$kier_id = $_REQUEST["kier_id" ];
		$co = $_REQUEST["co" ];
		$forma = $_REQUEST["forma" ];
		$opis = $_REQUEST["opis" ];
		$aktywny = $_REQUEST["aktywny"];
		$typ = $_REQUEST["typ"];
		$proj = $_REQUEST["proj"];
		if ($proj == 'null')
			$proj = "NULL";
		else
			$proj = '"'.$proj.'"';
		$koment = $_REQUEST["koment"];
		$dzial_wyk = $_REQUEST["dzial_wyk"];
		$dzial_zlec = $_REQUEST["dzial_zlec"]/1;
		$prac_wykon = $_REQUEST["prac_wykon"];
		$zlec = $_REQUEST["zlec" ];
		$rbh = $_REQUEST["rbh" ];
		$termin = $_REQUEST["termin"];
		
		$id = $_REQUEST["dodaj"];
		if ($_REQUEST["dodaj"] == 0){
			$query = "INSERT INTO `kart_pr_zadania`(`user_id`,`par_id`,`nazwa`, `forma`, `opis`, `aktywny`, `typ`, `dzial_wyk`, `dzial_zlec`, `prac_wykon`, `zlecenie`, `rbh`, `termin`, `komentarz`) "
			."VALUES (\"$kier_id\",$proj,\"$co\",\"$forma\",\"$opis\",\"$aktywny\",\"$typ\",\"$dzial_wyk\",$dzial_zlec,\"$prac_wykon\",\"$zlec\",$rbh,$termin,\"$koment\");";
			if ($mysqli->query($query))
				echo json_encode(array('OK',$mysqli->insert_id,'INSERT','Zadanie dodane'));
			else{
				echo json_encode(array('OK',$mysqli->error,'INSERT',$mysqli->error));
			}
			$query_log .= $query ."\n";
			// echo $query;
		} else {
			$query = "SELECT * FROM `kart_pr_zadania` WHERE `id`=$id;";
			$result = $mysqli->query($query);
			$row = null;
			if ($result) {
				$row = $result->fetch_assoc();
			}
			
			$query = "UPDATE `kart_pr_zadania` SET "
			."`user_id`=\"$kier_id\",`par_id`=$proj,`nazwa`=\"$co\", `forma`=\"$forma\", `opis`=\"$opis\", `aktywny`=\"$aktywny\", "
			."`typ`=\"$typ\", `dzial_wyk`=\"$dzial_wyk\", `prac_wykon`=\"$prac_wykon\", `rbh`=$rbh, `termin`=$termin, "
			."`dzial_zlec`=$dzial_zlec, `zlecenie`=\"$zlec\", `komentarz`=\"$koment\" WHERE `id`=$id;";
			if ($mysqli->query($query))
				echo json_encode(array('OK',$id,'UPDATE','Zadanie poprawione'));
			else
				echo $query;
			$query_log .= $query ."\n";
			if ($row && ($row["aktywny"] != $aktywny)) {
				$query = "INSERT INTO `logi`(`kto`, `co`) VALUES (".$_SESSION["myuser"]["id"].", 'zmiana statusu czynności (id=$id) z ".$row["aktywny"]." na $aktywny')";
				$mysqli->query($query);
			}
			// echo $query;
		}
//		echo $query;
//		var_dump($_REQUEST);
	}
	
//logowanie
	if ($query_log != '') {
		$file = "log_z.txt";
		file_put_contents($file, "data = ".date("c")."\n", FILE_APPEND | LOCK_EX);
		file_put_contents($file, "userid = ".$_SESSION["myuser"]["id"]."\n", FILE_APPEND | LOCK_EX);
		file_put_contents($file, $query_log, FILE_APPEND | LOCK_EX);
	}
	
	if (isset($_REQUEST["usun"]) || isset($_REQUEST["dodaj"])){
		if (isset($_REQUEST["callback"]) || isset($_REQUEST["jsoncallback"]))
			echo ')';
		exit;
	}

	$zadania = array();
	if (isset($_REQUEST["id"])){
		$query = "SELECT * FROM `kart_pr_zadania` WHERE id = ".$_REQUEST["id"].";";
			
		$result = $mysqli->query($query);
		if ($result)
			while($row = $result->fetch_assoc()){
				$zadania[] = $row;
			}
		$query = "SELECT user_id, kat_id, SUM( czas ) /60 AS czas, nazwa FROM `kart_pr_prace` LEFT JOIN kart_pr_kat ON kart_pr_prace.kat_id=kart_pr_kat.id WHERE zadanie = ".$_REQUEST["id"]." GROUP BY user_id, kat_id;";
		$result = $mysqli->query($query);
		if ($result) {
			while($row = $result->fetch_assoc()){
				$wykon[] = $row;
			}
		}
	}
	
	$projekty = array();
	// $query = "SELECT * FROM `kart_pr_projekty` WHERE id > 2;";
	// $query = "SELECT * FROM `kart_pr_projekty` WHERE par_id is not null;";
	$query = "SELECT * FROM `kart_pr_projekty`;";
		
	$result = $mysqli->query($query);
	if ($result) {
		while($row = $result->fetch_assoc()){
			$projekty[$row["id"]] = $row;
		}
	}
	// var_dump($projekty);
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
		</style>
  <title>Zadanie</title>
  </head>
	<body>
		<div style="float:right;font-size: 0.8em;margin-right: 1em;">Zalogowano jako: <?php echo $_SESSION["myuser"]["nazwa"]; ?></div><br/>
		<form action="zadanie.php" style="background-image:url(images/logo_km100.png);background-repeat: no-repeat; ">
			<table style="margin:auto;">
					<tr class="n_l4">
						<th><label for="zad">Czynność:</label></th>
						<td><input type="text" id="zad" name="zad" /></td>
					</tr>
					<tr>
						<th><label for="akt"></label></th>
						<td><select id="akt" name="akt">
								<option value="1">Aktywne</option>
								<option value="0">Nieaktywne</option>
								<option value="3">Zakończone</option>
							</select><br/>
						</td>
					</tr>
					<tr>
						<th><label for="typ">Praca dla:</label></th>
						<td><select id="typ" name="typ">
								<option value="PNU">PNU</option>
								<option value="DRW">DRW</option>
								<option value="Zlecenia">Zlecenia</option>
								<option value="DPP">DPP</option>
								<option value="DUG">DUG</option>
								<option value="DMiS">DMiS</option>
								<option value="DZZ DZR DZW">DZZ DZR DZW</option>
								<option value="DA">DA</option>
								<option value="DTM">DTM</option>
								<option value="Kopex">Kopex</option>
							</select><br/>
						</td>
					</tr>
					<tr class="n_l4">
						<th><label id="lab_zlec" for="zlec">Numer zlecenia / zamówienia:</label></th>
						<td>
							<select id="mpk" name="mpk"><option value=""></option></select>
							<select id="komisja" name="komisja"><option value=""></option></select>
							<br/>
						</td>
					</tr>
					<tr class="n_l4">
						<th><label id="lab_zlec" for="pnu">PNU Projekt nr:</label></th>
						<td><select id="pnu" name="pnu"><option value=""></option></select><br/>
						</td>
					</tr>
					<tr class="n_l4">
						<th><label for="pnu_etap">Etap:</label></th>
						<td><select id="pnu_etap" name="pnu_etap"><option value=""></option></select></td>
<!--						<td><input type="text" id="pnu_etap" name="pnu_etap" /></td> -->
					</tr>
					<tr class="n_l4">
						<th><label for="pnu_zad">Numer zadania:</label></th>
						<td><select id="pnu_zad" name="pnu_zad"><option value=""></option></select></td>
<!--						<td><input type="text" id="pnu_zad" name="pnu_zad" /></td>	-->
					</tr>
					<tr>
						<th>Folder:</th>
						<td><select id="proj" name="proj"><option value="null"></option></select></td>
					</tr>
					<tr><td colspan="3">Opis czynności:<br/><textarea id="opis" rows="6" name="opis"></textarea></td></tr>
					<tr>
						<th>Zlecone przez dział:</th>
						<td><select id="dzial_zlec" name="dzial_zlec"><option value="null"></option></select></td>
					</tr>
					<tr>
						<th>Forma zlecenia:</th>
						<td><select id="forma_zlec" name="forma_zlec"><option value="ustnie">Ustnie</option><option value="e-mail">E-mail</option><option value="telefon">Telefonicznie</option><option value="pnu">Zgodnie z PNU</option></select></td>
					</tr>
					<tr>
						<th>Dział wykonujący:</th>
					</tr>
					<tr>
						<td id="dzial_wyk">
						</td>
					</tr>
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
							<div id="gotowe">Dodaj</div>
							<div id="close">Zamknij</div>
						</td>
					</tr>
					<tr>
						<th>Pracownicy:</th>
					</tr>
					<tr>
						<td id="prac"></td>
					</tr>
					<tr>
						<td><span class="notify">Powiadom wybrane osoby</span></td>
					</tr>
				</table>
		</form>
	</body>
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="jquery-ui.min.js"></script>
	<script type="text/javascript" src="jquery.ui.datepicker-pl.js"></script>
	<script type="text/javascript" src="baza_karta.php"></script>
	<script type="text/javascript" src="users_zad.php"></script>
	<script type="text/javascript" src="http://192.168.30.12:88/pnu.js"></script>
	<script>
		// _user_kart_perm = <?php echo $_SESSION["myuser"]["kart_perm"];?>;
		_user_id = <?php echo $_SESSION["myuser"]["id"];?>;
		_zad_id = <?php if (isset($_REQUEST["id"])) echo $_REQUEST["id"]; else echo 'null';?>;
//		_kat_id = <?php if (isset($_REQUEST["id_k"])) echo $_REQUEST["id_k"]; else echo 'null';?>;
//		_dzia_id = <?php if (isset($_REQUEST["id_d"])) echo $_REQUEST["id_d"]; else echo 'null';?>;
//		_copy_id = '<?php if (isset($_REQUEST["copy_id"])) echo $_REQUEST["copy_id"]; else echo 'null';?>';
		var new_name = <?php if (isset($_REQUEST["text"])) echo "'".$_REQUEST["text"]."'"; else echo '""';?>;
		var sum_user_id = <?php if (isset($_REQUEST["user_id"])) echo $_REQUEST["user_id"]; else echo 'null';?>;
		var wykon = <?php echo json_encode($wykon);?>;
		var projekty = <?php echo json_encode($projekty);?>;
		var par_id = <?php if (isset($_REQUEST["par"])) echo $_REQUEST["par"]; else echo 'null';?>;
		var par_pnu = <?php if (isset($_REQUEST["pnu"])) echo '"'.$_REQUEST["pnu"].'"'; else echo 'null';?>;

		if (typeof console === "undefined")
			console = {log:function(){}};
		else if (typeof console.log === "undefined")
			console.log = function(){};
			
		$(function() {
			for (var d in dzialy){
				// if (d<5){
					$('#dzial_zlec').append('<option value=\"'+d+'\" title="'+dzialy[d].opis+'">'+dzialy[d].nazwa+'</option>');
				// }
			}

			$('#prac').append('<label id="user_all"><input type="checkbox" value="null"><b>Wszyscy</b><br/></label>');
			$('#user_all > input').change(function() {
				$('#prac >> input:visible:enabled').prop('checked',$(this).prop('checked'));
			});
			
			$("#rbh").spinner({
				min: 0,
				step: 1
			}).spinner( "value", 0 );

			$("#termin").datepicker();

			//dodanie listy pracownikow i zablokowanie nie-podwladnych
			// console.log(users);
			var users_by_id = [];
			var dzial_wyk_list = [];
			for (var u in users){
				var us = users[u];
				users_by_id[us.id] = us;
				// if (us.dzial != 'DRiW')
				if (us.dzial != 'TT')
					dzial_wyk_list[us.dzial] = true;
				var czy_kier = "";
				if (us.kart_perm > 0 && us.id != 1) 
					czy_kier = " cl_kierownik";
// console.log(_user_sekcja);

				$('#prac').append('<label id="user_'+us.id+'" class="cl_d_'+us.dzial.replace("-","_")+czy_kier+'" style="display: none;" title="' + us.nr + '"><input type="checkbox" name="user" value="'+us.id+'">'+us.nazwa+'<span class="wykon"></span><br/></label>');
				$('#user_'+us.id).data('id',us.id).dblclick(function(){
					// console.log("dbcl "+$(this).data('id'));
					window.open('sum.php?id='+$(this).data('id'));
				});
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
				// console.log($(this).val());
				if ($(this).prop('checked'))
					$('[class^="cl_d_'+$(this).val().replace("-","_")+'"]').show();
					// $('.cl_d_'+$(this).val().replace("-","_")).show();
				else
					$('[class^="cl_d_'+$(this).val().replace("-","_")+'"]').hide();
					// $('.cl_d_'+$(this).val().replace("-","_")).hide();
			});
			
			$('.notify').hide();
			// $('[class^="cl_d_"] > input:visible:enabled').before('<span class="notify">Powiadom</span>');
			// $('[class^="cl_d_"] .notify').button();
			$('.notify').button().click(function(){
				console.log('powiadom');
				// console.log(_zad_id);
				var adresaci = [];
				$('#prac >> input:checked:visible').each(function(){
					// console.log();
					adresaci.push($(this).parent().attr("title"));
				});
				var obj = {kto:adresaci, co: "Powiadomienie z czynności " + '<a href="http://192.168.30.12/karty_pracy/zadanie.php?id='+_zad_id+'">Link do czynności</a>' };
				// console.log(obj);
				// if (false)
				$.ajax({
					url: 'http://192.168.30.12:88/email?callback=?',
					dataType: 'json',
					type: 'POST',
					data: obj,
					timeout: 10000
				}).success(function(obj){
					if (!obj)
						console.log("ok");
					else
						console.log(obj);
				}).fail(function(obj){
					console.log("fail");
					console.log(obj);
				});
			});

			//wypelnienie listy PNU
			for (var si in pnu) {
				for (var pi in pnu[si]) {
					var p = pnu[si][pi];
					if (si == "RY" && pi == "4") 
						$('#pnu').append('<option value=\"'+si+"_"+pi+'\" title="'+p.opis+'">'+p.nr + " Wariant 1"+'</option>');
					else if (si == "RY" && pi == "4w2") 
						$('#pnu').append('<option value=\"'+si+"_"+pi+'\" title="'+p.opis+'">'+p.nr + " Wariant 2"+'</option>');
					else
						$('#pnu').append('<option value=\"'+si+"_"+pi+'\" title="'+p.opis+'">'+p.nr+'</option>');
				}
			}
			
			//wypelnienie listy projektow i mpk 
			for (var p in projekty){
        if (!projekty[p].par_id) continue;
        // console.log(projekty[p]);
				if (p>=3 && (projekty[p].deleted == 0)) {
					if (projekty[p].par_id == 1) {
						$('#mpk').append('<option value=\"'+projekty[p].id+'\" title="'+projekty[p].opis+'">'+projekty[p].nazwa+'</option>');
					} else if (projekty[projekty[p].par_id].par_id == 2) {
						//foldery komisji w DH (poziom 2)
						$('#komisja').append('<option value=\"'+projekty[p].id+'\" title="'+projekty[p].opis+'">'+projekty[p].nazwa+'</option>');
// console.log(projekty[p].nazwa + "-" + projekty[projekty[p].par_id].par_id);
					
					} else if (projekty[p].par_id == 2) {
						//foldery glowne w DH (typy maszyn) - czynnosci niedozwolone
					} else if (projekty[p].par_id == 0) {
						//foldery w PNU
					} else {
						var groups = ["cl_pnu", "cl_mpk", "cl_dh"];
						var t_title = projekty[p].nazwa;
						var t_par_id = projekty[p].par_id;
						do {
							t_title = projekty[t_par_id].nazwa + " -> " + t_title;
							t_par_id = projekty[t_par_id].par_id;
						} while (t_par_id > 3);
            // console.log(t_par_id);
            if (t_par_id)
						t_title = projekty[t_par_id].nazwa + " -> " + t_title;
						// console.log("g: "+group + " - " + par_id);
						// console.log("g: "+t_par_id);
						$('#proj').append('<option value=\"'+projekty[p].id+'\" title="'+projekty[p].opis+'" class= "' + groups[t_par_id] + '" >'+t_title+'</option>');
					}
				}
			}
			
			//sortowanie folderow			
			var proj_options = $("#proj option");
			proj_options.sort(function(a,b) {
				if (a.text > b.text) return 1;
				else if (a.text < b.text) return -1;
				else return 0
			})
			$("#proj").empty().append( proj_options );

			//sortowanie komisji
			var komis_options = $("#komisja option");
			komis_options.sort(function(a,b) {
				if (a.text > b.text) return 1;
				else if (a.text < b.text) return -1;
				else return 0
			})
			$("#komisja").empty().append( komis_options );
			
			pnu_change = function(){
				// console.log("pnu_change");
				$('#pnu_etap').empty();
				if ($('#pnu').val() == "") {
					$('#pnu_etap').append('<option value="" title=""></option>');
					$('#pnu_zad').empty();
					$('#pnu_zad').append('<option value="" title=""></option>');
				} else {
					var si_pi = $('#pnu').val().split("_");
					for (var ei in pnu[si_pi[0]][si_pi[1]].etap) {
						if(!pnu[si_pi[0]][si_pi[1]].etap[ei]) continue;
						$('#pnu_etap').append('<option value="'+ei+'" title="">'+ei+'</option>');
					}
					pnu_etap_change();
				}
			}
			
			pnu_etap_change = function(){
				// console.log("pnu_etap_change");
				$('#pnu_zad').empty();
				var si_pi = $('#pnu').val().split("_");
				var ei = $('#pnu_etap').val();
				for (var zi in pnu[si_pi[0]][si_pi[1]].etap[ei]) {
					var z = pnu[si_pi[0]][si_pi[1]].etap[ei][zi];
					$('#pnu_zad').append('<option value="'+z.nr+'" title="'+z.nazwa+'">'+z.nr+'</option>');
				}
			}

			//rekurencyjne szukanie typu zadania (mpk, pnu , dh)
			function find_typ(par_id){
        // console.log('par_id', par_id, projekty[par_id]);
				// if (par_id < 3) {
					// if (projekty[par_id].nazwa == "DRW")
						// return "MPK";
					// return projekty[par_id].nazwa;
				// } else {
          if (projekty[par_id].par_id === null) return projekty[par_id].nazwa;
					return find_typ(projekty[par_id].par_id);
				// }
			}

			function find_mpk(par_id){
				// console.log("find_mpk");
				// console.log(par_id);
				if (projekty[par_id].par_id < 3) {
					if (projekty[par_id].par_id == 1)
						return par_id;
					else return "null";
				} else {
					return find_mpk(projekty[par_id].par_id);
				}
			}
			
			function set_par(par_id2){
				console.log("set_par");
				console.log(par_id2);
				if ((par_id2 != null) && (par_id2 != 'null')) {
					var typ = find_typ(par_id2);
					$('#typ').val(typ);
					// .attr('disabled', true);
					console.log('typ', typ);
					switch (typ){
						case "Zlecenia":
							// $('#zlec').val('');
							set_komisja(par_id2);
						break;
						case "PNU":
							set_pnu(projekty[par_id2].zlec);
						break;
						default :
						// break;
						// case "MPK":
							// console.log(projekty[par_id2].id);
							// console.log(projekty[par_id2].par_id);
							// $('#mpk').val(projekty[par_id2].par_id);
							$('#mpk').val(find_mpk(par_id2));
							// $('#mpk').val(par_id2);
							// .attr('disabled', true);
						// break;
					}
					clear_err();
					return true;
				}
				clear_err();
				return false;
			}

			proj_change = function(par_id){
				console.log("proj_change");
// console.log(par_id);
				$('#typ').attr('disabled', false);
				$('#mpk').attr('disabled', false);
				$('#pnu').attr('disabled', false);
				$('#komisja').attr('disabled', false);
				$('#pnu_etap').attr('disabled', false);
				$('#pnu_zad').attr('disabled', false);
				// if (!par_id) par_id = $('#proj').val();
				if ((par_id != null) && (par_id != 'null')) {
					set_par(par_id);
					// $('#typ').attr('disabled', true);
					// $('#pnu').attr('disabled', true);
					// $('#pnu_etap').attr('disabled', true);
					// $('#pnu_zad').attr('disabled', true);
					// $('#mpk').attr('disabled', true);
					
					// $('#komisja').attr('disabled', true);
					// console.log("OK");
				} else {
					$('#komisja').val(null);
					$('#mpk').val(null);
				}
			}
			
			$('#proj').change(function(){
				// console.log("proj_change");
				proj_change($('#proj').val());
			});
			$('#komisja').change(function(){
				$('#proj').val(null);
			});
			$('#pnu').change(pnu_change);
			$('#pnu_etap').change(pnu_etap_change);

			$('input').change(clear_err).click(clear_err);
			$('#opis').change(clear_err).click(clear_err);
			$('#prac').change(clear_err).click(clear_err);
			$('#dzial').change(clear_err).click(clear_err);
			$('#typ').change(clear_err).click(clear_err);
			$('#mpk').change(clear_err).click(clear_err);
			$('#pnu').change(clear_err).click(clear_err);
			$('#dzial_zlec').change(clear_err).click(clear_err);

			function clear_err(){
				$('input').blur(function() {$(this).css("background-color",'');});
				$('select').blur(function() {$(this).css("background-color",'');});
				$('input').css("background-color",'');
				$('select').css("background-color",'');
				$('#prac').css("background-color",'');
				$('#komisja').css("background-color",'');
				$('#dzial_wyk').css("background-color",'');
				$('#opis').css("background-color",'');
				$('#mpk').css("background-color",'');
				$('#pnu').css("background-color",'');
				$('#pnu_zad').css("background-color",'');
				$('#pnu_etap').css("background-color",'');
				// $('#zlec').css("background-color",'');
				// console.log($('#pnu_zad').parent().parent());
				$('#pnu_zad').parent().parent().hide();
				$('#pnu_etap').parent().parent().hide();
				$('#pnu').parent().parent().hide();
				// $('#zlec').parent().parent().show();
				$('#komisja').parent().parent().show();
				$('#proj').parent().parent().show();
				switch ($('#typ').val()){
					case "PNU":
						// $('#lab_zlec').text("PNU Projekt nr:");
						$('#pnu_zad').parent().parent().show();
						$('#pnu_etap').parent().parent().show();
						$('#pnu').parent().parent().show();
						// $('#zlec').parent().parent().hide();
						$('#komisja').parent().parent().hide();
						$('#mpk').parent().parent().hide();
						$('.cl_dh').hide();
						$('.cl_mpk').hide();
						$('#proj').parent().parent().hide();
					break;					
					case "Zlecenia":
						$('#lab_zlec').text("Komisja nr:");
						// $('#zlec').show();
						$('#mpk').hide();
						$('#komisja').show();
						$('.cl_dh').show();
						$('.cl_mpk').hide();
						if ($('#proj option:selected').hasClass('cl_mpk'))
							$('#proj').val(null);
					break;					
					default:
						$('#lab_zlec').text("Rodzaj prac:");
						// $('#zlec').hide();
						$('#komisja').hide();
						$('#mpk').show();
						$('.cl_dh').hide();
						$('.cl_mpk').show();
						if ($('#proj option:selected').hasClass('cl_dh'))
							$('#proj').val(null);
					break;					
				}
			}

			function set_pnu(zlec, callback) {
				// console.log("set_pnu");
				if (zlec == "") { if (callback) callback(); return; }
				var wzor = /^([0-9]{1,3})([A-Z]{0,4})[\/]?([0-9]{1,3})?[\/]?([0-9]{1,3})?$/;
				var zlec_t = zlec.match(wzor);
				// console.log(zlec_t);
				if (zlec_t) {
					if (zlec_t[2] == "") zlec_t[2] = "Z";
					if (zlec_t[2] == "RY" && zlec_t[1] == 4 && zlec_t[3] == 2) zlec_t[1] += "w2"
					$('#pnu').val(zlec_t[2]+"_"+zlec_t[1]);
					pnu_change();
					$('#pnu_etap').val(zlec_t[3]);
					pnu_etap_change();
					$('#pnu_zad').val(zlec_t[4]);
					$('#proj').val(null);
				} else {
					console.log(zlec);
					console.log(zlec_t);
				}
				if (callback) callback();
			}
			
			function set_komisja(id, callback) {
				console.log("set_komisja");
				// console.log(id);
				// console.log(projekty[id]);
				zlec = projekty[id].zlec;
				var t2_par_id = id;
				while (zlec == "" && t2_par_id > 3) {
					// console.log(t2_par_id);
					zlec = projekty[t2_par_id].zlec;
					t2_par_id = projekty[t2_par_id].par_id;
				}
				console.log(">"+zlec+"<");
				// var wzor = /^([0-9]{1,3})([A-Z]{0,4})[\/]?([0-9]{1,3})?[\/]?([0-9]{1,3})?$/;
				if (zlec == "") { 
					$('#komisja').val(null);
				} else {
					// $('#zlec').val(zlec);
					$('#komisja').val($('#komisja option:contains("'+zlec+'")').val());
				}
				if (callback) callback();
			}
			
			if (_zad_id){
				var _zadanie = <?php if ($zadania) echo json_encode($zadania[0]); else echo 'null';?>;
				console.log("_zadanie");
				console.log(_zadanie);
				// console.log(_zadanie.zlecenie);
				// console.log(_zadanie.par_id);
				$('#zad').val(_zadanie.nazwa);
				$('#akt').val(_zadanie.aktywny);
				if (_zadanie.par_id) {
					if (_zadanie.typ != "PNU" && _zadanie.typ != "Zlecenia"){
						$('#mpk').val(_zadanie.par_id);
					}
					$('#proj').val(_zadanie.par_id);
					proj_change(_zadanie.par_id);
					// set_par(_zadanie.par_id);
				} else {
					$('#typ').val(_zadanie.typ);
					if (_zadanie.typ == "PNU"){
						set_pnu(_zadanie.zlecenie)
						// $('#proj').val(null);
					}
				}
				$('#opis').val(_zadanie.opis);
				$('#forma_zlec').val(_zadanie.forma);
				$('#koment').val(_zadanie.komentarz);
				$('#dzial_zlec').val(_zadanie.dzial_zlec);
				
				$('#rbh').val(_zadanie.rbh);
				if (_zadanie.termin != null){
					var temp_date = new Date();
					temp_date.setTime(_zadanie.termin);
					$('#termin').datepicker('setDate',temp_date);
				}
/*			
				$('#dzial_wyk >> input').each(function(){
					if (_zadanie.dzial_wyk.indexOf("'"+$(this).val()+"'") != -1){
						$(this).attr('checked',true);
						$('[class^="cl_d_'+$(this).val().replace("-","_")+'"]').show();
						// $('.cl_d_'+$(this).val().replace("-","_")).show();
					}
				});
*/
				var prac2 = _zadanie.prac_wykon.split("'").join('').split(',');
				for (var p in prac2) {
					if (prac2[p] == '') {continue;}
					// console.log('prac2', p, 'id=', prac2[p]);
//					console.log(prac2);
					// console.log(users_by_id[prac2[p]]);
					if (!users_by_id[prac2[p]]) {continue;}
					var dzial = users_by_id[prac2[p]].dzial;
					$('#user_'+prac2[p]+' > input').attr('checked',true);
					$('#dzial_wyk [value='+dzial+']').attr('checked',true);
					$('[class^="cl_d_'+dzial.replace("-","_")+'"]').show();
					// console.log(prac2[p], users_by_id[prac2[p]].dzial);
				}
				$('#gotowe').text("Edytuj");
				$('#del').button().click(function(){
					if (confirm('Czy napewno chcesz skasować zadanie ?')){
						send(true);
					}
				});
			} else {
				$('#del').hide();
				$('#zad').val(new_name);
				$('#dzial_zlec').val(4);
				
				if (par_pnu) {
					console.log("par_pnu");
					set_pnu(par_pnu);
				} else if (par_id) {
					console.log("par_id");
					console.log(par_id);
					$('#proj').val(par_id);
					proj_change(par_id);
				}
				$('#dzial >> input').each(function(){
					$(this).attr('checked',true);
				});
			}

			clear_err();
			$('#gotowe').button().click(function(){send(false);});
			$('#close').button().click(function(){window.close();});

			//wyliczenie zaraportowanych godzin
			var act_users = {};
			var sum_wykon = 0;
			for (var w in wykon){
				if (!act_users[wykon[w].user_id]) {
					act_users[wykon[w].user_id] = {sum:0, zad:{}};
					for (var u in users) {
						if (users[u].id == wykon[w].user_id)
							act_users[wykon[w].user_id].user = users[u].nazwa;
					}
				}
				sum_wykon += wykon[w].czas/1;
				act_users[wykon[w].user_id].sum += wykon[w].czas/1;
				act_users[wykon[w].user_id].zad[wykon[w].nazwa] = wykon[w].czas/1;
			}
			// console.log(sum_wykon);
			for (var a in act_users){
				$("#user_"+a+" .wykon").text(": " + act_users[a].sum + "h")
			}
//TODO: jezeli pracownik zaraportowal godziny to nie mozna odznaczyc dzialu
// $('[class^="cl_d_'+$(this).val().replace("-","_")+'"] .wykon').each(function(){

			// console.log("act_users");
			// console.log(act_users);
			
			function send(del){
				if (del) {
					var obj = {};
					if (_zad_id)
						obj.usun = _zad_id;	
	// console.log(obj);
		// return;
					$.ajax({
						url: 'zadanie.php?callback=?',
						dataType: 'json',
						type: 'POST',
						data: obj,
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
				obj.co = $('#zad').val();
				obj.opis = $('#opis').val();
				obj.forma = $('#forma_zlec').val();
				obj.dzial_zlec = $('#dzial_zlec').val();
				obj.koment = $('#koment').val();
				obj.typ = $('#typ').val();
				obj.proj = $('#proj').val();
				if (obj.typ == "PNU") {
					var si_pi = $('#pnu').val().split("_");
					if (si_pi[0] == "Z") obj.zlec = si_pi[1];
					else if (si_pi[0] == "RY" && si_pi[1] == "4w2") obj.zlec = "4"+si_pi[0];
					else obj.zlec = si_pi[1]+si_pi[0];
					// obj.zlec = $('#pnu').val();
					if ($('#pnu_zad').val().trim() != "") obj.zlec += '/' + $('#pnu_etap').val().trim() + '/' + $('#pnu_zad').val().trim();
				} else if (obj.typ == "MPK") {
				} else if (obj.typ == "Zlecenia") {
					if (!$('#komisja').val()) {
						alert('Proszę wybrać nr komisji');
						$('#komisja').css("background-color",'red');
					}
					console.log($('#komisja').val());
					obj.zlec = projekty[$('#komisja').val()].zlec;
					if ($('#proj').val() != "null") {
						obj.proj = $('#proj').val()
					} else {
						obj.proj = $('#komisja').val();
					}
				} else {
					if ($('#proj').val() != "null") {
						obj.zlec = projekty[$('#mpk').val()].zlec;
						// obj.zlec = projekty[$('#proj').val()].zlec;
					} else {
						obj.proj = $('#mpk').val();
						obj.zlec = projekty[$('#mpk').val()].zlec;
					}
				}
				obj.dzial_wyk = '';
				obj.rbh = $('#rbh').val()/1;
				if ($('#termin').val())
					obj.termin = $('#termin').datepicker('getDate').getTime();
				else 
					obj.termin = 'null';
				
				$('#dzial_wyk >> input:checked').each(function(){
					if (obj.dzial_wyk != '')
						obj.dzial_wyk += ",";
//					obj.dzial += $(this).val();
					obj.dzial_wyk += "'"+$(this).val()+"'";
//					console.log($(this).attr('title'));
				});
				obj.prac_wykon = '';
				$('#prac >> input:checked:visible').each(function(){
					if ($(this).val() == 'null')
						return;
					if (obj.prac_wykon != '')
						obj.prac_wykon += ",";
					obj.prac_wykon += "'"+$(this).val()+"'";
				})
				
				// console.log(obj.prac_wykon);
				// return;
				
				if (obj.zlec == ''){
					if (obj.typ == "PNU"){
						alert('Proszę wybrać Projekt, etap i zadanie');
						$('#pnu').css("background-color",'red');
						$('#pnu_etap').css("background-color",'red');
						$('#pnu_zad').css("background-color",'red');
					} else if (obj.typ == "MPK"){
						alert('Proszę wybrać konto MPK');
						$('#mpk').css("background-color",'red');
					} else {
						alert('Proszę wybrać nr komisji');
						$('#komisja').css("background-color",'red');
					}
					return;
				}

				if (obj.co == ''){
					$('#zad').css("background-color",'red');
					alert('Proszę wpisać nazwę');
					return;
				}

				if (obj.opis == ""){
					$('#opis').css("background-color",'red');
					alert('Proszę opisać zadanie');
					return;
				}

				if (obj.dzial_wyk == ''){
					$('#dzial_wyk').css("background-color",'red');
					alert('Proszę wybrać przynajmniej jeden dział');
					return;
				}

				if (!obj.dzial_zlec || obj.dzial_zlec == '' || obj.dzial_zlec == 'null'){
					$('#dzial_zlec').css("background-color",'red');
					alert('Proszę wybrać dział');
					return;
				}

				// if (obj.kto == ''){
					// $('#prac').css("background-color",'red');
					// alert('Proszę wybrać przynajmniej jednego pracownika');
					// return;
				// }

				obj.dodaj = 0;	
				if (_zad_id)
					obj.dodaj = _zad_id;	
// console.log(obj);
// return;
				$.ajax({
					url: 'zadanie.php?callback=?',
					dataType: 'json',
					type: 'POST',
					data: obj,
					timeout: 2000
				}).success(function(obj){
					if (obj && obj[0]=="OK"){
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
		});
	</script>
</html>
