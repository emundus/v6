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

        echo json_encode((object)$campaigns);
        exit;
    }

    public function getmessagesbycampaign(){
        $user = JFactory::getUser();

        $m_messages = $this->model;

        $jinput = JFactory::getApplication()->input;

        $cid = $jinput->getString('cid');

        $messages = $m_messages->getMessagesByCampaign($cid);

        echo json_encode((object)$messages);
        exit;
    }
}
