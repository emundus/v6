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

    public function getComments($file_id) {

    }
}
