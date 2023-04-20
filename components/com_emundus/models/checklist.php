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
		jimport('joomla.log.log');
		JLog::addLogger(['text_file' => 'com_emundus.checklist.php'], JLog::ALL, array('com_emundus.checklist'));

		require_once (JPATH_SITE.'/components/com_emundus/helpers/menu.php');
		$this->_db = JFactory::getDBO();
		$app =  JFactory::getApplication();
		$student_id = $app->input->getInt('sid');
		$current_user = JFactory::getUser()->id;

		if (!empty($student_id)) {
			if (EmundusHelperAccess::asPartnerAccessLevel($current_user)) {
				$this->_user = JFactory::getUser($student_id);
				if (!empty($this->_user->id)) {
					$query = $this->_db->getQuery(true);

					$query->select('jeu.profile')
						->from($this->_db->quoteName('#__emundus_users', 'jeu'))
						->where('jeu.user_id = ' . $this->_user->id);

					try {
						$this->_db->setQuery($query);
						$profile = $this->_db->loadResult();

						if (!empty($profile)) {
							$this->_user->profile = $profile;
						}
					} catch (Exception $e) {
						JLog::add('Failed to get user profile ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
					}
				} else {
					JLog::add('User ' . $current_user .  ' tried to read checklist of user ' . $student_id . ' but user does not exists.', JLog::INFO, 'com_emundus.checklist');
					$app->enqueueMessage(JText::_('COM_USERS_USER_NOT_FOUND'), 'warning');
				}
			} else {
				JLog::add('[' . $_SERVER['REMOTE_ADDR'] . '] User ' . $current_user .  ' tried to read checklist of user ' . $student_id . ' but does not have the rights to do it.', JLog::WARNING, 'com_emundus.checklist');
				$app->enqueueMessage(JText::_('ACCESS_DENIED'), 'warning');
				$app->redirect('/checklist');
			}
		} else {
			$this->_user = JFactory::getSession()->get('emundusUser');
		}
	}

	function getGreeting() {
		$query = 'SELECT id, title, text FROM #__emundus_setup_checklist WHERE page = "checklist" ';
		$note = 0;
		if ($note && is_numeric($note) && $note > 1) {
            $this->_need = $note;
        }
		$query .= 'AND (whenneed = '.$this->_need . ' OR whenneed='.$this->_user->status.')';
		$this->_db->setQuery( $query );
		return $this->_db->loadObject();
	}

	function getInstructions() {
		$query = 'SELECT id, title, text FROM #__emundus_setup_checklist WHERE page = "instructions"';
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
				$this->_need = $this->_attachments == 1 ?: 0;
			}
		}
		return $forms;
	}

	function getAttachmentsList() {
		$attachments = [];

		if (!empty($this->_user->profile)) {
			if (!empty($this->_user->campaign_id)) {
				$query = 'SELECT attachments.*, COUNT(uploads.attachment_id) AS nb, uploads.id as uid, profiles.mandatory as mandatory, profiles.duplicate as duplicate, profiles.has_sample, profiles.sample_filepath
					FROM #__emundus_setup_attachments AS attachments
						INNER JOIN #__emundus_setup_attachment_profiles AS profiles ON attachments.id = profiles.attachment_id
						LEFT JOIN #__emundus_uploads AS uploads ON uploads.attachment_id = profiles.attachment_id AND uploads.user_id = '.$this->_user->id.'  AND fnum like '.$this->_db->Quote($this->_user->fnum).'
					WHERE (profiles.campaign_id = '.$this->_user->campaign_id.' OR profiles.profile_id ='.$this->_user->profile.') AND profiles.displayed = 1
					GROUP BY attachments.id
					ORDER BY profiles.mandatory DESC, profiles.ordering ASC';
				$this->_db->setQuery($query);
				$attachments = $this->_db->loadObjectList();
			}

			if (empty($attachments)) {
				$query = 'SELECT attachments.id, COUNT(uploads.attachment_id) AS nb, uploads.id as uid, attachments.nbmax, attachments.value, attachments.lbl, attachments.description, attachments.allowed_types, profiles.mandatory, profiles.duplicate,  profiles.has_sample, profiles.sample_filepath
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
		if ($this->_db->loadResult() == 8) {
            return false;
        }
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
		$confirm_url = '';
	    if (empty($profile)) {
	        $profile = $this->_user->profile;
        }

		if (!empty($profile)) {
			$db = JFactory::getDBO();

			$query = $db->getQuery(true);
			$query->select('CONCAT(m.link,"&Itemid=", m.id) as link')
				->from('#__emundus_setup_profiles as esp')
				->leftJoin('#__menu as m on m.menutype = esp.menutype')
				->where('esp.id = ' . $profile)
				->andWhere('m.published > 0')
				->andWhere('m.level = 1')
				->order('m.lft DESC');

			try {
				$db->setQuery($query);
				$confirm_url = $db->loadResult();
			} catch(Exception $e) {
				JLog::add('Failed to get confirm url from profile ' . $profile . ' ' . $e->getMessage(), JLog::ERROR, 'com_emundus.checklist');
			}
		} else {
			JLog::add('Failed to get confirm url from profile because profile is not set', JLog::WARNING, 'com_emundus.checklist');
		}

		return $confirm_url;
	}


	function setDelete($status = 0, $student = null) {

		$db = JFactory::getDBO();

		if (empty($student)) {
            $student = JFactory::getSession()->get('emundusUser');
        }

		if ($status > 1) {
            $status = 1;
        }

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
	function setAttachmentName(string $file, string $lbl, array $fnumInfos): string {

		$file_array = explode(".", $file);

		$eMConfig = JComponentHelper::getParams('com_emundus');
		$applicant_file_name = $eMConfig->get('applicant_file_name', null);

		if (!empty($applicant_file_name)) {

			require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
			$m_emails = new EmundusModelEmails;

			$tags = $m_emails->setTags($fnumInfos['applicant_id'], null, $fnumInfos['fnum'], '', $applicant_file_name);
			$application_form_name = preg_replace($tags['patterns'], $tags['replacements'], $applicant_file_name);
			$application_form_name = $m_emails->setTagsFabrik($application_form_name, array($fnumInfos['fnum']));

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


    public function formatFileName(String $file, String $fnum, Array $post= []): string {
        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
        $m_emails = new EmundusModelEmails;

        $aid = intval(substr($fnum, 21, 7));
        $tags = $m_emails->setTags($aid, $post, $fnum, '', $file);
        $formatted_file = preg_replace($tags['patterns'], $tags['replacements'], $file);
        $formatted_file = $m_emails->setTagsFabrik($formatted_file, array($fnum));

        // Format filename
        $formatted_file = $m_emails->stripAccents($formatted_file);
        $formatted_file = preg_replace('/[^A-Za-z0-9 _.-]/','', $formatted_file);
        $formatted_file = preg_replace('/\s/', '', $formatted_file);
        return strtolower($formatted_file);
    }
}

