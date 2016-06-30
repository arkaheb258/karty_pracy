<?php
	$times = array(microtime(true));
	header('Content-type: application/json');
	header('Cache-Control: no-cache, must-revalidate');
	session_start();
	if ( !isset( $_SESSION["myusername"] ) )
		exit('[]');
	require_once ('conf.php');
//	error_reporting(0);

  function find_children_id($par_id, $arr){
    $ret = array();
    foreach ($arr as $key => $value) {
      if (($par_id === 'null') || ($value === 'null')){
        if ($value === $par_id) { 
          $ret[] = $key; 
        }
      } else {
        if ($value == $par_id) { 
          $ret[] = $key; 
        }
      }
    }
    return $ret;
  }

  function find_root($id) {
    global $projekty;
    $ret = array();
    $par = $id;
    $i = 0;
    while ($par != null && $i<10) {
      $i++;
      // echo $i.' '.$par. PHP_EOL;
      array_push($ret, $par);
      $par = $projekty[$par]->par_id;
    }
    return $ret;
  }
  
  //Recursive php function
  function category_tree($par_id, &$parent){
    global $projekty;
    global $zadania;
    global $projekty_par_ids;
    global $zadania_ids;
    $parent->id = $par_id;
    $parent->leafs = array();
    $parent->children = array();
    //dopisanie czynnosci
    foreach (find_children_id($par_id, $zadania_ids) as $id) {
      $parent->leafs[$id] = find_root($zadania[$id]->par_id);
      $zadania[$id]->parents = find_root($zadania[$id]->par_id);
      unset($zadania_ids[$id]);
    }
    //dopisanie projektow
    foreach (find_children_id($par_id, $projekty_par_ids) as $id) {
      $parent->children[$id] = new stdClass();
      unset($projekty_par_ids[$id]);
      //rekurencja
      category_tree($id, $parent->children[$id]);
      if (isset($_REQUEST["user_id"]))
      if (!count($parent->children[$id]->children) && !count($parent->children[$id]->leafs)){
        unset($parent->children[$id]);
        unset($projekty[$id]);
      }
    }
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
  // } else {
  if (true) {
    $projekty = array();
    $zadania = array();
    $projekty_par_ids = array();
    $zadania_ids = array();

    $where = " WHERE deleted = 0";
    // if (isset($_REQUEST["user_id"]))
      // $where .= " AND aktywny = 1";
    $query = "SELECT `id`, `par_id`, `nazwa` as `text`, `opis`, `aktywny`, `nested_folders` as folders, `nested_files` as files FROM kart_pr_projekty".$where." ORDER BY id ASC;";
    // echo $query;
array_push($times,microtime(true));	
    $result = $conn->query($query);
array_push($times,microtime(true));	
    if ($result) {
      while($row = $result->fetch_object()){
        $projekty[$row->id] = $row;
        if ($row->par_id === null) {
          $projekty_par_ids[$row->id] = 'null';
        } else {
          $projekty_par_ids[$row->id] = $row->par_id;
        }
      }
    }

array_push($times,microtime(true));	
// if (false)
// if (isset($_REQUEST["debug"])) 
    foreach ($projekty as $p) {
      $p->parents = find_root($p->par_id);
    }
array_push($times,microtime(true));	
    
    // $select = "*";
    $select = "`id`, `par_id`, `nazwa`, `opis`, `aktywny`, `prac_wykon`, `rbh`, `termin`, `json`, `komentarz`";
    $where = " WHERE deleted = 0";
    if (isset($_REQUEST["user_id"]))
      $where .= " AND aktywny = 1";
    if (isset($_REQUEST["zad_id"]))
      $where .= " AND id = ".$_REQUEST["zad_id"];
    if (isset($_REQUEST["user_id"])){
      $where .= " AND prac_wykon like concat(\"%'\",".$_REQUEST["user_id"].",\"'%\")";
      $select = "id, par_id, nazwa, opis, termin, json";
    }
    $query = "SELECT ".$select." FROM kart_pr_zadania".$where." ORDER BY id ASC;";
array_push($times,microtime(true));	
    $result = $conn->query($query);
array_push($times,microtime(true));	
    if ($result) {
      while($row = $result->fetch_object()){
        $row->json = json_decode($row->json);
        $zadania[$row->id] = $row;
        $zadania_ids[$row->id] = $row->par_id;
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
    echo ',"o_projekty": '.json_encode($projekty);
    echo ',"o_zadania": '.json_encode($zadania);
    echo ',"projekty_tree": '.json_encode($obj_tree).'}';
    if (isset($_REQUEST["callback"]) || isset($_REQUEST["jsoncallback"]))
      echo ')';
array_push($times,microtime(true));	
  }

  
  if (isset($_REQUEST["debug"])) {
    var_dump($times);
    // var_dump(find_root("988"));
    // var_dump($projekty);
    // var_dump($obj_tree);
    // var_dump($zadania);
    // var_dump($projekty_par_ids);
    // var_dump($new_query);
  }
?>
