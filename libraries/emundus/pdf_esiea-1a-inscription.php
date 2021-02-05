


<?php


// GET COUNTRY
function getCountry($country) {
    $db = JFactory::getDBO();
    $db->setQuery('SELECT valeur FROM jos_emundus_sise_code_pays WHERE id = '.$country);
    try {
        return $db->loadResult();
    } catch (Exception $e) {
        return "";
    }
}

// GET COUNTRY
function getCity($city) {
    $db = JFactory::getDBO();
    $db->setQuery('SELECT name FROM jos_emundus_french_cities WHERE id = '.(int)$city);
    try {
        return $db->loadResult();
    } catch (Exception $e) {
        return "";
    }
}

// GET COUNTRY
function getinstitut($inst) {
    $db = JFactory::getDBO();
    $db->setQuery('SELECT Institution FROM jos_emundus_institutions_francaise WHERE id = '.(int)$inst);
    try {
        return $db->loadResult();
    } catch (Exception $e) {
        return "";
    }
}


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


/** Generate a PDF letter based on the HTML it contains.
 * This is only for letter type 2, letters type 1 are any file uploaded by the user and 3 are DOC templates.
 *
 * @param Object $letter The letter to generate the pdf file from.
 * @param String $fnum The fnum of the file to generate for.
 * @param Int $user_id The ID of the user who's data we want.
 * @param String $training The training code for the fnum.
 *
 * @return Boolean False if queries fail or the letter template is not 2.
 */
function generateLetterFromHtml($letter, $fnum, $user_id, $training) {

    if ($letter->template_type != 2)
        return false;

    set_time_limit(0);
    require_once (JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'config'.DS.'lang'.DS.'eng.php');
    require_once (JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'tcpdf.php');
    require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
    require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
    require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');

    $user = JFactory::getUser($user_id);
    $current_user = JFactory::getUser();
    $db = JFactory::getDBO();
    $config = JFactory::getConfig();
    $app = JFactory::getApplication();

    $files = array();

    $m_application 	= new EmundusModelApplication;
    $m_campaign 	= new EmundusModelCampaign;
    $m_emails 		= new EmundusModelEmails;

    $campaign = $m_campaign->getCampaignsByCourse($training);

    if (class_exists('MYPDF') === false || !class_exists('MYPDF')) {
        // Extend the TCPDF class to create custom Header and Footer
        class MYPDF extends TCPDF {

            var $logo = "";
            var $logo_footer = "";
            var $footer = "";

            //Page header
            public function Header() {
                // Logo
                if (is_file($this->logo))
                    $this->Image($this->logo, 0, 0, 200, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                // Set font
                $this->SetFont('courier', 'B', 16);
                // Title
                $this->Cell(0, 15, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            }

            // Page footer
            public function Footer() {
                // Position at 15 mm from bottom
                $this->SetY(-15);
                // Set font
                $this->SetFont('courier', 'I', 8);
                // Page number
                $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
                // footer
                $this->writeHTMLCell($w=0, $h=0, $x='', $y=250, $this->footer, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
                //logo
                if (is_file($this->logo_footer))
                    $this->Image($this->logo_footer, 150, 280, 40, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

                $this->SetLineStyle(array('width' => 0.25 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 255, 255)));
            }
        }
    }

    $error = 0;

    $attachment = $m_application->getAttachmentByID($letter->attachment_id);

    try {

        // Test if letter type has already been created for that user/campaign/attachment and delete before if true.
        $query = 'SELECT * FROM #__emundus_uploads WHERE user_id='.$user_id.' AND attachment_id='.$letter->attachment_id.' AND campaign_id='.$campaign['id']. ' AND fnum like '.$db->Quote($fnum);
        $db->setQuery($query);
        $file = $db->loadAssoc();

    } catch (Exception $e) {
        JLog::add('SQL Error in emundus pdf library at query : '.$query, JLog::ERROR, 'com_emundus');
        return false;
    }

    // test if directory exist
    if (!file_exists(EMUNDUS_PATH_ABS.$user_id)) {
        mkdir(EMUNDUS_PATH_ABS.$user_id, 0755, true);
        chmod(EMUNDUS_PATH_ABS.$user_id, 0755);
    }

    if (count($file) > 0 && strpos($file['filename'], 'lock') === false) {

        try {

            $query = 'DELETE FROM #__emundus_uploads WHERE user_id='.$user_id.' AND attachment_id='.$letter->attachment_id.' AND campaign_id='.$campaign['id']. ' AND fnum like '.$db->Quote($fnum).' AND filename NOT LIKE "%lock%"';
            $db->setQuery($query);
            $db->execute();

        } catch (Exception $e) {
            JLog::add('SQL error in emundus pdf library at query : '.$query, JLog::ERROR, 'com_emundus');
            return false;
        }

        @unlink(EMUNDUS_PATH_ABS.$user_id.DS.$file['filename']);
    }

    // Common tags to use.
    $post = [
        'TRAINING_CODE' 	=> $training,
        'TRAINING_PROGRAMME'=> $campaign['label'],
        'USER_NAME' 		=> $user->name,
        'USER_EMAIL' 		=> $user->email,
        'FNUM' 				=> $fnum
    ];

    $tags = $m_emails->setTags($user_id, $post, $fnum);
    $htmldata = "";
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($current_user->name);
    $pdf->SetTitle($letter->title);

    // set margins
    //$pdf->SetMargins(0, 0, 0);

    $pdf->footer = $letter->footer;

    //get logo
    preg_match('#src="(.*?)"#i', $letter->header, $tab);
    $pdf->logo = JPATH_BASE.DS.$tab[1];

    preg_match('#src="(.*?)"#i', $letter->footer, $tab);
    $pdf->logo_footer = JPATH_BASE.DS.@$tab[1];

    unset($logo);
    unset($logo_footer);

    //$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
    $pdf->SetFont('courier', '', 8);

    $letter->body = $m_emails->setTagsFabrik($letter->body, array($fnum));

    $htmldata .= preg_replace($tags['patterns'], $tags['replacements'], preg_replace("/<span[^>]+\>/i", "", preg_replace("/<\/span\>/i", "", preg_replace("/<br[^>]+\>/i", "<br>", $letter->body))));

    $pdf->AddPage();

    $pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $htmldata, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
    $pdf->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 255, 255)));

    @chdir('tmp');

    $name = $attachment['lbl'].'_'.date('Y-m-d_H-i-s').'.pdf';

    $pdf->Output(EMUNDUS_PATH_ABS.$user_id.DS.$name, 'F');

    $path = EMUNDUS_PATH_ABS.$user_id.DS.$name;

    if ($error == 0) {

        try {

            $query = 'INSERT INTO #__emundus_uploads (user_id, attachment_id, filename, description, can_be_deleted, can_be_viewed, campaign_id, fnum) VALUES ('.$user_id.', '.$letter->attachment_id.', "'.$name.'","'.$training.' '.date('Y-m-d H:i:s').'", 0, 1, '.$campaign['id'].', '.$db->Quote($fnum).')';
            $db->setQuery($query);
            $db->execute();

        } catch (Exception $e) {
            JLog::add('SQL error in emundus pdf library at query : '.$query, JLog::ERROR, 'com_emundus');
        }

        return $path;
    }

}


/** Generate the letter result
 * @param int $user_id the user ID
 * @param bool Eligibility ID of the evaluation
 * @param String Code of the programme
 * @param int Campaign id
 * @param int Evaluation id
 * @param mixed output format
 * @param String File number
 * @return Array Files
 */
