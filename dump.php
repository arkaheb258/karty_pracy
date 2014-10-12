<?php
	require_once ('conf.php');
	$file = 'file.sql';
	$table = 'kart_pr_prace';
	$where = '--where="TRUE';
	if (isset($_REQUEST['timestamp']))
		$where .= " AND timestamp >= '".$_REQUEST['timestamp']."'";
	$where .= ' ORDER BY timestamp ASC';
	if (isset($_REQUEST['limit']))
		$where .= ' LIMIT '.$_REQUEST['limit'];
	if (isset($_REQUEST['table']))
		$table = $_REQUEST['table'];
	$where .= '"';
//	$where = "\"rok is null\"";
	exit("..\..\mysql\bin\mysqldump.exe --user=$login --password=$pass $db $table --no-create-info --compact --replace --skip-tz-utc $where > $file");
	echo file_get_contents($file);
	unlink($file);
?>
