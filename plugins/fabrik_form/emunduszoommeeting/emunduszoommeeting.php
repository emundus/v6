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
        $template = file_get_contents($route . __FUNCTION__ . '_meeting.json');
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

        # create meeting room host
        # get firstname, lastname, email of #host
        $query->clear()->select('jeu.firstname, jeu.lastname, ju.email')
            ->from($db->quoteName('#__users', 'ju'))
            ->leftJoin($db->quoteName('#__emundus_users', 'jeu') . ' ON ' . $db->quoteName('ju.id') . ' = ' . $db->quoteName('jeu.user_id'))
            ->where($db->quoteName('ju.id') . ' = ' . $db->quote($host));

        $db->setQuery($query);
        $raw = $db->loadObject();

        # prepare the user data
        $user = json_encode(array(
            "action" => 'custCreate',
            "user_info" => [
                "email" => $raw->email,
                'type' => current($_POST['jos_emundus_jury___user_type']),
                "first_name" => $raw->firstname,
                "last_name" => $raw->lastname],
        ));

        # call to Zoom API endpoint
        $response = $zoom->doRequest('POST', '/users', array(), array(), $user);        /* array */

        # HTTP status = 201 :: User created
        if($zoom->responseCode() == 201) {
            $host_id = $response['id'];
            # create new zoom host
            $insertSql = "INSERT INTO data_referentiel_zoom_token (user,email,zoom_id) VALUES (" . $db->quote($host) . ', ' . $db->quote($host) . ', ' . $db->quote($response['id']) . ')';
            $db->setQuery($insertSql);
            $db->execute();
        } else {
            $uzId = $host;
            
            if($response['code'] == 1005) {
                # User already exist :: update the user settings except the firstname, lastname, email

                # find user id (Zoom) from $uzId
                $getUserSql = "select * from data_referentiel_zoom_token where data_referentiel_zoom_token.user = " . $uzId;
                $db->setQuery($getUserSql);
                $res = $db->loadObject();

                # update this user # prepare the json data
                $updateUserJson = json_encode(array("type" => current($_POST['jos_emundus_jury___user_type'])));
                
                # send request to update user endpoint
                $response = $zoom->doRequest('PATCH', '/users/' . $res->zoom_id, array(), array(), $updateUserJson);

                # reget the hostid
                $host_id = $res->zoom_id;

                // var_dump($host_id);die;

                # update SQL
                $updateUserSQL = "update data_referentiel_zoom_token SET user = " . $db->quote($res->user) . ", email = " . $db->quote($res->user) . " WHERE user = " . $db->quote($res->user);
                $db->setQuery($updateUserSQL);
                $db->execute();
            } else {
                $zoom->requestErrors();
            }
        }
        
        // var_dump($host_id);die;

        #right now, we have $host_id

        # --- BEGIN CONFIG START TIME, END TIME, DURATION, TIMEZONE --- #
        $offset = $app->get('offset', 'UTC');
        $startTime = date('Y-m-d\TH:i:s\Z', strtotime($_POST["jos_emundus_jury___start_time_"]['date']));
        # $endTime = date('Y-m-d\TH:i:s\Z', strtotime($_POST["jos_emundus_jury___end_time_"]['date']));

        # in case of CELSA, the meeting session will start 15 min before
        $startTimeCELSA = date('Y-m-d\TH:i:s\Z', strtotime($_POST["jos_emundus_jury___start_time_"]['date']) - (15 * 60));

        ######################################################################################################################

        # calculate meeting duration (raw) by seconds
        # $duration = intval(strtotime($endTime)) - intval(strtotime($startTime));

        # duration CELSA
        #$durationCELSA = intval(strtotime($endTime)) - intval(strtotime($startTimeCELSA));

        # setup timezone, start_time, duration
        $_POST['jos_emundus_jury___timezone'] = $offset;
        $_POST['jos_emundus_jury___start_time'] = $startTimeCELSA;
        # $_POST['jos_emundus_jury___duration'] = $durationCELSA;

        # --- END CONFIG START TIME, END TIME, DURATION, TIMEZONE --- #

        $json = $this->dataMapping($_POST, 'jos_emundus_jury___', $json);

        # if meeting id (in db, not in Zoom) and meeting session do not exist, call endpoint to generate the new one
        if(empty($_POST['jos_emundus_jury___id']) and empty($_POST['jos_emundus_jury___meeting_session'])) {
            $response = $zoom->doRequest('POST', '/users/'. $host_id .'/meetings', array(), array(), json_encode($json, JSON_PRETTY_PRINT));

            // echo '<pre>'; var_dump(json_encode($json, JSON_PRETTY_PRINT)); echo '</pre>'; die;
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
                                                " , duration = "        . $db->quote($response['duration']) .
                                                    " , join_url = "        . $db->quote($response['join_url']) .
                                                         " , registration_url = " . $db->quote($response['registration_url']) .
                                                            " , password = "        . $db->quote($response['password']) .
                                                                ", encrypted_password ="    . $db->quote($response['encrypted_password']) .
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
            } else {
                # be careful, each time the meeting room is updated, the start_url / join_url / registration / password / encrypted_password will be updated too. So, we need to get again the meeting by calling
                $response = $zoom->doRequest('GET', '/meetings/' . $_POST['jos_emundus_jury___meeting_session'], array(), array(), "");

                if($zoom->responseCode() != 200) {
                    $zoom->requestErrors();
                } else {
                    try {
                        # write update SQL query
                        $updateSql = "UPDATE #__emundus_jury
                                        SET visio_link = "          . $db->quote($response['start_url']) .
                                            ", join_url = "             . $db->quote($response['join_url']) .
                                                ", registration_url ="      . $db->quote($response['registration_url']) .
                                                    ", password ="              . $db->quote($response['password']) .
                                                        ", encrypted_password ="    . $db->quote($response['encrypted_password']) .
                                                            " WHERE #__emundus_jury.id = " . $_POST['jos_emundus_jury___id'] .
                                                                " AND #__emundus_jury.meeting_session LIKE (" . $_POST['jos_emundus_jury___meeting_session'] . ")";

                        $db->setQuery($updateSql);
                        $db->execute();
                    } catch(Exception $e) {
                        JLog::add('Update Zoom meeting failed : ' . $e->getMessage(),JLog::ERROR, 'com_emundus');
                    }
                }
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