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

 
// data for the function


class EmundusModelCalendar extends JModelLegacy {

    
    function createEvent($calendarID, $title, $description, $startDate, $startTime, $endDate, $endTime,$candidate,$catID) {
        $eMConfig = JComponentHelper::getParams('com_emundus');


        // Get the API client and construct the service object.
        $client = $this->getClient($eMConfig->get('clientId'),$eMConfig->get('clientSecret'));
        $client->refreshToken($eMConfig->get('refreshToken'));
        $service = new Google_Service_Calendar($client);


        // https://developers.google.com/google-apps/calendar/v3/reference/events/quickAdd
        //$calendarID = '0lfn37j86rd32df9c89q3jjm58@group.calendar.google.com';
        $event = new Google_Service_Calendar_Event(array(
            'summary' => $title,
            //'location' => '800 Howard St., San Francisco, CA 94103',
            'description' => $description,
            'start' => array(
                'dateTime' => $startDate.'T'.$startTime.'+02:00',
                'timeZone' => 'Europe/Paris',
            ),
            'end' => array(
                'dateTime' => $endDate.'T'.$endTime.'+02:00',
                'timeZone' => 'Europe/Paris',
            ),
        ));


        $result = $service->events->insert($calendarID, $event);

        $startDated = $result->getStart()->getDateTime();
        $endDated = $result->getEnd()->getDateTime();

        $eventID = $result->id;
        $calID = $result->getOrganizer()->email;
        $this->getGCalEventCreate($eventID,$catID,$candidate,$calID);

        return $result;

    }
    
    function updateEvent($calendarID, $eventId, $title, $description, $startDate, $startTime, $endDate, $endTime,$candidate,$catID) {
        $idCalendar =  $this->getCalIdToChangeEventCal($eventId);

        $titleFinal = str_replace(' ', '.', $title);

        $eMConfig = JComponentHelper::getParams('com_emundus');   

        // Get the API client and construct the service object.
        $client = $this->getClient($eMConfig->get('clientId'),$eMConfig->get('clientSecret'));
        $client->refreshToken($eMConfig->get('refreshToken'));
        $service = new Google_Service_Calendar($client);

        $event = $service->events->get($idCalendar, $eventId);
        $event->setSummary($titleFinal);
        $event->setDescription($description);

        $start = new Google_Service_Calendar_EventDateTime();
        $start->setDateTime($startDate.'T'.$startTime.'+02:00');
        $event->setStart($start);

        $end = new Google_Service_Calendar_EventDateTime();
        $end->setDateTime($endDate.'T'.$endTime.'+02:00');
        $event->setEnd($end);
        
        
        $updatedEvent = $service->events->update($idCalendar, $event->getId(), $event);

        $eventID = $updatedEvent->id;
        $calID = $updatedEvent->getOrganizer()->email;

        $result = $service->events->move($calID, $eventID, $calendarID);

        $this->getGCalEventUpdate($eventID,$catID,$candidate,$calID);
    }

