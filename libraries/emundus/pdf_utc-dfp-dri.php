<?php

function application_form_pdf($user_id, $fnum = null, $output = true) {
	jimport( 'joomla.html.parameter' );

	$rowid = JRequest::getVar('rowid', null, 'GET', 'none',0);

	$db = JFactory::getDBO();

	require_once(JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
	require_once(JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
	require_once(JPATH_COMPONENT.DS.'helpers'.DS.'menu.php');
	require_once(JPATH_COMPONENT.DS.'models'.DS.'users.php');
	include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');

	/*$m_users = new EmundusModelUsers;
	$user_profile = $m_users->getCurrentUserProfile($user_id);
	die(var_dump($m_users));*/

	$menu = new EmundusHelperMenu;

	$current_user =  JFactory::getUser();
	$user =  JFactory::getUser($user_id); 

	$application = new EmundusModelApplication;

	// Element Fabrik ID list to display in PDF
	$elts = array(2111, 2112, 2114, 2117, 2133, 2119, 2120, 2121, 2123, 2124, 2125, 2126, 2127);
	
	$options = array('show_list_label' => 0, 'show_form_label' => 0, 'show_group_label' => 0, 'rowid' => $rowid, 'profile_id' => '13');
	//$forms = $application->getFormsPDF($user_id);
	$forms = $application->getFormsPDFElts($user_id, $elts, $options);

	// Set title for PDF
	$title = @EmundusHelperList::getElementsDetailsByID(2113); 
	$where = 'user='.$user_id;
	$where .= $options['rowid']>0?' AND id='.$options['rowid']:'';
	$query = 'SELECT '.$title[0]->element_name. ' FROM '.$title[0]->tab_name.' WHERE '.$where;
	$db->setQuery($query);
	$intitule_poste = $db->loadResult();
//die(var_dump($query));
	$htmldata = '';
	$htmldata .= '<h1>'.$intitule_poste.'</h1>';
	$htmldata .= $forms;
	// --------------------- //
	set_time_limit(0);
	require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'config'.DS.'lang'.DS.'eng.php');
	require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'tcpdf.php');
	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Decision Publique');
	$pdf->SetTitle('Application Form');
	
	
	$query = 'SELECT campaign_id FROM #__emundus_campaign_candidature WHERE applicant_id='.$user_id.' ORDER BY date_submitted DESC';
	$db->setQuery($query);
	$campaign_id = $db->loadResult();

	$query = 'SELECT id FROM #__usergroups WHERE title="Registered"';
	$db->setQuery($query);
	$registered = $db->loadResult();

	// Users informations
	$query = 'SELECT u.id AS user_id, c.firstname, c.lastname, a.filename AS avatar, p.label AS cb_profile, c.profile, esc.year AS cb_schoolyear, esc.training, u.id, u.registerDate, u.email, epd.gender, epd.nationality, epd.birth_date, ed.user, ecc.date_submitted
				FROM #__users AS u
				LEFT JOIN #__emundus_users AS c ON u.id = c.user_id
				LEFT JOIN #__emundus_uploads AS a ON a.user_id=u.id AND a.attachment_id = '.EMUNDUS_PHOTO_AID.'
				LEFT JOIN #__emundus_setup_profiles AS p ON p.id = c.profile
				LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id = '.$campaign_id.'  
				LEFT JOIN #__emundus_personal_detail AS epd ON epd.user = u.id
				LEFT JOIN #__emundus_declaration AS ed ON ed.user = u.id
				LEFT JOIN #__emundus_campaign_candidature AS ecc ON (ecc.applicant_id = u.id AND ecc.campaign_id = '.$campaign_id.')
				WHERE u.id='.$user_id. '
				ORDER BY esc.id DESC';
	$db->setQuery($query);
	$item = $db->loadObject();
//die(str_replace("#_", "jos", $query));
	//get logo
	$app 		= JFactory::getApplication();
	$template 	= $app->getTemplate(true);
	$params     = $template->params;
	$image   	= $params->get('logo')->custom->image; 
	$logo 		= preg_match_all("/'([^']*)'/", $image, $matches);
	$logo 		= !empty($matches[1][1]) ? JPATH_ROOT.DS.$matches[1][1] : preg_match_all('/"([^"]*)"/', $image, $matches);
	$logo 		= !empty($logo) ? JPATH_ROOT.DS.$matches[1][1] : "";
	
	
	//get title
	$config = JFactory::getConfig(); 
	//$title = $config->getValue('config.sitename');
	$title = 'Emplois étudiants Sorbonne Universités';
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

	$html_table = '';
	$nb_groupes = 0;
	$nb_lignes = 0;
	$current_group_repeated = 0;


	if (!empty($htmldata)) {
		$pdf->startTransaction();
		$start_y = $pdf->GetY();
		$start_page = $pdf->getPage();
		//$pdf->Bookmark($itemt->label, 0);
		$pdf->writeHTMLCell(0,'','',$start_y,$htmldata,'B', 1);
		// $pdf->Ln(1);
		$end_page = $pdf->getPage();
		/*if ($end_page != $start_page) {
			$pdf = $pdf->rollbackTransaction();
			$pdf->addPage(); 
			$pdf->Bookmark($itemt->label, 0);
			$pdf->writeHTMLCell(0,'','',$pdf->GetY(),$htmldata,'B', 1);
			// $pdf->Ln(1);
		}*/
		//$htmldata = '';
	}
	// die('ok');
	@chdir('tmp');
	if(!file_exists(EMUNDUS_PATH_ABS.$item->id)) {	
		if (!mkdir(EMUNDUS_PATH_ABS.$item->id, 0777, true) || !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$item->id.DS.'index.html')) 
			return JError::raiseWarning(500, 'Unable to create user file');
	}

	if($output){
		if($user->applicant != 1){
			//$output?'FI':'F'
			$name = 'application_form_'.date('Y-m-d_H-i-s').'_'.rand(1000,9999).'.pdf';
			$pdf->Output(EMUNDUS_PATH_ABS.$item->user_id.DS.$name, 'FI');
			$query = 'INSERT INTO #__emundus_uploads (user_id, attachment_id, filename, description, can_be_deleted, can_be_viewed, campaign_id) 
						VALUES ('.$item->user_id.',(
										   SELECT id 
										   FROM #__emundus_setup_attachments 
										   WHERE lbl = "_application_form"),"'.$name.'","'.$item->training.' '.date('Y-m-d H:i:s').'",0,0,'.$campaign_id.')';
			$db->setQuery($query);
			$db->query();
		}else{
			$pdf->Output(EMUNDUS_PATH_ABS.$item->user_id.DS.'application.pdf', 'FI');
		}
	}else{
		$pdf->Output(EMUNDUS_PATH_ABS.@$item->user_id.DS.$fnum.'_application.pdf', 'F');
	}

}
?>