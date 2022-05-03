<?php


function application_form_pdf($user_id, $rowid, $output = true) {
	jimport( 'joomla.html.parameter' );

	//$rowid = JRequest::getVar('rowid', null, 'GET', 'none',0);

	$db = JFactory::getDBO();

	require_once(JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
	require_once(JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
	require_once(JPATH_COMPONENT.DS.'helpers'.DS.'menu.php');
	require_once(JPATH_COMPONENT.DS.'models'.DS.'users.php');
	include_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');

	$eMConfig = JComponentHelper::getParams('com_emundus');
	$fabrik_elements_title = $eMConfig->get('fabrik_elements_title', '2113');
	$fabrik_elements_pdf = $eMConfig->get('fabrik_elements_pdf', '2111, 2112, 2114, 2117, 2133, 2119, 2120, 2121, 2123, 2124, 2125, 2126, 2127, 2116, 2118, 2122');
	$menu = new EmundusHelperMenu;	
	$user =  JFactory::getUser($user_id); 
	$application = new EmundusModelApplication;

	// Element Fabrik ID list to display in PDF
	//$elts = array(2111, 2112, 2114, 2117, 2133, 2119, 2120, 2121, 2123, 2124, 2125, 2126, 2127, 2116, 2118, 2122);
	$elts = array($fabrik_elements_pdf);
	
	$options = array('show_list_label' => 0, 'show_form_label' => 0, 'show_group_label' => 0, 'rowid' => $rowid, 'profile_id' => '13');

	$forms = $application->getFormsPDFElts($user_id, $elts, $options);

	// Set title for PDF
	$title = @EmundusHelperList::getElementsDetailsByID($fabrik_elements_title); 

	$where = 'user='.$user_id;
	$where .= $options['rowid']>0?' AND id='.$options['rowid']:'';
	$query = 'SELECT '.$title[0]->element_name. ' FROM '.$title[0]->tab_name.' WHERE '.$where;
	$db->setQuery($query);
	$intitule_poste = $db->loadResult();

	$htmldata = '';
	$htmldata .= '<h1>'.$intitule_poste.'</h1>';
	$htmldata .= $forms;
	// --------------------- //
	set_time_limit(0);
	require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'config'.DS.'lang'.DS.'eng.php');
	require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'tcpdf.php');
	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('www.emundus.fr');
	$pdf->SetTitle(JText::_('COM_EMUNDUS_TITLE_OFFER'));
	
	//get logo
	$app 		= JFactory::getApplication();
    $template 	= $app->getTemplate(true);
    $params     = $template->params;
	
	if (!empty($params->get('logo')->custom->image)) {
	    $logo = json_decode(str_replace("'", "\"", $params->get('logo')->custom->image), true);
	    $logo = !empty($logo['path']) ? JPATH_ROOT.DS.$logo['path'] : "";
    } else {
    	$logo_module = JModuleHelper::getModuleById('90');
	    preg_match('#src="(.*?)"#i', $logo_module->content, $tab);

	    $pattern = "/^(?:ftp|https?|feed)?:?\/\/(?:(?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*
        (?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@)?(?:
        (?:[a-z0-9\-\.]|%[0-9a-f]{2})+|(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\]))(?::[0-9]+)?(?:[\/|\?]
        (?:[\w#!:\.\?\+\|=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})*)?$/xi";
	    if ((bool) preg_match($pattern, $tab[1])) {
	    	$tab[1] = parse_url($tab[1], PHP_URL_PATH);
	    }

	    $logo = JPATH_SITE.DS.$tab[1];
	}
	
	// manage logo by programme
    $ext = substr($logo, -3);
    $logo_prg = substr($logo, 0, -4).'-'.$item->training.'.'.$ext;
    if (is_file($logo_prg)) {
	    $logo = $logo_prg;
    }
	
	//get title
	$title = $app->getCfg('sitename');
	if (is_file($logo)) {
		$pdf->SetHeaderData($logo, PDF_HEADER_LOGO_WIDTH, $title, PDF_HEADER_STRING);
	}

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
		$pdf->writeHTMLCell(0,'','',$start_y, preg_replace('/(<[^>]+) style=".*?"/i', '$1', $htmldata),'B', 1);
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

	@chdir('tmp');
	/*if(!file_exists(EMUNDUS_PATH_ABS.$item->id)) {	
		if (!mkdir(EMUNDUS_PATH_ABS.$item->id, 0777, true) || !copy(EMUNDUS_PATH_ABS.'index.html', EMUNDUS_PATH_ABS.$item->id.DS.'index.html')) 
			return JError::raiseWarning(500, 'Unable to create user file');
	}
*/

	if ($output)
		$pdf->Output(EMUNDUS_PATH_ABS.$user_id.DS.'fiche_'.$rowid.'.pdf', 'FI');
	else
		$pdf->Output(EMUNDUS_PATH_ABS.$user_id.DS.'fiche_'.$rowid.'.pdf', 'FI');
}
?>