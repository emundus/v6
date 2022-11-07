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
    $fnum = empty($fnum) ? $user->fnum : $fnum;

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
    try {

        $db->setQuery($query);
        $item = $db->loadObject();

    } catch (Exception $e) {
        throw $e;
    }

    //get logo
    $template = $app->getTemplate(true);
    $params = $template->params;

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

    //get title
    $title = $config->get('sitename');
    if (is_file($logo)) {
	    $pdf->SetHeaderData($logo, PDF_HEADER_LOGO_WIDTH, $title, PDF_HEADER_STRING);
    }
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
    </style>';

    if (!function_exists('exif_imagetype')) {
        function exif_imagetype($filename) {
            if (( list($width, $height, $type, $attr) = getimagesize( $filename )) !== false) {
                return $type;
            }
        return false;
        }
    }

    $anonymize_data = EmundusHelperAccess::isDataAnonymized(JFactory::getUser()->id);

    if (!empty($options) && $options[0] != "" && $options[0] != "0") {

        $allowed_attachments = EmundusHelperAccess::getUserAllowedAttachmentIDs(JFactory::getUser()->id);
        if (!$anonymize_data && ($allowed_attachments === true || in_array('10', $allowed_attachments))) {

            $htmldata .= '<div class="card">
                            <table width="100%"><tr>';
            if (file_exists(EMUNDUS_PATH_REL . $item->user_id . '/tn_' . $item->avatar) && !empty($item->avatar) && exif_imagetype(EMUNDUS_PATH_REL . $item->user_id . '/tn_' . $item->avatar)) {
                $htmldata .= '<td width="20%"><img src="' . EMUNDUS_PATH_REL . $item->user_id . '/tn_' . $item->avatar . '" width="100" align="left" /></td>';
            } elseif (file_exists(EMUNDUS_PATH_REL . $item->user_id . '/' . $item->avatar) && !empty($item->avatar) && exif_imagetype(EMUNDUS_PATH_REL . $item->user_id . '/' . $item->avatar)) {
                $htmldata .= '<td width="20%"><img src="' . EMUNDUS_PATH_REL . $item->user_id . '/' . $item->avatar . '" width="100" align="left" /></td>';
            }

            $htmldata .= '
                <td>
                <div class="name"><strong>' . $item->firstname . ' ' . strtoupper($item->lastname) . '</strong>, ' . $item->label . ' (' . $item->cb_schoolyear . ')</div>';
            if (isset($item->maiden_name)) {
                $htmldata .= '<div class="maidename">'.JText::_('MAIDEN_NAME').' : '.$item->maiden_name.'</div>';
            }
        }


        $date_submitted = !empty($item->date_submitted)?strftime("%d/%m/%Y %H:%M", strtotime($item->date_submitted)):JText::_('NOT_SENT');

        if (in_array("aid", $options)) {
            $htmldata .= '<div class="nationality">'.JText::_('ID_CANDIDAT').' : '.$item->user_id.'</div>';
        }
        if (in_array("afnum", $options)) {
            $htmldata .= '<div class="nationality">'.JText::_('FNUM').' : '.$fnum.'</div>';
        }
        if (!$anonymize_data && in_array("aemail", $options)) {
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
        $allowed_attachments = EmundusHelperAccess::getUserAllowedAttachmentIDs(JFactory::getUser()->id);
        if (!$anonymize_data && ($allowed_attachments === true || in_array('10', $allowed_attachments))) {
            $htmldata .= '<div class="card">
                        <table width="100%"><tr>';
            if (file_exists(EMUNDUS_PATH_REL . $item->user_id . '/tn_' . $item->avatar) && !empty($item->avatar)) {
                $htmldata .= '<td width="20%"><img src="' . EMUNDUS_PATH_REL . $item->user_id . '/tn_' . $item->avatar . '" width="100" align="left" /></td>';
            } elseif (file_exists(EMUNDUS_PATH_REL . $item->user_id . '/' . $item->avatar) && !empty($item->avatar)) {
                $htmldata .= '<td width="20%"><img src="' . EMUNDUS_PATH_REL . $item->user_id . '/' . $item->avatar . '" width="100" align="left" /></td>';
            }
            $htmldata .= '
            <td width="80%">
        
            <div class="name"><strong>' . $item->firstname . ' ' . strtoupper(@$item->lastname) . '</strong>, ' . $item->label . ' (' . $item->cb_schoolyear . ')</div>';

            if (isset($item->maiden_name)) {
                $htmldata .= '<div class="maidename">' . JText::_('MAIDEN_NAME') . ' : ' . $item->maiden_name . '</div>';
            }
        }

        $date_submitted = (!empty($item->date_submitted) && !strpos($item->date_submitted, '0000'))?JHTML::_('date',$item->date_submitted):JText::_('NOT_SENT');

        $applicant_email = (!$anonymize_data) ? '<div class="birthday">'.JText::_('EMAIL').' : '.$item->email.'</div>' : '';
        $htmldata .= '
        <div class="nationality">'.JText::_('ID_CANDIDAT').' : '.$item->user_id.'</div>
        <div class="nationality">'.JText::_('FNUM').' : '.$fnum.'</div>
        '. $applicant_email .'
        <div class="sent">'.JText::_('APPLICATION_SENT_ON').' : '.$date_submitted.'</div>
        <div class="sent">'.JText::_('DOCUMENT_PRINTED_ON').' : '.strftime("%d/%m/%Y  %H:%M", time()).'</div>
        </td>
        </tr>
        </table>
        </div>';
    }

    /**  END APPLICANT   ****/

    // get evaluation
    $evaluation = new EmundusHelperFiles();
    $data = $evaluation->getEvaluation('html',$fnum);
	if (empty($data)) {
		$htmldata .= '<p>'.JText::_('COM_EMUNDUS_NO_EVALUATIONS_FOUND').'</p>';
	}

    $pdf->startTransaction();
    $start_y = $pdf->GetY();
    if (!$anonymize_data) {
        $pdf->Bookmark($item->lastname.' '.$item->firstname, 0);
    }
    $pdf->writeHTMLCell(0,'','',$start_y, $htmldata,'B', 1);

    foreach ($data as $evals) {
        foreach ($evals as $user => $html) {
            $start_y = $pdf->GetY();
            $pdf->writeHTMLCell(0,'','',$start_y, $html,'B', 1);
            if($user != array_key_last($evals)) {
                $pdf->addPage();
            }
        }
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
