<?php
	session_start();
    ob_start();
	if (isset($_REQUEST["id"]) && $_REQUEST["id"] != "null"){
		require_once ('conf.php');
		$conn = polacz_sql();
		$query = "SELECT * FROM wwwkop.dbo.users u WHERE u.id = ".$_REQUEST["id"];
		$result = sqlsrv_query($conn, $query);
		if ($result) {
			$row1 = sqlsrv_fetch_object ( $result );
			$row = array();
			foreach ($row1 as $key => $value) {
				if ($key == "timestamp_diff_h")
					$row[$key] = round($value/3600);
				else
					$row[$key] = $value;
			}
			if ($row) {
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
	width: 800px;
}
.all{ 
	position:absolute; 
}
.imie{
	left:130px;
	top:110px;
}
.nr{
	left:170px;
	top:160px;
}
.miasto{
	left:170px;
	top:210px;
}
.od{
	left:240px;
	top:620px;
}
.do{
	left:460px;
	top:620px;
}
.kiedy{
	left:510px;
	top:110px;
}
.opis{
	left:50px;
	top:340px;
}
</style>
<page style="font-family: freeserif; font-size: 16px">
	<img class="all back" src="wniosek.jpg" alt="" />
	<div class="all nr"><?php echo $dzial ." " .$nr; ?></div>
	<div class="all imie"><?php echo $nazwa; ?></div>
	<div class="all miasto"><?php echo "Zabrze"; ?></div>
	<div class="all od"><?php echo $od;?></div>
	<div class="all do"><?php echo $do;?></div>
	<div class="all kiedy"><?php echo $date;?></div>
</page>
<script>
window.print();
</script>
<?php
echo 
exit;
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