function letter_pdf ($user_id, $eligibility, $training, $campaign_id, $evaluation_id, $output = true, $fnum = null) {
    set_time_limit(0);
    require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'config'.DS.'lang'.DS.'eng.php');
    require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'tcpdf.php');
    include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
    include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'evaluation.php');
    include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
    include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');

    $current_user 	= JFactory::getUser();
    $db 			= JFactory::getDBO();
    $config 		= JFactory::getConfig();
    $jdate 			= JFactory::getDate();
    $app			= JFactory::getApplication();

    $timezone = new DateTimeZone($config->get('offset'));
    $jdate->setTimezone($timezone);
    $now = $jdate->toSql();

    $files = array();

    $m_application 	= new EmundusModelApplication;
    $m_evaluation 	= new EmundusModelEvaluation;
    $m_campaign 	= new EmundusModelCampaign;
    $m_emails 		= new EmundusModelEmails;

    /*$query = "SELECT * FROM #__emundus_setup_letters WHERE eligibility=".$eligibility." AND training=".$db->Quote($training);
    $db->setQuery($query);
    $letters = $db->loadAssocList();*/
    $letters = $m_evaluation->getLettersTemplate($eligibility, $training);

    /*$query = "SELECT * FROM #__emundus_setup_teaching_unity WHERE id = (select training_id from #__emundus_training_174_repeat where applicant_id=".$user_id." and campaign_id=".$campaign_id.") ORDER BY date_start ASC";
    $db->setQuery($query);
    $courses = $db->loadAssocList();
    */

    try {

        $query = "SELECT * FROM #__emundus_setup_teaching_unity
					WHERE published=1 AND date_start>'".$now."' AND code IN (".$db->Quote($letters[0]['training']).")
					ORDER BY date_start ASC";
        $db->setQuery($query);
        $courses = $db->loadAssocList();

    } catch (Exception $e) {
        JLog::add('SQL Error in Emundus pdf library at query : '.$query, JLog::ERROR, 'com_emundus');
    }

    $courses_list = '';
    $courses_fee = ' ';
    foreach ($courses as $c) {
        $ds = !empty($c['date_start']) ? date(JText::_('DATE_FORMAT_LC3'), strtotime($c['date_start'])) : JText::_('NOT_DEFINED');
        $de = !empty($c['date_end']) ? date(JText::_('DATE_FORMAT_LC3'), strtotime($c['date_end'])) : JText::_('NOT_DEFINED');
        //$courses_list .= '<li>'.$ds.' - '.$de.'</li>';
        $courses_list .= '<img src="'.JPATH_BASE.DS."media".DS."com_emundus".DS."images".DS."icones".DS."checkbox-unchecked_16x16.png".'" width="8" height="8" align="left" /> ';
        $courses_list .= $ds.' - '.$de.'<br />';
        $courses_fee  .= 'Euro '.$c['price'].',-- ';
    }

    $campaign = $m_campaign->getCampaignByID($campaign_id);

    // Extend the TCPDF class to create custom Header and Footer
    class MYPDF extends TCPDF {

        var $logo = "";
        var $logo_footer = "";
        var $footer = "";

        //Page header
        public function Header() {
            // Logo
            if (is_file($this->logo))
                $this->Image($this->logo, 0, 0, 200, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            // Set font
            $this->SetFont('courier', 'B', 16);
            // Title
            $this->Cell(0, 15, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        }

        // Page footer
        public function Footer() {
            // Position at 15 mm from bottom
            $this->SetY(-15);
            // Set font
            $this->SetFont('courier', 'I', 8);
            // Page number
            $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
            // footer
            $this->writeHTMLCell($w=0, $h=0, $x='', $y=250, $this->footer, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
            //logo
            if (is_file($this->logo_footer))
                $this->Image($this->logo_footer, 150, 280, 40, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

            $this->SetLineStyle(array('width' => 0.25 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 255, 255)));
        }
    }

    //
    // Evaluation result
    //
    if ($evaluation_id > 0) {
        $evaluation = $m_evaluation->getEvaluationByID($evaluation_id);
        $reason = $m_evaluation->getEvaluationReasons();
        unset($evaluation[0]["id"]);
        unset($evaluation[0]["user"]);
        unset($evaluation[0]["time_date"]);
        unset($evaluation[0]["student_id"]);
        unset($evaluation[0]["parent_id"]);
        unset($evaluation[0]["campaign_id"]);
        unset($evaluation[0]["comment"]);

        if(empty($evaluation[0]["reason"])) {
            unset($evaluation[0]["reason"]);
            unset($evaluation[0]["reason_other"]);
        } elseif(empty($evaluation[0]["reason_other"])) {
            unset($evaluation[0]["reason_other"]);
        }

        include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'list.php');
        $evaluation_details = @EmundusHelperList::getElementsDetailsByName('"'.implode('","', array_keys($evaluation[0])).'"');

        $result = "";
        foreach ($evaluation_details as $ed) {
            if ($ed->hidden==0 && $ed->published==1 && $ed->tab_name=="jos_emundus_evaluations") {
                //$result .= '<br>'.$ed->element_label.' : ';
                if ($ed->element_name=="reason") {
                    $result .= '<ul>';
                    foreach ($evaluation as $e) {
                        $result .= '<li>'.@$reason[$e[@$ed->element_name]]->reason.'</li>'; //die(print_r(@$reason[$e[@$ed->element_name]]));
                    }
                    if (@!empty($evaluation[0]["reason_other"]))
                        $result .= '<ul><li>'.@$evaluation[0]["reason_other"].'</li></ul>';
                    $result .= '</ul>';
                } /*elseif($ed->element_name=="result") {
						$result .= $eligibility[$evaluation[0][$ed->element_name]]->title;
				} else
					$result .= $evaluation[0][$ed->element_name];*/
            }
        }
    }

    //
    // Replacement
    //
    $post = array(  'TRAINING_CODE' => $training,
        'TRAINING_PROGRAMME' => $campaign['label'],
        'REASON' => @$result,
        'TRAINING_FEE' => $courses_fee,
        'TRAINING_PERIODE' => $courses_list,
        'USER_NAME' => $current_user->name,
        'USER_EMAIL' => $current_user->email,
        'FNUM' => $fnum );

//die(var_dump($tags));
    foreach ($letters as $letter) {
        $error = 0;

        $attachment = $m_application->getAttachmentByID($letter['attachment_id']);

        /*$query = "SELECT * FROM #__emundus_setup_attachments WHERE id=".$letter['attachment_id'];
        $db->setQuery($query);
        $attachment = $db->loadAssoc();*/

        try {

            // Test if letter type has already been created for that user/campaign/attachment and delete before if true.
            $query = 'SELECT * FROM #__emundus_uploads WHERE user_id='.$user_id.' AND attachment_id='.$letter['attachment_id'].' AND campaign_id='.$campaign_id. ' AND fnum like '.$db->Quote($fnum);
            $db->setQuery($query);
            $file = $db->loadAssoc();

        } catch (Exception $e) {
            JLog::add('SQL Error in emundus pdf library at query : '.$query, JLog::ERROR, 'com_emundus');
        }
        // test if directory exist
        if (!file_exists(EMUNDUS_PATH_ABS.$user_id)) {
            mkdir(EMUNDUS_PATH_ABS.$user_id, 0755, true);
            chmod(EMUNDUS_PATH_ABS.$user_id, 0755);
        }

        if (count($file) > 0 && strpos($file['filename'], 'lock') === false && $letter['template_type'] != 4) {

            try {

                $query = 'DELETE FROM #__emundus_uploads WHERE user_id='.$user_id.' AND attachment_id='.$letter['attachment_id'].' AND campaign_id='.$campaign_id. ' AND fnum like '.$db->Quote($fnum).' AND filename NOT LIKE "%lock%"';
                $db->setQuery($query);
                $db->execute();

            } catch (Exception $e) {
                JLog::add('SQL error in emundus pdf library at query : '.$query, JLog::ERROR, 'com_emundus');
            }

            @unlink(EMUNDUS_PATH_ABS.$user_id.DS.$file['filename']);
        }

        if ($letter['template_type'] == 1) { // Static file

            $file_path = explode(DS, $letter['file']);
            $file_type = explode('.', $file_path[count($file_path)-1]);
            $name = $attachment['lbl'].'_'.date('Y-m-d_H-i-s').'.'.$file_type[1];

            if (file_exists(JPATH_BASE.$letter['file'])) {
                $path = EMUNDUS_PATH_ABS.$user_id.DS.$name;
                $url  = EMUNDUS_PATH_REL.$user_id.'/'.$name;
                copy(JPATH_BASE.$letter['file'], $path);
            } else {
                $app->enqueueMessage($name.' - '.JText::_("TEMPLATE_FILE_MISSING").' : '.JPATH_BASE.$letter['file'], 'error');
                $error++;
            }

        } elseif ($letter['template_type'] == 3) { // Template file .docx

            $tags = $m_emails->setTagsWord($user_id, $post, $fnum);
            require_once JPATH_LIBRARIES.DS.'PHPWord.php';

            $file_path = explode(DS, $letter['file']);
            $file_type = explode('.', $file_path[count($file_path)-1]);
            $name = $attachment['lbl'].'_'.date('Y-m-d_H-i-s').'.'.$file_type[1];

            if (file_exists(JPATH_BASE.$letter['file'])) {

                $PHPWord = new PHPWord();
                $document = $PHPWord->loadTemplate(JPATH_BASE.$letter['file']);

                for ($i = 0; $i < count($tags['patterns']); $i++) {
                    $document->setValue($tags['patterns'][$i], $tags['replacements'][$i]);
                    //echo $tags['patterns'][$i]." - ".$tags['replacements'][$i]."<br>";
                }

                $path = EMUNDUS_PATH_ABS.$user_id.DS.$name;
                $url  = EMUNDUS_PATH_REL.$user_id.'/'.$name;

                $document->save($path);
                unset($document);
            } else {
                $app->enqueueMessage($name.' - '.JText::_("TEMPLATE_FILE_MISSING").' : '.JPATH_BASE.$letter['file'], 'error');
                $error++;
            }

        } elseif ($letter['template_type'] == 4) { // Applicant file
            $upload_file = $m_application->getAttachmentsByFnum($fnum, $letter['attachment_id']);
            $name = $upload_file[0]->filename;
            if (file_exists(JPATH_BASE.$letter['file'])) {
                $path = EMUNDUS_PATH_ABS.$user_id.DS.$name;
                $url  = EMUNDUS_PATH_REL.$user_id.'/'.$name;
            } else {
                $app->enqueueMessage($name.' - '.JText::_("TEMPLATE_FILE_MISSING").' : '.JPATH_BASE.$letter['file'], 'error');
                $error++;
            }

        } else { // From HTML : $letter['template_type'] == 2
            $tags = $m_emails->setTags($user_id, $post, $fnum);
            $htmldata = "";
            $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor($current_user->name);
            $pdf->SetTitle($letter['title']);

            // set margins
            //$pdf->SetMargins(0, 0, 0);
            //$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            //$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            $pdf->footer = $letter["footer"];

            //get logo
            preg_match('#src="(.*?)"#i', $letter['header'], $tab);
            $pdf->logo = JPATH_BASE.DS.$tab[1];

            preg_match('#src="(.*?)"#i', $letter['footer'], $tab);
            $pdf->logo_footer = JPATH_BASE.DS.@$tab[1];

            //get title
            //	$config =& JFactory::getConfig();
            //	$title = $config->getValue('config.sitename');
            //	$title = "";
            //	$pdf->SetHeaderData($logo, PDF_HEADER_LOGO_WIDTH, $title, PDF_HEADER_STRING);

            unset($logo);
            unset($logo_footer);

            //$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            //$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            //$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
            //$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->SetFont('courier', '', 8);

            //$dimensions = $pdf->getPageDimensions();

            //$htmldata .= $letter["header"];
            $letter["body"] = $m_emails->setTagsFabrik($letter["body"], array($fnum));

            $htmldata .= preg_replace($tags['patterns'], $tags['replacements'], preg_replace("/<span[^>]+\>/i", "", preg_replace("/<\/span\>/i", "", preg_replace("/<br[^>]+\>/i", "<br>", $letter["body"]))));

            //$htmldata .= $letter["footer"];
            //die($htmldata);
            $pdf->AddPage();
            $pdf->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 255, 255)));

            // Print text using writeHTMLCell()
            $pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $htmldata, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);

            @chdir('tmp');

            $name = $attachment['lbl'].'_'.date('Y-m-d_H-i-s').'.pdf';
            if ($output)
                $pdf->Output(EMUNDUS_PATH_ABS.$user_id.DS.$name, $output);
            else
                $pdf->Output(EMUNDUS_PATH_ABS.$user_id.DS.$name, 'F');
            $path = EMUNDUS_PATH_ABS.$user_id.DS.$name;
            $url  = EMUNDUS_PATH_REL.$user_id.'/'.$name;
        }

        if ($error == 0) {
            if ($letter['template_type'] == 4) {
                $id = $upload_file[0]->id;
            } else {

                try {

                    $query = 'INSERT INTO #__emundus_uploads (user_id, attachment_id, filename, description, can_be_deleted, can_be_viewed, campaign_id, fnum) VALUES ('.$user_id.', '.$letter['attachment_id'].', "'.$name.'","'.$training.' '.date('Y-m-d H:i:s').'", 0, 1, '.$campaign_id.', '.$db->Quote($fnum).')';
                    $db->setQuery($query);
                    $db->execute();
                    $id = $db->insertid();

                } catch (Exception $e) {
                    JLog::add('SQL error in emundus pdf library at query : '.$query, JLog::ERROR, 'com_emundus');
                }
            }
            $file_info['id'] = $id;
            $file_info['path'] = $path;
            $file_info['attachment_id'] = $letter['attachment_id'];
            $file_info['name'] = $attachment['value'];
            $file_info['url'] = $url;

            $files[] = $file_info;
        }
    }

    return $files;
}


// @description Generate the letter template result
// @params Applicant user ID
// @params Eligibility ID of the evaluation
// @params Code of the programme
// @params Type of output

function letter_pdf_template ($user_id, $letter_id, $fnum = null) {
    set_time_limit(0);
    require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'config'.DS.'lang'.DS.'eng.php');
    require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'tcpdf.php');
    include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
    include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'evaluation.php');
    include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');

    $current_user 	= JFactory::getUser();
    $db 			= JFactory::getDBO();
    $config 		= JFactory::getConfig();
    $jdate 			= JFactory::getDate();

    $timezone = new DateTimeZone($config->get('offset'));
    $jdate->setTimezone($timezone);
    $now = $jdate->toSql();

    $files = array();

    $m_application 	= new EmundusModelApplication;
    $m_evaluation 	= new EmundusModelEvaluation;
    $m_emails 		= new EmundusModelEmails;


    $letters = $m_evaluation->getLettersTemplateByID($letter_id);

