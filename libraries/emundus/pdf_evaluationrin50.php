<?php

function pdf_evaluation($user_id, $fnum = null, $output = true, $name = null, $options = null) {
    jimport( 'joomla.html.parameter' );
    set_time_limit(0);
    require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'config'.DS.'lang'.DS.'eng.php');
    require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'tcpdf.php');

    require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'filters.php');
    include_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'evaluation.php');
    include_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
    include_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');

    $m_profile = new EmundusModelProfile;
    $m_files = new EmundusModelFiles;

    $db = JFactory::getDBO();
    $app = JFactory::getApplication();
    $config = JFactory::getConfig();
    $user = $m_profile->getEmundusUser($user_id);
    $fnum = empty($fnum)?$user->fnum:$fnum;

    $infos = $m_profile->getFnumDetails($fnum);
    $campaign_id = $infos['campaign_id'];

    // Get form HTML
    $htmldata = '';

    // Create PDF object
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('eMundus');
    $pdf->SetTitle('Evaluation');

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

    //get logo
    $template = $app->getTemplate(true);
    $params = $template->params;

    $logo = json_decode(str_replace("'", "\"", $params->get('logo')->custom->image), true);
    $logo = !empty($logo['path']) ? JPATH_ROOT.DS.$logo['path'] : "";

    //get title
    $title = $config->get('sitename');
    $pdf->SetHeaderData($logo, PDF_HEADER_LOGO_WIDTH, $title, PDF_HEADER_STRING);
    unset($logo);
    unset($title);

    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, 'I', PDF_FONT_SIZE_DATA));
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->AddPage();

	/*** Applicant ***/
	$htmldata .=
		'<style>
		.card  { background-color: #cecece; border: none; display:block; line-height:80%;}
		.name  { display: block; font-size: 12pt; margin: 0 0 0 20px; padding:0; display:block; line-height:110%;}
		.maidename  { display: block; font-size: 20pt; margin: 0 0 0 20px; padding:0; }
		.nationality { display: block; margin: 0 0 0 20px;  padding:0;}
		.sent { display: block; font-family: monospace; margin: 0 0 0 10px; padding:0; text-align:right;}
		.birthday { display: block; margin: 0 0 0 20px; padding:0;}
		.label		   {white-space:nowrap; color:black; border-radius: 2px; padding:2px 2px 2px 2px; font-size: 90%; font-weight:bold; }
		.label-default {background-color:#999999;}
		.label-primary {background-color:#337ab7;}
		.label-success {background-color:#5cb85c;}
		.label-info    {background-color:#033c73;}
		.label-warning {background-color:#dd5600;}
		.label-danger  {background-color:#c71c22;}
		.label-lightpurple { background-color: #DCC6E0 }
		.label-purple { background-color: #947CB0 }
		.label-darkpurple {background-color: #663399 }
		.label-lightblue { background-color: #6bb9F0 }
		.label-blue { background-color: #19B5FE }
		.label-darkblue { background-color: #013243 }
		.label-lightgreen { background-color: #00E640 }
		.label-green { background-color: #3FC380 }
		.label-darkgreen { background-color: #1E824C }
		.label-lightyellow { background-color: #FFFD7E }
		.label-yellow { background-color: #FFFD54 }
		.label-darkyellow { background-color: #F7CA18 }
		.label-lightorange { background-color: #FABE58 }
		.label-orange { background-color: #E87E04 }
		.label-darkorange {background-color: #D35400 }
		.label-lightred { background-color: #EC644B }
		.label-red { background-color: #CF000F }
		.label-darkred { background-color: #96281B }
		.label-lightpink { background-color: #e08283; }
		.label-pink { background-color: #d2527f; }
		.label-darkpink { background-color: #db0a5b; }
		 @media print {
            .breaker {
				page-break-before: always;
			}
        }
</style>';

if (!function_exists('exif_imagetype')) {
    function exif_imagetype($filename) {
        if (( list($width, $height, $type, $attr) = getimagesize( $filename )) !== false) {
            return $type;
        }
        return false;
    }
}

if (!empty($options) && $options[0] != "" && $options[0] != "0") {
    $htmldata .= '<div class="card">
					<table width="100%"><tr>';
	if (file_exists(EMUNDUS_PATH_REL.$item->user_id.'/tn_'.$item->avatar) && !empty($item->avatar) && exif_imagetype(EMUNDUS_PATH_REL.$item->user_id.'/tn_'.$item->avatar)) {
	    $htmldata .= '<td width="20%"><img src="'.EMUNDUS_PATH_REL.$item->user_id.'/tn_'.$item->avatar.'" width="100" align="left" /></td>';
	} elseif (file_exists(EMUNDUS_PATH_REL.$item->user_id.'/'.$item->avatar) && !empty($item->avatar) && exif_imagetype(EMUNDUS_PATH_REL.$item->user_id.'/'.$item->avatar)) {
	    $htmldata .= '<td width="20%"><img src="'.EMUNDUS_PATH_REL.$item->user_id.'/'.$item->avatar.'" width="100" align="left" /></td>';
	}

	$htmldata .= '
	    <td>
	    <div class="name"><strong>'.$item->firstname.' '.strtoupper($item->lastname).'</strong>, '.$item->label.' ('.$item->cb_schoolyear.')</div>';

	if (isset($item->maiden_name)) {
        $htmldata .= '<div class="maidename">'.JText::_('MAIDEN_NAME').' : '.$item->maiden_name.'</div>';
    }
	$date_submitted = !empty($item->date_submitted)?strftime("%d/%m/%Y %H:%M", strtotime($item->date_submitted)):JText::_('NOT_SENT');

    if (in_array("aid", $options)) {
        $htmldata .= '<div class="nationality">'.JText::_('ID_CANDIDAT').' : '.$item->user_id.'</div>';
    }
    if (in_array("afnum", $options)) {
        $htmldata .= '<div class="nationality">'.JText::_('FNUM').' : '.$fnum.'</div>';
    }
    if (in_array("aemail", $options)) {
        $htmldata .= '<div class="birthday">'.JText::_('EMAIL').' : '.$item->email.'</div>';
    }
    if (in_array("aapp-sent", $options)) {
        $htmldata .= '<div class="sent">'.JText::_('APPLICATION_SENT_ON').' : '.$date_submitted.'</div>';
    }
    if (in_array("adoc-print", $options)) {
        $htmldata .= '<div class="sent">'.JText::_('DOCUMENT_PRINTED_ON').' : '.strftime("%d/%m/%Y  %H:%M", time()).'</div>';
    }
    if (in_array("tags", $options)) {
        $tags = $m_files->getTagsByFnum(explode(',', $fnum));
        $htmldata .='<br/><table><tr><td style="display: inline;"> ';
        foreach ($tags as $tag) {
            $htmldata .= '<span class="label '.$tag['class'].'" >'.$tag['label'].'</span>&nbsp;';
        }
        $htmldata .='</td></tr></table>';
    }
    $htmldata .= '</td></tr></table></div>';

} elseif ($options[0] == "0") {
    $htmldata .= '';
} else {
    $htmldata .= '<div class="card">
                <table width="100%"><tr>';
    if (file_exists(EMUNDUS_PATH_REL.$item->user_id.'/tn_'.$item->avatar) && !empty($item->avatar)) {
        $htmldata .= '<td width="20%"><img src="'.EMUNDUS_PATH_REL.$item->user_id.'/tn_'.$item->avatar.'" width="100" align="left" /></td>';
    } elseif (file_exists(EMUNDUS_PATH_REL.$item->user_id.'/'.$item->avatar) && !empty($item->avatar)) {
        $htmldata .= '<td width="20%"><img src="'.EMUNDUS_PATH_REL.$item->user_id.'/'.$item->avatar.'" width="100" align="left" /></td>';
    }
    $htmldata .= '
    <td width="80%">

    <div class="name"><strong>'.$item->firstname.' '.strtoupper(@$item->lastname).'</strong>, '.$item->label.' ('.$item->cb_schoolyear.')</div>';

    if (isset($item->maiden_name)) {
        $htmldata .= '<div class="maidename">'.JText::_('MAIDEN_NAME').' : '.$item->maiden_name.'</div>';
    }

    $date_submitted = (!empty($item->date_submitted) && !strpos($item->date_submitted, '0000'))?JHTML::_('date',$item->date_submitted):JText::_('NOT_SENT');

    $htmldata .= '
	    <div class="nationality">'.JText::_('ID_CANDIDAT').' : '.$item->user_id.'</div>
	    <div class="nationality">'.JText::_('FNUM').' : '.$fnum.'</div>
	    <div class="birthday">'.JText::_('EMAIL').' : '.$item->email.'</div>
	    <div class="sent">'.JText::_('APPLICATION_SENT_ON').' : '.$date_submitted.'</div>
	    <div class="sent">'.JText::_('DOCUMENT_PRINTED_ON').' : '.strftime("%d/%m/%Y  %H:%M", time()).'</div>
	    </td>
	    </tr>
	    </table>
	    </div>';
}
/**  END APPLICANT   ****/

    // get evaluation
	require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'evaluation.php');
	require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');

	$m_evaluation = new EmundusModelEvaluation();
	$m_files = new EmundusModelFiles;
	$h_files = new EmundusHelperFiles;

	$fnumInfo = $m_files->getFnumInfos($fnum);

	$current_user = JFactory::getSession()->get('emundusUser');

	$expert = false;
	$ed_expert = false;
	$pole_expert = false;
	switch ($current_user->profile) {

		case '1049':
		case '14':
			$expert = true;
			break;
		case '1021':
			$ed_expert = true;
			break;
		case '1053':
			$pole_expert = true;
			break;
		default:
			$expert = true;
			$ed_expert = true;
			$pole_expert = true;
			break;
	}


	$element_id = $m_evaluation->getAllEvaluationElements(1, $item->training);

	// filter out element ID's that are not to be displayed based on the user profile.
	// NO MATTER THE PROFILE, do not display the profile field : it's useless.
	$to_remove = ['12757'];

	if (!$expert) {
		array_push($to_remove, '12653', '12657', '12661', '12665', '12669', '12673', '12677', '12681', '12697', '12701', '12689', '12693', '12769', '12781', '12793', '12805');
	}

	if (!$ed_expert) {
		array_push($to_remove, '12817', '12829', '12761', '12765', '12841', '12853');
	}

	if (!$pole_expert) {
		array_push($to_remove, '12717', '12721', '12725', '12729', '12737', '12741', '12705', '12709', '12865', '12877');
	}

	// Filter out the parts we don't want.
	$element_id = array_diff($element_id, $to_remove);

	$elements = $h_files->getElementsName(implode(',', $element_id));
	$evaluations = $m_files->getFnumArray([$fnum], $elements);


	$data = array();
	foreach ($evaluations as $eval) {

		$str = '<em>'.JText::_('COM_EMUNDUS_EVALUATION_EVALUATED_ON').' : '.JHtml::_('date', $eval['jos_emundus_evaluations___time_date'], JText::_('DATE_FORMAT_LC')).' - '.$fnumInfo['name'].'</em>';
		$str .= '<table width="100%" border="1" cellspacing="0" cellpadding="5">';

		foreach ($elements as $element) {

			if ($expert && $element->element_name == 'expert_user') {
				$str .= '</table><h1> Expert : '.JFactory::getUser($eval['jos_emundus_evaluations___expert_user_raw'])->name.'</h1><table width="100%" border="1" cellspacing="0" cellpadding="5">';
			}
			if ($ed_expert && $element->element_name == 'ed_user') {
				$str .= '</table>'.($expert?'<div class="breaker"></div>':'').'<h1> Expert de L\'école doctorale : '.JFactory::getUser($eval['jos_emundus_evaluations___ed_user_raw'])->name.'</h1><table width="100%" border="1" cellspacing="0" cellpadding="5">';
			}
			if ($pole_expert && $element->element_name == 'expert_pole') {
				$str .= '</table>'.($expert || $ed_expert?'<div class="breaker"></div>':'').'<h1> Expert de pôle : '.JFactory::getUser($eval['jos_emundus_evaluations___expert_pole_raw'])->name.'</h1><table width="100%" border="1" cellspacing="0" cellpadding="5">';
			}

			$k = $element->tab_name.'___'.$element->element_name;


			if ($element->element_name != 'id' &&
				$element->element_name != 'time_date' &&
				$element->element_name != 'campaign_id' &&
				$element->element_name != 'student_id'&&
				$element->element_name != 'user' &&
				$element->element_name != 'fnum' &&
				$element->element_name != 'email' &&
				$element->element_name != 'label' &&
				$element->element_name != 'code' &&
				$element->element_name != 'spacer' &&
				$element->element_name != 'expert_user' &&
				$element->element_name != 'ed_user' &&
				$element->element_name != 'expert_pole' &&
				array_key_exists($k, $eval))
			{
				$str .= '<tr>';


				if (strpos($element->element_name, 'justify') !== false) {
					$str .= '<td colspan="2"><b>'.$element->element_label.'</b> <br>'.JText::_($eval[$k]).'</td>';
				} else {
					$str .= '<td width="70%"><b>'.$element->element_label.'</b> </td><td width="30%">'.JText::_($eval[$k]).'</td>';
				}
				$str .= '</tr>';
			}
		}

		$str .= '</table>';
		$str .= '<p></p><hr>';

		$data[$eval['fnum']][$eval['jos_emundus_evaluations___user_raw']] = $str;
	}



	if (empty($data)) {
		$htmldata .= '<p>'.JText::_('COM_EMUNDUS_NO_EVALUATIONS_FOUND').'</p>';
	}

    foreach ($data as $evals) {
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
    }

    if (is_null($name)) {
        $path = EMUNDUS_PATH_ABS.$item->user_id.DS.'evaluations.pdf';
    } else {
        $path = $name;
    }

    @chdir('tmp');
    if ($output) {
        $pdf->Output($path, 'FI');
    } else {
        $pdf->Output($path, 'F');
    }
}
