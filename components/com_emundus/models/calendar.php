<?php
require_once JPATH_LIBRARIES.DS.'php-google-api-client'.DS.'vendor'.DS.'autoload.php';

JLoader::import('components.com_dpcalendar.libraries.dpcalendar.syncplugin', JPATH_ADMINISTRATOR);
JPluginHelper::importPlugin( 'dpcalendar' );

require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
define('APPLICATION_NAME', 'Google Calendar API PHP Emundus');
define('CREDENTIALS_PATH', JPATH_LIBRARIES.DS.'php-google-api-client'.DS.'credentials'.DS.'calendar-php-quickstart.json');
define('CLIENT_SECRET_PATH', JPATH_LIBRARIES.DS.'php-google-api-client'.DS.'certificates'.DS.'client_secret.json');

// If modifying these scopes, delete your previously saved credentials
// at __DIR__ . '/credentials/calendar-php-quickstart.json
define('SCOPES', implode(' ', array(
    Google_Service_Calendar::CALENDAR) // CALENDAR_READONLY
));


class EmundusModelCalendar extends JModelLegacy {


    // Uses Google API credentials to initialize a google service object.
    public function google_authenticate($clientID, $clientSecret, $refreshToken) {


        if (!isset($refreshToken) && empty($refreshToken)) {
            $this->authenticateClient();
            $eMConfig = JComponentHelper::getParams('com_emundus');
            $refreshToken = $eMConfig->get('refreshToken');
        }

        // Get the API client and construct the service object.
        $client = $this->getClient($clientID, $clientSecret);
        $client->refreshToken($refreshToken);
        return new Google_Service_Calendar($client);

    }

    // Creates a google calendar on Google Agenda
    public function google_add_calendar($google_api_service, $title) {

        // Creates a new google calendar using the API.
        $calendar = new Google_Service_Calendar_Calendar();
        $calendar->setSummary($title);
        $calendar->setTimeZone('Europe/Paris');
        $createdCalendar = $google_api_service->calendars->insert($calendar);

        return $createdCalendar->getId();
    }

    // Creates a local calendar to be managed by dpCalendar.
    public function dpcalendar_add_calendar($title, $alias, $color, $googleId, $parentId, $program) {

        $db = JFactory::getDbo();

        // in order to get the path we need the name of the parent calendar
        // This is because the path is written as parentAlias/calAlias
        try {

            $db->setQuery("SELECT alias FROM #__categories WHERE id = ".$parentId);
            $parentAlias = $db->loadResult();

        } catch (Exception $e) {
            die($e->getMessage());
        }

        $category_data['id'] = 0;
        $category_data['parent_id'] = (int)$parentId;
        $category_data['title'] = $title;
        $category_data['alias'] = $alias;
        $category_data['path'] = $parentAlias."/".$alias;
        $category_data['extension'] = 'com_dpcalendar';
        $category_data['published'] = 1;
        $category_data['language'] = '*';
        $category_data['params'] = array(
            'category_layout' => '',
            'image' => '',
            'color' => $color,
            'etag' => '1',
            'program' => $program,
            'googleId' => $googleId
        );
        $category_data['metadata'] = array(
            'author' => '',
            'robots' => ''
        );

        return $this->createCategory($category_data);

    }

