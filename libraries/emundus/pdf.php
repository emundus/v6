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

// @description Generate the letter result
// @params Applicant user ID
// @params Eligibility ID of the evaluation
// @params Code of the programme
// @params Type of output

function letter_pdf ($user_id, $eligibility, $training, $campaign_id, $evaluation_id, $output = true, $fnum = null) { 
	set_time_limit(0);
	require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'config'.DS.'lang'.DS.'eng.php');
	require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'tcpdf.php');
	include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
	include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'evaluation.php');
	include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
	include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');

	$current_user =  JFactory::getUser();
	$user =  JFactory::getUser($user_id);
	$db = JFactory::getDBO();

	$files = array();

	/*$query = "SELECT * FROM #__emundus_setup_letters WHERE eligibility=".$eligibility." AND training=".$db->Quote($training);
	$db->setQuery($query);
	$letters = $db->loadAssocList();*/
	$evaluations = new EmundusModelEvaluation;
	$letters = $evaluations->getLettersTemplate($eligibility, $training);

	/*$query = "SELECT * FROM #__emundus_setup_teaching_unity WHERE id = (select training_id from #__emundus_training_174_repeat where applicant_id=".$user_id." and campaign_id=".$campaign_id.") ORDER BY date_start ASC";
	$db->setQuery($query);
	$courses = $db->loadAssocList();
	*/
	$query = "SELECT * FROM #__emundus_setup_teaching_unity WHERE published=1 AND date_start>NOW() AND code=".$db->Quote($training). " ORDER BY date_start ASC";
	$db->setQuery($query);
	$courses = $db->loadAssocList();

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

	$campaigns = new EmundusModelCampaign;
	$campaign = $campaigns->getCampaignByID($campaign_id);

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
			$this->SetFont('helvetica', 'B', 16);
			// Title
			$this->Cell(0, 15, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
		}

		// Page footer
		public function Footer() {
			// Position at 15 mm from bottom
			$this->SetY(-15);
			// Set font
			$this->SetFont('helvetica', 'I', 8);
			// Page number
			$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
			// footer
			$this->writeHTMLCell($w=0, $h=0, $x='', $y=250, $this->footer, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
			//logo
			if (is_file($this->logo_footer))
				$this->Image($this->logo_footer, 150, 280, 40, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
			
		}
	}
	$emails = new EmundusModelEmails;
	$evaluations = new EmundusModelEvaluation;

	//
	// Evaluation result
	//
	if ($evaluation_id>0) {
		$evaluation = $evaluations->getEvaluationByID($evaluation_id);
		$reason = $evaluations->getEvaluationReasons();
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
		$evaluation_details = @EmundusHelperList::getElementsDetailsByName('"'.implode('","', array_keys($evaluation[0])).'"');

		$result = "";
		foreach ($evaluation_details as $ed) {
			if($ed->hidden==0 && $ed->published==1 && $ed->tab_name=="jos_emundus_evaluations") {
				//$result .= '<br>'.$ed->element_label.' : ';
				if($ed->element_name=="reason") {
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
		$applications = new EmundusModelApplication;
		$attachment = $applications->getAttachmentByID($letter['attachment_id']);
		
		/*$query = "SELECT * FROM #__emundus_setup_attachments WHERE id=".$letter['attachment_id'];
		$db->setQuery($query);
		$attachment = $db->loadAssoc();*/

		// Test if letter type has already been created for that user/campaign/attachment and delete before if true.
		$query = 'SELECT * FROM #__emundus_uploads WHERE user_id='.$user_id.' AND attachment_id='.$letter['attachment_id'].' AND campaign_id='.$campaign_id. ' AND fnum like '.$db->Quote($fnum);
		$db->setQuery($query);
		$file = $db->loadAssoc();
		// test if directory exist
		if (!file_exists(EMUNDUS_PATH_ABS.$user_id)) {
			mkdir(EMUNDUS_PATH_ABS.$user_id, 0755, true);
			chmod(EMUNDUS_PATH_ABS.$user_id, 0755);
		}
		if(count($file) > 0 && strpos($file['filename'], 'lock')===false) {
			$query = 'DELETE FROM #__emundus_uploads WHERE user_id='.$user_id.' AND attachment_id='.$letter['attachment_id'].' AND campaign_id='.$campaign_id. ' AND fnum like '.$db->Quote($fnum).' AND filename NOT LIKE "%lock%"';
			$db->setQuery($query);
			$db->query();

			@unlink(EMUNDUS_PATH_ABS.$user_id.DS.$file['filename']);
		}

		if($letter['template_type'] == 1) { // Static file
			$file_path = explode(DS, $letter['file']);
			$file_type = explode('.', $file_path[count($file_path)-1]);
			$name = $attachment['lbl'].'_'.date('Y-m-d_H-i-s').'.'.$file_type[1];
			if(file_exists(JPATH_BASE.$letter['file'])) {
				$path = EMUNDUS_PATH_ABS.$user_id.DS.$name;
				$url  = EMUNDUS_PATH_REL.$user_id.'/'.$name;
				copy(JPATH_BASE.$letter['file'], $path);
			} else {
				JFactory::getApplication()->enqueueMessage($name.' - '.JText::_("TEMPLATE_FILE_MISSING").' : '.JPATH_BASE.$letter['file'], 'error');  
				$error++;
			}

		} elseif($letter['template_type'] == 3) { // Template file .docx
			$tags = $emails->setTagsWord($user_id, $post, $fnum);
			require_once JPATH_LIBRARIES.DS.'PHPWord.php';

			$file_path = explode(DS, $letter['file']);
			$file_type = explode('.', $file_path[count($file_path)-1]);
			$name = $attachment['lbl'].'_'.date('Y-m-d_H-i-s').'.'.$file_type[1];
			if(file_exists(JPATH_BASE.$letter['file'])) {
				$PHPWord = new PHPWord();
				$document = $PHPWord->loadTemplate(JPATH_BASE.$letter['file']);
				for($i=0 ; $i<count($tags['patterns']) ; $i++) {
					$document->setValue($tags['patterns'][$i], $tags['replacements'][$i]);
					//echo $tags['patterns'][$i]." - ".$tags['replacements'][$i]."<br>";
				}
				$path = EMUNDUS_PATH_ABS.$user_id.DS.$name;
				$url  = EMUNDUS_PATH_REL.$user_id.'/'.$name;

				$document->save($path);
				unset($document);
			} else {
				JFactory::getApplication()->enqueueMessage($name.' - '.JText::_("TEMPLATE_FILE_MISSING").' : '.JPATH_BASE.$letter['file'], 'error');  
				$error++;
			}

		} else { // From HTML : $letter['template_type'] == 2
			$tags = $emails->setTags($user_id, $post, $fnum);
			$htmldata = "";
			$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor($current_user->name);
			$pdf->SetTitle($letter['title']);

			// set margins
			$pdf->SetMargins(5, 40, 5);
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

			$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
			//$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
			$pdf->SetFont('helvetica', '', 8);

			//$dimensions = $pdf->getPageDimensions();

			//$htmldata .= $letter["header"];
			$htmldata .= preg_replace($tags['patterns'], $tags['replacements'], preg_replace("/<span[^>]+\>/i", "", preg_replace("/<\/span\>/i", "", preg_replace("/<br[^>]+\>/i", "<br>", $letter["body"])))); 
			//$htmldata .= $letter["footer"];
			//die($htmldata);
			$pdf->AddPage();

			// Print text using writeHTMLCell()
			$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $htmldata, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);

			@chdir('tmp');

            $name = $attachment['lbl'].'_'.date('Y-m-d_H-i-s').'.pdf';
			if($output){
				//$output?'FI':'F'
				$pdf->Output(EMUNDUS_PATH_ABS.$user_id.DS.$name, $output);

			}else{
				$pdf->Output(EMUNDUS_PATH_ABS.$user_id.DS.$name, 'F');
			}
			$path = EMUNDUS_PATH_ABS.$user_id.DS.$name;
			$url  = EMUNDUS_PATH_REL.$user_id.'/'.$name;
		}

		if($error == 0) {
			$query = 'INSERT INTO #__emundus_uploads (user_id, attachment_id, filename, description, can_be_deleted, can_be_viewed, campaign_id, fnum) VALUES ('.$user_id.', '.$letter['attachment_id'].', "'.$name.'","'.$training.' '.date('Y-m-d H:i:s').'", 0, 1, '.$campaign_id.', '.$db->Quote($fnum).')';
			$db->setQuery($query);
			$db->query();
			$id = $db->insertid();

			$file_info['id'] = $id;
			$file_info['path'] = $path;
			$file_info['attachment_id'] = $letter['attachment_id'];
			$file_info['name'] = $attachment['value'];
			$file_info['url'] = $url;

			$files[] = $file_info;
		}
	}
//die(var_dump($files));
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
	include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
	include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');

	$current_user = & JFactory::getUser();
	$user = & JFactory::getUser($user_id);
	$db = &JFactory::getDBO();

	$files = array();

	$evaluations = new EmundusModelEvaluation;
	$letters = $evaluations->getLettersTemplateByID($letter_id);

//print_r($letters);
	$query = "SELECT * FROM #__emundus_setup_teaching_unity WHERE published=1 AND date_start>NOW() AND code=".$db->Quote($letters[0]['training']). " ORDER BY date_start ASC";
	$db->setQuery($query);
	$courses = $db->loadAssocList();
	
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
			$this->SetFont('helvetica', 'B', 16);
			// Title
			$this->Cell(0, 15, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
		}

		// Page footer
		public function Footer() {
			// Position at 15 mm from bottom
			$this->SetY(-15);
			// Set font
			$this->SetFont('helvetica', 'I', 8);
			// Page number
			$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
			// footer
			$this->writeHTMLCell($w=0, $h=0, $x='', $y=250, $this->footer, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
			//logo
			if (is_file($this->logo_footer))
				$this->Image($this->logo_footer, 150, 280, 40, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
			
		}
	}

	$emails = new EmundusModelEmails;
	//
	// Replacement
	//
	$post = array(  'TRAINING_CODE' 		=> @$letters[0]['training'], 
					'TRAINING_PROGRAMME' 	=> @$programme,
					'REASON'				=> JText::_("DEPEND_OF_EVALUATION"), 
					'TRAINING_FEE' 			=> @$courses_fee, 
					'TRAINING_PERIODE'		=> @$courses_list
				);
	$tags = $emails->setTags($user_id, $post, $fnum);

	foreach ($letters as $letter) {
		$applications = new EmundusModelApplication;
		$attachment = $applications->getAttachmentByID($letter['attachment_id']);

		if($letter['template_type'] == 1) { // Static file
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

		} elseif($letter['template_type'] == 3) { // Template file .docx
			require_once JPATH_LIBRARIES.DS.'PHPWord.php';

			$file_path = explode(DS, $letter['file']);
			$file_type = explode('.', $file_path[count($file_path)-1]);
			$name = date('Y-m-d_H-i-s').$attachment['lbl'].'.'.$file_type[1];

			$PHPWord = new PHPWord();

			$document = $PHPWord->loadTemplate(JPATH_BASE.$letter['file']);

			for($i=0 ; $i<count($tags['patterns']) ; $i++) {
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
			$pdf->SetMargins(5, 40, 5);
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

			$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
			//$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
			$pdf->SetFont('helvetica', '', 8);

			//$dimensions = $pdf->getPageDimensions();

			//$htmldata .= $letter["header"];
			;
			$htmldata .= preg_replace($tags['patterns'], $tags['replacements'], preg_replace("/<span[^>]+\>/i", "", preg_replace("/<\/span\>/i", "", preg_replace("/<br[^>]+\>/i", "<br>", $letter["body"])))); 
			//$htmldata .= $letter["footer"];
	//die($htmldata);
			$pdf->AddPage();

			// Print text using writeHTMLCell()
			$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $htmldata, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);

			@chdir('tmp');
			$pdf->Output(EMUNDUS_PATH_ABS.$user_id.DS."demo", 'I');
		}
	}
//die(print_r($files));
	exit();
}	


function application_form_pdf($user_id, $fnum = null, $output = true) {
	jimport( 'joomla.html.parameter' );
	set_time_limit(0);
	require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'config'.DS.'lang'.DS.'eng.php');
	require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'tcpdf'.DS.'tcpdf.php');

	require_once(JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
	require_once(JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
	require_once(JPATH_COMPONENT.DS.'helpers'.DS.'menu.php');
	require_once(JPATH_COMPONENT.DS.'models'.DS.'users.php');
	include_once(JPATH_COMPONENT.DS.'models'.DS.'application.php');
	include_once(JPATH_COMPONENT.DS.'models'.DS.'profile.php');

	$m_profile 		= new EmundusModelProfile;
	$m_users 		= new EmundusModelUsers;
	$menu 			= new EmundusHelperMenu;
	$application 	= new EmundusModelApplication;

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
if (file_exists(EMUNDUS_PATH_REL.@$item->user_id.'/tn_'.@$item->avatar) && !empty($item->avatar))
	$htmldata .= '<td width="20%"><img src="'.EMUNDUS_PATH_REL.@$item->user_id.'/tn_'.@$item->avatar.'" width="100" align="left" /></td>';
elseif (file_exists(EMUNDUS_PATH_REL.@$item->user_id.'/'.@$item->avatar) && !empty($item->avatar))
	$htmldata .= '<td width="20%"><img src="'.EMUNDUS_PATH_REL.@$item->user_id.'/'.@$item->avatar.'" width="100" align="left" /></td>';
$htmldata .= '
<td>

  <div class="name"><strong>'.@$item->firstname.' '.strtoupper(@$item->lastname).'</strong>, '.@$item->label.' ('.@$item->cb_schoolyear.')</div>';

if(isset($item->maiden_name))
	$htmldata .= '<div class="maidename">'.JText::_('MAIDEN_NAME').' : '.$item->maiden_name.'</div>';

$date_submitted = (!empty($item->date_submitted) && strpos($item->date_submitted, '0000')!=0)?strftime("%d/%m/%Y %H:%M", strtotime($item->date_submitted)):JText::_('NOT_SENT');

$htmldata .= '
  <div class="nationality">'.JText::_('ID_CANDIDAT').' : '.@$item->user_id.'</div>
  <div class="nationality">'.JText::_('FNUM').' : '.$fnum.'</div>
  <div class="birthday">'.JText::_('EMAIL').' : '.@$item->email.'</div>
  <div class="sent">'.JText::_('APPLICATION_SENT_ON').' : '.$date_submitted.'</div>
  <div class="sent">'.JText::_('DOCUMENT_PRINTED_ON').' : '.strftime("%d/%m/%Y 	%H:%M", time()).'</div>
</td>
</tr>
</table>
</div>';
/**  END APPLICANT   ****/


	$htmldata .= $forms;

	// Listes des fichiers chargés 
	$uploads = $application->getUserAttachmentsByFnum($fnum);

	$nbuploads = count($uploads);
	$titleupload = $nbuploads>0?JText::_('FILES_UPLOADED'):JText::_('FILE_UPLOADED');
	
	$htmldata .='
	<h2>'.$titleupload.' : '.$nbuploads.'</h2>';	
/*	<table>
	<tr>
		<td><h3>'.JText::_('FILE_TYPE').'</h3></td>
		<td><h3>'.JText::_('FILE_NAME').'</h3></td>
		<td><h3>'.JText::_('DESCRIPTION').'</h3></td>
		<td><h3>'.JText::_('SENT_ON').'</h3></td>
	</tr>';*/
	$htmldata .='<div class="file_upload">';
	$htmldata .= '<ol>';
	foreach($uploads as $upload){
		$path_href = JURI::base().EMUNDUS_PATH_REL.$user_id.'/'.$upload->filename;
		$htmldata .= '<li><h3>'.$upload->value.'</h3>';
			$htmldata .= '<ul>';
			 	$htmldata .= '<li><b>'.JText::_('FILE_NAME').'</b> : <a href="'.$path_href.'" dir="ltr" target="_blank">'.$upload->filename.'</a></li>';
				$htmldata .= '<li><b>'.JText::_('SENT_ON').'</b> : '.strftime("%d/%m/%Y %H:%M", strtotime($upload->timedate)).'</li>';
				$htmldata .= '<li><b>'.JText::_('DESCRIPTION').'</b> : '.$upload->description.'</li>';
			$htmldata .= '</ul>';
			$htmldata .= '</li>';

	}
	$htmldata .='</ol></div>';
	if (!empty($htmldata)) {
		$pdf->startTransaction();
		$start_y = $pdf->GetY();
		$start_page = $pdf->getPage();
		$pdf->writeHTMLCell(0,'','',$start_y,$htmldata,'B', 1);

		$htmldata = '';
	}

	if (!file_exists(EMUNDUS_PATH_ABS.@$item->user_id)) {
		mkdir(EMUNDUS_PATH_ABS.$item->user_id, 0777, true);
		chmod(EMUNDUS_PATH_ABS.$item->user_id, 0777);
	}

	@chdir('tmp');
	if($output){
		if(@$current_user->applicant != 1){
			//$output?'FI':'F'
			$name = 'application_form_'.date('Y-m-d_H-i-s').'.pdf';
			$pdf->Output(EMUNDUS_PATH_ABS.$item->user_id.DS.$name, 'FI');
            $attachment = $application->getAttachmentByLbl("_application_form");
			$keys = array('user_id', 'attachment_id', 'filename', 'description', 'can_be_deleted', 'can_be_viewed', 'campaign_id', 'fnum' );
			$values = array($item->user_id, $attachment['id'], $name, $item->training.' '.date('Y-m-d H:i:s'), 0, 0, $campaign_id, $fnum);
			$data = array('key' => $keys, 'value' => $values);
			$application->uploadAttachment($data);

		}else{
			$pdf->Output(EMUNDUS_PATH_ABS.@$item->user_id.DS.$fnum.'_application.pdf', 'FI');
		}
	}else{
		$pdf->Output(EMUNDUS_PATH_ABS.@$item->user_id.DS.$fnum.'_application.pdf', 'F');
	}

}
?>