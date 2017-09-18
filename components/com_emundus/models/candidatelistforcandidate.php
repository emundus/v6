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


class EmundusModelCandidatelistforcandidate extends JModelLegacy {

	function getUserForMail($calId)
{ 

 $session = JFactory::getSession();
 $user = $session->get('user');

 return $user->name;

}

function getUser($calId){
	$db = JFactory::getDBO();
	$query = $db->getQuery(true);
	$conditions = array(
		$db->quoteName('id') . ' = ' . $db->quote($user));

	$query->select($db->quoteName('name'));
	$query->from($db->quoteName('#__users'));
	$db->setQuery($query);
	$user = $db->loadResult();

	return $user;
}

	function getCoordinatorForMail($calId)
{ 

  $db = JFactory::getDBO();
  $query = $db->getQuery(true);
  $conditions = array(
    $db->quoteName('uid') . ' = ' . $db->quote($calId)
);
  $query->select($db->quoteName('name'));
  $query->from($db->quoteName('#__dpcalendar_bookings'));
  $query->where($conditions);
  $db->setQuery($query);
  $user = $db->loadResult();

  return $user;

}

	function getMessageCandidate($calId){

		$user = $this->getUserForMail($calId);
		$startdate = $this->getStartDate($calId);
		$enddate = $this->getEndDate($calId);
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

		function getMessageCoordinator($calId){

		$user = $this->getCoordinatorForMail($calId);
		$startdate = $this->getStartDate($calId);
		$enddate = $this->getEndDate($calId);
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$conditions = array(
    $db->quoteName('lbl') . ' = ' . $db->quote('cancel_interview_to_coordinator')
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

	function getMailCandidate(){
		$session = JFactory::getSession();
		$user = $session->get('user');

		return $user->email;
	}

	function getNameCoordinator($calId){
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$conditions = array(
			$db->quoteName('uid') . ' = ' . $db->quote($calId));
		$query->select($db->quoteName('name'));
		$query->from($db->quoteName('#__dpcalendar_bookings'));
		$query->where($conditions);
		$db->setQuery($query);
		$result = $db->loadResult();
		return $result;
	}

    function getIdCoordinator($calId){
    $db = JFactory::getDBO();
    $query = $db->getQuery(true);

    $conditions = array(
      $db->quoteName('uid') . ' = ' . $db->quote($calId));
    $query->select($db->quoteName('user_id'));
    $query->from($db->quoteName('#__dpcalendar_bookings'));
    $query->where($conditions);
    $db->setQuery($query);
    $result = $db->loadResult();
    return $result;
  }

	function getMailCoordinator($calId){
		$nameCoord = $this->getNameCoordinator($calId);

		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$conditions = array(
			$db->quoteName('name') . ' = ' . $db->quote($nameCoord));
		$query->select($db->quoteName('email'));
		$query->from($db->quoteName('#__users'));
		$query->where($conditions);
		$db->setQuery($query);

		$result = $db->loadResult();

		return $result;

	}

	function getStartDate($calId){
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
				$conditions = array(
    $db->quoteName('id') . ' = ' . $db->quote($calId)
);
		$query->select($db->quoteName('start_date'));
		$query->from($db->quoteName('#__dpcalendar_events'));
		$query->where($conditions);
		$db->setQuery($query);
		$result = $db->loadResult();
			
		return $result;
	}

		function getEndDate($calId){
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$conditions = array(
			$db->quoteName('id') . ' = ' . $db->quote($calId)
			);
		$query->select($db->quoteName('end_date'));
		$query->from($db->quoteName('#__dpcalendar_events'));
		$query->where($conditions);
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	function getEmailFromConfirmed(){
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

	function getEmailFromConfirmedCoordinator(){
  $db = JFactory::getDBO();
  $query = $db->getQuery(true);
  $conditions = array(
  $db->quoteName('lbl') . ' = ' . $db->quote('cancel_interview_to_coordinator')
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

	function sendMailCandidate($calId){
		// Envoi mail
		/*
         * @var EmundusModelEmails $model
		 *  */
		$this->updateQueryStartDateTags($calId);
		$this->updateQueryEndDateTags($calId);
		$current_user = JFactory::getUser();
       $model = new EmundusModelEmails;
		$email = $model->getEmail('cancel_interview_to_candidate');
		$mailCoord = $this->getMailCandidate();
		
    
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


	function sendMailCoordinateur($calId){
		// Envoi mail
		/*
         * @var EmundusModelEmails $model
		 *  */
		$this->updateQueryStartDateTags($calId);
		$this->updateQueryEndDateTags($calId);
		$current_user = JFactory::getUser();
       $model = new EmundusModelEmails;
		$email = $model->getEmail('cancel_interview_to_coordinator');
		$mailCand = $this->getMailCoordinator($calId);
		$idCand = $this->getIdCoordinator($calId);
    
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

	function CancelInterview($calId,$cancelInterview){

		$myUrl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; 
		$urlExplode = explode('?', $myUrl);
		$url = $urlExplode[1];
		$viewExplode = explode('&', $url);
		$view = $viewExplode[1];
		$valueView = explode('=', $view);
		$valView = $valueView[1];


		   $db = JFactory::getDbo();

 
$query = $db->getQuery(true);
 
// Fields to update.

if ($cancelInterview == '1') {
 	$fields = array(
 	$db->quoteName('capacity_used') . ' = ' . $db->quote('0'),
 	$db->quoteName('uid') . ' = ' . $db->quote('61'),
 	$db->quoteName('original_id') . ' = ' . $db->quote('0'),
 	$db->quoteName('fnum') . ' = ' . $db->quote('')

);
 	$this->sendMailCandidate($calId);
 	$this->sendMailCoordinateur($calId);
 	$this->deleteBookings($calId);
 	$this->deleteTickets($calId);
 }

// Conditions for which records should be updated.
$conditions = array(
    $db->quoteName('id') . ' = ' . $db->quote($calId)
);
 
$query->update($db->quoteName('#__dpcalendar_events'))->set($fields)->where($conditions);
 
$db->setQuery($query);
 
$result = $db->execute();


		
	}

	function deleteBookings($calId){
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$conditions = array(
			$db->quoteName('uid') . ' = ' . $db->quote($calId));

		$query->delete($db->quoteName('#__dpcalendar_bookings'));
		$query->where($conditions);
		$db->setQuery($query);
		$db->execute();
	}


	function deleteTickets($calId){
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$conditions = array(
			$db->quoteName('event_id') . ' = ' . $db->quote($calId));

		$query->delete($db->quoteName('#__dpcalendar_tickets'));
		$query->where($conditions);
		$db->setQuery($query);
		$db->execute();
	}



}