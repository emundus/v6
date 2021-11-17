<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2016 eMundus. All rights reserved.
 * @license     GNU/GPL
 * @author      eMundus - Benjamin Rivalland
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * campaign Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusControllerCampaign extends JControllerLegacy {
    var $_user = null;
    var $_db = null;

    function __construct($config = array()){
        //require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
        //require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
        //require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        //require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
        //require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');

        $this->_user = JFactory::getSession()->get('emundusUser');
        $this->_db = JFactory::getDBO();

        parent::__construct($config);
    }
    function display($cachable = false, $urlparams = false) {
        // Set a default view if none exists
        if ( ! JRequest::getCmd( 'view' ) ) {
            $default = 'campaign';
            JRequest::setVar('view', $default );
        }
        parent::display();
    }

    function clear() {
        EmundusHelperFilters::clear();
    }

    function setCampaign()
    {
        return true;
    }

    public function addcampaigns(){
        $user = JFactory::getUser();
        $view = JRequest::getVar('view', null, 'GET', 'none',0);
        $itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
        $data = array();
        $data['start_date'] = JRequest::getVar('start_date', null, 'POST', 'none',0);
        $data['end_date'] = JRequest::getVar('end_date', null, 'POST', 'none',0);
        $data['profile_id'] = JRequest::getVar('profile_id', null, 'POST', 'none',0);
        $data['year'] = JRequest::getVar('year', null, 'POST', 'none',0);
        $data['short_description'] = JRequest::getVar('short_description', null, 'POST', 'none',0);

        $mcampaign = $this->getModel('campaign');
        $mprogramme = $this->getModel('programme');

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $programmes = $mprogramme->getProgrammes(1);

            if (count($programmes) > 0)
                $result = $mcampaign->addCampaignsForProgrammes($data, $programmes);
            else $result = false;
            if ($result === false)
                $tab = array('status' => 0, 'msg' => JText::_('COM_EMUNDUS_AMETYS_ERROR_CANNOT_ADD_CAMPAIGNS'), 'data' => $result);
            else $tab = array('status' => 1, 'msg' => JText::_('COM_EMUNDUS_CAMPAIGNS_ADDED'), 'data' => $result);
        }
        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Gets all campaigns linked to a program code
     */
    public function getcampaignsbyprogram(){
        $user = JFactory::getUser();
        $jinput = JFactory::getApplication()->input;
        $course = $jinput->get('course');

        $model = $this->getModel('campaign');

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id))
        {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        }
        else
        {
            $campaigns = $model->getCampaignsByProgram($course);
        }
        echo json_encode((object) [
            'status' => true,
            'campaigns' => $campaigns
        ]);
        exit;
    }
}
?>
