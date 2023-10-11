<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';
require_once JPATH_ROOT . '/components/com_emundus/classes/api/Zoom.php';

/**
* Create a Joomla user from the forms data
*
* @package     Joomla.Plugin
* @subpackage  Fabrik.form.juseremundus
* @since       3.0
*/

$GLOBAL_ZOOM_SESSION = array(
    'NAME' => '',
    'START_TIME' => ''
);

class PlgFabrik_FormEmunduszoommeeting extends plgFabrik_Form {

    public function getParam($pname, $default = '') {
        $params = $this->getParams();

        if ($params->get($pname) == '') {
            return $default;
        }

        return $params->get($pname);
    }


    public function searchSubArray(Array $array, $key): array {
        foreach ($array as $index => $subarray){
            if (isset($subarray[$key])) {
                return array('parent' => $index, 'status' => true);
            }
        }

        return array('parent' => null, 'status' => false);
    }

    /**
        * onBeforeProcess ==> get the previous data
    */
    public function onBeforeLoad() {
        $rowId = $this->getModel()->getRowId();

        if (!empty($rowId)) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            # get the previous data of ZOOM meeting
            $query->select('jej.*, jejrj.user')
                ->from($db->quoteName('#__emundus_jury', 'jej'))
                ->leftJoin($db->quoteName('#__emundus_jury_repeat_jury', 'jejrj') . ' ON ' . $db->quoteName('jej.id') . ' = ' . $db->quoteName('jejrj.parent_id'))
                ->where('jej.id = ' . $db->quote($rowId));

            $db->setQuery($query);
            $raw = $db->loadObjectList();

            # create session
            $session = JFactory::getSession();
            $zoomSession = new stdClass();

            $zoomSession->ZOOM_SESSION_NAME = current($raw)->topic;
            $zoomSession->ZOOM_SESSION_START_TIME = current($raw)->start_time_;
            $zoomSession->ZOOM_SESSION_JURY = [];

            foreach($raw as $jr) {
                $zoomSession->ZOOM_SESSION_JURY[] = $jr->user;
            }

            # set "emunusZoomSession" session
            $session->set('emundusZoomSession', $zoomSession);
        }
    }


    /**
        * onAfterProcess ==> create new Zoom meeting
        * onAfterProcess ==> update an existing Zoom meeting
        * creator: eMundus
    */
    public function onAfterProcess() {
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $hosts = filter_input(INPUT_POST, 'jos_emundus_jury___president', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $topic = filter_input(INPUT_POST, 'jos_emundus_jury___topic', FILTER_SANITIZE_STRING);
        $jury_id = filter_input(INPUT_POST, 'jos_emundus_jury___id', FILTER_SANITIZE_STRING);
        $meeting_session = filter_input(INPUT_POST, 'jos_emundus_jury___meeting_session', FILTER_SANITIZE_STRING);

        if (empty($hosts)) {
            return false;
        }

        # get host (hosts -> users appear in table "data_referentiel_jury_token")
        $host = current($hosts);

        $zoomSession = JFactory::getSession()->get('emundusZoomSession');

        # this flag (true,false) indicates which email type will be sent (creation, update)
        $send_first_email_flag = false;

        # get two types of email
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $creationEmail = $this->getParam('emunduszoommeeting_first_email_to_send', null);
        $updateEmail = $this->getParam('emunduszoommeeting_secondary_email_to_send', null);

        # get the creator
        $creator = JFactory::getUser();

        # read and parse json template file
        $route = JPATH_BASE.'/plugins/fabrik_form/emunduszoommeeting/api_templates' . DS;
        $template = file_get_contents($route . __FUNCTION__ . '_meeting.json');
        $json = json_decode($template, true);

        $zoom = new Zoom();

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        # create meeting room host
        # get firstname, lastname, email of #host
        $query->select('jeu.firstname, jeu.lastname, ju.email')
            ->from($db->quoteName('#__users', 'ju'))
            ->leftJoin($db->quoteName('#__emundus_users', 'jeu') . ' ON ' . $db->quoteName('ju.id') . ' = ' . $db->quoteName('jeu.user_id'))
            ->where($db->quoteName('ju.id') . ' = ' . $db->quote($host));

        $db->setQuery($query);
        $raw = $db->loadObject();
        $host_id = 0;
        $host_first_name = '';
        $host_last_name = '';

        if (!empty($raw)) {
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
			$response = $zoom->createUser([
				'email' => $raw->email,
				'first_name' => $raw->firstname,
				'last_name' => $raw->lastname
			]);

            # HTTP status = 201 :: User created
            if (!empty($response) && $response['code'] == 201) {
                # get host id
                $host_id = $response['id'];
                $host_last_name = $response['last_name'];
                $host_first_name = $response['first_name'];
            } else {
                $uzId = $host;

                if ($response['code'] == 1005) {
                    # User already exist :: update the user settings except the firstname, lastname, email

                    # find the president email
                    $query->clear()
                        ->select('DISTINCT(email)')
                        ->from($db->quoteName('#__users', 'ju'))
                        ->leftJoin($db->quoteName('#__emundus_jury', 'jej') . ' ON ju.id = jej.president')
                        ->where('jej.president = ' . $db->quote($uzId));

                    $db->setQuery($query);
                    $email = $db->loadResult();

                    if (!empty($email)) {
                        # get the user id by email
	                    $response = $zoom->getUserById($email);

                        # get the Zoom user id
                        $host_id = $response['id'];

                        $host_last_name = $response['last_name'];
                        $host_first_name = $response['first_name'];
                    }
                }
            }
        }

        if (empty($host_id)) {
            // TODO: handle empty host_id
        }

        #right now, we have $host_id

        # --- BEGIN CONFIG START TIME, END TIME, DURATION, TIMEZONE --- #
        $juryStartDate = $jinput->getString('jos_emundus_jury___start_time_', '');
        if (empty($juryStartDate)) {
            // TODO: handle empty jos_emundus_jury___start_time_
        }


        $offset = $app->get('offset', 'UTC');

        # in case of CELSA, the meeting session will start 15 min before
        $startTimeCELSA = !empty($juryStartDate) ? gmdate('Y-m-d\TH:i:s\Z', strtotime($juryStartDate) - (15 * 60)) : gmdate('Y-m-d\TH:i:s\Z');

        ######################################################################################################################

        # setup timezone, start_time, duration
        $_POST['jos_emundus_jury___timezone'] = $offset;
        $_POST['jos_emundus_jury___start_time'] = $startTimeCELSA;

        # --- END CONFIG START TIME, END TIME, DURATION, TIMEZONE --- #

        # IF THE MEETING NAME IS MISSED, WE SET THE DEFAULT NAME BY FORMAT "Session {{start_time_just_date}} {{Number#Integer}} {{Meeting_Host}}"
        if (empty($topic)) {
            # get date from $startTimeCElSA
            $_date = date('d/m/Y', strtotime($startTimeCELSA));
            $db_date = date('Y-m-d', strtotime($startTimeCELSA));

            # find if this host already has another meeting in $_date
            $query->clear()
                ->select("COUNT(*)")
                ->from($db->quoteName('#__emundus_jury', 'jej'))
                ->where('jej.president = ' . $db->quote($host))
                ->andWhere('DATE(start_time_) = ' . $db->quote($db_date));

            $db->setQuery($query);
            $count = $db->loadResult();

            # set the default name for meeting
            $topic = JText::_('COM_EMUNDUS_ZOOM_SESSION_DEFAULT_NAME') . ' ' . $_date . ' ' . $count . ' ' . $raw->firstname;
            $_POST['jos_emundus_jury___topic'] = $topic;
        }

        $json = $this->dataMapping($_POST, 'jos_emundus_jury___', $json);

        # if meeting id (in db, not in Zoom) and meeting session do not exist, call endpoint to generate the new one
        $start_url = '';
        $join_url = '';
        $jid = '';

        if (empty($jury_id) && empty($meeting_session)) {
            $response = $zoom->createMeeting($host_id, $json);

            if($response['code'] == 201) {
                $send_first_email_flag = true;

                # get last insert id
                try {
                    $query->clear()
                        ->select('MAX(id)')
                        ->from($db->quoteName('#__emundus_jury'));

                    $db->setQuery($query);
                    $lid = $db->loadResult();

                    # update missing fields to table "jos_emundus_jury"
                    $query->clear()
                        ->update($db->quoteName('#__emundus_jury'))
                        ->set($db->quoteName('meeting_session') . ' = ' . $db->quote($response['id']))
                        ->set($db->quoteName('visio_link') . ' = ' . $db->quote($response['start_url']))
                        ->set($db->quoteName('duration') . ' = ' . $db->quote($response['duration']))
                        ->set($db->quoteName('join_url') . ' = ' . $db->quote($response['join_url']))
                        ->set($db->quoteName('registration_url') . ' = ' . $db->quote($response['registration_url']))
                        ->set($db->quoteName('password') . ' = ' . $db->quote($response['password']))
                        ->set($db->quoteName('encrypted_password') . ' = ' . $db->quote($response['encrypted_password']))
                        ->set($db->quoteName('user') . ' = ' . $db->quote($creator->id))
                        ->set($db->quoteName('date_time') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
                        ->set($db->quoteName('end_time_') . ' = ' . $db->quote($_POST["jos_emundus_jury___end_time_"]['date']))
                        ->set($db->quoteName('topic') . ' = ' . $db->quote($topic))
                        ->where($db->quoteName('id') . ' = ' . $lid);

                    $db->setQuery($query);
                    $db->execute();

                    # get jos_emundus_jury.id
                    $jid = $lid;

                    $start_url = $response['start_url'];
                    $join_url = $response['join_url'];

                    # set email body (creation)
                    $post = [
                        'ZOOM_SESSION_NAME' => $response['topic'],
                        'ZOOM_SESSION_START_TIME' => date("d/m/Y H\hi", strtotime($response['start_time'])),       # convert UTC time to local time
                        'ZOOM_SESSION_UPDATE_TIME' => date('d/m/Y H\hi'),
                        'ZOOM_SESSION_HOST' => $host_first_name . ' ' . $host_last_name
                    ];
                } catch(Exception $e) {
                    JLog::add('Create Zoom meeting failed : ' . $e->getMessage(),JLog::ERROR, 'com_emundus');
                }
            }
        } else {
	        $response = $zoom->updateMeeting($meeting_session, $json);

            if ($response['code'] != 204) {
                //$zoom->requestErrors();
            } else {
                # be careful, each time the meeting room is updated, the start_url / join_url / registration / password / encrypted_password will be updated too. So, we need to get again the meeting by calling
                $response = $zoom->getMeeting($meeting_session);

                if ($response['code'] != 200) {
                    //$zoom->requestErrors();
                } else {
                    try {
                        $query->clear()
                            ->update($db->quoteName('#__emundus_jury'))
                            ->set($db->quoteName('visio_link') . ' = ' . $db->quote($response['start_url']))
                            ->set($db->quoteName('join_url') . ' = ' . $db->quote($response['join_url']))
                            ->set($db->quoteName('registration_url') . ' = ' . $db->quote($response['registration_url']))
                            ->set($db->quoteName('password') . ' = ' . $db->quote($response['password']))
                            ->set($db->quoteName('encrypted_password') . ' = ' . $db->quote($response['encrypted_password']))
                            ->set($db->quoteName('date_time') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
                            ->where($db->quoteName('id') . ' = ' . $jury_id)
                            ->andWhere($db->quoteName('meeting_session') . ' LIKE (' . $meeting_session . ')');

                        $db->setQuery($query);
                        $db->execute();

                        $send_first_email_flag = false;

                        $jid = $jury_id;

                        # get the start_url from $response
                        $start_url = $response['start_url'];

                        # get the join_url from $response
                        $join_url = $response['join_url'];

                        # set email content (update)
                        $post = [
                            'ZOOM_SESSION_PREVIOUS_NAME' => '<span style="font-weight: normal;text-decoration: line-through">' . $zoomSession->ZOOM_SESSION_NAME . '</span>',
                            'ZOOM_SESSION_NAME' => '<span style="font-weight: normal">' . $response['topic'] . '</span>',
                            'ZOOM_SESSION_PREVIOUS_START_TIME' => '<span style="font-weight: normal;text-decoration: line-through">' . $zoomSession->ZOOM_SESSION_START_TIME . '</span>',
                            'ZOOM_SESSION_START_TIME' => '<span style="font-weight: normal">' . $juryStartDate . '</span>',
                            'ZOOM_SESSION_UPDATE_TIME' => date('d/m/Y H\hi'),
                            'ZOOM_SESSION_HOST' => $host_first_name . ' ' . $host_last_name
                        ];
                    } catch(Exception $e) {
                        JLog::add('Update Zoom meeting failed : ' . $e->getMessage(),JLog::ERROR, 'com_emundus');
                    }
                }
            }
        }

        # select which email will be sent by $send_first_email_flag (true, false)
        if ($send_first_email_flag === true) {
            $email_template = intval($creationEmail);
            $app->enqueueMessage(JText::_('ZOOM_SESSION_CREATED_SUCCESSFULLY'), 'success');
        } else {
            $email_template = intval($updateEmail);
            $app->enqueueMessage(JText::_('ZOOM_SESSION_UPDATED_SUCCESSFULLY'), 'success');
        }

        # get "creator" of Zoom meeting
        $query->clear()
            ->select('ju.email, ju.name')
            ->from($db->quoteName('#__users', 'ju'))
            ->leftJoin($db->quoteName('#__emundus_jury', 'jej') . ' ON ju.id = jej.user OR ju.id = jej.president')
            ->where($db->quoteName('jej.id') . ' = ' . $db->quote($jid));

        $db->setQuery($query);
        $raws = $db->loadObjectList();

        # get all evaluators of Zoom meeting
        $query->clear()
            ->select('ju.id, ju.name, ju.email')
            ->from($db->quoteName('#__users', 'ju'))
            ->leftJoin($db->quoteName('#__emundus_jury_repeat_jury', 'jejrj') . ' ON ju.id = jejrj.user')
            ->where($db->quoteName('jejrj.parent_id') . ' = ' . $db->quote($jid));

        $db->setQuery($query);

        $evaluators = $db->loadObjectList();
        $evaluatorsIds = [];

        foreach($evaluators as $eval) { $evaluatorsIds[] = $eval->id; }

        # now, we compare the latest juries and the newest juries
        $lastJuries = $zoomSession->ZOOM_SESSION_JURY;

        # now, we get the differences between last juries and newest juries
        $juriesDiff = array_diff($lastJuries,$evaluatorsIds);

        # add list of evaluators to $post
        $post['ZOOM_SESSION_JURY'] = '<ul>';

        if($send_first_email_flag === false) {
            if (empty($lastJuries) or is_null(current($lastJuries))) {
                $post['ZOOM_SESSION_JURY'] .= '<div style="color:red;text-decoration: line-through"><li>' . JText::_('COM_EMUNDUS_ZOOM_SESSION_NO_JURY') . "</li></div>";
            } else {
                # get info of $juriesDiff
                $query->clear()
                    ->select('ju.name')
                    ->from($db->quoteName('#__users', 'ju'))
                    ->where($db->quoteName('ju.id') . ' IN (' . implode(',', $lastJuries) . ')');

                $db->setQuery($query);
                $jrs = $db->loadColumn();

                foreach ($jrs as $jr) {
                    $post['ZOOM_SESSION_JURY'] .= '<div style="text-decoration: line-through"><li>' . $jr . '</li></div>';
                }
            }
        }

        if (count($evaluators) >= 1) {
            # grab all new evaluators of this Zoom meeting
            foreach ($evaluators as $eval) {
                $post['ZOOM_SESSION_JURY'] .= '<div><li>' . $eval->name . '</li></div>';
            }
        } else {
            $post['ZOOM_SESSION_JURY'] .= '<div style="color:red"><li>' . JText::_('COM_EMUNDUS_ZOOM_SESSION_NO_JURY') . "</li></div>";
        }

        $post['ZOOM_SESSION_JURY'] .= '</ul>';

        if ($email_template !== 0) {
            $this->sendMailToReceivers($email_template, $raws, $evaluators, $post, $start_url, $join_url);
        }
    }

    private function sendMailToReceivers($email_template, $recipients, $evaluators, $post, $start_url, $join_url) {
        # send email # call the 'messages' controllers
        require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'controllers' . DS . 'messages.php');
        $cMessages = new EmundusControllerMessages;

        # send email to Coordinator + Host with start_url ✅ ✅ ✅
        foreach ($recipients as $recipient) {
            # add NAME to $post
            $post['NAME'] = $recipient->name;

            # add START_URL to $post
            $post['ZOOM_SESSION_URL'] = '<a href="' . $start_url . '" target="_blank">' . JText::_('COM_EMUNDUS_ZOOM_SESSION_LABEL_HOST') . '</a>';

            # add PROFILE to $post
            $post['ZOOM_SESSION_PROFILE'] = JText::_('COM_EMUNDUS_ZOOM_SESSION_LABEL_HOST_PROFILE');

            # call to method 'sendEmailNoFnum'
            $cMessages->sendEmailNoFnum($recipient->email, $email_template, $post, null, array(), null);
        }

        # send email to all Evaluators with join_url ✅ ✅ ✅
        foreach ($evaluators as $evaluator) {
            # add NAME to $post
            $post['NAME'] = $evaluator->name;

            # add JOIN_URL to $post
            $post['ZOOM_SESSION_URL'] = '<a href="' . $join_url . '" target="_blank">' . JText::_('COM_EMUNDUS_ZOOM_SESSION_LABEL_PARTICIPANT') . '</a>';

            # add PROFILE to $post
            $post['ZOOM_SESSION_PROFILE'] = JText::_('COM_EMUNDUS_ZOOM_SESSION_LABEL_PARTICIPANT_PROFILE');

            # call to method 'sendEmailNoFnum'
            $cMessages->sendEmailNoFnum($evaluator->email, $email_template, $post, null, array(), null);
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
    public function dataMapping($input, $separator, $output): array
    {
        foreach ($input as $key => $post) {
            $exploded_key = explode($separator, $key);
            $suff = !empty($exploded_key) ? $exploded_key[1] : null;

            if (!empty($suff)) {
                if (array_key_exists($suff, $output)) {
                    if (is_array($post) && sizeof($post) == 1) {
                        $post = current($post);
                    }
                    $output[$suff] = $post;
                } else {
                    if ($this->searchSubArray($output, $suff)['status'] === true) {
                        $parentKey = $this->searchSubArray($output, $suff)['parent'];
                        if (is_array($post) && sizeof($post) == 1) {
                            $post = current($post);
                        }
                        $output[$parentKey][$suff] = $post;
                    }
                }
            }
        }

        return $output;
    }
}