    public function dpcalendar_confirm_interview($event_id, $user_id, $fnum, $contact_info = null) {

        $db = Jfactory::getDbo();
        $user = JFactory::getUser($user_id);

        $eMConfig = JComponentHelper::getParams('com_emundus');
        $booked_prefix = $eMConfig->get('bookedPrefix');

        $m_emails = new EmundusModelEmails;

        $post = array(
            'USER_NAME'     => $user->name,
            'USER_EMAIL'    => $user->email
        );

        // An array containing the tag names is created.
        $tags = $m_emails->setTags($user->id, $post, null, '', $booked_prefix);

        $booked_prefix = preg_replace($tags['patterns'], $tags['replacements'], $booked_prefix);

        // Get event
        try {

            $db->setQuery("SELECT * FROM #__dpcalendar_events WHERE id = ".$event_id);
            $event = $db->loadObject();

        } catch (Exception $e) {
            JLog::add("User: ".$user_id.' SQL Error: '.$e->getMessage(), JLog::ERROR, 'com_emundus');
            die(json_encode(['status' => false]));
        }

        if ($event->booking_information != '' && $event->capacity_used == 1) {
            die(json_encode(['status' => false, 'message' => 'Event already booked, please refresh the page.']));
        }

        $event->title = $booked_prefix.' - '.$event->title;
        $event->description = $user->name;
        $event->booking_information = $user->id;
        $event->capacity = '1';
        $event->capacity_used = '1';

        if (isset($contact_info)) {
            foreach ($contact_info as $type => $info) {
                $event->description .= ' <br> contact '.$type.' : '.$info;
            }
        }

        // Update dpcalendar event object.
        try {

            $query = "UPDATE #__dpcalendar_events ";
            $query .= "SET title = ".$db->quote($event->title).", description = ".$db->quote($event->description).", booking_information = ".$db->quote($event->booking_information).", capacity = ".$db->quote($event->capacity).", capacity_used = ".$db->quote($event->capacity_used);
            $query .= " WHERE id = ".$event_id;

            $db->setQuery($query);
            $db->execute();

        } catch (Exception $e) {
            JLog::add("User: ".$user_id." SQL Query: ".$query." SQL Error: ".$e->getMessage(), JLog::ERROR, "com_emundus");
            die(json_encode(['status' => false]));
        }

        $values = array();
        $values[] = $db->quote("0");            // booking_id
        $values[] = $db->quote($event->id);     // event_id
        $values[] = $db->quote($user->id);      // user_id
        $values[] = $db->quote("0");            // uid
        $values[] = $db->quote($user->email);   // email
        $values[] = $db->quote($user->name);    // name
        $values[] = $db->quote(" ");            // country
        $values[] = $db->quote(" ");            // province
        $values[] = $db->quote(" ");            // city
        $values[] = $db->quote(" ");            // zip
        $values[] = $db->quote(" ");            // street
        $values[] = $db->quote(" ");            // number
        $values[] = $db->quote(date("Y-m-d H:i:s"));


        try {

            $db->setQuery("INSERT INTO #__dpcalendar_tickets (booking_id, event_id, user_id, uid, email, name, country, province, city, zip, street, number, created) VALUES (".implode(",",$values).")");
            $db->execute();

        } catch (Exception $e) {
            JLog::add("User: ".$user_id." SQL Error: ".$e->getMessage(), JLog::ERROR, "com_emundus");
            die(json_encode(['status' => false]));
        }

        return $event;

    }

    public function google_add_event($google_api_service, $event) {

        $db = JFactory::getDbo();
        // Get google calendar id by getting the params from the calendar the event is attached to.
        try {

            $db->setQuery("SELECT params FROM #__categories WHERE id = ".$event->catid);
            $dpcalendar_params = $db->loadResult();

        } catch (Exception $e) {
            JLog::add("SQL Error: ".$e->getMessage(), JLog::ERROR, "com_emundus");
            die(json_encode(['status' => false]));
        }


        $dpcalendar_params = json_decode($dpcalendar_params);
        $google_calendar_id = $dpcalendar_params->googleId;


        // Convert event date to format for google calendar
        $offset     = JFactory::getConfig()->get('offset');

        $start_dt   = new DateTime($event->start_date, new DateTimeZone('GMT'));
        $start_dt->setTimezone(new DateTimeZone($offset));
        $start_date = $start_dt->format('Y-m-d');
        $start_time = $start_dt->format('H:i:s');

        $end_dt     = new DateTime($event->end_date, new DateTimeZone('GMT'));
        $end_dt->setTimezone(new DateTimeZone($offset));
        $end_date   = $end_dt->format('Y-m-d');
        $end_time   = $end_dt->format('H:i:s');


        // Build event object for Google.
        $google_event = new Google_Service_Calendar_Event([
            'summary'       => $event->title,
            'description'   => $event->description,
            'start' => [
                'dateTime'  => $start_date.'T'.$start_time,
                'timeZone'  => 'Europe/Paris',
            ],
            'end' => [
                'dateTime'  => $end_date.'T'.$end_time,
                'timeZone'  => 'Europe/Paris',
            ],
        ]);

        $result = $google_api_service->events->insert($google_calendar_id, $google_event);

        try {

            $db->setquery("UPDATE #__dpcalendar_events SET params = ".$db->Quote(json_encode(["googleEventId" => $result->id]))." WHERE id = ".$event->id);
            $db->execute();

        } catch (Exception $e) {
            JLog::add("SQL Error: ".$e->getMessage(), JLog::ERROR, "com_emundus");
            die(json_encode(['status' => false]));
        }

        if (isset($result)) {
	        return true;
        } else {
	        return false;
        }

    }

