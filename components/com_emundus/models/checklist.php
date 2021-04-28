<?php
/**
 * CheckList model : displays applicant checklist (docs and forms).
 *
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     eMundus SAS - Jonas Lerebours
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

class EmundusModelChecklist extends JModelList
{
	var $_user = null;
	var $_db = null;
	var $_need = 0;
	var $_forms = 0;
	var $_attachments = 0;

	function __construct()
	{
		parent::__construct();
		require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');
		$this->_db = JFactory::getDBO();
		$student_id = JRequest::getVar('sid', null, 'GET', 'none',0);

		if ($student_id > 0 && JFactory::getUser()->usertype != 'Registered') {
			$this->_user = JFactory::getUser($student_id);
			$this->_db->setQuery('SELECT user.profile, user.university_id, user.schoolyear, profile.menutype
				FROM #__emundus_users AS user
				LEFT JOIN #__emundus_setup_profiles AS profile ON profile.id = user.profile
				WHERE user.user_id = '.$this->_user->id);
			$res = $this->_db->loadObject();
			$this->_user->profile = $res->profile;
		} else {
			$this->_user = JFactory::getSession()->get('emundusUser');
		}
		//echo $this->_user->usertype;
	}

	function getGreeting(){
		$query = 'SELECT id, title, text FROM #__emundus_setup_checklist WHERE page = "checklist" ';
		$note = 0;//$this->getResult();
		if($note && is_numeric($note) && $note>1) $this->_need = $note;
		$query .= 'AND (whenneed = '.$this->_need . ' OR whenneed='.$this->_user->status.')';
		$this->_db->setQuery( $query );
		return $this->_db->loadObject();
	}

	function getInstructions()
	{
		$query = 'SELECT id, title, text FROM #__emundus_setup_checklist WHERE page = "instructions"';
		//$query.= $this->_need?'1':'0';
		$this->_db->setQuery( $query );
		return $this->_db->loadObject();
	}

	function getFormsList()
	{
		$forms = @EmundusHelperMenu::buildMenuQuery($this->_user->profile);
		foreach ($forms as $form) {
			$query = 'SELECT count(*) FROM '.$form->db_table_name.' WHERE user = '.$this->_user->id.' AND fnum like '.$this->_db->Quote($this->_user->fnum);
			$this->_db->setQuery( $query );
			$form->nb = $this->_db->loadResult();
			if ($form->nb==0) {
				$this->_forms = 1;
				$this->_need = $this->_attachments=1?1:0;
			}
		}
		return $forms;
	}

	function getAttachmentsList() {

        // Check if column campaign_id exist in emundus_setup_attachment_profiles
        $config = new JConfig();
        $db_name = $config->db;

        $query = $this->_db->getQuery(true);
        $query->select('COUNT(*)')
            ->from($this->_db->quoteName('information_schema.columns'))
            ->where($this->_db->quoteName('table_schema') . ' = ' . $this->_db->quote($db_name))
            ->andWhere($this->_db->quoteName('table_name') . ' = ' . $this->_db->quote('jos_emundus_setup_attachment_profiles'))
            ->andWhere($this->_db->quoteName('column_name') . ' = ' . $this->_db->quote('campaign_id'));
        $this->_db->setQuery($query);
        $exist = $this->_db->loadResult();

        if (intval($exist) > 0 && !empty($this->_user->campaign_id)) {
			$query = 'SELECT attachments.*, COUNT(uploads.attachment_id) AS nb, uploads.id as uid, profiles.mandatory as mandatory, profiles.duplicate as duplicate
					FROM #__emundus_setup_attachments AS attachments
						INNER JOIN #__emundus_setup_attachment_profiles AS profiles ON attachments.id = profiles.attachment_id
						LEFT JOIN #__emundus_uploads AS uploads ON uploads.attachment_id = profiles.attachment_id AND uploads.user_id = '.$this->_user->id.'  AND fnum like '.$this->_db->Quote($this->_user->fnum).'
					WHERE profiles.campaign_id = '.$this->_user->campaign_id.' AND profiles.displayed = 1
					GROUP BY attachments.id
					ORDER BY profiles.mandatory DESC, profiles.ordering ASC';
            $this->_db->setQuery($query);
            $attachments = $this->_db->loadObjectList();
        }

        if (intval($exist) == 0 || empty($attachments)) {
            $query = 'SELECT attachments.id, COUNT(uploads.attachment_id) AS nb, uploads.id as uid, attachments.nbmax, attachments.value, attachments.lbl, attachments.description, attachments.allowed_types, profiles.mandatory, profiles.duplicate
					FROM #__emundus_setup_attachments AS attachments
						INNER JOIN #__emundus_setup_attachment_profiles AS profiles ON attachments.id = profiles.attachment_id
						LEFT JOIN #__emundus_uploads AS uploads ON uploads.attachment_id = profiles.attachment_id AND uploads.user_id = '.$this->_user->id.'  AND fnum like '.$this->_db->Quote($this->_user->fnum).'
					WHERE profiles.profile_id = '.$this->_user->profile.' AND profiles.displayed = 1 AND profiles.campaign_id IS NULL
					GROUP BY attachments.id
					ORDER BY profiles.mandatory DESC, attachments.ordering ASC';
            $this->_db->setQuery($query);
            $attachments = $this->_db->loadObjectList();
        }

		foreach ($attachments as $attachment) {
			if ($attachment->nb > 0) {

				$query = 'SELECT * FROM #__emundus_uploads WHERE user_id = '.$this->_user->id.' AND attachment_id = '.$attachment->id. ' AND fnum like '.$this->_db->Quote($this->_user->fnum);
				$this->_db->setQuery($query);
				$attachment->liste = $this->_db->loadObjectList();

			} elseif ($attachment->mandatory == 1) {
				$this->_attachments = 1;
				$this->_need = $this->_forms=1?1:0;
			}
		}
		return $attachments;
	}

	function getNeed() {
		return $this->_need;
	}

	function getSent() {
		$query = 'SELECT submitted
					FROM #__emundus_campaign_candidature
					WHERE applicant_id = '.$this->_user->id.' AND fnum like '.$this->_db->Quote($this->_user->fnum);
		$this->_db->setQuery( $query );
		$res = $this->_db->loadResult();
		return $res>0;
	}

	function getResult() {
		$query = 'SELECT final_grade FROM #__emundus_final_grade WHERE student_id = '.$this->_user->id.' AND fnum like '.$this->_db->Quote($this->_user->fnum);
		$this->_db->setQuery( $query );
		return $this->_db->loadResult();
	}

	function getApplicant(){
		$query = 'SELECT profile FROM #__emundus_users WHERE user_id = '.$this->_user->id;
		$this->_db->setQuery( $query );
		if($this->_db->loadResult() == 8) return false;
		return true;
	}

	function getIsOtherActiveCampaign() {
		$query='SELECT count(id) as cpt
				FROM #__emundus_setup_campaigns
				WHERE id NOT IN (
								select campaign_id FROM #__emundus_campaign_candidature WHERE applicant_id='.$this->_user->id.'
								)';
		$this->_db->setQuery($query);
		$cpt = $this->_db->loadResult();
		return $cpt>0?true:false;
	}

	function getConfirmUrl($profile = null) {

	    if (empty($profile)) {
	        $profile = $this->_user->profile;
        }

        $db = JFactory::getDBO();
        $query = 'SELECT CONCAT(m.link,"&Itemid=", m.id) as link
        FROM #__emundus_setup_profiles as esp
        LEFT JOIN  #__menu as m on m.menutype = esp.menutype
        WHERE esp.id='.$profile.' AND m.published>=0 AND m.level=1 ORDER BY m.lft DESC';

        $db->setQuery($query);
        return $db->loadResult();
	}


	function setDelete($status = 0, $student = null) {

		$db = JFactory::getDBO();

		if (!isset($student) && empty($student))
			$student = JFactory::getSession()->get('emundusUser');

		if ($status > 1)
			$status = 1;

		$query = 'UPDATE #__emundus_uploads SET can_be_deleted = '.$status.' WHERE user_id = '.$student->id. ' AND fnum like '.$db->Quote($student->fnum);
		$db->setQuery($query);

		try {
			$db->execute();
		} catch (Exception $e) {
			JLog::add('Error in model/checklist at query : '.$query, JLog::ERROR, 'com_emundus');
		}
	}

	/**
	 * Set filename for uploaded attachment send by applicant
     * @param string $file filename received
     * @param string $lbl system name defined in emundus_setup_attachments
     * @param array $fnumInfos infos from fnum
     * @return string
     */
	function setAttachmentName($file, $lbl, $fnumInfos) {

		//$filename = strtolower(preg_replace(array('([\40])','([^a-zA-Z0-9-])','(-{2,})'),array('_','','_'),preg_replace('/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig);/','$1',htmlentities($user->lastname.'_'.$user->firstname,ENT_NOQUOTES,'UTF-8'))));

		$file_array = explode(".", $file);

		$eMConfig = JComponentHelper::getParams('com_emundus');
		$applicant_file_name = $eMConfig->get('applicant_file_name', null);

		if (!empty($applicant_file_name)) {

			require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
			$m_emails = new EmundusModelEmails;

			$tags = $m_emails->setTags($fnumInfos['applicant_id'], null, $fnumInfos['fnum']);
			$application_form_name = '[APPLICANT_NAME]';
			$application_form_name = preg_replace($tags['patterns'], $tags['replacements'], $application_form_name);
			//$application_form_name = $m_emails->setTagsFabrik($application_form_name, array($fnum));

			// Format filename
			$application_form_name = $m_emails->stripAccents($application_form_name);
			$application_form_name = preg_replace('/[^A-Za-z0-9 _.-]/','', $application_form_name);
			$application_form_name = preg_replace('/\s/', '_', $application_form_name);
			$application_form_name = strtolower($application_form_name);
			$filename = $application_form_name.'_'.trim($lbl, ' _').'-'.rand().'.'.end($file_array);

		} else {
			$filename = $fnumInfos['applicant_id'].'-'.$fnumInfos['id'].'-'.trim($lbl, ' _').'-'.rand().'.'.end($file_array);
		}

		return $filename;
	}
}
?>
