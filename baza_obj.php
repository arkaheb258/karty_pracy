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
	$projekty_ids = array();
	$zadania_ids = array();

  $where = " WHERE deleted = 0";
	$query = "SELECT * FROM kart_pr_projekty".$where." ORDER BY id ASC;";
  // echo $query;
	$result = $conn->query($query);
  if ($result) {
		while($row = $result->fetch_object()){
      $projekty[$row->id] = $row;
      if ($row->par_id === null) {
        $projekty_ids[$row->id] = 'null';
      } else {
        $projekty_ids[$row->id] = $row->par_id;
      }
		}
	}
  
  $select = "*";
  $where = " WHERE deleted = 0";
  $where .= " AND aktywny = 1";
  if (isset($_REQUEST["user_id"])){
    $where .= " AND prac_wykon like concat(\"%'\",".$_REQUEST["user_id"].",\"'%\")";
    $select = "id, par_id, nazwa, opis, json";
  }
	$query = "SELECT ".$select." FROM kart_pr_zadania".$where." ORDER BY id ASC;";
	$result = $conn->query($query);
  if ($result) {
		while($row = $result->fetch_object()){
      $zadania[$row->id] = $row;
      $zadania_ids[$row->id] = $row->par_id;
		}
	}

  function find_children_id($par_id, $arr){
    $ret = array();
    foreach ($arr as $key => $value) {
      if ($value == $par_id) { $ret[] = $key; }
    }
    // var_dump($ret);
    return $ret;
  }

  //Recursive php function
  function category_tree($par_id, &$parent){
    global $projekty_ids;
    global $zadania_ids;
    $parent->leafs = array();
    $parent->children = array();
    foreach (find_children_id($par_id, $zadania_ids) as $id) {
      $parent->leafs[$id] = $zadania_ids[$id];
      unset($zadania_ids[$id]);
    }
    foreach (find_children_id($par_id, $projekty_ids) as $id) {
      $parent->children[$id] = new stdClass();
      unset($projekty_ids[$id]);
      category_tree($id, $parent->children[$id]);
      if (isset($_REQUEST["user_id"]))
      if (!count($parent->children[$id]->children) && !count($parent->children[$id]->leafs))
        unset($parent->children[$id]);
    }
  }

  $obj_tree = new stdClass();
  
  category_tree('null', $obj_tree);
  echo 'var projekty = '.json_encode($projekty).';';
  echo 'var zadania = '.json_encode($zadania).';';
  echo 'var projekty_tree = '.json_encode($obj_tree).';';
	$conn->close();
  
  if (isset($_REQUEST["debug"])) {
    // var_dump($obj_tree);
    // var_dump($obj_tree);
    // echo $query;
    // var_dump($zadania_ids);
    // var_dump($projekty_ids);
    exit;
  }
?>
