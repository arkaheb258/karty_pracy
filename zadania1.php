<?php 
////http://luban.danse.us/jazzclub/javascripts/jquery/jsTree/reference/_examples/5_others.html
	include 'header.php'; 

	if ( $_SESSION["myuser"]["kart_perm"]  < 1) {
		exit("Brak uprawnień");
	}

	require_once ('conf.php');
	$conn = polacz_sql();

	if (isset($_REQUEST["usun"]) || isset($_REQUEST["dodaj"]) || isset($_REQUEST["dodaj_mpk"]) || isset($_REQUEST["label"]) || isset($_REQUEST["rename"]) || isset($_REQUEST["active"]) || isset($_REQUEST["template"])){
		if (isset($_REQUEST["callback"])){
			$callback = trim($_REQUEST['callback']);
			echo $callback .'(';
		}
		if (isset($_REQUEST["jsoncallback"])){
			$callback = trim($_REQUEST['jsoncallback']);
			echo $callback .'(';
		}

		if (isset($_REQUEST["dodaj"])){
			$par_id = $_REQUEST["par_id" ];
			$lvl = $_REQUEST["lvl" ];
			$nazwa = $_REQUEST["nazwa" ];
			$opis = "";
			if (isset($_REQUEST["opis"])){ $zlec = $_REQUEST["opis"];}
			$zlec = "";
			if (isset($_REQUEST["zlec"])){ $zlec = $_REQUEST["zlec"];}
			$query = "INSERT INTO wwwkop.dbo.kart_pr_projekty (par_id, lvl, nazwa,opis, zlec) VALUES ('$par_id', '$lvl', '$nazwa', '$opis', '$zlec');SELECT SCOPE_IDENTITY() as newId;";
			$result = sqlsrv_query($conn, $query);
			if ($result && sqlsrv_rows_affected($result) > 0) {
				sqlsrv_next_result($result); 
				$row = get_row_sql($result);
				echo json_encode(array('OK',$row["newId"],'INSERT','Projekt dodany'));
			} else {
				echo $query;
			}
		}
		
		if (isset($_REQUEST["usun"])){
			$id = $_REQUEST["usun"];
			// $query = "DELETE FROM kart_pr_projekty WHERE id=$id;";
			$query = "UPDATE wwwkop.dbo.kart_pr_projekty SET deleted = 1 WHERE id=$id;";
			$result = sqlsrv_query($conn, $query);
			if ($result && sqlsrv_rows_affected($result) > 0) {
				echo json_encode(array('OK',$id,'DELETE','Zadanie usunięte'));
			} else
				echo $query;
		}

		if (isset($_REQUEST["rename"])){
			$id = $_REQUEST["rename"];
			$nazwa = $_REQUEST["nazwa"];
			preg_match("/^([0-9]{2}H[0-9]{5,9})$/", $nazwa, $output_array);
			if (count($output_array))
				$query = "UPDATE wwwkop.dbo.kart_pr_projekty SET nazwa = '$nazwa', zlec = '$nazwa' WHERE id=$id;";
			else
				$query = "UPDATE wwwkop.dbo.kart_pr_projekty SET nazwa = '$nazwa' WHERE id=$id;";
			
			$result = sqlsrv_query($conn, $query);
			if ($result && sqlsrv_rows_affected($result) > 0) {
				echo json_encode(array('OK',$id,'RENAME','Nazwa zmieniona'));
			} else
				echo $query;
		}

		if (isset($_REQUEST["label"])){
			$id = $_REQUEST["label"];
			$nazwa = $_REQUEST["nazwa"];
			$query = "UPDATE wwwkop.dbo.kart_pr_projekty SET opis = '$nazwa' WHERE id=$id;";
			$result = sqlsrv_query($conn, $query);
			if ($result && sqlsrv_rows_affected($result) > 0) {
				echo json_encode(array('OK',$id,'LABEL','Opis zmieniony'));
			} else
				echo $query;
		}
		
		if (isset($_REQUEST["active"])){
			$id = $_REQUEST["active"];
			$query = "UPDATE wwwkop.dbo.kart_pr_projekty SET aktywny = not aktywny WHERE id=$id;";
			$result = sqlsrv_query($conn, $query);
			if ($result && sqlsrv_rows_affected($result) > 0) {
				echo json_encode(array('OK',$id,'ACTIVE','Aktywowano'));
			} else
				echo $query;
			// $query = "INSERT INTO logi(kto, co) VALUES (".$_SESSION["myuser"]["id"].", 'zmiana statusu folderu (id=$id)')";
			// $result = sqlsrv_query($conn, $query);
		}

		if (isset($_REQUEST["template"])){
			$id = $_REQUEST["template"];
			$komisja = $_REQUEST["nazwa"];
			$nazwa = $komisja;
			$template_typ = "komisja";
			if (isset($_REQUEST["template_typ"])){
				$template_typ = $_REQUEST["template_typ"];
				$komisja = $_REQUEST["komisja"];
				$nazwa = $_REQUEST["nazwa"];
			}
			
			if ($id == 411 || $template_typ == 'grot') //GROT
				$query = "EXECUTE [wwwkop].[dbo].[add_template_grot] ".$_SESSION["myuser"]["id"].",'$komisja','$nazwa', $id;";
			else if ($id == 412 || $template_typ == 'rybnik') //RYBNIK
				$query = "EXECUTE [wwwkop].[dbo].[add_template_rybnik] ".$_SESSION["myuser"]["id"].",'$komisja','$nazwa', $id;";
			else
				$query = "EXECUTE [wwwkop].[dbo].[add_template] ".$_SESSION["myuser"]["id"].",'$komisja', $id;";
			$result = sqlsrv_query($conn, $query);
			if ($result) {
				echo json_encode(array('OK',$id,'TEMPLATE','Dodano szablon'));
			} else
				echo $query;
		}
	
		if (isset($_REQUEST["callback"]) || isset($_REQUEST["jsoncallback"]))
			echo ')';
		exit;
	}

	// $query = "SELECT * FROM kart_pr_zadania WHERE deleted = 0;";
	
	$query = "SELECT z . * , w.wykon
		FROM  wwwkop.dbo.kart_pr_zadania z
		LEFT JOIN (
			SELECT zadanie AS zad_id, SUM( czas ) /60 AS wykon
			FROM  wwwkop.dbo.kart_pr_prace 
			WHERE zadanie IS NOT NULL 
			GROUP BY zadanie
		)w ON z.id = w.zad_id
		WHERE z.deleted = 0
		order by z.timestamp;";
	// echo $query;
		
	$zadania = array();
	$result = sqlsrv_query($conn, $query);
	if ($result)
		while($row = get_row_sql($result)) {
		// while($row = $result->fetch_assoc()){
			$zadania[] = $row;
		}

	$query = "SELECT * FROM wwwkop.dbo.kart_pr_projekty WHERE deleted = 0 and par_id is not null ORDER BY lvl, nazwa;";

	$projekty_lvl = array();
	$projekty = array();
	$result = sqlsrv_query($conn, $query);
	if ($result)
		while($row = get_row_sql($result)) {
			$projekty[$row["id"]] = $row;
			$projekty_lvl[] = $row;
		}
	if (isset($_REQUEST["test"])) {
		// var_dump($projekty);
		// exit;
	}
	head('Zadania');
