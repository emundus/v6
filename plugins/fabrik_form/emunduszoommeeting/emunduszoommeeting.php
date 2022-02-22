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

    public function getParam($pname, $default = '') {
        $params = $this->getParams();

        if ($params->get($pname) == '') {
            return $default;
        }

        return $params->get($pname);
    }


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
        # this flag (true,false) indicates which email type will be sent (creation, update)
        $send_first_email_flag = false;

        # get two types of email
        $eMConfig = JComponentHelper::getParams('com_emundus');

        $creationEmail = $this->getParam('emunduszoommeeting_first_email_to_send', null);
        $updateEmail = $this->getParam('emunduszoommeeting_secondary_email_to_send', null);

        # end config email

        # get the creator
        $creator = JFactory::getUser();

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
                "last_name" => $raw->lastname
            ],
        ));

        # call to Zoom API endpoint
        $response = $zoom->doRequest('POST', '/users', array(), array(), $user);        /* array */

        # HTTP status = 201 :: User created
        if($zoom->responseCode() == 201) {
            # get host id
            $host_id = $response['id'];
        } else {
            $uzId = $host;
            
            if($response['code'] == 1005) {
                # User already exist :: update the user settings except the firstname, lastname, email

                # find the president email
                $getUserSql = "select distinct(email) from jos_users as ju left join jos_emundus_jury as jej on ju.id = jej.president where jej.president = " . $db->quote($uzId);
                $db->setQuery($getUserSql);
                $email = $db->loadResult();

                # get the user id by email
                $response = $zoom->doRequest('GET', '/users/' . $email, array(), array(), '');

                # get the Zoom user id
                $host_id = $response['id'];
            } else {
                $zoom->requestErrors();
            }
        }

//        echo '<pre>'; var_dump($host_id); echo '</pre>'; die;
        
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
            
            $httpCode = $zoom->responseCode();

            if($httpCode == 201) {
                $send_first_email_flag = true;

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
                                                                    ", user = "                 . $db->quote($creator->id) .
                                                                        ", date_time = "            . $db->quote(date('Y-m-d H:i:s')) .
                                                                            " WHERE #__emundus_jury.id = " . $lid;
                    
                    $db->setQuery($updateSql);
                    $db->execute();

                    # get jos_emundus_jury.id
                    $jid = $lid;

                    # get the start_url from $response
                    $start_url = $response['start_url'];

                    # get the join_url from $response
                    $join_url = $response['join_url'];

                    # set email body (creation)
                    $post = [
                        'ZOOM_SESSION_NAME' => $response['topic'],
                        'ZOOM_SESSION_START_TIME' => date("Y-m-d H:i:s", strtotime($response['start_time'])),       # convert UTC time to local time
                        'ZOOM_SESSION_UPDATE_TIME' => date('Y-m-d H:i:s')
                    ];
                } catch(Exception $e) {
                    JLog::add('Create Zoom meeting failed : ' . $e->getMessage(),JLog::ERROR, 'com_emundus');
                }
            } else {
                $zoom->requestErrors();
            }
        } else {
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
                                                            ", date_time = "            . $db->quote(date('Y-m-d H:i:s')) .
                                                                " WHERE #__emundus_jury.id = " . $_POST['jos_emundus_jury___id'] .
                                                                    " AND #__emundus_jury.meeting_session LIKE (" . $_POST['jos_emundus_jury___meeting_session'] . ")";

                        $db->setQuery($updateSql);
                        $db->execute();

                        $send_first_email_flag = false;

                        $jid = $_POST['jos_emundus_jury___id'];

                        # get the start_url from $response
                        $start_url = $response['start_url'];

                        # get the join_url from $response
                        $join_url = $response['join_url'];

                        # set email content (update)
                        $post = [
                            'ZOOM_SESSION_NAME' => $response['topic'],
                            'ZOOM_SESSION_START_TIME' => date("Y-m-d H:i:s", strtotime($response['start_time'])),   # convert UTC time to local time
                            'ZOOM_SESSION_UPDATE_TIME' => $created_at = date('Y-m-d H:i:s')
                        ];
                    } catch(Exception $e) {
                        JLog::add('Update Zoom meeting failed : ' . $e->getMessage(),JLog::ERROR, 'com_emundus');
                    }
                }
            }
        }

        # send email # call the 'messages' controllers
        require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'controllers' . DS . 'messages.php');
        $cMessages = new EmundusControllerMessages;

        # select which email will be sent by $send_first_email_flag (true, false)
        if ($send_first_email_flag === true) {
            $email_template = intval($creationEmail);
        } else {
            $email_template = intval($updateEmail);
        }

        # get "creator" of Zoom meeting
        $getCreatorSql = "select ju.email, ju.name from jos_users as ju left join jos_emundus_jury jej on ju.id = jej.user or ju.id = jej.president where jej.id = " . $db->quote($jid);
        $db->setQuery($getCreatorSql);
        $raws = $db->loadObjectList();

        # get all evaluators of Zoom meeting
        $getEvaluatorsSql = "select ju.email, ju.name from jos_users as ju left join jos_emundus_jury_repeat_jury as jejrj on ju.id = jejrj.user where jejrj.parent_id = " . $db->quote($jid);
        $db->setQuery($getEvaluatorsSql);
        $evaluators = $db->loadObjectList();

        if(count($evaluators) >= 1) {
            # add list of evaluators to $post
            $post['ZOOM_SESSION_JURY'] = '<ul>';

            # grab all evaluator of this Zoom meeting
            foreach ($evaluators as $eval) { $post['ZOOM_SESSION_JURY'] .= '<li>' . $eval->name . '</li>'; }

        } else {
            $post['ZOOM_SESSION_JURY'] = '<p style="color:red">' . JText::_('COM_EMUNDUS_ZOOM_SESSION_NO_JURY') . "</p>";
        }

        $post['ZOOM_SESSION_JURY'] .= '</ul>';

        # send email to Coordinator + Host with start_url ✅ ✅ ✅
        foreach ($raws as $recipient) {
            # add NAME to $post
            $post['NAME'] = $recipient->name;

            # add START_URL to $post
            $post['ZOOM_SESSION_URL'] = '<a href="' . $start_url . '" target="_blank">' .  JText::_('COM_EMUNDUS_ZOOM_SESSION_LABEL_HOST') . '</a>';

            # add PROFILE to $post
            $post['ZOOM_SESSION_PROFILE'] = JText::_('COM_EMUNDUS_ZOOM_SESSION_LABEL_HOST_PROFILE');

            # call to method 'sendEmailNoFnum'
            $cMessages->sendEmailNoFnum($recipient->email, $email_template, $post, null ,array(), null);
        }

        # send email to all Evaluators with join_url ✅ ✅ ✅
        foreach ($evaluators as $evaluator) {
            # add NAME to $post
            $post['NAME'] = $evaluator->name;

            # add JOIN_URL to $post
            $post['ZOOM_SESSION_URL'] = '<a href="' . $join_url . '" target="_blank">' .  JText::_('COM_EMUNDUS_ZOOM_SESSION_LABEL_PARTICIPANT') . '</a>';

            # add PROFILE to $post
            $post['ZOOM_SESSION_PROFILE'] = JText::_('COM_EMUNDUS_ZOOM_SESSION_LABEL_PARTICIPANT_PROFILE');

            # call to method 'sendEmailNoFnum'
            $cMessages->sendEmailNoFnum($evaluator->email, $email_template, $post, null ,array(), null);
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