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

class plgFabrik_ElementEmundusreferent extends plgFabrik_Element {
	var $_user;
	var $_attachment_id;

	/**
	 * Check access ; user should be registrated
	 */
	function initUser() {
		$this->_user = JFactory::getUser();
		if ($this->_user->guest) {
			die("Can not reach this page : Permission denied");
		}
	}

	/**
	 * Draws the html form element
	 *
	 * @param   array  $data           to preopulate element with
	 * @param   int    $repeatCounter  repeat group counter
	 *
	 * @return  string	elements html
	 */
	public function render($data, $repeatCounter = 0) {

		$this->initUser();
		$params = $this->getParams();
		$element = $this->getElement();

		$app = JFactory::getApplication();
		$jinput = $app->input;
		$fnum = $jinput->get->get('rowid', null);

		$this->_attachment_id = $params->get('attachment_id');
		$info = $this->getReferentRequestInfo($this->_attachment_id, $fnum);
		$value = $info[0]['sent'];

		$id = $this->getHTMLId($repeatCounter);

		$type = '';
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
		if (!$this->editable) {
			$value = $this->_replaceWithIcons($value);
			return($element->hidden == '1') ? "<!-- " . $value . " -->" : $value;
		}

		$bits = $this->inputProperties($repeatCounter);
		$name = $this->getHTMLName($repeatCounter);
		$bits['class'] = "fabrikinput inputbox ".@$type;
		$bits['type'] = @$type;
		$bits['name'] = $name;
		$bits['id']	= $id;
		if (version_compare(phpversion(), '5.2.3', '<')) {
			$bits['value'] = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
		} else {
			$bits['value'] = htmlspecialchars($value, ENT_COMPAT, 'UTF-8', false);
		}

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
		if ($this->isReferentLetterUploaded($this->_attachment_id,$fnum) || $this->isReferentFormUploaded($this->_attachment_id,$fnum) == 1) {
			$str .= '<span class="emundusreferent_uploaded">'.JText::_('PLG_ELEMENT_EMUNDUSREFERENT_REFERENCE_LETTER_UPLOADED').'<span>';
		} else {
			$str .= '<input ' ;
			foreach ($bits as $key => $val) {
				$str .= $key.' = "'.$val.'" ';
			}
			$str .= " />\n";
			$txt_button = ($value>0)?JText::_('PLG_ELEMENT_EMUNDUSREFERENT_SEND_EMAIL_AGAIN'):JText::_('PLG_ELEMENT_EMUNDUSREFERENT_SEND_EMAIL');
			$str .= '<div id="'.$id.'_response"><input type="button" class="fabrikinput button btn-referent" id="'.$id.'_btn" name="'.$name.'" value="'.$txt_button.'" /></div>';

			$str .= '<img src="'.COM_FABRIK_LIVESITE.'media/com_fabrik/images/ajax-loader.gif" class="loader" id="'.$id.'_loader" alt="'.JText::_('Loading').'" style="display:none;padding-left:10px;" />';
			$str .= '<div id="'.$id.'_error"></div>';
		}

		return $str."</div>";
	}


	/**
	 * Get the element's HTML label
	 *
	 * @param   int     $repeatCounter  group repeat counter
	 * @param   string  $tmpl           form template
	 *
	 * @return  string  label
	 */
	public function getLabel($repeatCounter, $tmpl = '') {
		return '';
	}

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 *
	 * @param   int  $repeatCounter  repeat group counter
	 *
	 * @return array
	 */
	public function elementJavascript($repeatCounter) {
		$id = $this->getHTMLId($repeatCounter);
		$params = $this->getParams();
		$opts = $this->getElementJSOptions($repeatCounter);
		$filterid = $this->getHTMLId($repeatCounter) . 'value';

		$opts->email = $params->get('email_element');
		$opts->sending = JText::_('PLG_ELEMENT_EMUNDUSREFERENT_SENDING_EMAIL');
		$opts->sendmail = JText::_('PLG_ELEMENT_EMUNDUSREFERENT_SEND_EMAIL');
		$opts->sendmailagain = JText::_('PLG_ELEMENT_EMUNDUSREFERENT_SEND_EMAIL_AGAIN');
		$opts->attachment_id = $params->get('attachment_id');
		$opts->form_recommend = $params->get('form_id', '68');
		$opts->fullName = $this->getFullName(false, true);
		$opts->formid = $this->getForm()->getForm()->id;
		$opts->filterid = $filterid;
		return array('FbEmundusreferent', $id, $opts);
	}