    function getCatId($eventId) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($eventId)
        );
        $query->select($db->quoteName('catid'));
        $query->from($db->quoteName('#__dpcalendar_events'));
        $query->where($conditions);
        $db->setQuery($query);

        return $db->loadResult();
    }

    function getCalIdToChangeEventCal($eventId) {
        $catId = $this->getCatId($eventId);

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($catId)
        );
        $query->select($db->quoteName('calId'));
        $query->from($db->quoteName('#__categories'));
        $query->where($conditions);
        $db->setQuery($query);

        return $db->loadResult();
    }



    function getValidate($calId) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($calId)
        );
        $query->select($db->quoteName('original_id'));
        $query->from($db->quoteName('#__dpcalendar_events'));
        $query->where($conditions);
        $db->setQuery($query);
        $result = $db->loadResult();

        return $result;
    }

    function getUserIdCandidate($eventId) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($eventId)
        );
        $query->select($db->quoteName('uid'));
        $query->from($db->quoteName('#__dpcalendar_events'));
        $query->where($conditions);
        $db->setQuery($query);
        $result = $db->loadResult();

        return $result;
    }

    function getNameCandidate($eventId) {
        $userid = $this->getUserIdCandidate($eventId);
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($userid)
        );
        $query->select($db->quoteName('name'));
        $query->from($db->quoteName('#__users'));
        $query->where($conditions);
        $db->setQuery($query);
        $result = $db->loadResult();

        return $result;
    }



    function getMailCandidate($eventId) {
        $name = $this->getUserIdCandidate($eventId);

        $db= JFactory::getDBO();
        $query = $db->getQuery(true);
        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($name)
        );
        $query->select($db->quoteName('email'));
        $query->from($db->quoteName('#__users'));
        $query->where($conditions);
        $db->setQuery($query);
        $result = $db->loadResult();

        return $result;
    }

    function getMailUser($userBook) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($userBook));

        $query->select($db->quoteName('email'));
        $query->from($db->quoteName('#__users'));
        $query->where($conditions);
        $db->setQuery($query);

        return $db->loadResult();
    }

    function getEmailFromDelete() {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $conditions = array(
        $db->quoteName('lbl') . ' = ' . $db->quote('deleted_date')
        );
        $query->select($db->quoteName('emailfrom'));
        $query->from($db->quoteName('#__emundus_setup_emails'));
        $query->where($conditions);

        $db->setQuery($query);
        $result = $db->loadResult();

        return $result;
    }

    function getEmailFromCandidateBooked() {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $conditions = array(
            $db->quoteName('lbl') . ' = ' . $db->quote('booked_by_coordinator')
        );
        $query->select($db->quoteName('emailfrom'));
        $query->from($db->quoteName('#__emundus_setup_emails'));
        $query->where($conditions);

        $db->setQuery($query);
        $result = $db->loadResult();

        return $result;
    }

    function getEmailFromDelete2() {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $conditions = array(
        $db->quoteName('lbl') . ' = ' . $db->quote('cancel_interview_to_candidate')
        );
        $query->select($db->quoteName('emailfrom'));
        $query->from($db->quoteName('#__emundus_setup_emails'));
        $query->where($conditions);

        $db->setQuery($query);
        $result = $db->loadResult();

        return $result;
    }

    function getMessageDeleteVal1($eventId) {

        $user = $this->getNameCandidate($eventId);
        $startdate = $this->getStartDate($eventId);
        $enddate = $this->getEndDate($eventId);
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $conditions = array(
            $db->quoteName('lbl') . ' = ' . $db->quote('deleted_date')
        );

        $query->select($db->quoteName('message'));
        $query->from($db->quoteName('#__emundus_setup_emails'));
        $query->where($conditions);

        $db->setQuery($query);
        $result = $db->loadAssocList();

        $message = strip_tags($result[0]['message']);
        $msgExpl = explode(']', $message);

        $nameUser = substr_replace($msgExpl[0], $user , 8);
        $array = array($nameUser,$msgExpl[1]);
        $msg = implode('', $array);

        $msgFinal = str_replace('&nbsp;', '',$msg);
        $msgWithStartDate = str_replace('{startdate}', $startdate, $msgFinal);
        $msgWithEndDate = str_replace('{enddate}', $enddate, $msgWithStartDate);  

        return $msgWithEndDate;
    }

    function getMessageDeleteVal2($eventId) {

        $user = $this->getNameCandidate($eventId);
        $startdate = $this->getStartDate($eventId);
        $enddate = $this->getEndDate($eventId);
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $conditions = array(
            $db->quoteName('lbl') . ' = ' . $db->quote('cancel_interview_to_candidate')
        );

        $query->select($db->quoteName('message'));
        $query->from($db->quoteName('#__emundus_setup_emails'));
        $query->where($conditions);

        $db->setQuery($query);
        $result = $db->loadAssocList();

        $message = strip_tags($result[0]['message']);
        $msgExpl = explode(']', $message);

        $nameUser = substr_replace($msgExpl[0], $user , 8);
        $array = array($nameUser,$msgExpl[1]);
        $msg = implode('', $array);

        $msgFinal = str_replace('&nbsp;', '',$msg);
        $msgWithStartDate = str_replace('{startdate}', $startdate, $msgFinal);
        $msgWithEndDate = str_replace('{enddate}', $enddate, $msgWithStartDate);  

        return $msgWithEndDate;
    }

    function updateQueryStartDateTags($eventId) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        
        $fields = array(
            $db->quoteName('request') . ' = ' . $db->quote('start_date|#__dpcalendar_events|id="'.$eventId.'"'),
        );
        $conditions = array(
        $db->quoteName('tag') . ' = ' . $db->quote('START_DATE')
        );

        $query->update($db->quoteName('#__emundus_setup_tags'))->set($fields)->where($conditions);
    
        $db->setQuery($query);
    
        $result = $db->execute();

    }


    function updateQueryEndDateTags($eventId) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $fields = array(
            $db->quoteName('request') . ' = ' . $db->quote('end_date|#__dpcalendar_events|id="'.$eventId.'"'),
        );
        $conditions = array(
        $db->quoteName('tag') . ' = ' . $db->quote('END_DATE')
        );

        $query->update($db->quoteName('#__emundus_setup_tags'))->set($fields)->where($conditions);
    
        $db->setQuery($query);
    
        $result = $db->execute();
    }

    function sendMailTimesDeleteVal1($eventId) {
        // Envoi mail
        /*
            * @var EmundusModelEmails $model
        *  */
        $this->updateQueryStartDateTags($eventId);
        $this->updateQueryEndDateTags($eventId);
        $current_user = JFactory::getUser();
        $model = new EmundusModelEmails;
        $email = $model->getEmail('deleted_date');
        $mailCand = $this->getMailCandidate($eventId);
        $idCand = $this->getUserIdCandidate($eventId);
        
        $mailer = JFactory::getMailer();
        
        $tags = $model->setTags($idCand, null, null, '');

        $from = preg_replace($tags['patterns'], $tags['replacements'], $email->emailfrom);
        $fromname = preg_replace($tags['patterns'], $tags['replacements'], $email->name);
        $to = $mailCand;

        $subject = preg_replace($tags['patterns'], $tags['replacements'], $email->subject);
        $body = preg_replace($tags['patterns'], $tags['replacements'], $email->message);       

        $app = JFactory::getApplication();
        $email_from_sys = $app->getCfg('mailfrom');

        $sender = array(
            $email_from_sys,
            $fromname
        );

        $mailer->setSender($sender);
        $mailer->addReplyTo($email->emailfrom, $email->name);
        $mailer->addRecipient($to);
        $mailer->setSubject($email->subject);
        $mailer->isHTML(true);
        $mailer->Encoding = 'base64';
        $mailer->setBody($body);

        $send = $mailer->Send();
        if ($send !== true) {
            
            echo 'Error sending email: ' . $send->__toString(); 
            echo json_encode((object)array('status' => false, 'msg' => JText::_('EMAIL_NOT_SENT')));
            JLog::add($send->__toString(), JLog::ERROR, 'com_emundus.email');
            exit();
        
        } else {
        
            $message = array(
                'user_id_from' => $current_user->id,
                'user_id_to' => $idCand,
                'subject' => $email->subject,
                'message' => $body
            );
            $model->logEmail($message);
        
        }
    }

    function getMessageCandidateBooked($eventId){  

        $user = $this->getNameCandidate($eventId);
        $startdate = $this->getStartDate($eventId);
        $enddate = $this->getEndDate($eventId);
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $conditions = array(
            $db->quoteName('lbl') . ' = ' . $db->quote('booked_by_coordinator')
        );

        $query->select($db->quoteName('message'));
        $query->from($db->quoteName('#__emundus_setup_emails'));
        $query->where($conditions);

        $db->setQuery($query);
        $result = $db->loadAssocList();

        $message = strip_tags($result[0]['message']);
        $msgExpl = explode(']', $message);

        $nameUser = substr_replace($msgExpl[0], $user , 8);
        $array = array($nameUser,$msgExpl[1]);
        $msg = implode('', $array);

        $msgFinal = str_replace('&nbsp;', '',$msg);
        $msgWithStartDate = str_replace('{startdate}', $startdate, $msgFinal);
        $msgWithEndDate = str_replace('{enddate}', $enddate, $msgWithStartDate);  

        return $msgWithEndDate;
    }


    function sendMailToCandidateBooked($eventId) {
            // Envoi mail
        /*
            * @var EmundusModelEmails $model
        *  */
        $this->updateQueryStartDateTags($eventId);
        $this->updateQueryEndDateTags($eventId);
        $current_user = JFactory::getUser();
        $model = new EmundusModelEmails;
        $email = $model->getEmail('booked_by_coordinator');
        $mailCand = $this->getMailCandidate($eventId);
        $idCand = $this->getUserIdCandidate($eventId);

        if (empty($idCand))
            return true;
        
        $mailer = JFactory::getMailer();

        
        $tags = $model->setTags($idCand, null, null, '');

        $from = preg_replace($tags['patterns'], $tags['replacements'], $email->emailfrom);
        $fromname = preg_replace($tags['patterns'], $tags['replacements'], $email->name);
        $to = $mailCand;

        $subject = preg_replace($tags['patterns'], $tags['replacements'], $email->subject);
        $body = preg_replace($tags['patterns'], $tags['replacements'], $email->message);       

        $app    = JFactory::getApplication();
        $email_from_sys = $app->getCfg('mailfrom');

        $sender = array(
            $email_from_sys,
            $fromname
        );

        $mailer->setSender($sender);
        $mailer->addReplyTo($email->emailfrom, $email->name);
        $mailer->addRecipient($to);
        $mailer->setSubject($email->subject);
        $mailer->isHTML(true);
        $mailer->Encoding = 'base64';
        $mailer->setBody($body);

        $send = $mailer->Send();
        if ($send !== true) {
            echo 'Error sending email: '; 
            echo json_encode((object)array('status' => false, 'msg' => JText::_('EMAIL_NOT_SENT')));
            exit();
        } else {
            $message = array(
                'user_id_from' => $current_user->id,
                'user_id_to' => $idCand,
                'subject' => $email->subject,
                'message' => $body
            );
            $model->logEmail($message);
        }
    }

    function getStartDate($eventId) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($eventId)
        );
        $query->select($db->quoteName('start_date'));
        $query->from($db->quoteName('#__dpcalendar_events'));
        $query->where($conditions);
        $db->setQuery($query);
        $result = $db->loadResult();
        
        return $result;
    }

    function getEndDate($eventId){
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($eventId)
        );
        $query->select($db->quoteName('end_date'));
        $query->from($db->quoteName('#__dpcalendar_events'));
        $query->where($conditions);
        $db->setQuery($query);
        $result = $db->loadResult();

        return $result;
    }

    function sendMailTimesDeleteVal2($eventId) {
        // Envoi mail
        /*
            * @var EmundusModelEmails $model
        *  */
        $this->updateQueryStartDateTags($eventId);
        $this->updateQueryEndDateTags($eventId);
        $current_user = JFactory::getUser();
        $model = new EmundusModelEmails;
        $email = $model->getEmail('cancel_interview_to_candidate');
        $mailCand = $this->getMailCandidate($eventId);
        $idCand = $this->getUserIdCandidate($eventId);
        
        $mailer = JFactory::getMailer();

        
        $tags = $model->setTags($idCand, null, null, '');

        $from = preg_replace($tags['patterns'], $tags['replacements'], $email->emailfrom);
        $fromname = preg_replace($tags['patterns'], $tags['replacements'], $email->name);
        $to = $mailCand;

        $subject = preg_replace($tags['patterns'], $tags['replacements'], $email->subject);
        $body = preg_replace($tags['patterns'], $tags['replacements'], $email->message);       

        $app = JFactory::getApplication();
        $email_from_sys = $app->getCfg('mailfrom');

        $sender = array(
            $email_from_sys,
            $fromname
        );

        $mailer->setSender($sender);
        $mailer->addReplyTo($email->emailfrom, $email->name);
        $mailer->addRecipient($to);
        $mailer->setSubject($email->subject);
        $mailer->isHTML(true);
        $mailer->Encoding = 'base64';
        $mailer->setBody($body);

        $send = $mailer->Send();
        if ( $send !== true ) {
            echo 'Error sending email: ' . $send->__toString(); 
            echo json_encode((object)array('status' => false, 'msg' => JText::_('EMAIL_NOT_SENT')));
            JLog::add($send->__toString(), JLog::ERROR, 'com_emundus.email');
            exit();
        } else {
            $message = array(
                'user_id_from' => $current_user->id,
                'user_id_to' => $idCand,
                'subject' => $email->subject,
                'message' => $body
            );
            $model->logEmail($message);
        }
    }

    function deleteEvent($calendarID, $eventId) {

        $validate = $this->getValidate($eventId);

        /* if($validate == '0'){
            $this->sendMailTimesDeleteVal1($eventId);
        } else {
            $this->sendMailTimesDeleteVal2($eventId);
        }*/
        $eMConfig = JComponentHelper::getParams('com_emundus');

        // Get the API client and construct the service object.
        $client = $this->getClient($eMConfig->get('clientId'),$eMConfig->get('clientSecret'));
        $client->refreshToken($eMConfig->get('refreshToken'));
        $service = new Google_Service_Calendar($client);

        $service->events->delete($calendarID, $eventId);

        $idUser = $this->getIdUser($eventId);
        $idCoord = $this->getIdCoord($eventId);

        

        if ($idUser != '61' && $idCoord == '0') {

            $this->deleteTicketsSync($eventId);
            $this->sendMailTimesDeleteVal1($eventId);

        } else if ($idUser != '61' && $idCoord != '0') {

            $this->sendMailTimesDeleteVal2($eventId);
            $this->updateUserId($eventId);
            $this->deleteBookings($eventId);
            $this->deleteTicketsSync($eventId);

        }

        $this->deleteEventInDB($eventId);

    }

    function deleteEventInDB($eventId) {
        $db = JFactory::getDBO(); 

        $query = $db->getQuery(true);

        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($eventId)
        );

        $query->delete($db->quoteName('#__dpcalendar_events'));
        $query->where($conditions);  

        $db->setQuery($query);
        $result = $db->execute();
    }




    function getUserFromToken($token) {
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $ticket = $client->verifyIdToken($token);
        if ($ticket) {
            $data = $ticket->getAttributes();
            return $data['payload']['sub']; // user ID
        }
        return false;
    }

    function getFnum($userBook) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $conditions = array(
            $db->quoteName('applicant_id') . ' = ' . $db->quote($userBook)
        );
        $query->select($db->quoteName('fnum'));
        $query->from($db->quoteName('#__emundus_campaign_candidature'));
        $query->where($conditions);
        $db->setQuery($query);

        return $db->loadResult();
    }


    /**
    * Returns an authorized API client.
    * @return Google_Client the authorized client object
    */

    function authenticateClient() {

        $eMConfig = JComponentHelper::getParams('com_emundus');

        $app = JFactory::getApplication();

        $session = JFactory::getSession(array(
            'expire' => 30
        ));

        $myUrl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $urlExpl = explode('&', $myUrl);
        $authCodeExpl = explode('=', $urlExpl[2]);
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

        }
        $clientId = $session->get('clientId', null);
        $clientSecret = $session->get('clientSecret', null);

        if ($compareURL == 'code') {
            $session->set('clientId', null);
            $session->set('clientSecret', null);
        }

        try {

            $client = $this->getClient($eMConfig->get('clientId'), $eMConfig->get('clientSecret'));
            $client->setApprovalPrompt('force');
            
            if (empty($client)) return;

            if ($compareURL != 'code') {
                $app->redirect($client->createAuthUrl());
                $app->close();
            }
            
            $cal = new Google_Service_Calendar($client);
        
            $token = $client->authenticate($authCode);
            $tok = json_decode($token, true);

            if (($tok['refresh_token'] != null)) {

                $eMConfig->set('refreshToken', $tok['refresh_token']);

                $calId = $this->getCalId();

                if (isset($calId[0]))
                    $eMConfig->set('calendarId1', $calId[0]);
            
                if (isset($calId[1]))
                    $eMConfig->set('calendarId2', $calId[1]);
            
                if (isset($calId[2]))
                    $eMConfig->set('calendarId3', $calId[2]);
            
                if (isset($calId[3]))
                    $eMConfig->set('calendarId4', $calId[3]);
            
                if (isset($calId[4]))
                    $eMConfig->set('calendarId5', $calId[4]);
            
                if (isset($calId[5]))
                    $eMConfig->set('calendarId6', $calId[5]);
            
                if (isset($calId[6]))
                    $eMConfig->set('calendarId7', $calId[6]);
            
                if (isset($calId[7]))
                    $eMConfig->set('calendarId8', $calId[7]);
            
                if (isset($calId[8]))
                    $eMConfig->set('calendarId9', $calId[8]);
            
                if (isset($calId[9]))
                    $eMConfig->set('calendarId10', $calId[9]);
            
                if (isset($calId[10]))
                    $eMConfig->set('calendarId11', $calId[10]);
            
                if (isset($calId[11]))
                    $eMConfig->set('calendarId12', $calId[11]);
            
                if (isset($calId[12]))
                    $eMConfig->set('calendarId13', $calId[12]);
            
                if (isset($calId[13]))
                    $eMConfig->set('calendarId14', $calId[13]);
            
                if (isset($calId[14]))
                    $eMConfig->set('calendarId15', $calId[14]);
            
                if (isset($calId[15]))
                    $eMConfig->set('calendarId16', $calId[15]);
            
                if (isset($calId[16]))
                    $eMConfig->set('calendarId17', $calId[16]);
            
                if (isset($calId[17]))
                    $eMConfig->set('calendarId18', $calId[17]);
            
                if (isset($calId[18]))
                    $eMConfig->set('calendarId19', $calId[18]);
            
                if (isset($calId[19]))
                    $eMConfig->set('calendarId20', $calId[19]);
                        
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

            if ($token === true) die();

            if ($token) $client->setAccessToken($token);    
            
        } catch (Exception $e) {
            $error = JUri::getInstance().' :: USER ID : '.JFactory::getUser()->id.' -> '.$e->getMessage();
            JLog::add($error, JLog::ERROR, 'com_emundus');
        }

        /*if($client->isAccessTokenExpired()){
        $refreshTok = $eMConfig->get('refreshToken'); 
        $client->refreshToken($refreshTok);
        
        }*/


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
        {
            $uri->setHost('localhost');
        }
        
        $client->setRedirectUri(
            $uri->toString(array(
                'scheme',
                'host',
                'port',
                'path'
            )) . '?option=com_emundus&view=calendar');
    
    // Refresh the token if it's expired.
    
    return $client;
    }
    
    /**
    * Expands the home directory alias '~' to the full path.
    * @param string $path the path to expand.
    * @return string the expanded path.
    */
    function expandHomeDirectory($path) {
        $homeDirectory = getenv('HOME');
        if (empty($homeDirectory))
            $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
        return str_replace('~', realpath($homeDirectory), $path);
    }

    function getIdCalCat() {
        $db = JFactory::getDBO(); 
        $query = $db->getQuery(true);

        $query->select($db->quoteName('id'));
        $query->from($db->quoteName('#__categories'));
        $query->where($db->quoteName('name') .'='.$db->quote('emunduscalendar')); 

        $db->setQuery($query);
        $cal = $db->loadColumn();

        return $cal;

    }



    function getIdEventDB(){
        $db = JFactory::getDBO(); 
        $query = $db->getQuery(true);

        $query->select($db->quoteName('id'));
        $query->from($db->quoteName('#__dpcalendar_events')); 

        $db->setQuery($query);
        $cal = $db->loadColumn();

        var_dump($cal);

    }

    function getStartDateDB(){
        $db = JFactory::getDBO(); 
        $query = $db->getQuery(true);

        $query->select($db->quoteName('start_date'));
        $query->from($db->quoteName('#__dpcalendar_events')); 

        $db->setQuery($query);
        $cal = $db->loadColumn();

        var_dump($cal);

    }

    function getEndDateDB(){
        $db = JFactory::getDBO(); 
        $query = $db->getQuery(true);

        $query->select($db->quoteName('end_date'));
        $query->from($db->quoteName('#__dpcalendar_events')); 

        $db->setQuery($query);
        $cal = $db->loadColumn();

        var_dump($cal);

    }

    function getDescriptionDB(){
        $db = JFactory::getDBO(); 
        $query = $db->getQuery(true);

        $query->select($db->quoteName('id'));
        $query->from($db->quoteName('#__dpcalendar_events')); 

        $db->setQuery($query);
        $cal = $db->loadColumn();

        var_dump($cal);

    }


    function getGCalEventUpdate($eventID,$catID,$candidate,$calID) {

        $this->deleteEventInDB($eventID);
        $session = JFactory::getSession();
        $sessionCurrentUser = $session->get('user');

        $this->saveParams();
        $accountId = $this->getFirstCalendar();


        $eMConfig = JComponentHelper::getParams('com_emundus');

        // $this->setTicketSaved();
        $db = JFactory::getDBO(); 

        $query = $db->getQuery(true);

        $query->select($db->quoteName('user_id'));

        $query->from($db->quoteName('#__dpcalendar_bookings')); 

        $db->setQuery($query);

        $bookUsed = $db->loadAssocList();


        $db = JFactory::getDBO(); 

        /*$query = $db->getQuery(true);

        $query->select($db->quoteName(array('user_id','state')));

        $query->from($db->quoteName('#__dpcalendar_tickets')); 

        $db->setQuery($query);

        $ticketUsed = $db->loadAssocList();   */  
    
        $this->deleteDPCalEvent();
        

        $client = $this->getClient($eMConfig->get('clientId'), $eMConfig->get('clientSecret'));
        $client->refreshToken($eMConfig->get('refreshToken'));

        
        $service = new Google_Service_Calendar($client);
        

        $jinput = JFactory::getApplication()->getParams();
        $calendarID1 = $jinput->get('calendarId1');
        $calendarID2 = $jinput->get('calendarId2');
        $calendarID3 = $jinput->get('calendarId3');
        $calendarID4 = $jinput->get('calendarId4');
        $calendarID5 = $jinput->get('calendarId5');
        $calendarID6 = $jinput->get('calendarId6');
        $calendarID7 = $jinput->get('calendarId7');
        $calendarID8 = $jinput->get('calendarId8');
        $calendarID9 = $jinput->get('calendarId9');
        $calendarID10 = $jinput->get('calendarId10');
        $calendarID11 = $jinput->get('calendarId11');
        $calendarID12 = $jinput->get('calendarId12');
        $calendarID13 = $jinput->get('calendarId13');
        $calendarID14 = $jinput->get('calendarId14');
        $calendarID15 = $jinput->get('calendarId15');
        $calendarID16 = $jinput->get('calendarId16');
        $calendarID17 = $jinput->get('calendarId17');
        $calendarID18 = $jinput->get('calendarId18');
        $calendarID19 = $jinput->get('calendarId19');
        $calendarID20 = $jinput->get('calendarId20');


        $cal = $this->getIdCalCat();

        $event = $service->events->get($calID,$eventID); 

        if ($candidate[0] != 61) {

            $booking = '1';
            $userBook = $candidate[0];
            $coordinatorBook = $sessionCurrentUser->id;
        
        } else {
        
            $booking = '0';
            $userBook = '61';
            $coordinatorBook = '0';
        
        }
    
        $title = $event->getSummary();
        $description = $event->getDescription();
        $eventsId = $event->getId();


        $fnum = $this->getFnum($userBook);

        if ($userBook == '61')
            $userBook == '';


        //Get Date and Time for Start
        $start = $event->getStart();
        
        $startDateTimes = $start->getDateTime();  
        $startDateTime = explode('T', $startDateTimes);
        $startDate = $startDateTime[0];
        $startTimeUTC = $startDateTime[1];
        $startTimes = explode('+', $startTimeUTC);
        $startTime = $startTimes[0];

        //Get Date and Time for End
        $end = $event->getEnd();

        $endDateTimes = $end->getDateTime();  
        $endDateTime = explode('T', $endDateTimes);
        $endDate = $endDateTime[0];

        $endTimeUTC = $endDateTime[1];
        $endTimes = explode('+', $endTimeUTC);
        $endTime = $endTimes[0];

        $arrayStartDateTime = array($startDate,$startTime);
        $startDateTimeDB = implode(' ', $arrayStartDateTime);

        $arrayEndDateTime = array($endDate,$endTime);
        $endDateTimeDB = implode(' ', $arrayEndDateTime);


        $db = JFactory::getDBO(); 

        $query = $db->getQuery(true);

        $columns = array('id', 'catid', 'uid', 'original_id', 'title', 'alias', 'rrule', 'recurrence_id', 'start_date', 'end_date', 'all_day', 'color', 'url', 'images', 'description', 'date', 'hits', 'capacity', 'capacity_used', 'max_tickets', 'booking_closing_date', 'price', 'earlybird', 'user_discount', 'booking_information', 'tax', 'ordertext', 'orderurl', 'canceltext', 'cancelurl', 'state', 'checked_out', 'checked_out_time', 'access', 'access_content', 'params', 'language', 'created', 'created_by', 'created_by_alias', 'modified', 'modified_by', 'metakey', 'metadesc', 'metadata', 'featured', 'xreference', 'publish_up', 'publish_down', 'plugintype','fnum');

    
        $values = array($db->quote($eventsId),$db->quote($catID),$db->quote($userBook),$db->quote($coordinatorBook),$db->quote($title),$db->quote($title),$db->quote(NULL),$db->quote(NULL),$db->quote($startDateTimeDB),$db->quote($endDateTimeDB),$db->quote('0'),$db->quote(''),$db->quote(''),$db->quote(''),$db->quote($description),$db->quote(NULL),$db->quote('0'),$db->quote('1'),$db->quote($booking),$db->quote('1'),$db->quote(NULL),$db->quote(NULL),$db->quote(NULL),$db->quote(NULL),$db->quote(NULL),$db->quote('0'),$db->quote(''),$db->quote(NULL),$db->quote(''),$db->quote(NULL),$db->quote('1'),$db->quote('0'),$db->quote(NULL),$db->quote('1'),$db->quote('1'),$db->quote(''),$db->quote('*'),$db->quote(NULL),$db->quote('0'),$db->quote(''),$db->quote(NULL),$db->quote('0'),$db->quote(''),$db->quote(''),$db->quote(''),$db->quote('0'),$db->quote(''),$db->quote(NULL),$db->quote(NULL),$db->quote(''),$db->quote($fnum));

        $result =  $query->insert($db->quoteName('#__dpcalendar_events'))->columns($db->quoteName($columns))->values(implode(',', $values));  

        $db->setQuery($query);
        $db->execute();
        
        $idUser = $this->getIdUser($eventsId);
        $idCoord = $this->getIdCoord($eventsId);

        $titles = '(booked).'. $title ;
        

        if ($idUser != '61' && $idCoord != '0' && !preg_match('(booked)',$title) == true) {

            $event->setSummary($titles);

            $updatedEvent = $service->events->update($calID, $eventsId, $event);

            $this->ticketInterview($eventsId,$userBook);
            $this->confirmInterview($eventsId,$userBook);
            $this->updateTitleInDB($titles,$eventsId);
            $this->sendMailToCandidateBooked($eventsId);

        } elseif ($idUser == '61' && $idCoord == '0' && !preg_match('(booked)',$title) == false) {

            $eventTitle = explode(' ', $titles);

            $event->setSummary($eventTitle[0]);

            $updatedEvent = $service->events->update($calID, $eventsId, $event);

            $this->updateTitleInDB($eventTitle[0],$eventsId);
            $this->sendMailTimesDeleteVal2($eventsId);
            $this->updateUserId($eventsId);
            $this->deleteBookings($eventsId);
            $this->deleteTicketsSync($eventsId);

        }

        $this->deleteTicketSaved();

    }

    function deleteBookings($eventId) {

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $conditions = array($db->quoteName('uid') . ' = ' . $db->quote($eventId));
        $query->delete($db->quoteName('#__dpcalendar_bookings'));
        $query->where($conditions);
        $db->setQuery($query);
        $db->execute();

    }

    function deleteTicketsSync($eventId) {

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $conditions = array($db->quoteName('event_id') . ' = ' . $db->quote($eventId));
        $query->delete($db->quoteName('#__dpcalendar_tickets'));
        $query->where($conditions);
        $db->setQuery($query);
        $db->execute();

    }

    function ticketInterview($calId,$userBook) {
    
        $email = $this->getMailCandidate($calId);
        $name = $this->getNameCandidate($calId);

        $db = JFactory::getDBO(); 

        $query = $db->getQuery(true);

        $columns = array('event_id','user_id','email','name','state');

        $values = array($db->quote($calId),$db->quote($userBook),$db->quote($email),$db->quote($name),$db->quote('1'));

        $query->insert($db->quoteName('#__dpcalendar_tickets'))->columns($db->quoteName($columns))->values(implode(',', $values)); 

        $db->setQuery($query);
        $db->execute();
    }


    function confirmInterview($calId, $userBook) {

        $candidateMail = $this->getMailUser($userBook); 
    
        $session = JFactory::getSession();
        $userName = $session->get('user');
        $name = $userName->name;
        $mail = $userName->email;
        
        // Fields to update.


        $db = JFactory::getDBO(); 

        $query = $db->getQuery(true);

        $columns = array('user_id','uid','email','name','state','payer_id','payer_email','raw_data');

        $values = array($db->quote($userBook),$db->quote($calId),$db->quote($mail),$db->quote($name),$db->quote('1'),$db->quote($userBook),$db->quote($candidateMail),$db->quote('1'));

        $query->insert($db->quoteName('#__dpcalendar_bookings'))->columns($db->quoteName($columns))->values(implode(',', $values)); 

        $db->setQuery($query);
        $db->execute();

        $this->updateBookingId($calId);

    // Conditions for which records should be updated.

    }

    function updateBookingId($calId) {

        $idBook = $this->idBooking($calId);
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $conditions = array(
            $db->quoteName('event_id') . ' = ' . $db->quote($calId));

        $fields = array(
            $db->quoteName('booking_id') . ' = ' . $db->quote($idBook),
        );
        $query->update($db->quoteName('#__dpcalendar_tickets'))->set($fields)->where($conditions);
        $db->setQuery($query);
        $result = $db->execute();
    }


    function idBooking($calId){
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $conditions = array($db->quoteName('uid') . ' = ' . $db->quote($calId));
        $query->select($db->quoteName('id'));
        $query->from($db->quoteName('#__dpcalendar_bookings'));
        $query->where($conditions);
        $db->setQuery($query);

        return $db->loadResult();
    }

    

    function getGCalEventCreate($eventID,$catID,$candidate,$calID) {

        $session = JFactory::getSession();
        $sessionCurrentUser = $session->get('user');

        $this->saveParams();
        $accountId = $this->getFirstCalendar();


        $eMConfig = JComponentHelper::getParams('com_emundus');

        // $this->setTicketSaved();
        $db = JFactory::getDBO(); 
        /*$query = $db->getQuery(true);

        $query->select($db->quoteName('user_id'));
        $query->from($db->quoteName('#__dpcalendar_bookings')); 

        $db->setQuery($query);
        $bookUsed = $db->loadAssocList();


        $db = JFactory::getDBO(); 
        $query = $db->getQuery(true);

        $query->select($db->quoteName(array('user_id','state')));
        $query->from($db->quoteName('#__dpcalendar_tickets')); 

        $db->setQuery($query);
        $ticketUsed = $db->loadAssocList();*/
    
        $this->deleteDPCalEvent();
        

        $client = $this->getClient($eMConfig->get('clientId'), $eMConfig->get('clientSecret'));
        $client->refreshToken($eMConfig->get('refreshToken'));

        
        $service = new Google_Service_Calendar($client);
        

        $jinput = JFactory::getApplication()->getParams();
        $calendarID1 = $jinput->get('calendarId1');
        $calendarID2 = $jinput->get('calendarId2');
        $calendarID3 = $jinput->get('calendarId3');
        $calendarID4 = $jinput->get('calendarId4');
        $calendarID5 = $jinput->get('calendarId5');
        $calendarID6 = $jinput->get('calendarId6');
        $calendarID7 = $jinput->get('calendarId7');
        $calendarID8 = $jinput->get('calendarId8');
        $calendarID9 = $jinput->get('calendarId9');
        $calendarID10 = $jinput->get('calendarId10');
        $calendarID11 = $jinput->get('calendarId11');
        $calendarID12 = $jinput->get('calendarId12');
        $calendarID13 = $jinput->get('calendarId13');
        $calendarID14 = $jinput->get('calendarId14');
        $calendarID15 = $jinput->get('calendarId15');
        $calendarID16 = $jinput->get('calendarId16');
        $calendarID17 = $jinput->get('calendarId17');
        $calendarID18 = $jinput->get('calendarId18');
        $calendarID19 = $jinput->get('calendarId19');
        $calendarID20 = $jinput->get('calendarId20');


        $cal = $this->getIdCalCat();


        $event = $service->events->get($calID,$eventID); 

        if ($candidate[0] != 61) {

            $booking = '1';
            $userBook = $candidate[0];
            $coordinatorBook = $sessionCurrentUser->id;
        
        } else {
        
            $booking = '0';
            $userBook = '61';
            $coordinatorBook = '0';
        
        }
    
        $title = $event->getSummary();
        $description = $event->getDescription();
        $eventsId = $event->getId();

        //Get Date and Time for Start
        $start = $event->getStart();
        
        $startDateTimes = $start->getDateTime();  
        $startDateTime = explode('T', $startDateTimes);
        $startDate = $startDateTime[0];
        $startTimeUTC = $startDateTime[1];
        $startTimes = explode('+', $startTimeUTC);
        $startTime = $startTimes[0];

        //Get Date and Time for End
        $end = $event->getEnd();

        $endDateTimes = $end->getDateTime();  
        $endDateTime = explode('T', $endDateTimes);
        $endDate = $endDateTime[0];

        $endTimeUTC = $endDateTime[1];
        $endTimes = explode('+', $endTimeUTC);
        $endTime = $endTimes[0];

        $arrayStartDateTime = array($startDate,$startTime);
        $startDateTimeDB = implode(' ', $arrayStartDateTime);

        $arrayEndDateTime = array($endDate,$endTime);
        $endDateTimeDB = implode(' ', $arrayEndDateTime);


        //$this->insertCategories($titleCal,$aliasCal,$colorCal);
        $db = JFactory::getDBO(); 

        $query = $db->getQuery(true);

        $columns = array('id', 'catid', 'uid', 'original_id', 'title', 'alias', 'rrule', 'recurrence_id', 'start_date', 'end_date', 'all_day', 'color', 'url', 'images', 'description', 'date', 'hits', 'capacity', 'capacity_used', 'max_tickets', 'booking_closing_date', 'price', 'earlybird', 'user_discount', 'booking_information', 'tax', 'ordertext', 'orderurl', 'canceltext', 'cancelurl', 'state', 'checked_out', 'checked_out_time', 'access', 'access_content', 'params', 'language', 'created', 'created_by', 'created_by_alias', 'modified', 'modified_by', 'metakey', 'metadesc', 'metadata', 'featured', 'xreference', 'publish_up', 'publish_down', 'plugintype','fnum');


        $values = array($db->quote($eventsId),$db->quote($catID[0]),$db->quote($userBook),$db->quote($coordinatorBook),$db->quote($title),$db->quote($title),$db->quote(NULL),$db->quote(NULL),$db->quote($startDateTimeDB),$db->quote($endDateTimeDB),$db->quote('0'),$db->quote(''),$db->quote(''),$db->quote(''),$db->quote($description),$db->quote(NULL),$db->quote('0'),$db->quote('1'),$db->quote($booking),$db->quote('1'),$db->quote(NULL),$db->quote(NULL),$db->quote(NULL),$db->quote(NULL),$db->quote(NULL),$db->quote('0'),$db->quote(''),$db->quote(NULL),$db->quote(''),$db->quote(NULL),$db->quote('1'),$db->quote('0'),$db->quote(NULL),$db->quote('1'),$db->quote('1'),$db->quote(''),$db->quote('*'),$db->quote(NULL),$db->quote('0'),$db->quote(''),$db->quote(NULL),$db->quote('0'),$db->quote(''),$db->quote(''),$db->quote(''),$db->quote('0'),$db->quote(''),$db->quote(NULL),$db->quote(NULL),$db->quote(''),$db->quote(''));

        $result =  $query->insert($db->quoteName('#__dpcalendar_events'))->columns($db->quoteName($columns))->values(implode(',', $values));  

        $db->setQuery($query);
        $db->execute();

        $idUser = $this->getIdUser($eventsId);
        $idCoord = $this->getIdCoord($eventsId);


        $titles = '(booked) ' . $title ;

        if ($idUser != '0' && $idCoord != '0' && !preg_match('(booked)',$title) == true) {

            $event->setSummary($titles);

            $updatedEvent = $service->events->update($calID, $eventsId, $event);

            $this->ticketInterview($eventsId,$userBook);
            $this->confirmInterview($eventsId,$userBook);
            $this->updateTitleInDB($titles,$eventsId);
            $this->sendMailToCandidateBooked($eventsId);

        }
    
        $this->deleteTicketSaved();

    }

    function getPayerIdFromBookings($eventId) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $conditions = array(
        $db->quoteName('uid') . ' = ' . $db->quote($eventId));

        $query->select($db->quoteName('payer_id'));
        $query->from($db->quoteName('#__dpcalendar_bookings'));
        $query->where($conditions);
        $db->setQuery($query);

        return $db->loadResult();
    }

    function updateTitleInDB($titles,$eventId) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        
        $fields = array(
            $db->quoteName('title') . ' = ' . $db->quote($titles),    
        );

        $conditions = array(
        $db->quoteName('id') . ' = ' . $db->quote($eventId)
        );

        $query->update($db->quoteName('#__dpcalendar_events'))->set($fields)->where($conditions);
        $db->setQuery($query);
        $db->execute();
    }

    function getIdUser($eventId) {
        $db = JFactory::getDBO(); 
        $query = $db->getQuery(true);

        $conditions = array(
        $db->quoteName('id') . ' = ' . $db->quote($eventId)
        );

        $query->select($db->quoteName('uid'));
        $query->from($db->quoteName('#__dpcalendar_events'));
        $query->where($conditions); 

        $db->setQuery($query);
        $results = $db->loadResult(); 
    
        return $results;

    }


    function getIdCoord($eventsId){
        $db = JFactory::getDBO(); 
        $query = $db->getQuery(true);

        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($eventsId)
        );

        $query->select($db->quoteName('original_id'));
        $query->from($db->quoteName('#__dpcalendar_events'));
        $query->where($conditions); 

        $db->setQuery($query);
        $results = $db->loadResult(); 
    
        return $results;
    }

    function updateTitle($calendarId, $eventId, $title, $event) {
        $idUser = $this->getIdUser($eventId);
        $idCoord = $this->getIdCoord($eventId);

        $titles = '(booked) ' . $title;

        if($idUser != '0' && $idCoord != '0') {

            $event->setSummary($titles);

            $updatedEvent = $service->events->update($calendarID, $eventId, $event);

        }
    }


    function updateTicket($user){

        $db = JFactory::getDbo();
    
        $query = $db->getQuery(true);
    
        // Fields to update.

        if($user == '--NO BODY--' || $user == NULL || $user == ' '){
        
            $fields = array(
                $db->quoteName('capacity_used') . ' = ' . $db->quote('0'),
                $db->quoteName('Validate') . ' = ' . $db->quote('0'),
            );

        } else {

            $fields = array(
                $db->quoteName('capacity_used') . ' = ' . $db->quote('1'),
                $db->quoteName('Validate') . ' = ' . $db->quote('2')
            );
        
        }
        
        // Conditions for which records should be updated.
        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($id)
        );
        
        $query->update($db->quoteName('#__dpcalendar_events'))->set($fields)->where($conditions);
        
        $db->setQuery($query);
        
        $result = $db->execute();

    }



    function deleteDPCalEvent() {

        //Delete events in DPCalendar events Database
        $db = JFactory::getDBO(); 

        $query = $db->getQuery(true);

        $conditions = array(
        $db->quoteName('id') . ' = ' . $db->quote("")
        );

        $query->delete($db->quoteName('#__dpcalendar_events'));
        $query->where($conditions);  

        $db->setQuery($query);
        $result = $db->execute();

    }


    function getTitleDPCalEvent() {

        //get Title events in DPCalendar events Database
        $db = JFactory::getDBO(); 
        $query = $db->getQuery(true);

        $query->select($db->quoteName('title'));
        $query->from($db->quoteName('#__dpcalendar_events')); 

        $db->setQuery($query);
        $results =$db->loadObjectList(); 

    }

    function getUserDPCal() {

        $db = JFactory::getDBO(); 
        $query = $db->getQuery(true);

        $query->select($db->quoteName('user'));
        $query->from($db->quoteName('#__dpcalendar_events')); 

        $db->setQuery($query);
        $results = $db->loadAssocList();

        return $results;

    }

    function getTicketDPCal() {

        //get Tickets booked in DPCalendar events Database

        $db = JFactory::getDBO(); 
        $query = $db->getQuery(true);

        $query->select($db->quoteName(array('id','capacity_used','original_id','uid')));
        $query->from($db->quoteName('#__dpcalendar_events')); 

        $db->setQuery($query);
        $results =$db->loadAssocList(); 
    
        return $results;

    }

    function updateUserId($eventId){
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $fields = array(
            $db->quoteName('uid') . ' = ' . $db->quote('61'),
            $db->quoteName('original_id') . ' = ' . $db->quote('0')
        );

        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($eventId)
        );

        $query->update($db->quoteName('#__dpcalendar_events'))->set($fields)->where($conditions);

        $db->setQuery($query);
        $db->execute();

    }

    function getUidOidandCapcityused($eventId){
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($eventId)
        );
        $query->select($db->quoteName(array('uid','original_id','title','capacity_used')));
        $query->from($db->quoteName('#__dpcalendar_events'));
        $query->where($conditions);
        $db->setQuery($query);

        return $db->loadAssocList();
    }

    function updateTitleForBookingsByCandidate(){
    

        $eMConfig = JComponentHelper::getParams('com_emundus');

        $client = $this->getClient($eMConfig->get('clientId'), $eMConfig->get('clientSecret'));
        $client->refreshToken($eMConfig->get('refreshToken'));
        $service = new Google_Service_Calendar($client);

        $calendarList = $service->calendarList->listCalendarList();
        $calList = $calendarList->getItems();
    
        foreach($calList as $calendarlist) {
    
            $eventsList = $service->events->listEvents($calendarlist->id);
            $events = $eventsList->getItems();


            foreach ($events as $evt) {
            
                $info = $this->getUidOidandCapcityused($evt->id);
                
                if (!empty($info)) {
                
                    $uid = $info[0]['uid'];
                    $originalId = $info[0]['original_id'];
                    $capUsed = $info[0]['capacity_used'];
                    $title = $info[0]['title'];
            
                    if ($uid != '61' && $originalId != '0' && $capUsed == '1' && !preg_match('(booked)',$title) == true) {
                
                        $titles = array('(booked)',$title);
                        $titleBook = implode(' ', $titles);
                        $evt->setSummary($titleBook);

                        $updatedEvent = $service->events->update($calendarlist->id, $evt->getId(), $evt);

            
                    } else if ($uid == '61' && $originalId == '0' && $capUsed == '0' && !preg_match('(booked)',$title) == false) {

                        $titleBook = explode(' ',$title);
                        $evt->setSummary($titleBook[0]);

                        $updatedEvent = $service->events->update($calendarlist->id, $evt->getId(), $evt);

                    }

                    $summary = $evt->summary;
                    $startDateTimes = $evt->start->dateTime;
                    $description = $evt->description;
                    $endDateTimes = $evt->end->dateTime;

                    $explStartDateTimes = explode('T',$startDateTimes);
                    $startDate = $explStartDateTimes[0];
                    $explStartTimes = explode('+', $explStartDateTimes[1]);
                    $startTime = $explStartTimes[0];

                    $arrayStartDate = array($startDate,$startTime);
                    $startDateTime = implode(' ', $arrayStartDate);

                    $explEndDateTimes = explode('T',$endDateTimes);
                    $endDate = $explEndDateTimes[0];
                    $explEndTimes = explode('+', $explEndDateTimes[1]);
                    $endTime = $explEndTimes[0];

                    $arrayEndDate = array($endDate,$endTime);
                    $endDateTime = implode(' ', $arrayEndDate);

                    $toUpdate = $this->getForUpdate($evt->id);

                    $summaryDB = $toUpdate[0]['title'];
                    $startDateTimeDB = $toUpdate[0]['start_date'];
                    $descriptionDB = $toUpdate[0]['description'];
                    $endDateTimeDB = $toUpdate[0]['end_date'];

                

                    if ($summary != $summaryDB || $startDateTime != $startDateTimeDB || $endDateTime != $endDateTimeDB || $description != $descriptionDB) {
                        $db = JFactory::getDBO();
                        $query = $db->getQuery(true);
                        
                        $fields = array(
                            $db->quoteName('title') . ' = ' . $db->quote($summary),
                            $db->quoteName('start_date') . ' = ' . $db->quote($startDateTime),
                            $db->quoteName('end_date') . ' = ' . $db->quote($endDateTime),
                            $db->quoteName('description') . ' = ' . $db->quote($description),
                        );

                        $conditions = array(
                            $db->quoteName('id') . ' = ' . $db->quote($evt->id)
                        );

                        $query->update($db->quoteName('#__dpcalendar_events'))->set($fields)->where($conditions);

                        $db->setQuery($query);
                        $db->execute();
                    }
                }
            } 
        }
    }

    function getForUpdate($eventId) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        
        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($eventId)
        );
        
        $query->select($db->quoteName(array('title','start_date','end_date','description')));
        $query->from($db->quoteName('#__dpcalendar_events'));
        $query->where($conditions);
        $db->setQuery($query);
        return $db->loadAssocList();
    }



    function setAliasColorEm(){

    
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $aliasColor = $this->getAliasColorCat();


        foreach ($aliasColor as $colorAlias) {
            $colorAliasExplode = explode(' / ', $colorAlias);
            $alias = $colorAliasExplode[0];
            $color = $colorAliasExplode[1];

            $db = JFactory::getDBO(); 

            $query = $db->getQuery(true);

            $columns = array('id','alias','color');

            $values = array($db->quote(NULL),$db->quote($alias),$db->quote($color));

            $query->insert($db->quoteName('#__emundus_alias_color_calendar'))->columns($db->quoteName($columns))->values(implode(',', $values)); 

            $db->setQuery($query);
            $db->execute();
        }
    
    }


    function getCategoriesCal(){
        $db = JFactory::getDBO(); 

        $query = $db->getQuery(true);

        $conditions = array(
            $db->quoteName('name') . ' = ' . $db->quote('emunduscalendar')
        );

        $query->select('*');
        $query->from($db->quoteName('#__categories'));
        $query->where($conditions);
        $query->order($db->quoteName('id') . 'ASC'); 

        $db->setQuery($query);

        $results =$db->loadAssocList(); 

        return $results;
    }



    function deleteAliasColorEm(){
        $db = JFactory::getDBO(); 
        $query = $db->getQuery(true);


        $query->delete($db->quoteName('#__emundus_alias_color_calendar')); 

        $db->setQuery($query);
        $result = $db->execute();
    }


    function getcalIdSaved(){
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('calid');
        $query->from('#__dpcalendar_ticketsused_events');

        $db->setQuery($query);
        $result = $db->loadColumn();

    
    }
        

    function setTicketSaved() {

        //set Tickets booked in DPCalendar ticketsused Database

        $ticketBooked = $this->getTicketDPCal();

        foreach ($ticketBooked as $ticketBook) {
            
            $ticket     = $ticketBook[capacity_used];
            $user       = $ticketBook[uid];
            $validate   = $ticketBook[original_id];


            $db = JFactory::getDBO(); 
            $query = $db->getQuery(true);

            $columns = array('capacity_used','user','uid');

            $values = array($db->quote($ticket),$db->quote($validate),$db->quote($user));

            $query->insert($db->quoteName('#__dpcalendar_ticketsused_events'))->columns($db->quoteName($columns))->values(implode(',', $values)); 

            $db->setQuery($query);
            $db->execute();

        }

    }

    function getTicketSaved() {

        //get Tickets booked in DPCalendar ticketsused Database

        $db = JFactory::getDBO(); 
        $query = $db->getQuery(true);

        $query->select($db->quoteName('capacity_used'));
        $query->from($db->quoteName('#__dpcalendar_ticketsused_events')); 

        $db->setQuery($query);
        $results =$db->loadColumn(); 

    }




    function initAutoIncrementCat() {
        $db = JFactory::getDBO();
        $query = "ALTER TABLE `#__categories` AUTO_INCREMENT".'='."105";
        $db->setQuery($query);
        $result = $db->query();
    }

    function deleteCalCatToSync() {
        $db = JFactory::getDBO(); 
        $query = $db->getQuery(true);

        $conditions = array(
            $db->quoteName('name') . ' = ' . $db->quote('emunduscalendar')
        );

        $query->delete($db->quoteName('#__categories')); 
        $query->where($conditions); 

        $db->setQuery($query);
        $result = $db->execute();

        $this->initAutoIncrementCat();
    }

    function deleteEmCal() {
        $db = JFactory::getDBO(); 
        $query = $db->getQuery(true);

        $query->delete($db->quoteName('#__emundus_calendar'));  

        $db->setQuery($query);
        $result = $db->execute();

        $this->initAutoIncrementEmCal();
    }

    function initAutoIncrementEmCal() {
        $db = JFactory::getDBO();
        $query = "ALTER TABLE `#__emundus_calendar` AUTO_INCREMENT".'='."105";
        $db->setQuery($query);
        $result = $db->query();
    }

    function deleteTicketSaved() {

        //delete Tickets booked in DPCalendar ticketsused Database

        $db = JFactory::getDBO(); 
        $query = $db->getQuery(true);

        $query->delete($db->quoteName('#__dpcalendar_ticketsused_events'));  

        $db->setQuery($query);
        $result = $db->execute();

    }

    function getIdEventUser($calendarID,$eventId) {

        $eMConfig = JComponentHelper::getParams('com_emundus');

        $client = $this->getClient($eMConfig->get('clientId'), $eMConfig->get('clientSecret'));
        $client->refreshToken($eMConfig->get('refreshToken'));
        $service = new Google_Service_Calendar($client);

        $event = $service->events->get($calendarID, $eventId);
        $id = $event->getId();

        $db = JFactory::getDBO(); 

        $query = $db->getQuery(true);

        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($id)
        ); 

        $query->select($db->quoteName('uid'));
        $query->from($db->quoteName('#__dpcalendar_events'));
        $query->where($conditions);

        $db->setQuery($query);
        $results = $db->loadColumn();   

        return $results;

    }

    function bookEvent($calendarID,$eventId, $user) {
        $eMConfig = JComponentHelper::getParams('com_emundus');

        $client = $this->getClient($eMConfig->get('clientId'), $eMConfig->get('clientSecret'));
        $client->refreshToken($eMConfig->get('refreshToken'));
        $service = new Google_Service_Calendar($client);

        $event = $service->events->get($calendarID, $eventId);
        $id = $event->getId();

        $session = JFactory::getSession();
        $coordinator = $session->get('user');


        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
    
        // Fields to update.
        if ($user == '--NO BODY--' || $user == NULL || $user == ' ') {
        
            $fields = array(
                $db->quoteName('capacity_used') . ' = ' . $db->quote('0'),
                $db->quoteName('Validate') . ' = ' . $db->quote('0'),
                $db->quoteName('coordinator') . ' = ' . $db->quote(' ')
            );

        } else {

            $fields = array(
                $db->quoteName('capacity_used') . ' = ' . $db->quote('1'),
                $db->quoteName('Validate') . ' = ' . $db->quote('2'),
                $db->quoteName('coordinator') . ' = ' . $db->quote($coordinator->name)
            );

            $this->sendMailToCandidateBooked($eventId);
        }
    
        // Conditions for which records should be updated.
        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($id)
        );
        
        $query->update($db->quoteName('#__dpcalendar_events'))->set($fields)->where($conditions);
        
        $db->setQuery($query);
        
        $result = $db->execute();

    }

    function updateUser($user,$eventId){
        $db = JFactory::getDbo();
        
        $query = $db->getQuery(true);
        
        // Fields to update.
        $fields = array(
            $db->quoteName('user') . ' = ' . $db->quote($user),
        );
        
        // Conditions for which records should be updated.
        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($eventId)
        );
        
        $query->update($db->quoteName('#__dpcalendar_events'))->set($fields)->where($conditions);
        
        $db->setQuery($query);
        
    $result = $db->execute();

    }

    function getDpCalExt(){
        $db = JFactory::getDBO(); 

        $query = $db->getQuery(true);

        $query->select($db->quoteName(array('title', 'color')));
        $query->from($db->quoteName('#__dpcalendar_extcalendars')); 

        $db->setQuery($query);
        $calId = $db->loadAssocList(); 

        foreach ($calId as $result) {

            $color = $result[color];
            $title = $result[title];

            $resultFinal[] = $title . ' / ' . $color;
        } 


        return $resultFinal;

    }

    function insertAliasColor($alias, $color, $title){ 
        $accountId = $this->getFirstCalendar();
        $cal = $this->getFirstCalendar();  

        $db = JFactory::getDBO(); 

        $query = $db->getQuery(true);

        $columns = array('id','alias','color','accountId','title');

        $values = array($db->quote(NULL),$db->quote($alias),$db->quote($color),$db->quote($accountId),$db->quote($title));

        $query->insert($db->quoteName('#__emundus_alias_color_calendar'))->columns($db->quoteName($columns))->values(implode(',', $values)); 

        $db->setQuery($query);
        $db->execute();

    }

    function getPath(){
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $conditions = array($db->quoteName('path') . ' = ' . $db->quote('uncategorised'),
        $db->quoteName('name') . ' = ' . $db->quote('emunduscalendar'));
        $query->select($db->quoteName('path'));
        $query->from($db->quoteName('#__categories'));
        $query->where($conditions);
        $db->setQuery($query);
        return $db->loadResult();
    }

    function deleteUncategorised(){
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $conditions = array($db->quoteName('path') . ' = ' . ($db->quote('uncategorised')));
        $query->delete($db->quoteName('#__categories')); 
        $query->where($conditions);
        $db->setQuery($query);
        $db->execute();
    }

    function deleteUncategorisedFromSave(){
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $conditions = array($db->quoteName('path') . ' = ' . ($db->quote('uncategorised')));
        $query->delete($db->quoteName('#__emundus_calendar_save')); 
        $query->where($conditions);
        $db->setQuery($query);
        $db->execute();
    }

    // This function is called by the php script called on submit of the 'Crerate Calendar' fabrik form.
    function createCalendar($title,$alias,$color) {


        if ($this->getPath() != null) {
            $this->deleteUncategorised();
            $this->deleteUncategorisedFromSave();
            $this->initAutoIncrementCat();
        }

        $accountId = $this->getFirstCalendar();
        $mainframe = JFactory::getApplication();
        $jinput = $mainframe->input;
        $eMConfig = JComponentHelper::getParams('com_emundus');


        $client = $this->getClient($eMConfig->get('clientId'),$eMConfig->get('clientSecret'));
        $client->refreshToken($eMConfig->get('refreshToken'));
        $service = new Google_Service_Calendar($client);
        $calendar = new Google_Service_Calendar_Calendar();
        $calendar->setSummary($title);
        $calendar->setTimeZone('Europe/Paris');
        

        $createdCalendar = $service->calendars->insert($calendar);  
        $calid = $createdCalendar->getId();

        $this->insertCategories($title,$alias,$color,$calid);

        $db = JFactory::getDBO(); 
        $query = $db->getQuery(true);

        $columns = array('catid','calid','title');
        $values = array($db->quote(NULL),$db->quote($calid),$db->quote($title));
        $query->insert($db->quoteName('#__emundus_calendar'))->columns($db->quoteName($columns))->values(implode(',', $values)); 

        $db->setQuery($query);
        $db->execute();

    }

    function getFirstCalendar(){

        $eMConfig = JComponentHelper::getParams('com_emundus');
        $client = $this->getClient($eMConfig->get('clientId'),$eMConfig->get('clientSecret'));
        $client->refreshToken($eMConfig->get('refreshToken'));
        $service = new Google_Service_Calendar($client); 
        $calendarList = $service->calendarList->listCalendarList();
        $calList = array_reverse($calendarList->getItems());

        foreach ($calList as $calendarListEntry) {
            
            $calendarlist = $calendarListEntry->getId();
            $titleCal = $calendarListEntry->getSummary();
            $explcalList = explode('@', $calendarlist);
            

            if (!preg_match("/\bgroup.v.calendar.google.com\b/i", $explcalList[1]) && !preg_match("/\bgroup.calendar.google.com\b/i", $explcalList[1]))
                $calId = $calendarlist;
        }
        return $calId;
    }

    function getAliasColorEm(){

        $calendar = $this->getFirstCalendar();
        $eMConfig = JComponentHelper::getParams('com_emundus');  

        $db = JFactory::getDBO(); 

        $query = $db->getQuery(true);  

        $conditions = array(
            $db->quoteName('accountId') . ' = ' . $db->quote($calendar)
        ); 

        $query->select($db->quoteName(array('alias', 'color','accountId','title')));
        $query->from($db->quoteName('#__emundus_alias_color_calendar'));
        $query->where($conditions);
        $query->order($db->quoteName('id') . 'ASC'); 

        $db->setQuery($query);
        $results = $db->loadAssocList();

        foreach ($results as $result) {

            $color = $result[color];
            $alias = $result[alias];
            $accountId = $result[accountId];
            $title = $result[title];

            $resultFinal[] = $alias . ' / ' . $color . ' / ' . $accountId . ' / ' . $title;
        } 

        
    return $results;    


    }

    function getCalendarSaved(){
        $db = JFactory::getDBO(); 

        $query = $db->getQuery(true);

        $conditions = array(
            $db->quoteName('accountId') . ' = ' . $db->quote($this->getFirstCalendar())
        );

        $query->select('*');
        $query->from($db->quoteName('#__emundus_calendar_save'));
        $query->where($conditions);
        $query->order($db->quoteName('id') . 'ASC'); 

        $db->setQuery($query);
        $results = $db->loadAssocList();   

        return $results;

    }

    function saveCategoriesCalendar() {
        $calSaved = $this->getCalendarSaved();
        $result = $this->getCategoriesCal();

        $i = 0;

        foreach ($result as $calendar) {

            $asset_id           = $calendar['asset_id'];
            $parent_id          = $calendar['parent_id'];
            $lft                = $calendar['lft'];
            $rgt                = $calendar['rgt'];
            $level              = $calendar['level'];
            $path               = $calendar['path'];
            $extension          = $calendar['extension'];
            $title              = $calendar['title'];
            $alias              = $calendar['alias'];
            $note               = $calendar['note'];
            $description        = $calendar['description'];
            $published          = $calendar['published'];
            $checked_out        = $calendar['checked_out'];
            $checked_out_time   = $calendar['checked_out_time'];
            $access             = $calendar['access'];
            $params             = $calendar['params'];
            $metadesc           = $calendar['metadesc'];
            $metakey            = $calendar['metakey'];
            $metadata           = $calendar['metadata'];
            $created_user_id    = $calendar['created_user_id'];
            $created_time       = $calendar['created_time'];
            $modified_user_id   = $calendar['modified_user_id'];
            $modified_time      = $calendar['modified_time'];
            $hits               = $calendar['hits'];
            $language           = $calendar['language'];
            $version            = $calendar['version'];
            $name               = $calendar['name'];
            $calId              = $calendar['calId'];
            $accountId          = $calendar['accountId'];
            $code               = $calendar['code'];

        

            if ($asset_id != $calSaved[$i]['asset_id'] || $parent_id != $calSaved[$i]['parent_id'] || $lft != $calSaved[$i]['lft'] || $rgt != $calSaved[$i]['rgt'] || $level != $calSaved[$i]['level'] || $path != $calSaved[$i]['path'] || $extension != $calSaved[$i]['extension'] || $title != $calSaved[$i]['title'] || $alias != $calSaved[$i]['alias'] || $note != $calSaved[$i]['note'] || $description != $calSaved[$i]['description'] || $published != $calSaved[$i]['published'] || $checked_out != $calSaved[$i]['checked_out'] || $checked_out_time != $calSaved[$i]['checked_out_time'] || $access != $calSaved[$i]['access'] || $params != $calSaved[$i]['params'] || $metadesc != $calSaved[$i]['metadesc'] || $metakey != $calSaved[$i]['metakey'] || $metadata != $calSaved[$i]['metadata'] || $created_user_id != $calSaved[$i]['created_user_id'] || $created_time != $calSaved[$i]['created_time'] || $modified_user_id != $calSaved[$i]['modified_user_id'] || $modified_time != $calSaved[$i]['modified_time'] || $hits != $calSaved[$i]['hits'] || $language != $calSaved[$i]['language'] || $version != $calSaved[$i]['version'] || $name != $calSaved[$i]['name'] || $calId != $calSaved[$i]['calId'] || $accountId != $calSaved[$i]['accountId']) {

                if ($accountId == $this->getFirstCalendar()) {

                    $db = JFactory::getDBO(); 

                    $query = $db->getQuery(true);

                    $columns = array('id','asset_id','parent_id','lft','rgt','level','path','extension','title','alias','note','description','published','checked_out','checked_out_time','access','params','metadesc','metakey','metadata','created_user_id','created_time','modified_user_id','modified_time','hits','language','version','name','calId','accountId','code');

                    $values = array($db->quote(NULL),$db->quote($asset_id),$db->quote($parent_id),$db->quote($lft),$db->quote($rgt),$db->quote($level),$db->quote($path),$db->quote($extension),$db->quote($title),$db->quote($alias),$db->quote($note),$db->quote($description),$db->quote($published),$db->quote($checked_out),$db->quote($checked_out_time),$db->quote($access),$db->quote($params),$db->quote($metadesc),$db->quote($metakey),$db->quote($metadata),$db->quote($created_user_id),$db->quote($created_time),$db->quote($modified_user_id),$db->quote($modified_time),$db->quote($hits),$db->quote($language),$db->quote($version),$db->quote($name),$db->quote($calId),$db->quote($accountId),$db->quote($code));

                    $query->insert($db->quoteName('#__emundus_calendar_save'))->columns($db->quoteName($columns))->values(implode(',', $values)); 

                    $db->setQuery($query);
                    $db->execute();

                }

            }
        $i++;

        }
    }



    function getCalendarSavedByAccount(){

        $db = JFactory::getDBO(); 

        $query = $db->getQuery(true);

    $conditions = array(
            $db->quoteName('accountId') . ' = ' . $db->quote($this->getFirstCalendar())
        ); 

        $query->select('*');

        $query->from($db->quoteName('#__emundus_calendar_save'));

        $query->where($conditions);

        $query->order($db->quoteName('id') . 'ASC'); 

        $db->setQuery($query);

        $results =$db->loadAssocList();   


        return $results;


    }

    function insertCategoriesCalendar() {      
        $calSaved = $this->getCalendarSavedByAccount();


        $this->deleteCalCatToSync();
        $this->initAutoIncrementCat();


        $eMConfig = JComponentHelper::getParams('com_emundus');
        $client = $this->getClient($eMConfig->get('clientId'),$eMConfig->get('clientSecret'));
        $client->refreshToken($eMConfig->get('refreshToken'));
        $service = new Google_Service_Calendar($client);     

        foreach ($calSaved as $calendar) {

            $asset_id           = $calendar['asset_id'];
            $parent_id          = $calendar['parent_id'];
            $lft                = $calendar['lft'];
            $rgt                = $calendar['rgt'];
            $level              = $calendar['level'];
            $path               = $calendar['path'];
            $extension          = $calendar['extension'];
            $title              = $calendar['title'];
            $alias              = $calendar['alias'];
            $note               = $calendar['note'];
            $description        = $calendar['description'];
            $published          = $calendar['published'];
            $checked_out        = $calendar['checked_out'];
            $checked_out_time   = $calendar['checked_out_time'];
            $access             = $calendar['access'];
            $params             = $calendar['params'];
            $metadesc           = $calendar['metadesc'];
            $metakey            = $calendar['metakey'];
            $metadata           = $calendar['metadata'];
            $created_user_id    = $calendar['created_user_id'];
            $created_time       = $calendar['created_time'];
            $modified_user_id   = $calendar['modified_user_id'];
            $modified_time      = $calendar['modified_time'];
            $hits               = $calendar['hits'];
            $language           = $calendar['language'];
            $version            = $calendar['version'];
            $name               = $calendar['name'];
            $calId              = $calendar['calId'];
            $accountId          = $calendar['accountId'];
            $code               = $calendar['code'];


            $db = JFactory::getDBO(); 

            $query = $db->getQuery(true);

            $columns = array('id','asset_id','parent_id','lft','rgt','level','path','extension','title','alias','note','description','published','checked_out','checked_out_time','access','params','metadesc','metakey','metadata','created_user_id','created_time','modified_user_id','modified_time','hits','language','version','name','calId','accountId','code');

            $values = array($db->quote(NULL),$db->quote($asset_id),$db->quote($parent_id),$db->quote($lft),$db->quote($rgt),$db->quote($level),$db->quote($path),$db->quote($extension),$db->quote($title),$db->quote($alias),$db->quote($note),$db->quote($description),$db->quote($published),$db->quote($checked_out),$db->quote($checked_out_time),$db->quote($access),$db->quote($params),$db->quote($metadesc),$db->quote($metakey),$db->quote($metadata),$db->quote($created_user_id),$db->quote($created_time),$db->quote($modified_user_id),$db->quote($modified_time),$db->quote($hits),$db->quote($language),$db->quote($version),$db->quote($name),$db->quote($calId),$db->quote($accountId),$db->quote($code));

            $query->insert($db->quoteName('#__categories'))->columns($db->quoteName($columns))->values(implode(',', $values)); 

            $db->setQuery($query);
            $db->execute(); 

    }

    }



    function insertEmundusCalendar(){

        $this->deleteEmCal();

        $eMConfig = JComponentHelper::getParams('com_emundus');
        $client = $this->getClient($eMConfig->get('clientId'),$eMConfig->get('clientSecret'));
        $client->refreshToken($eMConfig->get('refreshToken'));
        $service = new Google_Service_Calendar($client);  


        $calendarList = $service->calendarList->listCalendarList();  
        $calList = array_reverse($calendarList->getItems());

        $i = 0;
        foreach ($calList as $calendarListEntry) {
            $i++;
            $calendarlist = $calendarListEntry->getId();
            $titleCal = $calendarListEntry->getSummary();
            $explcalList = explode('@', $calendarlist);

            if (preg_match("/\b@\b/i", $titleCal))
                $titleCal = "Cour 1";
            

            if (!preg_match("/\b#\b/i", $explcalList[0]) && !preg_match("/\bcontacts\b/i", $explcalList[0]) && $i<=20) {
                $db = JFactory::getDBO(); 

                $query = $db->getQuery(true);

                $columns = array('catid','calid','title');

                $values = array($db->quote(NULL),$db->quote($calendarlist),$db->quote($titleCal));

                $query->insert($db->quoteName('#__emundus_calendar'))->columns($db->quoteName($columns))->values(implode(',', $values)); 

                $db->setQuery($query);
                $db->execute();
            }

        }
        
    }

    function getCodeCalCreated() {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('code');
        $query->from('#__dpcalendar_extcalendars');
        $query->order($db->quoteName('id') . 'DESC');
        $db->setQuery($query);

        $code = $db->loadColumn();

        return $code;
    }


    function insertCategories($title,$alias,$color,$calid) {
        $account = $this->getFirstCalendar();
        $code = $this->getCodeCalCreated();
        $colors = '{"category_layout":"","image":"","image_alt":"","color":"'.$color[0].'","etag":""}';


        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $columns = array('id','parent_id','lft','rgt','level','path','extension','title','alias','note','description','published','checked_out','checked_out_time','access','params','metadesc','metakey','metadata','created_user_id','created_time','modified_user_id','modified_time','hits','language','version','name','calId','accountId','code');
        $values = [
            $db->quote(NULL),
            $db->quote('1'),
            $db->quote(NULL),
            $db->quote(NULL),
            $db->quote('1'),
            $db->quote($alias),
            $db->quote('com_dpcalendar'),
            $db->quote($title),
            $db->quote($alias),
            $db->quote(''),
            $db->quote(''),
            $db->quote('1'),
            $db->quote('0'),
            $db->quote(NULL),
            $db->quote('1'),
            $db->quote($colors),
            $db->quote(''),
            $db->quote(''),
            $db->quote('{"author":"","robots":""}'),
            $db->quote('62'),$db->quote(NULL),
            $db->quote('62'),
            $db->quote(NULL),
            $db->quote('0'),
            $db->quote('*'),
            $db->quote('1'),
            $db->quote('emunduscalendar'),
            $db->quote($calid),
            $db->quote($account),
            $db->quote($code[0])
        ];
        
        $query->insert($db->quoteName('#__categories'))->columns($db->quoteName($columns))->values(implode(',', $values));    

        $db->setQuery($query);
        $db->execute();
    }

    function getCalId(){

        $db = JFactory::getDBO(); 
        $query = $db->getQuery(true);

        $conditions = array(
            $db->quoteName('name') . ' = ' . $db->quote('emunduscalendar')
        ); 


        $query->select($db->quoteName('calid'));
        $query->from($db->quoteName('#__categories'));
        $query->where($conditions);
        $query->order($db->quoteName('id') . 'ASC'); 

        $db->setQuery($query);
        $calId = $db->loadColumn();
        
        return $calId;
    }

    function getParams(){

        $db = JFactory::getDBO(); 

        $query = $db->getQuery(true);

        $query->select($db->quoteName('params'));
        $query->from($db->quoteName('#__extensions'));
        $query->where($db->quoteName('extension_id') . "=" . $db->quote('11369')); 

        $db->setQuery($query);

        $getParams = $db->loadColumn(); 
        $getParam = $getParams[0];
        
        return $getParam;

    }



    function saveParams() {

        $eMConfig = JComponentHelper::getParams('com_emundus');

        $calId = $this->getCalId();        

        if (isset($calId[0]))
            $eMConfig->set('calendarId1', $calId[0]);

        if (isset($calId[1]))
            $eMConfig->set('calendarId2', $calId[1]);

        if (isset($calId[2]))
            $eMConfig->set('calendarId3', $calId[2]);

        if (isset($calId[3]))
            $eMConfig->set('calendarId4', $calId[3]);

        if (isset($calId[4]))
            $eMConfig->set('calendarId5', $calId[4]);

        if (isset($calId[5]))
            $eMConfig->set('calendarId6', $calId[5]);

        if (isset($calId[6]))
            $eMConfig->set('calendarId7', $calId[6]);

        if (isset($calId[7]))
            $eMConfig->set('calendarId8', $calId[7]);

        if (isset($calId[8]))
            $eMConfig->set('calendarId9', $calId[8]);

        if (isset($calId[9]))
            $eMConfig->set('calendarId10', $calId[9]);

        if (isset($calId[10]))
            $eMConfig->set('calendarId11', $calId[10]);

        if (isset($calId[11]))
            $eMConfig->set('calendarId12', $calId[11]);

        if (isset($calId[12]))
            $eMConfig->set('calendarId13', $calId[12]);

        if (isset($calId[13]))
            $eMConfig->set('calendarId14', $calId[13]);

        if (isset($calId[14]))
            $eMConfig->set('calendarId15', $calId[14]);

        if (isset($calId[15]))
            $eMConfig->set('calendarId16', $calId[15]);

        if (isset($calId[16]))
            $eMConfig->set('calendarId17', $calId[16]);

        if (isset($calId[17]))
            $eMConfig->set('calendarId18', $calId[17]);

        if (isset($calId[18]))
            $eMConfig->set('calendarId19', $calId[18]);

        if (isset($calId[19]))
            $eMConfig->set('calendarId20', $calId[19]);
        

        // Save the parameters
        $componentid = JComponentHelper::getComponent('com_emundus')->id;

        $db = JFactory::getDBO();        

        $query = "UPDATE #__extensions SET params = ".$db->Quote($eMConfig->toString())." WHERE extension_id = ".$componentid;

        try {
            $db->setQuery($query);
            $db->execute();
            return true;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    function getCode() {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->select($db->quoteName('code'));
        $query->from($db->quoteName('#__emundus_setup_programmes'));

        $db->setQuery($query);

        $code = $db->loadColumn(); 

        return $code;
    }

    function setCodeCal1() {
        $code = $this->getCode();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);


        if (isset($code[0])) {
            // Fields to update.
            $fields = array(
                $db->quoteName('code') . ' = ' . $db->quote($code[0]),
            );
            
            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('id') . ' = ' . $db->quote('105')
            );
            $query->update($db->quoteName('#__categories'))->set($fields)->where($conditions);
            
            $db->setQuery($query);
            $result = $db->execute();
        }
    }


    function setCodeCal2() {
        $code = $this->getCode();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        
        
        if (isset($code[1])) {
            // Fields to update.
            $fields = array(
                $db->quoteName('code') . ' = ' . $db->quote($code[1]),
            );
            
            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('id') . ' = ' . $db->quote('106')
            );
            $query->update($db->quoteName('#__categories'))->set($fields)->where($conditions);
            
            $db->setQuery($query);
            $result = $db->execute();
        }
    }


    function setCodeCal3(){
        $code = $this->getCode();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);


        if (isset($code[2])) {
            // Fields to update.
            $fields = array(
                $db->quoteName('code') . ' = ' . $db->quote($code[2]),
            );
            
            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('id') . ' = ' . $db->quote('107')
            );
            $query->update($db->quoteName('#__categories'))->set($fields)->where($conditions);
            
            $db->setQuery($query);
            $result = $db->execute();
        }
    }


    function setCodeCal4(){
        $code = $this->getCode();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        if (isset($code[3])) {
            // Fields to update.
            $fields = array(
                $db->quoteName('code') . ' = ' . $db->quote($code[3]),
            );
            
            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('id') . ' = ' . $db->quote('108')
            );
            $query->update($db->quoteName('#__categories'))->set($fields)->where($conditions);
            
            $db->setQuery($query);
            $result = $db->execute();
        }

    }


    function setCodeCal5(){
        $code = $this->getCode();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);


        if (isset($code[4])) {
            // Fields to update.
            $fields = array(
                $db->quoteName('code') . ' = ' . $db->quote($code[4]),
            );
            
            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('id') . ' = ' . $db->quote('109')
            );
            $query->update($db->quoteName('#__categories'))->set($fields)->where($conditions);
            
            $db->setQuery($query);
            $result = $db->execute();
        }
    }


    function setCodeCal6(){
        $code = $this->getCode();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);


        if (isset($code[5])) {
            // Fields to update.
            $fields = array(
                $db->quoteName('code') . ' = ' . $db->quote($code[5]),
            );
            
            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('id') . ' = ' . $db->quote('110')
            );
            $query->update($db->quoteName('#__categories'))->set($fields)->where($conditions);
            
            $db->setQuery($query);
            $result = $db->execute();
        }
    }


    function setCodeCal7(){
        $code = $this->getCode();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);


        if (isset($code[6])) {
            // Fields to update.
            $fields = array(
                $db->quoteName('code') . ' = ' . $db->quote($code[6]),
            );
            
            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('id') . ' = ' . $db->quote('111')
            );
            $query->update($db->quoteName('#__categories'))->set($fields)->where($conditions);
            
            $db->setQuery($query);
            $result = $db->execute();
        }
    }


    function setCodeCal8(){
        $code = $this->getCode();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);


        if (isset($code[7])) {
            // Fields to update.
            $fields = array(
                $db->quoteName('code') . ' = ' . $db->quote($code[7]),
            );
            
            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('id') . ' = ' . $db->quote('112')
            );
            $query->update($db->quoteName('#__categories'))->set($fields)->where($conditions);
            
            $db->setQuery($query);
            $result = $db->execute();
        }
    }


    function setCodeCal9(){
        $code = $this->getCode();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);


        if (isset($code[8])) {
            // Fields to update.
            $fields = array(
                $db->quoteName('code') . ' = ' . $db->quote($code[8]),
            );
            
            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('id') . ' = ' . $db->quote('113')
            );
            $query->update($db->quoteName('#__categories'))->set($fields)->where($conditions);
            
            $db->setQuery($query);
            $result = $db->execute();
        }
    }


    function setCodeCal10(){
        $code = $this->getCode();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        if (isset($code[9])) {
            // Fields to update.
            $fields = array(
                $db->quoteName('code') . ' = ' . $db->quote($code[9]),
            );
            
            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('id') . ' = ' . $db->quote('114')
            );
            $query->update($db->quoteName('#__categories'))->set($fields)->where($conditions);
            
            $db->setQuery($query);
            $result = $db->execute();
        }
    }


    function setCodeCal11(){
        $code = $this->getCode();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);


        if (isset($code[10])) {
            // Fields to update.
            $fields = array(
                $db->quoteName('code') . ' = ' . $db->quote($code[10]),
            );
            
            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('id') . ' = ' . $db->quote('115')
            );
            $query->update($db->quoteName('#__categories'))->set($fields)->where($conditions);
            
            $db->setQuery($query);
            $result = $db->execute();
        }
    }


    function setCodeCal12(){
        $code = $this->getCode();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);


        if (isset($code[11])) {
            // Fields to update.
            $fields = array(
                $db->quoteName('code') . ' = ' . $db->quote($code[11]),
            );
            
            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('id') . ' = ' . $db->quote('116')
            );
            $query->update($db->quoteName('#__categories'))->set($fields)->where($conditions);
            
            $db->setQuery($query);
            $result = $db->execute();
        }
    }


    function setCodeCal13(){
        $code = $this->getCode();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);


        if (isset($code[12])) {
            // Fields to update.
            $fields = array(
                $db->quoteName('code') . ' = ' . $db->quote($code[12]),
            );
            
            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('id') . ' = ' . $db->quote('117')
            );
            $query->update($db->quoteName('#__categories'))->set($fields)->where($conditions);
            
            $db->setQuery($query);
            $result = $db->execute();
        }
    }


    function setCodeCal14(){
        $code = $this->getCode();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);


        if (isset($code[13])) {
            // Fields to update.
            $fields = array(
                $db->quoteName('code') . ' = ' . $db->quote($code[13]),
            );
            
            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('id') . ' = ' . $db->quote('118')
            );
            $query->update($db->quoteName('#__categories'))->set($fields)->where($conditions);
            
            $db->setQuery($query);
            $result = $db->execute();
        }
    }


    function setCodeCal15(){
        $code = $this->getCode();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);


        if (isset($code[14])) {
            // Fields to update.
            $fields = array(
                $db->quoteName('code') . ' = ' . $db->quote($code[14]),
            );

            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('id') . ' = ' . $db->quote('119')
            );
            $query->update($db->quoteName('#__categories'))->set($fields)->where($conditions);

            $db->setQuery($query);
            $result = $db->execute();
        }
    }


    function setCodeCal16(){
        $code = $this->getCode();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);


        if (isset($code[15])) {
            // Fields to update.
            $fields = array(
                $db->quoteName('code') . ' = ' . $db->quote($code[15]),
            );
            
            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('id') . ' = ' . $db->quote('120')
            );
            $query->update($db->quoteName('#__categories'))->set($fields)->where($conditions);
            
            $db->setQuery($query);
            $result = $db->execute();
        }
    }


    function setCodeCal17(){
        $code = $this->getCode();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);


        if (isset($code[16])) {
            // Fields to update.
            $fields = array(
                $db->quoteName('code') . ' = ' . $db->quote($code[16]),
            );
            
            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('id') . ' = ' . $db->quote('121')
            );
            $query->update($db->quoteName('#__categories'))->set($fields)->where($conditions);
            
            $db->setQuery($query);
            
            $result = $db->execute();
        }
    }


    function setCodeCal18(){
        $code = $this->getCode();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);


        if (isset($code[17])) {
            // Fields to update.
            $fields = array(
                $db->quoteName('code') . ' = ' . $db->quote($code[17]),
            );
            
            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('id') . ' = ' . $db->quote('122')
            );
            $query->update($db->quoteName('#__categories'))->set($fields)->where($conditions);
            
            $db->setQuery($query);
            
            $result = $db->execute();
        }
    }


    function setCodeCal19(){
        $code = $this->getCode();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);


        if (isset($code[18])) {
            // Fields to update.
            $fields = array(
                $db->quoteName('code') . ' = ' . $db->quote($code[18]),
            );
            
            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('id') . ' = ' . $db->quote('123')
            );
            $query->update($db->quoteName('#__categories'))->set($fields)->where($conditions);
            
            $db->setQuery($query);
            
            $result = $db->execute();
        }
    }


    function setCodeCal20(){
        $code = $this->getCode();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);


        if (isset($code[19])) {
            // Fields to update.
            $fields = array(
                $db->quoteName('code') . ' = ' . $db->quote($code[19]),
            );
            
            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('id') . ' = ' . $db->quote('124')
            );
            $query->update($db->quoteName('#__categories'))->set($fields)->where($conditions);
            
            $db->setQuery($query);
            
            $result = $db->execute();
        }
    }



    /*function linkPrograms(){
    $this->setCodeCal1();
    $this->setCodeCal2();
    $this->setCodeCal3();
    $this->setCodeCal4();
    $this->setCodeCal5();
    $this->setCodeCal6();
    $this->setCodeCal7();
    $this->setCodeCal8();
    $this->setCodeCal9();
    $this->setCodeCal10();
    $this->setCodeCal11();
    $this->setCodeCal12();
    $this->setCodeCal13();
    $this->setCodeCal14();
    $this->setCodeCal15();
    $this->setCodeCal16();
    $this->setCodeCal17();
    $this->setCodeCal18();
    $this->setCodeCal19();
    $this->setCodeCal20();
    }*/
    



}


?>

