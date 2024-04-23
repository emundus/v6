<?php

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');
jimport('joomla.user.helper');

use \classes\files\files;

class EmundusControllerComments extends JControllerLegacy
{
    private $user;

    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->user = JFactory::getUser();
    }

}