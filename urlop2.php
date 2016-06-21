<?php
	session_start();
    ob_start();
	if (isset($_REQUEST["id"]) && $_REQUEST["id"] != "null"){
		require_once ('conf.php');
		$conn = polacz_sql();
		// $mysqli = new_polacz_z_baza();
		// $query = "SELECT * FROM users u WHERE u.id = ".$_REQUEST["id"];
		$query = "SELECT * FROM wwwkop.dbo.users u WHERE u.id = ".$_REQUEST["id"];
// echo $query;
		$result = sqlsrv_query($conn, $query);
		// $result = $mysqli->query($query);
		if ($result) {
			// $row = $result->fetch_assoc();
			$row1 = sqlsrv_fetch_object ( $result );
			$row = array();
			foreach ($row1 as $key => $value) {
				if ($key == "timestamp_diff_h")
					$row[$key] = round($value/3600);
				else
					$row[$key] = $value;
				// $row[$key] = $row[$key].'';
			}
			if ($row) {
//				var_dump($row);
				$nr = $row["nr"];
				$nazwa = $row["nazwa"];
				$dzial = $row["dzial"];
				$dzial = substr($dzial,0,2);
			}
		}
	} else {
		$dzial = $_SESSION["myuser"]["dzial"];
		$dzial = substr($dzial,0,2);
		$nr = $_SESSION["myuser"]["nr"];
		$nazwa = $_SESSION["myuser"]["nazwa"];
	}
// echo $query;
// exit;
	if ($nr == 913 || $nr == 914 )
		$dzial = "TP";
	if ($dzial == "RT")
		$dzial = "TR-R";
	$date = "Zabrze ".(new DateTime())->format('d.m.Y');
	$od = (new DateTime())->setTimestamp($_REQUEST['od'])->format('d.m.Y');
	$do = (new DateTime())->setTimestamp($_REQUEST['do'])->format('d.m.Y');
?>
<style type="text/Css">
.back{
	left:0px;
	top:0px;
	width: 525px;
	height: 750px;
}
.all{ 
	position:absolute; 
}
.nr{
	left:400px;
	top:100px;
}
.dzial{
	left:150px;
	top:75px;
}
.imie{
	left:150px;
	top:160px;
}
.typ{
	left:200px;
	top:200px;
}
.od{
	left:130px;
	top:250px;
}
.do{
	left:320px;
	top:250px;
}
.kiedy{
	left:70px;
	top:300px;
}
.opis{
	left:50px;
	top:340px;
}
.nr2{
	left:400px;
	top:475px;
}
.dzial2{
	left:150px;
	top:450px;
}
.imie2{
	left:150px;
	top:535px;
}
.typ2{
	left:200px;
	top:575px;
}
.od2{
	left:130px;
	top:625px;
}
.do2{
	left:320px;
	top:625px;
}
.kiedy2{
	left:70px;
	top:675px;
}
.opis2{
	left:50px;
	top:715px;
}
</style>
<page style="font-family: freeserif; font-size: 16px">
	<img class="all back" src="urlop2.png" alt="" />
	<div class="all nr"><?php echo $nr; ?></div>
	<div class="all dzial"><?php echo $dzial; ?></div>
	<div class="all imie"><?php echo $nazwa; ?></div>
	<div class="all typ"><?php if ($_REQUEST['opis'] != "opieka") { echo "taryfowego"; } else { echo "opieka"; }?></div>
	<div class="all od"><?php echo $od;?></div>
	<div class="all do"><?php echo $do;?></div>
	<div class="all kiedy"><?php echo $date;?></div>
	<div class="all opis"><?php if ($_REQUEST['opis'] != "opieka") { echo $_REQUEST['opis']; } ?></div>
	<div class="all nr2"><?php echo $nr; ?></div>
	<div class="all dzial2"><?php echo $dzial; ?></div>
	<div class="all imie2"><?php echo $nazwa; ?></div>
	<div class="all typ2"><?php if ($_REQUEST['opis'] != "opieka") { echo "taryfowego"; } else { echo "opieka"; }?></div>
	<div class="all od2"><?php echo $od;?></div>
	<div class="all do2"><?php echo $do;?></div>
	<div class="all kiedy2"><?php echo $date;?></div>
	<div class="all opis2"><?php if ($_REQUEST['opis'] != "opieka") { echo $_REQUEST['opis']; } ?></div>
</page>
<?php
echo 
		// exit;
		$content = ob_get_clean();
		require_once('../scripts/pdf/html2pdf.class.php');
		try
		{
			$html2pdf = new HTML2PDF('P', 'A4', 'en');
//			$html2pdf->pdf->SetDisplayMode('real');
			$html2pdf->writeHTML($content, false);
			$pdf_file = $nr.'.pdf';
			if(is_file($pdf_file))
				unlink($pdf_file);
			$html2pdf->Output($pdf_file, 'F');	//, 'F' - zapis do pliku
//			$pdf = $html2pdf->Output('', 'S');
//			$html2pdf->Output('exemple00.pdf');
			header("location:".$pdf_file);			
//			echo $content;
		}
		catch(HTML2PDF_exception $e) {
			echo $e;
			exit;
		}
?>