//print_r($letters);
    //$query = "SELECT * FROM #__emundus_setup_teaching_unity WHERE published=1 AND date_start>NOW() AND code=".$db->Quote($letters[0]['training']). " ORDER BY date_start ASC";
    try {

        $query = "SELECT * FROM #__emundus_setup_teaching_unity
					WHERE published=1 AND date_start>'".$now."' AND code IN (".$letters[0]['training'].")
					ORDER BY date_start ASC";
        $db->setQuery($query);
        $courses = $db->loadAssocList();

    } catch (Exception $e) {
        JLog::add('SQL error in emundus pdf library at query : '.$query, JLog::ERROR, 'com_emundus');
    }

    $courses_list = '';
    $courses_fee = ' ';
    foreach ($courses as $c) {
        $ds = !empty($c['date_start']) ? date(JText::_('DATE_FORMAT_LC3'), strtotime($c['date_start'])) : JText::_('NOT_DEFINED');
        $de = !empty($c['date_end']) ? date(JText::_('DATE_FORMAT_LC3'), strtotime($c['date_end'])) : JText::_('NOT_DEFINED');
        //$courses_list .= '<li>'.$ds.' - '.$de.'</li>';
        $courses_list .= '<img src="'.JPATH_BASE.DS."media".DS."com_emundus".DS."images".DS."icones".DS."checkbox-unchecked_16x16.png".'" width="8" height="8" align="left" /> ';
        $courses_list .= $ds.' - '.$de.'<br />';
        $courses_fee  .= 'Euro '.$c['price'].'<br>';
        $programme = $c['label'];
    }

    // Extend the TCPDF class to create custom Header and Footer
    class MYPDF extends TCPDF {

        var $logo = "";
        var $logo_footer = "";
        var $footer = "";

        //Page header
        public function Header() {
            // Logo
            if (is_file($this->logo))
                $this->Image($this->logo, 0, 0, 200, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            // Set font
            $this->SetFont('courier', 'B', 16);
            // Title
            $this->Cell(0, 15, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        }

        // Page footer
        public function Footer() {
            // Position at 15 mm from bottom
            $this->SetY(-15);
            // Set font
            $this->SetFont('courier', 'I', 8);
            // Page number
            $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
            // footer
            $this->writeHTMLCell($w=0, $h=0, $x='', $y=250, $this->footer, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
            //logo
            if (is_file($this->logo_footer))
                $this->Image($this->logo_footer, 150, 280, 40, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

            $this->SetLineStyle(array('width' => 0.25 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 255, 255)));
        }
    }

    //
    // Replacement
    //
    $post = array(  'TRAINING_CODE' 		=> @$letters[0]['training'],
        'TRAINING_PROGRAMME' 	=> @$programme,
        'REASON'				=> JText::_("DEPEND_OF_EVALUATION"),
        'TRAINING_FEE' 			=> @$courses_fee,
        'TRAINING_PERIODE'		=> @$courses_list
    );
    $tags = $m_emails->setTags($user_id, $post, $fnum);

    foreach ($letters as $letter) {
        $attachment = $m_application->getAttachmentByID($letter['attachment_id']);

        if ($letter['template_type'] == 1) { // Static file
            $file_path = explode(DS, $letter['file']);
            $file_type = explode('.', $file_path[count($file_path)-1]);
            $name = date('Y-m-d_H-i-s').$attachment['lbl'].'.'.$file_type[1];

            $file = JPATH_BASE.$letter['file']; //die($file);
            if (file_exists($file)) {
                $mime_type = get_mime_type($file);
                header('Content-type: application/'.$mime_type);
                header('Content-Disposition: inline; filename='.basename($file));
                header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
                header('Cache-Control: no-store, no-cache, must-revalidate');
                header('Cache-Control: pre-check=0, post-check=0, max-age=0');
                header('Pragma: anytextexeptno-cache', true);
                header('Cache-control: private');
                header('Expires: 0');
                //header('Content-Transfer-Encoding: binary');
                //header('Content-Length: ' . filesize($file));
                //header('Accept-Ranges: bytes');

                ob_clean();
                flush();
                readfile($file);
                exit;
            } else {
                JError::raiseWarning( 500, JText::_( 'FILE_NOT_FOUND' ).' '.$file );
                //$this->setRedirect('index.php?option=com_emundus&view='.$view.'&Itemid='.$Itemid);
            }

        } elseif ($letter['template_type'] == 3) { // Template file .docx
            require_once JPATH_LIBRARIES.DS.'PHPWord.php';

            $file_path = explode(DS, $letter['file']);
            $file_type = explode('.', $file_path[count($file_path)-1]);
            $name = date('Y-m-d_H-i-s').$attachment['lbl'].'.'.$file_type[1];

            $PHPWord = new PHPWord();

            $document = $PHPWord->loadTemplate(JPATH_BASE.$letter['file']);

            for ($i = 0; $i < count($tags['patterns']); $i++) {
                $document->setValue($tags['patterns'][$i], $tags['replacements'][$i]);
                //echo $tags['patterns'][$i]." - ".$tags['replacements'][$i]."<br>";
            }

            $document->save(JPATH_BASE.DS.'tmp'.DS.$name);

            $file = JPATH_BASE.DS.'tmp'.DS.$name; //die($file);
            if (file_exists($file)) {
                $mime_type = get_mime_type($file);
                header('Content-type: application/'.$mime_type);
                header('Content-Disposition: inline; filename='.basename($file));
                header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
                header('Cache-Control: no-store, no-cache, must-revalidate');
                header('Cache-Control: pre-check=0, post-check=0, max-age=0');
                header('Pragma: anytextexeptno-cache', true);
                header('Cache-control: private');
                header('Expires: 0');
                //header('Content-Transfer-Encoding: binary');
                //header('Content-Length: ' . filesize($file));
                //header('Accept-Ranges: bytes');

                ob_clean();
                flush();
                readfile($file);
                exit;
            } else {
                JError::raiseWarning( 500, JText::_( 'FILE_NOT_FOUND' ).' '.$file );
                //$this->setRedirect('index.php?option=com_emundus&view='.$view.'&Itemid='.$Itemid);
            }

            unset($document);

        } else { // From HTML
            $htmldata = "";

            $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor($current_user->name);
            $pdf->SetTitle($letter['title']);

            // set margins
            //$pdf->SetMargins(5, 30, 5);
            //$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            //$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            $pdf->footer = $letter["footer"];

            //get logo
            preg_match('#src="(.*?)"#i', $letter['header'], $tab);
            $pdf->logo = JPATH_BASE.DS.$tab[1];

            preg_match('#src="(.*?)"#i', $letter['footer'], $tab);
            $pdf->logo_footer = JPATH_BASE.DS.$tab[1];

            //get title
            /*	$config =& JFactory::getConfig();
                $title = $config->getValue('config.sitename');
                $title = "";
                $pdf->SetHeaderData($logo, PDF_HEADER_LOGO_WIDTH, $title, PDF_HEADER_STRING);*/
            unset($logo);
            unset($logo_footer);

            //$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            //$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            //$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
            //$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->SetFont('courier', '', 8);

            //$dimensions = $pdf->getPageDimensions();

            //$htmldata .= $letter["header"];
            ;
            $htmldata .= preg_replace($tags['patterns'], $tags['replacements'], preg_replace("/<span[^>]+\>/i", "", preg_replace("/<\/span\>/i", "", preg_replace("/<br[^>]+\>/i", "<br>", $letter["body"]))));
            //$htmldata .= $letter["footer"];
            //die($htmldata);
            $pdf->AddPage();
            $pdf->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 255, 255)));

            // Print text using writeHTMLCell()
            $pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $htmldata, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);

            @chdir('tmp');
            $pdf->Output(EMUNDUS_PATH_ABS.$user_id.DS."demo", 'I');
        }
    }
//die(print_r($files));
    exit();
}

function data_to_img($match) {
    list(, $img, $type, $base64, $end) = $match;

    $bin = base64_decode($base64);
    $md5 = md5($bin);   // generate a new temporary filename
    $fn = "tmp/$md5.$type";
    file_exists($fn) or file_put_contents($fn, $bin);

    return "$img$fn$end";  // new <img> tag
}

function getSiseCountry($country) {
    $db = JFactory::getDBO();

    $query = 'SELECT valeur FROM #__emundus_sise_code_pays WHERE code LIKE "'.$country.'"';
    try {
        $db->setQuery($query);
        return $db->loadResult();
    } catch (Exception $e) {
        JLog::add('SQL error in emundus pdf library at query : '.$query, JLog::ERROR, 'com_emundus');
    }
    return null;
}


function getProfession($profession) {
    $db = JFactory::getDBO();

    $query = 'SELECT valeur FROM #__emundus_list_profession_insee_sise WHERE code LIKE "'.$profession.'"';
    try {
        $db->setQuery($query);
        return $db->loadResult();
    } catch (Exception $e) {
        JLog::add('SQL error in emundus pdf library at query : '.$query, JLog::ERROR, 'com_emundus');
    }
    return null;
}


