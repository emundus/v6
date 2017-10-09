<?php
require_once __DIR__ .DS.'php-google-api-client'.DS.'vendor'.DS.'autoload.php';
JLoader::import('components.com_dpcalendar.libraries.dpcalendar.syncplugin', JPATH_ADMINISTRATOR);
JPluginHelper::importPlugin( 'dpcalendar' );
 
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
define('APPLICATION_NAME', 'Google Calendar API PHP Emundus');
define('CREDENTIALS_PATH', __DIR__ .DS.'php-google-api-client'.DS.'credentials'.DS.'calendar-php-quickstart.json');
define('CLIENT_SECRET_PATH', __DIR__ .DS.'php-google-api-client'.DS.'certificates'.DS.'client_secret.json');

// If modifying these scopes, delete your previously saved credentials
// at __DIR__ . '/credentials/calendar-php-quickstart.json
define('SCOPES', implode(' ', array(
    Google_Service_Calendar::CALENDAR) // CALENDAR_READONLY
));


class EmundusModelCalendar extends JModelLegacy {


    // Uses Google API credentials to initialize a google service object.
    public function google_authenticate($clientID, $clientSecret, $refreshToken) {
        
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
        $user = JFactory::getUser();
        
        // in order to get the path we need the name of the parent calendar
        // This is because the path is written as parentAlias/calAlias
        try {
            
            $db->setQuery("SELECT alias FROM #__categories WHERE id = ".$parentId);
            $parentAlias = $db->loadResult();

        } catch (Exception $e) {
            die($e->getMessage());
        }

        $category_data['id'] = 0;
        $category_data['parent_id'] = $parentId;
        $category_data['title'] = $title[0];
        $category_data['alias'] = $alias[0];
        $category_data['path'] = $parentAlias."/".$alias[0];
        $category_data['extension'] = 'com_dpcalendar';
        $category_data['published'] = 1;
        $category_data['language'] = '*';
        $category_data['params'] = array(
            'category_layout' => '',
            'image' => '',
            'color' => $color[0],
            'etag' => '1',
            'program' => $program[0],
            'googleId' => $googleId
        );
        $category_data['metadata'] = array(
            'author' => '',
            'robots' => ''
        );
        
        return $this->createCategory($category_data);

    }

    public function dpcalendar_confirm_interview($event_id, $user_id, $fnum) {

        $db = Jfactory::getDbo();
        $user = JFactory::getUser($user_id);

        // Get event
        try {
            
            $db->setQuery("SELECT * FROM #__dpcalendar_events WHERE id = ".$event_id);
            $event = $db->loadObject();

        } catch (Exception $e) {
            JLog::add("User: ".$user_id." SQL Error: ".$e->getMessage(), JLog::ERROR, "com_emundus");
            die(json_encode(['status' => false]));
        }

        // TODO: Synthesis as description?
        $event->title = "(BOOKED) ".$event->title;
        $event->description = $user->name;
        $event->booking_information = $user->id;
        $event->capacity = "1";
        $event->capacity_used = "1";
 
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

        if (isset($result))
            return true;
        else return false;

    }

