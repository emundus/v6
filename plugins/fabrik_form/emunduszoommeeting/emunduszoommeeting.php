<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';
require_once "ZoomAPIWrapper.php";

/**
* Create a Joomla user from the forms data
*
* @package     Joomla.Plugin
* @subpackage  Fabrik.form.juseremundus
* @since       3.0
*/

class PlgFabrik_FormEmunduszoommeeting extends plgFabrik_Form {
    public function onAfterProcess() {
        /* create new zoom meeting room */
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        /* get api key */
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $apiSecret = $eMConfig->get('zoom_jwt', '');

        /* call to api to create new zoom meeting */
        $zoom = new ZoomAPIWrapper($apiSecret);

        /* get info of host from $_POST */
        $host = current($_POST['jos_emundus_jury___president']);


        $startTime = date('Y-m-d\TH:i:s\Z', strtotime($_POST["jos_emundus_jury___start_time"]));
        $endTime = date('Y-m-d\TH:i:s\Z', strtotime($_POST["jos_emundus_jury___end_time"]));

        $hostQuery = "select * from data_referentiel_zoom_token as drzt where drzt.user = " . $host;
        $db->setQuery($hostQuery);
        $raw = $db->loadObject();

        $meetingData = json_encode(array(
            "topic" => $_POST['jos_emundus_jury___meeting_name'],
            "type" => 2,                                                // type 2 = scheduling meeting
            "start_time" => $startTime,
            "duration" => intval(strtotime($endTime)-strtotime($startTime)),
            "schedule for" => $raw->email,
            "timezone" => "Europe/Paris",
            "settings" => array(
                "registration_type" => 2,
                "host_video" => true,
                "participant_video" => true,
                "join_before_host" => true,
                "mute_upon_entry" => true,
                "approval_type" => 0,
                "close_registration" => true,
                "waiting_room" => false,
                "registrants_email_notification" => true,
                "contact_name" => "Official name",
                "contact_email"=> "official.email@example.com",
                "show_share_button" => false,
                "allow_multiple_devices" => true
            ),
            "encryption_type" => "enhanced_encryption"
        ));

        $response = $zoom->doRequest('POST', '/users/'. $raw->zoom_id .'/meetings', array(), array(), $meetingData);
        
        echo '<pre>'; var_dump($response); echo '</pre>'; die;
    }
}