function application_form_pdf($user_id, $fnum = null, $output = true, $form_post = 1, $form_ids = null, $options = null, $application_form_order = null, $profile_id = null, $file_lbl = null) {
    jimport('joomla.html.parameter');
    set_time_limit(0);
    require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'config'.DS.'lang'.DS.'eng.php');
    require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'tcpdf.php');

    require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
    require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
    require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');

    if (empty($file_lbl)) {
        $file_lbl = "_application";
    }

    $config = JFactory::getConfig();
    $offset = $config->get('offset');

    $m_profile = new EmundusModelProfile;
    $m_application = new EmundusModelApplication;
    $m_files = new EmundusModelFiles;

    $db = JFactory::getDBO();
    $app = JFactory::getApplication();
    $current_user = JFactory::getUser();
    $user = $m_profile->getEmundusUser($user_id);
    $fnum = empty($fnum)?$user->fnum:$fnum;

    $infos = $m_profile->getFnumDetails($fnum);
    $campaign_id = $infos['campaign_id'];

    // Get form HTML
    $htmldata = '';

    // Create PDF object
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Decision Publique');
    $pdf->SetTitle('Application Form');
    $m_files = new EmundusModelFiles();
    $m_application = new EmundusModelApplication();

    date_default_timezone_set('Europe/Paris');
    // --- La setlocale() fonctionnne pour strftime mais pas pour DateTime->format()
    setlocale(LC_TIME, 'fr_FR.utf8','fra');// OK
    $date = strftime('%d %B %Y');

    try {
        $fnumInfo = $m_files->getFnumInfos($fnum);
        $payment = $m_application->getHikashopOrder($fnumInfo,true);

        $query = $db->getQuery(true);

        $emundus_user = [
            $db->qn('eu.civility'),
            $db->qn('eu.firstname'),
            $db->qn('eu.lastname'),
            $db->qn('eu.email', 'user_email'),
            $db->qn('eu.mobile_phone')
        ];

        $personal_details = [
            $db->qn('pd.street_1'),
            $db->qn('pd.street_2'),
            $db->qn('pd.street_3')
        ];

        $admission = [
            'ea.*'
        ];

        $cursus_repeat = [
            $db->qn('cursus_repeat.cursus_suivi', 'cursus_suivi_repeat'),
            $db->qn('cursus_repeat.previous_establishment', 'previous_establishment_repeat'),
            $db->qn('cursus_repeat.institut_adresse', 'institut_adresse_repeat'),
            $db->qn('cursus_repeat.institut_country', 'institut_country_repeat'),
            $db->qn('cursus_repeat.institut_city', 'institut_city_repeat'),
            $db->qn('cursus_repeat.institut_city_other', 'institut_city_other_repeat'),
            $db->qn('cursus_repeat.fin_formation', 'fin_formation_repeat')
        ];

        $declaration = [
            $db->qn('declaration.iban'),
            $db->qn('declaration.bic'),
            $db->qn('declaration.multiple_payment')
        ];

        $cities = [
            $db->qn('city_fr.name', 'fr_city')
        ];

        $query
            ->select(array_merge($emundus_user, $personal_details, $admission, $cursus_repeat, $cities, $declaration))
            ->from($db->qn('#__emundus_admission', 'ea'))
            ->leftjoin($db->qn('#__emundus_admission_718_repeat', 'cursus_repeat') . ' ON ' . $db->qn('cursus_repeat.parent_id') . ' = ' . $db->qn('ea.id'))
            ->leftjoin($db->qn('#__emundus_campaign_candidature', 'cc') . ' ON ' . $db->qn('cc.fnum') . ' = ' . $db->qn('ea.fnum'))
            ->leftjoin($db->qn('#__emundus_personal_detail', 'pd') . ' ON ' . $db->qn('cc.fnum') . ' = ' . $db->qn('pd.fnum'))
            //->leftjoin($db->qn('#__emundus_academic', 'aca') . ' ON ' . $db->qn('cc.fnum') . ' = ' . $db->qn('aca.fnum'))
            ->leftjoin($db->qn('#__emundus_declaration_inscription', 'declaration') . ' ON ' . $db->qn('cc.fnum') . ' = ' . $db->qn('declaration.fnum'))
            ->leftjoin($db->qn('#__emundus_users', 'eu') . ' ON ' . $db->qn('cc.applicant_id') . ' = ' . $db->qn('eu.user_id'))
            ->leftjoin($db->qn('#__emundus_french_cities', 'city_fr') . ' ON ' . $db->qn('ea.city_1') . ' = ' . $db->qn('city_fr.id'))
            ->where($db->qn('cc.fnum') . ' LIKE ' . $db->q($fnum));

        $db->setQuery($query);
        $item = $db->loadAssoc();
    } catch (Exception $e) {
        var_dump($query->__toString()).die();
        JLog::add('SQL error in emundus pdf library at query : '.$query, JLog::ERROR, 'com_emundus');
    }

    // PRO LIST
    $pro_list = '';
    try {
        $query = 'SELECT valeur, code FROM #__emundus_list_profession_insee_sise';
        $db->setQuery($query);
        $elements = array();
        foreach ($db->loadAssocList() as $profession) {
            $elements[] = '<span class="fnt-size-30 justify" style="font-weight:300;"><b style="color: #0081c5">'.$profession['code'].'</b> '.$profession['valeur'].'</span>';
        }
        $pro_list = implode(' - ', $elements);
    } catch (Exception $e) {
        JLog::add('SQL error in emundus pdf library at query : '.$query, JLog::ERROR, 'com_emundus');
    }

    //GET SISE BACS
    $bac_list = '';
    try {
        $query = 'SELECT value FROM #__emundus_sise_bac';
        $db->setQuery($query);
        $elements = array();
        foreach ($db->loadColumn()as $bac) {
            $val = explode('-',$bac);
            $elements[] = '<span class="fnt-size-30 justify" style="font-weight:300;"><b class="blue-text">'.$val[0].'</b> '.$val[1].'</span>';
        }
        $bac_list = implode(' - ', $elements);
    } catch (Exception $e) {
        JLog::add('SQL error in emundus pdf library at query : '.$query, JLog::ERROR, 'com_emundus');
    }

    // ACADEMY LIST
    $academy_list = '';
    try {
        $query = 'SELECT DISTINCT(code_acadmi) FROM #__emundus_sise_departments';
        $db->setQuery($query);
        $elements = array();
        foreach ($db->loadAssocList() as $academy) {
            $elements[] = '<span class="fnt-size-30 justify" style="font-weight:300;"><b style="color: #0081c5">'.$academy['code_acadmi'][0].$academy['code_acadmi'][1].'</b> '.substr($academy['code_acadmi'],2).'</span>';
        }
        $academy_list = implode(' - ', $elements);
    } catch (Exception $e) {
        JLog::add('SQL error in emundus pdf library at query : '.$query, JLog::ERROR, 'com_emundus');
    }

    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, 'I', PDF_FONT_SIZE_DATA));
    $pdf->SetMargins(-1, 0);
    $pdf->SetFooterMargin(5);
    $pdf->SetHeaderMargin(0);
    $pdf->SetAutoPageBreak(TRUE, 0);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->SetFont('courier', '', 10);
    $pdf->SetPrintHeader(false);
    $pdf->SetPrintFooter(true);
    $pdf->AddPage();
    $pdf->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 255, 255)));

    /*** Applicant   ***/

    // SET PDF STYLE
    $style .=
        '<style>
            .pdf-header { 
					margin:0;
					padding:0;
					border: none; 
					line-height:5px; 
					background-color: #27aae1; 
					color: white;
				}

				.section-title {
					background-color: #27aae1; 
					color: white;
					border: 1px solid #27aae1;
				}
				
				.blue-border {
					border: 1px solid #27aae1;
				}

				.black-border {
					border:1px solid #000000;
				}

				.blue-border-top {
					border-top: 1px solid #27aae1;
				}

				.fnt-size-35 {
					font-size: 35px;
				}

				.fnt-size-32 {
					font-size: 32px;
				}

				.fnt-size-30 {
					font-size: 30px;
				}

				.fnt-size-25 {
					font-size: 25px;
				}

				.ftn-size-18 {
					font-size: 18px;
				}

				.justify {
					text-align: justify;
				}

				.txt-left {
					text-align: left;
				}

				.txt-center {
					text-align: center;
				}

				.checkbox {
					width: 8px; height: auto;
				}	  

				.triangle {
					width: 8px; height: auto;
				}	  

				.page-break {
					page-break-after: always;
				}
				  
				.blue-cercle {
					text-align: center;
					line-height: 3px;
					font-size: 50px;
				}

				.logo {
					width: 175px;
					height: auto;
				}

				.mandat-logo {
					width: 150px;
					height: auto;
				}

				.big-logo {
					width: 400px;
					height: auto;
				}
			
				.title-text {
					margin: 10px 15px !important;
					padding: 10px 15px !important;
					text-align:right;
					line-height: 3px;
				}

				.courier-font {
					font-family:courier;
				}
				
				.blue-text {
					color: #27aae1;
				}

				.dossier, .inscription {
					font-size: 80px;
					line-height: 2px;
				}

				.annee-univ {
					font-size: 60px;
					line-height: 0px;
				}

				.em-years{
					font-size: 40px;
					line-height: 0px;
				}

            .spec-line-height {
					line-height: 0px;
				}
				p, td {
					letter-spacing: 0.1em;			  
				}

				.contacts {
					border-collapse: collapse;
				}

				.contacts td {
					line-height: 4px;
				}
	    </style>';

    // $pdf->SetFont('Helvetica', '', 5);
    $page1 =	$style;
    $page1 .=
        '<table class="pdf-header">
		<tr>
		<td class="header-sepeator" width="20"></td>
		<td class="logo-td" height="95" width="290">
			<img src="/images/custom/logo-esiea.png" class="logo">
		</td>
		<td class="header-sepeator"></td>
		<td class="title-text" width="260">
			<p class="spec-line-height"> </p>
			<p><b class="dossier courier-font">Dossier</b></p>
			<p><b class="inscription courier-font">D’INSCRIPTION</b><br></p>
			<p class="annee-univ courier-font">ANNÉE UNIVERSITAIRE</p>
			<p class="em-years courier-font">2020/2021</p>
		</td>
		<td class="header-sepeator" width="50"></td>
		</tr>
	</table>
   ';
    $pdf->SetFont('helvetica', '', 8);
    $page1 .='	
   <table cellpadding="6">
      <tr><th width="20"></th><th width="600" ></th></tr>
	</table>
	
	<table class="contacts" cellspacing="3">
		<tr>
			<td width="20"></td>
			<td class="c c1" width="200">
				<b class="blue-text" style="padding-top: 10px;">Service admission • </b><span>admissions@esiea.fr</span>
			</td>
			
			<td class="blue-cercle-td" width="25"><label class="blue-cercle">•</label></td>
			
			<td class="c c2" width="210">
				<b class="blue-text">Campus de Paris • </b><span>Service des Admissions</span>
            <span>74 bis av. M.Thorez • 94200 Ivry-sur-Seine</span>
			</td>
			
			<td class="blue-cercle-td" width="25"><label class="blue-cercle">•</label></td>
			
			<td class="c c3" width="210">
				<b class="blue-text">Campus de Laval • </b><span>Service des Admissions</span>
            <span>Parc universitaire Laval-Changé</span>
            <br><span>38, rue des Docteurs Calmette et Guérin</span>
            <br><span>53000 Laval</span>
			</td>
			
		</tr>
   </table>
	';

    //START ON FIRST SECTION
    $page1 .= '

	<table>
		<tr>
			<td width="20"></td>
			<td width="200" height="20" class="section-title courier-font"><div style="font-size:9px">&nbsp;</div><b> ANNÉE D’ÉTUDE (DE BAC À BAC +4)</b></td>
		</tr>
		<tr>
		<td class="header-sepeator" width="20"></td>
		<td class="blue-border" width="700" height="50">
				<table cellpadding="5">
				<tr>
					<td class="header-sepeator" width="20"></td>
					<td width="450">
						<p class="fnt-size-35">Année admis : <b class="blue-text">1re ANNÉE</b></p>
					</td>
					<td width="450"><p class="fnt-size-35">Choix du campus : <b class="blue-text">' . strtoupper($item['formation_place']) . '</b></p></td>
				</tr>
				<tr>
					<td class="header-sepeator" width="20"></td>
					<td width="480">
						<p class="fnt-size-35">N° BEA / INE : <span class="blue-text">' . $item['ine'] . '</span></p>
					</td>
				</tr>
				</table>
			
		</td>
		</tr>
	</table>
	';


    //START ON SECOND SECTION
    $page1 .= '
	<br><table>
		<tr>
		<td class="header-sepeator" width="20"></td>
			<td width="350">
				<table>
					<tr>
					<td width="200" height="20" class="section-title courier-font"><div style="font-size:9px">&nbsp;</div><b> COORDONNÉES DE L’ETUDIANT(E)</b></td>
					</tr>
					<tr>
						<td class="blue-border" width="340" height="300">
							<table cellpadding="4">
								<tr>
									<td class="fnt-size-25"><p><b>Civilité : </b>' . $item['civility'] . '</p></td>
								</tr>
								<tr>
									<td class="fnt-size-25"><p><b>Nom : </b>' . $item['last_name'] . '</p></td>
								</tr>
								<tr>
								<td class="fnt-size-25"><p><b>Prénom : </b>' . $item['first_name'] . '</p></td>
								</tr>
								<tr>
								<td></td>
								</tr>
								<tr>
									<td class="fnt-size-25"><p><b>Adresse : </b>' . $item['addresse_1'] . '</p></td>
								</tr>
								<tr>
									<td class="fnt-size-25"><p><b>CP : </b>' . $item['zipcode_1'] . '</p></td>
								</tr>	
								<tr>
									<td class="fnt-size-25"><p><b>Ville : </b>' . (!empty($item['city_1']) ? $item['fr_city'] : $item['city_other']). '</p></td>
								</tr>	
								<tr>
									<td class="fnt-size-25"><p><b>Pays : </b>' . getCountry($item['country_res']) . '</p></td>
								</tr>	
								<tr>
								<td></td>
								</tr>
								<tr>
									<td class="fnt-size-25"><p><b>Fixe ou Mobile :</b>' . $item['mobile_phone'] . '</p></td>
								</tr>	
								<tr>
									<td class="fnt-size-25"><p><b>E-mail(privé) : </b>' . $item['user_email'] . '</p></td>
								</tr>	
								<tr>
									<td class="fnt-size-25"><p><b>Date de naissance : </b>' . date("d/m/Y",strtotime($item["birth_date"])) . '</p></td>
								</tr>	
								<tr>
									<td class="fnt-size-25 "><b>Ville de naissance : </b>' . (!empty($item['city_1']) ? $item['fr_city'] : $item['city_other']). '</td>
								</tr>
								<tr>
								<td></td>
								</tr>
								<tr>
									<td class="fnt-size-25"><p><b>Nationalité : </b>' . getCountry($item['nationality']) . '</p></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>

				<br>
				
				<table>
					<tr>
					<td width="60" height="20" class="section-title courier-font"><div style="font-size:9px">&nbsp;</div><b> BOURSES</b></td>
					<td class="header-sepeator" width="10"></td>
					<td width="250" height="10" class="blue-text"><div style="font-size:5px">&nbsp;</div><i style="font-size:25px; line-height:0.8em;">Réservé au service Scolarité (ne pas remplir)</i></td>
					</tr>
					<tr>
						<td class="blue-border" width="340" height="85">
							<table cellpadding="3">
								<tr>
									<td width="5"></td>
									<td width="175">
										<table>
											<tr>
												<td width="15">
													<img class="checkbox"  src="/images/custom/'.($item['grand_classe'] == 'oui' ? "filled-box" : "BOX1").'.png" alt="checkbox">
												</td>
												<td width="150">
												<p>Grand Classé Concours Puissance Alpha</p>
												</td>
											</tr>
										</table>
									</td>
									<td>
										<p><img class="checkbox"  src="/images/custom/BOX1.png" alt="checkbox"> Bourse Mayennaise</p>
									</td>
								</tr>

								<tr>
									<td width="5"></td>
									<td width="175">
										<table>
											<tr>
												<td width="15">
													<img class="checkbox"  src="/images/custom/'.($item['bac_mention'] == 'TRES BIEN' ? "filled-box" : "BOX1").'.png" alt="checkbox">
												</td>
												<td width="150">
												<p>Mention très bien au BAC</p>
												</td>
											</tr>
										</table>
									</td>
									<td>
										<p>Code étudiant ESIEA :</p>
									</td>
								</tr>

								<tr>
									<td width="5"></td>
									<td width="175">
										<table>
											<tr>
												<td width="15">
													<img class="checkbox"  src="/images/custom/'.($item['grand_admis'] == 'oui' ? "filled-box" : "BOX1").'.png" alt="checkbox">
												</td>
												<td width="150">
												<p>Bourse d’Excellence 3A</p>
												</td>
											</tr>
										</table>
									</td>
									<td width="130" style="border-bottom:1px dashed black"></td>
								</tr>

								<tr>
									<td width="5"></td>
									<td width="175">
										<table>
											<tr>
												<td width="15">
													<img class="checkbox"  src="/images/custom/BOX1.png" alt="checkbox">
												</td>
												<td width="150">
												<p>Crous</p>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>

			<td>
				<table>
					<tr>
					<td width="200" height="20" class="section-title courier-font"><div style="font-size:9px">&nbsp;</div><b> ENGAGEMENT D’INSCRIPTION</b></td>
					<td class="header-sepeator" width="10"></td>
					<td width="150" height="10" class="blue-text"><i style="font-size:25px; line-height:0.8em;">(à compléter et à signer par l’étudiant)</i></td>
					</tr>
					<tr>
						<td class="blue-border" width="350" height="420">
						<table cellpadding="4">
							<tr>
								<td>
									<b>Je soussigné(e)</b> <span>Nom et Prénom de l’étudiant(e)</span>
									<p><b class="blue-text">' . $item['last_name'] . ' ' . $item['first_name'] . '</b></p>
									<br>
									<span>confirme mon inscription à l’ESIEA pour l’année universitaire 2020 / 2021*.</span>
									<span>Je reconnais avoir pris connaissance des conditions générales d’inscription (ci-après) et des frais de scolarité qui me sont applicables, avant déduction éventuelle des bourses ESIEA** auxquelles je pourrais prétendre.</span>
									
									<p><b class="fnt-size-35">Modalité de règlement choisie*** :</b></p>
								</td>
							</tr>

							<tr>
								<td width="10"></td>
								<td width="88">
									<p class="blue-text"><img class="checkbox"  src="/images/custom/' . ($item["multiple_payment"] == 1 ? "filled-box.png" : "BOX1.png"). '" alt="checkbox"> Modalité n°1 ></p>
								</td>
								<td width="238">
									<span>Paiement à l’inscription de 1 450 € (par virement ou cb)****, puis paiement comptant du solde avant
									le 5 septembre 2020.</span>
								</td>
							</tr>
							<tr>
								<td width="10"></td>
								<td width="88">
									<p class="blue-text"><img class="checkbox"  src="/images/custom/' . ($item["multiple_payment"] == 10 ? "filled-box.png" : "BOX1.png"). '" alt="checkbox"> Modalité n°2 ></p>
								</td>
								<td width="238">
									<span>Paiement à l’inscription de 1 450 € (par virement ou cb)****, puis paiement en 10 prélèvements mensuels.
									Je joins un RIB et un mandat SEPA.</span>
								</td>
							</tr>
							<tr>
								<td width="10"></td>
								<td width="88">
									<p class="blue-text"><img class="checkbox"  src="/images/custom/' . ($item["multiple_payment"] == 2 ? "filled-box.png" : "BOX1.png"). '" alt="checkbox"> Modalité n°3 ></p>
								</td>
								<td width="250">
									<span>Paiement à l’inscription de 1 450 € puis paiement du solde en 2 échéances (5 septembre et 5 janvier) (obligatoire pour les étudiants hors zone euro ou n’ayant pas de domiciliation bancaire en zone euro).</span>
								</td>
							</tr>

							<tr>
								<td width="10"></td>
								<td width="200">
									<b>Signature : </b>
									<div style="color: white;">signature_1</div>
								</td>
							</tr>

							<tr><td></td></tr>

							<tr>
								<td width="10"></td>
								<td>
									<table>
										<tr>
											<td width="330">
												<span class="ftn-size-18">* Merci de référencer votre virement de la manière suivante : NOM - Prénom de l’élève - année d’intégration.</span>
											</td>
										</tr>
										<tr>
											<td width="330">
												<span class="ftn-size-18">** Les bourses attribuées par l’ESIEA viennent en déduction de ce montant. Votre échéancier sera recalculé par notre service comptabilité.</span>
											</td>
										</tr>
										<tr>
											<td width="330">
												<span class="ftn-size-18">*** Voir détails en page 4.</span>
											</td>
										</tr>
										<tr>
											<td width="330">
												<span class="ftn-size-18">**** Le paiement peut se faire en ligne depuis notre site www.esiea.fr (rubrique « Admissions » puis « Frais de scolarité ») ou par virement (RIB joint).</span>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	';

    //THIRD SECTION
    $page1 .= '
	<br>
	<table>
		<tr>
			<td width="20"></td>
			<td width="130" height="20" class="section-title courier-font"><div style="font-size:9px">&nbsp;</div><b> RENSEIGNEMENTS</b></td>
		</tr>
		<tr>
		<td class="header-sepeator" width="20"></td>
		<td class="blue-border" width="700" height="80">
				<table cellpadding="3">
					<tr>
						<td width="10"></td>
						<td width="350">
							<p>Avez-vous un frère ou une soeur scolarisé(e) au sein de l’ESIEA ?</p>
						</td>
						<td width="75">
							<p><img class="checkbox"  src="/images/custom/' . ($item["link_school_sibling"]=="oui" ? "filled-box" : "BOX1"). '.png" alt="checkbox"> Oui </p>
						</td>
						<td>
							<p width="50"><img class="checkbox"  src="/images/custom/' . ($item["link_school_sibling"]=="non" ? "filled-box" : "BOX1"). '.png" alt="checkbox"> Non </p>
						</td>
					</tr>

					<tr>
						<td width="10"></td>
						<td width="680">
							<p>Si oui, précisez les nom et prénom et n° étudiant : ' . strtoupper($item["link_school_sibling_nom"]) . ' ' . $item["link_school_sibling_prenom"] .'</p>
						</td>
					</tr>

					<tr>
						<td width="10"></td>
						<td width="350">
							<p>Êtes-vous l’enfant d’un(e) diplômé(e) ?</p>
						</td>
						<td width="75">
							<p><img class="checkbox"  src="/images/custom/' . ($item["link_school_parent_alu"]=="oui" ? "filled-box" : "BOX1"). '.png" alt="checkbox"> Oui </p>
						</td>
						<td>
							<p width="50"><img class="checkbox"  src="/images/custom/' . ($item["link_school_parent_alu"]=="non" ? "filled-box" : "BOX1"). '.png" alt="checkbox"> Non </p>
						</td>
					</tr>

					<tr>
						<td width="10"></td>
						<td width="680">
							<p>Si oui, précisez les nom, prénom, année de promotion : ' . strtoupper($item["link_school_parent_alu_nom"]) . ' ' . $item["link_school_parent_alu_prenom"] .'</p>
						</td>
					</tr>
				</table>
		</td>
		</tr>
	</table>
	';

    //4TH SECTION
    $page1 .= '
	<br>
	<table>
		<tr>
			<td width="20"></td>
			<td width="500" height="20" class="section-title courier-font"><div style="font-size:9px">&nbsp;</div><b> ENGAGEMENT FINANCIER </b><span style="font-size:25px">(à compléter obligatoirement et à signer par le répondant financier)</span></td>
		</tr>
		<tr>
		<td class="header-sepeator" width="20"></td>
		<td class="blue-border" width="700" height="112">
				<table cellpadding="3">
					<tr>
						<td>
							<table cellpadding="3">
								<tr>
									<td class="header-sepeator" width="5"></td>
									<td width="320"><p><b>Je soussigné(e)</b> Nom et Prénom : ' . strtoupper($item["repondant_financier_nom_1"]) . ' ' . $item["repondant_financier_prenom_1"] .'</p></td>
								</tr>
								<tr>
									<td class="header-sepeator" width="5"></td>
									<td width="320"><p>Adresse postale : ' . $item["responsable_adresse_1"] .'</p>
									</td>
								</tr>
							</table>
						</td>
						<td>
							<p>J’ai pris connaissance des conditions générales d’inscription (ci-après) et m’engage à prendre en charge les frais de scolarité et frais annexes de l’étudiant(e) pour 2020 / 2021.</p>
							<p>Je prie les Services Financiers d’adresser les factures et correspondances liées aux frais de scolarité à mon attention.</p>
						</td>
					</tr>
					<tr>
						<td class="header-sepeator" width="8"></td>
						<td width="75" class="fnt-size-25 "><p>CP : ' . $item["repondant_financier_zipcode"] .'</p></td>
						<td width="265" class="fnt-size-25 "><p>Ville : ' . (!empty($item["repondant_financier_city"]) ? getCity($item["repondant_financier_city"]) : $item["repondant_financier_city_other"]) . '</p></td>
					</tr>	
					<tr>
						<td class="header-sepeator" width="8"></td>
						<td width="130" class="fnt-size-25 "><p>Pays : ' . getCountry($item["repondant_financier_country"]) .'</p></td>
						<td width="210" class="fnt-size-25 "><p>Fixe ou Mobile : ' . $item["repondant_financier_telephone"] .'</p></td>
					</tr>	
					<tr>
						<td class="header-sepeator" width="8"></td>
						<td width="340" class="fnt-size-25 "><p>E-mail : ' . $item["repondant_financier_email"] .'</p></td>
						<td width="125" class="fnt-size-25 "><b>Signature du répondant financier :</b><div style="color: white;">signature_2</div></td>
					</tr>	
				</table>
		</td>
		</tr>
	</table>
	';

    // WRITE FIRST PAGE
    $pdf->writeHTMLCell(0,'','',$start_y,$page1,'B', 1);

    // BUILD 2ND PAGE
    $pdf->SetMargins(-1, 10, 0, true);
    $pdf->addPage();

    $page2 = $style;
    $page2 .= '
	<table>
		<tr>
			<td width="20"></td>
			<td width="250" height="20" class="section-title courier-font"><div style="font-size:9px">&nbsp;</div><b> COORDONNÉES DU OU DES TUTEURS LÉGAUX </b></td>
		</tr>
		<tr>
		<td class="header-sepeator" width="20"></td>
		<td class="blue-border" width="700" height="950">
			<table cellpadding="5">
			<tr><td></td></tr>
			<tr>
				<td width="10"></td>
				<td width="500"><b class="blue-text fnt-size-35">TUTEUR LÉGAL 1</b></td>
			</tr>
			<tr>
				<td width="10"></td>
				<td width="250"><p class="fnt-size-35"><b>Nom : </b>' . $item["responsable_nom_1"].'</p></td>
				<td width="250"><p class="fnt-size-35"><b>Prénom : </b>' . $item["responsable_prenom_1"].'</p></td>
			</tr>
			<tr>
				<td width="10"></td>
				<td width="680"><p class="fnt-size-35"><b>Lien de parenté avec l’étudiant(e) : </b>' . $item["responable_relation_1"].'</p></td>
			</tr>
			<tr>
				<td width="10"></td>
				<td width="680"><p class="fnt-size-35"><b>Adresse : </b>' . $item["responsable_adresse_1"].'</p></td>
			</tr>
			<tr>
				<td width="10"></td>
				<td width="250"><p class="fnt-size-35"><b>CP : </b>' . $item["responsable_cp_1"].'</p></td>
				<td width="250"><p class="fnt-size-35"><b>Ville : </b>' . (!empty($item["responsable_ville_1"]) ? getCity($item["responsable_ville_1"]) : $item["responsable_ville_other_1"]) . '</p></td>
			</tr>
			<tr>
				<td width="10"></td>
				<td width="250"><p class="fnt-size-35"><b>Pays : </b>' . getCountry($item["responsable_pays_1"]) .'</p></td>
				<td width="250"><p class="fnt-size-35"><b>Fixe ou Mobile : </b>' . $item["responsable_telephone_1"].'</p></td>
			</tr>
			<tr>
				<td width="10"></td>
				<td width="680"><p class="fnt-size-35"><b>E-mail : </b>' . $item["responsable_email_1"].'</p></td>
			</tr>
			<tr>
				<td width="10"></td>
				<td width="680"><p class="fnt-size-35"><b>Nom de l’entreprise : </b>' . $item["responsable_entreprise_1"].'</p></td>
			</tr>
			<tr>
				<td width="10"></td>
				<td width="650"><p class="fnt-size-35"><b>Profession (chiffres de la codification INSEE correspondante) : </b>' . $item["responsable_profession_1"].'</p></td>
			</tr>

			<tr>
						<td width="10"></td>
						<td width="500"><b class="blue-text fnt-size-35">TUTEUR LÉGAL 2</b></td>
					</tr>
					<tr>
						<td width="10"></td>
						<td width="250"><p class="fnt-size-35"><b>Nom : </b>' . $item["responsable_nom_2"].'</p></td>
						<td width="250"><p class="fnt-size-35"><b>Prénom : </b>' . $item["responsable_prenom_2"].'</p></td>
					</tr>
					<tr>
						<td width="10"></td>
						<td width="680"><p class="fnt-size-35"><b>Lien de parenté avec l’étudiant(e) : </b>' . $item["responable_relation_2"].'</p></td>
					</tr>
					<tr>
						<td width="10"></td>
						<td width="680"><p class="fnt-size-35"><b>Adresse : </b>' . $item["responsable_adresse_2"].'</p></td>
					</tr>
					<tr>
						<td width="10"></td>
						<td width="250"><p class="fnt-size-35"><b>CP : </b>' . $item["responsable_cp_2"].'</p></td>
						<td width="250"><p class="fnt-size-35"><b>Ville : </b>' . (!empty($item["responsable_ville_2"]) ? getCity($item["responsable_ville_2"]) : $item["responsable_ville_other_2"]) . '</p></td>
					</tr>
					<tr>
						<td width="10"></td>
						<td width="250"><p class="fnt-size-35"><b>Pays : </b>' . getCountry($item["responsable_pays_2"]) .'</p></td>
						<td width="250"><p class="fnt-size-35"><b>Fixe ou Mobile : </b>' . $item["responsable_telephone_2"].'</p></td>
					</tr>
					<tr>
						<td width="10"></td>
						<td width="680"><p class="fnt-size-35"><b>E-mail : </b>' . $item["responsable_email_2"].'</p></td>
					</tr>
					<tr>
						<td width="10"></td>
						<td width="680"><p class="fnt-size-35"><b>Nom de l’entreprise : </b>' . $item["responsable_entreprise_2"].'</p></td>
					</tr>
					<tr>
						<td width="10"></td>
						<td width="650"><p class="fnt-size-35"><b>Profession (choisir, dans la liste ci-dessous, les deux chiffres de la codification INSEE correspondante) : </b>' . $item["responsable_profession_2"].'</p></td>
					</tr>

					<tr><td></td></tr>



					<tr><td height="270"></td></tr>

					<tr>
						<td width="10"></td>
						<td width="500"><p class="blue-text fnt-size-35">CODIFICATIONS INSEE</p></td>
					</tr>

					<tr>
						<td width="10"></td>
						<td width="680" class="justify">'.$pro_list.'</td>
					</tr>
			</table>
		</td>
		</tr>
	</table>';

    $pdf->writeHTMLCell(0,'','',$start_y,$page2,'B', 1);