    public function dpcalendar_delete_interview($event_id) {

        $db = JFactory::getDbo();
        $user = JFactory::getSession()->get('emundusUser');

        try {

            // Here we are going to update the ticket to show it is no longer valid.
            $db->setQuery("UPDATE #__dpcalendar_tickets SET state = 1 WHERE event_id = ". $event_id);
            $db->execute();

        } catch (Exception $e) {
            JLog::add("SQL Error: ".$e->getMessage(), JLog::ERROR, "com_emundus");
            die(json_encode(['status' => false]));
        }

        // Get event
        try {

            $db->setQuery("SELECT title FROM #__dpcalendar_events WHERE id = ".$event_id);
            $title = $db->loadResult();

        } catch (Exception $e) {
            JLog::add("SQL Error: ".$e->getMessage(), JLog::ERROR, "com_emundus");
            die(json_encode(['status' => false]));
        }

        $post = array(
            'USER_NAME'     => $user->name,
            'USER_EMAIL'    => $user->email
        );

        $eMConfig = JComponentHelper::getParams('com_emundus');
        $booked_prefix = $eMConfig->get('bookedPrefix');

        $m_emails = new EmundusModelEmails;

        // An array containing the tag names is created.
        $tags = $m_emails->setTags($user->id, $post, null, '', $booked_prefix);

        $booked_prefix = preg_replace($tags['patterns'], $tags['replacements'], $booked_prefix);

        $title = str_replace($booked_prefix." - ", "", $title);

        try {

            $query = "UPDATE #__dpcalendar_events ";
            $query .= "SET title = ".$db->Quote($title).", description = ".$db->Quote("").", booking_information = ".$db->Quote("").", capacity_used = ".$db->Quote("0");
            $query .= " WHERE  id = ".$event_id;

            $db->setQuery($query);
            $db->execute();

        } catch (Exception $e) {
            JLog::add("SQL Query: ".$query." SQL Error: ".$e->getMessage(), JLog::ERROR, "com_emundus");
            die(json_encode(['status' => false]));
        }

        return true;

    }

    public function google_delete_event($event_id, $google_api_service) {

        $db = JFactory::getDbo();

        // First we need to get the calendar ID
        try {

            $db->setQuery("SELECT params FROM #__categories WHERE id = (SELECT catid FROM #__dpcalendar_events WHERE id = ".$event_id.")");
            $params = $db->loadResult();

        } catch (Exception $e) {
            JLog::add("SQL Error: ".$e->getMessage(), JLog::ERROR, "com_emundus");
            die(json_encode(['status' => false]));
        }

        $dpcalendar_params = json_decode($params);
        $google_calendar_id = $dpcalendar_params->googleId;

        // then we need the Google event ID from the dpcalndar events table
        try {

            $db->setquery("SELECT params FROM #__dpcalendar_events WHERE id = ".$event_id);
            $params = $db->loadResult();

        } catch (Exception $e) {
            JLog::add("SQL Error: ".$e->getMessage(), JLog::ERROR, "com_emundus");
            die(json_encode(['status' => false]));
        }

        $event_params = json_decode($params);
        $google_event_id = $event_params->googleEventId;

        $google_api_service->events->delete($google_calendar_id, $google_event_id);

    }

