<?php


/**
 * @package    Joomla
 * @subpackage eMundus
 * @link       http://www.emundus.fr
 * @copyright	Copyright (C) 2016 eMundus SAS. All rights reserved.
 * @license    GNU/GPL
 * @author     eMundus SAS - Benjamin Rivalland
 */

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * campaign View
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusViewCampaign extends JViewLegacy {

    function __construct($config = array()) {

        // Set up the data to be sent in the response.
        // call camaign controller
        require_once (JPATH_COMPONENT.DS.'models'.DS.'campaign.php');

        parent::__construct($config);
    }

    function display($tpl = null)
    {
        $user = JFactory::getUser();

        if ($user->guest) {
            $m_campaign = new EmundusModelCampaign();
            $data = $m_campaign->getTeachingUnity();

            echo json_encode($data);
        }
    }
}
?>