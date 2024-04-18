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

	function __construct($student_id = null)
	{
		parent::__construct();
		jimport('joomla.log.log');
		JLog::addLogger(['text_file' => 'com_emundus.checklist.php'], JLog::ALL, array('com_emundus.checklist'));

		require_once (JPATH_SITE.'/components/com_emundus/helpers/menu.php');
		$this->_db = JFactory::getDBO();
		$app =  JFactory::getApplication();
		$student_id = !empty($student_id) ?? $app->input->getInt('sid', 0);
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

	function getAttachmentsList()
	{
		$attachments = [];

		if (!empty($this->_user->profile)) {
			$attachments = $this->getAttachmentsForProfile($this->_user->profile, $this->_user->campaign_id);

			foreach ($attachments as $attachment) {
				$query = $this->_db->getQuery(true);

				$query->select('COUNT(*)')
					->from('#__emundus_uploads')
					->where('user_id = ' . $this->_user->id)
					->where('attachment_id = ' . $attachment->id)
					->where('fnum like ' . $this->_db->quote($this->_user->fnum));

				$this->_db->setQuery($query);
				$attachment->nb = $this->_db->loadResult();
			}

			foreach ($attachments as $attachment) {
				if ($attachment->nb > 0) {

					$query = 'SELECT * FROM #__emundus_uploads WHERE user_id = ' . $this->_user->id . ' AND attachment_id = ' . $attachment->id . ' AND fnum like ' . $this->_db->Quote($this->_user->fnum);
					$this->_db->setQuery($query);
					$attachment->liste = $this->_db->loadObjectList();

				} elseif ($attachment->mandatory == 1) {
					$this->_attachments = 1;
					$this->_need = $this->_forms = 1 ?? 0;
				}
			}
		}

		return $attachments;
	}

	public function getAttachmentsForCampaignId($campaign_id)
	{
		$attachments = [];

		if (!empty($campaign_id)) {
			$query = $this->_db->getQuery(true);

			$query->select('DISTINCT attachments.id, attachments.nbmax, attachments.value, attachments.lbl, attachments.description, attachments.allowed_types, profiles.mandatory, profiles.duplicate, profiles.has_sample, profiles.sample_filepath')
				->from($this->_db->quoteName('#__emundus_setup_attachments', 'attachments'))
				->leftJoin($this->_db->quoteName('#__emundus_setup_attachment_profiles', 'profiles') . ' ON attachments.id = profiles.attachment_id')
				->where('profiles.campaign_id = ' . $campaign_id)
				->andWhere('profiles.displayed = 1')
				->andWhere('attachments.published = 1')
				->order('profiles.mandatory DESC, profiles.ordering ASC');

			try {
				$this->_db->setQuery($query);
				$attachments = $this->_db->loadObjectList();
			} catch (Exception $e) {
				JLog::add('Failed to get attachments for campaign ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
			}
		}

		return $attachments;
	}

	/**
	 * Get attachments for a profile
	 * Be aware that this method will dispatch onAfterGetAttachmentsForProfile event
	 * This event can be used to add or remove attachments from the list based on some conditions
	 * @param $profile_id
	 * @param $campaign_id
	 * @return array|mixed
	 */
	public function getAttachmentsForProfile($profile_id, $campaign_id = null)
	{
		$attachments = [];

		if (!empty($profile_id)) {
			if (!empty($campaign_id)) {
				$attachments = $this->getAttachmentsForCampaignId($campaign_id);
			}

			if (empty($attachments)) {
				$query = $this->_db->getQuery(true);

				$query->select('DISTINCT attachments.id, attachments.nbmax, attachments.value, attachments.lbl, attachments.description, attachments.allowed_types, profiles.mandatory, profiles.duplicate, profiles.has_sample, profiles.sample_filepath')
					->from($this->_db->quoteName('#__emundus_setup_attachments', 'attachments'))
					->leftJoin($this->_db->quoteName('#__emundus_setup_attachment_profiles', 'profiles') . ' ON attachments.id = profiles.attachment_id')
					->where('profiles.profile_id = ' . $profile_id)
					->andWhere('profiles.campaign_id IS NULL')
					->andWhere('profiles.displayed = 1')
					->andWhere('attachments.published = 1')
					->order('profiles.mandatory DESC, profiles.ordering ASC');

				try {
					$this->_db->setQuery($query);
					$attachments = $this->_db->loadObjectList();
				} catch (Exception $e) {
					JLog::add('Failed to get attachments for campaign ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
				}
			}

			// Sometimes mandatory attachments are linked to the profile but also to some form fields.
			// To allow that, we dispatch an event onAfterGetMandatoryAttachmentsForProfile
			// to allow other components to add/remove their own mandatory attachments.
			JPluginHelper::importPlugin('emundus', 'custom_event_handler');
			$dispatcher = JEventDispatcher::getInstance();
			$dispatcher->trigger('onAfterGetAttachmentsForProfile', array($profile_id, &$attachments));
			$dispatcher->trigger('callEventHandler', ['onAfterGetAttachmentsForProfile', ['profile_id' => $profile_id, 'attachments' => &$attachments]]);
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


	/**
	 * Toogle can_be_deleted student attachments state
	 *
	 * @param $can_be_deleted
	 * @param $student
	 * @return void
	 */
	function setDelete($can_be_deleted = 0, $student = null) {
		$toggled = false;

		if (empty($student)) {
			$student = JFactory::getSession()->get('emundusUser');
		}
		$can_be_deleted = $can_be_deleted >= 1 ? 1 : 0;

		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__emundus_uploads'))
			->set($db->quoteName('can_be_deleted') . ' = ' . $can_be_deleted)
			->where($db->quoteName('user_id') . ' = ' . $student->id)
			->andWhere($db->quoteName('fnum') . ' like ' . $db->quote($student->fnum));

		if ($can_be_deleted === 1) {
			$emundus_config = JComponentHelper::getParams('com_emundus');
			$attachment_ids = $emundus_config->get('attachment_to_keep_non_deletable', []);

			if (!empty($attachment_ids)) {
				$query->andWhere($db->quoteName('attachment_id') . ' NOT IN (' . implode(',', $attachment_ids) . ')');
			}
		}

		try {
			$db->setQuery($query);
			$toggled = $db->execute();
		} catch (Exception $e) {
			JLog::add('Error in model/checklist at query : '.$query, JLog::ERROR, 'com_emundus');
		}

		return $toggled;
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

