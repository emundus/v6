<?php
/**
 * @package		Joomla.Plugin
 * @subpackage	Fabrik.element.emundusreferent
 * @copyright	Copyright (C) 2005 Fabrik. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Plugin element to render button
 *
 * @package		Joomla.Plugin
 * @subpackage	Fabrik.element.emundusreferent
 */

class plgFabrik_ElementEmundusreferent extends plgFabrik_Element
{
	var $_pluginName = 'emundusreferent';
	var $_user;
	var $_attachment_id;

	/**
	 * Check access ; user should be registrated
	 */

	function initUser()
	{
		$this->_user = & JFactory::getUser();
		$accessibility = false;
		foreach ($this->_user->groups as $group)
			if ($group > 1)
				$accessibility = true;
		if ($accessibility === false) die("Can not reach this page : Permission denied");
	}
	
	/**
	 * Draws the html form element
	 *
	 * @param   array  $data           to preopulate element with
	 * @param   int    $repeatCounter  repeat group counter
	 *
	 * @return  string	elements html
	 */

	public function render($data, $repeatCounter = 0)
	{
		$name 			= $this->getHTMLName($repeatCounter);
		$id 			= $this->getHTMLId($repeatCounter);
		$params 		=& $this->getParams();
		$element 		=& $this->getElement();
		$size 			= $element->width;

		$this->initUser();
		JHTML::stylesheet('emundusreferent.css', 'plugins/fabrik_element/emundusreferent/css/');
		FabrikHelperHTML::script('emundusreferent.js', 'plugins/fabrik_element/emundusreferent/', false);
		$bits = array();
		$this->_attachment_id = $params->get('attachment_id');
		$info = $this->getReferentRequestInfo($this->_attachment_id);

		if (is_array($this->getForm()->_data)) {
			$data 	=& $this->getForm()->_data;
		}
		$value = $info[0]['sent'];
		//$value 	= $this->getValue($data, $repeatCounter);
		if (isset($this->_elementError) && $this->_elementError != '') {
			$type .= " elementErrorHighlight";
		}
		if ($element->hidden == '1') {
			$type = "hidden";
		}

		if (JRequest::getCmd('task') == 'processForm') {
			$value = $this->unNumberFormat($value);
		}
		$value = $this->numberFormat($value);
		if (!$this->_editable) {
			$value = $this->_replaceWithIcons($value);
			return($element->hidden == '1') ? "<!-- " . $value . " -->" : $value;
		}

		$bits['class'] = "fabrikinput inputbox ".@$type;
		$bits['type']		= @$type;
		$bits['name']		= $name;
		$bits['id']			= $id;
		if (version_compare( phpversion(), '5.2.3', '<')) {
			$bits['value'] = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
		}
		else {
			$bits['value'] = htmlspecialchars($value, ENT_COMPAT, 'UTF-8', false);
		}
		$bits['size']	= $size;

		//cant be used with hidden element types
		if ($element->hidden != '1') {
			if ($params->get('readonly')) {
				$bits['readonly'] = "readonly";
				$bits['class'] .= " readonly";
			}
			if ($params->get('disable')) {
				$bits['class'] .= " disabled";
				$bits['disabled'] = 'disabled';
			}
		}
		$str = '<div><label class="fabrikLabel " for="'.$element->name.'">'.$element->label.'<img class="fabrikTip fabrikImg" title="" src="media/com_fabrik/images/notempty.png"></label>';
		if ($this->isReferentLetterUploaded($this->_attachment_id))
			$str .= '<span class="emundusreferent_uploaded">'.JText::_('REFERENCE_LETTER_UPLOADED').'<span>';
		else {
			$str .= "<input ";
			foreach ($bits as $key=>$val) {
				$str.= "$key = \"$val\" ";
			}
			$str .= " />\n";
			$txt_button = ($value>0)?JText::_('SEND_EMAIL_AGAIN'):JText::_('SEND_EMAIL');
			$str .= "<div id=\"".$id."_response\"><input type=\"button\" class=\"fabrikinput button\" id=\"".$id."_btn\" name=\"$name\" value=\"$txt_button\" /></div>";
			
			$str .= "<img src=\"".COM_FABRIK_LIVESITE."media/com_fabrik/images/ajax-loader.gif\" class=\"loader\" id=\"".$id."_loader\" alt=\"" . JText::_('Loading') . "\" style=\"display:none;padding-left:10px;\" />";
			$str .= "<div id=\"".$id."_error\"></div>";
		}
		$str .= "</div>";
		return $str;
	}

