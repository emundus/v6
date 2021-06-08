<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2016 eMundus. All rights reserved.
 * @license     GNU/GPL
 * @author      James Dean
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Campaign Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusmessengerControllermessages extends JControllerLegacy
{

    var $model = null;

    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->model = $this->getModel('messages');
    }

    /**
     * Get campaigns by fnums of current user
     */
    public function getcampaignsbyuser() {
        $user = JFactory::getUser();

        $m_messages = $this->model;

        $campaigns = $m_messages->getCampaignsByUser();

        $data = array('data' => $campaigns, 'current_user' => $user->id);

        echo json_encode((object)$data);
        exit;
    }

    public function getmessagesbyfnum(){
        $user = JFactory::getUser();

        $m_messages = $this->model;

        $jinput = JFactory::getApplication()->input;

        $fnum = $jinput->getString('fnum');

        $messages = $m_messages->getMessagesByFnum($fnum);

        $data = array('data' => $messages);

        echo json_encode((object)$data);
        exit;
    }

    public function sendmessage(){
        $user = JFactory::getUser();

        $m_messages = $this->model;

        $jinput = JFactory::getApplication()->input;

        $message = $jinput->getString('message');
        $fnum = $jinput->getString('fnum');

        $new_message = $m_messages->sendMessage($message,$fnum);

        echo json_encode((object)$new_message);
        exit;
    }
}