// BUILD 3RD PAGE
    $pdf->SetMargins(-1, 10, 0, true);
    $pdf->addPage();

    $page3 = $style;

    $page3 .=
        '
<table>
	<tr>
		<td width="20"></td>
		<td width="250" height="20" class="section-title courier-font"><div style="font-size:9px">&nbsp;</div><b> FORMATION ANTÉRIEURE </b></td>
	</tr>
	
	<tr>
		<td class="header-sepeator" width="20"></td>
		<td class="blue-border" width="700" height="575">
			<table cellpadding="5">
				<tr><td></td></tr>
				<tr>
					<td width="10"></td>
					<td width="500"><b class="blue-text fnt-size-35">BACCALAURÉAT</b></td>
				</tr>
				<tr>
					<td width="10"></td>
					<td width="400"><b class="fnt-size-35">Type du baccalauréat :</b></td>
				</tr>
				<tr>
					<td width="10"></td>
					<td width="130" class="blue-text"><p class="fnt-size-35">CODE BAC : ' . $item["bac_type"] . '</p></td>
					<td width="220" class="blue-text"><p class="fnt-size-35">ANNÉE D’OBTENTION : ' . $item["annee_bac"] . '</p></td>
					<td width="180" class="blue-text"><p class="fnt-size-35">MENTION : ' . $item["bac_mention"] . '</p></td>
					<td width="120" class="blue-text"><p class="fnt-size-35">MOYENNE : ' . $item["bac_grade"] . '</p></td>
				</tr>
				<tr>
					<td width="10"></td>
					<td width="680" class="justify">'.$bac_list.'</td>
				</tr>

				<tr><td></td></tr>

				<tr>
					<td width="10"></td>
					<td width="680" class="fnt-size-30 justify"><p><b>Nom et adresse de l’établissement fréquenté : </b>' . getinstitut($item["previous_establishment"]). ' ' .$item["institut_adresse"]. '</p></td>
				</tr>

				<tr><td></td></tr>
				<tr><td></td></tr>

				<tr>
					<td width="10"></td>
					<td width="600"><b class="fnt-size-35">Académie d’obtention du baccalauréat :</b></td>
				</tr>

				<tr>
					<td width="10"></td>
					<td width="600"><p class="fnt-size-35"><span class="blue-text">ACADÉMIE</span> : ' .$item["academie_bac"]. '</p></td>
				</tr>
				<tr>
					<td width="10"></td>
					<td width="680" class="justify">'.$academy_list.'</td>
				</tr>

				<tr><td></td></tr>

				<tr>
					<td width="10"></td>
					<td width="500"><b class="blue-text fnt-size-35">ÉTUDES ANTÉRIEURES (à renseigner si différent du baccalauréat)</b></td>
				</tr>
				
				<tr>
					<td width="10"></td>
					<td width="680"><p class="fnt-size-35"><b>Cursus suivi </b>(exemple : BTS, DUT, CPGE, LICENCE...) : ' . $item["cursus_suivi_repeat"]. '</p></td>
				</tr>

				<tr>
					<td width="10"></td>
					<td width="370"><p class="fnt-size-35">Spécialité : </p></td>
					<td width="310"><p class="fnt-size-35">Mention : </p></td>
				</tr>

				<tr>
					<td width="10"></td>
					<td width="680"><p class="fnt-size-35"><b>Nom et adresse de l’établissement fréquenté : </b>'.$item["previous_establishment_repeat"]. ', ' . $item["institut_adresse_repeat"] .'</p></td>
				</tr>

				<tr>
					<td width="10"></td>
					<td width="680"><p class="fnt-size-35"><b>Année d’obtention du diplôme : </b>'.$item["fin_formation_repeat"].'</p></td>
				</tr>

			</table>
		</td>
	</tr>

	<tr><td></td></tr>
	<tr><td></td></tr>

	<tr>
		<td width="20"></td>
		<td width="250" height="20" class="section-title courier-font"><div style="font-size:9px">&nbsp;</div><b> CONDITIONS GÉNÉRALES D’INSCRIPTION </b></td>
	</tr>

	<tr>
		<td class="header-sepeator" width="20"></td>
		<td class="blue-border-top" width="700" height="250">
				<table cellpadding="5">

					<tr><td></td></tr>

					<tr>
						<td width="10"></td>
						<td width="500"><b class="blue-text fnt-size-35">1_MONTANT DES FRAIS DE SCOLARITÉ 2020 / 2021</b></td>
					</tr>

					<tr><td></td></tr>

					<tr>
						<td width="10"></td>
						<td width="650"><p class="fnt-size-30 justify">Le montant des frais de scolarité constitue un forfait annuel, comprenant les frais d’inscription, d’études, de polycopies, de documentation, de laboratoire, d’accès illimité à Internet, d’adhésion à l’Association des Étudiants, d’un examen de TOEIC par an et d’assurance “accident du travail scolaire”.</p></td>
					</tr>

					<tr>
						<td width="10"></td>
						<td width="650"><p class="fnt-size-30 justify">Dès réception des résultats d’admission, l’étudiant(e) dispose d’un délai de quinze jours pour confirmer son inscription par retour du dossier d’inscription, accompagné du règlement des frais de dossier, selon les modalités de règlement choisies. La place est alors réservée jusqu’au 5 septembre 2020.</p></td>
					</tr>

				</table>
		</td>
	</tr>