	/**
	 * Get the element's HTML label
	 *
	 * @param   int     $repeatCounter  group repeat counter
	 * @param   string  $tmpl           form template
	 *
	 * @return  string  label
	 */

	public function getLabel($repeatCounter, $tmpl = '')
	{
		return '';
	}

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 *
	 * @param   int  $repeatCounter  repeat group counter
	 *
	 * @return  string
	 */

	public function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$filterid = $id . 'value';
		$opts = $this->elementJavascriptOpts($repeatCounter);
		return "new FbEmundusreferent('$id', $opts)";
	}
	
	/**
	 * Get element JS options
	 *
	 * @param   int  $repeatCounter  group repeat counter
	 *
	 * @return  string  json_encoded options
	 */

	protected function elementJavascriptOpts($repeatCounter)
	{
		$params = $this->getParams();
		$element = $this->getElement();
		$data = $this->_form->_data;
		$opts = $this->getElementJSOptions($repeatCounter);
		$filterid = $this->getHTMLId($repeatCounter) . 'value';
		
		$opts->email = $params->get('email_element');
		$opts->sending = JText::_('SENDING_EMAIL');
		$opts->sendmail = JText::_('SEND_EMAIL');
		$opts->sendmailagain = JText::_('SEND_EMAIL_AGAIN');
		//$opts->emailelement = $params->get('email_element');
		$opts->attachment_id = $params->get('attachment_id');
		$opts->id = $this->_id;
		$opts->fullName = $this->getFullName(false, true, false);
		$opts->formid = $this->getForm()->getForm()->id;
		$opts->filterid = $filterid;
		return json_encode($opts);
	}

	/**
	 * Get an array of element html ids and their corresponding
	 * js events which trigger a validation.
	 * Examples of where this would be overwritten include timedate element with time field enabled
	 *
	 * @param   int  $repeatCounter  repeat group counter
	 *
	 * @return  array  html ids to watch for validation
	 */

	public function getValidationWatchElements($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$ar = array('id' => $id, 'triggerEvent' => 'click');
		return array($ar);
	}
		
	//////////////////////////  SET FILES REQUEST  /////////////////////////////
	// 
	// G�n�ration de l'id du prochain fichier qui devra �tre ajout� par le referent
	
	// 1. G�n�ration al�atoire de l'ID
	private function rand_string($len, $chars = 'abcdefghijklmnopqrstuvwxyz0123456789')
	{
		$string = '';
		for ($i = 0; $i < $len; $i++)
		{
			$pos = rand(0, strlen($chars)-1);
			$string .= $chars{$pos};
		}
		return $string;
	}
	
	/**
	 * Ajax request
	 *
	 * @return  array  of messages
	 */

	public function onAjax_getOptions() {	
		$baseurl = JURI::root();
		$db =& JFactory::getDBO();
		$this->_user = & JFactory::getUser();
				
		$recipient = JRequest::getVar('email');
		$attachment_id = JRequest::getVar('attachment_id');

		if (!empty($attachment_id)) { 
			require(JPATH_LIBRARIES.DS.'emundus'.DS.'email.class.php');
			$email = new Email( $recipient );
			$results = $email->checkEmail_results;
			if ($results['checkEmailSyntax'] == 0) {
				$response = array("result"=>0, "message"=>'<span class="emundusreferent_error">'.JText::_('EMAIL_FORMAT_ERROR').'</span>');
				die(json_encode($response));
			}
			if (!($results['gethostbyname']==1 && $results['customCheckEmailWith_Dnsrr']==1) && 
				!($results['checkEmailWith_Dnsrr']==1 && $results['customCheckEmailWith_Mxrr']==1)) {
					$response = array("result"=>0, "message"=>'<span class="emundusreferent_error">'.JText::_('EMAIL_DOMAIN_ERROR').'</span>');
					die(json_encode($response));
			}
		} else {
			$response = array("result"=>0, "message"=>'<span class="emundusreferent_error">'.JText::_('EMAIL_ERROR').'</span>');
			die(json_encode($response));
		}
	
		// R�cup�ration des donn�es du mail
		$query = 'SELECT id, subject, emailfrom, name, message
						FROM #__emundus_setup_emails
						WHERE lbl="referent_letter"';
		$db->setQuery( $query );
		$db->query();
		$obj = $db->loadObjectList() or die(json_encode(array("result"=>0, "message"=>'<span class="emundusreferent_error">'.JText::_('ERROR_DB_SETUP_EMAIL').'</span>')));
		
		// R�cup�ration de la pi�ce jointe : modele de lettre
		$query = 'SELECT esp.reference_letter
						FROM #__emundus_users as eu 
						LEFT JOIN #__emundus_setup_profiles as esp on esp.id = eu.profile 
						WHERE eu.user_id = '.$this->_user->id;
		$db->setQuery( $query );
		$db->query() or die(json_encode(array("result"=>0, "message"=>'<span class="emundusreferent_error">'.JText::_('ERROR_DB_REFERENCE_LETTER').'</span>')));
		$obj_letter=$db->loadRowList();
		
		// Reference  /////////////////////////////////////////////////////////////
		if (!$this->isReferentLetterUploaded($attachment_id)) {
			$key1 = md5($this->rand_string(20).time());
			// MAJ de la table emundus_files_request
			$query = 'INSERT INTO #__emundus_files_request (time_date, student_id, keyid, attachment_id) 
								  VALUES (NOW(), '.$this->_user->id.', "'.$key1.'", '.$attachment_id.')';
			$db->setQuery($query);
			$db->query() or die(json_encode(array("result"=>0, "message"=>'<span class="emundusreferent_error">'.JText::_('ERROR_DB_FILE_REQUEST').'</span>')));

			// 3. Envoi du lien vers lequel le professeur va pouvoir uploader la lettre de r�f�rence
			$link_upload1 = $baseurl.'index.php?option=com_fabrik&view=form&formid=68&keyid='.$key1.'&sid='.$this->_user->id;

			///////////////////////////////////////////////////////
			$patterns = array ('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/', '/\[UPLOAD_URL\]/');

			// Mail 
			$from = $obj[0]->emailfrom;
			$fromname = $obj[0]->name;
			$from_id = $obj[0]->id;
			
			$subject = $obj[0]->subject;
			$mode = 1;
			//$cc = $user->email;
			//$bcc = $user->email;
			$attachment[] = JPATH_BASE.str_replace("\\", "/", $obj_letter[0][0]);
			//die(print_r($obj_letter[0][0]));
			$replyto = $obj[0]->emailfrom;
			$replytoname = $obj[0]->name;
		
			$replacements = array ($this->_user->id, $this->_user->name, $this->_user->email, $link_upload1);
			$body1 = preg_replace($patterns, $replacements, $obj[0]->message);
			unset($replacements);
			
			if (JUtility::sendMail($from, $fromname, $recipient, $subject, $body1, $mode, null, null, $attachment, $replyto, $replytoname)) {
				$sql = 'INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`) VALUES (62, -1, "'.$subject.'", "'.$db->quote($body1).'", NOW())';
				$db->setQuery( $sql );
				$db->query() or die(json_encode(array("result"=>0, "message"=>'<span class="emundusreferent_error">'.JText::_('ERROR_DB_MESSAGE').'</span>')));
				//echo '1|<span class="emundusreferent_sent">'.JText::_('EMAIL_SENT').'</span>';
				$response = array("result"=>1, "message"=>'<span class="emundusreferent_sent">'.JText::_('EMAIL_SENT').'</span>');
			} else {
				//echo '0|<span class="emundusreferent_error">'.JText::_('EMAIL_ERROR').'</span>';
				$response = array("result"=>0, "message"=>'<span class="emundusreferent_error">'.JText::_('EMAIL_ERROR').'</span>');
			}
		} else {
			//echo '1|<span class="emundusreferent_uploaded">'.JText::_('REFERENCE_LETTER_UPLOADED').'</span>';
			$response = array("result"=>1, "message"=>'<span class="emundusreferent_uploaded">'.JText::_('REFERENCE_LETTER_UPLOADED').'</span>');	
		}
		echo json_encode($response);
	}

	protected function getReferentRequestInfo($attachment_id)
	{
		$db =& JFactory::getDBO();
		$query = "SELECT count(id) as sent, SUM(uploaded) uploaded FROM #__emundus_files_request WHERE student_id=".$this->_user->id." AND attachment_id=".$attachment_id;
		$db->setQuery( $query );
		$data = $db->loadAssocList(); 
		return $data;
	}
	
	protected function isReferentLetterUploaded($attachment_id)
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT count(id) as cpt FROM #__emundus_uploads WHERE user_id='.$this->_user->id.' AND attachment_id='.$attachment_id;
		$db->setQuery( $query ); 
		$db->query();
		return ($db->loadResult()>0?true:false);
	}
	
}
