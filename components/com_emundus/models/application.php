<?php
/**
 * Application Model for eMundus Component
 *
 * @package    Joomla
 * @subpackage eMundus
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
 */

// No direct access

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus/models'); // call com_emundus model

include_once(JPATH_SITE . '/components/com_emundus/helpers/fabrik.php');

use Joomla\CMS\Filesystem\File;


class EmundusModelApplication extends JModelList
{
    var $_user = null;
    var $_db = null;

    /**
     * Constructor
     *
     * @since 1.5
     */
    public function __construct()
    {
        parent::__construct();
        global $option;
        require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'logs.php');
        require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'menu.php');
        require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'fabrik.php');
        require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'profile.php');
        require_once (JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'date.php');

        $this->_mainframe = JFactory::getApplication();

        $this->_db = JFactory::getDBO();
        $this->_user = JFactory::getSession()->get('emundusUser');

        $this->locales = substr(JFactory::getLanguage()->getTag(), 0, 2);
    }

    public function getApplicantInfos($aid, $param)
    {
        $applicant_infos = [];

        if (!empty($aid) && !empty($param)) {
            $query = 'SELECT ' .  implode(',', $param) . '
                FROM #__users
                LEFT JOIN #__emundus_users ON #__emundus_users.user_id=#__users.id
                LEFT JOIN #__emundus_personal_detail ON #__emundus_personal_detail.user=#__users.id
                LEFT JOIN #__emundus_setup_profiles ON #__emundus_setup_profiles.id=#__emundus_users.profile
                LEFT JOIN #__emundus_uploads ON (#__emundus_uploads.user_id=#__users.id AND #__emundus_uploads.attachment_id=10)
                WHERE #__users.id=' . $aid;
            $this->_db->setQuery($query);

            try {
                $applicant_infos =  $this->_db->loadAssoc();
            } catch (Exception $e) {
                JLog::add("Failed to get applicant infos for user_id $aid " . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
            }
        }

        return $applicant_infos;
    }

    public function getApplicantDetails($aid, $ids)
    {
        $details = @EmundusHelperList::getElementsDetailsByID($ids);
        $select = array();
        foreach ($details as $detail) {
            $select[] = $detail->tab_name . '.' . $detail->element_name . ' AS "' . $detail->element_id . '"';
        }

        $query = 'SELECT ' . implode(",", $select) . '
                FROM #__users as u
                LEFT JOIN #__emundus_users ON #__emundus_users.user_id=u.id
                LEFT JOIN #__emundus_personal_detail ON #__emundus_personal_detail.user=u.id
                LEFT JOIN #__emundus_setup_profiles ON #__emundus_setup_profiles.id=#__emundus_users.profile
                LEFT JOIN #__emundus_uploads ON (#__emundus_uploads.user_id=u.id AND #__emundus_uploads.attachment_id=10)
                WHERE u.id=' . $aid;
        $this->_db->setQuery($query);
        $values = $this->_db->loadAssoc();

        foreach ($details as $detail) {
            $detail->element_value = $values[$detail->element_id];
        }
        return $details;
    }

    public function getUserCampaigns($id, $cid = null, $published_only = true)
    {
		$user_campaigns = [];

        $query = $this->_db->getQuery(true);
	    $query->select('esc.*,ecc.date_submitted,ecc.submitted,ecc.id as campaign_candidature_id,efg.result_sent,efg.date_result_sent,efg.final_grade,ecc.fnum,ess.class,ess.step,ess.value as step_value')
		    ->from($this->_db->quoteName('#__emundus_users','eu'))
		    ->leftJoin($this->_db->quoteName('#__emundus_campaign_candidature','ecc').' ON '.$this->_db->quoteName('ecc.applicant_id').' = '.$this->_db->quoteName('eu.user_id'))
		    ->leftJoin($this->_db->quoteName('#__emundus_setup_campaigns','esc').' ON '.$this->_db->quoteName('ecc.campaign_id').' = '.$this->_db->quoteName('esc.id'))
		    ->leftJoin($this->_db->quoteName('#__emundus_final_grade','efg').' ON '.$this->_db->quoteName('efg.campaign_id').' = '.$this->_db->quoteName('esc.id').' AND '.$this->_db->quoteName('efg.student_id').' = '.$this->_db->quoteName('eu.user_id'))
		    ->leftJoin($this->_db->quoteName('#__emundus_setup_status','ess').' ON '.$this->_db->quoteName('ess.step').' = '.$this->_db->quoteName('ecc.status'))
		    ->where($this->_db->quoteName('eu.user_id').' = '.$this->_db->quote($id))
		    ->andWhere($this->_db->quoteName('ecc.published').' = '.$this->_db->quote(1));

		if ($cid === null) {
			if ($published_only) {
				$query->andWhere($this->_db->quoteName('esc.published') . ' = ' . $this->_db->quote(1));
	        }

            $this->_db->setQuery($query);
	        $user_campaigns = $this->_db->loadObjectList();
        } else {
			$query->andWhere($this->_db->quoteName('esc.id').' = '.$this->_db->quote($cid));

            $this->_db->setQuery($query);
	        $user_campaigns =  $this->_db->loadObject();
        }

		return $user_campaigns;
    }

    public function getCampaignByFnum($fnum)
    {
        $query = 'SELECT esc.*, ecc.date_submitted, ecc.submitted, ecc.id as campaign_candidature_id, efg.result_sent, efg.date_result_sent, efg.final_grade, ecc.fnum, ess.class, ess.step, ess.value as step_value
            FROM #__emundus_users eu
            LEFT JOIN #__emundus_campaign_candidature ecc ON ecc.applicant_id=eu.user_id
            LEFT JOIN #__emundus_setup_campaigns esc ON ecc.campaign_id=esc.id
            LEFT JOIN #__emundus_final_grade efg ON efg.campaign_id=esc.id AND efg.student_id=eu.user_id
            LEFT JOIN #__emundus_setup_status as ess ON ess.step = ecc.status
            WHERE ecc.fnum like ' . $fnum;

        $this->_db->setQuery($query);
        return $this->_db->loadObjectList();
    }

    public function getUserAttachments($id)
    {

        $query = 'SELECT eu.id AS aid, esa.*, eu.filename, eu.description, eu.timedate, esc.label as campaign_label, esc.year, esc.training
            FROM #__emundus_uploads AS eu
            LEFT JOIN #__emundus_setup_attachments AS esa ON  eu.attachment_id=esa.id
            LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id=eu.campaign_id
            WHERE eu.user_id = ' . $id . '
            ORDER BY esa.category,esa.ordering,esa.value';
        $this->_db->setQuery($query);
        return $this->_db->loadObjectList();
    }

    function getUserAttachmentsByFnum($fnum, $search = '', $profile = null)
    {
		$attachments = [];

		if (!empty($fnum)) {
			$eMConfig = JComponentHelper::getParams('com_emundus');
			$expert_document_id = $eMConfig->get('expert_document_id', '36');

			$query = $this->_db->getQuery(true);
			$query->select('eu.id AS aid, eu.user_id, esa.*, eu.attachment_id, eu.filename, eu.description  AS upload_description, eu.timedate, eu.can_be_deleted, eu.can_be_viewed, eu.is_validated, eu.modified, eu.modified_by, esc.label as campaign_label, esc.year, esc.training')
				->from($this->_db->quoteName('#__emundus_uploads', 'eu'))
				->leftJoin($this->_db->quoteName('#__emundus_setup_attachments', 'esa') . ' ON ' . $this->_db->quoteName('eu.attachment_id') . ' = ' . $this->_db->quoteName('esa.id'));

			if(!empty($profile)){
				$query->leftJoin($this->_db->quoteName('#__emundus_setup_attachment_profiles', 'esap') . ' ON ' . $this->_db->quoteName('esa.id') . ' = ' . $this->_db->quoteName('esap.attachment_id'));
			}
			$query->leftJoin($this->_db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $this->_db->quoteName('esc.id') . ' = ' . $this->_db->quoteName('eu.campaign_id'))
				->where($this->_db->quoteName('eu.fnum') . ' LIKE ' . $this->_db->quote($fnum))
				->andWhere('esa.lbl NOT LIKE ' . $this->_db->quote('_application_form'));

			if (!empty($this->_user) && EmundusHelperAccess::isExpert($this->_user->id)) {
				$query->andWhere($this->_db->quoteName('esa.id') . ' != ' . $expert_document_id);
			}

			if (isset($search) && !empty($search)) {
				$query->andWhere($this->_db->quoteName('esa.value') . ' LIKE ' . $this->_db->quote('%' . $search . '%')
					. ' OR ' . $this->_db->quoteName('esa.description') . ' LIKE ' . $this->_db->quote('%' . $search . '%')
					. ' OR ' . $this->_db->quoteName('eu.timedate') . ' LIKE ' . $this->_db->quote('%' . $search . '%'));
			}

			if (!empty($profile)) {
				$query->andWhere($this->_db->quoteName('esap.profile_id') . ' = ' . $this->_db->quote($profile));
				$query->order($this->_db->quoteName('esap.mandatory') . ' DESC, ' . $this->_db->quoteName('esap.ordering') . ', ' . $this->_db->quoteName('esa.value') . ' ASC');
			} else {
				$query->order($this->_db->quoteName('eu.modified') . ' DESC');
			}

			try {
				$this->_db->setQuery($query);
				$attachments = $this->_db->loadObjectList();
			} catch(Exception $e) {
				JLog::add('Error getting user attachments in model at query : '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus.error');
			}

			if (!empty($attachments)) {
				$allowed_attachments = EmundusHelperAccess::getUserAllowedAttachmentIDs(JFactory::getUser()->id);
				if ($allowed_attachments !== true) {
					foreach ($attachments as $key => $attachment) {
						if (!in_array($attachment->id, $allowed_attachments)) {
							unset($attachments[$key]);
						}
					}
				}

				$query->clear()
					->select('applicant_id')
					->from($this->_db->quoteName('#__emundus_campaign_candidature'))
					->where($this->_db->quoteName('fnum').' LIKE '.$this->_db->quote($fnum));

				$this->_db->setQuery($query);
				$applicant_id = $this->_db->loadResult();

				foreach($attachments as $attachment) {
					if (!file_exists(EMUNDUS_PATH_ABS.$applicant_id.'/'.$attachment->filename)) {
						$attachment->existsOnServer = false;
					} else {
						$attachment->existsOnServer = true;
					}

					$query->clear()
						->select('profile_id')
						->from($this->_db->quoteName('#__emundus_setup_attachment_profiles'))
						->where($this->_db->quoteName('attachment_id').' = '.$this->_db->quote($attachment->attachment_id));
					$this->_db->setQuery($query);
					$attachment->profiles = $this->_db->loadColumn();
                    $attachment->upload_description = empty($attachment->upload_description)? '' : $attachment->upload_description;
				}

				if ($attachments !== array_values($attachments)) {
					$attachments = array_values($attachments);
				}
			}
		}

        return $attachments;
    }

    public function getUsersComments($id)
    {
        $query = 'SELECT ec.id, ec.comment_body as comment, ec.reason, ec.date, u.name
                FROM #__emundus_comments ec
                LEFT JOIN #__users u ON u.id = ec.user_id
                WHERE ec.applicant_id ="' . $id . '"
                ORDER BY ec.date DESC ';
        $this->_db->setQuery($query);
        return $this->_db->loadObjectList();
    }

    public function getComment($id)
    {
        $query = 'SELECT * FROM #__emundus_comments ec WHERE ec.id =' . $id;
        $this->_db->setQuery($query);
        return $this->_db->loadAssoc();
    }

    public function getTag($id)
    {
        $query = 'SELECT * FROM #__emundus_tag_assoc WHERE id =' . $id;
        $this->_db->setQuery($query);
        return $this->_db->loadAssoc();
    }

    public function getFileComments($fnum)
    {

        $query = 'SELECT ec.id, ec.comment_body as comment, ec.reason, ec.fnum, ec.user_id, ec.date, u.name
                FROM #__emundus_comments ec
                LEFT JOIN #__users u ON u.id = ec.user_id
                WHERE ec.fnum like ' . $this->_db->Quote($fnum) . '
                ORDER BY ec.date ASC ';
        $this->_db->setQuery($query);
        return $this->_db->loadObjectList();
    }

    public function getFileOwnComments($fnum, $user_id)
    {

        $query = 'SELECT ec.id, ec.comment_body as comment, ec.reason, ec.fnum, ec.user_id, ec.date, u.name
                FROM #__emundus_comments ec
                LEFT JOIN #__users u ON u.id = ec.user_id
                WHERE ec.fnum like ' . $this->_db->Quote($fnum) . '
                AND ec.user_id = ' . $user_id . '
                ORDER BY ec.date ASC ';
        $this->_db->setQuery($query);
        return $this->_db->loadObjectList();
    }

    public function editComment($id, $title, $text)
    {

        try {
            // Get the old comment content for the logging system
            $query = 'SELECT reason, comment_body FROM #__emundus_comments WHERE id =' . $this->_db->quote($id);
            $this->_db->setQuery($query);
            $old_comment = $this->_db->loadObject();

            // Update the comment content
            $query = 'UPDATE #__emundus_comments SET reason = ' . $this->_db->quote($title) . ', comment_body = ' . $this->_db->quote($text) . '  WHERE id = ' . $this->_db->quote($id);
            $this->_db->setQuery($query);
            $this->_db->execute();

            // Logging requires the fnum, we have to get this from the comment ID being edited.
            // Only get the fnum if logging is on and comments are in the list of actions to be logged.
            $eMConfig = JComponentHelper::getParams('com_emundus');
            $log_actions = $eMConfig->get('log_action', null);
            if ($eMConfig->get('logs', 0) && (empty($log_actions) || in_array(10, explode(',', $log_actions)))) {

                $query = $this->_db->getQuery(true);
                $query->select($this->_db->quoteName('fnum'))
                    ->from($this->_db->quoteName('#__emundus_comments'))
                    ->where($this->_db->quoteName('id') . '=' . $id);

                $this->_db->setQuery($query);
                $fnum = $this->_db->loadResult();

                // Log the comment in the eMundus logging system.
                $logsParams = array('updated' => []);

                if(empty(trim($old_comment->reason))) {
                    $old_comment->reason = JText::_('COM_EMUNDUS_COMMENT_NO_TITLE');
                }

                if(empty(trim($title))) {
                    $title = JText::_('COM_EMUNDUS_COMMENT_NO_TITLE');
                }

                if ($old_comment->reason !== $title) {
                    array_push($logsParams['updated'], ['description' => '<b>' . '[' . $old_comment->reason . ']' . '</b>', 'element' => '<span>' . JText::_('COM_EMUNDUS_EDIT_COMMENT_TITLE') . '</span>',
                        'old' => $old_comment->reason,
                        'new' => $title]);
                }

                /////////////
                if ($old_comment->comment_body !== $text) {
                    array_push($logsParams['updated'], ['description' => '<b>' . '[' . $old_comment->reason . ']' . '</b>', 'element' => '<span>' . JText::_('COM_EMUNDUS_EDIT_COMMENT_BODY') . '</span>',
                        'old' => $old_comment->comment_body,
                        'new' => $text]);
                }

                if (!empty($logsParams['updated'])) {
                    $logsParams['updated'] = array_values($logsParams['updated']);
                    EmundusModelLogs::log(JFactory::getUser()->id, (int)substr($fnum, -7), $fnum, 10, 'u', 'COM_EMUNDUS_ACCESS_COMMENT_FILE_UPDATE', json_encode($logsParams, JSON_UNESCAPED_UNICODE));
                }
            }

            return true;

        } catch (Exception $e) {
            JLog::add('Query: ' . $query . ' Error:' . $e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }

    }

    public function deleteComment($id, $fnum = null)
    {

        $query = $this->_db->getQuery(true);

        if (empty($fnum)) {
            $query->select($this->_db->quoteName('fnum'))
                ->from($this->_db->quoteName('#__emundus_comments'))
                ->where($this->_db->quoteName('id') . ' = ' . $id);
            $this->_db->setQuery($query);

            try {
                $this->_db->execute();
            } catch (Exception $e) {
                JLog::add('Error getting fnum for comment id ' . $id . ' in m/application.', JLog::ERROR, 'com_emundus');
            }
        }

        // Get the comment for logs
        $query->select($this->_db->quoteName('reason') . ',' . $this->_db->quoteName('comment_body'))
            ->from($this->_db->quoteName('#__emundus_comments'))
            ->where($this->_db->quoteName('id') . ' = ' . $id);
        $this->_db->setQuery($query);
        $deleted_comment = $this->_db->loadObject();

        // Delete comment
        $query->clear()->delete($this->_db->quoteName('#__emundus_comments'))
            ->where($this->_db->quoteName('id') . ' = ' . $id);
        $this->_db->setQuery($query);

        // Log the comments in the eMundus logging system.
        $logsStd = new stdClass();

        try {
            $res = $this->_db->execute();
        } catch (Exception $e) {
            JLog::add('Error deleting comment id ' . $id . ' in m/application. ERROR -> ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }

        if ($res && !empty($fnum)) {
            // Log the comment in the eMundus logging system
            // Log only the body if the comment had no title
            if (empty($deleted_comment->reason)) {
                $logsStd->element = '[' . JText::_('COM_EMUNDUS_COMMENT_NO_TITLE') . ']';
                $logsStd->details = $deleted_comment->comment_body;
            } else {
                $logsStd->element = "[" . $deleted_comment->reason . "]";
                $logsStd->details = $deleted_comment->comment_body;
            }

            $logsParams = array('deleted' => [$logsStd]);
            EmundusModelLogs::log(JFactory::getUser()->id, (int)substr($fnum, -7), $fnum, 10, 'd', 'COM_EMUNDUS_ACCESS_COMMENT_FILE_DELETE', json_encode($logsParams, JSON_UNESCAPED_UNICODE));
        }

        return $res;

    }

    public function deleteTag($id_tag, $fnum, $user_id = null)
    {
        $query = $this->_db->getQuery(true);

		// Get the tag for logs
        $query->select($this->_db->quoteName('label'))
            ->from($this->_db->quoteName('#__emundus_setup_action_tag'))
            ->where($this->_db->quoteName('id') . ' = ' . $id_tag);
        $this->_db->setQuery($query);
        $deleted_tag = $this->_db->loadResult();

		$query->clear()
			->delete($this->_db->quoteName('#__emundus_tag_assoc'))
			->where($this->_db->quoteName('id_tag') . ' = ' . $id_tag)
			->andWhere($this->_db->quoteName('fnum') . ' like ' . $this->_db->Quote($fnum));

		if (!empty($user_id)) {
			$query->andWhere($this->_db->quoteName('user_id') . ' = ' . $user_id);
		}

        $this->_db->setQuery($query);
        $res = $this->_db->execute();

        if ($res) {
	        // Log the tag in the eMundus logging system.
	        $logsStd = new stdClass();
            $logsStd->details = $deleted_tag;
            $logsParams = array('deleted' => [$logsStd]);
            $user_id = JFactory::getUser()->id;
            $user_id = empty($user_id) ? 62 : $user_id;
            EmundusModelLogs::log($user_id, (int)substr($fnum, -7), $fnum, 14, 'd', 'COM_EMUNDUS_ACCESS_TAGS_DELETE', json_encode($logsParams, JSON_UNESCAPED_UNICODE));
        }

        return $res;
    }

    public function addComment($row)
    {
        // Log the comment in the eMundus logging system.
        $logsStd = new stdClass();
        // Log only the body if there is no title
        if (empty($row['reason'])) {
            $logsStd->element = '[' . JText::_('COM_EMUNDUS_COMMENT_NO_TITLE') . ']';
            $logsStd->details = $row['comment_body'];
        } else {
            $logsStd->element = '[' . $row['reason'] . ']';
            $logsStd->details = $row['comment_body'];
        }

        //$logsStd->details =  $row['comment_body'];

        $logsParams = array('created' => [$logsStd]);
        EmundusModelLogs::log(JFactory::getUser()->id, (int)substr($row['fnum'], -7), $row['fnum'], 10, 'c', 'COM_EMUNDUS_ACCESS_COMMENT_FILE_CREATE', json_encode($logsParams, JSON_UNESCAPED_UNICODE));

	    $now = EmundusHelperDate::getNow();

        $query = 'INSERT INTO `#__emundus_comments` (applicant_id, user_id, reason, date, comment_body, fnum)
                VALUES('.$row['applicant_id'].','.$row['user_id'].','.$this->_db->Quote($row['reason']).',"'.$now.'",'.$this->_db->Quote($row['comment_body']).','.$this->_db->Quote(@$row['fnum']).')';
        $this->_db->setQuery($query);

        try {
            $this->_db->execute();
            return $this->_db->insertid();
        } catch (Exception $e) {
            JLog::add('Error in model/application at query: ' . $query, JLog::ERROR, 'com_emundus');
            return null;
        }
    }

    public function deleteData($id, $table)
    {
        $query = 'DELETE FROM `' . $table . '` WHERE id=' . $id;
        $this->_db->setQuery($query);

        return $this->_db->Query();
    }

    public function deleteAttachment($id)
    {
	    $deleted = false;

		if (!empty($id)) {
			$query = $this->_db->getQuery(true);
			try {
				$query->clear()
					->select('*')
					->from('#__emundus_uploads')
					->where('id = ' . $id);

				$this->_db->setQuery($query);
				$file = $this->_db->loadAssoc();
			} catch (Exception $e) {
				JLog::add('Error in model/application at query: ' . $query, JLog::ERROR, 'com_emundus');
			}

			if (!empty($file)) {
				$f = EMUNDUS_PATH_ABS . $file['user_id'] . DS . $file['filename'];
				$deleted_file = unlink($f);

				if ($deleted_file) {
					try {
						$query->clear()
							->delete('#__emundus_uploads')
							->where('id = ' . $id);
						$this->_db->setQuery($query);
						$deleted = $this->_db->execute();

						if ($deleted) {
							$logsStd = new stdClass();
							$attachmentTpe = $this->getAttachmentByID($file['attachment_id']);

							$logsStd->element = '[' . $attachmentTpe['value'] . ']';
							$logsStd->details = $file['filename'];
							$logsParams = array('deleted' => [$logsStd]);

							EmundusModelLogs::log(JFactory::getUser()->id, (int)substr($file['fnum'], -7), $file['fnum'], 4, 'd', 'COM_EMUNDUS_ACCESS_ATTACHMENT_DELETE', json_encode($logsParams, JSON_UNESCAPED_UNICODE));
						}
					} catch (Exception $e) {
						JLog::add('Error in model/application at query: ' . $query, JLog::ERROR, 'com_emundus');
					}
				}
			}
		}

		return $deleted;
    }

    public function uploadAttachment($data)
    {
		$upload_id = false;

		if (!empty($data) && !empty($data['key']) && !empty($data['value'])) {
			try {
				$values = implode(',', $this->_db->quote($data['value']));

				$query = $this->_db->getQuery(true);
				$query->insert($this->_db->quoteName('#__emundus_uploads'))
					->columns($data['key'])
					->values($values);

				$this->_db->setQuery($query);
				$this->_db->execute();
				$upload_id = $this->_db->insertid();
			} catch (RuntimeException $e) {
				JFactory::getApplication()->enqueueMessage($e->getMessage());
				JLog::add('Error in model/application at query: ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
			}
		}

		return $upload_id;
    }

    public function getAttachmentByID($id)
    {
        $query = "SELECT * FROM #__emundus_setup_attachments WHERE id=" . $id;
        $this->_db->setQuery($query);

        return $this->_db->loadAssoc();
    }

    public function getAttachmentByLbl($label)
    {
        $query = "SELECT * FROM #__emundus_setup_attachments WHERE lbl LIKE" . $this->_db->Quote($label);
        $this->_db->setQuery($query);

        return $this->_db->loadAssoc();
    }

    public function getUploadByID($id)
    {
        $query = "SELECT * FROM #__emundus_uploads WHERE id=" . $id;
        $this->_db->setQuery($query);

        return $this->_db->loadAssoc();
    }

    /**
     * @param string $fnum
     *
     * @return array|bool|false|float
     *
     * @since version
     */
    public function getFormsProgress($fnum = "0",$use_session = 0)
    {
        require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'profile.php');
        $m_profile = new EmundusModelProfile;

        if (empty($fnum) || (!is_array($fnum) && !is_numeric($fnum))) {
            return false;
        }

        $session = JFactory::getSession();
        $current_user = $session->get('emundusUser');

        if (!is_array($fnum)) {
            $profile_by_status = $m_profile->getProfileByStatus($fnum,$use_session);

            if (empty($profile_by_status['profile'])) {
                $query = 'SELECT esc.profile_id AS profile_id, ecc.campaign_id AS campaign_id
                    FROM #__emundus_setup_campaigns AS esc
                    LEFT JOIN #__emundus_campaign_candidature AS ecc ON ecc.campaign_id = esc.id
                    WHERE ecc.fnum like ' . $this->_db->Quote($fnum);
                $this->_db->setQuery($query);

                $profile_by_status = $this->_db->loadAssoc();
            }

            $profile = !empty($profile_by_status["profile_id"]) ? $profile_by_status["profile_id"] : $profile_by_status["profile"];
            $profile_id = (!empty($current_user->fnums[$fnum]) && $current_user->profile != $profile && $current_user->applicant === 1) ? $current_user->profile : $profile;

            $forms = @EmundusHelperMenu::getUserApplicationMenu($profile_id);
            $nb = 0;
            $formLst = array();

            if (empty($forms)) {
                return 100;
            }

            foreach ($forms as $form) {
                $query = 'SELECT count(*) FROM ' . $form->db_table_name . ' WHERE fnum like ' . $this->_db->Quote($fnum);
                $this->_db->setQuery($query);
                $cpt = $this->_db->loadResult();
                if ($cpt == 1) {
                    $nb++;
                } else {
                    $formLst[] = $form->label;
                }
            }

            $this->updateFormProgressByFnum(@floor(100 * $nb / count($forms)), $fnum);
            return @floor(100 * $nb / count($forms));

        } else {

            $result = array();
            foreach ($fnum as $f) {
                $profile_by_status = $m_profile->getProfileByStatus($f);

                if (empty($profile_by_status["profile"])) {
                    $query = 'SELECT esc.profile_id AS profile_id, ecc.campaign_id AS campaign_id
                FROM #__emundus_setup_campaigns AS esc
                LEFT JOIN #__emundus_campaign_candidature AS ecc ON ecc.campaign_id = esc.id
                WHERE ecc.fnum like ' . $this->_db->Quote($f);
                    $this->_db->setQuery($query);

                    $profile_by_status = $this->_db->loadAssoc();
                }

                $profile_id = !empty($profile_by_status["profile_id"]) ? $profile_by_status["profile_id"] : $profile_by_status["profile"];

                $forms = @EmundusHelperMenu::buildMenuQuery($profile_id);
                $nb = 0;
                $formLst = array();

                if (empty($forms)) {
                    $result[$f] = 100;
                } else {
                    foreach ($forms as $form) {
                        $query = 'SELECT count(*) FROM ' . $form->db_table_name . ' WHERE fnum like ' . $this->_db->Quote($f);
                        $this->_db->setQuery($query);
                        $cpt = $this->_db->loadResult();
                        if ($cpt == 1) {
                            $nb++;
                        } else {
                            $formLst[] = $form->label;
                        }
                    }
                    $this->updateFormProgressByFnum(@floor(100 * $nb / count($forms)), $f);
                    $result[$f] = @floor(100 * $nb / count($forms));
                }
            }
            return $result;
        }
    }

    public function getFormsProgressWithProfile($fnum, $profile_id)
    {
        $forms = @EmundusHelperMenu::getUserApplicationMenu($profile_id);
        $nb = 0;

        if (empty($forms)) {
            return 100;
        }

        foreach ($forms as $form) {
            $query = 'SELECT count(*) FROM ' . $form->db_table_name . ' WHERE fnum like ' . $this->_db->Quote($fnum);
            $this->_db->setQuery($query);
            $cpt = $this->_db->loadResult();
            if ($cpt == 1) {
                $nb++;
            }
        }

        $this->updateFormProgressByFnum(@floor(100 * $nb / count($forms)), $fnum);
        return @floor(100 * $nb / count($forms));
    }

    public function updateFormProgressByFnum($result, $fnum)
    {
        $query = $this->_db->getQuery(true);
        $query->update($this->_db->quoteName('#__emundus_campaign_candidature'))
            ->set($this->_db->quoteName('form_progress') . ' = ' . $this->_db->quote($result))
            ->where($this->_db->quoteName('fnum') . ' = ' . $this->_db->quote($fnum));
        $this->_db->setQuery($query);
        return $this->_db->execute();
    }


    public function getFilesProgress($fnum = null)
    {
        if (empty($fnum) || (!is_array($fnum) && !is_numeric($fnum))) {
            return false;
        }

        $m_profile = new EmundusModelProfile();

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        if (!is_array($fnum)) {
            $fnum = [$fnum];
        }

        $result = array();
        foreach ($fnum as $f) {

            // get profile for each fnum
            $current_profile = $m_profile->getProfileByFnum($f);
            $this->getFormsProgressWithProfile($f,$current_profile);

            $query->clear()
                ->select('attachment_progress, form_progress')
                ->from('#__emundus_campaign_candidature')
                ->where($db->quoteName('fnum') . ' = ' . $db->quote($f));
            $db->setQuery($query);

            $progress = $db->loadObject();

            $result['attachments'][$f] = $progress->attachment_progress;
            $result['forms'][$f] = $progress->form_progress;
        }

        return $result;
    }

    /**
     * @param null $fnum
     *
     * @return array|bool|false|float
     *
     * @since version
     */
	public function getAttachmentsProgress($fnums = null,$use_session = 0)
	{
		$progress = 0.0;
		$return_array = true;

		if (empty($fnums) || (!is_array($fnums) && !is_numeric($fnums))) {
			$progress = 0.0;
		} else {
			$session = JFactory::getSession();
			$current_user = $session->get('emundusUser');
			require_once(JPATH_ROOT . '/components/com_emundus/models/profile.php');
			require_once(JPATH_ROOT . '/components/com_emundus/models/checklist.php');
			$m_profile = new EmundusModelProfile;
			$m_checklist = new EmundusModelChecklist;

			if (!is_array($fnums)) {
				$fnums = [$fnums];
				$return_array = false;
			}

			$result = array();
			foreach ($fnums as $f) {
				$result[$f] = 0.0;

				$profile_by_status = $m_profile->getProfileByStatus($f,$use_session);

				if (empty($profile_by_status["profile"])) {
					$query = 'SELECT esc.profile_id AS profile_id, ecc.campaign_id AS campaign_id
                FROM #__emundus_setup_campaigns AS esc
                LEFT JOIN #__emundus_campaign_candidature AS ecc ON ecc.campaign_id = esc.id
                WHERE ecc.fnum like ' . $this->_db->Quote($f);
					$this->_db->setQuery($query);
					$profile_by_status = $this->_db->loadAssoc();
				}

				if(!empty($profile_by_status))
				{
					$profile_id  = !empty($profile_by_status["profile_id"]) ? $profile_by_status["profile_id"] : $profile_by_status["profile"];
					$campaign_id = $profile_by_status["campaign_id"];

					$attachments = $m_checklist->getAttachmentsForProfile($profile_id, $campaign_id);

					// verify
					// check how many attachments are completed
					$completion = 0;

					if (!empty($attachments))
					{
						$nb_mandatory_attachments = 0;
						foreach ($attachments as $attachment)
						{
							if ($attachment->mandatory == 1)
							{
								$nb_mandatory_attachments++;
							}
						}

						if ($nb_mandatory_attachments > 0)
						{
							foreach ($attachments as $attachment)
							{
								if ($attachment->mandatory == 1)
								{
									$query = $this->_db->getQuery(true);

									$query->select('count(*)')
										->from($this->_db->quoteName('#__emundus_uploads'))
										->where($this->_db->quoteName('fnum') . ' = ' . $this->_db->quote($f))
										->andWhere($this->_db->quoteName('attachment_id') . ' = ' . $attachment->id);
									$this->_db->setQuery($query);
									$nb = $this->_db->loadResult();

									if ($nb > 0)
									{
										$completion += 100 / $nb_mandatory_attachments;
									}
								}
							}
						}
						else
						{
							$completion = 100;
						}
					}
					else
					{
						$completion = 100;
					}


					$this->updateAttachmentProgressByFnum(floor($completion), $f);
					$result[$f] = floor($completion);
				}
			}
			$progress = $result;
		}

		if (!$return_array && count($progress) == 1) {
			$progress = $progress[$fnums[0]];
		}

		return $progress;
	}

    /**
     * @deprecated since version 1.38.0, use getAttachmentsProgress instead
     *
     * @param $fnum
     *
     * @return array|bool|false|float
     *
     * @since version 1.28.0
     */
    public function getAttachmentsProgressWithProfile($fnum, $profile_id)
    {
        if (empty($fnum)) {
            return false;
        }

        require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'profile.php');
        $m_profile = new EmundusModelProfile;
        $profile_by_status = $m_profile->getProfileByStatus($fnum);

        $query = 'SELECT COUNT(profiles.id)
            FROM #__emundus_setup_attachment_profiles AS profiles
            WHERE profiles.campaign_id = ' . intval($profile_by_status["campaign_id"]) . ' AND profiles.displayed = 1';

        $this->_db->setQuery($query);
        $attachments = $this->_db->loadResult();

        if (intval($attachments) == 0) {
            $query = 'SELECT IF(COUNT(profiles.attachment_id)=0, 100, 100*COUNT(uploads.attachment_id>0)/COUNT(profiles.attachment_id))
            FROM #__emundus_setup_attachment_profiles AS profiles
            LEFT JOIN #__emundus_uploads AS uploads ON uploads.attachment_id = profiles.attachment_id AND uploads.fnum like ' . $this->_db->Quote($fnum) . '
            WHERE profiles.profile_id = ' . $profile_id . ' AND profiles.displayed = 1 AND profiles.mandatory = 1';
        } else {
            $query = 'SELECT IF(COUNT(profiles.attachment_id)=0, 100, 100*COUNT(uploads.attachment_id>0)/COUNT(profiles.attachment_id))
            FROM #__emundus_setup_attachment_profiles AS profiles
            LEFT JOIN #__emundus_uploads AS uploads ON uploads.attachment_id = profiles.attachment_id AND uploads.fnum like ' . $this->_db->Quote($fnum) . '
            WHERE profiles.campaign_id = ' . $profile_by_status["campaign_id"] . ' AND profiles.displayed = 1 AND profiles.mandatory = 1';
        }

        $this->_db->setQuery($query);
        $doc_result = $this->_db->loadResult();
        $this->updateAttachmentProgressByFnum(floor($doc_result), $fnum);
        return floor($doc_result);
    }

    public function updateAttachmentProgressByFnum($result, $fnum)
    {
		$updated = false;

		if (!empty($fnum)) {
			$query = $this->_db->getQuery(true);
			$query->update($this->_db->quoteName('#__emundus_campaign_candidature'))
				->set($this->_db->quoteName('attachment_progress') . ' = ' . $this->_db->quote($result))
				->where($this->_db->quoteName('fnum') . ' = ' . $this->_db->quote($fnum));

			try {
				$this->_db->setQuery($query);
				$updated = $this->_db->execute();
			} catch (Exception $e) {
				$updated = false;
			}
		}

        return $updated;
    }

    public function  checkFabrikValidations($fnum, $redirect = false, $itemId = null) {
        $validate = true;

        if (!empty($fnum)) {
            require_once(JPATH_SITE . '/components/com_emundus/models/profile.php');
            $m_profile = new EmundusModelProfile;
            $profile = $m_profile->getProfileByStatus($fnum);

            if (!empty($profile['profile'])) {
	            require_once(JPATH_SITE . '/components/com_emundus/models/form.php');
                $m_form = new EmundusModelForm;
                $forms = $m_form->getFormsByProfileId($profile['profile']);

                if (!empty($forms)) {
                    $form_ids = array_map(function($form) {return $form->id;}, $forms);

                    $query = $this->_db->getQuery(true);
                    $query->select('jfe.label, jfe.params, jff.form_id')
                        ->from('jos_fabrik_elements as jfe')
                        ->leftJoin('jos_fabrik_formgroup jff on jfe.group_id = jff.group_id')
                        ->where('jff.form_id IN (' . implode(',', $form_ids) . ')')
                        ->andWhere('jfe.plugin = ' . $this->_db->quote('emundus_fileupload'))
                        ->andWhere('jfe.published = 1')
                        ->andWhere('JSON_SEARCH(jfe.params, "one", "notempty")  != ""');

                    try {
                        $this->_db->setQuery($query);
                        $elements_params = $this->_db->loadObjectList();
                    } catch (Exception $e) {
                        JLog::add('Failed to check if emundus fileuploads fields are correctly filled ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
                    }

                    if (!empty($elements_params)) {
                        foreach ($elements_params as $element) {
                            $params = json_decode($element->params, true);
                            $notempty_key = array_search('notempty', $params['validations']['plugin']);

                            if ($params['validations']['plugin_published'][$notempty_key] == 1) {
                                // check user uploaded file
                                $query->clear()
                                    ->select('id')
                                    ->from('#__emundus_uploads')
                                    ->where('fnum LIKE ' . $this->_db->quote($fnum))
                                    ->andWhere('attachment_id = ' . $params['attachmentId']);

                                try {
                                    $this->_db->setQuery($query);
                                    $is_uploaded =  $this->_db->loadResult();
                                    if (empty($is_uploaded)) {
                                        $form_label = '';
                                        foreach($forms as $form) {
                                            if ($form->id == $element->form_id) {
                                                $form_label = JText::_($form->label);
                                                break;
                                            }
                                        }

                                        $app = JFactory::getApplication();
                                        $app->enqueueMessage(sprintf(JText::_('COM_EMUNDUS_MISSING_MANDATORY_FILE_UPLOAD'), '<b>' . JText::_($element->label) . '</b>', '<b>' . $form_label . '</b>')  , 'warning');
                                        if ($redirect && !empty($itemId)) {
                                            $app->redirect("index.php?option=com_fabrik&view=form&formid=" . $element->form_id . "&Itemid=$itemId&usekey=fnum&rowid=$fnum");
                                        }
                                        return false;
                                    }
                                } catch (Exception $e) {
                                    JLog::add('Failed to check if emundus fileuploads fields are correctly filled ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
                                }
                            }
                        }
                    }
                }
            }
        }

        return $validate;
    }

    /**
     * @param $aid
     *
     * @return bool|mixed
     *
     * @since version
     */
    public function getLogged($aid)
    {
        $user = JFactory::getUser();
        $query = 'SELECT s.time, s.client_id, u.id, u.name, u.username
                    FROM #__session AS s
                    LEFT JOIN #__users AS u on s.userid = u.id
                    WHERE u.id = "' . $aid . '"';
        $this->_db->setQuery($query);
        $results = $this->_db->loadObjectList();

        // Check for database errors
        if ($error = $this->_db->getErrorMsg()) {
            JError::raiseError(500, $error);
            return false;
        }

        foreach ($results as $k => $result) {
            $results[$k]->logoutLink = '';

            if ($user->authorise('core.manage', 'com_users')) {
                $results[$k]->editLink = JRoute::_('index.php?option=com_emundus&view=users&edit=1&rowid=' . $result->id . '&tmpl=component');
                $results[$k]->logoutLink = JRoute::_('index.php?option=com_login&task=logout&uid=' . $result->id . '&' . JSession::getFormToken() . '=1');
            }
            $results[$k]->name = $results[$k]->username;
        }

        return $results;
    }


    /**
     * @param        $formID
     * @param        $aid
     * @param int $fnum
     *
     * @return string|null
     *
     * @since version
     */
    public function getFormByFabrikFormID($formID, $aid, $fnum = 0)
    {
        $h_access = new EmundusHelperAccess;
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $show_empty_fields = $eMConfig->get('show_empty_fields', 1);

        $form = '';

        // Get table by form ID
        $query = 'SELECT fbtables.id AS table_id, fbtables.form_id, fbforms.label, fbtables.db_table_name
                    FROM #__fabrik_forms AS fbforms
                    LEFT JOIN #__fabrik_lists AS fbtables ON fbtables.form_id = fbforms.id
                    WHERE fbforms.id IN (' . implode(',', $formID) . ')
                    ORDER BY find_in_set(fbforms.id, "' . implode(',', $formID) . '")';

        try {
            $this->_db->setQuery($query);
            $table = $this->_db->loadObjectList();
        } catch (Exception $e) {
            return null;
        }


        for ($i = 0; $i < sizeof($table); $i++) {
            $form .= '<br><hr><div class="TitleAdmission"><h2>';

            $title = explode('-', $table[$i]->label);
            $form .= !empty($title[1]) ? JText::_(trim($title[1])) : JText::_(trim($title[0]));

            $form .= '</h2>';
            if ($h_access->asAccessAction(1, 'u', $this->_user->id, $fnum) && $table[$i]->db_table_name != "#__emundus_training") {

                $query = 'SELECT count(id) FROM `' . $table[$i]->db_table_name . '` WHERE fnum like ' . $this->_db->Quote($fnum);
                try {

                    $this->_db->setQuery($query);
                    $cpt = $this->_db->loadResult();

                } catch (Exception $e) {
                    return $e->getMessage();
                }


                $allowEmbed = $this->allowEmbed(JURI::base() . 'index.php?lang=en');
                if ($cpt > 0) {

                    if ($allowEmbed) {
                        $form .= '<button type="button" id="' . $table[$i]->form_id . '" class="btn btn btn-info btn-sm em-actions-form marginRightbutton" url="index.php?option=com_fabrik&view=form&formid=' . $table[$i]->form_id . '&usekey=fnum&rowid=' . $fnum . '&tmpl=component" title="' . JText::_('COM_EMUNDUS_ACTIONS_EDIT') . '"><i> ' . JText::_('COM_EMUNDUS_ACTIONS_EDIT') . '</i></button>';
                    } else {
                        $form .= ' <a id="' . $table[$i]->form_id . '" class="btn btn btn-info btn-sm marginRightbutton" href="index.php?option=com_fabrik&view=form&formid=' . $table[$i]->form_id . '&usekey=fnum&rowid=' . $fnum . '" title="' . JText::_('COM_EMUNDUS_ACTIONS_EDIT') . '" target="_blank"><i> ' . JText::_('COM_EMUNDUS_ACTIONS_EDIT') . '</i></a>';
                    }

                } else {
                    if ($allowEmbed) {
                        $form .= '<button type="button" id="' . $table[$i]->form_id . '" class="btn btn-default btn-sm em-actions-form marginRightbutton" url="index.php?option=com_fabrik&view=form&formid=' . $table[$i]->form_id . '&' . $table[$i]->db_table_name . '___fnum=' . $fnum . '&' . $table[$i]->db_table_name . '___user_raw=' . $aid . '&' . $table[$i]->db_table_name . '___user=' . $aid . '&sid=' . $aid . '&tmpl=component" title="' . JText::_('COM_EMUNDUS_ADD') . '"><i> ' . JText::_('COM_EMUNDUS_ADD') . '</i></button>';
                    } else {
                        $form .= ' <a type="button" id="' . $table[$i]->form_id . '" class="btn btn-default btn-sm marginRightbutton" href="index.php?option=com_fabrik&view=form&formid=' . $table[$i]->form_id . '&' . $table[$i]->db_table_name . '___fnum=' . $fnum . '&' . $table[$i]->db_table_name . '___user_raw=' . $aid . '&' . $table[$i]->db_table_name . '___user=' . $aid . '&sid=' . $aid . '" title="' . JText::_('COM_EMUNDUS_ADD') . '" target="_blank"><i> ' . JText::_('COM_EMUNDUS_ADD') . '</i></a>';
                    }
                }

            }
            $form .= '</div>';

            // liste des groupes pour le formulaire d'une table
            $query = 'SELECT ff.id, ff.group_id, fg.id, fg.label, fg.params
                      FROM #__fabrik_formgroup ff, #__fabrik_groups fg
                      WHERE ff.group_id = fg.id AND fg.published = 1 AND ff.form_id = ' . $table[$i]->form_id . '
                      ORDER BY ff.ordering';
            try {

                $this->_db->setQuery($query);
                $groupes = $this->_db->loadObjectList();

            } catch (Exception $e) {
                return $e->getMessage();
            }

            /*-- Liste des groupes -- */
            foreach ($groupes as $itemg) {

                $g_params = json_decode($itemg->params);

                if (!EmundusHelperAccess::isAllowedAccessLevel($this->_user->id, (int)$g_params->access)) {
                    continue;
                }

                // liste des items par groupe
                $query = 'SELECT fe.id, fe.name, fe.label, fe.plugin, fe.params
                            FROM #__fabrik_elements fe
                            WHERE fe.published=1 AND fe.hidden=0 AND fe.group_id = "' . $itemg->group_id . '"
                            ORDER BY fe.ordering';

                try {
                    $this->_db->setQuery($query);
                    $elements = $this->_db->loadObjectList();
                } catch (Exception $e) {
                    return $e->getMessage();
                }

                if (count($elements) > 0) {
                    $form .= '<fieldset><legend class="legend">';
                    $form .= JText::_($itemg->label);
                    $form .= '</legend>';

                    if ($itemg->group_id == 14) {

                        foreach ($elements as &$element) {
                            if (!empty($element->label) && $element->label != ' ') {

                                if ($element->plugin == 'date' && $element->content > 0) {
                                    if (!empty($element->content) && $element->content != '0000-00-00 00:00:00') {
                                        $date_params = json_decode($element->params);
                                        $elt = date($date_params->date_form_format, strtotime($element->content));
                                    } else {
                                        $elt = '';
                                    }

                                } elseif (($element->plugin == 'birthday' || $element->plugin == 'birthday_remove_slashes') && $element->content > 0) {
                                    preg_match('/([0-9]{4})-([0-9]{1,})-([0-9]{1,})/', $element->content, $matches);
                                    if (count($matches) == 0) {
                                        $elt = $element->content;
                                    } else {
                                        $format = json_decode($element->params)->list_date_format;

                                        $d = DateTime::createFromFormat($format, $element->content);
                                        if ($d && $d->format($format) == $element->content) {
                                            $elt = JHtml::_('date', $element->content, JText::_('DATE_FORMAT_LC'));
                                        } else {
                                            $elt = JHtml::_('date', $element->content, $format);
                                        }
                                    }

                                } elseif ($element->plugin == 'databasejoin') {
                                    $params = json_decode($element->params);
                                    $select = !empty($params->join_val_column_concat) ? "CONCAT(" . $params->join_val_column_concat . ")" : $params->join_val_column;

                                    if ($params->database_join_display_type == 'checkbox') {
                                        $db = $this->getDbo();
                                        $query = $db->getQuery(true);

                                        $parent_id = strlen($element->content_id) > 0 ? $element->content_id : 0;
                                        $select = $this->getSelectFromDBJoinElementParams($params);

                                        $query->select($select)
                                            ->from($db->quoteName($params->join_db_name . '_repeat_' . $element->name, 't'))
                                            ->leftJoin($db->quoteName($params->join_db_name, 'jd') . ' ON ' . $db->quoteName('jd.' . $params->join_key_column) . ' = ' . $db->quoteName('t.' . $element->name))
                                            ->where($db->quoteName('parent_id') . ' = ' . $db->quote($parent_id));

                                        try {
                                            $this->_db->setQuery($query);
                                            $res = $this->_db->loadColumn();
                                            $elt = implode(', ', $res);
                                        } catch (Exception $e) {
                                            JLog::add('line ' . __LINE__ . ' - Error in model/application at query: ' . $query, JLog::ERROR, 'com_emundus');
                                            throw $e;
                                        }
                                    } else {
                                        $from = $params->join_db_name;
                                        $where = $params->join_key_column . '=' . $this->_db->Quote($element->content);
                                        $query = "SELECT " . $select . " FROM " . $from . " WHERE " . $where;
                                        $query = preg_replace('#{thistable}#', $from, $query);
                                        $query = preg_replace('#{shortlang}#', $this->locales, $query);
                                        $query = preg_replace('#{my->id}#', $aid, $query);

                                        try {

                                            $this->_db->setQuery($query);

                                            $elt = $this->_db->loadResult();

                                        } catch (Exception $e) {
                                            return $e->getMessage();
                                        }
                                    }

                                } elseif ($element->plugin == 'checkbox') {

                                    $elt = implode(", ", json_decode(@$element->content));

                                } else {
                                    $elt = $element->content;
                                }

                                $form .= '<b>' . JText::_($element->label) . ': </b>' . JText::_($elt) . '<br/>';
                            }
                        }

                        // TABLEAU DE PLUSIEURS LIGNES
                    } elseif ((int)$g_params->repeated === 1 || (int)$g_params->repeat_group_button === 1) {

                        $form .= '<table class="table table-bordered table-striped">
                            <thead>
                            <tr> ';

                        // Entrée du tableau
                        $t_elt = array();

                        foreach ($elements as &$element) {
                            $t_elt[] = $element->name;
                            $form .= '<th scope="col">' . JText::_($element->label) . '</th>';
                        }
                        unset($element);

                        $query = 'SELECT table_join FROM #__fabrik_joins WHERE group_id=' . $itemg->group_id . ' AND table_join_key like "parent_id"';

                        try {

                            $this->_db->setQuery($query);
                            $r_table = $this->_db->loadResult();

                        } catch (Exception $e) {
                            return $e->getMessage();
                        }

                        $query = 'SELECT `' . implode("`,`", $t_elt) . '`, id FROM ' . $r_table . ' WHERE parent_id=(SELECT id FROM ' . $table[$i]->db_table_name . ' WHERE fnum like ' . $this->_db->Quote($fnum) . ')';


                        try {

                            $this->_db->setQuery($query);
                            $repeated_elements = $this->_db->loadObjectList();

                        } catch (Exception $e) {
                            return $e->getMessage();
                        }

                        unset($t_elt);
                        $form .= '</tr></thead>';

                        // Ligne du tableau
                        if (count($repeated_elements) > 0) {
                            $form .= '<tbody>';

                            foreach ($repeated_elements as $r_element) {
                                $form .= '<tr>';
                                $j = 0;

                                foreach ($r_element as $key => $r_elt) {
                                    if ($key != 'id' && $key != 'parent_id' && isset($elements[$j])) {

                                        if ($elements[$j]->plugin == 'date') {
                                            if (!empty($r_elt) && $r_elt != '0000-00-00 00:00:00') {
                                                $date_params = json_decode($elements[$j]->params);
                                                $elt = date($date_params->date_form_format, strtotime($r_elt));
                                            } else {
                                                $elt = '';
                                            }

                                        } elseif (($elements[$j]->plugin == 'birthday' || $elements[$j]->plugin == 'birthday_remove_slashes') && $r_elt > 0) {
                                            preg_match('/([0-9]{4})-([0-9]{1,})-([0-9]{1,})/', $r_elt, $matches);
                                            if (count($matches) == 0) {
                                                $elt = $r_elt;
                                            } else {
                                                $format = json_decode($elements[$j]->params)->list_date_format;
                                                $d = DateTime::createFromFormat($format, $r_elt);
                                                if ($d && $d->format($format) == $r_elt) {
                                                    $elt = JHtml::_('date', $r_elt, JText::_('DATE_FORMAT_LC'));
                                                } else {
                                                    $elt = JHtml::_('date', $r_elt, $format);
                                                }
                                            }

                                        } elseif ($elements[$j]->plugin == 'databasejoin') {

                                            $params = json_decode($elements[$j]->params);
                                            $select = !empty($params->join_val_column_concat) ? "CONCAT(" . $params->join_val_column_concat . ")" : $params->join_val_column;

                                            if ($params->database_join_display_type == 'checkbox') {
                                                $db = $this->getDbo();
                                                $query = $db->getQuery(true);

                                                $parent_id = strlen($elements[$j]->content_id) > 0 ? $elements[$j]->content_id : 0;
                                                $select = $this->getSelectFromDBJoinElementParams($params);

                                                $query->select($select)
                                                    ->from($db->quoteName($params->join_db_name . '_repeat_' . $elements[$j]->name, 't'))
                                                    ->leftJoin($db->quoteName($params->join_db_name, 'jd') . ' ON ' . $db->quoteName('jd.' . $params->join_key_column) . ' = ' . $db->quoteName('t.' . $elements[$j]->name))
                                                    ->where($db->quoteName('parent_id') . ' = ' . $db->quote($parent_id));

                                                try {
                                                    $this->_db->setQuery($query);
                                                    $res = $this->_db->loadColumn();
                                                    $elt = implode(', ', $res);
                                                } catch (Exception $e) {
                                                    JLog::add('line ' . __LINE__ . ' - Error in model/application at query: ' . $query, JLog::ERROR, 'com_emundus');
                                                    throw $e;
                                                }
                                            } else {
                                                $from = $params->join_db_name;
                                                $where = $params->join_key_column . '=' . $this->_db->Quote($r_elt);
                                                $query = "SELECT " . $select . " FROM " . $from . " WHERE " . $where;
                                                $query = preg_replace('#{thistable}#', $from, $query);
                                                $query = preg_replace('#{my->id}#', $aid, $query);
                                                $query = preg_replace('#{shortlang}#', $this->locales, $query);

                                                try {
                                                    $this->_db->setQuery($query);
                                                    $elt = $this->_db->loadResult();
                                                } catch (Exception $e) {
                                                    return $e->getMessage();
                                                }
                                            }

                                        } elseif ($elements[$j]->plugin == 'checkbox') {

                                            $elt = implode(", ", json_decode(@$r_elt));

                                        } elseif ($elements[$j]->plugin == 'dropdown' || $elements[$j]->plugin == 'radiobutton') {

                                            $params = json_decode($elements[$j]->params);
                                            $index = array_search($r_elt, $params->sub_options->sub_values);
                                            if (strlen($index) > 0) {
                                                $elt = JText::_($params->sub_options->sub_labels[$index]);
                                            } else {
                                                $elt = "";
                                            }

                                        } else {
                                            $elt = $r_elt;
                                        }

                                        $form .= '<td><div id="em_training_' . $r_element->id . '" class="course ' . $r_element->id . '"> ' . JText::_($elt) . '</div></td>';
                                    }
                                    $j++;
                                }
                                $form .= '</tr>';
                            }
                            $form .= '</tbody>';
                        }
                        $form .= '</table>';

                        // AFFICHAGE EN LIGNE
                    } else {
                        $form .= '<table class="em-personalDetail-table-inline">';
                        $modulo = 0;
                        foreach ($elements as &$element) {

                            if (!empty($element->label) && $element->label != ' ') {
                                $query = 'SELECT `id`, `' . $element->name . '` FROM `' . $table[$i]->db_table_name . '` WHERE fnum like ' . $this->_db->Quote($fnum);

                                try {
                                    $this->_db->setQuery($query);
                                    $res = $this->_db->loadRow();
                                } catch (Exception $e) {
                                    return $e->getMessage();
                                }

                                $element->content = @$res[1];
                                $element->content_id = @$res[0];

                                // Do not display elements with no value inside them.
                                if ($show_empty_fields == 0 && trim($element->content) == '') {
                                    continue;
                                }
                                if ($element->plugin == 'date' && $element->content > 0) {
                                    if (!empty($element->content) && $element->content != '0000-00-00 00:00:00') {
                                        $date_params = json_decode($element->params);
                                        $elt = date($date_params->date_form_format, strtotime($element->content));
                                    } else {
                                        $elt = '';
                                    }

                                } elseif (($element->plugin == 'birthday' || $element->plugin == 'birthday_remove_slashes') && $element->content > 0) {
                                    preg_match('/([0-9]{4})-([0-9]{1,})-([0-9]{1,})/', $element->content, $matches);
                                    if (count($matches) == 0) {
                                        $elt = $element->content;
                                    } else {
                                        $format = json_decode($element->params)->list_date_format;

                                        $d = DateTime::createFromFormat($format, $element->content);
                                        if ($d && $d->format($format) == $element->content) {
                                            $elt = JHtml::_('date', $element->content, JText::_('DATE_FORMAT_LC'));
                                        } else {
                                            $elt = JHtml::_('date', $element->content, $format);
                                        }
                                    }
                                } elseif ($element->plugin == 'databasejoin') {

                                    $params = json_decode($element->params);
                                    $select = !empty($params->join_val_column_concat) ? "CONCAT(" . $params->join_val_column_concat . ")" : $params->join_val_column;

                                    if ($params->database_join_display_type == 'checkbox') {

                                        $elt = implode(", ", json_decode(@$element->content));

                                    } else {

                                        $from = $params->join_db_name;
                                        $where = $params->join_key_column . '=' . $this->_db->Quote($element->content);
                                        $query = "SELECT " . $select . " FROM " . $from . " WHERE " . $where;
                                        $query = preg_replace('#{thistable}#', $from, $query);
                                        $query = preg_replace('#{my->id}#', $aid, $query);
                                        $query = preg_replace('#{shortlang}#', $this->locales, $query);

                                        try {
                                            $this->_db->setQuery($query);
                                            $elt = $this->_db->loadResult();
                                        } catch (Exception $e) {
                                            return $e->getMessage();
                                        }
                                    }

                                } elseif ($element->plugin == 'cascadingdropdown') {

                                    $params = json_decode($element->params);
                                    $cascadingdropdown_id = $params->cascadingdropdown_id;

                                    $r1 = explode('___', $cascadingdropdown_id);
                                    $cascadingdropdown_label = JText::_($params->cascadingdropdown_label);

                                    $r2 = explode('___', $cascadingdropdown_label);

                                    $select = !empty($params->cascadingdropdown_label_concat) ? "CONCAT(" . $params->cascadingdropdown_label_concat . ")" : $r2[1];
                                    $from = $r2[0];
                                    $where = $r1[1] . '=' . $this->_db->Quote($element->content);
                                    $query = "SELECT " . $select . " FROM " . $from . " WHERE " . $where;
                                    $query = preg_replace('#{thistable}#', $from, $query);
                                    $query = preg_replace('#{my->id}#', $aid, $query);
                                    $query = preg_replace('#{shortlang}#', $this->locales, $query);

                                    try {
                                        $this->_db->setQuery($query);
                                        $elt = $this->_db->loadResult();
                                    } catch (Exception $e) {
                                        return $e->getMessage();
                                    }

                                } elseif ($element->plugin == 'checkbox') {

                                    $elt = implode(", ", json_decode(@$element->content));

                                } elseif ($element->plugin == 'dropdown' || $element->plugin == 'radiobutton') {

                                    $params = json_decode($element->params);
                                    $index = array_search($element->content, $params->sub_options->sub_values);
                                    if (strlen($index) > 0) {
                                        $elt = JText::_($params->sub_options->sub_labels[$index]);
                                    } else {
                                        $elt = "";
                                    }

                                } else {

                                    $elt = $element->content;
                                }

                                // modulo for strips css
                                if ($modulo % 2) {
                                    $form .= '<tr class="table-strip-1"><td style="padding-right:50px;"><b>' . JText::_($element->label) . '</b></td> <td> ' . JText::_($elt) . '</td></tr>';
                                } else {
                                    $form .= '<tr class="table-strip-2"><td style="padding-right:50px;"><b>' . JText::_($element->label) . '</b></td> <td> ' . JText::_($elt) . '</td></tr>';
                                }
                                $modulo++;
                            }
                        }
                    }
                    $form .= '</table>';
                    $form .= '</fieldset>';
                }
            }
        }
        return $form;
    }

    // Get form to display in application page layout view
    public function getForms($aid, $fnum = 0, $pid = 9)
    {
        $h_menu = new EmundusHelperMenu;
        $h_access = new EmundusHelperAccess;
        $tableuser = $h_menu->buildMenuQuery($pid);

        $eMConfig = JComponentHelper::getParams('com_emundus');
        $show_empty_fields = $eMConfig->get('show_empty_fields', 1);

        $forms = '';

        try {

            if (isset($tableuser)) {

                $allowed_groups = EmundusHelperAccess::getUserFabrikGroups($this->_user->id);
                $allowed_attachments = EmundusHelperAccess::getUserAllowedAttachmentIDs($this->_user->id);

                $allowEmbed = $this->allowEmbed(JURI::base() . 'index.php?lang=en');

                foreach ($tableuser as $key => $itemt) {

                    $forms .= '<br><hr><div class="TitlePersonalInfo em-personalInfo em-mb-12">';
                    $title = explode(' - ', JText::_($itemt->label));
                    if (empty($title[1])) {
                        $title= JText::_(trim($itemt->label));
                    } else {
                        $title= JText::_(trim($title[1]));
                    }
                    $forms .= '<h5>' . $title . '</h5>';
                    $form_params = json_decode($itemt->params);

                    if ($h_access->asAccessAction(1, 'u', $this->_user->id, $fnum) && $itemt->db_table_name != '#__emundus_training') {

	                    $query = $this->_db->getQuery(true);
						$query->select('count(id)')
							->from($this->_db->quoteName($itemt->db_table_name))
							->where($this->_db->quoteName('fnum') . ' LIKE ' . $this->_db->quote($fnum));
                        $this->_db->setQuery($query);
                        $cpt = $this->_db->loadResult();

                        if ($cpt > 0) {
                            if ($allowEmbed) {
                                $forms .= ' <button type="button" id="' . $itemt->form_id . '" class="btn btn btn-info btn-sm em-actions-form" url="index.php?option=com_fabrik&view=form&formid=' . $itemt->form_id . '&usekey=fnum&rowid=' . $fnum . '&tmpl=component" title="' . JText::_('COM_EMUNDUS_ACTIONS_EDIT') . '" target="_blank"><i> ' . JText::_('COM_EMUNDUS_ACTIONS_EDIT') . '</i></button>';
                            } else {
                                $forms .= ' <a id="' . $itemt->form_id . '" class="em-link" href="index.php?option=com_fabrik&view=form&formid=' . $itemt->form_id . '&usekey=fnum&rowid=' . $fnum . '" title="' . JText::_('COM_EMUNDUS_ACTIONS_EDIT') . '" target="_blank"><span> ' . JText::_('COM_EMUNDUS_ACTIONS_EDIT') . '</span></a>';
                            }
                        } else {
                            if ($allowEmbed) {
                                $forms .= ' <button type="button" id="' . $itemt->form_id . '" class="btn btn-default btn-sm em-actions-form" url="index.php?option=com_fabrik&view=form&formid=' . $itemt->form_id . '&' . $itemt->db_table_name . '___fnum=' . $fnum . '&' . $itemt->db_table_name . '___user_raw=' . $aid . '&' . $itemt->db_table_name . '___user=' . $aid . '&sid=' . $aid . '&tmpl=component" title="' . JText::_('COM_EMUNDUS_ADD') . '"><i> ' . JText::_('COM_EMUNDUS_ADD') . '</i></button>';
                            } else {
                                $forms .= ' <a type="button" id="' . $itemt->form_id . '" class="em-link" href="index.php?option=com_fabrik&view=form&formid=' . $itemt->form_id . '&' . $itemt->db_table_name . '___fnum=' . $fnum . '&' . $itemt->db_table_name . '___user_raw=' . $aid . '&' . $itemt->db_table_name . '___user=' . $aid . '&sid=' . $aid . '" title="' . JText::_('COM_EMUNDUS_ADD') . '" target="_blank"><span> ' . JText::_('COM_EMUNDUS_ADD') . '</span></a>';
                            }
                        }
                    }
                    $forms .= '</div>';

                    // liste des groupes pour le formulaire d'une table
	                $query = $this->_db->getQuery(true);
	                $query->select('ff.id,ff.group_id,fg.id,fg.label,fg.params,fg.is_join')
		                ->from($this->_db->quoteName('#__fabrik_formgroup','ff'))
		                ->leftJoin($this->_db->quoteName('#__fabrik_groups','fg').' ON '.$this->_db->quoteName('fg.id').' = '.$this->_db->quoteName('ff.group_id'))
		                ->where($this->_db->quoteName('fg.published') . ' = 1')
		                ->where($this->_db->quoteName('ff.form_id') . ' = ' . $this->_db->quote($itemt->form_id))
		                ->order($this->_db->quoteName('ff.ordering'));
                    $this->_db->setQuery($query);
                    $groupes = $this->_db->loadObjectList();

                    /*-- Liste des groupes -- */
                    $hidden_group_param_values = [0, '-1', '-2'];
                    foreach ($groupes as $itemg) {
                        $g_params = json_decode($itemg->params);

	                    $query = $this->_db->getQuery(true);
	                    $query->select('fe.id,fe.name,fe.label,fe.plugin,fe.params,fe.default,fe.eval')
		                    ->from($this->_db->quoteName('#__fabrik_elements','fe'))
		                    ->where($this->_db->quoteName('fe.published') . ' = 1')
		                    ->where($this->_db->quoteName('fe.hidden') . ' = 0')
		                    ->where($this->_db->quoteName('fe.group_id') . ' = ' . $this->_db->quote($itemg->group_id))
		                    ->order($this->_db->quoteName('fe.ordering'));

	                    try {
		                    $this->_db->setQuery($query);
		                    $elements = $this->_db->loadObjectList();
	                    } catch (Exception $e) {
		                    JLog::add('Error in model/application at query: ' . $query, JLog::ERROR, 'com_emundus');
		                    throw $e;
	                    }

	                    if (count($elements) > 0) {
                            if ((($allowed_groups !== true && !in_array($itemg->group_id, $allowed_groups)) || !EmundusHelperAccess::isAllowedAccessLevel($this->_user->id, (int)$g_params->access)) && !in_array($g_params->repeat_group_show_first, $hidden_group_param_values)) {
	                            $forms .= '<fieldset class="em-personalDetail">
												<h6 class="em-font-weight-400">' . JText::_($itemg->label) . '</h6>
												<table class="em-restricted-group mt-4 mb-4">
													<thead><tr><td>' . JText::_('COM_EMUNDUS_CANNOT_SEE_GROUP') . '</td></tr></thead>
												</table>
											</fieldset>';
	                            continue;
	                        }

                            if ((int)$g_params->repeated === 1 || (int)$g_params->repeat_group_button === 1 || (int)$itemg->is_join === 1) {

								$query->clear()
									->select('table_join')
									->from($this->_db->quoteName('#__fabrik_joins'))
									->where($this->_db->quoteName('list_id') . ' = ' . $this->_db->quote($itemt->table_id))
									->where($this->_db->quoteName('group_id') . ' = ' . $this->_db->quote($itemg->group_id))
									->where($this->_db->quoteName('table_join_key') . ' = ' . $this->_db->quote('parent_id'));
                                try {
                                    $this->_db->setQuery($query);
                                    $table = $this->_db->loadResult();
                                } catch (Exception $e) {
                                    JLog::add('Error in model/application at query: ' . $query, JLog::ERROR, 'com_emundus');
                                    throw $e;
                                }

                                $check_repeat_groups = $this->checkEmptyRepeatGroups($elements, $table, $itemt->db_table_name, $fnum);

                                if ($check_repeat_groups) {
                                    // -- Entrée du tableau --
                                    $t_elt = array();
                                    foreach ($elements as &$element) {
                                        $t_elt[] = $element->name;
                                    }
                                    unset($element);

                                    $forms .= '<fieldset class="em-personalDetail">';
                                    $forms .= (!empty($itemg->label)) ? '<h6 class="em-font-weight-400">' . JText::_($itemg->label) . '</h6>' : '';

                                    $forms .= '<table class="em-mt-8 em-mb-16 table table-bordered table-striped em-personalDetail-table-multiplleLine"><thead><tr> ';

                                    foreach ($elements as &$element) {
                                        if ($element->plugin != 'id') {
                                            $forms .= '<th scope="col">' . JText::_($element->label) . '</th>';
                                        }
                                    }

                                    if ($itemg->group_id == 174) {
                                        $query = 'SELECT `' . implode("`,`", $t_elt) . '`, id FROM ' . $table . '
	                                        WHERE parent_id=(SELECT id FROM ' . $itemt->db_table_name . ' WHERE fnum like ' . $this->_db->Quote($fnum) . ') OR applicant_id=' . $aid;
                                    } else {
                                        $query = 'SELECT `' . implode("`,`", $t_elt) . '`, id FROM ' . $table . '
	                                    WHERE parent_id=(SELECT id FROM ' . $itemt->db_table_name . ' WHERE fnum like ' . $this->_db->Quote($fnum) . ')';
                                    }

                                    try {
                                        $this->_db->setQuery($query);
                                        $repeated_elements = $this->_db->loadObjectList();
                                    } catch (Exception $e) {
                                        JLog::Add('ERROR getting repeated elements in model/application at query: '.$query, JLog::ERROR, 'com_emundus');
                                        $repeated_elements = [];
                                    }

                                    unset($t_elt);

                                    $forms .= '</tr></thead>';
                                    // -- Ligne du tableau --
                                    if (count($repeated_elements) > 0) {
                                        $forms .= '<tbody>';
                                        foreach ($repeated_elements as $r_element) {
                                            $forms .= '<tr>';
                                            $j = 0;
                                            foreach ($r_element as $key => $r_elt) {

                                                if (!empty($elements[$j])) {
                                                    $params = json_decode($elements[$j]->params);
                                                }

                                                // Do not display elements with no value inside them.
                                                if (($show_empty_fields == 0 && trim($r_elt) == '') || empty($params->store_in_db)) {
                                                    $forms .= '<td></td>';
                                                    $j++;
                                                    continue;
                                                }

                                                if ($key != 'id' && $key != 'parent_id' && isset($elements[$j])) {

                                                    if ($elements[$j]->plugin == 'date') {
                                                        if (!empty($r_elt) && ($r_elt != '0000-00-00 00:00:00' || $r_elt != '0000-00-00')) {
                                                            $elt = date($params->date_form_format, strtotime($r_elt));
                                                        } else {
                                                            $elt = '';
                                                        }
                                                    }
													elseif (($elements[$j]->plugin == 'birthday' || $elements[$j]->plugin == 'birthday_remove_slashes') && $r_elt > 0)
													{
                                                        preg_match('/([0-9]{4})-([0-9]{1,})-([0-9]{1,})/', $r_elt, $matches);
                                                        if (count($matches) == 0) {
                                                            $elt = $r_elt;
                                                        } else {
                                                            $format = $params->list_date_format;

                                                            $d = DateTime::createFromFormat($format, $r_elt);
                                                            if ($d && $d->format($format) == $r_elt) {
                                                                $elt = JHtml::_('date', $r_elt, JText::_('DATE_FORMAT_LC'));
                                                            } else {
                                                                $elt = JHtml::_('date', $r_elt, $format);
                                                            }
                                                        }
                                                    }
													elseif ($elements[$j]->plugin == 'databasejoin')
													{
                                                        $select = !empty($params->join_val_column_concat)?"CONCAT(".$params->join_val_column_concat.")":$params->join_val_column;

                                                        if ($params->database_join_display_type == 'checkbox' || $params->database_join_display_type == 'multilist') {
                                                            $query = $this->_db->getQuery(true);

                                                            $select = $this->getSelectFromDBJoinElementParams($params, 't');
                                                            $query->select($select)
                                                                ->from($this->_db->quoteName($params->join_db_name, 't'))
                                                                ->leftJoin($this->_db->quoteName($itemt->db_table_name . '_' . $itemg->id . '_repeat_repeat_' . $elements[$j]->name, 'checkbox_repeat') . ' ON ' . $this->_db->quoteName('checkbox_repeat.' . $elements[$j]->name) . ' = ' . $this->_db->quoteName('t.id'))
                                                                ->leftJoin($this->_db->quoteName($itemt->db_table_name . '_' . $itemg->id . '_repeat', 'repeat_grp') . ' ON ' . $this->_db->quoteName('repeat_grp.id') . ' = ' . $this->_db->quoteName('checkbox_repeat.parent_id'))
                                                                ->where($this->_db->quoteName('checkbox_repeat.parent_id') . ' = ' . $r_element->id);

                                                            try {
                                                                $this->_db->setQuery($query);
                                                                $value = $this->_db->loadColumn();
                                                                $elt = '<ul>';
                                                                foreach ($value as $val){
                                                                    $elt .= '<li>'.JText::_($val).'</li>';
                                                                }
                                                                $elt .= "</ul>";
                                                            } catch (Exception $e) {
                                                                JLog::add('line ' . __LINE__ . ' - Error in model/application at query: ' . $query, JLog::ERROR, 'com_emundus');
                                                                throw $e;
                                                            }
                                                        } else {
                                                            $from = $params->join_db_name;
                                                            $where = $params->join_key_column . '=' . $this->_db->Quote($r_elt);
                                                            $query = "SELECT " . $select . " FROM " . $from . " WHERE " . $where;

                                                            $query = preg_replace('#{thistable}#', $from, $query);
                                                            $query = preg_replace('#{my->id}#', $aid, $query);
                                                            $query = preg_replace('#{shortlang}#', $this->locales, $query);

                                                            $this->_db->setQuery($query);
                                                            $ret = $this->_db->loadResult();
                                                            if (empty($ret)) {
                                                                $ret = $r_elt;
                                                            }
                                                            $elt = JText::_($ret);
                                                        }
                                                    }
													elseif ($elements[$j]->plugin == 'cascadingdropdown')
													{
                                                        $cascadingdropdown_id = $params->cascadingdropdown_id;
                                                        $r1 = explode('___', $cascadingdropdown_id);
                                                        $cascadingdropdown_label = $params->cascadingdropdown_label;
                                                        $r2 = explode('___', $cascadingdropdown_label);
                                                        $select = !empty($params->cascadingdropdown_label_concat) ? "CONCAT(" . $params->cascadingdropdown_label_concat . ")" : $r2[1];
                                                        $from = $r2[0];

                                                        // Checkboxes behave like repeat groups and therefore need to be handled a second level of depth.
                                                        if ($params->cdd_display_type == 'checkbox') {
                                                            $select = !empty($params->cascadingdropdown_label_concat) ? " CONCAT(" . $params->cascadingdropdown_label_concat . ")" : 'GROUP_CONCAT(' . $r2[1] . ')';

                                                            // Load the Fabrik join for the element to it's respective repeat_repeat table.
                                                            $query = $this->_db->getQuery(true);
                                                            $query
                                                                ->select([$this->_db->quoteName('join_from_table'), $this->_db->quoteName('table_key'), $this->_db->quoteName('table_join'), $this->_db->quoteName('table_join_key')])
                                                                ->from($this->_db->quoteName('#__fabrik_joins'))
                                                                ->where($this->_db->quoteName('element_id') . ' = ' . $elements[$j]->id);
                                                            $this->_db->setQuery($query);
                                                            $f_join = $this->_db->loadObject();

                                                            $where = $r1[1] . ' IN (
	                                                    SELECT ' . $this->_db->quoteName($f_join->table_join . '.' . $f_join->table_key) . '
	                                                    FROM ' . $this->_db->quoteName($f_join->table_join) . '
	                                                    WHERE ' . $this->_db->quoteName($f_join->table_join . '.' . $f_join->table_join_key) . ' = ' . $r_element->id . ')';
                                                        } else {
                                                            $where = $r1[1] . '=' . $this->_db->quote($r_elt);
                                                        }
                                                        $query = "SELECT " . $select . " FROM " . $from . " WHERE " . $where;
                                                        $query = preg_replace('#{thistable}#', $from, $query);
                                                        $query = preg_replace('#{my->id}#', $aid, $query);
                                                        $query = preg_replace('#{shortlang}#', $this->locales, $query);

                                                        $this->_db->setQuery($query);
                                                        $ret = $this->_db->loadResult();
                                                        if (empty($ret)) {
                                                            $ret = $r_elt;
                                                        }
                                                        $elt = JText::_($ret);
                                                    }
													elseif ($elements[$j]->plugin == 'checkbox')
													{
                                                        $elm = array();
                                                        if(!empty(array_filter($params->sub_options->sub_values))){
                                                            if(!empty($r_elt)) {
                                                                $index = array_intersect(json_decode(@$r_elt), $params->sub_options->sub_values);
                                                            }
                                                        }
                                                        else{
                                                            $index = json_decode(@$r_elt);
                                                        }

                                                        foreach($index as $value) {
                                                            if(!empty(array_filter($params->sub_options->sub_values))){
                                                                $key = array_search($value,$params->sub_options->sub_values);
                                                                $elm[] = JText::_($params->sub_options->sub_labels[$key]);
                                                            }
                                                            else{
                                                                $elm[] = $value;
                                                            }
                                                        }
                                                        $elt = '<ul>';
                                                        foreach ($elm as $val){
                                                            $elt .= '<li>'.JText::_($val).'</li>';
                                                        }
                                                        $elt .= "</ul>";
                                                    }
													elseif ($elements[$j]->plugin == 'dropdown' || $elements[$j]->plugin == 'radiobutton')
													{
                                                        $index = array_search($r_elt, $params->sub_options->sub_values);
                                                        if (strlen($index) > 0) {
                                                            $elt = JText::_($params->sub_options->sub_labels[$index]);
                                                        } elseif (!empty($params->dropdown_populate)) {
                                                            try {
                                                                $options = eval($params->dropdown_populate);
                                                                $elt = $r_elt;
                                                                foreach ($options as $option) {
                                                                    if ($option->value == $r_elt) {
                                                                        $elt = $option->text;
                                                                        break;
                                                                    }
                                                                }
                                                            } catch (Exception $e) {
                                                                $elt = $r_elt;
                                                            }
                                                        } else {
                                                            $elt = "";
                                                        }
                                                    }
													elseif ($elements[$j]->plugin == 'internalid')
                                                    {
                                                        $elt = '';
                                                    }
													elseif ($elements[$j]->plugin == 'field')
													{
                                                        if ($params->password == 1) {
                                                            $elt = '******';
                                                        } elseif ($params->password == 3) {
                                                            $elt = '<a href="mailto:' . $r_elt . '">' . $r_elt . '</a>';
                                                        } elseif ($params->password == 5) {
                                                            $elt = '<a href="' . $r_elt . '" target="_blank">' . $r_elt . '</a>';
                                                        } else {
                                                            $elt = JText::_($r_elt);
                                                        }
                                                    }
													elseif ($elements[$j]->plugin == 'yesno')
													{
                                                        $elt = ($r_elt == 1) ? JText::_("JYES") : JText::_("JNO");
                                                    }
													elseif ($elements[$j]->plugin == 'display')
													{
                                                        $elements[$j]->content = empty($elements[$j]->eval) ? $elements[$j]->default : $r_elt;
                                                        $elt = JText::_($elements[$j]->content);
                                                    } elseif ($elements[$j]->plugin == 'calc') {
	                                                    $elt = JText::_($r_elt);

	                                                    $stripped = strip_tags($elt);
	                                                    if ($stripped != $elt) {
		                                                    $elt = strip_tags($elt, ['p', 'a', 'div', 'ul', 'li', 'br']);
	                                                    }
                                                    } elseif ($elements[$j]->plugin == 'emundus_phonenumber') {
                                                        $elt = substr($r_elt, 2, strlen($r_elt));
                                                    }
													elseif ($elements[$j]->plugin == 'iban') {
														$elt = $r_elt;

														if($params->encrypt_datas == 1) {
															$elt = EmundusHelperFabrik::decryptDatas($r_elt);
														}

														$elt = chunk_split($elt, 4, ' ');
                                                    }
													else {
                                                        $elt = $r_elt;
                                                    }

                                                    $forms .= '<td><div id="em_training_' . $r_element->id . '" class="course ' . $r_element->id . '"> ' . (($elements[$j]->plugin != 'field') ? JText::_($elt) : $elt) . '</div></td>';
                                                }
                                                $j++;
                                            }
                                            $forms .= '</tr>';
                                        }
                                        $forms .= '</tbody>';
                                    }
                                    $forms .= '</table>';
	                                $forms .= '</fieldset>';
                                }
                                // AFFICHAGE EN LIGNE
                            }
							else
							{
                                $check_not_empty_group = $this->checkEmptyGroups($elements ,$itemt->db_table_name, $fnum);

                                if($check_not_empty_group && !in_array($g_params->repeat_group_show_first, $hidden_group_param_values)) {
                                    $forms .= '<table class="em-mt-8 em-mb-16 em-personalDetail-table-inline"><h6 class="em-font-weight-400">' . JText::_($itemg->label) . '</h6>';

                                    $modulo = 0;
                                    foreach ($elements as &$element) {

                                        if (!empty(trim($element->label))) {
                                            // TODO : If databasejoin checkbox or multilist get value from children table. Add a query to get join table from jos_fabrik_joins where element_id = $element->id
                                            if ($element->plugin == 'databasejoin'){
                                                $params = json_decode($element->params);
                                                $query = 'SELECT `id`, `' . $element->name . '` FROM `' . $itemt->db_table_name . '` WHERE fnum like ' . $this->_db->Quote($fnum);

                                                if($params->database_join_display_type == 'checkbox' || $params->database_join_display_type == 'multilist'){
                                                    $query = 'SELECT t.id, jd.'.$element->name.' FROM `' .$itemt->db_table_name  . '` as t LEFT JOIN `'.$itemt->db_table_name.'_repeat_' . $element->name.'` as jd ON jd.parent_id = t.id WHERE fnum like ' . $this->_db->Quote($fnum);
                                                }

                                            }
                                            else
											{
                                                $query = 'SELECT `id`, `' . $element->name . '` FROM `' . $itemt->db_table_name . '` WHERE fnum like ' . $this->_db->Quote($fnum);
                                            }
                                            $this->_db->setQuery($query);
                                            $res = $this->_db->loadRow();

                                            if (count($res) > 1) {
                                                $element->content = $res[1];
                                                $element->content_id = $res[0];
                                            } else {
                                                $element->content = '';
                                                $element->content_id = -1;
                                            }

                                            if (count($res) > 1) {
                                                if ($element->plugin == 'display') {
                                                    $element->content = empty($element->eval) ? $element->default : $res[1];
                                                } else {
                                                    $element->content = $res[1];
                                                }
                                                $element->content_id = $res[0];
                                            } else {
                                                $element->content = '';
                                                $element->content_id = -1;
                                            }

                                            // Do not display elements with no value inside them.
                                            if ($show_empty_fields == 0 && (trim($element->content) == '' || trim($element->content_id) == -1) && $element->plugin != 'emundus_fileupload') {
                                                continue;
                                            }

                                            // Decrypt datas encoded
                                            if($form_params->note == 'encrypted'){
	                                            $element->content = EmundusHelperFabrik::decryptDatas($element->content,null,'aes-128-cbc',$element->plugin);
                                            }
                                            //

                                            if ($element->plugin == 'date' && !empty($element->content))
											{
                                                if ($element->content != '0000-00-00 00:00:00' || $element->content != '0000-00-00') {
                                                    $date_params = json_decode($element->params);
                                                    $elt = date($date_params->date_form_format, strtotime($element->content));
                                                } else {
                                                    $elt = '';
                                                }
                                            }
											elseif (($element->plugin == 'birthday' || $element->plugin == 'birthday_remove_slashes') && $element->content > 0)
											{
                                                preg_match('/([0-9]{4})-([0-9]{1,})-([0-9]{1,})/', $element->content, $matches);
                                                if (count($matches) == 0) {
                                                    $elt = $element->content;
                                                } else {
                                                    $format = json_decode($element->params)->list_date_format;

                                                    $d = DateTime::createFromFormat($format, $element->content);
                                                    if ($d && $d->format($format) == $element->content) {
                                                        $elt = JHtml::_('date', $element->content, JText::_('DATE_FORMAT_LC'));
                                                    } else {
                                                        $elt = JHtml::_('date', $element->content, $format);
                                                    }
                                                }
                                            }
											elseif ($element->plugin == 'databasejoin')
											{
                                                $params = json_decode($element->params);
                                                $select = !empty($params->join_val_column_concat) ? "CONCAT(" . $params->join_val_column_concat . ")" : $params->join_val_column;

                                                if ($params->database_join_display_type == 'checkbox' || $params->database_join_display_type == 'multilist') {
                                                    $query = $this->_db->getQuery(true);

                                                    $parent_id = strlen($element->content_id) > 0 ? $element->content_id : 0;
                                                    $select = $this->getSelectFromDBJoinElementParams($params);
                                                    $query->select($select)
                                                        ->from($this->_db->quoteName($itemt->db_table_name . '_repeat_' . $element->name, 't'))
                                                        ->leftJoin($this->_db->quoteName($params->join_db_name, 'jd') . ' ON ' . $this->_db->quoteName('jd.' . $params->join_key_column) . ' = ' . $this->_db->quoteName('t.' . $element->name))
                                                        ->where($this->_db->quoteName('parent_id') . ' = ' . $this->_db->quote($parent_id));

                                                    try {
                                                        $this->_db->setQuery($query);
                                                        $value = $this->_db->loadColumn();
                                                        $elt = '<ul>';
                                                        foreach ($value as $val){
                                                            $elt .= '<li>'.JText::_($val).'</li>';
                                                        }
                                                        $elt .= "</ul>";
                                                    } catch (Exception $e) {
                                                        JLog::add('Line 997 - Error in model/application at query: ' . $query, JLog::ERROR, 'com_emundus');
                                                        throw $e;
                                                    }
                                                } else {
                                                    $from = $params->join_db_name;
                                                    $where = $params->join_key_column . '=' . $this->_db->Quote($element->content);
                                                    $query = "SELECT " . $select . " FROM " . $from . " WHERE " . $where;

                                                    $query = preg_replace('#{thistable}#', $from, $query);
                                                    $query = preg_replace('#{my->id}#', $aid, $query);
                                                    $query = preg_replace('#{shortlang}#', $this->locales, $query);

                                                    $this->_db->setQuery($query);
                                                    $ret = $this->_db->loadResult();
                                                    if (empty($ret)) {
                                                        $ret = $element->content;
                                                    }
                                                    $elt = JText::_($ret);
                                                }
                                            }
											elseif ($element->plugin == 'cascadingdropdown')
											{
                                                $params = json_decode($element->params);
                                                $cascadingdropdown_id = $params->cascadingdropdown_id;
                                                $r1 = explode('___', $cascadingdropdown_id);
                                                $cascadingdropdown_label = JText::_($params->cascadingdropdown_label);
                                                $r2 = explode('___', $cascadingdropdown_label);
                                                $select = !empty($params->cascadingdropdown_label_concat) ? "CONCAT(" . $params->cascadingdropdown_label_concat . ")" : $r2[1];
                                                $from = $r2[0];
                                                $where = $r1[1] . '=' . $this->_db->Quote($element->content);
                                                $query = "SELECT " . $select . " FROM " . $from . " WHERE " . $where;
                                                $query = preg_replace('#{thistable}#', $from, $query);
                                                $query = preg_replace('#{my->id}#', $aid, $query);
                                                $query = preg_replace('#{shortlang}#', $this->locales, $query);

                                                $this->_db->setQuery($query);
                                                $ret = $this->_db->loadResult();
                                                if (empty($ret)) {
                                                    $ret = $element->content;
                                                }
                                                $elt = JText::_($ret);
                                            }
											elseif ($element->plugin == 'checkbox' && !empty($element->content))
											{
                                                $params = json_decode($element->params);
                                                $elm = [];
                                                $index = array_intersect(json_decode(@$element->content), $params->sub_options->sub_values);
                                                foreach ($index as $value) {
                                                    $key = array_search($value,$params->sub_options->sub_values);
                                                    $elm[] = JText::_($params->sub_options->sub_labels[$key]);
                                                }

                                                $elt = '<ul>';
                                                foreach ($elm as $val){
                                                    $elt .= '<li>'.JText::_($val).'</li>';
                                                }
                                                $elt .= "</ul>";
                                            }
											elseif (($element->plugin == 'dropdown' || $element->plugin == 'radiobutton') && !empty($element->content))
											{
                                                $params = json_decode($element->params);
                                                $index = array_search($element->content, $params->sub_options->sub_values);

                                                if (strlen($index) > 0) {
                                                    $elt = JText::_($params->sub_options->sub_labels[$index]);
                                                } elseif (!empty($params->dropdown_populate)) {
                                                    try {
                                                        $options = eval($params->dropdown_populate);
                                                        $elt = $element->content;
                                                        foreach ($options as $option) {
                                                            if ($option->value == $element->content) {
                                                                $elt = $option->text;
                                                                break;
                                                            }
                                                        }
                                                    } catch (Exception $e) {
                                                        $elt = $element->content;
                                                    }
                                                } elseif ($params->multiple == 1) {
                                                    $elt = $elt = "<ul><li>" . implode("</li><li>", json_decode(@$element->content)) . "</li></ul>";
                                                } else {
                                                    $elt = "";
                                                }
                                            }
											elseif ($element->plugin == 'internalid')
											{
                                                $elt = '';
                                            }
											elseif ($element->plugin == 'yesno')
											{
                                                $elt = '';
                                                if($element->content === '1'){
                                                    $elt = JText::_('JYES');
                                                } elseif ($element->content === '0') {
                                                    $elt = JText::_('JNO');
                                                }
                                            }
											elseif ($element->plugin == 'field')
											{
                                                $params = json_decode($element->params);

                                                if ($params->password == 1) {
                                                    $elt = '******';
                                                } elseif ($params->password == 3) {
                                                    $elt = '<a href="mailto:' . $element->content . '" title="' . JText::_($element->label) . '">' . $element->content . '</a>';
                                                } elseif ($params->password == 5) {
                                                    $elt = '<a href="' . $element->content . '" target="_blank" title="' . JText::_($element->label) . '">' . $element->content . '</a>';
                                                } else {
                                                    $elt = $element->content;
                                                }
                                            }
											elseif ($element->plugin == 'emundus_fileupload')
											{
                                                $params = json_decode($element->params);
                                                $query = $this->_db->getQuery(true);

                                                try {
                                                    $query->select('esa.id,esa.value as attachment_name,eu.filename')
                                                        ->from($this->_db->quoteName('#__emundus_uploads','eu'))
                                                        ->leftJoin($this->_db->quoteName('#__emundus_setup_attachments','esa').' ON '.$this->_db->quoteName('esa.id').' = '.$this->_db->quoteName('eu.attachment_id'))
                                                        ->where($this->_db->quoteName('eu.fnum') . ' LIKE ' . $this->_db->quote($fnum))
                                                        ->andWhere($this->_db->quoteName('eu.attachment_id') . ' = ' . $this->_db->quote($params->attachmentId));
                                                    $this->_db->setQuery($query);
                                                    $attachment_upload = $this->_db->loadObject();

                                                    if(!empty($attachment_upload->filename) && (($allowed_attachments !== true && in_array($params->attachmentId,$allowed_attachments)) || $allowed_attachments === true)) {
                                                        $path = DS . 'images' . DS . 'emundus' . DS . 'files' . DS . $aid . DS . $attachment_upload->filename;
                                                        $elt = '<a href="'.$path.'" target="_blank" style="text-decoration: underline;">' . $attachment_upload->attachment_name . '</a>';
                                                    } else {
                                                        $elt = '';
                                                    }
                                                } catch (Exception $e) {
                                                    JLog::add('component/com_emundus/models/application | Error at getting emundus_fileupload for applicant ' . $fnum . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
                                                    $elt = '';
                                                }
                                            }
											elseif ($element->plugin == 'emundus_phonenumber') {
                                                $elt = substr($element->content, 2, strlen($element->content));
                                            }
                                            elseif ($element->plugin == 'textarea') {
                                                $params = json_decode($element->params);

                                                if (!empty($params) && $params->use_wysiwyg == 1) {
                                                    $elt = $element->content;
                                                } else {
                                                    $elt = nl2br($element->content);
                                                }
                                            }
											elseif ($element->plugin == 'iban') {
												$elt = $element->content;
												$params = json_decode($element->params);

	                                            if($params->encrypt_datas == 1) {
		                                            $elt = EmundusHelperFabrik::decryptDatas($element->content);
	                                            }

												$elt = chunk_split($elt, 4, ' ');
											}
											else
											{
                                                $elt = $element->content;
                                            }

                                            // modulo for strips css //
                                            if ($modulo % 2) {
                                                $forms .= '<tr class="table-strip-1">' . (!empty(JText::_($element->label)) ? '<td style="padding-right:50px;"><b>' . JText::_($element->label) . '</b></td>' : '') . '<td> ' . ((!in_array($element->plugin,['field','textarea'])) ? JText::_($elt) : $elt) . '</td></tr>';
                                            } else {
                                                $forms .= '<tr class="table-strip-2">' . (!empty(JText::_($element->label)) ? '<td style="padding-right:50px;"><b>' . JText::_($element->label) . '</b></td>' : '') . '<td> ' . ((!in_array($element->plugin,['field','textarea'])) ? JText::_($elt) : $elt) . '</td></tr>';
                                            }
                                            $modulo++;
                                            unset($params);
                                        }
                                    }
                                }
                            }
                            $forms .= '</table>';
                        }
                    }
                }
            }
        } catch (Exception $e) {
            error_log($e->getMessage(), 0);
            return $e->getMessage();
        }
        return $forms;
    }


    // @description  generate HTML to send to PDF librairie
    // @param   int applicant user id
    // @param   int fnum application file number
    // @return  string HTML to send to PDF librairie
    public function getFormsPDF($aid, $fnum = 0, $fids = null, $gids = 0, $profile_id = null, $eids = null, $attachments = true)
    {
        /* COULEURS*/
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $show_empty_fields = $eMConfig->get('show_empty_fields', 1);
        $em_breaker = $eMConfig->get('export_application_pdf_breaker', '0');

        require_once(JPATH_SITE . '/components/com_emundus/helpers/list.php');
        $h_list = new EmundusHelperList();
        $tableuser = $h_list->getFormsList($aid, $fnum, $fids, $profile_id);
        $forms = '';

        if (isset($tableuser)) {

            $allowed_groups = EmundusHelperAccess::getUserFabrikGroups($this->_user->id);

            foreach ($tableuser as $key => $itemt) {
                $form_params = json_decode($itemt->params);
                $breaker = ($em_breaker) ? ($key === 0) ? '' : 'class="page-break"' : '';
                // liste des groupes pour le formulaire d'une table
                $query = 'SELECT ff.id, ff.group_id, fg.id, fg.label, fg.params
                            FROM #__fabrik_formgroup ff, #__fabrik_groups fg
                            WHERE ff.group_id = fg.id AND fg.published = 1';

                if (!empty($gids) && $gids != 0) {
                    $query .= ' AND  fg.id IN (' . implode(',', $gids) . ')';
                }

                $query .= ' AND ff.form_id = "' . $itemt->form_id . '"
                            ORDER BY ff.ordering';
                try {
                    $this->_db->setQuery($query);
                    $groupes = $this->_db->loadObjectList();
                } catch (Exception $e) {
                    JLog::add('Error in model/application at query: ' . $query, JLog::ERROR, 'com_emundus');
                    throw $e;
                }

                if (count($groupes) > 0) {
                    $forms .= '<h2 ' . $breaker . '>';
                    $title = explode('-', JText::_($itemt->label));
                    if (empty($title[1])) {
                        $form_label = preg_replace('/\s+/', ' ', JText::_(trim($itemt->label)));
                        if(!empty($form_label)) {
                            $forms .= '<b><h2 class="pdf-page-title">' . $form_label . '</h2></b>';
                        }
                    } else {
                        $form_label = preg_replace('/\s+/', ' ', JText::_(trim($title[1])));
                        if(!empty($form_label)) {
                            $forms .= '<b><h2 class="pdf-page-title">' . $form_label . '</h2></b>';
                        }
                    }
                }

                $forms .= '</h2>';

				/*-- Liste des groupes -- */
                foreach ($groupes as $itemg) {

                    $g_params = json_decode($itemg->params);

                    if (!EmundusHelperAccess::isAllowedAccessLevel($this->_user->id, (int)$g_params->access)) {
                        continue;
                    }

                    if ($allowed_groups !== true && !in_array($itemg->group_id, $allowed_groups)) {
                        $forms .= '<h3 class="group">' . JText::_($itemg->label) . '</h3>';
                        $forms .= '<table>
										<thead><tr><th>' . JText::_('COM_EMUNDUS_CANNOT_SEE_GROUP') . '</th></tr></thead>
									</table>';
                        continue;
                    }

                    // liste des items par groupe
                    $query = 'SELECT fe.id, fe.name, fe.label, fe.plugin, fe.params, fe.default, fe.eval
                                FROM #__fabrik_elements fe
                                WHERE fe.published=1 AND
                                    fe.hidden=0 AND
                            fe.group_id = "' . $itemg->group_id . '"';
                    if (!empty($eids)) {
                        $query .= ' AND  fe.id IN (' . implode(',', $eids) . ')';
                    }

                    $query .= ' ORDER BY fe.ordering';

                    try {
                        $this->_db->setQuery($query);
                        $elements = $this->_db->loadObjectList();
                    } catch (Exception $e) {
                        JLog::add('Error in model/application at query: ' . $query, JLog::ERROR, 'com_emundus');
                        throw $e;
                    }

                    if (count($elements) > 0) {

                        $asTextArea = false;
                        foreach ($elements as $key => $element) {
                            if ($element->plugin == 'textarea') {
                                $asTextArea = true;
                            }
                        }

                        $group_label =  JText::_($itemg->label);

                        if ($itemg->group_id == 14) {
                            $forms .= '<table>';
                            foreach ($elements as $element) {
                                if (!empty($element->label) && $element->label != ' ' && !empty($element->content)) {
                                    $forms .= '<tbody><tr><td>' . JText::_($element->label) . '</td></tr><tbody>';
                                }
                            }
                            $forms .= '</table>';
                            // TABLEAU DE PLUSIEURS LIGNES avec moins de 7 colonnes
                        }
						elseif (((int)$g_params->repeated === 1 || (int)$g_params->repeat_group_button === 1) && count($elements) < 4 && !$asTextArea)
						{
                            //-- Entrée du tableau -- */
                            $t_elt = array();
                            foreach ($elements as &$element) {
                                $t_elt[] = $element->name;
                            }
                            unset($element);

                            $query = 'SELECT table_join FROM #__fabrik_joins WHERE list_id=' . $itemt->table_id . ' AND group_id=' . $itemg->group_id . ' AND table_join_key like "parent_id"';
                            try {
                                $this->_db->setQuery($query);
                                $table = $this->_db->loadResult();
                            } catch (Exception $e) {
                                JLog::add('Line ' . __LINE__ . ' - Error in model/application at query: ' . $query, JLog::ERROR, 'com_emundus');
                                throw $e;
                            }

                            $check_repeat_groups = $this->checkEmptyRepeatGroups($elements, $table, $itemt->db_table_name, $fnum);

                            if ($check_repeat_groups) {
								$forms .= '<h3 class="group">' . $group_label . '</h3>';
                                $forms .= '<table class="pdf-forms"><thead><tr class="background"> ';
                                foreach ($elements as &$element) {
                                    $forms .= '<th scope="col" class="background">' . JText::_($element->label) . '</th>';
                                }
                                unset($element);

                                if ($itemg->group_id == 174) {
                                    $query = 'SELECT `' . implode("`,`", $t_elt) . '`, id FROM ' . $table . '
                                        WHERE parent_id=(SELECT id FROM ' . $itemt->db_table_name . ' WHERE fnum like ' . $this->_db->Quote($fnum) . ') OR applicant_id=' . $aid;
                                } else {
                                    $query = 'SELECT `' . implode("`,`", $t_elt) . '`, id FROM ' . $table . '
                                    WHERE parent_id=(SELECT id FROM ' . $itemt->db_table_name . ' WHERE fnum like ' . $this->_db->Quote($fnum) . ')';
                                }

                                try {
                                    $this->_db->setQuery($query);
                                    $repeated_elements = $this->_db->loadObjectList();
                                } catch (Exception $e) {
                                    JLog::add('Line ' . __LINE__ . ' - Error in model/application at query: ' . $query, JLog::ERROR, 'com_emundus');
                                    throw $e;
                                }

                                unset($t_elt);

                                $forms .= '</tr></thead><tbody>';

                                // -- Ligne du tableau --
                                if (count($repeated_elements) > 0) {
                                    foreach ($repeated_elements as $r_element) {
                                        $forms .= '<tr>';
                                        $j = 0;

                                        foreach ($r_element as $key => $r_elt) {

                                            if ($key != 'id' && $key != 'parent_id' && isset($elements[$j])) {

                                                $params = json_decode($elements[$j]->params);

                                                if ($elements[$j]->plugin == 'date' && (!empty($r_elt) && $r_elt != '0000-00-00 00:00:00')) {
                                                    $elt = EmundusHelperDate::displayDate($r_elt, $params->date_table_format, (int)$params->date_store_as_local);
                                                } elseif (($elements[$j]->plugin == 'birthday' || $elements[$j]->plugin == 'birthday_remove_slashes') && $r_elt > 0) {
                                                    $elt = EmundusHelperDate::displayDate($r_elt, $params->list_date_format);
                                                } elseif ($elements[$j]->plugin == 'databasejoin') {
                                                    $select = !empty($params->join_val_column_concat) ? "CONCAT(" . $params->join_val_column_concat . ")" : $params->join_val_column;

                                                    if ($params->database_join_display_type == 'checkbox' || $params->database_join_display_type == 'multilist') {
                                                        $db = $this->getDbo();
                                                        $query = $db->getQuery(true);

                                                        $parent_id = strlen($elements[$j]->content_id) > 0 ? $elements[$j]->content_id : 0;
                                                        $select = $this->getSelectFromDBJoinElementParams($params, 't');

                                                        $query->select($select)
                                                            ->from($db->quoteName($params->join_db_name, 't'))
                                                            ->leftJoin($db->quoteName($itemt->db_table_name . '_' . $itemg->id . '_repeat_repeat_' . $elements[$j]->name, 'checkbox_repeat') . ' ON ' . $db->quoteName('checkbox_repeat.' . $elements[$j]->name) . ' = ' . $db->quoteName('t.id'))
                                                            ->leftJoin($db->quoteName($itemt->db_table_name . '_' . $itemg->id . '_repeat', 'repeat_grp') . ' ON ' . $db->quoteName('repeat_grp.id') . ' = ' . $db->quoteName('checkbox_repeat.parent_id'))
                                                            ->where($db->quoteName('checkbox_repeat.parent_id') . ' = ' . $r_element->id);

                                                        try {
                                                            $this->_db->setQuery($query);
                                                            $value = $this->_db->loadColumn();
                                                            $elt = '<ul>';
                                                            foreach ($value as $val){
                                                                $elt .= '<li>'.JText::_($val).'</li>';
                                                            }
                                                            $elt .= "</ul>";
                                                        } catch (Exception $e) {
                                                            JLog::add('line ' . __LINE__ . ' - Error in model/application at query: ' . $query, JLog::ERROR, 'com_emundus');
                                                            throw $e;
                                                        }
                                                    } else {
                                                        $from = $params->join_db_name;
                                                        $where = $params->join_key_column . '=' . $this->_db->Quote($r_elt);
                                                        $query = "SELECT " . $select . " FROM " . $from . " WHERE " . $where;

                                                        $query = preg_replace('#{thistable}#', $from, $query);
                                                        $query = preg_replace('#{my->id}#', $aid, $query);
                                                        $query = preg_replace('#{shortlang}#', $this->locales, $query);

                                                        $this->_db->setQuery($query);
                                                        $elt = $this->_db->loadResult();
                                                    }
                                                } elseif ($elements[$j]->plugin == 'cascadingdropdown') {
                                                    $cascadingdropdown_id = $params->cascadingdropdown_id;
                                                    $r1 = explode('___', $cascadingdropdown_id);
                                                    $cascadingdropdown_label = $params->cascadingdropdown_label;
                                                    $r2 = explode('___', $cascadingdropdown_label);
                                                    $select = !empty($params->cascadingdropdown_label_concat) ? "CONCAT(" . $params->cascadingdropdown_label_concat . ")" : $r2[1];

                                                    // Checkboxes behave like repeat groups and therefore need to be handled a second level of depth.
                                                    if ($params->cdd_display_type == 'checkbox') {
                                                        $select = !empty($params->cascadingdropdown_label_concat) ? " CONCAT(" . $params->cascadingdropdown_label_concat . ")" : 'GROUP_CONCAT(' . $r2[1] . ')';

                                                        // Load the Fabrik join for the element to it's respective repeat_repeat table.
                                                        $query = $this->_db->getQuery(true);
                                                        $query
                                                            ->select([$this->_db->quoteName('join_from_table'), $this->_db->quoteName('table_key'), $this->_db->quoteName('table_join'), $this->_db->quoteName('table_join_key')])
                                                            ->from($this->_db->quoteName('#__fabrik_joins'))
                                                            ->where($this->_db->quoteName('element_id') . ' = ' . $elements[$j]->id);
                                                        $this->_db->setQuery($query);
                                                        $f_join = $this->_db->loadObject();

                                                        $where = $r1[1] . ' IN (
                                                    SELECT ' . $this->_db->quoteName($f_join->table_join . '.' . $f_join->table_key) . '
                                                    FROM ' . $this->_db->quoteName($f_join->table_join) . '
                                                    WHERE ' . $this->_db->quoteName($f_join->table_join . '.' . $f_join->table_join_key) . ' = ' . $r_element->id . ')';
                                                    } else {
                                                        $where = $r1[1] . '=' . $this->_db->Quote($r_elt);
                                                    }

                                                    $from = $r2[0];
                                                    $query = "SELECT " . $select . " FROM " . $from . " WHERE " . $where;
                                                    $query = preg_replace('#{thistable}#', $from, $query);
                                                    $query = preg_replace('#{my->id}#', $aid, $query);
                                                    $query = preg_replace('#{shortlang}#', $this->locales, $query);

                                                    $this->_db->setQuery($query);
                                                    $elt = JText::_($this->_db->loadResult());
                                                }
                                                elseif ($elements[$j]->plugin == 'checkbox') {
                                                    $elt = "<ul><li>" . implode("</li><li>", json_decode(@$r_elt)) . "</li></ul>";
                                                }
                                                elseif ($elements[$j]->plugin == 'dropdown' || $elements[$j]->plugin == 'radiobutton') {
                                                    $index = array_search($r_elt, $params->sub_options->sub_values);
                                                    if (strlen($index) > 0) {
                                                        $elt = JText::_($params->sub_options->sub_labels[$index]);
                                                    } elseif (!empty($params->dropdown_populate)) {
                                                        try {
                                                            $options = eval($params->dropdown_populate);
                                                            $elt = $r_elt;
                                                            foreach ($options as $option) {
                                                                if ($option->value == $r_elt) {
                                                                    $elt = $option->text;
                                                                    break;
                                                                }
                                                            }
                                                        } catch (Exception $e) {
                                                            $elt = $r_elt;
                                                        }
                                                    } else {
                                                        $elt = "";
                                                    }
                                                } elseif ($elements[$j]->plugin == 'internalid') {
                                                    $elt = '';
                                                } elseif ($elements[$j]->plugin == 'field') {
                                                    if ($params->password == 1) {
                                                        $elt = '******';
                                                    } elseif ($params->password == 3) {
                                                        $elt = '<a href="mailto:' . $r_elt . '">' . $r_elt . '</a>';
                                                    } elseif ($params->password == 5) {
                                                        $elt = '<a href="' . $r_elt . '" target="_blank">' . $r_elt . '</a>';
                                                    } else {
                                                        $elt = JText::_($r_elt);
                                                    }
                                                } elseif ($elements[$j]->plugin == 'yesno') {
                                                    $elt = ($r_elt == 1) ? JText::_("JYES") : JText::_("JNO");
                                                } elseif($elements[$j]->plugin == 'emundus_phonenumber'){
                                                    $elt = substr($r_elt, 2, strlen($r_elt));
                                                }
												elseif ($elements[$j]->plugin == 'iban') {
	                                                $elt = $r_elt;

	                                                if($params->encrypt_datas == 1) {
		                                                $elt = EmundusHelperFabrik::decryptDatas($r_elt);
	                                                }

	                                                $elt = chunk_split($elt, 4, ' ');
                                                }
												else {
                                                    $elt = JText::_($r_elt);
                                                }

                                                // trick to prevent from blank value in PDF when string is to long without spaces (usually emails)
                                                $elt = str_replace('@', '<br>@', $elt);
                                                $forms .= '<td class="background-light"><div id="em_training_' . $r_element->id . '" class="course ' . $r_element->id . '">' . (($elements[$j]->plugin != 'field') ? JText::_($elt) : $elt) . '</div></td>';
                                            }
                                            $j++;
                                        }
                                        $forms .= '</tr>';
                                    }
                                }
                                $forms .= '</tbody></table></p>';
                            }


                            // TABLEAU DE PLUSIEURS LIGNES sans tenir compte du nombre de lignes
                        }
						elseif ((int)$g_params->repeated === 1 || (int)$g_params->repeat_group_button === 1)
						{
                            //-- Entrée du tableau -- */
                            $t_elt = array();
                            foreach ($elements as &$element) {
                                $t_elt[] = $element->name;
                            }
                            unset($element);

                            $query = 'SELECT table_join FROM #__fabrik_joins WHERE group_id=' . $itemg->group_id . ' AND table_join_key like "parent_id"';
                            $this->_db->setQuery($query);
                            $table = $this->_db->loadResult();

                            $check_repeat_groups = $this->checkEmptyRepeatGroups($elements, $table, $itemt->db_table_name, $fnum);

                            if ($check_repeat_groups) {
								$forms .= '<h3 class="group">' . $group_label . '</h3>';

                                if ($itemg->group_id == 174) {
                                    $query = 'SELECT `' . implode("`,`", $t_elt) . '`, id FROM ' . $table . '
                                        WHERE parent_id=(SELECT id FROM ' . $itemt->db_table_name . ' WHERE fnum like ' . $this->_db->Quote($fnum) . ') OR applicant_id=' . $aid;
                                } else {
                                    $query = 'SELECT `' . implode("`,`", $t_elt) . '`, id FROM ' . $table . '
                                    WHERE parent_id=(SELECT id FROM ' . $itemt->db_table_name . ' WHERE fnum like ' . $this->_db->Quote($fnum) . ')';
                                }

                                $this->_db->setQuery($query);
                                $repeated_elements = $this->_db->loadObjectList();
                                unset($t_elt);

                                // -- Ligne du tableau --
                                if (count($repeated_elements) > 0) {
                                    $i = 1;

                                    foreach ($repeated_elements as $r_element) {
                                        $j = 0;
                                        $forms .= '<p class="pdf-repeat-count">---- ' . $i . ' ----</p>';
                                        $forms .= '<table class="pdf-forms">';
                                        foreach ($r_element as $key => $r_elt) {
                                            $params = json_decode($elements[$j]->params);

                                            // Do not display elements with no value inside them.
                                            if (($show_empty_fields == 0 && trim($r_elt) == '') || empty($params->store_in_db)) {
                                                $j++;
                                                continue;
                                            }

                                            if ((!empty($r_elt) || $r_elt == 0) && $key != 'id' && $key != 'parent_id' && isset($elements[$j])) {

                                                if ($elements[$j]->plugin == 'date' && (!empty($r_elt) && $r_elt != '0000-00-00 00:00:00')) {
                                                    $elt = EmundusHelperDate::displayDate($r_elt, $params->date_table_format, (int)$params->date_store_as_local);
                                                }  elseif (($elements[$j]->plugin == 'birthday' || $elements[$j]->plugin == 'birthday_remove_slashes') && $r_elt > 0) {
                                                    $elt = EmundusHelperDate::displayDate($r_elt, $params->list_date_format);
                                                } elseif ($elements[$j]->plugin == 'databasejoin') {
                                                    $params = json_decode($elements[$j]->params);
                                                    $select = !empty($params->join_val_column_concat) ? "CONCAT(" . $params->join_val_column_concat . ")" : $params->join_val_column;

                                                    if ($params->database_join_display_type == 'checkbox' || $params->database_join_display_type == 'multilist') {
                                                        $db = $this->getDbo();
                                                        $query = $db->getQuery(true);

                                                        $parent_id = strlen($elements[$j]->content_id) > 0 ? $elements[$j]->content_id : 0;
                                                        $select = $this->getSelectFromDBJoinElementParams($params, 't');

                                                        $query->select($select)
                                                            ->from($db->quoteName($params->join_db_name, 't'))
                                                            ->leftJoin($db->quoteName($itemt->db_table_name . '_' . $itemg->id . '_repeat_repeat_' . $elements[$j]->name, 'checkbox_repeat') . ' ON ' . $db->quoteName('checkbox_repeat.' . $elements[$j]->name) . ' = ' . $db->quoteName('t.id'))
                                                            ->leftJoin($db->quoteName($itemt->db_table_name . '_' . $itemg->id . '_repeat', 'repeat_grp') . ' ON ' . $db->quoteName('repeat_grp.id') . ' = ' . $db->quoteName('checkbox_repeat.parent_id'))
                                                            ->where($db->quoteName('checkbox_repeat.parent_id') . ' = ' . $r_element->id);

                                                        try {
                                                            $this->_db->setQuery($query);
                                                            $value = $this->_db->loadColumn();
                                                            $elt = '<ul>';
                                                            foreach ($value as $val){
                                                                $elt .= '<li>'.JText::_($val).'</li>';
                                                            }
                                                            $elt .= "</ul>";
                                                        } catch (Exception $e) {
                                                            JLog::add('Line ' . __LINE__ . ' - Error in model/application at query: ' . $query, JLog::ERROR, 'com_emundus');
                                                            throw $e;
                                                        }
                                                    } else {
                                                        $from = $params->join_db_name;
                                                        $where = $params->join_key_column . '=' . $this->_db->Quote($r_elt);
                                                        $query = "SELECT " . $select . " FROM " . $from . " WHERE " . $where;

                                                        $query = preg_replace('#{thistable}#', $from, $query);
                                                        $query = preg_replace('#{my->id}#', $aid, $query);
                                                        $query = preg_replace('#{shortlang}#', $this->locales, $query);

                                                        $this->_db->setQuery($query);
                                                        $elt = JText::_($this->_db->loadResult());
                                                    }
                                                } elseif ($elements[$j]->plugin == 'cascadingdropdown') {
                                                    $params = json_decode($elements[$j]->params);
                                                    $cascadingdropdown_id = $params->cascadingdropdown_id;
                                                    $r1 = explode('___', $cascadingdropdown_id);
                                                    $cascadingdropdown_label = $params->cascadingdropdown_label;
                                                    $r2 = explode('___', $cascadingdropdown_label);
                                                    $select = !empty($params->cascadingdropdown_label_concat) ? "CONCAT(" . $params->cascadingdropdown_label_concat . ")" : $r2[1];
                                                    $from = $r2[0];
                                                    $where = $r1[1].'='.$this->_db->Quote($elements[$j]->content);
                                                    $query = "SELECT " . $select . " FROM " . $from . " WHERE " . $where;
                                                    $query = preg_replace('#{thistable}#', $from, $query);
                                                    $query = preg_replace('#{my->id}#', $aid, $query);
                                                    $query = preg_replace('#{shortlang}#', $this->locales, $query);

                                                    $this->_db->setQuery($query);
                                                    $elt = JText::_($this->_db->loadResult());
                                                } elseif ($elements[$j]->plugin == 'textarea') {
                                                    $elt = JText::_($r_elt);
                                                } elseif ($elements[$j]->plugin == 'checkbox') {
                                                    $elm = array();
                                                    if(!empty(array_filter($params->sub_options->sub_values)) && !empty($r_elt)){
                                                        $index = array_intersect(json_decode(@$r_elt), $params->sub_options->sub_values);
                                                    }
                                                    else{
                                                        $index = json_decode(@$r_elt);
                                                    }

                                                    foreach($index as $value) {
                                                        if(!empty(array_filter($params->sub_options->sub_values))){
                                                            $key = array_search($value,$params->sub_options->sub_values);
                                                            $elm[] = JText::_($params->sub_options->sub_labels[$key]);
                                                        }
                                                        else{
                                                            $elm[] = $value;
                                                        }
                                                    }
                                                    $elt = '<ul>';
                                                    foreach ($elm as $val){
                                                        $elt .= '<li>'.JText::_($val).'</li>';
                                                    }
                                                    $elt .= "</ul>";
                                                } elseif ($elements[$j]->plugin == 'dropdown' || $elements[$j]->plugin == 'radiobutton') {
                                                    $params = json_decode($elements[$j]->params);
                                                    $index = array_search($r_elt, $params->sub_options->sub_values);
                                                    if (strlen($index) > 0) {
                                                        $elt = JText::_($params->sub_options->sub_labels[$index]);
                                                    } elseif (!empty($params->dropdown_populate)) {
                                                        try {
                                                            $options = eval($params->dropdown_populate);
                                                            $elt = $r_elt;
                                                            foreach ($options as $option) {
                                                                if ($option->value == $r_elt) {
                                                                    $elt = $option->text;
                                                                    break;
                                                                }
                                                            }
                                                        } catch (Exception $e) {
                                                            $elt = $r_elt;
                                                        }
                                                    } else {
                                                        $elt = "";
                                                    }
                                                } elseif ($elements[$j]->plugin == 'internalid') {
                                                    $elt = '';
                                                } elseif ($elements[$j]->plugin == 'field') {
                                                    if ($params->password == 1) {
                                                        $elt = '******';
                                                    } elseif ($params->password == 3) {
                                                        $elt = '<a href="mailto:' . $r_elt . '">' . $r_elt . '</a>';
                                                    } elseif ($params->password == 5) {
                                                        $elt = '<a href="' . $r_elt . '" target="_blank">' . $r_elt . '</a>';
                                                    } else {
                                                        $elt = JText::_($r_elt);
                                                    }
                                                } elseif ($elements[$j]->plugin == 'yesno') {
                                                    $elt = ($r_elt == 1) ? JText::_("JYES") : JText::_("JNO");
                                                } elseif ($elements[$j]->plugin == 'display') {
                                                    $elt = empty($elements[$j]->eval) ? $elements[$j]->default : $r_elt;
                                                } elseif ($elements[$j]->plugin == 'emundus_phonenumber') {
	                                                $elt = substr($r_elt, 2, strlen($r_elt));
                                                } else if ($elements[$j]->plugin == 'calc') {
	                                                $elt = JText::_($r_elt);

	                                                $stripped = strip_tags($elt);
	                                                if ($stripped != $elt) {
		                                                $elt = strip_tags($elt, ['p', 'a', 'div', 'ul', 'li', 'br']);
	                                                }
                                                }
                                                elseif ($elements[$j]->plugin == 'iban') {
	                                                $elt = $r_elt;

	                                                if($params->encrypt_datas == 1) {
		                                                $elt = EmundusHelperFabrik::decryptDatas($r_elt);
	                                                }

	                                                $elt = chunk_split($elt, 4, ' ');
                                                }
												else {
                                                    $elt = JText::_($r_elt);
                                                }

                                                if ($show_empty_fields == 1 || !empty($elt)) {
                                                    if ($elements[$j]->plugin == 'display') {
                                                        $forms .= '<tr><td colspan="2" style="background-color: var(--neutral-200);"><span style="color: #000000;">' . (!empty($params->display_showlabel) && !empty(JText::_($elements[$j]->label)) ? JText::_($elements[$j]->label) . ' : ' : '') . '</span></td></tr><tr><td colspan="2"><span style="color: #000000;">' . $elt . '</span></td></tr><br/>';
                                                    } elseif ($elements[$j]->plugin == 'textarea') {
                                                        $forms .= '</table>';
                                                        $forms .= '<div style="width: 93.5%;padding: 8px 16px;">';
                                                        $forms .= '<div style="width: 100%; padding: 4px 8px;background-color: #F3F3F3;color: #000000;border: solid 1px #A4A4A4;border-bottom: unset;font-size: 12px">' .  (!empty(JText::_($elements[$j]->label)) ? JText::_($elements[$j]->label) . ' : ' : '')  . '</div>';

                                                        $params = json_decode($elements[$j]->params);
                                                        if (!empty($params) && $params->use_wysiwyg == 1) {
                                                            $forms .= '<div style="width: 100%; padding: 4px 8px;color: #000000;border: solid 1px #A4A4A4;font-size: 12px">' . preg_replace('/<br\s*\/?>/','',JText::_($elt)) . '</div>';
                                                        } else {
                                                            $forms .= '<div style="width: 100%; padding: 4px 8px;color: #000000;border: solid 1px #A4A4A4;font-size: 12px;word-break:break-all; hyphens:auto;">' . JText::_($elt) . '</div>';
                                                        }
                                                        $forms .= '</div>';
                                                        $forms .= '<table class="pdf-forms">';
                                                    } else {
                                                        $forms .= '<tr><td colspan="1" style="background-color: var(--neutral-200);"><span style="color: #000000;">' . (!empty(JText::_($elements[$j]->label)) ? JText::_($elements[$j]->label) . ' : ' : '') . '</span></td> <td> ' . (($elements[$j]->plugin != 'field') ? JText::_($elt) : $elt) . '</td></tr>';
                                                    }
                                                }
                                            }
                                            $j++;
                                        }
                                        $forms .= '</table>';
                                        $i++;
                                    }
                                }
                            }


                            // AFFICHAGE EN LIGNE
                        } else {
                            $check_not_empty_group = $this->checkEmptyGroups($elements ,$itemt->db_table_name, $fnum);

                            if($check_not_empty_group) {
								$forms .= '<h3 class="group">' . $group_label . '</h3>';

                                $forms .= '<table class="pdf-forms">';
                                foreach ($elements as $element) {
                                    $params = json_decode($element->params);
                                    if (empty($params->store_in_db)) {
                                        continue;
                                    }

                                    $query = 'SELECT `id`, `' . $element->name . '` FROM `' . $itemt->db_table_name . '` WHERE fnum like ' . $this->_db->Quote($fnum);
                                    try {
                                        $this->_db->setQuery($query);
                                        $res = $this->_db->loadRow();
                                    } catch (Exception $e) {
                                        JLog::add('Error in model/application at query: ' . $query, JLog::ERROR, 'com_emundus');
                                        throw $e;
                                    }

                                    if (count($res) > 1) {
                                        if ($element->plugin == 'display') {
                                            $element->content = empty($element->eval) ? $element->default : $res[1];
                                        } else {
                                            $element->content = $res[1];
                                        }
                                        $element->content_id = $res[0];
                                    } else {
                                        $element->content = '';
                                        $element->content_id = -1;
                                    }

                                    // Decrypt datas encoded
                                    if($form_params->note == 'encrypted'){
	                                    $element->content = EmundusHelperFabrik::decryptDatas($element->content,null,'aes-128-cbc',$element->plugin);
                                    }
                                    //

                                    if (!empty($element->content) || (isset($params->database_join_display_type) && ($params->database_join_display_type == 'checkbox' || $params->database_join_display_type == 'multilist')) || $element->plugin == 'yesno') {

                                        if (!empty($element->label) && $element->label != ' ' || $element->plugin === 'display') {

                                            if ($element->plugin == 'date') {

                                                // Empty date elements are set to 0000-00-00 00:00:00 in DB.
                                                if ($show_empty_fields == 0 && ($element->content == '0000-00-00 00:00:00' || empty($element->content))) {
                                                    continue;
                                                } elseif (!empty($element->content) && $element->content != '0000-00-00 00:00:00') {
                                                    $elt = EmundusHelperDate::displayDate($element->content, $params->date_table_format, (int)$params->date_store_as_local);
                                                } else {
                                                    $elt = '';
                                                }
                                            }
                                            elseif ($element->plugin == 'textarea'){
	                                            $elt = nl2br($element->content);
                                            }
											elseif (($element->plugin == 'birthday' || $element->plugin == 'birthday_remove_slashes') && $element->content > 0) {
                                                $elt = EmundusHelperDate::displayDate($element->content, $params->list_date_format);
                                            } elseif ($element->plugin == 'databasejoin') {
                                                $select = !empty($params->join_val_column_concat) ? "CONCAT(" . $params->join_val_column_concat . ")" : $params->join_val_column;

                                                if ($params->database_join_display_type == 'checkbox' || $params->database_join_display_type == 'multilist') {
                                                    $db = $this->getDbo();
                                                    $query = $db->getQuery(true);

                                                    $parent_id = strlen($element->content_id) > 0 ? $element->content_id : 0;
                                                    $select = $this->getSelectFromDBJoinElementParams($params);

                                                    $query->select($select)
                                                        ->from($db->quoteName($itemt->db_table_name . '_repeat_' . $element->name, 't'))
                                                        ->leftJoin($db->quoteName($params->join_db_name, 'jd') . ' ON ' . $db->quoteName('jd.' . $params->join_key_column) . ' = ' . $db->quoteName('t.' . $element->name))
                                                        ->where($db->quoteName('parent_id') . ' = ' . $db->quote($parent_id));

                                                    try {
                                                        $this->_db->setQuery($query);
                                                        $value = $this->_db->loadColumn();
                                                        $elt = '<ul>';
                                                        foreach ($value as $val){
                                                            $elt .= '<li>'.JText::_($val).'</li>';
                                                        }
                                                        $elt .= "</ul>";
                                                    } catch (Exception $e) {
                                                        JLog::add('line ' . __LINE__ . ' - Error in model/application at query: ' . $query, JLog::ERROR, 'com_emundus');
                                                        throw $e;
                                                    }
                                                } else {
                                                    $from = $params->join_db_name;
                                                    $where = $params->join_key_column . '=' . $this->_db->Quote($element->content);
                                                    $query = "SELECT " . $select . " FROM " . $from . " WHERE " . $where;

                                                    $query = preg_replace('#{thistable}#', $from, $query);
                                                    $query = preg_replace('#{my->id}#', $aid, $query);
                                                    $query = preg_replace('#{shortlang}#', $this->locales, $query);

                                                    $this->_db->setQuery($query);
                                                    $elt = JText::_($this->_db->loadResult());
                                                }
                                            } elseif ($element->plugin == 'cascadingdropdown') {
                                                $cascadingdropdown_id = $params->cascadingdropdown_id;
                                                $r1 = explode('___', $cascadingdropdown_id);
                                                $cascadingdropdown_label = $params->cascadingdropdown_label;
                                                $r2 = explode('___', $cascadingdropdown_label);
                                                $select = !empty($params->cascadingdropdown_label_concat) ? "CONCAT(" . $params->cascadingdropdown_label_concat . ")" : $r2[1];
                                                $from = $r2[0];
                                                $where = $r1[1] . '=' . $this->_db->Quote($element->content);
                                                $query = "SELECT " . $select . " FROM " . $from . " WHERE " . $where;
                                                $query = preg_replace('#{thistable}#', $from, $query);
                                                $query = preg_replace('#{my->id}#', $aid, $query);
                                                $query = preg_replace('#{shortlang}#', $this->locales, $query);

                                                $this->_db->setQuery($query);
                                                $elt = JText::_($this->_db->loadResult());

                                            } elseif ($element->plugin == 'textarea') {
                                                $elt = JText::_($element->content);
                                            } elseif ($element->plugin == 'checkbox') {
                                                $params = json_decode($element->params);
                                                $elm = array();
                                                if(!empty($element->content)) {
                                                    $index = array_intersect(json_decode(@$element->content), $params->sub_options->sub_values);
                                                    foreach ($index as $value) {
                                                        $key = array_search($value, $params->sub_options->sub_values);
                                                        $elm[] = JText::_($params->sub_options->sub_labels[$key]);
                                                    }
                                                }
                                                $elt = '<ul>';
                                                foreach ($elm as $val){
                                                    $elt .= '<li>'.JText::_($val).'</li>';
                                                }
                                                $elt .= "</ul>";
                                            } elseif ($element->plugin == 'dropdown' || $element->plugin == 'radiobutton') {
                                                $index = array_search($element->content, $params->sub_options->sub_values);
                                                if (strlen($index) > 0) {
                                                    $elt = JText::_($params->sub_options->sub_labels[$index]);
                                                } elseif ($params->multiple == 1) {
                                                    $elt = implode(", ", json_decode(@$element->content));
                                                } elseif (!empty($params->dropdown_populate)) {
                                                    try {
                                                        $options = eval($params->dropdown_populate);
                                                        $elt = $element->content;
                                                        foreach ($options as $option) {
                                                            if ($option->value == $element->content) {
                                                                $elt = $option->text;
                                                                break;
                                                            }
                                                        }
                                                    } catch (Exception $e) {
                                                        $elt = $element->content;
                                                    }
                                                } else {
                                                    $elt = "";
                                                }
                                            } elseif ($element->plugin == 'internalid') {
                                                $elt = '';
                                            } elseif ($element->plugin == 'yesno') {
                                                $elt = ($element->content == 1) ? JText::_('JYES') : JText::_('JNO');
                                            } elseif ($element->plugin == 'field') {
                                                $params = json_decode($element->params);

                                                if ($params->password == 1) {
                                                    $elt = '******';
                                                } elseif ($params->password == 3) {
                                                    $elt = '<a href="mailto:' . $element->content . '" title="' . JText::_($element->label) . '">' . $element->content . '</a>';
                                                } elseif ($params->password == 5) {
                                                    $elt = '<a href="' . $element->content . '" target="_blank" title="' . JText::_($element->label) . '">' . $element->content . '</a>';
                                                } else {
                                                    $elt = $element->content;
                                                }
                                            } elseif ($element->plugin == 'emundus_phonenumber'){
                                                $elt = substr($element->content, 2, strlen($element->content));
                                            } else if ($element->plugin == 'calc') {
	                                            $elt = JText::_($element->content);

	                                            $stripped = strip_tags($elt);
	                                            if ($stripped != $elt) {
		                                            $elt = strip_tags($elt, ['p', 'a', 'div', 'ul', 'li', 'br']);
	                                            }
                                            }
											elseif ($element->plugin == 'iban') {
												$params = json_decode($element->params);
	                                            $elt = $element->content;

	                                            if($params->encrypt_datas == 1) {
		                                            $elt = EmundusHelperFabrik::decryptDatas($element->content);
	                                            }

	                                            $elt = chunk_split($elt, 4, ' ');
                                            }
											else {
                                                $elt = JText::_($element->content);
                                            }

                                            if ($element->plugin == 'display') {
                                                $forms .= '<tr><td colspan="2"><span style="color: #000000;">' . (!empty($params->display_showlabel) && !empty(JText::_($element->label)) ? JText::_($element->label) . ' : ' : '') . '</span></td></tr><tr><td colspan="2"><span style="color: #000000;">' . $elt . '</span></td></tr><br/>';
                                            } elseif ($element->plugin == 'textarea') {
                                                $params = json_decode($element->params);

                                                $forms .= '</table>';
	                                            $forms .= '<div style="width: 93.5%;padding: 8px 16px;">';
	                                            $forms .= '<div style="width: 100%; padding: 4px 8px;background-color: #F3F3F3;color: #000000;border: solid 1px #A4A4A4;border-bottom: unset;font-size: 12px">' .  (!empty(JText::_($element->label)) ? JText::_($element->label) . ' : ' : '')  . '</div>';
                                                if (!empty($params) && $params->use_wysiwyg == 1) {
                                                    $forms .= '<div style="width: 100%; padding: 4px 8px;color: #000000;border: solid 1px #A4A4A4;font-size: 12px">' . preg_replace('/<br\s*\/?>/','',JText::_($elt)) . '</div>';
                                                } else {
                                                    $forms .= '<div style="width: 100%; padding: 4px 8px;color: #000000;border: solid 1px #A4A4A4;font-size: 12px;word-break:break-word; hyphens:auto;">' . JText::_($elt) . '</div>';
                                                }
	                                            $forms .= '</div>';
	                                            $forms .= '<table class="pdf-forms">';
                                            } else {
                                                $forms .= '<tr><td colspan="1" style="background-color: var(--neutral-200);"><span style="color: #000000;">' . (!empty(JText::_($element->label)) ? JText::_($element->label) . ' : ' : '') . '</span></td> <td> ' . (($element->plugin != 'field') ? JText::_($elt) : $elt) . '</td></tr>';
                                            }
                                        }
                                    } elseif (empty($element->content) && $show_empty_fields == 1) {
                                        if (!empty($element->label) && $element->label != ' ') {
                                            $forms .= '<tr><td><span style="color: #000000;">' . JText::_($element->label) . ' ' . '</span></td> <td>' . $element->content . '</td></tr>';
                                        }
                                    }
                                }
                                $forms .= '</table><div></div>';
                            }
                        }
                    }
                }
                $forms .= '<p></p>';
            }
        }
        $forms .= '<p></p>';

        if($attachments) {
			$forms .= '<div class="page-break pdf-attachments">';
            $upload_files = $this->getCountUploadedFile($fnum, $aid, $profile_id);
            $forms .= $upload_files;

            $list_upload_files = $this->getListUploadedFile($fnum, $aid, $profile_id);
            $forms .= $list_upload_files;
			$forms .= '</div>';
        }

        return $forms;
    }

    public function getFormsPDFElts($aid, $elts, $options, $checklevel = true)
    {

        $tableuser = @EmundusHelperList::getFormsListByProfileID($options['profile_id'], $checklevel);

        $forms = "<style>
					table {
					    border-spacing: 1px;
					    background-color: #f2f2f2;
					    width: 100%;
					}
					th {
					    border-spacing: 1px; color: #666666;
					}
					td {
					    border-spacing: 1px;
					    background-color: #FFFFFF;
					}
					</style>";
        if (isset($tableuser)) {
            foreach ($tableuser as $key => $itemt) {
                $forms .= ($options['show_list_label'] == 1) ? '<h2>' . JText::_($itemt->label) . '</h2>' : '';
                // liste des groupes pour le formulaire d'une table
                $query = 'SELECT ff.id, ff.group_id, fg.id, fg.label, fg.params
                            FROM #__fabrik_formgroup ff, #__fabrik_groups fg
                            WHERE ff.group_id = fg.id AND fg.published = 1 AND
                                  ff.form_id = "' . $itemt->form_id . '"
                            ORDER BY ff.ordering';

                $this->_db->setQuery($query);

                $groupes = $this->_db->loadObjectList();

                /*-- Liste des groupes -- */
                foreach ($groupes as $keyg => $itemg) {

                    $g_params = json_decode($itemg->params);

                    if (!EmundusHelperAccess::isAllowedAccessLevel($this->_user->id, (int)$g_params->access)) {
                        continue;
                    }

                    // liste des items par groupe
                    $query = 'SELECT fe.id, fe.name, fe.label, fe.plugin, fe.params
                                FROM #__fabrik_elements fe
                                WHERE fe.published=1 AND
                                      fe.hidden=0 AND
                                      fe.group_id = "' . $itemg->group_id . '" AND
                                      fe.id IN (' . implode(',', $elts) . ')
                                ORDER BY fe.ordering';

                    $this->_db->setQuery($query);

                    $elements = $this->_db->loadObjectList();

                    if (count($elements) > 0) {
                        $forms .= ($options['show_group_label'] == 1) ? '<h3>' . JText::_($itemg->label) . '</h3>' : '';

                        foreach ($elements as &$iteme) {
                            $where = $options['rowid'] > 0 ? ' id=' . $options['rowid'] : ' 1=1 ';

                            if ($checklevel) {
                                $where .= ' AND user=' . $aid;
                            }

                            $query = 'SELECT `' . $iteme->name . '` FROM `' . $itemt->db_table_name . '` WHERE ' . $where;
                            $this->_db->setQuery($query);

                            $iteme->content = $this->_db->loadResult();
                        }
                        unset($iteme);

                        if ($itemg->group_id == 14) {

                            foreach ($elements as $element) {
                                if (!empty($element->label) && $element->label != ' ') {
                                    if ($element->plugin == 'date' && $element->content > 0) {
                                        $date_params = json_decode($element->params);
                                        $elt = date($date_params->date_form_format, strtotime($element->content));
                                    } else {
                                        $elt = $element->content;
                                    }
                                    $forms .= '<p class="form-element"><b>' . JText::_($element->label) . ': </b>' . JText::_($elt) . '</p>';
                                }
                            }

                            // TABLEAU DE PLUSIEURS LIGNES
                        } elseif ((int)$g_params->repeated === 1 || (int)$g_params->repeat_group_button === 1) {
                            $forms .= '<p><table class="adminlist">
                              <thead>
                              <tr> ';

                            //-- Entrée du tableau -- */
                            //$nb_lignes = 0;
                            $t_elt = array();
                            foreach ($elements as $element) {
                                $t_elt[] = $element->name;
                                $forms .= '<th scope="col">' . JText::_($element->label) . '</th>';
                            }
                            unset($element);
                            //$table = $itemt->db_table_name.'_'.$itemg->group_id.'_repeat';
                            $query = 'SELECT table_join FROM #__fabrik_joins WHERE group_id=' . $itemg->group_id;
                            $this->_db->setQuery($query);
                            $table = $this->_db->loadResult();

                            if ($itemg->group_id == 174)
                                $query = 'SELECT ' . implode(",", $t_elt) . ', id FROM ' . $table . '
                                        WHERE parent_id=(SELECT id FROM ' . $itemt->db_table_name . ' WHERE user=' . $aid . ') OR applicant_id=' . $aid;
                            else
                                $query = 'SELECT ' . implode(",", $t_elt) . ', id FROM ' . $table . '
                                    WHERE parent_id=(SELECT id FROM ' . $itemt->db_table_name . ' WHERE user=' . $aid . ')';
                            $this->_db->setQuery($query);
                            $repeated_elements = $this->_db->loadObjectList();
                            unset($t_elt);
                            $forms .= '</tr></thead><tbody>';

                            // -- Ligne du tableau --
                            foreach ($repeated_elements as $r_element) {
                                $forms .= '<tr>';
                                $j = 0;
                                foreach ($r_element as $key => $r_elt) {
                                    if ($key != 'id' && $key != 'parent_id' && isset($elements[$j]) && $elements[$j]->plugin != 'display') {

                                        if ($elements[$j]->plugin == 'date') {
                                            if (!empty($elements[$j]->content) && $r_elt != '0000-00-00 00:00:00') {
                                                $date_params = json_decode($elements[$j]->params);
                                                $elt = date($date_params->date_form_format, strtotime($r_elt));
                                            } else {
                                                $elt = '';
                                            }

                                        } elseif (($elements[$j]->plugin == 'birthday' || $elements[$j]->plugin == 'birthday_remove_slashes') && $r_elt > 0) {
                                            preg_match('/([0-9]{4})-([0-9]{1,})-([0-9]{1,})/', $r_elt, $matches);
                                            if (count($matches) == 0) {
                                                $elt = $r_elt;
                                            } else {
                                                $format = json_decode($elements[$j]->params)->list_date_format;

                                                $d = DateTime::createFromFormat($format, $r_elt);
                                                if ($d && $d->format($format) == $r_elt) {
                                                    $elt = JHtml::_('date', $r_elt, JText::_('DATE_FORMAT_LC'));
                                                } else {
                                                    $elt = JHtml::_('date', $r_elt, $format);
                                                }
                                            }
                                        } elseif ($elements[$j]->plugin == 'databasejoin') {
                                            $params = json_decode($elements[$j]->params);
                                            $select = !empty($params->join_val_column_concat) ? "CONCAT(" . $params->join_val_column_concat . ")" : $params->join_val_column;

                                            if ($params->database_join_display_type == 'checkbox') {
                                                $db = $this->getDbo();
                                                $query = $db->getQuery(true);

                                                $parent_id = strlen($elements[$j]->content_id) > 0 ? $elements[$j]->content_id : 0;
                                                $select = $this->getSelectFromDBJoinElementParams($params);

                                                $query->select($select)
                                                    ->from($db->quoteName($itemt->db_table_name . '_repeat_' . $elements[$j]->name, 't'))
                                                    ->leftJoin($db->quoteName($params->join_db_name, 'jd') . ' ON ' . $db->quoteName('jd.' . $params->join_key_column) . ' = ' . $db->quoteName('t.' . $elements[$j]->name))
                                                    ->where($db->quoteName('parent_id') . ' = ' . $db->quote($parent_id));

                                                try {
                                                    $this->_db->setQuery($query);
                                                    $res = $this->_db->loadColumn();
                                                    $elt = implode(', ', $res);
                                                } catch (Exception $e) {
                                                    JLog::add('line ' . __LINE__ . ' - Error in model/application at query: ' . $query, JLog::ERROR, 'com_emundus');
                                                    throw $e;
                                                }
                                            } else {
                                                $from = $params->join_db_name;
                                                $where = $params->join_key_column . '=' . $this->_db->Quote($r_elt);
                                                $query = "SELECT " . $select . " FROM " . $from . " WHERE " . $where;
                                                $query = preg_replace('#{thistable}#', $from, $query);
                                                $query = preg_replace('#{my->id}#', $aid, $query);
                                                $query = preg_replace('#{shortlang}#', $this->locales, $query);

                                                $this->_db->setQuery($query);
                                                $elt = $this->_db->loadResult();
                                            }
                                        } elseif ($elements[$j]->plugin == 'checkbox') {
                                            $elt = implode(", ", json_decode(@$r_elt));
                                        } elseif ($elements[$j]->plugin == 'dropdown' || $elements[$j]->plugin == 'radiobutton') {
                                            $params = json_decode($elements[$j]->params);
                                            $index = array_search($r_elt, $params->sub_options->sub_values);
                                            if (strlen($index) > 0) {
                                                $elt = JText::_($params->sub_options->sub_labels[$index]);
                                            } else {
                                                $elt = "";
                                            }
                                        } elseif ($elements[$j]->plugin == 'fileupload') {
											$file_path = explode('/', $r_elt);
                                            $filename = end($file_path);
                                            $elt = '<a href="' . JUri::base() . $elt . '" target="_blank">' . $filename . '</a>';
                                        } elseif ($elements[$j]->plugin == 'yesno') {
                                            $elt = ($r_elt == 1) ? JText::_("JYES") : JText::_("JNO");
                                        } else
                                            $elt = $r_elt;

                                        $forms .= '<td><div id="em_training_' . $r_element->id . '" class="course ' . $r_element->id . '">' . JText::_($elt) . '</div></td>';
                                    }
                                    $j++;
                                }
                                $forms .= '</tr>';
                            }
                            $forms .= '</tbody></table></p>';

                            // AFFICHAGE EN LIGNE
                        } else {
                            foreach ($elements as $element) {
                                if (!empty($element->label) && $element->label != ' ' && $element->plugin != 'display') {

                                    if ($element->plugin == 'date' && $element->content > 0) {
                                        if (!empty($element->content) && $element->content != '0000-00-00 00:00:00') {
                                            $date_params = json_decode($element->params);
                                            $elt = date($date_params->date_form_format, strtotime($element->content));
                                        } else {
                                            $elt = '';
                                        }

                                    } elseif (($element->plugin == 'birthday' || $element->plugin == 'birthday_remove_slashes') && $element->content > 0) {
                                        preg_match('/([0-9]{4})-([0-9]{1,})-([0-9]{1,})/', $element->content, $matches);
                                        if (count($matches) == 0) {
                                            $elt = $element->content;
                                        } else {
                                            $format = json_decode($element->params)->list_date_format;

                                            $d = DateTime::createFromFormat($format, $element->content);
                                            if ($d && $d->format($format) == $element->content) {
                                                $elt = JHtml::_('date', $element->content, JText::_('DATE_FORMAT_LC'));
                                            } else {
                                                $elt = JHtml::_('date', $element->content, $format);
                                            }
                                        }
                                    } elseif ($element->plugin == 'databasejoin') {
                                        $params = json_decode($element->params);
                                        $select = !empty($params->join_val_column_concat) ? "CONCAT(" . $params->join_val_column_concat . ")" : $params->join_val_column;

                                        if ($params->database_join_display_type == 'checkbox') {
                                            $db = $this->getDbo();
                                            $query = $db->getQuery(true);

                                            $parent_id = strlen($element->content_id) > 0 ? $element->content_id : 0;
                                            $select = $this->getSelectFromDBJoinElementParams($params);

                                            $query->select($select)
                                                ->from($db->quoteName($itemt->db_table_name . '_repeat_' . $element->name, 't'))
                                                ->leftJoin($db->quoteName($params->join_db_name, 'jd') . ' ON ' . $db->quoteName('jd.' . $params->join_key_column) . ' = ' . $db->quoteName('t.' . $element->name))
                                                ->where($db->quoteName('parent_id') . ' = ' . $db->quote($parent_id));

                                            try {
                                                $this->_db->setQuery($query);
                                                $res = $this->_db->loadColumn();
                                                $elt = implode(', ', $res);
                                            } catch (Exception $e) {
                                                JLog::add('line ' . __LINE__ . ' - Error in model/application at query: ' . $query, JLog::ERROR, 'com_emundus');
                                                throw $e;
                                            }
                                        } else {
                                            $from = $params->join_db_name;
                                            $where = $params->join_key_column . '=' . $this->_db->Quote($element->content);
                                            $query = "SELECT " . $select . " FROM " . $from . " WHERE " . $where;
                                            $query = preg_replace('#{thistable}#', $from, $query);
                                            $query = preg_replace('#{my->id}#', $aid, $query);
                                            $query = preg_replace('#{shortlang}#', $this->locales, $query);

                                            $this->_db->setQuery($query);
                                            $elt = $this->_db->loadResult();
                                        }
                                    } elseif ($element->plugin == 'cascadingdropdown') {
                                        $params = json_decode($element->params);
                                        $cascadingdropdown_id = $params->cascadingdropdown_id;
                                        $r1 = explode('___', $cascadingdropdown_id);
                                        $cascadingdropdown_label = $params->cascadingdropdown_label;
                                        $r2 = explode('___', $cascadingdropdown_label);
                                        $select = !empty($params->cascadingdropdown_label_concat) ? "CONCAT(" . $params->cascadingdropdown_label_concat . ")" : $r2[1];
                                        $from = $r2[0];
                                        $where = $r1[1] . '=' . $this->_db->Quote($element->content);
                                        $query = "SELECT " . $select . " FROM " . $from . " WHERE " . $where;
                                        $query = preg_replace('#{thistable}#', $from, $query);
                                        $query = preg_replace('#{my->id}#', $aid, $query);
                                        $query = preg_replace('#{shortlang}#', $this->locales, $query);

                                        $this->_db->setQuery($query);
                                        $elt = $this->_db->loadResult();
                                    } elseif ($element->plugin == 'checkbox') {
                                        $elt = implode(", ", json_decode(@$element->content));
                                    } elseif ($element->plugin == 'dropdown' || $element->plugin == 'radiobutton') {
                                        $params = json_decode($element->params);
                                        $index = array_search($element->content, $params->sub_options->sub_values);
                                        if (strlen($index) > 0) {
                                            $elt = JText::_($params->sub_options->sub_labels[$index]);
                                        } else {
                                            $elt = "";
                                        }
                                    } elseif ($element->plugin == 'fileupload') {
										$file_path = explode('/', @$element->content);
                                        $filename = end($file_path);
                                        $elt = '<a href="' . JUri::base() . $element->content . '" target="_blank">' . $filename . '</a>';
                                    } elseif ($element->plugin == 'yesno') {
                                        $elt = ($element->content == 1) ? JText::_("JYES") : JText::_("JNO");
                                    } else {
                                        $elt = $element->content;
                                    }

                                    $forms .= '<p class="form-element"><b>' . JText::_($element->label) . ': </b>' . JText::_($elt) . '</p>';
                                }
                            }
                        }
                        //$forms .= '</fieldset>';
                    }
                }
            }
        }
        return $forms;
    }

    public function getEmail($user_id) {
        $query = 'SELECT *
        FROM #__messages as email
        LEFT JOIN #__users as user ON user.id=email.user_id_from
        LEFT JOIN #__emundus_users as eu ON eu.user_id=user.id
        WHERE email.user_id_to ='.$user_id.' ORDER BY `date_time` DESC';
        $this->_db->setQuery($query);
        $results['to'] = $this->_db->loadObjectList('message_id');

        $query = 'SELECT *
        FROM #__messages as email
        LEFT JOIN #__users as user ON user.id=email.user_id_to
        LEFT JOIN #__emundus_users as eu ON eu.user_id=user.id
        WHERE email.user_id_from ='.$user_id.' ORDER BY `date_time` DESC';
        $this->_db->setQuery($query);
        $results['from'] = $this->_db->loadObjectList('message_id');

        return $results;
    }

    public function getApplicationMenu($user_id = 0) {
		$user_id = $user_id ?: JFactory::getUser()->id;
        $juser = JFactory::getUser($user_id);

		$menus = [];

        try {
            $db = $this->getDbo();
	        $query = $db->getQuery(true);

            $grUser = $juser->getAuthorisedViewLevels();

			$query->select('id, title, link, lft, rgt, note')
				->from($db->quoteName('#__menu'))
				->where($db->quoteName('published') . ' = 1')
				->where($db->quoteName('menutype') . ' = ' . $db->quote('application'))
				->where($db->quoteName('access') . ' IN (' . implode(',', $grUser) . ')')
				->order($db->quoteName('lft'));
            $db->setQuery($query);
            $menus = $db->loadAssocList();

        } catch (Exception $e) {
            JLog::add('line ' . __LINE__ . ' - Error in model/application at query: ' . $query->__toString(), JLog::ERROR, 'com_emundus');
        }

		return $menus;
    }

    public function getProgramSynthesis($cid) {
        try {
            $db = $this->getDbo();
            $query = 'select p.synthesis, p.id, p.label from #__emundus_setup_programmes as p left join #__emundus_setup_campaigns as c on c.training = p.code where c.id='.$cid;
            $db->setQuery($query);
            return $db->loadObject();
        } catch (Exception $e) {
            return null;
        }
    }

    public function getAttachments($ids) {
        try {
            $query = "SELECT id, fnum, user_id, filename FROM #__emundus_uploads WHERE id in (".implode(',', $ids).")";
            $this->_db->setQuery($query);
            return $this->_db->loadObjectList();
        } catch (Exception $e) {
            error_log($e->getMessage(), 0);
            return false;
        }
    }

    public function getAttachmentsByFnum($fnum, $ids=null, $attachment_id=null, $profile=null) {
        try {
            require_once(JPATH_SITE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'profile.php');
            require_once(JPATH_SITE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'files.php');

            $m_profiles = new EmundusModelProfile;
            $m_files = new EmundusModelFiles;
            $fnumInfos = $m_files->getFnumInfos($fnum);

            $profiles_by_campaign = $m_profiles->getProfilesIDByCampaign([$fnumInfos['id']]);

            // TODO : Group attachments by profile and adding profile column in jos_emundus_uploads
            $query = "SELECT DISTINCT eu.*, sa.value 
                        FROM #__emundus_uploads as eu
                        LEFT JOIN #__emundus_setup_attachments as sa ON sa.id = eu.attachment_id
                        LEFT JOIN #__emundus_setup_attachment_profiles as sap ON sap.attachment_id = sa.id AND sap.profile_id IN (".implode(',',$profiles_by_campaign).")
                        WHERE fnum like ".$this->_db->quote($fnum);

            if (isset($attachment_id) && !empty($attachment_id)){
                if (is_array($attachment_id) && $attachment_id[0] != "") {
                    $query .= " AND eu.attachment_id IN (".implode(',', $attachment_id).")";
                } else {
                    $query .= " AND eu.attachment_id = ".$attachment_id;
                }
            }

            if (!empty($ids) && $ids != "null") {
                $query .= " AND eu.id in ($ids)";
            }

	        if(!empty($profile))
	        {
		        $query .= " ORDER BY sap.mandatory DESC,sap.ordering,sa.value ASC";
	        } else {
		        $query .= " ORDER BY sa.category, sa.ordering, sa.value ASC";
	        }

            $this->_db->setQuery($query);
            $docs = $this->_db->loadObjectList();
        } catch(Exception $e) {
            error_log($e->getMessage(), 0);
            return false;
        }

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
        // Sort the docs out that are not allowed to be exported by the user.
        $allowed_attachments = EmundusHelperAccess::getUserAllowedAttachmentIDs(JFactory::getUser()->id);
        if ($allowed_attachments !== true) {
            foreach ($docs as $key => $doc) {
                if (!in_array($doc->attachment_id, $allowed_attachments)) {
                    unset($docs[$key]);
                }
            }
        }
        return $docs;
    }

    public function getAccessFnum($fnum)
    {
        $access = [];

        if (!empty($fnum)) {
            $query = "SELECT jecc.fnum, jesg.label as gname, jea.*, jesa.label as aname FROM #__emundus_campaign_candidature as jecc
                    LEFT JOIN #__emundus_setup_campaigns as jesc on jesc.id = jecc.campaign_id
                    LEFT JOIN #__emundus_setup_programmes as jesp on jesp.code = jesc.training
                    LEFT JOIN #__emundus_setup_groups_repeat_course as jesgrc on jesgrc.course = jesp.code
                    LEFT JOIN #__emundus_setup_groups as jesg on jesg.id = jesgrc.parent_id
                    LEFT JOIN #__emundus_acl as jea on jea.group_id = jesg.id
                    LEFT JOIN #__emundus_setup_actions as jesa on jesa.id = jea.action_id
                    WHERE jecc.fnum like '".$fnum."' and jesa.status = 1 order by jecc.fnum, jea.group_id, jea.action_id";

            try
            {
                $db = $this->getDbo();
                $db->setQuery($query);
                $res = $db->loadAssocList();

                foreach($res as $r)
                {
                    $access['groups'][$r['group_id']]['gname'] = $r['gname'];
                    $access['groups'][$r['group_id']]['isAssoc'] = false;
                    $access['groups'][$r['group_id']]['isACL'] = true;
                    $access['groups'][$r['group_id']]['actions'][$r['action_id']]['aname'] = $r['aname'];
                    $access['groups'][$r['group_id']]['actions'][$r['action_id']]['c'] = $r['c'];
                    $access['groups'][$r['group_id']]['actions'][$r['action_id']]['r'] = $r['r'];
                    $access['groups'][$r['group_id']]['actions'][$r['action_id']]['u'] = $r['u'];
                    $access['groups'][$r['group_id']]['actions'][$r['action_id']]['d'] = $r['d'];
                }
                $query = "SELECT jeacl.group_id, jeacl.action_id as acl_action_id, jeacl.c as acl_c, jeacl.r as acl_r, jeacl.u as acl_u, jeacl.d as acl_d,
                        jega.fnum, jega.action_id, jega.c, jega.r, jega.u, jega.d, jesa.label as aname,
                        jesg.label as gname
                        FROM jos_emundus_acl as jeacl
                        LEFT JOIN jos_emundus_setup_actions as jesa ON jesa.id = jeacl.action_id
                        LEFT JOIN jos_emundus_setup_groups as jesg on jesg.id = jeacl.group_id
                        LEFT JOIN jos_emundus_group_assoc as jega on jega.group_id=jesg.id
                        WHERE  jega.fnum like ".$db->quote($fnum)." and jesa.status = 1
                        ORDER BY jega.fnum, jega.group_id, jega.action_id";
                $db->setQuery($query);
                $res = $db->loadAssocList();
                foreach($res as $r)
                {
                    $overrideAction = ($r['acl_action_id'] == $r['action_id']) ? true : false;
                    if(isset($access['groups'][$r['group_id']]['actions'][$r['acl_action_id']]))
                    {
                        $access['groups'][$r['group_id']]['isAssoc'] = true;
                        $access['groups'][$r['group_id']]['actions'][$r['acl_action_id']]['c'] += ($overrideAction) ? (($r['acl_c']==-2 || $r['c']==-2) ? -2 : max($r['acl_c'], $r['c'])) : $r['acl_c'];
                        $access['groups'][$r['group_id']]['actions'][$r['acl_action_id']]['r'] += ($overrideAction) ? (($r['acl_r']==-2 || $r['r']==-2) ? -2 : max($r['acl_r'], $r['r'])) : $r['acl_r'];
                        $access['groups'][$r['group_id']]['actions'][$r['acl_action_id']]['u'] += ($overrideAction) ? (($r['acl_u']==-2 || $r['u']==-2) ? -2 : max($r['acl_u'], $r['u'])) : $r['acl_u'];
                        $access['groups'][$r['group_id']]['actions'][$r['acl_action_id']]['d'] += ($overrideAction) ? (($r['acl_d']==-2 || $r['d']==-2) ? -2 : max($r['acl_d'], $r['d'])) : $r['acl_d'];
                    }
                    else
                    {
                        $access['groups'][$r['group_id']]['gname'] = $r['gname'];
                        $access['groups'][$r['group_id']]['isAssoc'] = true;
                        $access['groups'][$r['group_id']]['isACL'] = false;
                        $access['groups'][$r['group_id']]['actions'][$r['acl_action_id']]['aname'] = $r['aname'];
                        $access['groups'][$r['group_id']]['actions'][$r['acl_action_id']]['c'] = ($overrideAction) ? (($r['acl_c']==-2 || $r['c']==-2) ? -2 : max($r['acl_c'], $r['c'])) : $r['acl_c'];
                        $access['groups'][$r['group_id']]['actions'][$r['acl_action_id']]['r'] = ($overrideAction) ? (($r['acl_r']==-2 || $r['r']==-2) ? -2 : max($r['acl_r'], $r['r'])) : $r['acl_r'];
                        $access['groups'][$r['group_id']]['actions'][$r['acl_action_id']]['u'] = ($overrideAction) ? (($r['acl_u']==-2 || $r['u']==-2) ? -2 : max($r['acl_u'], $r['u'])) : $r['acl_u'];
                        $access['groups'][$r['group_id']]['actions'][$r['acl_action_id']]['d'] = ($overrideAction) ? (($r['acl_d']==-2 || $r['d']==-2) ? -2 : max($r['acl_d'], $r['d'])) : $r['acl_d'];
                    }
                }

                $query = "SELECT jeua.*, ju.name as uname, jesa.label as aname
                        FROM #__emundus_users_assoc as jeua
                        LEFT JOIN #__users as ju on ju.id = jeua.user_id
                        LEFT JOIN   #__emundus_setup_actions as jesa on jesa.id = jeua.action_id
                        where  jeua.fnum like '".$fnum."' and jesa.status = 1
                        ORDER BY jeua.fnum, jeua.user_id, jeua.action_id";
                $db->setQuery($query);
                $res = $db->loadAssocList();
                foreach($res as $r)
                {
                    if(isset($access['groups'][$r['user_id']]['actions'][$r['action_id']])) {
                        $access['users'][$r['user_id']]['actions'][$r['action_id']]['c'] += $r['c'];
                        $access['users'][$r['user_id']]['actions'][$r['action_id']]['r'] += $r['r'];
                        $access['users'][$r['user_id']]['actions'][$r['action_id']]['u'] += $r['u'];
                        $access['users'][$r['user_id']]['actions'][$r['action_id']]['d'] += $r['d'];
                    } else {
                        $access['users'][$r['user_id']]['uname'] = $r['uname'];
                        $access['users'][$r['user_id']]['actions'][$r['action_id']]['aname'] = $r['aname'];
                        $access['users'][$r['user_id']]['actions'][$r['action_id']]['c'] = $r['c'];
                        $access['users'][$r['user_id']]['actions'][$r['action_id']]['r'] = $r['r'];
                        $access['users'][$r['user_id']]['actions'][$r['action_id']]['u'] = $r['u'];
                        $access['users'][$r['user_id']]['actions'][$r['action_id']]['d'] = $r['d'];
                    }
                }
            }
            catch(Exception $e)
            {
                error_log($e->getMessage(), 0);
            }
        }

        return $access;
    }

    public function getActions()
    {
        $dbo = $this->getDbo();
        try
        {
            $query = 'select * from #__emundus_setup_actions ';
            $dbo->setQuery($query);
            return $dbo->loadAssocList('id');
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    public function checkGroupAssoc($fnum, $gid, $aid = null)
    {
        $dbo = $this->getDbo();
        try
        {
            if(!is_null($aid))
            {
                $query = "select * from #__emundus_group_assoc where `action_id` = $aid and  `group_id` = $gid and `fnum` like ".$dbo->quote($fnum);
            }
            else
            {
                $query = "select * from #__emundus_group_assoc where `group_id` = $gid and `fnum` like ".$dbo->quote($fnum);
            }
            $dbo->setQuery($query);
            return $dbo->loadObject();
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    public function updateGroupAccess($fnum, $gid, $actionId, $crud, $value)
    {
        $dbo = $this->getDbo();
        try
        {
            if($this->checkGroupAssoc($fnum, $gid) !== null)
            {
                if($this->checkGroupAssoc($fnum, $gid, $actionId) !== null)
                {
                    $query = "update #__emundus_group_assoc set ".$dbo->quoteName($crud)." = ".$value.
                        " where `group_id` = $gid and `action_id` = $actionId and `fnum` like ".$dbo->quote($fnum);
                    $dbo->setQuery($query);
                    return $dbo->execute();
                }
                else
                {
                    return $this->_addGroupAssoc($fnum, $crud, $actionId, $gid, $value);
                }
            }
            else
            {
                return $this->_addGroupAssoc($fnum, $crud, $actionId, $gid, $value);
            }
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    private function _addGroupAssoc($fnum, $crud, $aid, $gid, $value)
    {
        $dbo = $this->getDbo();
        $actionQuery = "select c, r, u, d from #__emundus_acl where action_id = $aid  and  group_id = $gid";
        $dbo->setQuery($actionQuery);
        $actions = $dbo->loadAssoc();
        $actions[$crud] = $value;
        $query = "INSERT INTO `#__emundus_group_assoc`(`group_id`, `action_id`, `fnum`, `c`, `r`, `u`, `d`) VALUES ($gid, $aid, ".$dbo->quote($fnum).",{$actions['c']}, {$actions['r']}, {$actions['u']}, {$actions['d']})";
        $dbo->setQuery($query);
        return $dbo->execute();
    }

    public function checkUserAssoc($fnum, $uid, $aid = null)
    {
        $dbo = $this->getDbo();
        try
        {
            if(!is_null($aid))
            {
                $query = "select * from #__emundus_users_assoc where `action_id` = $aid and  `user_id` = $uid and `fnum` like ".$dbo->quote($fnum);
            }
            else
            {
                $query = "select * from #__emundus_users_assoc where `user_id` = $uid and `fnum` like ".$dbo->quote($fnum);
            }
            $dbo->setQuery($query);
            return $dbo->loadObject();
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    private function _addUserAssoc($fnum, $crud, $aid, $uid, $value)
    {
        $dbo = $this->getDbo();
        $actionQuery = "select jea.c, jea.r, jea.u, jea.d from #__emundus_acl as jea left join #__emundus_groups as jeg on jeg.group_id = jea.group_id
        where jea.action_id = {$aid}  and jeg.user_id  = {$uid}";
        $dbo->setQuery($actionQuery);
        $actions = $dbo->loadAssoc();
        $actionQuery = "select jega.c, jega.r, jega.u, jega.d from #__emundus_group_assoc as jega left join #__emundus_groups as jeg on jeg.group_id = jega.group_id
        where jega.action_id = {$aid} and jeg.user_id  = {$uid} and jega.fnum like {$dbo->quote($fnum)}";
        $dbo->setQuery($actionQuery);
        $actionAssoc = $dbo->loadAssoc();
        if(!empty($actionAssoc))
        {
            $actions['c'] += $actionAssoc['c'];
            $actions['r'] += $actionAssoc['r'];
            $actions['u'] += $actionAssoc['u'];
            $actions['d'] += $actionAssoc['d'];
        }
        $actions[$crud] = $value;
        $query = "INSERT INTO `#__emundus_group_assoc`(`user_id`, `action_id`, `fnum`, `c`, `r`, `u`, `d`) VALUES ($uid, $aid, ".$dbo->quote($fnum).",{$actions['c']}, {$actions['r']}, {$actions['u']}, {$actions['d']})";
        $dbo->setQuery($query);
        return $dbo->execute();
    }

    public function updateUserAccess($fnum, $uid, $actionId, $crud, $value)
    {
        $dbo = $this->getDbo();
        try
        {
            if($this->checkUserAssoc($fnum, $uid) !== null)
            {
                if($this->checkUserAssoc($fnum, $uid, $actionId) !== null)
                {
                    $query = "update #__emundus_users_assoc set ".$dbo->quoteName($crud)." = ".$value.
                        " where `user_id` = $uid and `action_id` = $actionId and `fnum` like ".$dbo->quote($fnum);
                    $dbo->setQuery($query);
                    return $dbo->execute();
                }
                else
                {
                    return $this->_addUserAssoc($fnum, $crud, $actionId, $uid, $value);
                }
            }
            else
            {
                return $this->_addUserAssoc($fnum, $crud, $actionId, $uid, $value);
            }
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    /**
     * @param $fnum string
     * @param $gid int
     * @param $current_user int If null, the current user will be used
     * @return false|mixed
     */
    public function deleteGroupAccess($fnum, $gid, $current_user = null)
    {
        $deleted = false;

        if (!empty($fnum) && !empty($gid)) {
            if (empty($current_user)) {
                $current_user = JFactory::getUser()->id;
            }

            $dbo = $this->getDbo();
            $query = $dbo->getQuery(true);

            $query->delete('#__emundus_group_assoc')
                ->where($dbo->quoteName('group_id') . ' = ' . $gid)
                ->andWhere($dbo->quoteName('fnum') . ' = ' . $dbo->quote($fnum));

            try {
                $dbo->setQuery($query);
                $deleted = $dbo->execute();
            } catch (Exception $e) {
                JLog::add('Error in model/application at query: ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
            }

            if ($deleted) {
                $query->clear()
                    ->select('label')
                    ->from('#__emundus_setup_groups')
                    ->where('id = ' . $gid);

                $dbo->setQuery($query);
                $label = $dbo->loadResult();

                $logsParams = ['deleted' => ['details' => $label]];
                EmundusModelLogs::log($current_user, '', $fnum, 11, 'd', 'COM_EMUNDUS_ACCESS_ACCESS_FILE', json_encode($logsParams, JSON_UNESCAPED_UNICODE));
            }
        }

        return $deleted;
    }

    /**
     * @param $fnum string
     * @param $uid int
     * @param $current_user int if null, the current user will be used
     * @return false|mixed
     */
    public function deleteUserAccess($fnum, $uid, $current_user = null)
    {
        $deleted = false;

        if (!empty($fnum) && !empty($uid)) {
            if (empty($current_user)) {
                $current_user = JFactory::getUser()->id;
            }

            $dbo = $this->getDbo();
            $query = $dbo->getQuery(true);

            $query->delete('#__emundus_users_assoc')
                ->where($dbo->quoteName('user_id') . ' = ' . $uid)
                ->andWhere($dbo->quoteName('fnum') . ' = ' . $dbo->quote($fnum));

            try {
                $dbo->setQuery($query);
                $deleted = $dbo->execute();
            } catch (Exception $e) {
                JLog::add('Error in model/application at query: ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
            }

            if ($deleted) {
                $query->clear()
                    ->select('name')
                    ->from('#__users')
                    ->where('id = ' . $uid);

                $dbo->setQuery($query);
                $user_name = $dbo->loadResult();

                $logsParams = ['deleted' => ['details' => $user_name]];
                EmundusModelLogs::log($current_user, '', $fnum, 11, 'd', 'COM_EMUNDUS_ACCESS_ACCESS_FILE', json_encode($logsParams, JSON_UNESCAPED_UNICODE));
            }
        }

        return $deleted;
    }

    public function getApplications($uid)
    {
        $db = $this->getDbo();
        try
        {
            $query = 'SELECT ecc.*, esc.*, ess.step, ess.value, ess.class
                        FROM #__emundus_campaign_candidature AS ecc
                        LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id=ecc.campaign_id
                        LEFT JOIN #__emundus_setup_status AS ess ON ess.step=ecc.status
                        WHERE ecc.applicant_id ='.$uid.'
                        ORDER BY esc.end_date DESC';
            $db->setQuery($query);
            $result = $db->loadObjectList('fnum');
            return (array) $result;
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    public function getApplication($fnum)
    {
        $dbo = $this->getDbo();
        try
        {
            $query = 'SELECT ecc.*, esc.*, ess.step, ess.value, ess.class, esp.id as prog_id, esp.color as tag_color, esp.label as prog_label
                        FROM #__emundus_campaign_candidature AS ecc
                        LEFT JOIN #__emundus_setup_campaigns AS esc ON esc.id=ecc.campaign_id
                        LEFT JOIN #__emundus_setup_status AS ess ON ess.step=ecc.status
                        LEFT JOIN #__emundus_setup_programmes as esp on esc.training = esp.code
                        WHERE ecc.fnum like '.$dbo->Quote($fnum).'
                        ORDER BY esc.end_date DESC';
            $dbo->setQuery($query);
            $result = $dbo->loadObject();
            return $result;
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    /**
     * Return the order for current fnum. If an order with confirmed status is found for fnum campaign period, then return the order
     * If $sent is sent to true, the function will search for orders with a status of 'created' and offline paiement methode
     * @param $fnumInfos $sent
     * @param bool $cancelled
     * @return bool|object
     */
    public function getHikashopOrder($fnumInfos, $cancelled = false, $confirmed = true) {
        $eMConfig = JComponentHelper::getParams('com_emundus');

        require_once(JPATH_SITE.'/components/com_emundus/models/campaign.php');
        $m_campaign = new EmundusModelCampaign;

        $prog_id = $m_campaign->getProgrammeByTraining($fnumInfos['training'])->id;

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        /* First determine the program the user is applying to is in the emundus_hikashop_programs */
        $query
            ->select('hp.id')
            ->from($db->quoteName('#__emundus_hikashop_programs', 'hp'))
            ->leftJoin($db->quoteName('jos_emundus_hikashop_programs_repeat_id_prog','hpr').' ON '.$db->quoteName('hpr.parent_id').' = '.$db->quoteName('hp.id'))
            ->where($db->quoteName('hpr.id_prog') . ' = ' .$db->quote($prog_id));
        $db->setQuery($query);
        $rule = $db->loadResult();

        /* If we find a row, we use the emundus_hikashop_programs, otherwise we use the eMundus config */
        $em_application_payment = isset($rule) ? 'programmes' : $eMConfig->get('application_payment', 'user');

        $order_status = array();
        if ($cancelled) {
            array_push($order_status, 'cancelled');
        } else {
            if($confirmed) {
                array_push($order_status, 'confirmed');
            }
            switch ($eMConfig->get('accept_other_payments', 0)) {
                case 1:
                    array_push($order_status, 'created');
                    break;
                case 3:
                    array_push($order_status, 'pending');
                    break;
                case 4:
                    array_push($order_status, 'created', 'pending');
                    break;
                default:
                    // No need to push to the array
                    break;

            }
        }

        $query
            ->clear()
            ->select(['ho.*', $db->quoteName('eh.user', 'user_cms_id')])
            ->from($db->quoteName('#__emundus_hikashop', 'eh'))
            ->leftJoin($db->quoteName('#__hikashop_order','ho').' ON '.$db->quoteName('ho.order_id').' = '.$db->quoteName('eh.order_id'))
            ->where($db->quoteName('ho.order_status') . ' IN (' . implode(", ", $db->quote($order_status)) . ')')
            ->order($db->quoteName('order_created') . ' DESC');

        switch ($em_application_payment) {

            default :
            case 'fnum' :
                $query
                    ->where($db->quoteName('eh.fnum') . ' LIKE ' . $db->quote($fnumInfos['fnum']));
                break;

            case 'user' :
                $query
                    ->where($db->quoteName('eh.user') . ' = ' . $db->quote($fnumInfos['applicant_id']));
                break;

            case 'campaign' :
                $query
                    ->where($db->quoteName('eh.campaign_id') . ' = ' . $db->quote($fnumInfos['id']))
                    ->where($db->quoteName('eh.user') . ' = ' . $db->quote($fnumInfos['applicant_id']));
                break;

            case 'status' :
                $em_application_payment_status = $eMConfig->get('application_payment_status', '0');
                $payment_status = explode(',', $em_application_payment_status);

                if (in_array($fnumInfos['status'], $payment_status)) {
                    $query
                        ->where($db->quoteName('eh.status') . ' = ' . $db->quote($fnumInfos['status']))
                        ->where($db->quoteName('eh.fnum') . ' LIKE ' . $db->quote($fnumInfos['fnum']));
                } else{
                    $query
                        ->where($db->quoteName('eh.fnum') . ' LIKE ' . $db->quote($fnumInfos['fnum']));
                }
                break;

            case 'programmes' :
                /* By using the parent_id from the emundus_hikashop_programs table, we can get the list of the other programs that use the same settings */
                /* We check only those with a payment_type of 2, for the others it's one payment by file */
                $hika_query = $db->getQuery(true);
                $hika_query->select('hpr.id_prog')
                    ->from($db->quoteName('#__emundus_hikashop_programs_repeat_id_prog', 'hpr'))
                    ->leftJoin($db->quoteName('#__emundus_hikashop_programs','hp').' ON '.$db->quoteName('hpr.parent_id').' = '.$db->quoteName('hp.id'))
                    ->where($db->quoteName('hpr.parent_id').' = '.$db->quote($rule))
                    ->andWhere($db->quoteName('hp.payment_type').' = '.$db->quote(2));
                $db->setQuery($hika_query);
                $progs_to_check = $db->loadColumn();

                /* If there are programs, we must check if there was a payment on one of the campaigns this year */
                if (!empty($progs_to_check)) {
                    $fnum_query = $db->getQuery(true);
                    /* Get the list of the candiate's files that are in the list of programs in the year*/
                    $fnum_query->select('cc.fnum')
                        ->from($db->quoteName('#__emundus_campaign_candidature', 'cc'))
                        ->leftJoin($db->quoteName('#__emundus_setup_campaigns','sc').' ON '.$db->quoteName('sc.id').' = '.$db->quoteName('cc.campaign_id'))
                        ->leftJoin($db->quoteName('#__emundus_setup_programmes','sp').' ON '.$db->quoteName('sc.training').' = '.$db->quoteName('sp.code'))
                        ->where($db->quoteName('sp.id').' IN ('.implode(',',$db->quote($progs_to_check)) . ')')
                        ->andWhere($db->quoteName('sc.year').' = ' .$db->quote($fnumInfos['year']))
                        ->andWhere($db->quoteName('cc.applicant_id').' = ' .$db->quote($fnumInfos['applicant_id']));
                    $db->setQuery($fnum_query);
                    $program_year_fnum = $db->loadColumn();
                }

                /* If we find another file in the list of programs during the same year, we can determine that he's already paid*/
                if(!empty($program_year_fnum)) {
                    $query
                        ->where($db->quoteName('eh.fnum') . ' IN (' . implode(',', $program_year_fnum) . ')');
                } else {
                    $query
                        ->where($db->quoteName('eh.fnum') . ' LIKE ' . $db->quote($fnumInfos['fnum']));
                }
                break;
        }
        try {
            $db->setQuery($query);
            return $db->loadObject();
        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function getHikashopCartOrder($fnumInfos, $cancelled = false, $confirmed = true) {
        $eMConfig = JComponentHelper::getParams('com_emundus');

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $em_application_payment = $eMConfig->get('application_payment', 'user');

        $query
            ->select('eh.user,eh.cart_id')
            ->from($db->quoteName('#__emundus_hikashop', 'eh'))
            ->where($db->quoteName('eh.order_id') .' = 0' . ' OR ' . $db->quoteName('eh.order_id') .' IS NULL');

        switch ($em_application_payment) {
            default :
            case 'fnum' :
                $query
                    ->andWhere($db->quoteName('eh.fnum') . ' LIKE ' . $db->quote($fnumInfos['fnum']));
                break;

            case 'user' :
                $query
                    ->andWhere($db->quoteName('eh.user') . ' = ' . $db->quote($fnumInfos['applicant_id']));
                break;

            case 'campaign' :
                $query
                    ->andWhere($db->quoteName('eh.campaign_id') . ' = ' . $db->quote($fnumInfos['id']))
                    ->andWhere($db->quoteName('eh.user') . ' = ' . $db->quote($fnumInfos['applicant_id']));
                break;

            case 'status' :
                $em_application_payment_status = $eMConfig->get('application_payment_status', '0');
                $payment_status = explode(',', $em_application_payment_status);

                if (in_array($fnumInfos['status'], $payment_status)) {
                    $query
                        ->andWhere($db->quoteName('eh.status') . ' = ' . $db->quote($fnumInfos['status']))
                        ->andWhere($db->quoteName('eh.fnum') . ' LIKE ' . $db->quote($fnumInfos['fnum']));
                } else{
                    $query
                        ->andWhere($db->quoteName('eh.fnum') . ' LIKE ' . $db->quote($fnumInfos['fnum']));
                }
                break;
        }

        try {
            $db->setQuery($query);
            $cart_pending = $db->loadObject();

            if(!empty($cart_pending)){
                return null;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }

        $order_status = array();
        if ($cancelled) {
            array_push($order_status, 'cancelled');
        } else {
            if($confirmed) {
                array_push($order_status, 'confirmed');
            }
            switch ($eMConfig->get('accept_other_payments', 0)) {
                case 1:
                    array_push($order_status, 'created');
                    break;
                case 3:
                    array_push($order_status, 'pending');
                    break;
                case 4:
                    array_push($order_status, 'created', 'pending');
                    break;
                default:
                    // No need to push to the array
                    break;

            }
        }

        $query->clear()
            ->select(['ho.*', $db->quoteName('eh.user', 'user_cms_id')])
            ->from($db->quoteName('#__emundus_hikashop', 'eh'))
            ->leftJoin($db->quoteName('#__hikashop_order','ho').' ON '.$db->quoteName('ho.order_id').' = '.$db->quoteName('eh.order_id'))
            ->where($db->quoteName('ho.order_status') . ' IN (' . implode(", ", $db->quote($order_status)) . ')')
            ->order($db->quoteName('order_created') . ' DESC');

        switch ($em_application_payment) {

            default :
            case 'fnum' :
                $query
                    ->where($db->quoteName('eh.fnum') . ' LIKE ' . $db->quote($fnumInfos['fnum']));
                break;

            case 'user' :
                $query
                    ->where($db->quoteName('eh.user') . ' = ' . $fnumInfos['applicant_id']);
                break;

            case 'campaign' :
                $query
                    ->where($db->quoteName('eh.campaign_id') . ' = ' . $fnumInfos['id'])
                    ->where($db->quoteName('eh.user') . ' = ' . $fnumInfos['applicant_id']);
                break;

            case 'status' :
                $em_application_payment_status = $eMConfig->get('application_payment_status', '0');
                $payment_status = explode(',', $em_application_payment_status);

                if (in_array($fnumInfos['status'], $payment_status)) {
                    $query
                        ->where($db->quoteName('eh.status') . ' = ' . $fnumInfos['status'])
                        ->where($db->quoteName('eh.fnum') . ' LIKE ' . $db->quote($fnumInfos['fnum']));
                } else{
                    $query
                        ->where($db->quoteName('eh.fnum') . ' LIKE ' . $db->quote($fnumInfos['fnum']));
                }
                break;
        }
        try {
            $db->setQuery($query);
            return $db->loadObject();
        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function getHikashopCart($fnumInfos){
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query
            ->select('cart_id')
            ->from($db->quoteName('#__emundus_hikashop'))
            ->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnumInfos['fnum']));

        try {
            $db->setQuery($query);
            return $db->loadResult();
        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * Return the checkout URL order for current fnum.
     * @param $pid   string|int   the applicant's profile_id
     * @return bool|string
     */
    public function getHikashopCheckoutUrl($pid)
    {
        $dbo = $this->getDbo();
        try
        {
            $query = 'SELECT CONCAT(link, "&Itemid=", id) as url
                        FROM #__menu
                        WHERE alias like "checkout'.$pid.'" and published = 1';
            $dbo->setQuery($query);
            $url = $dbo->loadResult();

            if (empty($url)) {
                $query = 'SELECT CONCAT(m.link, "&Itemid=", m.id) as url
                    FROM #__menu m
                    LEFT JOIN #__emundus_setup_profiles esp on esp.menutype = m.menutype
                    WHERE m.alias like "checkout%" and m.published = 1 and esp.id = '.$pid;
                $dbo->setQuery($query);
                $url = $dbo->loadResult();
            }
            return $url;
        }
        catch (Exception $e)
        {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * Return the checkout URL order for current fnum.
     * @param $pid   string|int   the applicant's profile_id
     * @return bool|string
     */
    public function getHikashopCartUrl($pid)
    {
        $dbo = $this->getDbo();
        try
        {
            $query = 'SELECT CONCAT(link, "&Itemid=", id) as url
                        FROM #__menu
                        WHERE alias like "cart'.$pid.'"';
            $dbo->setQuery($query);
            $url = $dbo->loadResult();
            return $url;
        }
        catch (Exception $e)
        {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }


    /**
     * Move an application file from one programme to another
     *
     * @param      $fnum_from String the fnum of the source
     * @param      $fnum_to   String the fnum of the moved application
     * @param      $campaign  String the programme id to move the file to
     * @param null $status
     *
     * @return bool
     */
    public function moveApplication(string $fnum_from, string $fnum_to, $campaign, $status = null, $params = array()): bool {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        try {

            $query->clear()
                ->select('*')
                ->from($db->quoteName('#__emundus_campaign_candidature'))
                ->where($db->quoteName('fnum').' LIKE '.$db->quote($fnum_from));
            $db->setQuery($query);
            $cc_line = $db->loadAssoc();

            if (!empty($cc_line)) {

                $fields = [
                    $db->quoteName('fnum').' = '.$db->quote($fnum_to),
                    $db->quoteName('campaign_id').' = '.$db->quote($campaign),
                    $db->quoteName('copied').' = 2'
                ];

                if (!empty($status)) {
                    $fields[] = $db->quoteName('status').' = '.$db->quote($status);
                }

                $query->clear()
                    ->update($db->quoteName('#__emundus_campaign_candidature'))
                    ->set($fields)
                    ->where($db->quoteName('id').' = '.$db->quote($cc_line['id']));

                $db->setQuery($query);
                $db->execute();

                JPluginHelper::importPlugin('emundus');
                $dispatcher = JDispatcher::getInstance();
                $dispatcher->trigger('callEventHandler', array(
                        'onAfterMoveApplication',
                        array(
                            'fnum_from' => $fnum_from,
                            'fnum_to' => $fnum_to,
                            'campaign_id' => $campaign,
                            'params' => $params)
                    )
                );

                return true;

            } else {
                return false;
            }

        } catch (Exception $e) {

            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * Duplicate an application file (form data)
     * @param $fnum_from String the fnum of the source
     * @param $fnum_to String the fnum of the duplicated application
     * @param $pid Int the profile_id to get list of forms
     * @return bool
     */
    public function copyApplication($fnum_from, $fnum_to, $pid = null, $copy_attachment = 0, $campaign_id = null, $copy_tag = 0, $move_hikashop_command = 0, $delete_from_file = 0, $params = array(), $copyUsersAssoc = 0, $copyGroupsAssoc = 0) {
        $db = JFactory::getDbo();
        $pids = [];

        try {
            $divergent_users = false;
            $m_profiles = new EmundusModelProfile();
            $fnumInfos = $m_profiles->getFnumDetails($fnum_from);
            $fnumToInfos =  $m_profiles->getFnumDetails($fnum_to);

            if ($fnumInfos['applicant_id'] !== $fnumToInfos['applicant_id']) {
                $divergent_users = true;
            }

            if(!empty($campaign_id)){
                $pids = $m_profiles->getProfilesIDByCampaign((array)$campaign_id);
            }

            if (empty($pid) && empty($campaign_id)) {
                $pids[] = (isset($fnumInfos['profile_id_form']) && !empty($fnumInfos['profile_id_form']))?$fnumInfos['profile_id_form']:$fnumInfos['profile_id'];
            } elseif (!empty($pid)){
                $pids[] = $pid;
            }

            $forms = array();
            foreach ($pids as $profile){
                $menus = @EmundusHelperMenu::buildMenuQuery($profile);
                foreach ($menus as $menu){
                    $forms[] = $menu;
                }
            }

            $tempArray = array_unique(array_column($forms, 'db_table_name'));
            $forms = array_values(array_intersect_key($forms, $tempArray));

            foreach ($forms as $form) {
                $query = 'SELECT * FROM '.$form->db_table_name.' WHERE fnum like '.$db->Quote($fnum_from);
                $db->setQuery($query);
                $stored = $db->loadAssoc();

                if (!empty($stored)) {
                    // update form data
                    $parent_id = $stored['id'];
                    unset($stored['id']);
                    $stored['fnum'] = $fnum_to;
                    $q=1;

                    if ($divergent_users) {
                        foreach($stored as $key => $value) {
                            if ($key === 'user' && $value == $fnumInfos['applicant_id']) {
                                $stored[$key] = $fnumToInfos['applicant_id'];
                            }
                        }
                    }

                    $query = 'INSERT INTO '.$form->db_table_name.' (`'.implode('`,`', array_keys($stored)).'`) VALUES('.implode(',', $db->Quote($stored)).')';
                    $db->setQuery($query);
                    $db->execute();
                    $id = $db->insertid();

                    // liste des groupes pour le formulaire d'une table
                    $query = 'SELECT ff.id, ff.group_id, fe.name, fg.id, fg.label, (IF( ISNULL(fj.table_join), fl.db_table_name, fj.table_join)) as `table`, fg.params as `gparams`
                                FROM #__fabrik_formgroup ff
                                LEFT JOIN #__fabrik_lists fl ON fl.form_id=ff.form_id
                                LEFT JOIN #__fabrik_groups fg ON fg.id=ff.group_id
                                LEFT JOIN #__fabrik_elements fe ON fe.group_id=fg.id
                                LEFT JOIN #__fabrik_joins AS fj ON (fj.group_id = fe.group_id AND fj.list_id != 0 AND fj.element_id = 0)
                                WHERE ff.form_id = "'.$form->form_id.'"
                                AND fe.published = 1
                                ORDER BY ff.ordering';
                    $q=2;
                    $db->setQuery($query);
                    $groups = $db->loadObjectList();

                    // get data and update current form
                    $data = array();
                    if (count($groups) > 0) {
                        foreach ($groups as $group) {
                            $group_params = json_decode($group->gparams);
                            if (@$group_params->repeat_group_button == 1) {
                                $data[$group->group_id]['repeat_group'] = $group_params->repeat_group_button;
                                $data[$group->group_id]['group_id'] = $group->group_id;
                                $data[$group->group_id]['element_name'][] = $group->name;
                                $data[$group->group_id]['table'] = $group->table;
                            }
                        }

                        if (count($data) > 0) {
                            foreach ($data as $d) {
                                $q=3;
                                $query = 'SELECT '.implode(',', $db->quoteName($d['element_name'])).' FROM '.$d['table'].' WHERE parent_id='.$parent_id;
                                $db->setQuery($query);
                                $stored = $db->loadAssocList();

                                if (count($stored) > 0) {
                                    $arrayValue = [];

                                    foreach($stored as $rowvalues) {
                                        unset($rowvalues['id']);
                                        $rowvalues['parent_id'] = $id;
                                        $arrayValue[] = '('.implode(',', $db->quote($rowvalues)).')';
                                        $keyValue[] = $rowvalues;
                                    }
                                    unset($stored[0]['id']);
                                    $q=4;

                                    // update form data
                                    $query = 'INSERT INTO '.$d['table'].' (`'.implode('`,`', array_keys($stored[0])).'`)'.' VALUES '.implode(',', $arrayValue);
                                    $db->setQuery($query);
                                    $db->execute();
                                }
                            }
                        }
                    }
                }
            }

            // sync documents uploaded
            // 1. get list of uploaded documents for previous file defined as duplicated
            if ($copy_attachment) {
                $query = $db->getQuery(true);

                $query->select('jeu.*, jsa.lbl')
                    ->from('#__emundus_uploads AS jeu')
                    ->leftJoin('#__emundus_setup_attachments AS jsa ON jsa.id=jeu.attachment_id')
                    ->where('jeu.fnum LIKE '. $db->quote($fnum_from));

                $db->setQuery($query);

                $documents = [];
                try {
                    $documents = $db->loadAssocList();
                } catch(Exception $e) {
                    JLog::add('Error getting documents for fnum '.$fnum_from.' in emundus model application at query '.$query, JLog::ERROR, 'com_emundus');
                }

                if (!empty($documents)) {
                    foreach ($documents as $document) {
                        $file_ext = pathinfo($document['filename'], PATHINFO_EXTENSION);
                        $new_file = $fnumToInfos['applicant_id'] . '-' . $campaign_id . '-' . trim($document['lbl'], ' _') . '-' . rand() . '.' . $file_ext;

                        // try to copy file with new name
                        $copied = copy(JPATH_SITE . DS . "images/emundus/files" . DS .  $fnumInfos['applicant_id'] . DS . $document['filename'], JPATH_SITE . DS . "images/emundus/files" . DS .  $fnumToInfos['applicant_id'] . DS . $new_file);
                        if (!$copied) {
                            JLog::add("La copie " . $document['file'] . " du fichier a échoué...\n", JLog::ERROR, 'com_emundus');
                        }

                        $document['user_id'] = $fnumToInfos['applicant_id'];
                        $document['filename'] = $new_file;
                        $document['fnum'] = $fnum_to;
                        $document['is_validated'] = empty($document['is_validated']) ? '-2' : $document['is_validated'];
                        $document['modified_by'] = empty($document['modified_by']) ? $document['user_id'] : $document['modified_by'];
                        unset($document['id']);
                        unset($document['lbl']);

                        try {
                            $query->clear();
                            $query->insert('jos_emundus_uploads')
                                ->columns(array_keys($document))
                                ->values(implode(", ", $db->quote($document)));

                            $db->setQuery($query);
                            $db->execute();

                        } catch(Exception $e) {
                            JLog::add('Error inserting document in jos_emundus_uploads table for fnum '.$fnum_to.' : '.$e, JLog::ERROR, 'com_emundus');
                        }
                    }
                }
            }

            if ($copy_tag) {
                $query = $db->getQuery(true);
                $query->select('*')
                    ->from($db->quoteName('#__emundus_tag_assoc'))
                    ->where($db->quoteName('fnum') . ' = ' . $db->quote($fnum_from));
                $db->setQuery($query);
                $tags_assoc_rows = $db->loadAssocList();
                if (count($tags_assoc_rows) > 0) {
                    foreach ($tags_assoc_rows as $key => $row) {
                        $query->clear()
                            ->insert($db->quoteName('#__emundus_tag_assoc'))
                            ->set($db->quoteName('id_tag') . ' = ' . $row['id_tag'])
                            ->set($db->quoteName('user_id') . ' = ' . $row['user_id'])
                            ->set($db->quoteName('fnum') . ' = ' . $db->quote($fnum_to))
                            ->set($db->quoteName('date_time') . ' = ' . $db->quote($row['date_time']));
                        $db->setQuery($query);
                        $db->execute();

                    }
                }
            }

            if($move_hikashop_command) {
                $query = $db->getQuery(true);
                $query->update($db->quoteName('#__emundus_hikashop'))
                    ->set($db->quoteName('fnum') . ' = ' . $db->quote($fnum_to))
                    ->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum_from));
                $db->setQuery($query);
                $db->execute();
            }

            if($delete_from_file) {
                $query = $db->getQuery(true);
                $query->update($db->quoteName('#__emundus_campaign_candidature'))
                    ->set($db->quoteName('published') . ' = -1')
                    ->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum_from));
                $db->setQuery($query);
                $db->execute();
            }
            if($copyUsersAssoc){
                $this->copyUsersAssoc($fnum_from,$fnum_to);
            }
            if($copyGroupsAssoc){
                $this->copyGroupsAssoc($fnum_from,$fnum_to);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$q.' :: '.$query, JLog::ERROR, 'com_emundus');
            return false;
        }

        JPluginHelper::importPlugin('emundus');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('callEventHandler', array(
            'onAfterCopyApplication',
            array(
                'fnum_from' => $fnum_from,
                'fnum_to' => $fnum_to,
                'pid' => $pid,
                'copy_attachment' => $copy_attachment,
                'campaign_id' => $campaign_id,
                'copy_tag' => $copy_tag,
                'move_hikashop_command' => $move_hikashop_command,
                'delete_from_file' => $delete_from_file,
                'params' => $params)
            )
        );

        return true;
    }

    /**
     * Duplicate all documents (files)
     * @param $fnum_from String the fnum of the source
     * @param $fnum_to String the fnum of the duplicated application
     * @param $pid Int the profile_id to get list of forms
     * @param null $duplicated
     * @return bool
     */
    public function copyDocuments($fnum_from, $fnum_to, $pid = null, $can_delete = null) {
        $db = JFactory::getDbo();

        try {
            if (empty($pid)) {
                $m_profiles = new EmundusModelProfile();

                $fnumInfos = $m_profiles->getFnumDetails($fnum_from);
                $pid = (isset($fnumInfos['profile_id_form']) && !empty($fnumInfos['profile_id_form']))?$fnumInfos['profile_id_form']:$fnumInfos['profile_id'];
            }

            // 1. get list of uploaded documents for previous file defined as duplicated
            $query = 'SELECT eu.*
                        FROM #__emundus_uploads as eu
                        LEFT JOIN #__emundus_setup_attachment_profiles as esap on esap.attachment_id=eu.attachment_id AND esap.profile_id='.$pid.'
                        WHERE eu.fnum like '.$db->Quote($fnum_from);

            if (empty($pid)) {
                $query .= ' AND esap.duplicate=1';
            }

            $db->setQuery($query);
            $stored = $db->loadAssocList();

            if (count($stored) > 0) {
                // 2. copy DB définition and duplicate files in applicant directory
                foreach ($stored as $row) {
                    $src = $row['filename'];
                    $ext = explode('.', $src);
                    $ext = $ext[count($ext)-1];
                    $cpt = 0-(int)(strlen($ext)+1);
                    $dest = substr($row['filename'], 0, $cpt).'-'.$row['id'].'.'.$ext;
                    $row['filename'] = $dest;
                    $row['fnum'] = $fnum_to;
                    $row['can_be_deleted'] = empty($can_delete) ? 0 : 1;
                    unset($row['id']);

                    try {
                        $query = 'INSERT INTO #__emundus_uploads (`'.implode('`,`', array_keys($row)).'`) VALUES('.implode(',', $db->Quote($row)).')';
                        $db->setQuery($query);
                        $db->execute();
                        $id = $db->insertid();
                        $path = EMUNDUS_PATH_ABS.$row['user_id'].DS;
                        if (!copy($path.$src, $path.$dest)) {
                            $query = 'UPDATE #__emundus_uploads SET filename='.$src.' WHERE id='.$id;
                            $db->setQuery($query);
                            $db->execute();
                        }
                    } catch (Exception $e) {
                        $error = JUri::getInstance().' :: USER ID : '.$row['user_id'].' -> '.$e->getMessage();
                        JLog::add($error, JLog::ERROR, 'com_emundus');
                    }
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }

        return true;
    }

    public function copyUsersAssoc($fnum_from, $fnum_to)
    {
        $query = $this->_db->getQuery(true);

        try {
			$query->select('eua.*')
				->from($this->_db->quoteName('#__emundus_users_assoc', 'eua'))
				->where($this->_db->quoteName('eua.fnum') . ' LIKE ' . $this->_db->quote($fnum_from));

	        $this->_db->setQuery($query);
            $users_assoc = $this->_db->loadAssocList();

            if (count($users_assoc) > 0) {
                // 2. copy DB définition and duplicate files in applicant directory
                foreach ($users_assoc as $user) {
                    $user['fnum'] = $fnum_to;
                    unset($user['id']);

                    try {
						$query->clear()
							->insert($this->_db->quoteName('#__emundus_users_assoc'))
							->columns(array_keys($user))
							->values(implode(", ", $this->_db->quote($user)));
	                    $this->_db->setQuery($query);
	                    $this->_db->execute();
                    } catch (Exception $e) {
                        $error = JUri::getInstance().' :: USER ID : '.$user['user_id'].' -> '.$e->getMessage();
                        JLog::add($error, JLog::ERROR, 'com_emundus');
                    }
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }

        return true;
    }

    public function copyGroupsAssoc($fnum_from, $fnum_to)
    {
	    $query = $this->_db->getQuery(true);

        try {
			$query->select('ega.*')
				->from($this->_db->quoteName('#__emundus_group_assoc', 'ega'))
				->where($this->_db->quoteName('ega.fnum') . ' LIKE ' . $this->_db->quote($fnum_from));
	        $this->_db->setQuery($query);
            $groups_assoc = $this->_db->loadAssocList();

            if (count($groups_assoc) > 0) {
                // 2. copy DB définition and duplicate files in applicant directory
                foreach ($groups_assoc as $group) {
                    $group['fnum'] = $fnum_to;
                    unset($group['id']);

                    try {
						$query->clear()
							->insert($this->_db->quoteName('#__emundus_group_assoc'))
							->columns(array_keys($group))
							->values(implode(", ", $this->_db->quote($group)));
	                    $this->_db->setQuery($query);
	                    $this->_db->execute();
                    } catch (Exception $e) {
                        $error = JUri::getInstance().' :: fnum : '.$group['user_id'].' :: group : '.$group['group_id'].' -> '.$e->getMessage();
                        JLog::add($error, JLog::ERROR, 'com_emundus');
                    }
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }

        return true;
    }

    /**
     * Duplicate all documents (files)
     *
     * @param       $fnum             String     the fnum of application file
     * @param       $applicant        Object     the applicant user ID
     * @param array $param
     * @param int   $status
     *
     * @return bool
     */
    public function sendApplication($fnum, $applicant, $param = array(), $status = 1) {
        include_once(JPATH_SITE.'/components/com_emundus/models/emails.php');

        if ($status == '-1') {
            $status = $applicant->status;
        }

        $db = JFactory::getDBO();
        try {
            // Vérification que le dossier à été entièrement complété par le candidat
            $query = 'SELECT id
                        FROM #__emundus_declaration
                        WHERE fnum  like '.$db->Quote($fnum);
            $db->setQuery($query);
            $db->execute();
            $id = $db->loadResult();
            $offset = JFactory::getConfig()->get('offset', 'UTC');
            $dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
            $dateTime = $dateTime->setTimezone(new DateTimeZone($offset));
            $now = $dateTime->format('Y-m-d H:i:s');

            if ($id > 0) {
                $query = 'UPDATE #__emundus_declaration SET time_date='.$db->quote($now). ', user='.$applicant->id.' WHERE id='.$id;
            } else {
                $query = 'INSERT INTO #__emundus_declaration (time_date, user, fnum, type_mail)
                                VALUE ('.$db->quote($now). ', '.$applicant->id.', '.$db->Quote($fnum).', "paid_validation")';
            }

            $db->setQuery($query);
            $db->execute();

            // Insert data in #__emundus_campaign_candidature
            $query = 'UPDATE #__emundus_campaign_candidature SET submitted=1, date_submitted='.$db->quote($now).', status='.$status.' WHERE applicant_id='.$applicant->id.' AND campaign_id='.$applicant->campaign_id. ' AND fnum like '.$db->Quote($applicant->fnum);
            $db->setQuery($query);
            $db->execute();

            // Send emails defined in trigger
            $m_emails = new EmundusModelEmails;
            $code = array($applicant->code);
            $to_applicant = '0,1';
            $m_emails->sendEmailTrigger($status, $code, $to_applicant, $applicant);

        } catch (Exception $e) {
            // catch any database errors.
            JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }

        return true;
    }

    /**
     * Check if iframe can be used
     * @param $url String url to check
     * @return bool
     */
    function allowEmbed($url) {

        $eMConfig = JComponentHelper::getParams('com_emundus');
        $header = $eMConfig->get('headerCheck', '0') == 1 ? @get_headers($url, 1) : true;

        // URL okay?
        if (!$header || stripos($header[0], '200 ok') === false) {
            return false;
        }

        // Check X-Frame-Option
        elseif (isset($header['X-Frame-Options']) && (stripos($header['X-Frame-Options'], 'SAMEORIGIN') !== false || stripos($header['X-Frame-Options'], 'deny') !== false)) {
            return false;
        }

        // Everything passed? Return true!
        return true;
    }

    /**
     * Gets the first page of the application form. Used for opening a file.
     *
     * @param string $redirect
     * @param null   $fnums
     *
     * @return String The URL to the form.
     * @since 3.8.8
     */
    function getFirstPage($redirect = 'index.php', $fnums = null) {

        $user = JFactory::getSession()->get('emundusUser');
        $db = JFactory::getDBo();
        $query = $db->getQuery(true);

        if (!empty($fnums)) {

            $fnums = is_array($fnums) ? implode(',', $fnums) : $fnums;

            $query->select(['CONCAT(m.link,"&Itemid=", m.id) as link', $db->quoteName('cc.fnum')])
                ->from($db->quoteName('#__emundus_campaign_candidature', 'cc'))
                ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'esc').' ON '.$db->quoteName('esc.id').' = '.$db->quoteName('cc.campaign_id'))
                ->leftJoin($db->quoteName('#__emundus_setup_profiles', 'esp').' ON '.$db->quoteName('esp.id').' = '.$db->quoteName('esc.profile_id'))
                ->leftJoin($db->quoteName('#__menu', 'm').' ON '.$db->quoteName('m.menutype').' = '.$db->quoteName('esp.menutype').' AND '.$db->quoteName('m.published').'=1 AND '.$db->quoteName('link').' <> "" AND '.$db->quoteName('link').' <> "#"')
                ->where($db->quoteName('cc.fnum').' IN('.$fnums.')')
                ->order($db->quoteName('m.lft').' DESC');
            $db->setQuery($query);

            try {
                return $db->loadAssocList('fnum');
            } catch (Exception $e) {
                JLog::add('Error getting first page of application at model/application in query : '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
                return $redirect;
            }

        } else {
            if (empty($user->menutype)) {
                return $redirect;
            }

            $query->select('CONCAT(link,"&Itemid=", id) as link')
                ->from($db->quoteName('#__menu'))
                ->where($db->quoteName('published').'=1 AND '.$db->quoteName('menutype').' LIKE '.$db->quote($user->menutype).' AND '.$db->quoteName('link').' <> "" AND '.$db->quoteName('link').' <> "#"')
                ->order($db->quoteName('lft').' ASC');

            try {
                $db->setQuery($query);
	            return $db->loadResult();
            } catch (Exception $e) {
                JLog::add('Error getting first page of application at model/application in query : '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
                return $redirect;
            }
        }
    }

    public function attachment_validation($attachment_id, $state) {
        $dbo = $this->getDbo();
        try {
            $query = 'UPDATE #__emundus_uploads  SET `is_validated` = '.(int) $state.' WHERE `id` = '.(int) $attachment_id;
            $dbo->setQuery($query);
            return $dbo->execute();
        } catch (Exception $e) {
            throw $e;
        }
    }



    /** Gets the URL of the final form in the application in order to submit.
     * @param $fnums
     *
     * @return Mixed
     */
    function getConfirmUrl($fnums = null) {

        $user = JFactory::getSession()->get('emundusUser');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if (!empty($fnums)) {
            $query->select(['CONCAT(m.link,"&Itemid=", m.id) as link', $db->quoteName('cc.fnum')])
                ->from($db->quoteName('#__emundus_campaign_candidature', 'cc'))
                ->leftJoin($db->quoteName('#__emundus_setup_campaigns', 'esc') . ' ON ' . $db->quoteName('esc.id') . ' = ' . $db->quoteName('cc.campaign_id'))
                ->leftJoin($db->quoteName('#__emundus_setup_profiles', 'esp') . ' ON ' . $db->quoteName('esp.id') . ' = ' . $db->quoteName('esc.profile_id'))
                ->leftJoin($db->quoteName('#__menu', 'm') . ' ON ' . $db->quoteName('m.menutype') . ' = ' . $db->quoteName('esp.menutype') . ' AND ' . $db->quoteName('m.published') . '>=0 AND ' . $db->quoteName('m.level') . '=1 AND ' . $db->quoteName('m.link') . ' <> "" AND ' . $db->quoteName('m.link') . ' <> "#"')
                ->where($db->quoteName('cc.fnum') . ' IN(' . implode(',', $fnums) . ')')
                ->order($db->quoteName('m.lft') . ' ASC');

            $db->setQuery($query);
            try {
                return $db->loadAssocList('fnum');
            } catch (Exception $e) {
                JLog::add('Error getting confirm URLs in model/application at query -> ' . preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
                return false;
            }
        } else {

            if (empty($user->menutype)) {
                return false;
            }

            $query->select(['id','link'])
                ->from($db->quoteName('#__menu'))
                ->where($db->quoteName('published').'=1 AND '.$db->quoteName('menutype').' LIKE '.$db->quote($user->menutype).' AND '.$db->quoteName('link').' <> "" AND '.$db->quoteName('link').' <> "#"')
                ->order($db->quoteName('lft') . ' DESC');
            try {
                $db->setQuery($query);

                $res = $db->loadObject();
                return $res->link.'&Itemid='.$res->id;
            } catch (Exception $e) {
                JLog::add('Error getting first page of application at model/application in query : '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
                return false;
            }
        }

    }


    public function searchFilesByKeywords($fnum){
        $db = $this->getDbo();
        $jinput = JFactory::getApplication()->input;
        $search = $jinput->get('search');

        $query = 'SELECT eu.id AS aid, esa.*, eu.attachment_id, eu.filename, eu.description, eu.timedate, eu.can_be_deleted, eu.can_be_viewed, eu.is_validated, esc.label as campaign_label, esc.year, esc.training
            FROM #__emundus_uploads AS eu
            LEFT JOIN #__emundus_setup_attachments AS esa ON  eu.attachment_id=esa.id
            WHERE eu.fnum like '.$this->_db->Quote($fnum).'
            AND $where LIKE '.$search;

        $db->setQuery($query);
        return $db->execute();
    }

    /**
     * @param $elements
     * @param $table
     * @param $parent_table
     * @param $fnum
     *
     * @return bool
     *
     */
    public function checkEmptyRepeatGroups($elements, $table, $parent_table, $fnum) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $subQuery = $db->getQuery(true);

        $eMConfig = JComponentHelper::getParams('com_emundus');
        $show_empty_fields = $eMConfig->get('show_empty_fields', 1);

        $elements = array_map(function($obj) {return 't.'.$obj->name;}, $elements);

        $subQuery
            ->select($db->quoteName('id'))
            ->from($db->quoteName($parent_table))
            ->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum));

        $query
            ->select(implode(',', $elements))
            ->from($db->quoteName($table, 't'))
            ->leftJoin($db->quoteName($parent_table, 'j') . ' ON ' . $db->quoteName('j.id') . ' = '. $db->quoteName('t.parent_id'))
            ->where($db->quoteName('t.parent_id') . " = (" . $subQuery . ")");

        try {
            $db->setQuery($query);
            $db->execute();

            if ($db->getNumRows() >= 1) {
                $res = $db->loadAssoc();

                $elements = array_map(function($arr) {
                    if (is_numeric($arr)) {
                        return (empty(floatval($arr)));
                    } else {
                        if ($arr == "0000-00-00 00:00:00") {
                            return true;
                        }
                        return empty($arr);
                    }
                }, $res);

                $elements = array_filter($elements, function($el) {return $el === false;});
                return !empty($elements);
            } else {
                if($show_empty_fields == 0){
                    return false;
                }
            }

            return true;

        } catch (Exception $e ) {
            JLog::add('Error checking if repeat group is empty at model/application in query : '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    /**
     * @param $elements
     * @param $parent_table
     * @param $fnum
     *
     * @return bool
     *
     */
	public function checkEmptyGroups($elements, $parent_table, $fnum) {
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$eMConfig = JComponentHelper::getParams('com_emundus');
		$show_empty_fields = $eMConfig->get('show_empty_fields', 1);

		$databases_join_params = [];
		$elements_name = array_map(function($obj) use ($db,$parent_table,&$databases_join_params) {
			if($obj->plugin == 'databasejoin'){
				$params = json_decode($obj->params);
				if($params->database_join_display_type == 'checkbox' || $params->database_join_display_type == 'multilist'){
					$databases_join_params[] = $db->quoteName($parent_table.'_repeat_' . $obj->name).' ON '.$db->quoteName($parent_table.'_repeat_' . $obj->name).'.parent_id = t.id';

					return $parent_table.'_repeat_' . $obj->name.'.'.$obj->name;
				}
			}
			return 't.'.$obj->name;
		}, $elements);

		$query->select(implode(',', $db->quoteName($elements_name)))
			->from($db->quoteName($parent_table,'t'));
		if(!empty($databases_join_params)){
			foreach ($databases_join_params as $db_join)
			{
				$query->leftJoin($db_join);
			}
		}
		$query->where($db->quoteName('t.fnum') . ' LIKE ' . $db->quote($fnum));

		try {
			$db->setQuery($query);
			$db->execute();
			if ($db->getNumRows() == 1)
			{
				$res = $db->loadAssoc();

				$at_least_one_visible = false;
				foreach($res as $element_name => $element_value) {
					$current_fb_element = array_filter($elements, function($el) use ($element_name) {return $el->name === $element_name;});
					$current_fb_element = current($current_fb_element);

					if (!empty($current_fb_element)) {
						if ($current_fb_element->plugin === 'yesno' && !is_null($element_value) && $element_value !== '') {
							$at_least_one_visible = true;
						}

						if (is_numeric($element_value)) {
							if (!empty(floatval($element_value))) {
								$at_least_one_visible = true;
							}
						} else if ($element_value !== "0000-00-00 00:00:00" && !empty($element_value)) {
							$at_least_one_visible = true;
						}
					}
				}

				return $at_least_one_visible;
			}
			elseif ($db->getNumRows() > 1)
			{
				return true;
			}
			else
			{
				if($show_empty_fields == 0){
					return false;
				}
			}

			return true;

		} catch (Exception $e ) {
			JLog::add('Error checking if group is empty at model/application in query : '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
			return false;
		}
	}

    /// get count uploaded files
    public function getCountUploadedFile($fnum,$user_id, $profile = null) {
        require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'application.php');
        $m_application = new EmundusModelApplication;

        $html = '';
        $uploads = $m_application->getUserAttachmentsByFnum($fnum,'',$profile);

        $nbuploads = 0;
        foreach ($uploads as $upload) {
            if (strrpos($upload->filename, "application_form") === false) {
                $nbuploads++;
            }
        }
        $titleupload = $nbuploads > 0 ? JText::_('COM_EMUNDUS_ATTACHMENTS_FILES_UPLOADED') : JText::_('COM_EMUNDUS_ATTACHMENTS_ERROR_FILE_UPLOADED');
        $html .= '<h2>' . $titleupload . ' : ' . $nbuploads . '</h2>';

        return $html;
    }

    /// get list uploaded files
    public function getListUploadedFile($fnum, $user_id, $profile = null) {
        require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'application.php');
        $m_application = new EmundusModelApplication;

        $html = '';
        $uploads = $m_application->getUserAttachmentsByFnum($fnum,'',$profile);

        $nbuploads = 0;
        foreach ($uploads as $upload) {
            if (strrpos($upload->filename, "application_form") === false) {
                $nbuploads++;
            }
        }

        $html .= '<ol>';
        foreach ($uploads as $upload) {
            if (strrpos($upload->filename, "application_form") === false) {
                $path_href = JURI::base() . EMUNDUS_PATH_REL . $user_id . '/' . $upload->filename;
                $html .= '<li><b>' . $upload->value . '</b>';
                $html .= '<ul>';
                $html .= '<li><a href="' . $path_href . '" dir="ltr" target="_blank">' . $upload->filename . '</a> (' . strftime("%d/%m/%Y %H:%M", strtotime($upload->timedate)) . ')<br/><b>' . JText::_('COM_EMUNDUS_ATTACHMENTS_DESCRIPTION') . '</b> : ' . $upload->description . '</li>';
                $html .= '</ul>';
                $html .= '</li>';
            }
        }
        $html .= '</ol>';

        return $html;
    }

    /**
     * Update attachment file, description, is_validated values
     *
     * @param object data
     *
     * @return (array) containing status of update and file content update
     */
    public function updateAttachment($data)
    {
        $return = [
            "update" => false
        ];

        if (!empty($data['id']) && !empty($data['user']) && !empty($data['fnum'])) {
            $is_validated   = array(1 => 'VALID', 0 => 'INVALID', 2 => 'COM_EMUNDUS_ATTACHMENTS_WARNING', -2 => 'COM_EMUNDUS_ATTACHMENTS_WAITING');
            $can_be_viewed  = array(1 => 'JYES', 0 => 'JNO');
            $can_be_deleted = array(1 => 'JYES', 0 => 'JNO');

            if (isset($data['file'])) {
                $content    = file_get_contents($data['file']['tmp_name']);
                $attachment = $this->getUploadByID($data['id']);
                $updated    = file_put_contents(EMUNDUS_PATH_REL . $attachment['user_id'] . "/" . $attachment['filename'], $content);
                $return['file_update'] = (bool)$updated;
            }

            $oldData = $this->getUploadByID($data['id']);

            $query = $this->_db->getQuery(true);
            $query->update($this->_db->quoteName('#__emundus_uploads'));

            if (isset($data['description'])) {
                $query->set($this->_db->quoteName('description') . ' = ' . $this->_db->quote($data['description']));
            }

            if (isset($data['is_validated'])) {
                if (in_array($data['is_validated'], array_keys($is_validated))) {
                    $query->set($this->_db->quoteName('is_validated') . ' = ' . $this->_db->quote($data['is_validated']));
                }
            }

            if (isset($data['can_be_viewed'])) {
                if (in_array($data['can_be_viewed'], array_keys($can_be_viewed))) {
                    $query->set($this->_db->quoteName('can_be_viewed') . ' = ' . $this->_db->quote($data['can_be_viewed']));
                }
            }

            if (isset($data['can_be_deleted'])) {
                if (in_array($data['can_be_deleted'], array_keys($can_be_deleted))) {
                    $query->set($this->_db->quoteName('can_be_deleted') . ' = ' . $this->_db->quote($data['can_be_deleted']));
                }
            }

            $query->set($this->_db->quoteName('modified') . ' = ' . $this->_db->quote(date("Y-m-d H:i:s")))
                ->set($this->_db->quoteName('modified_by') . ' = ' . $this->_db->quote($data['user']))
                ->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($data['id']));

            try {
                $this->_db->setQuery($query);
                $return['update'] = $this->_db->execute();

                if ($return['update']) {
                    $newData = $this->getUploadByID($data['id']);
                    $logger = array();

                    $includedKeys = ['description', 'can_be_deleted', 'can_be_viewed', 'is_validated'];

                    require_once(JPATH_SITE . '/components/com_emundus/models/logs.php');
                    require_once(JPATH_SITE . '/components/com_emundus/models/files.php');

                    $m_files = new EmundusModelFiles();
                    $applicant_id = ($m_files->getFnumInfos($data['fnum']))['applicant_id'];
                    $attachmentParams = $this->getAttachmentByID($newData['attachment_id']);

                    if (empty($_FILES)) {
                        // find the difference(s)
                        foreach ($oldData as $key => $value) {
                            // by default : null = "invalid" or -2
                            if ($key === 'is_validated' and is_null($value)) {
                                $value = -2;            # recheck !!!
                            }

                            $logsStd = new stdClass();
                            if ($oldData[$key] !== $newData[$key] and in_array($key, $includedKeys)) {
                                $logsStd->description = '<b>' . '[' . $attachmentParams['value'] . ']' . '</b>';
                                $logsStd->element = '<u>' . JText::_($key) . '</u>';

                                // check if a var with same name as the key exists
                                $column_values = ${$key};
                                if (!empty($column_values) && in_array($oldData[$key], array_keys($column_values))) {
                                    $logsStd->old = JText::_($column_values[$oldData[$key]]);
                                    $logsStd->new = JText::_($column_values[$newData[$key]]);
                                }
                                else {
                                    $logsStd->old = $oldData[$key];
                                    $logsStd->new = $newData[$key];
                                }

                                $logger[] = $logsStd;
                            }
                            else {
                                continue;
                            }

                            $logsParams = array('updated' => $logger);
                        }
                        EmundusModelLogs::log($this->_user->id, $applicant_id, $data['fnum'], 4, 'u', 'COM_EMUNDUS_ACCESS_ATTACHMENT_UPDATE', json_encode($logsParams, JSON_UNESCAPED_UNICODE));
                    }
                    else {
                        $logsStd = new stdClass();

                        $logsStd->element = '[' . $attachmentParams['value'] . ']';
                        $logsStd->details = $_FILES['file']['name'];
                        $logsParams       = array('created' => [$logsStd]);
                        EmundusModelLogs::log($this->_user->id, $applicant_id, $data['fnum'], 4, 'c', 'COM_EMUNDUS_ACCESS_ATTACHMENT_CREATE', json_encode($logsParams, JSON_UNESCAPED_UNICODE));
                    }
                }
            }
            catch (Exception $e) {
                JLog::add('Failed to update attachment ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
            }
        }

        return $return;
    }

    /**
     * Generate preview based on file types
     * @param int user id of the applicant
     * @param string filename
     *
     * @return string preview html tags
     */
    public function getAttachmentPreview($user, $fileName)
    {
        $preview = [
            'status' => true,
            'overflowX' => false,
            'overflowY' => false,
            'style' => '',
            'msg' => '',
            'error' => ''
        ];
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $filePath = EMUNDUS_PATH_REL . $user . "/" . $fileName;
        $fileExists = File::exists($filePath);

        if ($fileExists) {
            // create preview based on filetype
            if ($extension == 'pdf') {
                $config = JFactory::getConfig();
                if ($config->get('sef') == 0) {
                    $preview['content'] = '<iframe src="index.php?option=com_emundus&task=getfile&u=images/emundus/files/'. $user . '/' . $fileName . '" style="width:100%;height:100%;" border="0"></iframe>';
                }
                else{
                    $preview['content'] = '<iframe src="/index.php?option=com_emundus&task=getfile&u=images/emundus/files/'. $user . '/' . $fileName . '" style="width:100%;height:100%;" border="0"></iframe>';
                }
            } else if ($extension == 'txt') {
                $content = file_get_contents($filePath);
                $preview['overflowY'] = true;
                $preview['content'] = '<div class="wrapper" style="max-width: 100%;margin: 5px;padding: 20px;background-color: white;"><pre style="white-space: break-spaces;">' . $content . '</pre></div>';
            } else if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                $mimeType = mime_content_type($extension);

                if (empty($mimeType)) {
                    switch ($extension) {
                        case 'jpeg':
                        case 'jpg':
                            $mimeType = 'image/jpeg';
                            break;
                        case 'png':
                            $mimeType = 'image/png';
                            break;
                        case 'gif':
                            $mimeType = 'image/gif';
                            break;
                        default:
                            $mimeType = 'text/plain';
                            break;
                    }
                }

                $base64_images_preview = JComponentHelper::getParams('com_emundus')->get('base64_images_preview', 1);

                if ($base64_images_preview) {
                    $content = base64_encode(file_get_contents(JPATH_SITE . DS . $filePath));
                    $preview['content'] = '<div class="wrapper" style="height: 100%;display: flex;justify-content: center;align-items: center;"><img src="data:'. $mimeType .';base64,' . $content . '" style="display: block;max-width:100%;max-height:100%;width: auto;height: auto;" /></div>';
                } else {
                    $preview['content'] = '<div class="wrapper" style="height: 100%;display: flex;justify-content: center;align-items: center;"><img src="' . JURI::base() . $filePath . '" style="display: block;max-width:100%;max-height:100%;width: auto;height: auto;" /></div>';
                }
            } else if (in_array($extension, ['doc', 'docx', 'odt', 'rtf'])) {
                require_once (JPATH_LIBRARIES . '/emundus/vendor/autoload.php');

                switch($extension) {
                    case 'odt':
                        $class = 'ODText';
                        break;
                    case 'rtf':
                        $class = 'RTF';
                        break;
                    case 'doc':
                    case 'docx':
                    default:
                        $class = 'Word2007';
                }

                $phpWord = \PhpOffice\PhpWord\IOFactory::load(JPATH_SITE . DS . $filePath, $class);
                $htmlWriter = new \PhpOffice\PhpWord\Writer\HTML($phpWord);
                $content = $htmlWriter->getContent();

                $contentWithoutSpaces = preg_replace('/\s+/', '', $content);
                if (strpos($contentWithoutSpaces, '<body></') !== false) {
                    $preview['status'] = false;
                    $preview['error'] = 'unavailable';
                    $preview['content'] = '<div style="width:100%;height: 100%;display: flex;justify-content: center;align-items: center;"><p style="margin:0;text-align:center;">' . JText::_('COM_EMUNDUS_ATTACHMENTS_DOCUMENT_PREVIEW_UNAVAILABLE') . '</p></div>';
                } else {
                    $preview['content'] = '<div class="wrapper">' . $content . '</div>';
                    $preview['overflowY'] = true;
                    $preview['style'] = 'word';
                    $preview['msg'] = JText::_('COM_EMUNDUS_ATTACHMENTS_DOCUMENT_PREVIEW_INCOMPLETE_MSG');
                }
            } else if (in_array($extension, ['xls', 'xlsx', 'ods', 'csv'])) {
                require_once (JPATH_LIBRARIES . '/emundus/vendor/autoload.php');

                $phpSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(JPATH_SITE . DS . $filePath);
                $htmlWriter = new \PhpOffice\PhpSpreadsheet\Writer\Html($phpSpreadsheet);
                $htmlWriter->setGenerateSheetNavigationBlock(true);
                $htmlWriter->setSheetIndex(null);
                $preview['content'] = $htmlWriter->generateHtmlAll();
                $preview['overflowY'] = true;
                $preview['overflowX'] = true;
                $preview['style'] = 'sheet';

                $preview['msg'] = JText::_('COM_EMUNDUS_ATTACHMENTS_DOCUMENT_PREVIEW_INCOMPLETE_MSG');
            } else if (in_array($extension, ['ppt', 'pptx', 'odp'])) {
                // ? PHPPresentation is not giving html support... need to create it manually ?
                $preview['content'] = $this->convertPowerPointToHTML($filePath);
                $preview['overflowY'] = true;
                $preview['style'] = 'presentation';

                $preview['msg'] = JText::_('COM_EMUNDUS_ATTACHMENTS_DOCUMENT_PREVIEW_INCOMPLETE_MSG');
            } else if (in_array($extension, ['mp3', 'wav', 'ogg'])) {
                $preview['content'] = '<div class="wrapper" style="height: 100%;display: flex;justify-content: center;align-items: center;"><audio controls><source src="' . JURI::base() . $filePath . '" type="audio/' . $extension . '"></audio></div>';
            } else if (in_array($extension, ['mp4', 'webm', 'ogg'])) {
                $preview['content'] = '<div class="wrapper" style="height: 100%;display: flex;justify-content: center;align-items: center;"><video controls  style="max-width: 100%;"><source src="'. JURI::base() . $filePath . '" type="video/' . $extension . '"></video></div>';
            } else {
                $preview['status'] = false;
                $preview['error'] = 'unsupported';
                $preview['content'] = '<div style="width:100%;height: 100%;display: flex;flex-direction: column;justify-content: center;align-items: center;"><p style="margin:0;text-align:center;">' . JText::_('COM_EMUNDUS_ATTACHMENTS_FILE_TYPE_NOT_SUPPORTED') . '</p><p><a href="'. JURI::base() . $filePath . '" target="_blank" download>' . JText::_('COM_EMUNDUS_ATTACHMENTS_DOWNLOAD')  . '</a></p></div>';
            }
        } else {
            $preview['status'] = false;
            $preview['error'] = 'file_not_found';
            $preview['content'] = '<div style="width:100%;height: 100%;display: flex;justify-content: center;align-items: center;"><p style="margin:0;text-align:center;">' . JText::_('COM_EMUNDUS_ATTACHMENTS_FILE_NOT_FOUND') . '</p></div>';
        }

        return $preview;
    }

    /**
     * @param $filePath
     * @return string (html content)
     */
    private function convertPowerPointToHTML($filePath)
    {
        $content = '';

        // create a ziparchive
        $zip = new ZipArchive;

        if ($zip->open($filePath)) {
            // get xml content of all slides
            $slides = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (strpos($filename, 'ppt/slides/slide') !== false) {
                    $slides[] = $zip->getFromIndex($i);
                }
            }

            // get style properties of all slides
            $slideStyles = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (strpos($filename, 'ppt/slideMasters/slideMaster') !== false) {
                    $slideStyles[] = $zip->getFromIndex($i);
                }
            }

            $zip->close();

            // create html content from slides and style
            $content = '<div class="wrapper" style="display: flex;flex-direction:column;justify-content: flex-start;align-items: center;">';
            foreach ($slides as $key => $slide) {
                $content .= '<div class="slide" style="width: 100%;height: 100%;">';

                $dom = new DOMDocument();
                $dom->loadXML($slide);

                $xpath = new DOMXPath($dom);

                $query = '//a:p';
                $entries = $xpath->query($query);

                foreach($entries as $e_key => $entry) {
                    $content .= "<p>";

                    // use . for relative query
                    $query = './/a:t';
                    $text_entries = $xpath->query($query, $entry);

                    foreach($text_entries as $text) {
                        $content .= $text->nodeValue;
                    }

                    $content .= "</p>";
                }

                // $content .= $dom->saveXML();

                $content .= '</div>';
            }
        }

        return $content;
    }

	public function getValuesByElementAndFnum($fnum,$eid,$fid,$raw=1,$wheres = [],$uid=null,$format = true,$repeate_sperator = ","){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$result = '';

		try {
			$query->select('db_table_name')
				->from($db->quoteName('#__fabrik_lists'))
				->where($db->quoteName('form_id') . ' = ' . $fid);
			$db->setQuery($query);
			$table = $db->loadResult();

			$query->clear()
				->select('applicant_id')
				->from($db->quoteName('#__emundus_campaign_candidature'))
				->where($db->quoteName('fnum') . ' LIKE ' . $fnum);
			$db->setQuery($query);
			$aid = $db->loadResult();

			$query->clear()
				->select('fe.id,fe.name,fe.group_id,fe.plugin,fe.default,fe.params,fg.params as group_params')
				->from($db->quoteName('#__fabrik_elements','fe'))
				->leftJoin($db->quoteName('#__fabrik_groups','fg').' ON '.$db->quoteName('fg.id').' = '.$db->quoteName('fe.group_id'))
				->where($db->quoteName('fe.id') . ' = ' . $db->quote($eid));
			$db->setQuery($query);
			$element = $db->loadObject();
			$group_params = json_decode($element->group_params);

			if($table == 'jos_emundus_evaluations'){
				$params = JComponentHelper::getParams('com_emundus');
				$multi_eval = $params->get('multi_eval', 0);

				if($multi_eval == 1) {
					$wheres[] = $db->quoteName('user') . ' = ' . $db->quote(JFactory::getUser()->id);
				}
			}

			if($group_params->repeat_group_button == 1){
				$query->clear()
					->select('join_from_table,table_join,table_key,table_join_key')
					->from($db->quoteName('#__fabrik_joins'))
					->where($db->quoteName('group_id') . ' = ' . $db->quote($element->group_id))
					->andWhere($db->quoteName('table_join_key') . ' = ' . $db->quote('parent_id'));
				$db->setQuery($query);
				$join_params = $db->loadObject();

				$query->clear()
					->select($db->quoteName('r.'.$element->name))
					->from($db->quoteName($join_params->join_from_table,'p'))
					->leftJoin($db->quoteName($join_params->table_join,'r').' ON '.$db->quoteName('r.'.$join_params->table_join_key).' = '.$db->quoteName('p.'.$join_params->table_key))
					->where($db->quoteName('p.fnum') . ' LIKE ' . $db->quote($fnum));
				foreach ($wheres as $where){
					$query->where($where);
				}
				$db->setQuery($query);


				$values = $db->loadColumn();

			} else {
				$query->clear()
					->select($db->quoteName($element->name))
					->from($db->quoteName($table))
					->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum));
                if(!empty($uid)){
                    $query->andWhere($db->quoteName('user') . ' = ' . $db->quote($uid));
                }
				foreach ($wheres as $where){
					$query->where($where);
				}
				$db->setQuery($query);
				$values = $db->loadResult();
			}

            $elt = [];

            if ($format) {
                if(!is_array($values)){
                    $values = [$values];
                }

                if (!empty($values) || $element->plugin == 'yesno') {
                    foreach ($values as $value) {
                        $elt[] = EmundusHelperFabrik::formatElementValue($element->name, $value, $element->group_id, $aid, true);
                    }
                }

                $result = implode($repeate_sperator,$elt);

            } else {

                $result = $values;
            }
        } catch (Exception $e) {
            JLog::add('Problem when get values of element ' . $eid . ' with fnum ' . $fnum . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
        }

		return $result;
    }

    /**
     * @param $element farbik element object
     * @param $value value of the element
     * @param $table table name
     * @param $applicant_id
     * @return $elt
     * @deprecated Use EmundusHelperFabrik::formatElementValue instead
     * @throws Exception
     */
    public function formatElementValue($element, $value, $table, $applicant_id)
    {
		$query = $this->_db->getQuery(true);
		$query->select('group_id')
			->from($this->_db->quoteName('#__fabrik_elements'))
			->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($element->id));
	    $this->_db->setQuery($query);
		$group_id = $this->_db->loadResult();

		return EmundusHelperFabrik::formatElementValue($element->name,$value,$group_id,$applicant_id,true);
    }


    public function moveApplicationByColumn($fnum, $direction = 'up', $order_column = 'ordering')
    {
        $moved = false;

        $exclude_columns = ['fnum', 'id', 'user', 'user_id', 'applicant_id', 'status', 'submitted'];
        if (in_array($order_column, $exclude_columns) || empty($order_column) || empty($fnum)) {
            throw new Exception('Invalid order column or fnum');
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select($order_column)
            ->from('#__emundus_campaign_candidature as ecc')
            ->where('fnum LIKE ' . $db->quote($fnum));

        try {
            $db->setQuery($query);
            $current_position = $db->loadResult();

            $query->clear()
                ->select('esc.year, ecc.applicant_id')
                ->from('#__emundus_campaign_candidature as ecc')
                ->join('INNER', '#__emundus_setup_campaigns as esc ON esc.id = ecc.campaign_id')
                ->where('fnum LIKE ' . $db->quote($fnum));

            $db->setQuery($query);
            $current_file_infos = $db->loadAssoc();

            $query->clear()
                ->select($order_column . ' as target_position, ecc.fnum')
                ->from('#__emundus_campaign_candidature as ecc')
                ->join('INNER', '#__emundus_setup_campaigns as esc ON esc.id = ecc.campaign_id')
                ->where('ecc.applicant_id = ' . $db->quote($current_file_infos['applicant_id']))
                ->andWhere('esc.year = ' . $db->quote($current_file_infos['year']))
                ->andWhere($db->quoteName($order_column) . ' ' . ($direction == 'up' ? '<' : '>') . ' ' . $db->quote($current_position))
                ->andWhere('ecc.published = 1')
                ->order($db->quoteName($order_column) . ' ' . ($direction == 'up' ? 'DESC' : 'ASC'))
                ->setLimit(1);

            $db->setQuery($query);
            $target_file = $db->loadAssoc();

            if (!empty($target_file)) {
                $query->clear()
                    ->update('#__emundus_campaign_candidature')
                    ->set($db->quoteName($order_column) . ' = ' . $target_file['target_position'])
                    ->where('fnum LIKE ' . $db->quote($fnum));

                $db->setQuery($query);
                $moved = $db->execute();

                if ($moved) {
                    $query->clear()
                        ->update('#__emundus_campaign_candidature')
                        ->set($db->quoteName($order_column) . ' = ' . $current_position)
                        ->where('fnum LIKE ' . $db->quote($target_file['fnum']));

                    $db->setQuery($query);
                    $moved = $db->execute();
                }
            }
        } catch (Exception $e) {
            JLog::add('Failed to get ' . $order_column . ' in #__emundus_campaign_candidature for ' . $fnum . ' ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
            throw new Exception($e->getMessage());
        }

        return $moved;
    }

    private function getSelectFromDBJoinElementParams($params, $alias = 'jd') {
		$select = '';

		if (!empty($params)) {
			$db = JFactory::getDBO();
			$select = $db->quoteName($params->join_val_column);
			if (!empty($params->join_val_column_concat)) {
				$select = 'CONCAT(' . $params->join_val_column_concat . ')';
				$select = preg_replace('#{thistable}#', $alias, $select);
				$select = preg_replace('#{shortlang}#', $this->locales, $select);
			}
		}

        return $select;
    }

	public function createTab($name, $user_id){
		$tab_id = 0;

		if (!empty($user_id) && !empty($name) && strlen($name) <= 255 && strlen($name) >= 3) {
			/**
			 * \d: digits
			 * \s: Space character.
			 * \p{L}: Unicode letters.
             * u :  unicode characters accepted
			 */
			$regex = '/^[\d\s\p{L}\'"\-]{3,255}$/u';

			if (preg_match($regex, $name)) {
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->insert($db->quoteName('#__emundus_campaign_candidature_tabs'))
					->set($db->quoteName('name') . ' = ' . $db->quote($name))
					->set($db->quoteName('ordering') . ' = 1')
					->set($db->quoteName('applicant_id') . ' = ' . $user_id);
				try {
					$db->setQuery($query);
					$db->execute();

					$tab_id = $db->insertid();
				} catch (Exception $e) {
					JLog::add('Failed to create for user ' . $user_id . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
				}			}
		}

		return $tab_id;
	}

	public function getTabs($user_id) {
		$tabs = [];

		if (!empty($user_id)) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('ecct.*,count(ecc.id) as no_files')
				->from($db->quoteName('#__emundus_campaign_candidature_tabs','ecct'))
				->leftJoin($db->quoteName('#__emundus_campaign_candidature','ecc').' ON '.$db->quoteName('ecc.tab').' = '.$db->quoteName('ecct.id'))
				->where($db->quoteName('ecct.applicant_id') . ' = ' . $user_id)
				->group($db->quoteName('ecct.id'))
				->order($db->quoteName('ecct.ordering'));

			try {
				$db->setQuery($query);
				$tabs =  $db->loadAssocList();
			} catch (Exception $e) {
				JLog::add(JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage(), JLog::ERROR, 'com_emundus');
			}
		}

		return $tabs;
	}

	public function updateTabs($tabs, $user_id){
		$updated = false;

		if (!empty($tabs) && !empty($user_id)) {
			try {
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);

				$updates = [];
				foreach ($tabs as $tab) {
					$tab->id = (int) $tab->id;
					$owned = $this->isTabOwnedByUser($tab->id, $user_id);

					if ($owned) {
						$query->clear()
							->update($db->quoteName('#__emundus_campaign_candidature_tabs'))
							->set($db->quoteName('name') . ' = ' . $db->quote($tab->name))
							->set($db->quoteName('ordering') . ' = ' . $db->quote($tab->ordering))
							->where($db->quoteName('id') . ' = ' . $db->quote($tab->id));

						$db->setQuery($query);
						$updates[] = $db->execute();
					}
				}

				$updated = !in_array(false, $updates) && !empty($updates);
			} catch (Exception $e) {
				JLog::add('Failed to update tabs for user ' . $user_id . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
			}
		}

		return $updated;
	}

	public function deleteTab(int $tab_id, int $user_id){
		$deleted = false;

		if (!empty($tab_id) && !empty($user_id)) {
			$owned = $this->isTabOwnedByUser($tab_id, $user_id);

			if ($owned) {
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);

				try {
					$query->clear()
						->update($db->quoteName('#__emundus_campaign_candidature'))
						->set($db->quoteName('tab') . ' = NULL')
						->where($db->quoteName('tab') . ' = ' . $tab_id)
						->where($db->quoteName('applicant_id') . ' = ' . $user_id);
					$db->setQuery($query);
					$db->execute();

					$query->clear()
						->delete($db->quoteName('#__emundus_campaign_candidature_tabs'))
						->where($db->quoteName('id') . ' = ' . $tab_id);
					$db->setQuery($query);
					$deleted = $db->execute();
				} catch (Exception $e) {
					JLog::add('Failed to create for user ' . $user_id . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
					$deleted = false;
				}
			}
		}

		return $deleted;
	}

	public function moveToTab($fnum, $tab){
		$moved = false;

		if (!empty($fnum) && !empty($tab)) {
			$tab = (int) $tab;
			$owned = $this->isTabOwnedByUser($tab, 0, $fnum);

			if ($owned) {
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);

				try {
					$query->clear()
						->update($db->quoteName('#__emundus_campaign_candidature'))
						->set($db->quoteName('tab') . ' = ' . $db->quote($tab))
						->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum));
					$db->setQuery($query);
					$moved = $db->execute();
				} catch (Exception $e) {
					JLog::add('Failed to move fnum ' . $fnum . ' in tab ' . $tab . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
				}
			}
		}

		return $moved;
	}

	public function copyFile($fnum, $fnum_to){
		$result = false;

		if (!empty($fnum) && !empty($fnum_to)) {
			try {
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->insert($db->quoteName('#__emundus_campaign_candidature_links'))
					->set($db->quoteName('date_time') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
					->set($db->quoteName('fnum_from') . ' = ' . $db->quote($fnum))
					->set($db->quoteName('fnum_to') . ' = ' . $db->quote($fnum_to));
				$db->setQuery($query);
				$result = $db->execute();
			} catch (Exception $e) {
				JLog::add('Failed to copy fnum from ' . $fnum . ' to ' . $fnum_to . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
			}
		}

		return $result;
	}

	public function renameFile($fnum, $new_name){
		$result = false;

        $new_name = trim($new_name);
		if (!empty($fnum) && !empty($new_name)) {
            $regex = '/^[\d\s\p{L}\'"\-]{3,255}$/u';

			if (preg_match($regex, $new_name)) {
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->update($db->quoteName('#__emundus_campaign_candidature'))
					->set($db->quoteName('name') . ' = ' . $db->quote($new_name))
					->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum));

				try {
					$db->setQuery($query);
					$result = $db->execute();
				} catch (Exception $e) {
					JLog::add('Failed to rename file ' . $fnum . ' with name ' . $new_name . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
				}
			} else {
                throw new Exception(JText::_('COM_EMUNDUS_INVALID_NAME'));
            }
		}

		return $result;
	}

	public function getCampaignsAvailableForCopy($fnum){
		$campaigns = [];

		try {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('esc.training')
				->from($db->quoteName('#__emundus_campaign_candidature','ecc'))
				->leftJoin($db->quoteName('#__emundus_setup_campaigns','esc').' ON '.$db->quoteName('esc.id').' = '.$db->quoteName('ecc.campaign_id'))
				->where($db->quoteName('ecc.fnum') . ' LIKE ' . $db->quote($fnum));
			$db->setQuery($query);
			$prog_code = $db->loadResult();

			if(!empty($prog_code)){
				$query->clear()
					->select('esc.id,esc.label')
					->from($db->quoteName('#__emundus_setup_campaigns','esc'))
					->where($db->quoteName('esc.training') . ' LIKE ' . $db->quote($prog_code))
					->where($db->quoteName('esc.start_date') . ' < NOW()')
					->where($db->quoteName('esc.end_date') . ' > NOW()');
				$db->setQuery($query);
				$campaigns_object = $db->loadObjectList('id');

				foreach ($campaigns_object as $key => $campaign){
					$campaigns[$campaign->id] = $campaign->label;
				}
			}
		}
		catch (Exception $e) {
			JLog::add('Failed to get available campaigns via fnum ' . $fnum . ' with error ' . $e->getMessage(), JLog::ERROR, 'com_emundus.error');
		}

		return $campaigns;
	}

	/**
	 * @param $tab_id
	 * @param $user_id - if empty, check for fnum
	 * @param $fnum - if empty, check for user_id
	 * @return bool
	 */
	public function isTabOwnedByUser($tab_id, $user_id = 0, $fnum = '') {
		$owned = false;

		$tab_id = (int)$tab_id;
		$user_id = (int)$user_id;
		$fnum = preg_replace('/^[^0-9]+$/', '', $fnum);

		if (!empty($tab_id)) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$tab_id_found = 0;
			if (!empty($user_id)) {
				$query->select('ecct.id')
					->from($db->quoteName('#__emundus_campaign_candidature_tabs', 'ecct'))
					->where('ecct.id = ' . $db->quote($tab_id))
					->andWhere('ecct.applicant_id = ' .  $db->quote($user_id));

				$db->setQuery($query);
				$tab_id_found = $db->loadResult();
			} elseif (!empty($fnum)) {
				$query->select('ecct.id')
					->from($db->quoteName('#__emundus_campaign_candidature_tabs', 'ecct'))
					->leftJoin('#__emundus_campaign_candidature as ecc ON ecc.applicant_id = ecct.applicant_id')
					->where('ecc.fnum = ' . $db->quote($fnum))
					->andWhere('ecct.id = ' .  $db->quote($tab_id));

				$db->setQuery($query);
				$tab_id_found = $db->loadResult();
			}
			$owned = !empty($tab_id_found);
		}

		return $owned;
	}

	/**
	 * @param $action
	 * @param $fnum
	 * @param $module_id if not specified, will use the first published module
	 * @param $redirect
	 * @return bool true if the action was done successfully
	 */
	public function applicantCustomAction($action, $fnum, $module_id = 0, $redirect = false) {
		$done = false;

		if (!empty($action) && !empty($fnum)) {
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);

			$query->select('id, params')
				->from('#__modules')
				->where('module LIKE ' . $db->quote('mod_emundus_applications'))
				->andWhere('published = 1');

			if (!empty($module_id)) {
				$query->andWhere('id = ' . $db->quote($module_id));
			}

			$db->setQuery($query);
			$module = $db->loadAssoc();

			if (!empty($module['params'])) {
				$params = json_decode($module['params'], true);

				if (!empty($params['mod_em_application_custom_actions'][$action])) {
					$current_action = $params['mod_em_application_custom_actions'][$action];

					if (isset($current_action['mod_em_application_custom_action_new_status'])) {
						$query->clear()
							->select('status')
							->from('#__emundus_campaign_candidature')
							->where('fnum LIKE ' . $db->quote($fnum));

						$db->setQuery($query);
						$status = $db->loadResult();

						if (in_array($status, $current_action['mod_em_application_custom_action_status']) && $status != $current_action['mod_em_application_custom_action_new_status']) {
							require_once(JPATH_ROOT . '/components/com_emundus/models/files.php');
							$m_files = new EmundusModelFiles();
							$updated = $m_files->updateState($fnum, $current_action['mod_em_application_custom_action_new_status']);

							if ($updated) {
								$done = true;

								if ($redirect) {
									$app = JFactory::getApplication();
									$app->redirect('/');
								}
							}
						}
					}
				}
			}
		}

		return $done;
	}
}
