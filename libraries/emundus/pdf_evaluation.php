<?php
function age($naiss) {
		@list($annee, $mois, $jour) = preg_split('[-.]', $naiss);
		$today['mois'] = date('n');
		$today['jour'] = date('j');
		$today['annee'] = date('Y');
		$annees = $today['annee'] - $annee;
		if ($today['mois'] <= $mois) {
			if ($mois == $today['mois']) {
			if ($jour > $today['jour'])
				$annees--;
		}
		else
			$annees--;
		}
		return $annees;
}

function get_mime_type($filename, $mimePath = '../etc') {
	   $fileext = substr(strrchr($filename, '.'), 1);
	   if (empty($fileext)) return (false);
	   $regex = "/^([\w\+\-\.\/]+)\s+(\w+\s)*($fileext\s)/i";
	   $lines = file("$mimePath/mime.types");
	   foreach($lines as $line) {
	      if (substr($line, 0, 1) == '#') continue; // skip comments
	      $line = rtrim($line) . " ";
	      if (!preg_match($regex, $line, $matches)) continue; // no match to the extension
	      return ($matches[1]);
	   }
	   return (false); // no match at all
} 

function pdf_evaluation($user_id, $fnum = null, $output = true) {
	jimport( 'joomla.html.parameter' );
	set_time_limit(0);
	require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'config'.DS.'lang'.DS.'eng.php');
	require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'tcpdf.php');

	require_once(JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
	//require_once(JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
	//require_once(JPATH_COMPONENT.DS.'helpers'.DS.'menu.php');
	require_once(JPATH_COMPONENT.DS.'models'.DS.'users.php');
	include_once(JPATH_COMPONENT.DS.'models'.DS.'application.php');
	include_once(JPATH_COMPONENT.DS.'models'.DS.'evaluation.php');
	//include_once(JPATH_COMPONENT.DS.'models'.DS.'files.php');
	include_once(JPATH_COMPONENT.DS.'models'.DS.'profile.php');

	$m_profile 		= new EmundusModelProfile;
	$m_users 		= new EmundusModelUsers;
	//$menu 			= new EmundusHelperMenu;
	$application 	= new EmundusModelApplication;
	$evaluation 	= new EmundusModelEvaluation;
	//$files 			= new EmundusModelFiles;

	$db 			= JFactory::getDBO();
	$app 			= JFactory::getApplication();
	$config 		= JFactory::getConfig();
	$eMConfig 		= JComponentHelper::getParams('com_emundus');
	$current_user 	= JFactory::getUser();
	$user 			= JFactory::getUser($user_id);
	$fnum 			= empty($fnum)?$user->fnum:$fnum;

	$export_pdf = $eMConfig->get('export_pdf'); 

	$user_profile = $m_users->getCurrentUserProfile($user_id);
	
	$infos = $m_profile->getFnumDetails($fnum);
	$campaign_id = $infos['campaign_id'];

	// Get form HTML
	$htmldata = '';

	$forms = $application->getFormsPDF($user_id, $fnum);

	// Create PDF object
	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Decision Publique');
	$pdf->SetTitle('Application Form');

	// Users informations
	$query = 'SELECT u.id AS user_id, c.firstname, c.lastname, a.filename AS avatar, p.label AS cb_profile, c.profile, esc.label, esc.year AS cb_schoolyear, esc.training, u.id, u.registerDate, u.email, epd.gender, epd.nationality, epd.birth_date, ed.user, ecc.date_submitted
				FROM #__emundus_campaign_candidature AS ecc
				LEFT JOIN #__users AS u ON u.id=ecc.applicant_id 
				LEFT JOIN #__emundus_users AS c ON u.id = c.user_id
				LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id = '.$campaign_id.'  
				LEFT JOIN #__emundus_uploads AS a ON a.user_id=u.id AND a.attachment_id = '.EMUNDUS_PHOTO_AID.' AND a.fnum like '.$db->Quote($fnum).' 
				LEFT JOIN #__emundus_setup_profiles AS p ON p.id = esc.profile_id
				LEFT JOIN #__emundus_personal_detail AS epd ON epd.user = u.id AND epd.fnum like '.$db->Quote($fnum).' 
				LEFT JOIN #__emundus_declaration AS ed ON ed.user = u.id AND ed.fnum like '.$db->Quote($fnum).' 
				WHERE ecc.fnum like '.$db->Quote($fnum).' 
				ORDER BY esc.id DESC';
	$db->setQuery($query);
	$item = $db->loadObject();
//die(str_replace("#_", "jos", $query));

	//get logo
	$template 	= $app->getTemplate(true);
	$params     = $template->params;
	$image   	= $params->get('logo')->custom->image; 
	$logo 		= preg_match_all("/'([^']*)'/", $image, $matches);
	$logo 		= !empty($matches[1][1]) ? JPATH_ROOT.DS.$matches[1][1] : preg_match_all('/"([^"]*)"/', $image, $matches);
	$logo 		= !empty($logo) ? JPATH_ROOT.DS.$matches[1][1] : "";
	
	//get title
	$title = $config->get('sitename');
	$pdf->SetHeaderData($logo, PDF_HEADER_LOGO_WIDTH, $title, PDF_HEADER_STRING);
	unset($logo);
	unset($title);
	
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	$pdf->SetFont('helvetica', '', 8);
	$pdf->AddPage();
	$dimensions = $pdf->getPageDimensions();
	
/*** Applicant   ***/   
$htmldata .= 
'<style>
.card  { background-color: #cecece; border: none; display:block; line-height:80%;}
.name  { display: block; font-size: 12pt; margin: 0 0 0 20px; padding:0; display:block; line-height:110%;}
.maidename  { display: block; font-size: 20pt; margin: 0 0 0 20px; padding:0; }
.nationality { display: block; margin: 0 0 0 20px;  padding:0;}
.sent { display: block; font-family: monospace; margin: 0 0 0 10px; padding:0; text-align:right;}
.birthday { display: block; margin: 0 0 0 20px; padding:0;}
</style>
<div class="card">
<table>
<tr>
';
if (file_exists(EMUNDUS_PATH_REL.$item->user_id.'/tn_'.$item->avatar) && !empty($item->avatar) && exif_imagetype(EMUNDUS_PATH_REL.$item->user_id.'/tn_'.$item->avatar))
	$htmldata .= '<td width="20%"><img src="'.EMUNDUS_PATH_REL.$item->user_id.'/tn_'.$item->avatar.'" width="100" align="left" /></td>';
elseif (file_exists(EMUNDUS_PATH_REL.$item->user_id.'/'.$item->avatar) && !empty($item->avatar) && exif_imagetype(EMUNDUS_PATH_REL.$item->user_id.'/'.$item->avatar))
	$htmldata .= '<td width="20%"><img src="'.EMUNDUS_PATH_REL.$item->user_id.'/'.$item->avatar.'" width="100" align="left" /></td>';
$htmldata .= '
<td>

  <div class="name"><strong>'.$item->firstname.' '.strtoupper($item->lastname).'</strong>, '.$item->label.' ('.$item->cb_schoolyear.')</div>';

if(isset($item->maiden_name))
	$htmldata .= '<div class="maidename">'.JText::_('MAIDEN_NAME').' : '.$item->maiden_name.'</div>';
$date_submitted = !empty($item->date_submitted)?strftime("%d/%m/%Y %H:%M", strtotime($item->date_submitted)):JText::_('NOT_SENT');
$htmldata .= '
  <div class="nationality">'.JText::_('ID_CANDIDAT').' : '.$item->user_id.'</div>
  <div class="nationality">'.JText::_('FNUM').' : '.$fnum.'</div>
  <div class="birthday">'.JText::_('EMAIL').' : '.$item->email.'</div>
  <div class="sent">'.JText::_('APPLICATION_SENT_ON').' : '.$date_submitted.'</div>
  <div class="sent">'.JText::_('DOCUMENT_PRINTED_ON').' : '.strftime("%d/%m/%Y 	%H:%M", time()).'</div>
</td>
</tr>
</table>
</div>';
/**  END APPLICANT   ****/

	// get evaluations
	//$users = $evaluation->getUsers($fnum);

	$data = @EmundusHelperFiles::getEvaluation('html', $fnum);

	foreach ($data as $fnums => $evals) {
		foreach ($evals as $user => $html) {
			$htmldata .= $html;
		}
	}

	if (!empty($htmldata)) {
		$pdf->startTransaction();
		$start_y = $pdf->GetY();
		$start_page = $pdf->getPage();
		$pdf->Bookmark($item->lastname.' '.$item->firstname, 0);
		$pdf->writeHTMLCell(0,'','',$start_y, $htmldata,'B', 1);

		//$htmldata = '';
	}

	@chdir('tmp');
	if($output){
		$pdf->Output(EMUNDUS_PATH_ABS.$item->user_id.DS.'evaluations.pdf', 'FI');
	}else{
		$pdf->Output(EMUNDUS_PATH_ABS.$item->user_id.DS.'evaluations.pdf', 'F');
	}

}
?>