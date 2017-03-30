<?php
	$times = array(microtime(true));
	header('Content-type: application/json');
	header('Cache-Control: no-cache, must-revalidate');
	session_start();
	if ( !isset( $_SESSION["myusername"] ) )
		exit('[]');
	require_once ('conf.php');
//	error_reporting(-1);

  function find_root($id) {
    global $projekty;
    $ret = array();
    $par = $id;
    $i = 0;
    while ($par != null && $i<10) {
      $i++;
      // echo $i.' '.$par. PHP_EOL;
      array_push($ret, $par);
      if (isset($projekty[$par]))
        $par = $projekty[$par]->par_id;
      else 
        $par = null;
    }
    return $ret;
  }
  
  //Recursive php function
  function category_tree($par_id, &$parent){
    global $projekty;
    global $zadania;
    global $projekty_leafs;
    global $projekty_childrens;
    $parent->id = $par_id;
    $parent->leafs = array();
    $parent->children = array();
    //dopisanie czynnosci
    if (isset($projekty_leafs[$par_id]))
    foreach ($projekty_leafs[$par_id] as $id) {
      $roots = find_root($zadania[$id]->par_id);
      // $parent->leafs[$id] = find_root($zadania[$id]->par_id);
      // $zadania[$id]->parents = find_root($zadania[$id]->par_id);
      // $parent->leafs[$id] = array_slice($roots,0,count($roots));
      // $zadania[$id]->parents = array_slice($roots,0,count($roots));
      if (count($roots)) {
        $parent->leafs[$id] = $roots;
        $zadania[$id]->parents = $roots;
      }
    }
    //dopisanie projektow
    if (isset($projekty_childrens[$par_id]))
    foreach ($projekty_childrens[$par_id] as $id) {
      $parent->children[$id] = new stdClass();
      //rekurencja !!!
      category_tree($id, $parent->children[$id]);
    }
    // var_dump($par_id);
    // var_dump($projekty_childrens[$par_id]);
    // var_dump($projekty_leafs[$par_id]);
    // exit;
  }

	$conn = new_polacz_z_baza();

  if (isset($_REQUEST["sql"])) {
    $query = $_REQUEST["sql"];
    $result = $conn->query($query);
    if (gettype($result) != 'boolean') {
      $rows = array();
      while($row = $result->fetch_object()){
        $rows[] = $row;
      }
      echo '{'.json_encode($rows).'}';
    } else {
      echo $result;
    }
    $conn->close();
    exit;
  }
  if (true) {
    $projekty = array();
    $zadania = array();
    $projekty_leafs = array();
    $projekty_childrens = array();

    $where = " WHERE deleted = 0";
    $query = "SELECT `id`, `par_id`, `nazwa` as `text`, `opis`, `aktywny`, `aktywny` as `status`, `nested_folders` as folders, `nested_files` as files FROM kart_pr_projekty".$where." ORDER BY id ASC;";
    $result = $conn->query($query);
    if ($result) {
      while($row = $result->fetch_object()){
        $projekty[$row->id] = $row;
        if (!isset($projekty_childrens[$row->par_id]))
          $projekty_childrens[$row->par_id] = array();
        $projekty_childrens[$row->par_id][] = $row->id;
      }
    }

    foreach ($projekty as $p) {
      $p->parents = find_root($p->par_id);
    }

    $select = "`id`, `par_id`, `nazwa`, `opis`, `aktywny`, `aktywny` as `status`, `prac_wykon`, `rbh`, `termin`, `json`, `komentarz`";
    $where = " WHERE deleted = 0";
    if (isset($_REQUEST["user_id"]))
      $where .= " AND aktywny = 1";
    if (isset($_REQUEST["zad_id"]))
      $where .= " AND id = ".$_REQUEST["zad_id"];
    if (isset($_REQUEST["user_id"])){
      $where .= " AND prac_wykon like concat(\"%'\",".$_REQUEST["user_id"].",\"'%\")";
      $select = "id, par_id, nazwa, opis, termin, json";
    }
    $query = "SELECT z.*, SUM(l.used)/60 as sum_rbh, SUM(l.max)/60 as sum_max_rbh FROM (SELECT ".$select." FROM kart_pr_zadania".$where.") z LEFT JOIN kart_pr_limity l on (z.id = l.zad_id) GROUP BY z.id;";
    // $query = "SELECT ".$select." FROM kart_pr_zadania".$where." ORDER BY id ASC;";
    $result = $conn->query($query);
    if ($result) {
      while($row = $result->fetch_object()){
        $row->json = json_decode($row->json);
        $zadania[$row->id] = $row;
        if (!isset($projekty_leafs[$row->par_id]))
          $projekty_leafs[$row->par_id] = array();
        $projekty_leafs[$row->par_id][] = $row->id;
      }
    }
    // exit($query);

    foreach ($zadania as $z) {
      $z->parents = find_root($z->par_id);
      if (!count($z->parents)) unset($z->parents);
    }
    
    // var_dump($projekty_childrens); exit;
    if (isset($projekty_leafs[null])){
      $projekty_leafs['null'] = $projekty_leafs[null];
      unset($projekty_leafs[null]);
    }
    if (isset($projekty_childrens[null])){
      $projekty_childrens['null'] = $projekty_childrens[null];
      unset($projekty_childrens[null]);
    }

    $stat = array();

    if (isset($_REQUEST["stat"])){
      $od = $_REQUEST["_od"];
      $do = $_REQUEST["_do"];
      $query = "
        SELECT s.*, u.nazwa, u.dzial FROM
        (SELECT user_id as id, zadanie as zad, SUM(czas)/60 as ile FROM `kart_pr_prace` 
          WHERE data > $od
          AND data < $do
          GROUP BY zadanie, user_id) s
        LEFT JOIN users u ON (u.id = s.id)
        ORDER BY `ile`  DESC";
      $result = $conn->query($query);
      if ($result) {
        while($row = $result->fetch_object()){
          $row->ile = floatval ($row->ile);
          $stat[] = $row;
        }
      }
    }

    
    $obj_tree = new stdClass();
array_push($times,microtime(true));	
    category_tree('null', $obj_tree);
array_push($times,microtime(true));	
    
    $conn->close();
    if (isset($_REQUEST["callback"])){
      $callback = trim($_REQUEST['callback']);
      echo $callback .'(';
    }
    if (isset($_REQUEST["jsoncallback"])){
      $callback = trim($_REQUEST['jsoncallback']);
      echo $callback .'(';
    }
    $date = new DateTime();
    echo '{"serv_epoch": '.$date->getTimestamp().'000';
    if (isset($_REQUEST["proj"])){
      echo ',"o_projekty": '.json_encode($projekty);
    }
    echo ',"o_zadania": '.json_encode($zadania);
    if (isset($_REQUEST["tree"])){
      echo ',"projekty_tree": '.json_encode($obj_tree);
    }
    if (isset($_REQUEST["stat"])){
      echo ',"stat": '.json_encode($stat);
    }
    echo '}';
    if (isset($_REQUEST["callback"]) || isset($_REQUEST["jsoncallback"]))
      echo ')';
array_push($times,microtime(true));	
  }

  
  if (isset($_REQUEST["debug"])) {
    var_dump($times);
    // var_dump(find_root("988"));
    // var_dump($projekty);
    // var_dump($obj_tree);
    // var_dump($new_query);
  }
?>
