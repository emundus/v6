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

    public function searchSubArray(Array $array, $key) {
        foreach ($array as $index => $subarray){
            if (isset($subarray[$key])) {
                return array('parent' => $index, 'status' => true);
            }
        }
    }

    /**
        * onAfterProcess ==> create new Zoom meeting
        * onAfterProcess ==> update an existing Zoom meeting
        * creator: eMundus
     */
    public function onAfterProcess() {
        # read and parse json template file
        $route = JPATH_BASE.'/plugins/fabrik_form/emunduszoommeeting/api_templates' . DS;
        $template = file_get_contents($route . __FUNCTION__ . '.json');
        $json = json_decode($template, true);

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $app = JFactory::getApplication();

        # get api key from Back-Office
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $apiSecret = $eMConfig->get('zoom_jwt', '');

        $zoom = new ZoomAPIWrapper($apiSecret);

        # get host (hosts -> users appear in table "data_referentiel_jury_token")
        $host = current($_POST['jos_emundus_jury___president']);

        # get host zoom id
        $hostQuery = "select * from data_referentiel_zoom_token as drzt where drzt.user = " . $host;
        $db->setQuery($hostQuery);
        $raw = $db->loadObject();

        # --- BEGIN CONFIG START TIME, END TIME, DURATION, TIMEZONE --- #
        $offset = $app->get('offset', 'UTC');
        $startTime = date('Y-m-d\TH:i:s\Z', strtotime($_POST["jos_emundus_jury___start_time_"]['date']));
        $endTime = date('Y-m-d\TH:i:s\Z', strtotime($_POST["jos_emundus_jury___end_time_"]['date']));

        # calculate meeting duration (raw) by seconds
        $duration = intval(strtotime($endTime)) - intval(strtotime($startTime));

        # calculate CELSA duration (started before 15 min -> 900 seconds) by minutes
        $celsa_duration = floor(($duration - 900) / 60);

        $_POST['jos_emundus_jury___timezone'] = $offset;
        $_POST['jos_emundus_jury___start_time'] = $startTime;
        $_POST['jos_emundus_jury___duration'] = $celsa_duration;
        # --- END CONFIG START TIME, END TIME, DURATION, TIMEZONE --- #

        $json = $this->dataMapping($_POST, 'jos_emundus_jury___', $json);

        # if meeting id (in db, not in Zoom) and meeting session do not exist, call endpoint to generate the new one
        if(empty($_POST['jos_emundus_jury___id']) and empty($_POST['jos_emundus_jury___meeting_session'])) {
            $response = $zoom->doRequest('POST', '/users/'. $raw->zoom_id .'/meetings', array(), array(), json_encode($json, JSON_PRETTY_PRINT));
            $httpCode = $zoom->responseCode();

            if($httpCode == 201) {
                # get last insert id
                try {
                    $getLastIdSql = "SELECT MAX(id) FROM jos_emundus_jury";
                    $db->setQuery($getLastIdSql);
                    $lid = $db->loadResult();

                    # update missing fields to table "jos_emundus_jury"
                    $updateSql = "UPDATE #__emundus_jury 
                                        SET meeting_session = "     . $db->quote($response['id']) .
                                            " , visio_link = "      . $db->quote($response['start_url']) .
                                                " , duration = "        . $db->quote($celsa_duration) .
                                                    " , join_url = "        . $db->quote($response['join_url']) .
                                                         " , registration_url = " . $db->quote($response['registration_url']) .
                                                            " , password = "        . $db->quote($response['password']) .
                                                                " WHERE #__emundus_jury.id = " . $lid;
                    $db->setQuery($updateSql);
                    $db->execute();
                } catch(Exception $e) {
                    JLog::add('Create Zoom meeting failed : ' . $e->getMessage(),JLog::ERROR, 'com_emundus');
                }
            } else {
                $zoom->requestErrors();
            }
        } else {
            /** HTTP Status Code
                * 204 : Meeting updated
                * 300 : Invalid enforce_login_domains, separate multiple domains by semicolon / A maximum of {rateLimitNumber} meetings can be created/updated for a single user in one day.
                * 400 : User not found on this account: {accountId} (error 1010) / Cannot access meeting information (error 3000) / You are not the meeting host. (error 3003) / (error 3000)
                * 404 : Meeting not found
            **/
            $zoom->doRequest('PATCH', '/meetings/' . $_POST['jos_emundus_jury___meeting_session'], array(), array(), json_encode($json, JSON_PRETTY_PRINT));

            if($zoom->responseCode() != 204) {
                $zoom->requestErrors();
            }
        }
    }

    /**
     * This method is used to map $_POST data with request schema (declared in api_templates)
     * Params:
        * $input (e.g: $_POST)
        * $separator (e.g: "jos_emundus_jury___")
        * $output
     * creator: eMundus
     **/
    public function dataMapping($input, $separator, $output) {
        foreach($input as $key => $post) {
            $suff = explode($separator, $key)[1];

            if($suff === null or empty($suff)) {
                unset($input[$suff]);
            } else {
                if (array_key_exists($suff, $output)) {
                    if (is_array($post) and sizeof($post) == 1)
                        $post = current($post);
                    $output[$suff] = $post;
                } else {
                    if ($this->searchSubArray($output, $suff)['status'] === true) {
                        $parentKey = $this->searchSubArray($output, $suff)['parent'];
                        if (is_array($post) and sizeof($post) == 1)
                            $post = current($post);
                        $output[$parentKey][$suff] = $post;

                    }
                }
            }
        }
        return $output;
    }
}