</table>';


    $pdf->writeHTMLCell(0,'','',$start_y,$page3,'B', 1);


// BUILD 4TH PAGE
    $pdf->SetMargins(10, 10, 10, true);
    $pdf->addPage();

    $page4 = $style;

    $page4 .=
        '
<table cellpadding="5">
	<tr>
		<td><p class="blue-text fnt-size-35">CHOIX DE LA MODALITÉ DE PAIEMENT POUR LE RÈGLEMENT DES FRAIS DE SCOLARITÉ :</p></td>
	</tr>
	<tr><td></td></tr>
	<tr>
		<td>
				<table cellpadding="5">
					<tr>
						<td class="blue-border txt-left"><br><b>Frais de scolarité = frais de dossier + tarif annuel</br></td>
						<td class="blue-border txt-center"><b class="blue-text">MODALITÉ 1</b><br><b>au comptant</br></td>
						<td class="blue-border txt-center"><b class="blue-text">MODALITÉ 2 *</b><br><b>règlement en 10 fois</b><br><b>par prélèvement automatique</b></td>
						<td class="blue-border txt-center"><b class="blue-text">MODALITÉ 3 *</b><br><b>règlement en 3 fois</b><br><b>uniquement par virement</b></td>
					</tr>
					<tr>
						<td class="blue-border txt-left"><p>Paiement à l’inscription</p></td>
						<td class="blue-border txt-center"><p>1 450 €</p></td>
						<td class="blue-border txt-center"><p>1 450 €</p></td>
						<td class="blue-border txt-center"><p>1 450 €</p></td>
					</tr>
					<tr>
						<td class="blue-border txt-left"><p>Tarif annuel de la 1re année</p></td>
						<td class="blue-border txt-center"><p>5 680 €</p></td>
						<td class="blue-border txt-center"><p>581 € x 10</p></td>
						<td class="blue-border txt-center"><p>2 905 € x 2</p></td>
					</tr>
					<tr>
						<td class="blue-border txt-left"><p>Tarif annuel de la 2e et de la 3e année</p></td>
						<td class="blue-border txt-center"><p>6 710 €</p></td>
						<td class="blue-border txt-center"><p>686 € x 10</p></td>
						<td class="blue-border txt-center"><p>3 430 € x 2</p></td>
					</tr>
					<tr>
						<td class="blue-border txt-left"><p>Tarif annuel de la 4e et de la 5e année</p></td>
						<td class="blue-border txt-center"><p>6 850 €</p></td>
						<td class="blue-border txt-center"><p>700 € x 10</p></td>
						<td class="blue-border txt-center"><p>3 500 € x 2</p></td>
					</tr>
					<tr>
						<td class="blue-border txt-left"><p>Commentaires sur les modalités de paiement</p></td>
						<td class="blue-border txt-center"><p>Un paiement au-delà du<br>5 septembre 2020 entraine <br>l’application de la majoration.</p></td>
						<td class="blue-border txt-center"><p>10 prélèvements à compter du<br> 5 septembre 2020. Toute échéance de prélèvement en retard se cumule avec l’échéance suivante.<br> Les prélèvements rejetés ne sont<br> pas cumulables.</p></td>
						<td class="blue-border txt-center"><p>2 échéances : 5 septembre 2020 et 5 janvier 2021. Modalité obligatoire pour tout étudiant hors zone euro ou ne disposant pas d’une domiciliation bancaire en zone euro</p></td>
					</tr>
				</table>
		</td>
	</tr>

	<tr>
		<td><i><span class="blue-text">*</span> Cette modalité entraîne une majoration des frais de scolarité (130 € en 1re année et 150 € les autres années du cursus).</i></td>
	</tr>
	<tr>
		<td><p class="fnt-size-32">Les modes de paiement acceptés à l’ESIEA sont : prélèvement bancaire, virement bancaire et paiement en ligne par carte bancaire.</p></td>
	</tr>
	<tr>
		<td><p class="fnt-size-30">En cas de rejet du paiement, des frais de gestion de 25 € viendront majorer les sommes dues.</p></td>
	</tr>
	<tr>
		<td><p class="fnt-size-30">Les étudiants bénéficiaires d’une bourse d’État, d’une collectivité ou d’Ambassade doivent joindre un courrier à leur dossier d’inscription, précisant l’organisme attributaire de la bourse et la date éventuelle de versement.</p></td>
	</tr>

	<tr><td></td></tr>

	<tr>
		<td><b class="blue-text fnt-size-35">2_RÉSILIATION DE L’INSCRIPTION</b></td>
	</tr>

	<tr>
		<td class="justify"><p>L’ESIEA accepte les résiliations d’inscription effectuées par lettre recommandée, adressée au Directeur du campus. Seuls les signataires du présent document d’inscription peuvent décider de la résiliation.</p></td>
	</tr>

	<tr>
		<td><b>Quand la résiliation d’inscription intervient avant le début des cours, les dispositions suivantes sont applicables :</b></td>
	</tr>

	<tr>
		<td width="20"></td>
		<td width="20"><img class="triangle" src="/images/custom/triangle.png"></td>
		<td width="610" class="justify"><p>L’ESIEA accepte les résiliations d’inscription effectuées par lettre recommandée, adressée au Directeur du campus. Seuls les signataires du présent document d’inscription peuvent décider de la résiliation.</p></td>
	</tr>

	<tr>
		<td width="20"></td>
		<td width="20"><img class="triangle" src="/images/custom/triangle.png"></td>
		<td width="610" class="justify"><p>pour cause de non obtention du visa, <b>sur présentation du justificatif nominatif reçu,</b> l’acompte et les frais de dossier sont intégralement remboursés par l’ESIEA.</p></td>
	</tr>
	
	<tr>
		<td width="20"></td>
		<td width="20"><img class="triangle" src="/images/custom/triangle.png"></td>
		<td width="610" class="justify"><p>dans tout autre cas, les frais de dossier de 1 450 €, restent acquis à l’ESIEA ; les autres sommes éventuellement versées sont remboursées.</p></td>
	</tr>

	<tr>
		<td width="650">
			<b>Au-delà du début des cours, la résiliation d’inscription a pour conséquence l’interruption partielle ou définitive de la scolarité.</b>
		</td>
	</tr>

	<tr><td></td></tr>

	<tr>
		<td><b class="blue-text fnt-size-35">3_INTERRUPTION PARTIELLE OU DÉFINITIVE DE LA SCOLARITÉ</b></td>
	</tr>

	<tr><td><p>Les signataires du présent document d’inscription peuvent décider de l’interruption partielle ou définitive de la scolarité par lettre recommandée, adressée au Directeur du campus.</p></td></tr>

	<tr><td><p>Dans tous les cas d’interruption de la scolarité, définitive ou simplement suspendue, <b>tout semestre engagé reste dû ; les frais de dossier restent intégralement acquis à l’ESIEA ; la date de référence est la date de distribution du courrier recommandé AR demandant l’interruption.</b></p></td></tr>

	<tr><td><p>Le non-respect du Règlement des Études, dont un exemplaire est remis, peut entraîner des sanctions allant jusqu’à l’exclusion définitive. Les frais de scolarité dus sont calculés selon les mêmes dispositions.</p></td></tr>

	<tr><td><b>Dans tous les cas d’interruption, les sommes dues deviennent immédiatement exigibles.</b></td></tr>