    public function email_event($event_id, $booked = null) {

        if (!isset($booked)) {
            return false;
        }

        $mailer = JFactory::getMailer();
        $config = JFactory::getConfig();
        $user   = JFactory::getSession()->get('emundusUser');
        $db     = JFactory::getDbo();

        require_once JPATH_ROOT . '/components/com_emundus/helpers/emails.php';
        $h_emails = new EmundusHelperEmails;
        if (!$h_emails->assertCanSendMailToUser($user->id)) {
            return false;
        }

        $m_emails = new EmundusModelEmails;

        try {

            $db->setQuery('SELECT * FROM #__dpcalendar_events WHERE id = '.$event_id);
            $event = $db->loadObject();

        } catch (Exception $e) {
            JLog::add("SQL Error: ".$e->getMessage(), JLog::ERROR, "com_emundus");
        }

        try {

            $db->setQuery('SELECT params, description, title FROM #__categories WHERE id = '.$event->catid);
            $category_dp = $db->loadObject();

        } catch (Exception $e) {
            JLog::add("SQL Error: ".$e->getMessage(), JLog::ERROR, "com_emundus");
        }

        $params = json_decode($category_dp->params);

        // Set event time to the correct timezone.
        $offset     = $config->get('offset');
        $event_dt   = new DateTime($event->start_date, new DateTimeZone('GMT'));
        $event_dt->setTimezone(new DateTimeZone($offset));
        $event_date = $event_dt->format('M j, Y');
        $event_time = $event_dt->format('g:i A');


        try {

            $db->setQuery('SELECT label FROM #__emundus_setup_programmes WHERE code LIKE '.$db->Quote($params->program));
            $label = $db->loadResult();

        } catch (Exception $e) {
            JLog::add("SQL Error: ".$e->getMessage(), JLog::ERROR, "com_emundus");
        }


        // The first thing to do is that we need to send a mail to the user that just decided to book the event.

        // The post variable allows us to insert data that will be transformed by the tags.
        // In this case we want the dates and times as well as programmes, the fnum is there possibly temporarly as we do not know whether is it used yet.

        $db->setQuery('SELECT title FROM #__categories WHERE id = '.$event->catid);
        $event_category_title = $db->loadResult();

        $post = array(
            'FNUM'          => $user->fnum,
            'EVENT_DATE'    => $event_date,
            'EVENT_TIME'    => $event_time,
            'USER_NAME'     => $user->name,
            'PROGRAM'       => $label,
            'JURY'          => $category_dp->title,
            'LINK'          => $category_dp->description
        );

        $from_id = 62;

        if ($booked) {
            $email = $m_emails->getEmail('booking_created_user');
        } else {
            $email = $m_emails->getEmail('booking_deleted_user');
        }

        // An array containing the tag names is created.
        $tags = $m_emails->setTags($user->id, $post, $user->fnum, '', $email->emailfrom . $email->subject . $email->message);

        // Tags are replaced with their corresponding values using the PHP preg_replace function.
        $subject = preg_replace($tags['patterns'], $tags['replacements'], $email->subject);
        $body = preg_replace($tags['patterns'], $tags['replacements'], $email->message);
        $body = $m_emails->setTagsFabrik($body, array($user->fnum));

        $body = preg_replace(array("/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"), array($subject, $body), $email->Template);

        $email_from_sys = $config->get('mailfrom');
        $email_from = preg_replace($tags['patterns'], $tags['replacements'], $email->emailfrom);

        if ($email->name === null) {
            $mail_from_name = $config->get('fromname');
        } else {
            $mail_from_name = $email->name;
        }

        // If the email sender has the same domain as the system sender address.
        if (!empty($email_from) && substr(strrchr($email_from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1)) {
            $mail_from_address = $email_from;
        } else {
            $mail_from_address = $email_from_sys;
        }

        // Set sender
        $sender = [
            $mail_from_address,
            $mail_from_name
        ];

        // Configure email sender
        $mailer->setSender($sender);
        $mailer->addReplyTo($email_from, $mail_from_name);
        $mailer->addRecipient($user->email);
        $mailer->setSubject($subject);
        $mailer->isHTML(true);
        $mailer->Encoding = 'base64';
        $mailer->setBody($body);

        // Send and log the email.
        $send = $mailer->Send();
        if ( $send !== true ) {
            echo 'Error sending email: ' . $send->__toString();
            JLog::add($send->__toString(), JLog::ERROR, 'com_emundus');
        } else {
            $message = array(
                'user_id_from' => $from_id,
                'user_id_to' => $user->id,
                'subject' => $subject,
                'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('COM_EMUNDUS_APPLICATION_SENT').' '.JText::_('COM_EMUNDUS_TO').' '.$user->email.'</i><br>'.$body
            );
            $m_emails->logEmail($message);
        }

        // Part two is sending the email to the coordinators.
        // We are using the program name to get the associated group and then getting all users inside of that group.
        $recipients = $this->getProgramRecipients($params->program);

        if ($booked) {
            $email = $m_emails->getEmail('booking_created_recipient');
        } else {
            $email = $m_emails->getEmail('booking_deleted_recipient');
        }

        if (isset($recipients) && is_array($recipients)) {
            foreach ($recipients as $recipient) {

                $post = array(
                    'FNUM'          => $user->fnum,
                    'EVENT_DATE'    => $event_date,
                    'EVENT_TIME'    => $event_time,
                    'USER_NAME'     => $user->name,
                    'RECIPIENT_NAME'=> $recipient->name,
                    'PROGRAM'       => $label
                );

                // An array containing the tag names is created.
                $tags = $m_emails->setTags($recipient->id, $post, $user->fnum, '', $email->emailfrom.$email->subject.$email->message);

                // Tags are replaced with their corresponding values using the PHP preg_replace function.
                $subject = preg_replace($tags['patterns'], $tags['replacements'], $email->subject);
                $body = preg_replace($tags['patterns'], $tags['replacements'], $email->message);
                $body = $m_emails->setTagsFabrik($body, array($user->fnum));

                $body = preg_replace(array("/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"), array($subject, $body), $email->Template);

                $email_from_sys = $config->get('mailfrom');
                $email_from = preg_replace($tags['patterns'], $tags['replacements'], $email->emailfrom);

                if ($email->name === null) {
                    $mail_from_name = $config->get('fromname');
                } else {
                    $mail_from_name = $email->name;
                }

                // If the email sender has the same domain as the system sender address.
                if (!empty($email_from) && substr(strrchr($email_from, "@"), 1) === substr(strrchr($email_from_sys, "@"), 1)) {
                    $mail_from_address = $email_from;
                } else {
                    $mail_from_address = $email_from_sys;
                }

                // Set sender
                $sender = [
                    $mail_from_address,
                    $mail_from_name
                ];

                // Configure email sender
                $mailer->ClearAllRecipients();
                $mailer->setSender($sender);
                $mailer->addReplyTo($email_from, $mail_from_name);
                $mailer->addRecipient($recipient->email);
                $mailer->setSubject($subject);
                $mailer->isHTML(true);
                $mailer->Encoding = 'base64';
                $mailer->setBody($body);

                // Send and log the email.
                $send = $mailer->Send();
                if ( $send !== true ) {
                    echo 'Error sending email: ' . $send->__toString();
                    JLog::add($send->__toString(), JLog::ERROR, 'com_emundus');
                } else {
                    $message = array(
                        'user_id_from' => $from_id,
                        'user_id_to' => $recipient->id,
                        'subject' => $subject,
                        'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('COM_EMUNDUS_APPLICATION_SENT').' '.JText::_('COM_EMUNDUS_TO').' '.$recipient->email.'</i><br>'.$body
                    );
                    $m_emails->logEmail($message);
                }

            }
        }

        return true;

    }

    public function createTimeslots($calendar_id = null, $start_dates = array(), $end_dates = array()) {

        $db = JFactory::getDbo();
        $user = JFactory::getUser();

        // Make sure variables aren't empty and the start and end dates have the same number of elements.
        if ($calendar_id == null || count($start_dates) == 0 || count($end_dates) == 0 || count($start_dates) != count($end_dates)) {
            return false;
        }

        // Get calendar information to use for the events.
        $query = 'SELECT extension FROM #__categories WHERE id = '.$calendar_id;

        try {

            $db->setQuery($query);
            $extension = $db->loadResult();

        } catch (Exception $e) {
            JLog::add('Error getting calendar from id in model/calendar at query: '.$query, JLog::ERROR, 'com_emundus');
        }

        // Check that the calendar id actually corresponds to a calendar.
        if ($extension != 'com_dpcalendar') {
            return false;
        }

        $query = 'INSERT INTO #__dpcalendar_events
                    (catid, uid, original_id,title, alias, start_date, end_date, images, url, language, created, created_by, created_by_alias) VALUES ';

        $index = 0;
        foreach ($start_dates as $start_date) {

            $start_date = $start_date->format('Y-m-d G:i:s');
            $end_date = $end_dates[$index]->format('Y-m-d G:i:s');

            $query .= ' ('.$db->Quote($calendar_id).', '.$db->Quote(md5(str_shuffle(date('Y-m-d G:i:s')))).',0 , '.$db->Quote($start_date).', '.$db->Quote(str_replace(' ','-',$start_date)).', '.$db->Quote($start_date).', '.$db->Quote($end_date).', '.$db->Quote('{}').', '.$db->Quote(' ').', '.$db->Quote('*').', '.$db->Quote(date('Y-m-d G:i:s')).', '.$user->id.', '.$db->Quote(str_replace(' ','-',$user->name)).'),';

            $index++;
        }

        $query = rtrim($query, ',');

        try {

            $db->setQuery($query);
            $db->execute();
            return true;

        } catch (Exception $e) {
            JLog::add('Error inserting events in model/calendar at query: '.$query, JLog::ERROR, 'com_emundus');
            return false;
        }

    }

    // Helper function, gets all users that are coordinators to a program.
    function getProgramRecipients($program_name) {

        $db = JFactory::getDbo();
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $profilesToNotify = $eMConfig->get('mailRecipients');

        if ($profilesToNotify == '-1') {
            return null;
        }

        try {

            $query = "SELECT eu.user_id, u.name, u.email FROM #__emundus_groups AS eg
                        LEFT JOIN #__emundus_users AS eu ON eu.user_id = eg.user_id
                        LEFT JOIN #__users AS u ON eu.user_id = u.id
                        WHERE eu.profile IN (".$profilesToNotify.") AND eg.group_id IN (
                            SELECT DISTINCT(g.id) FROM #__emundus_setup_groups AS g
                            LEFT JOIN #__emundus_setup_groups_repeat_course AS gr ON gr.parent_id = g.id
                            WHERE gr.course = ".$db->Quote($program_name)."
                        )";

            $db->setQuery($query);
            return $db->loadObjectList();

        } catch (Exception $e) {
            JLog::add("SQL Query: ".$query." SQL Error: ".$e->getMessage(), JLog::ERROR, "com_emundus");
        }

    }

    // Helper function, creates a category.
    function createCategory($data) {
        $table = JTable::getInstance('Category');

        $data['rules'] = array(
            'core.edit.state' => array(),
            'core.edit.delete' => array(),
            'core.edit.edit' => array(),
            'core.edit.state' => array(),
            'core.edit.own' => array(1 => true)
        );

        $table->setLocation($data['parent_id'], 'last-child');

        $table->bind($data);

        if ($table->check() && $table->store()) {
            return true;
        } else {
            return false;
        }
    }

    // Helper function, gets the synthesis
    function getSynthesis($fnum) {

        require_once (JPATH_COMPONENT.DS.'models'.DS.'profile.php');
        require_once (JPATH_COMPONENT.DS.'models'.DS.'application.php');
        require_once (JPATH_COMPONENT.DS.'models'.DS.'emails.php');

        $m_profile      = new EmundusModelProfile;
        $m_application  = new EmundusModelApplication;
        $m_email        = new EmundusModelEmails;

        $fnumInfos      = $m_profile->getFnumDetails($fnum);
        $program        = $m_application->getProgramSynthesis($fnumInfos['campaign_id']);
        $campaignInfo   = $m_application->getUserCampaigns($fnumInfos['applicant_id'], $fnumInfos['campaign_id']);

        $tag = array(
            'FNUM' => $fnum,
            'CAMPAIGN_NAME' => $fnum,
            'APPLICATION_STATUS' => $fnum,
            'APPLICATION_TAGS' => $fnum,
            'APPLICATION_PROGRESS' => $fnum
        );

        $tags = $m_email->setTags(intval($fnumInfos['applicant_id']), $tag, $fnum, '', $program->synthesis);

        $synthesis = new stdClass();
        $synthesis->program = $program;
        $synthesis->camp    = $campaignInfo;
        $synthesis->fnum    = $fnum;
        $synthesis->block   = preg_replace($tags['patterns'], $tags['replacements'], @$program->synthesis);

        $element_ids = $m_email->getFabrikElementIDs($synthesis->block);
        if (count(@$element_ids[0]) > 0) {
            $element_values = $m_email->getFabrikElementValues($fnum, $element_ids[1]);
            $synthesis->block = $m_email->setElementValues($synthesis->block, $element_values);
        }

        return $synthesis->block;

    }

    function getClient($clientId, $clientSecret) {

        $client = new Google_Client();
        $client->setClassConfig('Google_IO_Curl', 'options', array(
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
        ));
        $client->setApplicationName(APPLICATION_NAME);
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setScopes(SCOPES);
        $client->setAccessType('offline');

        $uri = !isset($_SERVER['HTTP_HOST']) ? JUri::getInstance('http://localhost') : JFactory::getURI();

        if (filter_var($uri->getHost(), FILTER_VALIDATE_IP)) {
            $uri->setHost('localhost');
        }

        $client->setRedirectUri($uri->toString(array('scheme', 'host', 'port', 'path')).'?option=com_emundus&controller=calendar&task=authenticateclient');

        // Refresh the token if it's expired.
        return $client;
    }

    function authenticateClient() {

        $eMConfig = JComponentHelper::getParams('com_emundus');
        $app = JFactory::getApplication();
        $session = JFactory::getSession(array(
            'expire' => 30
        ));
        $myUrl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $urlExpl = explode('&', $myUrl);
        $authCodeExpl = explode('=', $urlExpl[3]);
        $compareURL = $authCodeExpl[0];
        $authCode = $authCodeExpl[1];

	    // If we are on the callback from google don't save
	    if ($compareURL != 'code') {
		    $params = $app->input->get('params', array(
			    'clientId' => null,
			    'clientSecret' => null
		    ), 'array');
		    $session->set('clientId', $params['clientId']);
		    $session->set('clientSecret', $params['clientSecret']);
	    } else {
		    $session->set('clientId', null);
		    $session->set('clientSecret', null);
	    }

        try {
            $client = $this->getClient($eMConfig->get('clientId'), $eMConfig->get('clientSecret'));
            $client->setApprovalPrompt('force');

            if (empty($client)) {
                return;
            }
            if ($compareURL != 'code') {
                $app->redirect($client->createAuthUrl());
                $app->close();
            }

            $token = $client->authenticate($authCode);
            $tok = json_decode($token, true);
            if ($tok['refresh_token'] != null) {
                $eMConfig->set('refreshToken', $tok['refresh_token']);

                $componentid = JComponentHelper::getComponent('com_emundus')->id;
                $db = JFactory::getDBO();

                $query = "UPDATE #__extensions SET params = ".$db->Quote($eMConfig->toString())." WHERE extension_id = ".$componentid;

                try {
                    $db->setQuery($query);
                    $db->execute();
                } catch (Exception $e) {
                    die($e->getMessage());
                }
            }

            if ($token === true) {
                die();
            }

            if ($token) {
                $client->setAccessToken($token);
            }

        } catch (Exception $e) {
            $error = JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage();
            JLog::add($error, JLog::ERROR, 'com_emundus');
        }
    }
}

