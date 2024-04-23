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
    public function addComment($file_id, $comment, $target, $visible_to_applicant, $user): int
    {
        $new_comment_id = 0;

        if (!empty($file_id) && !empty($comment)) {
            $target_type = !empty($target) && isset($target['target_type']) ? $target['target_type'] : '';
            $target_id = !empty($target) && isset($target['target_id']) ? $target['target_id'] : '';

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
                    $this->db->quoteName('date')
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
                    NOW()'
                );

            try {
                $this->db->setQuery($query);
                $inserted = $this->db->execute();

                if ($inserted) {
                    $new_comment_id = $this->db->insertid();
                }
            } catch (Exception $e) {
                var_dump($query->__toString());
                var_dump($e->getMessage());exit;

                JLog::add('Failed to add comment ' . $e->getMessage(), JLog::ERROR, 'com_emundus.comments');
            }
        }

        return $new_comment_id;
    }

    public function deleteComment($comment_id) {

    }

    public function updateComment($comment_id, $comment, $user) {

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
        }

        return $comments;
    }
}
