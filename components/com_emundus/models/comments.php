<?php

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');
jimport('joomla.user.helper');


class EmundusModelComments extends JModelLegacy
{
    private $db;

    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->db = JFactory::getDbo();
    }

    /**
     * @param $file_id int
     * @param $comment string
     * @param $target array|null (target_type, target_id)
     * @param $visible_to_applicant int (0|1)
     * @param $user int
     * @return int
     */
    public function addComment($file_id, $comment, $target, $visible_to_applicant, $parent_id = 0, $user = null): int
    {
        $new_comment_id = 0;

        if (empty($user)) {
            $user = JFactory::getUser()->id;
        }

        if (!empty($file_id) && !empty($comment)) {
            $target_type = !empty($target) && isset($target['type']) ? $target['type'] : '';
            $target_id = !empty($target) && isset($target['id']) ? $target['id'] : '';

            $query = $this->db->getQuery(true);

            $query->select('fnum, applicant_id')
                ->from($this->db->quoteName('#__emundus_campaign_candidature'))
                ->where('id = ' . $this->db->quote($file_id));

            $this->db->setQuery($query);
            $file_infos = $this->db->loadAssoc();

            $query->clear()
                ->insert($this->db->quoteName('#__emundus_comments'))
                ->columns([
                    $this->db->quoteName('ccid'),
                    $this->db->quoteName('fnum'),
                    $this->db->quoteName('applicant_id'),
                    $this->db->quoteName('comment_body'),
                    $this->db->quoteName('target_type'),
                    $this->db->quoteName('target_id'),
                    $this->db->quoteName('visible_to_applicant'),
                    $this->db->quoteName('user_id'),
                    $this->db->quoteName('date'),
                    $this->db->quoteName('parent_id')
                ])
                ->values(
                    $this->db->quote($file_id) . ', ' .
                    $this->db->quote($file_infos['fnum']) . ', ' .
                    $this->db->quote($file_infos['applicant_id']) . ', ' .
                    $this->db->quote($comment) . ', ' .
                    $this->db->quote($target_type) . ', ' .
                    $this->db->quote($target_id) . ', ' .
                    $this->db->quote($visible_to_applicant) . ', ' .
                    $this->db->quote($user) . ', 
                    NOW(), ' .
                    $this->db->quote($parent_id)
                );

            try {
                $this->db->setQuery($query);
                $inserted = $this->db->execute();

                if ($inserted) {
                    $new_comment_id = $this->db->insertid();

                    // TODO: log added comments
                }
            } catch (Exception $e) {
                JLog::add('Failed to add comment ' . $e->getMessage(), JLog::ERROR, 'com_emundus.comments');
            }
        }

        return $new_comment_id;
    }

    public function deleteComment($comment_id, $user) {
        $deleted = false;

        if (!empty($comment_id) && !empty($user)) {
            $query = $this->db->getQuery(true);
            $query->delete($this->db->quoteName('#__emundus_comments'))
                ->where('id = ' . $this->db->quote($comment_id))
                ->orWhere('parent_id = ' . $this->db->quote($comment_id));

            try {
                $this->db->setQuery($query);
                $deleted = $this->db->execute();
            } catch (Exception $e) {
                JLog::add('Failed to delete comment ' . $e->getMessage(), JLog::ERROR, 'com_emundus.comments');
            }

            if ($deleted) {
                // TODO: log deleted comments
            }
        }

        return $deleted;
    }

    public function updateComment($comment_id, $comment, $user) {
        $updated = false;

        if (!empty($comment_id) && !empty($comment) && !empty($user)) {
            $query = $this->db->getQuery(true);
            $query->update($this->db->quoteName('#__emundus_comments'))
                ->set($this->db->quoteName('comment_body') . ' = ' . $this->db->quote($comment))
                ->set($this->db->quoteName('updated') . ' = NOW()')
                ->set($this->db->quoteName('updated_by') . ' = ' . $this->db->quote($user))
                ->where('id = ' . $this->db->quote($comment_id));

            try {
                $this->db->setQuery($query);
                $updated = $this->db->execute();
            } catch (Exception $e) {
                JLog::add('Failed to update comment ' . $e->getMessage(), JLog::ERROR, 'com_emundus.comments');
            }

            if ($updated) {
                // TODO: log updated comments
            }
        }

        return $updated;
    }

    /**
     * @param $comment_id
     * @param $opened
     * @param $user
     * @return false|mixed
     */
    public function updateCommentOpenedState($comment_id, $opened, $user)
    {
        $updated = false;

        if (!empty($comment_id)) {
            $query = $this->db->getQuery(true);
            $query->update($this->db->quoteName('#__emundus_comments'))
                ->set($this->db->quoteName('opened') . ' = ' . $this->db->quote($opened))
                ->set($this->db->quoteName('updated') . ' = NOW()')
                ->set($this->db->quoteName('updated_by') . ' = ' . $this->db->quote($user))
                ->where('id = ' . $this->db->quote($comment_id));

            try {
                $this->db->setQuery($query);
                $updated = $this->db->execute();
            } catch (Exception $e) {
                JLog::add('Failed to update comment opened state ' . $e->getMessage(), JLog::ERROR, 'com_emundus.comments');
            }
        }

        return $updated;
    }

    /**
     * @param $comment_id
     * @return array
     */
    public function getComment($comment_id): array
    {
        $comment = [];

        if (!empty($comment_id)) {
            $query = $this->db->getQuery(true);
            $query->select('ec.*')
                ->from($this->db->quoteName('#__emundus_comments', 'ec'))
                ->where('ec.id = ' . $this->db->quote($comment_id));

            try {
                $this->db->setQuery($query);
                $comment = $this->db->loadAssoc();
            } catch (Exception $e) {
                JLog::add('Failed to get comment ' . $e->getMessage(), JLog::ERROR, 'com_emundus.comments');
            }
        }

        return $comment;
    }

    /**
     * @param $file_id
     * @param $current_user
     * @return void
     */
    public function getComments($file_id, $current_user) {
        $comments = [];

        if (!empty($file_id) && !empty($current_user)) {
            $query = $this->db->getQuery(true);
            $query->select('ec.*')
                ->from($this->db->quoteName('#__emundus_comments', 'ec'))
                ->where('ec.ccid = ' . $this->db->quote($file_id));

            try {
                $this->db->setQuery($query);
                $comments = $this->db->loadAssocList();
            } catch (Exception $e) {
                JLog::add('Failed to get comments ' . $e->getMessage(), JLog::ERROR, 'com_emundus.comments');
            }

            if (!empty($comments)) {
                $users = [];
                $user_ids = array_column($comments, 'user_id');
                $user_ids = array_unique($user_ids);

                $query->clear()
                    ->select('id, name')
                    ->from($this->db->quoteName('#__users'))
                    ->where('id IN (' . implode(',', $user_ids) . ')');

                try {
                    $this->db->setQuery($query);
                    $users = $this->db->loadAssocList('id');
                } catch (Exception $e) {
                    JLog::add('Failed to get users ' . $e->getMessage(), JLog::ERROR, 'com_emundus.comments');
                }

                foreach ($comments as $key => $comment) {
                    $comments[$key]['username'] = $users[$comment['user_id']]['name'];
                    $comments[$key]['date_time'] = strtotime($comment['date']);
                    $comments[$key]['date'] = EmundusHelperDate::displayDate($comment['date']);
                    $comments[$key]['updated'] = EmundusHelperDate::displayDate($comment['updated']);
                }
            }
        }

        return $comments;
    }

    /**
     * @param $fnum
     * @return array
     */
    public function getTargetableElements($ccid)
    {
        $elements = [];

        if (!empty($ccid)) {
            $query = $this->db->getQuery(true);
            $query->select('campaign_id')
                ->from($this->db->quoteName('#__emundus_campaign_candidature'))
                ->where('id = ' . $this->db->quote($ccid));

            $this->db->setQuery($query);
            $campaign_id = $this->db->loadResult();

            if (!empty($campaign_id)) {
                require_once(JPATH_ROOT . '/components/com_emundus/models/campaign.php');
                $m_campaign = new EmundusModelCampaign();
                $profile_ids = $m_campaign->getProfilesFromCampaignId([$campaign_id]);

                if (!empty($profile_ids)) {
                    require_once(JPATH_ROOT . '/components/com_emundus/models/form.php');
                    $m_form = new EmundusModelForm();
                    require_once(JPATH_ROOT . '/components/com_emundus/helpers/fabrik.php');
                    $h_fabrik = new EmundusHelperFabrik();

                    foreach($profile_ids as $profile_id) {
                        $forms = $m_form->getFormsByProfileId($profile_id);

                        if (!empty($forms)) {
                            $form_ids = array_column($forms, 'id');

                            if (!empty($form_ids)) {
                                $elements = array_merge($elements, $h_fabrik->getElementsFromFabrikForms($form_ids));
                            }
                        }
                    }
                }
            }
        }

        return $elements;
    }
}