</table>
';

    $pdf->writeHTMLCell(0,'','',$start_y,$page4,'B', 1);

// BUILD 5TH PAGE
    $pdf->SetMargins(10, 10, 10, true);
    $pdf->addPage();

    $page5 = $style;

    $page5 .= '
				<table cellpadding="5">
					<tr>
						<td width="370" height="20" class="section-title courier-font fnt-size-35"><div style="font-size:9px">&nbsp;</div><b> ANNEXE AUX CONDITIONS GÉNÉRALES D’INSCRIPTION</b></td>
					</tr>
					<tr>
						<td class="blue-border fnt-size-32" width="670">
							<table cellpadding="5">
								<tr>
									<td width="10"></td>
									<td width="30"><b>1_</b></td>
									<td width="600" class="justify">
										<p>Pour valider son inscription à l’ESIEA, l’étudiant doit obligatoirement verser des frais de dossier de 1 450 €. L’inscription définitive n’est acquise qu’après encaissement des frais de dossier qui seront automatiquement déduits des sommes restant dues pour le paiement de la scolarité.<br>En cas de paiement par prélèvements, l’inscription doit être accompagnée d’un mandat SEPA et d’un RIB (sauf étudiants hors zone euro).</p>
									</td>
								</tr>

								<tr>
									<td width="10"></td>
									<td width="30"><b>2_</b></td>
									<td width="600" class="justify">
									<p>En confirmation de l’inscription définitive, l’étudiant recevra un courrier ainsi que la facture des frais de scolarité correspondants. Elle garantit une place dans l’école pour la rentrée 2020. Elle autorise l’école à utiliser l’image (photo ou vidéo) de l’étudiant dans le cadre des enseignements dispensés ou de la promotion du Groupe.</p>
									</td>
								</tr>

								<tr>
									<td width="10"></td>
									<td width="30"><b>3_</b></td>
									<td width="600" class="justify">
										<p>Les frais de scolarité couvrent forfaitairement l’ensemble des activités scolaires des étudiants pendant le semestre concerné. Ils doivent être impérativement acquittés aux dates prévues sur l’échéancier, sous peine de sanctions pouvant entraîner l’exclusion de l’étudiant. En cas de retard de paiement ou d’impayé, les frais de relance ou de recouvrement par huissier seront facturés.</p>
									</td>
								</tr>

								<tr>
									<td width="10"></td>
									<td width="30"><b>4_</b></td>
									<td width="600" class="justify">
										<p>Chaque étudiant doit obligatoirement fournir chaque année la confirmation de son adresse et de l’adresse de son répondant financier et, sur demande de l’école, une photo d’identité récente. Conformément à la loi sur l’utilisation des données à caractère personnel, ces informations ne pourront être utilisées que dans le but de gestion de la scolarité de l’étudiant.</p>
									</td>
								</tr>

								<tr>
									<td width="10"></td>
									<td width="30"><b>5_</b></td>
									<td width="600" class="justify">
										<p>Les dégradations, volontaires ou non, sont à la charge de l’étudiant concerné, quelles que soient les sanctions disciplinaires prises par ailleurs. Lorsque le fautif n’est pas identifié, les étudiants sont déclarés solidairement responsables.</p>
									</td>
								</tr>

								<tr>
									<td width="10"></td>
									<td width="30"><b>6_</b></td>
									<td width="600" class="justify">
										<p>Les programmes des cours et les pédagogies mises en œuvre sont construits par l’école en étroite coordination avec le Conseil Scientifique ou le Conseil de Perfectionnement. Ils peuvent donc évoluer rapidement. Les notices, dépliants, programmes, ... édités et diffusés par l’école sont distribués à titre indicatif, et n’ont pas de caractère contractuel.</p>
									</td>
								</tr>

							</table>
						</td>
					</tr>

					<tr><td></td></tr>
					<tr>
						<td class="fnt-size-32">
							<b>Toute inscription implique l’acceptation sans réserve des conditions ci-dessus.</b>
						</td>
					</tr>

					<tr>
						<td class="fnt-size-32">
							<p>L’étudiant(e) et son répondant financier reconnaissent avoir pris connaissance des dispositions du présent dossier d’inscription et s’engagent solidairement à assurer le paiement intégral des frais de scolarité 2020 / 2021.</p>
						</td>
					</tr>

					<tr>
						<td class="fnt-size-32">
							<p>Selon le Règlement Général sur la Protection des Données personnelles, vous consentez à ce que les informations recueillies sur ce dossier soient enregistrées dans un fichier informatisé qui sera conservé pendant 10 ans.<br>Le responsable de traitement est le directeur de l’ESIEA et les données sont destinées aux services chargés de la gestion étudiante tout au long de la scolarité. Conformément à la loi “Informatique et Libertés”, vous pouvez exercer votre droit d’accès aux données vous concernant et les faire rectifier ou supprimer en contactant le service Scolarité <b>scolarite-paris@esiea.fr.</b></p>
						</td>
					</tr>

					<tr><td></td></tr>

					<tr>
						<td class="fnt-size-32">
							<b>L’étudiant(e) ou son représentant légal ainsi que son répondant financier reconnaissent avoir pris connaissance des dispositions du présent dossier et des divers règlements et chartes et les accepter sans réserve. Cette acceptation pleine et entière est matérialisée par leur signature ci-après :</b>
							<ul>
								<li>Conditions générales d’inscription r(reprises également dans le dossier)</li>
								<li>Règlement des études</li>
								<li>Règlement intérieur</li>
								<li>Politique RGPD</li>
								<li>Charte informatique</li>
								<li>Equipement informatique personnel</li>
							</ul>
						</td>
					</tr>

					<tr><td></td></tr>

					<tr>
						<td width="330" class="fnt-size-32"><p><b>Signature obligatoire</b><br>de l\'étudiant(e)</p><div style="color: white;">signature_1</div></td>
						<td width="330" class="fnt-size-32"><p><b>Signature obligatoire</b><br>du répondant financier</p><div style="color: white;">signature_2</div></td>
					</tr>
				</table>
';

    $pdf->writeHTMLCell(0,'','',$start_y,$page5,'', 1);


// BUILD 6TH PAGE
    /*
    $pdf->SetMargins(10, 10, 10, true);
    $pdf->addPage();

    $page6 = $style;

    $page6 .= '

                    <table cellpadding="5">
                        <tr>
                            <td width="300" height="20" class="section-title courier-font fnt-size-35"><div style="font-size:9px">&nbsp;</div><b> DOCUMENTS À JOINDRE</b></td>
                        </tr>
                        <tr>
                            <td class="blue-border fnt-size-32" width="670">
                                <table cellpadding="5">
                                    <tr><td></td></tr>
                                    <tr>
                                        <td><p>• Votre justificatif de paiement dans le cas d’un virement ou paiement par CB.</p></td>
                                    </tr>

                                    <tr><td></td></tr>

                                    <tr>
                                        <td><p>• Le paiement peut se faire en ligne depuis notre site <span class="blue-text">www.esiea.fr</span> (rubrique <b class="blue-text">« Admissions »</b> puis <b class="blue-text">« Frais de scolarité »</b>) ou par virement (RIB joint).</p></td>
                                    </tr>

                                    <tr><td></td></tr>

                                    <tr>
                                        <td><p>• Une photocopie de votre pièce d’identité (ou titre de séjour).</p></td>
                                    </tr>

                                    <tr><td></td></tr>

                                    <tr>
                                        <td><p>• Une photocopie de la pièce d’identité du répondant financier si celui-ci est différent (copie recto-verso).</p></td>
                                    </tr>

                                    <tr><td></td></tr>

                                    <tr>
                                        <td><p>• Une photo d’identité au format <b>JPG (taille 32 x 30 mm)</b> qui sera utilisée pour votre future carte étudiante (à envoyer par mail à contact@esiea.fr). L’intitulé du fichier JPG doit comporter vos NOM, Prénom, Année d’inscription, Campus choisi selon le modèle suivant : <b>DUPONT - Marie – 1A PARIS.jpg.</b></p></td>
                                    </tr>

                                </table>
                            </td>
                        </tr>
                    </table>

    ';

    $pdf->writeHTMLCell(0,'','',$start_y,$page6,'', 1);
    */

