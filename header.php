<?php
	error_reporting(1);
	session_start();
	if ( !isset( $_SESSION["myusername"] ) ){
		header("location:login.php?url=".$_SERVER["REQUEST_URI"]);
		exit;
	} else {
		if ($_SESSION['timeout'] + 15 * 60 < time()) {
			header("location:logout.php?session_timeout&url=".$_SERVER["REQUEST_URI"]);
			exit;
		} else {
			$_SESSION['timeout'] = time();
		};
	}﻿;

	function head($title) {
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
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
			textarea{
				width : 75%;
			}
			td {
				vertical-align: top;
			}
			.ui-menu { width: 150px; }
			
			.ui-widget-content {
				background: none;			
			}
		</style>'
		.'<title>'.$title.'</title>'
	.'</head>';
	}
	function logged_as(){
		echo '<div style="float:right;font-size: 0.8em;margin-right: 1em;">Zalogowano jako: '.$_SESSION["myuser"]["nazwa"].'</div><br/>';
	}
?>

