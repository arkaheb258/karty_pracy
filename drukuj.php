<?php
	session_start();
    ob_start();
	$dzial = $_SESSION["myuser"]["dzial"];
	$dzial = substr($dzial,0,2);
	$nr = $_SESSION["myuser"]["nr"];
	if ($nr == 913 || $nr == 914 )
		$dzial = "TP";
		$date = "Zabrze ".(new DateTime())->format('d.m.Y');
		$od = (new DateTime())->setTimestamp($_REQUEST['od'])->format('d.m.Y');
		$do = (new DateTime())->setTimestamp($_REQUEST['do'])->format('d.m.Y');
?>
<style type="text/Css">
.back{
	left:0px;
	top:0px;
	width: 525px;
	height: 375px;
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
</style>
<page style="font-family: freeserif; font-size: 16px">
	<img class="all back" src="urlop.png" alt="" />
	<div class="all nr"><?php echo $_SESSION["myuser"]["nr"]; ?></div>
	<div class="all dzial"><?php echo $dzial; ?></div>
	<div class="all imie"><?php echo $_SESSION["myuser"]["nazwa"]; ?></div>
	<div class="all typ">taryfowego</div>
	<div class="all od"><?php echo $od;?></div>
	<div class="all do"><?php echo $do;?></div>
	<div class="all kiedy"><?php echo $date;?></div>
	<div class="all opis"><?php echo $_REQUEST['opis'];?></div>
</page>
<?php
echo 
//		exit;
		$content = ob_get_clean();
		require_once('../scripts/pdf/html2pdf.class.php');
		try
		{
			$html2pdf = new HTML2PDF('L', 'A4', 'en');
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