?>
	<head>
		<link rel="stylesheet" href="./jstree/style.min.css" />
		<style>
			.jstree-default .jstree-filex {
				background: url("jstree/32px.png") -132px -68px no-repeat;
			}
			.jstree-default .jstree-fileok {
				background: url("jstree/32px.png") -228px -68px no-repeat;
			}
			.jstree-default .jstree-folderx {
				background: url("jstree/32px.png") -164px -68px no-repeat;
			}
			.jstree-default .jstree-folderok {
				background: url("jstree/32px.png") -196px -68px no-repeat;
			}
		</style>
	</head>
	<body style="background-image:url(images/logo_km100.png);background-repeat: no-repeat; ">
<?php logged_as();?>
	
			<table style="margin:auto; width: 50%;">
					<tr>
						<td>Filtr: <select id="akt" name="akt" style="width: 10em;">
								<option value="">Wszystkie</option>
								<option value="1">Aktywne</option>
								<option value="0">Nieaktywne</option>
								<option value="3">Zakończone</option>
							</select><br/>
						</td>
					</tr>
					<tr>
						<td>Ukryj projekty bez zadań<select id="empty" name="empty" style="width: 5em;">
								<option value="0">Nie</option>
								<option value="1">Tak</option>
							</select><br/>
						</td>
					</tr>
					<tr>
						<td>Dział: <select id="dzial" name="dzial" style="width: 10em;">
								<option value="">Wszystkie</option>
								<option value="TR-1">TR-1</option>
								<option value="TR-2">TR-2</option>
								<option value="TR-3">TR-2-5</option>
								<option value="TP">TP</option>
								<option value="TRIN">TRIN</option>
								<option value="RTR">TR-R</option>
							</select><br/>
						</td>
					</tr>
					<tr>
						<td>
							Szukaj: <input type="text" id="lista_pnu_q" value="" class="input" />
						</td>
					</tr>
					<tr>
						<td id="lista_pnu"></td>
					</tr>
				</table>
	</body>
	<script type="text/javascript" src="jquery.min.js"></script>
	<script type="text/javascript" src="jquery-ui.min.js"></script>
	<script type="text/javascript" src="users.php"></script>
	<script type="text/javascript" src="http://192.168.30.12:8888/pnu.js"></script>
	<script src="./jstree/jstree.js"></script>
	<script>
		_user_id = <?php echo $_SESSION["myuser"]["id"];?>;
		_prac_id = '<?php if (isset($_REQUEST["id"])) echo $_REQUEST["id"]; else echo 'null';?>';
		_kat_id = <?php if (isset($_REQUEST["id_k"])) echo $_REQUEST["id_k"]; else echo 'null';?>;
		_dzia_id = <?php if (isset($_REQUEST["id_d"])) echo $_REQUEST["id_d"]; else echo 'null';?>;
		_copy_id = '<?php if (isset($_REQUEST["copy_id"])) echo $_REQUEST["copy_id"]; else echo 'null';?>';
		_zadania = <?php echo json_encode($zadania);?>;
		_projekty = <?php echo json_encode($projekty_lvl);?>;
		_projekty2 = <?php echo json_encode($projekty);?>;
		var sum_user_id = <?php if (isset($_REQUEST["user_id"])) echo $_REQUEST["user_id"]; else echo 'null';?>;

		if (typeof console === "undefined")
			console = {log:function(){}};
		else if (typeof console.log === "undefined")
			console.log = function(){};
			
		function load_zad(){
			// console.log("load_zad");
			// console.log($('#akt').val());

			if ($('#empty').val() == 1) {
				$(".jstree-leaf").hide();
				$(".jstree-file").parent().parent().show();
			} else 
				$(".jstree-leaf").show();
			
			var _akt = $('#akt').val();
			for (var zi in _zadania){
				var zad = _zadania[zi];
				var _dzial = $('#dzial').val();
				var _dzial_sek = zad.dzial_wyk.replace(/'/g, "");
				// if (zad.sekcja_wyk) _dzial_sek += "-"+zad.sekcja_wyk;
				var _typ = $('#typ').val();
				if (((zad.aktywny+"").indexOf(_akt)==-1) || (_dzial_sek.indexOf(_dzial)==-1)) {
					$('#'+zad.id).hide();
				} else {
					$('#'+zad.id).show();
				}
			}
			
			// console.log(_akt);
			for (var pi in _projekty){
				var fold = _projekty[pi];
				// if (!_akt)
					// console.log(fold.lvl > 1);
				if ((fold.aktywny+"").indexOf(_akt)==-1 && fold.lvl > 1) {
					$('#p'+fold.id).hide();
				} else {
					$('#p'+fold.id).show();
				}
			}
			// console.log(_akt);
			for (var si in sekcje) if (pnu[sekcje[si]]) {
				for (var pi in pnu[sekcje[si]]) {
					var p = pnu[sekcje[si]][pi];
					for (var ei in p.etap) {
						var e = p.etap[ei];
						if (e){
							for (var zgi in e) {
								// console.log(e[zgi].status);
								if(_akt != '' && (e[zgi].status+"").indexOf(_akt)==-1) {
									$('#pnu' + si + "_" + pi + "_" + ei + "_" + e[zgi].nr).hide();
								} else {
									$('#pnu' + si + "_" + pi + "_" + ei + "_" + e[zgi].nr).show();
								}
							}
						}
					}
					if(_akt != '' && (p.status+"").indexOf(_akt)==-1) {
						$('#pnu' + si + "_" + pi).hide();
					} else {
						$('#pnu' + si + "_" + pi).show();
					}
				}
			}
		}
		
		$('#dzial').change(load_zad);
		$('#typ').change(load_zad);
		$('#akt').change(load_zad);
		$('#empty').change(load_zad);

		$('#empty').parent().hide();

		function search_par(data, pr){
			// console.log(pr.lvl + ": id " + pr.par_id);
			// console.log(pr);
			for (var i in data) {
				if (data[i].id == "p"+pr.par_id)
					return data[i].children;
				var p = search_par(data[i].children, pr);
				if (p) return p;
			}
			return null;
		}

		function search_par_pnu(data, zlec){
			var wzor = /^([0-9]{1,3}w?1?2?)([A-Z]{0,4})[\/]?([0-9]{1,3})?[\/]?([0-9]{1,3})?$/;
			var zlec_t = zlec.match(wzor);
			// console.log(zlec);
			// console.log(zlec_t);
			if(!zlec_t) return null;
			var nr_sekcji = 0;
			if (zlec_t[2] == "W") nr_sekcji = 2;
			else if (zlec_t[2] == "RY") nr_sekcji = 1;
			else if (zlec_t[2] == "DHTP") nr_sekcji = 3;
			// console.log(zlec_t);
			for (var pi in data[nr_sekcji].children){
				var p = data[nr_sekcji].children[pi];
				if (p.text == "Projekt nr "+zlec_t[1]+zlec_t[2]) {
					if (zlec_t[4]) {
						for (var z2i in p.children){
							var z2 = p.children[z2i];
							if (z2.text == "Zadanie "+zlec_t[4]) {
								return z2.children;
							}
						}
					} else {
						return p.children;
					}
					for (var ei in data[nr_sekcji].children[pi].children){
						var e = data[nr_sekcji].children[pi].children[ei];
						if (e.text == "Wariant "+zlec_t[3] 
						|| e.text == "Etap "+zlec_t[3]) {
							if (zlec_t[4]) {
								for (var z2i in e.children){
									var z2 = e.children[z2i];
									if (z2.text == "Zadanie "+zlec_t[4]) {
										return z2.children;
									}
								}
							} else {	//nigdy nie wystapi
								return p.children;
							}
						}
					}
				}
			}
			// for (var pi in data[0].children[nr_sekcji].children){
			return null;
		}
		
		//wypelnienie PNU
		var sekcje = ['Z','RY','W','DHTP'];
		var sekcja_opis = ['TR-1, TR-2 i TRIN','TR-R','TR-2-5','DHTP'];
		var pnu_t = [];
		var ikona_fok = "jstree-folderok";
		var ikona_fnakt = "jstree-folderx";
		var temp_icon = null;
		for (var si in sekcje) if (pnu[sekcje[si]]) {
			var sekcja = [];
			// var w24ry = null;
			for (var pi in pnu[sekcje[si]]) {
				var p = pnu[sekcje[si]][pi];
				var etap = [];
				// if (p.nr == "4w2RY") p.nr = "4RY";
				for (var ei in p.etap) {
					var e = p.etap[ei];
					var zad_gl = [];
					// if (w24ry) etap = w24ry;
					if (e){
						for (var zgi in e) {
							// console.log("pnu" + si + "_" + pi + "_" + ei + "_" + e[zgi].nr);
							var temp_style = null;
							// sygnalizacja przekroczenia planu pnu
							if (e[zgi].rbh_plan_czyn > e[zgi].rbh_pnu_plan) {
								console.log(e[zgi]);
								temp_style = 'color: orange; font-weight: bold;';
							}
							// sygnalizacja nadwykonania planu
							if (e[zgi].rbh_wyk > e[zgi].rbh_plan_czyn){
								console.log(e[zgi]);
								temp_style = 'color: red; font-weight: bold;';
							}
							temp_icon = null;
							if (e[zgi].status == 3 || p.status == 3)
								temp_icon = ikona_fok;
							zad_gl.push({text:"Zadanie "+e[zgi].nr, data: {pnu: p.nr+"/"+ei+"/"+zgi}, children: [], a_attr: {title: e[zgi].nazwa, style: temp_style}, icon : temp_icon, id:"pnu" + si + "_" + pi + "_" + ei + "_" + e[zgi].nr });
						}
						// if (Object.keys(p.etap).length > 1) {
						if (p.nr == "4RY") {
							etap.push({text:"Wariant "+ei, children: zad_gl, icon : temp_icon });
							// console.log(e);
						} else {
							if (Object.keys(p.etap).length > 2) {
								etap.push({text:"Etap "+ei, children: zad_gl, icon : temp_icon });
							} else {
								etap = zad_gl;
							}
						}
					}
				}
				temp_icon = null;
				if (p.status == 3)
					temp_icon = ikona_fok;
				// if (!w24ry || p.nr != "4RY") {
					sekcja.push({text:"Projekt nr "+p.nr, children: etap, a_attr: {title: p.opis}, icon : temp_icon, id:"pnu" + si + "_" + pi });
				// } 
				// if (p.nr == "4RY") {
					// w24ry = etap;
				// }
			}
			pnu_t.push({text: sekcja_opis[si], children: sekcja });
		}
		var data = [
			{ text: "PNU", id: "p0", children: pnu_t }, 
			{ text: "MPK", id: "p1", children: [] }, 
			{ text: "DH", id: "p2", children: [] }
		];

		//zsumowanie godzin z czynnosci na foldery
		for (var pri in _projekty2) {
			_projekty2[pri].wykon = 0;	//godziny wykonane wszystkich czynnosci w folerze
			_projekty2[pri].agr = 0;	//godziny zagregowane poziom wyżej
		}
		for (var zi in _zadania){
			var zad = _zadania[zi];
			if (zad.wykon && zad.typ == "DH") {
				_projekty2[zad.par_id].wykon += zad.wykon/1;
			}
		}
		for (var i=0; i<5; i+=1){
			var count = 0;
			for (var pri in _projekty2) {
				if (_projekty2[pri].wykon > _projekty2[pri].agr) {
					var prp = _projekty2[_projekty2[pri].par_id];
					if (prp.lvl > 1) {
						var diff = _projekty2[pri].wykon - _projekty2[pri].agr;
						prp.wykon += diff;
						_projekty2[pri].agr += diff;
						count += 1;
					}
				}
			}
			if (count == 0) break;
			// console.log(count);
		}

		//dopisanie projektow do drzewka
		for (var pri in _projekty) {
			var ikona_fnakt = "jstree-folderx";
			var pr = _projekty[pri];
			// console.log(pr);
			var new_node = {text:pr.nazwa, id:"p"+pr.id, children:[], a_attr:{title:pr.opis}};
			if (_projekty2[pr.id].wykon) new_node.text += " (suma: " + Math.floor(_projekty2[pr.id].wykon) + " rbh)";
			if (pr.par_id && pr.par_id != 0) {
				var p = search_par(data, pr);
				if (pr.par_id > 2 && _projekty2[pr.par_id]) {
					if (_projekty2[pr.par_id].aktywny && _projekty2[pr.par_id].aktywny != 1){
						_projekty2[pr.id].aktywny = _projekty2[pr.par_id].aktywny;
						pr.aktywny = _projekty2[pr.par_id].aktywny;
					}
				}
				if (pr.aktywny != 1)
					new_node.icon = ikona_fnakt;
				if (p) {
					p.push(new_node);
				} else {
					console.log(pr);
				}
			} else {
				var p = search_par_pnu(data[0].children, pr.zlec);
				new_node.data = {pnu: pr.zlec};
				if (p) {
					p.push(new_node);
				} else {
					console.log(pr);
				}
			}
		}

		//dopisanie zadań / czynności do drzewka
		for (var zi in _zadania) {
			var z = _zadania[zi];
			if (z.rbh > 0){
				if (z.wykon > 0){
					var wykon = (100*z.wykon/z.rbh).toFixed(2);
					z.wykon_proc = wykon;
					// z.nazwa += " " + z.wykon_proc + " %";
					// console.log(z);
				}
				// z.nazwa
			}
			var dodany = false;
			var ikona_akt = "jstree-file";
			var ikona_ok = "jstree-fileok";
			var ikona_nakt = "jstree-filex";
			var ikona_fnakt = "jstree-folderx";
			var new_node = {text:z.nazwa, id:z.id, icon:ikona_akt, a_attr:{title:z.opis, href:"zadanie1.php?id="+z.id}};
			if (z.par_id && _projekty2[z.par_id]) {
				if (_projekty2[z.par_id].aktywny && _projekty2[z.par_id].aktywny != 1){
					z.aktywny = _projekty2[z.par_id].aktywny;
				}
			}
			if (z.aktywny == 3)
				new_node.icon = ikona_ok;
			if (z.aktywny == 0)
				new_node.icon = ikona_nakt;
			new_node.text += " (suma: " + Math.floor(z.wykon) + " rbh";
			if (z.wykon_proc > 100){
				new_node.text += ", >100%)";
				new_node.a_attr.style = 'color: red; font-weight: bold;';
			} else if (z.wykon_proc > 75){
				new_node.text += ", " + z.wykon_proc + "%)";
				new_node.a_attr.style = 'color: orange; font-weight: bold;';
			} else if (z.wykon_proc > 0){
				new_node.text += ", " + z.wykon_proc + "%)";
			} else 
				new_node.text += ")";
			if (z.typ == "PNU"){
				// if (z.zlecenie[0] == "4" && z.zlecenie[1] == "R") {
					// console.log(z.zlecenie);
				// }
				var p = search_par_pnu(data[0].children, z.zlecenie);
				if (p) {
					var p2 = search_par(p, z);
					if (p2) { p = p2; }
					new_node.data = {pnu: z.zlecenie};
					p.push(new_node);
					dodany = true;
				}
			} else if (z.typ == "MPK"){
				if (z.par_id) {
					var p = search_par(data[1].children, z);
					if (p) {
						p.push(new_node);
						dodany = true;
					}
				}
			} else {
//TODO: docelowo ujednolicic z MPK
				if (z.par_id) {
					var p = search_par(data[2].children, z);
					if (p) {
						p.push(new_node);
						dodany = true;
					}
				} else {
					data[2].children.push(new_node);
					dodany = true;
				}
			}
			if (!dodany) {
				console.log(z);
				data.push(new_node);
			}
		}
		var plugs = [ "search", "contextmenu"];
		if (!_prac_id || _prac_id == "null")
			plugs.push("state");
		
		$('#lista_pnu').jstree({
			'core' : { 'data' : data, "check_callback" : true}, 
			"plugins" : plugs, //[ "search", "contextmenu", "state" ],
/*				"rules" : {
				// clickable : [ "root2", "folder" ],
				// deletable : [ "root2", "folder" ],
				renameable : "all",
				// creatable : [ "folder" ],
				// draggable : [ "folder" ],
				// dragrules : [ "folder * folder", "folder inside root", "tree-drop * folder" ],
				// drag_button : "left",
				droppable : [ "tree-drop" ]
			},
*/				"contextmenu" : { "items": customMenu}
		}).bind("select_node.jstree", function (e, data) {
			// var href = data.node.a_attr.href;
			// if (href != "#")
				// window.open(href);
			// console.log(href);
			
		}).bind('open_node.jstree', function(e, data) {
			// invoked after jstree has loaded
			load_zad();
		}).on("changed.jstree", function (e, data) {
			if(data.selected.length) { 
				// console.log(data.node);
				// var href = data.node.a_attr.href;
			}
		}).on('loaded.jstree', function() {
			if (_prac_id){
				// console.log(_prac_id);
				$('#lista_pnu').jstree(true).select_node(_prac_id, true);
			}
		});;
		
		
		var to = false;
		$('#lista_pnu_q').keyup(function () {
			if (to) { clearTimeout(to); }
			to = setTimeout(function () {
				var v = $('#lista_pnu_q').val();
				// console.log(v);
				$('#lista_pnu').jstree(true).search(v);
			}, 750);
		});

		function customMenu($node) {
			// The default set of all items
			var tree = $("#lista_pnu").jstree(true);
			var items = {
				open: {
					"separator_before": false,
					"separator_after": false,
					"label": "Edytuj",
					"action": function (obj) { 
						// console.log("Edytuj");
						var href = $node.a_attr.href;
						window.open(href);
					}
				},
				activate: {
					"separator_before": false,
					"separator_after": false,
					"label": "Aktywuj",
					"action": function (obj) { 
						var obj_2_send = {active: $node.id.substring(1)};
						send(obj_2_send, function(obj) {
							if (obj[0] == "OK") {
								console.log(obj[1]);
								window.location.reload()
							} else {
								console.log("blad aktywacji");
							}
						});
						// console.log("Edytuj");
						// window.open(href);
					}
				},
				renameItem: {
					"separator_before": false,
					"separator_after": false,
					"label": "Zmień nazwę",
		//							"action": default_rename_node(obj)
					"action": function (obj) { 
						// tree.edit($node);
						var text = prompt("Nowa nazwa:", $node.text.split(' (suma')[0]);
						if (text && text.length && text != $node.text){
							var obj_2_send = {rename: $node.id.substring(1), nazwa: text};
							send(obj_2_send, function(obj) {
								if (obj[0] == "OK") {
									console.log(obj[1]);
									tree.rename_node ($node, text);
								} else {
									console.log("blad zmiany nazwy");
								}
							});
						}
					}
				},                         
				labelItem: {
					"separator_before": false,
					"separator_after": false,
					"label": "Zmień opis",
					"action": function (obj) { 
						var text = prompt("Nowy opis:", $node.a_attr.title);
						if (text && text.length && text != $node.text){
							var obj_2_send = {label: $node.id.substring(1), nazwa: text};
							send(obj_2_send, function(obj) {
								if (obj[0] == "OK") {
									console.log(obj[1]);
									window.location.reload()
								} else {
									console.log("blad zmiany opisu");
								}
							});
						}
					}
				},                         
				create : {
					"separator_before"  : false,
					"separator_after"   : true,
					"label"             : "Dodaj",
					"action"            : false,
					"submenu" :{
						"create_file" : {
							"seperator_before" : false,
							"seperator_after" : false,
							"label" : "Czynność",
							action : function (obj) {
								var text = prompt("Nazwa nowej czynności:", "nowa czynność");
								if (text && text.length){
									//nr zadania z rodzicow dla PNU, nr MPK lub nr komisji
									if ($node.id[0] == "p" && $node.id[1] != "n")
										window.open("zadanie1.php?text="+text+"&par="+$node.id.substring(1));
									else if ($node.data && $node.data.pnu)
										window.open("zadanie1.php?text="+text+"&pnu="+$node.data.pnu);
									else 
										window.open("zadanie1.php?text="+text);							
								}
							}
						},
						"create_mpk" : {
							"seperator_before" : false,
							"seperator_after" : false,
							"label" : "MPK",
							action : function (obj) {
								var zlec = prompt("Nr nowego MPK:", "01-xxxx");
								var text = "";
								if (zlec && zlec.length){
									text = prompt("Opis nowego MPK:", "");
								}
//TODO: sprawdzenie formatu przy pomocy reg_exp i zgloszenie bledu
								if (text && text.length){
									console.log(text);
									obj_2_send = {dodaj:true, par_id: 1, nazwa: zlec, opis: text, zlec: zlec};
									send(obj_2_send, function(obj) {
										console.log(obj);
										if (obj[0] == "OK") {
											console.log(obj[1]);
											$node = tree.create_node($node, {"text":zlec}, "last", function(_node){
												tree.deselect_all(true);
												tree.select_node(_node);
											});
										} else {
											console.log("blad dodawania");
										}
									});
								}
							}
						},
						"create_folder" : {
							"seperator_before" : false,
							"seperator_after" : false,
							"label" : "Folder",
							action : function (obj) {
								// console.log($node.parents);
								var text = "nowy folder";
								if ($node.parents[0] == "p2") {
									text = "nr komisji";
								}
								if ( $node.id == "p411" || $node.id == "p412") 
									text = "37H900????";
								text = prompt("Nazwa folderu:", text);
								if (text && text.length){
									var obj_2_send = {};
									if ($node.id[0] == "p") {
										// console.log({zlec: });
										obj_2_send = {dodaj:true, par_id: $node.id.substring(1), lvl: $node.parents.length, nazwa: text, zlec: _projekty2[$node.id.substring(1)].zlec};
									} else {
										if ($node.parents[2] == "p0" 
										|| $node.parents[3] == "p0") {
											obj_2_send = {dodaj:true, par_id: 0, lvl: 1, nazwa: text, zlec: $node.data.pnu};
										} else {
											obj_2_send = {dodaj:true, par_id: $node.parent.substring(1), lvl: 1, nazwa: text, zlec: $node.text};
										}
									}
									if ($node.parents[0] == "p2") {
										console.log($node.parents);
										obj_2_send.zlec = obj_2_send.nazwa;
									}

									console.log(obj_2_send);
									send(obj_2_send, function(obj) {
										// console.log(obj);
										if (obj[0] == "OK") {
											console.log(obj[1]);
											$node = tree.create_node($node, {"text":text, id:"p"+obj[1]}, "last", function(_node){
												tree.deselect_all(true);
												tree.select_node(_node);
											});
										} else {
											console.log("blad dodawania");
										}
									});
								}
								// tree.edit($node);
							}
						},
						"template" : {
							"seperator_before" : false,
							"seperator_after" : false,
							"label" : "Szablon komisji",
							action : function (obj) {
								var text = "01H900????";
								if($node.id == "p411" 
								|| $node.parents[0] == "p411"
								|| $node.parents[0] == "p412"
								|| $node.id == "p412") {
									text = "37H900????";
								}
								console.log($node.text);
								text = prompt("Komisja:", text);
								// alert('Oczekiwanie na szablon...');
								// return;
								if (text && text.length){
									var obj_2_send = {template: $node.id.substring(1), nazwa: text};
									if ($node.parents[0] == "p411"){
										obj_2_send.komisja = $node.text;
										obj_2_send.template_typ = 'grot';
									}
									if ($node.parents[0] == "p412") {
										obj_2_send.komisja = $node.text;
										obj_2_send.template_typ = 'rybnik';
									}
									send(obj_2_send, function(obj) {
										if (obj[0] == "OK") {
											// console.log(obj);
											window.location.reload()
										} else {
											console.log("blad dodania szablonu");
										}
									});
								}
							}
						}
					}
				},		
				deleteItem: { // The "delete" menu item
					"separator_before": false,
					"separator_after": false,
					"label": "Usuń",
					"action": function (obj) { 
						var obj_2_send = {usun: $node.id.substring(1)};
						// console.log(obj_2_send);
						// console.log($node);
						if ($node.children.length > 0) alert("Nie można skasować folderu, który zawiera czynności!");
						else if (confirm('Czy napewno chcesz skasować projekt "' + $node.text + '" ?')){
							send(obj_2_send, function(obj) {
								if (obj[0] == "OK") {
									tree.delete_node($node);
								}
							});
						}
					}
				}
			};

			console.log($node);
			if ($node.id != "p1")
				delete items.create.submenu.create_mpk;
			else {
				delete items.create.submenu.create_file;
				delete items.create.submenu.create_folder;
			}

			if ($node.icon != "jstree-folderx") {
				items.activate.label = "Dezaktywuj";
			}
			
			if ($node.id == "p0" 
			|| $node.parents[0] == "p0"
			|| $node.parents[1] == "p0") {
				delete items.create;
				delete items.deleteItem;
				delete items.renameItem;
				delete items.labelItem;
				delete items.activate;
			}

			if ($node.parents[2] == "p0" 
			|| $node.parents[3] == "p0") {
				if ($node.text.search("Etap ") == 0) {
					delete items.create;
					delete items.deleteItem;
					delete items.renameItem;
					delete items.labelItem;
					delete items.activate;
					// delete items.create.submenu.create_folder;
				}
				if ($node.text.search("Zadanie ") == 0) {
					delete items.renameItem;
					delete items.labelItem;
					delete items.deleteItem;
					delete items.activate;
				}
			}

			if ($node.parents[0] == "#") {
				delete items.deleteItem;
				delete items.renameItem;
				delete items.labelItem;
				delete items.activate;
			}

			if (($node.icon != "jstree-file") && ($node.icon != "jstree-filex") && ($node.icon != "jstree-fileok")) {
				delete items.open;
			} else {
				delete items.renameItem;
				delete items.labelItem;
				delete items.create;
				delete items.deleteItem;
				delete items.activate;
			}
			
			if ($node.id == "p2") {
				delete items.create;
			}

			if (items.create) {
				if ($node.parents[0] != "p2" && $node.parents[0] != "p411" && $node.parents[0] != "p412"){
					delete items.create.submenu.template;
				} else {
					if($node.id == "p54") {
						delete items.create.submenu.template;
					} else if($node.id == "p411" || $node.parents[0] == "p411") {
						items.create.submenu.template.label = "Przenośnik GROT";
					} else if($node.id == "p412" || $node.parents[0] == "p412") {
						items.create.submenu.template.label = "Przenośnik RYBNIK";
					}
					delete items.create.submenu.create_file;
				}
			}

			return items;
		}			

		function send(obj, callback){
			$.ajax({
				url: 'zadania1.php?callback=?',
				dataType: 'json',
				type: 'POST',
				data: obj,
				timeout: 2000
			}).success(function(obj){
				callback(obj);
				if (obj && obj[0]=="OK"){
					// alert(obj[3]);
					// window.location.reload();
				} else
					alert('Błąd skryptu.');
			}).fail( function() {
				alert('Błąd serwera.');
			});
		}
	</script>
</html>