    public function dpcalendar_delete_interview($event_id) {

        $db = JFactory::getDbo();

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

        $title = str_replace("(BOOKED) ", "", $title);

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

        if (!isset($booked)) 
            return false;

        $mailer = JFactory::getMailer();
        $config = JFactory::getConfig();
        $user   = JFactory::getSession()->get('emundusUser');
        $db     = JFactory::getDbo();
        
        $m_emails = new EmundusModelEmails;

        try {
            
            $db->setQuery("SELECT * FROM #__dpcalendar_events WHERE id = ".$event_id);
            $event = $db->loadObject();

        } catch (Exception $e) {
            JLog::add("SQL Error: ".$e->getMessage(), JLog::ERROR, "com_emundus");
            die(json_encode(['status' => false]));
        }

        try {

            $db->setQuery("SELECT params FROM #__categories WHERE id = ".$event->catid);
            $params = $db->loadResult();

        } catch (Exception $e) {
            JLog::add("SQL Error: ".$e->getMessage(), JLog::ERROR, "com_emundus");
            die(json_encode(['status' => false]));
        }

        $params = json_decode($params);

        // Set event time to the correct timezone.
        $offset     = $config->get('offset');
        $event_dt   = new DateTime($event->start_date, new DateTimeZone('GMT'));
        $event_dt->setTimezone(new DateTimeZone($offset));
        $event_date = $event_dt->format('M j Y');
        $event_time = $event_dt->format('g:i A');

        // The first thing to do is that we need to send a mail to the user that just decided to book the event.
        
        // The post variable allows us to insert data that will be transformed by the tags.
        // In this case we want the dates and times as well as programmes, the fnum is there possibly temporarly as we do not know whether is it used yet.
        $post = array(
            'FNUM'          => $user->fnum,
            'EVENT_DATE'    => $event_date,
            'EVENT_TIME'    => $event_time,
            'USER_NAME'     => $user->name,
            'PROGRAM'       => $params->program
        );

        // An array containing the tag names is created.
        $tags = $m_emails->setTags($user->id, $post);
        
        // TODO: Why is this ID hardcoded?
        $from_id = 62;

        if ($booked)
            $email = $m_emails->getEmail('booking_created_user');
        else
            $email = $m_emails->getEmail('booking_deleted_user');

        // Tags are replaced with their corresponding values using the PHP preg_replace function.        
        $subject = preg_replace($tags['patterns'], $tags['replacements'], $email->subject);
        $body = preg_replace($tags['patterns'], $tags['replacements'], $email->message);
        $body = $m_emails->setTagsFabrik($body, array($user->fnum));

        // TODO: Try to get these tags out of this code, either put them in the DB or make a function that builds the emails with the template?
        $body = preg_replace(array("/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"), array($subject, $body), $email->Template);

        $sender = array( 
            $config->get('mailfrom'),
            $config->get('fromname') 
        );

        // Configure email sender
        $mailer->setSender($sender);
        $mailer->addReplyTo($config->get('mailfrom'), $config->get('fromname'));
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
                'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$user->email.'</i><br>'.$body
            );
            $m_emails->logEmail($message);
            JLog::add($user->email.' '.$body, JLog::INFO, 'com_emundus');
        }

        // Part two is sending the email to the coordinators.
        // We are using the program name to get the associated group and then getting all users inside of that group.
        $recipients = $this->getProgramRecipients($params->program);

        if ($booked)
            $email = $m_emails->getEmail('booking_created_recipient');
        else
            $email = $m_emails->getEmail('booking_deleted_recipient');

        foreach ($recipients as $recipient) {

            $post = array(
                'FNUM'          => $user->fnum,
                'EVENT_DATE'    => $event_date,
                'EVENT_TIME'    => $event_time,
                'USER_NAME'     => $user->name,
                'RECIPIENT_NAME'=> $recipient->name,
                'PROGRAM'       => $params->program
            );
    
            // An array containing the tag names is created.
            $tags = $m_emails->setTags($recipient->id, $post);
    
            // Tags are replaced with their corresponding values using the PHP preg_replace function.        
            $subject = preg_replace($tags['patterns'], $tags['replacements'], $email->subject);
            $body = preg_replace($tags['patterns'], $tags['replacements'], $email->message);
            $body = $m_emails->setTagsFabrik($body, array($user->fnum));

            // TODO: Try to get these tags out of this code, either put them in the DB or make a function that builds the emails with the template?
            $body = preg_replace(array("/\[EMAIL_SUBJECT\]/", "/\[EMAIL_BODY\]/"), array($subject, $body), $email->Template);
    
            $sender = array(
                $config->get('mailfrom'),
                $config->get('fromname') 
            );
    
            // Configure email sender
            $mailer->ClearAllRecipients();
            $mailer->setSender($sender);
            $mailer->addReplyTo($config->get('mailfrom'), $config->get('fromname'));
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
                    'message' => '<i>'.JText::_('MESSAGE').' '.JText::_('SENT').' '.JText::_('TO').' '.$recipient->email.'</i><br>'.$body
                );
                $m_emails->logEmail($message);
                JLog::add($recipient->email.' '.$body, JLog::INFO, 'com_emundus');
            }

        }

        return true;
    
    }

    // Helper function, gets all users that are coordinators to a program.
    function getProgramRecipients($program_name) {

        $db = JFactory::getDbo();
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $profilesToNotify = $eMConfig->get('mailRecipients');

        try {

            $query = "SELECT user_id FROM #__emundus_groups WHERE group_id IN ( ";
            $query .= "SELECT DISTINCT(g.id) FROM #__emundus_setup_groups AS g ";
            $query .= "LEFT JOIN #__emundus_setup_groups_repeat_course AS gr ON gr.parent_id = g.id WHERE gr.course = ".$db->Quote($program_name).")";

            $db->setQuery($query);
            $program_user_ids = $db->loadColumn();

        } catch (Exception $e) {
            JLog::add("SQL Query: ".$query." SQL Error: ".$e->getMessage(), JLog::ERROR, "com_emundus");
            die(json_encode(['status' => false]));
        }

        try {

            $query = "SELECT u.id, name, email FROM #__users AS u LEFT JOIN #__emundus_users AS eu ON eu.user_id = u.id WHERE eu.profile IN (".$profilesToNotify.") AND u.id IN (".implode(",", $program_user_ids).")";

            $db->setQuery($query);
            return $db->loadObjectList();

        } catch (Exception $e) {
            JLog::add("SQL Query: ".$query." SQL Error: ".$e->getMessage(), JLog::ERROR, "com_emundus");
            die(json_encode(['status' => false]));
        }


    }

    // Helper function, creates a category.
    function createCategory($data) {
        $data['rules'] = array(
            'core.edit.state' => array(),
            'core.edit.delete' => array(),
            'core.edit.edit' => array(),
            'core.edit.state' => array(),
            'core.edit.own' => array(1 => true)
        );
    
        $basePath = JPATH_ADMINISTRATOR.'/components/com_categories';
        require_once $basePath.'/models/category.php';
        
        $config  = array('table_path' => $basePath.'/tables');
        $m_categories = new CategoriesModelCategory($config);
        
        if (!$m_categories->save($data)) {
            $err_msg = $m_categories->getError();
            return false;
        } else {
            $id = $m_categories->getItem()->id;
            return true;
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

        $tags = $m_email->setTags(intval($fnumInfos['applicant_id']), $tag);
        
        $synthesis = new stdClass();
        $synthesis->program = $program;
        $synthesis->camp    = $campaignInfo;
        $synthesis->fnum    = $fnum;
        $synthesis->block   = preg_replace($tags['patterns'], $tags['replacements'], @$program->synthesis);

        $element_ids = $m_email->getFabrikElementIDs($synthesis->block);
        if (count(@$element_ids[0])>0) {
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
        $eMConfig = JComponentHelper::getParams('com_emundus');
        
        $uri = ! isset($_SERVER['HTTP_HOST']) ? JUri::getInstance('http://localhost') : JFactory::getURI();
        
        if (filter_var($uri->getHost(), FILTER_VALIDATE_IP))
            $uri->setHost('localhost');
        
        $client->setRedirectUri(
            $uri->toString(
                array(
                    'scheme',
                    'host',
                    'port',
                    'path'
                )
            ).'?option=com_emundus&view=calendar'
        );
    
        // Refresh the token if it's expired.
    
        return $client;
    }

}

?>

