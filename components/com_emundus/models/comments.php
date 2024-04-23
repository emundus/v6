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

    public function addComment($file_id, $comment, $target, $user) {

    }

    public function deleteComment($comment_id) {

    }

    public function updateComment($comment_id, $comment, $user) {

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
                $comments = $this->db->loadObjectList();
            } catch (Exception $e) {
                JLog::add('Failed to get comments ' . $e->getMessage(), JLog::ERROR, 'com_emundus.comments');
            }
        }

        return $comments;
    }
}
