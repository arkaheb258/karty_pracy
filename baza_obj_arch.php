<?php
	$times = array(microtime(true));
	header('Content-type: application/json');
	header('Cache-Control: no-cache, must-revalidate');
	session_start();
	if ( !isset( $_SESSION["myusername"] ) )
		exit('[]');
	require_once ('conf.php');
//	error_reporting(0);
	$conn = new_polacz_z_baza();
	$projekty = array();
	$zadania = array();
	// $refs = array();

  function find_children_id($par_id, $arr){
    $ret = array();
    foreach ($arr as &$value) {
        if ($value->par_id == $par_id)
          $ret[] = $value->id;
    }
    return $ret;
  }
  
  //Recursive php function
  function category_tree($par_id, &$parent){
    global $projekty;
    global $zadania;
    foreach (find_children_id($par_id, $zadania) as $id) {
      $parent->leafs[$id] = $zadania[$id];
      unset($zadania[$id]);
    }
    foreach (find_children_id($par_id, $projekty) as $id) {
      $parent->children[$id] = $projekty[$id];
      unset($projekty[$id]);
      category_tree($id, $parent->children[$id]);
      if (!count($parent->children[$id]->children) && !count($parent->children[$id]->leafs))
        unset($parent->children[$id]);
    }
  }

  $where = " WHERE deleted = 0";
	$query = "SELECT * FROM kart_pr_projekty".$where." ORDER BY lvl ASC, nazwa ASC;";
  // echo $query;
	$result = $conn->query($query);
  if ($result) {
		while($row = $result->fetch_object()){
      $row->children = array();
      $row->leafs = array();
      $projekty[$row->id] = $row;
		}
	}
  
  $select = "*";
  $where = " WHERE deleted = 0";
  $where .= " AND aktywny = 1";
  if (isset($_REQUEST["user_id"])){
    $where .= " AND prac_wykon like concat(\"%'\",".$_REQUEST["user_id"].",\"'%\")";
    $select = "id, par_id, nazwa, opis, json";
  }
	$query = "SELECT ".$select." FROM kart_pr_zadania".$where." ORDER BY nazwa ASC;";
	$result = $conn->query($query);
  if ($result) {
		while($row = $result->fetch_object()){
      $zadania[$row->id] = $row;
		}
	}
  if (isset($_REQUEST["debug"])) {
    // echo $query;
    // var_dump($zadania);
    // exit;
  }
  
  
  $obj = new stdClass();
  $obj->children = array();
  
  category_tree(null, $obj);
  echo 'var projekty_obj = '.json_encode($obj->children).';';
  // var_dump($obj);
	$conn->close();
?>