// ONLY BUILD 7th PAGE IF MODALITY x10
    if ($item["multiple_payment"] == 10) {
// BUILD 7TH PAGE
        $pdf->SetMargins(10, 10, 10, true);
        $pdf->addPage();

        $page7 = $style;
        $page7 .= '

				<table cellpadding="5">
					<tr>
						<td class="black-border fnt-size-32" width="670">
							<table cellpadding="5">
							<tr>
								<td class="txt-center" width="500">
									<table>
									<tr><td></td></tr>
									<tr><td></td></tr>
									<tr><td><h1>MANDAT DE PRELEVEMENT</h1></td></tr>
									</table>
								</td>
								<td width="150"><img class="mandat-logo" src="images/custom/bleu-gris-sans-fond.png"></td>
							</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td><p>En signant ce formulaire de mandat, vous autorisez (A)</p></td>
					</tr>
					<tr>
						<td style="border-bottom:1px solid black;"><p>Veuillez compléter les champs marqués *</p></td>
					</tr>
					<tr>
						<td>
							<p cla>Votre nom : ' . strtoupper($item["repondant_financier_nom_1"]) . ' ' . $item["repondant_financier_prenom_1"] .'</p>
						</td>
					</tr>

					<tr>
						<td>
							<p>Votre adresse : ' . $item["responsable_adresse_1"] .'</p>
						</td>
					</tr>
					<tr>
						<td width="250">
							<p>Code postale : ' . $item["repondant_financier_zipcode"] .'</p>
						</td>
						<td width="550">
							<p>Ville : ' . (!empty($item["repondant_financier_city"]) ? getCity($item["repondant_financier_city"]) : $item["repondant_financier_city_other"]) . '</p>
						</td>
					</tr>
					<tr>
						<td>
							<p>Pays : ' . getCountry($item["repondant_financier_country"]) .'</p>
						</td>
					</tr>

					<tr>
						<td>
							<p>Les coordonnées de votre compte : </p>
						</td>
					</tr>

					<tr>
						<td width="680">
							<p>IBAN : ' . $item["iban"]. '</p>
						</td>
					</tr>

					<tr>
						<td width="680">
							<p>BIC : ' . $item["bic"]. '</p>
						</td>
					</tr>

					<tr>
						<td width="100">
							<p>Nom du créancier : </p>
						</td>
						<td>GROUPE ESIEA</td>
					</tr>

					<tr>
						<td width="50">
							<p>I. C. S : </p>
						</td>
						<td width="400">
							<p>F R 1 4 Z Z Z 3 9 9 2 0 4</p>
						</td>
					</tr>

					<tr>
						<td width="120">
							<p>Adress du créancier :</p>
						</td>
						<td width="400">
							<p>9 RUE VESALE<br>75005	PARIS<br>FRANCE</p>
						</td>
					</tr>

					<tr>
						<td width="450">
							<p>Type de paiement : Paiement récurrent /répétitif</p>
						</td>

					</tr>

					<tr><td></td></tr>

					<tr>
						<td width="330"><p>Fait à : ................................................................................................</p></td>
						<td width="330"><p>le : ................................................................................................</p></td>
					</tr>

					<tr><td></td></tr>

					<tr>
						<td width="330">
						<p>Signature(s)</p>
						</td>
					</tr>

					<tr>
						<td width="330">
							<div style="color: white;">signature_1</div>
						</td>
						<td width="330">
							<div style="color: white;">signature_2</div>
						</td>
					</tr>

					<tr><td></td></tr>
				</table>

';

        $pdf->writeHTMLCell(0,'','',$start_y,$page7,'B', 1);
    }


// BUILD 8TH PAGE
    $pdf->SetMargins(10, 40, 10, true);
    $pdf->addPage();

    $page8 = $style;


    $page8 .=
        '
				<table>
					<tr>
						<td>
							<img src="images/custom/identite_bancaire.png">
						</td>
					</tr>
				</table>
';

    $pdf->writeHTMLCell(0,'','',$start_y,$page8,'B', 1);


// BUILD 9TH PAGE
    $pdf->SetMargins(-1, 0, -1, true);
    $pdf->SetAutoPageBreak(FALSE);
    $font_size = $pdf->pixelsToUnits('50');
    $pdf->SetFont ('courier', '', $font_size , '', 'default', true );
    $pdf->addPage();

    $page9 = $style;


    $page9 .=
        '
				<table class="section-title" cellpadding="20" >
					<tr>
						<td width="100%" height="1100">
							<table >

								<tr><td height="100"></td></tr>

								<tr>
									<td width="150"></td>
									<td width="400" class="txt-center">
										<img class="big-logo" src="images/custom/blanc_sans_fond.png" width="300">
									</td>
									<td width="150"></td>
								</tr>

								<tr><td height="60"></td></tr>

								<tr>
									<td width="150"></td>
									<td width="400" class="txt-center courier-font" >
										<p>CAMPUS DE PARIS</p>
									</td>
									<td width="150"></td>
								</tr>

								<tr>
									<td width="150"></td>
									<td width="400" class="txt-center courier-font">
										<p><img src="images/custom/pause-solid.png" width="10"></p>
									</td>
									<td width="150"></td>
								</tr>

								<tr >
									<td width="150"></td>
									<td width="400" class="txt-center courier-font" >
										<p>9, RUE VÉSALE – 75005 PARIS</p>
									</td>
									<td width="150"></td>
								</tr>

								<tr>
									<td width="150"></td>
									<td width="400" class="txt-center courier-font" >
										<p><img src="images/custom/pause-solid.png" width="10"></p>
									</td>
									<td width="150"></td>
								</tr>

								<tr >
									<td width="150"></td>
									<td width="400" class="txt-center courier-font" >
										<p>74 BIS, AVENUE MAURICE THOREZ<br>94200 IVRY-SUR-SEINE<br>TÉL : +33 (0)1 82 39 25 00</p>
									</td>
									<td width="150"></td>
								</tr>

								<tr><td height="60"></td></tr>

								<tr>
									<td width="150"></td>
									<td width="400" class="txt-center courier-font" >
										<p>CAMPUS DE LAVAL</p>
									</td>
									<td width="150"></td>
								</tr>

								<tr>
									<td width="150"></td>
									<td width="400" class="txt-center courier-font" >
										<p><img src="images/custom/pause-solid.png" width="10"></p>
									</td>
									<td width="150"></td>
								</tr>

								<tr>
									<td width="150"></td>
									<td width="400" class="txt-center courier-font" >
										<p>PARC UNIVERSITAIRE LAVAL-CHANGÉ<br>38, RUE DES DOCTEURS CALMETTE ET GUÉRIN<br>53000 LAVAL<br>TÉL : +33 (0)2 43 59 46 15</p>
									</td>
									<td width="150"></td>
								</tr>

								<tr><td height="60"></td></tr>

								<tr>
									<td width="150"></td>
									<td width="400" class="txt-center courier-font">
										<p>ESIEA.FR</p>
									</td>
									<td width="150"></td>
								</tr>

								<tr>
									<td width="150"></td>
									<td width="400" class="txt-center courier-font">
										<p><img src="images/custom/pause-solid.png" width="10"></p>
									</td>
									<td width="150"></td>
								</tr>

								<tr>
									<td width="150"></td>
									<td width="400" class="txt-center courier-font">
										<p>ADMISSIONS@ESIEA.FR</p>
									</td>
									<td width="150"></td>
								</tr>

								<tr><td height="60"></td></tr>
								
								<tr>
									<td width="100" class="txt-center"></td>
									<td width="100" class="txt-center">
										<p><img src="images/custom/cti-logo-baseline-blanc.png" width="50"></p>
									</td>
									<td width="100" class="txt-center">
										<p><img src="images/custom/CGE-responsiveRVB_blanc.png" width="50"></p>
									</td>
									<td width="100" class="txt-center">
										<p><img src="images/custom/logo_ugei_blanc.png" width="50"></p>
									</td>
									<td width="100" class="txt-center">
										<p><img src="images/custom/secnumedu_logo-300x205_blanc.png" width="50"></p>
									</td>
									<td width="100" class="txt-center">
										<p><img src="images/custom/Label_EESPIG_blanc.png" width="50"></p>
									</td>
								</tr>
								
							</table>
							
						</td>
					</tr>
				</table>
';

    $pdf->writeHTMLCell(0,'','',$start_y,$page9,'B', 1);


    if (!file_exists(EMUNDUS_PATH_ABS.@$item['user_id'])) {
        mkdir(EMUNDUS_PATH_ABS.$item['user_id'], 0777, true);
        chmod(EMUNDUS_PATH_ABS.$item['user_id'], 0777);
    }

    @chdir('tmp');
    if ($output) {
        if (!isset($current_user->applicant) && @$current_user->applicant != 1) {
            $name = 'application_form_'.date('Y-m-d_H-i-s').'.pdf';
            $pdf->Output(EMUNDUS_PATH_ABS.$item['user'].DS.$name, 'FI');
            $attachment = $m_application->getAttachmentByLbl("_application_form");
            $keys 	= array('user_id', 'attachment_id', 'filename', 'description', 'can_be_deleted', 'can_be_viewed', 'campaign_id', 'fnum' );
            $values = array($item['user'], $attachment['id'], $name, $item['training'].' '.date('Y-m-d H:i:s'), 0, 0, $campaign_id, $fnum);
            $data 	= array('key' => $keys, 'value' => $values);
            $m_application->uploadAttachment($data);
        } else {
            $pdf->Output(EMUNDUS_PATH_ABS.@$item['user'].DS.$fnum.$file_lbl.'.pdf', 'FI');
        }
    } else {
        $pdf->Output(EMUNDUS_PATH_ABS.@$item['user'].DS.$fnum.$file_lbl.'.pdf', 'F');
    }
}



/** Generate a PDF file from HTML.
 * This is a general function which takes an HTML string and builds a PDF from it.
 *
 * @param String $html The HTML to generate the pdf file from.
 * @param String $path The path to export the file to, if none is supplied a path will be generated.
 * @param String $footer HTML for the footer of the PDF.
 * @return String The path to the generated PDF or false if export fails.
 */
function generatePDFfromHTML($html, $path = null, $footer = '') {

    set_time_limit(0);
    require_once (JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'config'.DS.'lang'.DS.'eng.php');
    require_once (JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'tcpdf.php');

    $db = JFactory::getDBO();
    $config = JFactory::getConfig();
    $app = JFactory::getApplication();

    $files = array();


    if (class_exists('MYPDF') === false || !class_exists('MYPDF')) {
        // Extend the TCPDF class to create custom Header and Footer
        class MYPDF extends TCPDF {

            var $logo = "";
            var $logo_footer = "";
            var $footer = "";

            //Page header
            public function Header() {
                // Logo
                if (is_file($this->logo))
                    $this->Image($this->logo, 0, 0, 200, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                // Set font
                $this->SetFont('courier', 'B', 16);
                // Title
                $this->Cell(0, 0, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            }

            // Page footer
            public function Footer() {
                // Position at 15 mm from bottom
                $this->SetY(-15);
                // Set font
                $this->SetFont('courier', 'I', 8);
                // footer
                $this->writeHTMLCell($w=0, $h=10, $x='', $y=260, $this->footer.' Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages().'</p>', $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
                //logo
                if (is_file($this->logo_footer))
                    $this->Image($this->logo_footer, 150, 280, 40, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);

                // Page number
                $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
            }
        }
    }

    $error = 0;

    // Generate a random file name in case one isn't supplied.
    if (empty($path))
        $path = DS.'images'.DS.'emundus'.DS.'pdf'.substr(md5(microtime()),rand(0,26),5).'.pdf';

    if (!file_exists(dirname(JPATH_BASE.$path))) {
        mkdir(dirname(JPATH_BASE.$path), 0755, true);
        chmod(dirname(JPATH_BASE.$path), 0755);
    }

    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor(PDF_AUTHOR);
    $pdf->SetTitle(basename(JPATH_BASE.$path));
    $pdf->footer = $footer;
    // set margins
    //$pdf->SetMargins(5, 10, 5);

    $pdf->SetAutoPageBreak(true, 50);
    $pdf->SetFont('courier', '', 8);
    $pdf->AddPage();

    $pdf->writeHTMLCell($w=0, $h=30, $x='', $y=10, $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
    @chdir('tmp');
    $pdf->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(255, 255, 255)));

    $pdf->Output(JPATH_BASE.$path, 'F');

    if ($error == 0)
        return $path;
    else
        return false;

}