	/**
	 * Get the class to manage the form element
	 * to ensure that the file is loaded only once
	 *
	 * @param   array   &$srcs   Scripts previously loaded
	 * @param   string  $script  Script to load once class has loaded
	 * @param   array   &$shim   Dependant class names to load before loading the class - put in requirejs.config shim
	 *
	 * @return void|boolean
	 */
	public function formJavascriptClass(&$srcs, $script = '', &$shim = array()) {
		$key = FabrikHelperHTML::isDebug() ? 'element/emundusreferent/emundusreferent' : 'element/emundusreferent/emundusreferent-min';

		$s = new stdClass;
		// Seems OK now - reverting to empty array
		$s->deps = array();

		if (array_key_exists($key, $shim)) {
			$shim[$key]->deps = array_merge($shim[$key]->deps, $s->deps);
		} else {
			$shim[$key] = $s;
		}

		parent::formJavascriptClass($srcs, $script, $shim);

		// $$$ hugh - added this, and some logic in the view, so we will get called on a per-element basis
		return false;
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
	public function getValidationWatchElements($repeatCounter) {
		$id = $this->getHTMLId($repeatCounter);
		$ar = array('id' => $id, 'triggerEvent' => 'click');
		return array($ar);
	}

	//////////////////////////  SET FILES REQUEST  /////////////////////////////
	//
	// Génération de l'id du prochain fichier qui devra être ajouté par le referent
	// 1. Génération aléatoire de l'ID
	private function rand_string($len, $chars = 'abcdefghijklmnopqrstuvwxyz0123456789') {
		$string = '';
		for ($i = 0; $i < $len; $i++) {
			$pos = rand(0, strlen($chars)-1);
			$string .= $chars{$pos};
		}
		return $string;
	}

	/**
	 * Ajax request
	 *
	 * @return void echos the array of messages sent
	 * @throws Exception
	 */
	public function onAjax_getOptions() {

		include_once(JPATH_BASE.'/components/com_emundus/models/profile.php');
        include_once(JPATH_SITE.'/components/com_emundus/models/emails.php');

		$baseurl = JURI::root();
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$this->_user = JFactory::getUser();

		JLog::addLogger(['text_file' => 'com_emundus.filerequest.php'], JLog::ALL, ['com_emundus']);

		$recipient = $jinput->post->getRaw('email');
		$attachment_id = $jinput->post->getInt('attachment_id');
		$form_recommend = $jinput->post->getInt('form_recommend');
		$fnum = $jinput->post->get('fnum');

        //// GET REFEENCE FIRSTNAME, REFERENCE LASTNAME ////
        $firstname = ucwords($jinput->post->getString('firstname'));
        $lastname = ucwords($jinput->post->getString('lastname'));

		if (empty($recipient)) {
			$response = array("result" => 0, "message"=>'<span class="emundusreferent_error">'.JText::_('PLG_ELEMENT_EMUNDUSREFERENT_EMAIL_MISSING_ERROR').'</span>');
			die(json_encode($response));
		}

		if (empty($fnum)) {
			$response = array("result" => 0, "message"=>'<span class="emundusreferent_error">'.JText::_('PLG_ELEMENT_EMUNDUSREFERENT_FNUM_INCORRECT_ERROR').'</span>');
			die(json_encode($response));
		}

		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'))
			->from($db->quoteName('#__emundus_campaign_candidature'))
			->where($db->quoteName('fnum').' LIKE '.$db->quote($fnum));
		$db->setQuery($query);
		try {
			$cc_id = $db->loadResult();
			if (empty($cc_id)) {
				$response = array("result" => 0, "message"=>'<span class="emundusreferent_error">'.JText::_('PLG_ELEMENT_EMUNDUSREFERENT_FNUM_INCORRECT_ERROR').'</span>');
				die(json_encode($response));
			}
		} catch (Exception $e) {
			JLog::add('Error getting CC by fnum in query -> '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			$response = array("result" => 0, "message"=>'<span class="emundusreferent_error">'.JText::_('PLG_ELEMENT_EMUNDUSREFERENT_FNUM_INCORRECT_ERROR').'</span>');
			die(json_encode($response));
		}

		if (empty($attachment_id)) {
			$response = array("result" => 0, "message" => '<span class="emundusreferent_error">'.JText::_('PLG_ELEMENT_EMUNDUSREFERENT_EMAIL_ERROR').'</span>');
			die(json_encode($response));
		}

		// Récupèration des données du mail
		$query = 'SELECT se.id, se.subject, se.emailfrom, se.name, se.message, et.Template
					FROM #__emundus_setup_emails AS se
					LEFT JOIN #__emundus_email_templates AS et ON se.email_tmpl = et.id
                	WHERE se.lbl="referent_letter"';
		$db->setQuery($query);
		$db->execute();
		$obj = $db->loadObject() or die(json_encode(array("result"=>0, "message"=>'<span class="emundusreferent_error">'.JText::_('ERROR_DB_SETUP_EMAIL').'</span>')));

		// Récupèration de la pièce jointe : modele de lettre
		$query = 'SELECT esp.reference_letter
						FROM #__emundus_setup_profiles AS esp 
						LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.profile_id = esp.id
						LEFT JOIN #__emundus_campaign_candidature AS cc ON cc.campaign_id = esc.id
						WHERE cc.id = '.$cc_id;
		$db->setQuery($query);
		$db->execute() or die(json_encode(array("result"=>0, "message"=>'<span class="emundusreferent_error">'.JText::_('ERROR_DB_REFERENCE_LETTER').'</span>')));
		$obj_letter = $db->loadResult();

		// Reference  /////////////////////////////////////////////////////////////
		if (!$this->isReferentLetterUploaded($attachment_id, $fnum)) {
			$key = md5($this->rand_string(20).time());
			// 2. MAJ de la table emundus_files_request
			$query = 'INSERT INTO #__emundus_files_request (time_date, student_id, keyid, attachment_id, fnum, email) 
                          VALUES (NOW(), '.$this->_user->id.', "'.$key.'", "'.$attachment_id.'", '.$fnum.', '.$db->Quote($recipient).')';
			$db->setQuery($query);
			$db->execute() or die(json_encode(array("result"=>0, "message"=>'<span class="emundusreferent_error">'.JText::_('ERROR_DB_FILE_REQUEST').'</span>')));

			$profile = new EmundusModelProfile;
			$fnum_detail = $profile->getFnumDetails($fnum);

			// 3. Envoi du lien vers lequel le professeur va pouvoir uploader la lettre de référence
			$link_upload = $baseurl.'index.php?option=com_fabrik&view=form&formid='.$form_recommend.'&keyid='.$key.'&sid='.$this->_user->id;

			$patterns = array('/\[ID\]/', '/\[NAME\]/', '/\[EMAIL\]/', '/\[UPLOAD_URL\]/', '/\[PROGRAMME_NAME\]/','/\[REFERENT_FIRST_NAME\]/', '/\[REFERENT_NAME\]/');
			$replacements = array($this->_user->id, $this->_user->name, $this->_user->email, $link_upload, $fnum_detail['label'],$firstname, $lastname);

			$subject = preg_replace($patterns, $replacements, $obj->subject);
			$body = $obj->message;

			if ($obj->Template) {
				$body = preg_replace(["/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"], [$subject, $body], $obj->Template);
			}
			$body = preg_replace($patterns, $replacements, $body);

            //// set tags and set fabrik tags for email subject + email body ////
            $m_emails = new EmundusModelEmails();
            $tags = $m_emails->setTags($fnum_detail['applicant_id'], ['FNUM' => $fnum], $fnum, '', $obj);

            $subject = $m_emails->setTagsFabrik($subject, [$fnum]);
            $subject = preg_replace($tags['patterns'], $tags['replacements'], $subject);

            $body = $m_emails->setTagsFabrik($body, [$fnum]);
            $body = preg_replace($tags['patterns'], $tags['replacements'], $body);

			// Mail
			$from = $obj->emailfrom;
			$fromname = $obj->name;

			$email_from_sys = $app->getCfg('mailfrom');
			$sender = array(
				$email_from_sys,
				$fromname
			);

			$mailer = JFactory::getMailer();
			$mailer->setSender($sender);
			$mailer->addReplyTo($from, $fromname);
			$mailer->addRecipient([$recipient]);
			$mailer->setSubject($subject);
			$mailer->isHTML(true);
			$mailer->Encoding = 'base64';
			$mailer->setBody($body);
			if (!empty($obj_letter)) {
				$attachment[] = JPATH_BASE.str_replace("\\", "/", $obj_letter);
				$mailer->addAttachment($attachment);
			}

			$send = $mailer->send();

			if ($send !== true) {
				JLog::add($send->__toString(), JLog::ERROR, 'com_emundus');
				$response = array("result" => 0, "message" => '<span class="emundusreferent_error">'.JText::_('PLG_ELEMENT_EMUNDUSREFERENT_EMAIL_ERROR').'</span>');
			} else {
				JFactory::getApplication()->enqueueMessage(JText::_('MESSAGE_SENT').' : '.$recipient, 'message');
				$sql = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `subject`, `message`, `date_time`)
                            VALUES ('62', '-1', ".$db->quote($subject).", ".$db->quote($body).", NOW())";
				$db->setQuery($sql);
				try {
					$db->execute();
				} catch (Exception $e) {
					JLog::add('Error logging email : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
				}
				$response = array("result" => 1, "message" => '<span class="emundusreferent_sent">'.JText::_('PLG_ELEMENT_EMUNDUSREFERENT_EMAIL_SENT').'</span>');
			}
		} else {
			$response = array("result" => 1, "message" => '<span class="emundusreferent_uploaded">'.JText::_('PLG_ELEMENT_EMUNDUSREFERENT_REFERENCE_LETTER_UPLOADED').'</span>');
		}
		echo json_encode($response);
	}

	protected function getReferentRequestInfo($attachment_id, $fnum) {
		$db = JFactory::getDBO();
		$query = "SELECT count(id) as sent, SUM(uploaded) uploaded FROM #__emundus_files_request WHERE fnum LIKE ".$db->quote($fnum)." AND attachment_id=".$attachment_id;
		$db->setQuery($query);
		return $db->loadAssocList();
	}

	protected function isReferentLetterUploaded($attachment_id, $fnum) {
		$db = JFactory::getDBO();
		$query = 'SELECT count(id) as cpt FROM #__emundus_uploads WHERE fnum LIKE '.$db->quote($fnum).' AND attachment_id='.$attachment_id;
		$db->setQuery($query);
		$db->execute();
		return ($db->loadResult() > 0);
	}

	protected function isReferentFormUploaded($attachment_id,$fnum){
        $db = JFactory::getDBO();
        $query = 'SELECT uploaded FROM #__emundus_files_request WHERE fnum LIKE '.$db->quote($fnum).' AND attachment_id='.$attachment_id.' ORDER BY ID limit 1';
        $db->setQuery($query);

        return $db->loadColumn();
    }

}
