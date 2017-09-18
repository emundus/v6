<?php
require_once __DIR__ . '\php-google-api-client\vendor\autoload.php';
JLoader::import('components.com_dpcalendar.libraries.dpcalendar.syncplugin', JPATH_ADMINISTRATOR);
JPluginHelper::importPlugin( 'dpcalendar' );
 
 require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php'); 
define('APPLICATION_NAME', 'Google Calendar API PHP Emundus');
define('CREDENTIALS_PATH', __DIR__ . '/php-google-api-client/credentials/calendar-php-quickstart.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/php-google-api-client/certificates/client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at __DIR__ . '/credentials/calendar-php-quickstart.json
define('SCOPES', implode(' ', array(
  Google_Service_Calendar::CALENDAR) // CALENDAR_READONLY
));

 
// data for the function


class EmundusModelCandidatelist extends JModelLegacy {



function getUserForMail($calId)
{ 
  $db = JFactory::getDBO();
  $query = $db->getQuery(true);
  $conditions = array(
    $db->quoteName('event_id') . ' = ' . $db->quote($calId)
);
  $query->select($db->quoteName('name'));
  $query->from($db->quoteName('#__dpcalendar_tickets'));
  $query->where($conditions);
  $db->setQuery($query);
  $user = $db->loadResult();

  return $user;

}

function getIdUserForMail($calId)
{ 
  $db = JFactory::getDBO();
  $query = $db->getQuery(true);
  $conditions = array(
    $db->quoteName('event_id') . ' = ' . $db->quote($calId)
);
  $query->select($db->quoteName('user_id'));
  $query->from($db->quoteName('#__dpcalendar_tickets'));
  $query->where($conditions);
  $db->setQuery($query);
  $user = $db->loadResult();

  return $user;

}


function getMail($calId){
$db = JFactory::getDBO();
$query = $db->getQuery(true);
$conditions = array(
	$db->quoteName('event_id') . ' = ' . $db->quote($calId)
	);
$query->select($db->quoteName('email'));
$query->from($db->quoteName('#__dpcalendar_tickets'));
$query->where($conditions);

$db->setQuery($query);
$mail = $db->loadResult();

return $mail;

}

function getMailMessageConfirmedCandidate($calId){
  $user = $this->getUserForMail($calId);
    $db = JFactory::getDBO();
    $query = $db->getQuery(true);
    $conditions = array(
  $db->quoteName('lbl') . ' = ' . $db->quote('confirmed_interview')
  );
    $query->select($db->quoteName(array('emailfrom','message')));
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

   return $msgFinal;
}

function getMailMessageRefusedCandidate($calId){
  $user = $this->getUserForMail($calId);
    $db = JFactory::getDBO();
    $query = $db->getQuery(true);
    $conditions = array(
  $db->quoteName('lbl') . ' = ' . $db->quote('refused_interview')
  );
    $query->select($db->quoteName(array('emailfrom','message')));
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

   return $msgFinal;
}

function getEmailFromConfirmed(){
  $db = JFactory::getDBO();
  $query = $db->getQuery(true);
  $conditions = array(
  $db->quoteName('lbl') . ' = ' . $db->quote('confirmed_interview')
  );
  $query->select($db->quoteName('emailfrom'));
  $query->from($db->quoteName('#__emundus_setup_emails'));
  $query->where($conditions);

  $db->setQuery($query);
  $result = $db->loadResult();

  return $result;
}

function getEmailFromRefused(){
  $db = JFactory::getDBO();
  $query = $db->getQuery(true);
  $conditions = array(
  $db->quoteName('lbl') . ' = ' . $db->quote('refused_interview')
  );
  $query->select($db->quoteName('emailfrom'));
  $query->from($db->quoteName('#__emundus_setup_emails'));
  $query->where($conditions);

  $db->setQuery($query);
  $result = $db->loadResult();

  return $result;
}

  function updateQueryStartDateTags($calId){
    $db = JFactory::getDBO();
    $query = $db->getQuery(true);
    $fields = array(
  $db->quoteName('request') . ' = ' . $db->quote('start_date|#__dpcalendar_events|id="'.$calId.'"'),
 
);
    $conditions = array(
      $db->quoteName('tag') . ' = ' . $db->quote('START_DATE'));

    $query->update($db->quoteName('#__emundus_setup_tags'))->set($fields)->where($conditions);
 
    $db->setQuery($query);
 
    $result = $db->execute();

  }


    function updateQueryEndDateTags($calId){
    $db = JFactory::getDBO();
    $query = $db->getQuery(true);
    $fields = array(
  $db->quoteName('request') . ' = ' . $db->quote('end_date|#__dpcalendar_events|id="'.$calId.'"'),
 
);
    $conditions = array(
      $db->quoteName('tag') . ' = ' . $db->quote('END_DATE'));

    $query->update($db->quoteName('#__emundus_setup_tags'))->set($fields)->where($conditions);
 
    $db->setQuery($query);
 
    $result = $db->execute();

  }

function sendMailConfirmed($calId){
  // Envoi mail
    /*
         * @var EmundusModelEmails $model
     *  */
    $this->updateQueryStartDateTags($calId);
    $this->updateQueryEndDateTags($calId);
    $current_user = JFactory::getUser();
       $model = new EmundusModelEmails;
    $email = $model->getEmail('confirmed_interview');
    $mailCand = $this->getMailTicket($calId);
    $idCand = $this->getIdUserForMail($calId);
    
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


function sendMailRefused($calId){
  // Envoi mail
    /*
         * @var EmundusModelEmails $model
     *  */
   $this->updateQueryStartDateTags($calId);
    $this->updateQueryEndDateTags($calId);
    $current_user = JFactory::getUser();
       $model = new EmundusModelEmails;
    $email = $model->getEmail('refused_interview');
    $mailCand = $this->getMailTicket($calId);
    $idCand = $this->getIdUserForMail($calId);
    
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

function getMailCurrentCoordinator(){
  $session = JFactory::getSession();
  $user = $session->get('user');

  $mail = $user->email;

  return $mail;
}

function getNameCurrentCoordinator(){
  $session = JFactory::getSession();
  $user = $session->get('user');

  $name = $user->name;

  return $name;
}

function getIdCurrentCoordinator(){
  $session = JFactory::getSession();
  $user = $session->get('user');

  $name = $user->id;

  return $name;
}

function getMessageForCoordinatorAccept(){
  $coordName = $this->getNameCurrentCoordinator();
  $db = JFactory::getDBO();
  $query = $db->getQuery(true);
  $conditions = array(
  $db->quoteName('lbl') . ' = ' . $db->quote('confirmed_interview_to_coordinator')
  );
  $query->select($db->quoteName('message'));
  $query->from($db->quoteName('#__emundus_setup_emails'));
  $query->where($conditions);

  $db->setQuery($query);
    $result = $db->loadAssocList();

    $message = strip_tags($result[0]['message']);
   $msgExpl = explode(']', $message);
    
    $nameUser = substr_replace($msgExpl[0], $coordName , 8);
    $array = array($nameUser,$msgExpl[1]);
    $msg = implode('', $array);

    $msgFinal = str_replace('&nbsp;', '',$msg);   

   return $msgFinal;
}

function getEmailFromCoordinatorAccept(){
    $db = JFactory::getDBO();
  $query = $db->getQuery(true);
  $conditions = array(
  $db->quoteName('lbl') . ' = ' . $db->quote('confirmed_interview_to_coordinator')
  );
  $query->select($db->quoteName('emailfrom'));
  $query->from($db->quoteName('#__emundus_setup_emails'));
  $query->where($conditions);

  $db->setQuery($query);
  $result = $db->loadResult();

  return $result;

}

function sendMail($calId){
    // Envoi mail
    /*
         * @var EmundusModelEmails $model
     *  */
     $this->updateQueryStartDateTags($calId);
    $this->updateQueryEndDateTags($calId);
    $current_user = JFactory::getUser();
       $model = new EmundusModelEmails;
    $email = $model->getEmail('confirmed_interview_to_coordinator');
    $mailCoord = $this->getMailCurrentCoordinator($calId);
    
    
        $mailer = JFactory::getMailer();

        
        $tags = $model->setTags($current_user->id, null, null, '');

        $from = preg_replace($tags['patterns'], $tags['replacements'], $email->emailfrom);
        $fromname = preg_replace($tags['patterns'], $tags['replacements'], $email->name);
        $to = $mailCoord;

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
        if ( $send !== true ) {
            echo 'Error sending email: ' . $send->__toString(); 
            echo json_encode((object)array('status' => false, 'msg' => JText::_('EMAIL_NOT_SENT')));
            JLog::add($send->__toString(), JLog::ERROR, 'com_emundus.email');
            exit();
        } else {
            $message = array(
                'user_id_from' => $current_user->id,
                'user_id_to' => $current_user->id,
                'subject' => $email->subject,
                'message' => $body
            );
            $model->logEmail($message);
        }

}

function getEventId($calId){
  $db = JFactory::getDBO();
  $query = $db->getQuery(true);
  $conditions = array(
    $db->quoteName('event_id') . ' = ' . $db->quote($calId));

  $query->select($db->quoteName('event_id'));
  $query->from($db->quoteName('#__dpcalendar_tickets'));
  $query->where($conditions);
  $db->setQuery($query);

  return $db->loadResult();
}

function getUserIdTicket($calId){
  $db = JFactory::getDBO();
  $query = $db->getQuery(true);
  $conditions = array(
    $db->quoteName('event_id') . ' = ' . $db->quote($calId));

  $query->select($db->quoteName('user_id'));
  $query->from($db->quoteName('#__dpcalendar_tickets'));
  $query->where($conditions);
  $db->setQuery($query);

  return $db->loadResult();
}

function getMailTicket($calId){
  $db = JFactory::getDBO();
  $query = $db->getQuery(true);
  $conditions = array(
    $db->quoteName('event_id') . ' = ' . $db->quote($calId));

  $query->select($db->quoteName('email'));
  $query->from($db->quoteName('#__dpcalendar_tickets'));
  $query->where($conditions);
  $db->setQuery($query);

  return $db->loadResult();
}

function confirmInterview($calId, $validInterview) {

$userid = $this->getIdCurrentCoordinator();
$mail = $this->getMailCurrentCoordinator();
$name = $this->getNameCurrentCoordinator();	
	
$eventid = $this->getEventId($calId);
$candidateId = $this->getUserIdTicket($calId);
$candidateMail = $this->getMailTicket($calId);
	$session = JFactory::getSession();
	$userName = $session->get('user');
 
// Fields to update.

if($validInterview == '1'){
 $db = JFactory::getDBO(); 

  $query = $db->getQuery(true);

  $columns = array('user_id','uid','email','name','state','payer_id','payer_email','raw_data');

  $values = array($db->quote($userid),$db->quote($eventid),$db->quote($mail),$db->quote($name),$db->quote('1'),$db->quote($candidateId),$db->quote($candidateMail),$db->quote('1'));

  $query->insert($db->quoteName('#__dpcalendar_bookings'))->columns($db->quoteName($columns))->values(implode(',', $values)); 

  $db->setQuery($query);
  $db->execute();
  $this->updateOriginalId($calId);
  $this->updateBookingId($calId);
$this->sendMailConfirmed($calId);
$this->sendMail($calId);

 } else if ($validInterview == '0') {
   $db = JFactory::getDbo();
 
$query = $db->getQuery(true);
 	$fields = array(
 	$db->quoteName('capacity_used') . ' = ' . $db->quote('0'),
  $db->quoteName('uid') . ' = ' . $db->quote('61'),
  $db->quoteName('fnum') . ' = ' . $db->quote('')
);

  $conditions = array(
    $db->quoteName('id') . ' = ' . $db->quote($calId)
);
 
$query->update($db->quoteName('#__dpcalendar_events'))->set($fields)->where($conditions);
 
$db->setQuery($query);
 
$result = $db->execute();
$this->sendMailRefused($calId);
$this->deleteTicket($calId);
 	
 }

// Conditions for which records should be updated.


}

function deleteTicket($calId){
      $db = JFactory::getDBO(); 

    $query = $db->getQuery(true);
    $conditions = array(
      $db->quoteName('event_id') . ' = ' . $db->quote($calId));
    $query->delete($db->quoteName('#__dpcalendar_tickets'));  
    $query->where($conditions);
    $db->setQuery($query);
    $result = $db->execute();
}

function updateOriginalId($calId){
  $idCoord = $this->getIdCurrentCoordinator();
  $db = JFactory::getDBO();
  $query = $db->getQuery(true);
  $conditions = array(
    $db->quoteName('id') . ' = ' . $db->quote($calId));

    $fields = array(
    $db->quoteName('original_id') . ' = ' . $db->quote($idCoord),
  );

    $query->update($db->quoteName('#__dpcalendar_events'))->set($fields)->where($conditions);
 
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

function updateBookingId($calId){

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
}


?>

