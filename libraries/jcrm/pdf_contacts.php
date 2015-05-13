<?php
require('fpdf.php');

class PDF extends FPDF
{
//Chargement des données
function LoadData($file)
{
    // Lecture des lignes du fichier
    $lines = file($file);
    $data = array();
    foreach($lines as $line)
        $data[] = explode(';',trim($line));
    return $data;
}

//Tableau color?
function ExportTableau($header,$data)
{
    //Couleurs, aisseur du trait et police grasse
    $this->SetFillColor(150,180,255); //fond des entetes de colonnes
    $this->SetTextColor(0); //couleur du texte des entetes des colonnes
    $this->SetDrawColor(128,0,0); // couleur des bordures
    $this->SetLineWidth(.2); //epaisseur des traits
    $this->SetFont('','B');
    
	//En-te
    $w=array(10,40,40,50,50);
    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],7,$header[$i],1,0,'C',1);
    $this->Ln();
    
	//Restauration des couleurs et de la police
    $this->SetFillColor(240,248,255); //couleur du fond des cases
    $this->SetTextColor(0); //couleur du texte des cases
    $this->SetFont('');
    
	//Données
    $fill=false;
    foreach($data as $row)
    {
        $this->Cell($w[0],6,$row[0],'LR',0,'L',$fill);
        $this->Cell($w[1],6,$row[1],'LR',0,'L',$fill);
        $this->Cell($w[2],6,$row[2],'LR',0,'L',$fill);
        $this->Cell($w[3],6,$row[3],'LR',0,'L',$fill);
		$this->Cell($w[4],6,$row[4],'LR',0,'L',$fill);	 
		$this->Ln();
        $fill=!$fill;
    }
    $this->Cell(array_sum($w),0,'','T');
}
}
function selected($reqids){

$app = JFactory::getApplication();

$query = 'SELECT id,first_name,last_name,email,phone_work FROM #__jcrm_contacts WHERE 1';

$elements = array('id', 'first_name', 'last_name','email','phone_work');

$db	= &JFactory::getDBO();
 $db->setQuery($query);

$fields_cnt = $db->loadRowlist();
//Creation pdf
$pdf=new PDF();
//Titres des colonnes
$header=array('Id', 'First name', 'Last name','Email','Phone number');
$pdf->SetFont('Arial','',5);
$pdf->SetMargins(10,10);
$pdf->AddPage(); 
$pdf->ExportTableau($header,$fields_cnt);
$pdf->Output('jcrm_contacts_'.date("Y-m-d").'.pdf','D');

}
?>