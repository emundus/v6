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


class EmundusModelInterviewrequest extends JModelLegacy {


function getIdEvent($calId){
$db = JFactory::getDBO(); 

    $query = $db->getQuery(true);

    $query->select($db->quoteName('id'));

    $query->from($db->quoteName('#__dpcalendar_events'));

    $query->where($db->quoteName('catid') .'='.$db->quote($calId));

    $db->setQuery($query);

    $cal = $db->loadColumn();

    return $cal;

}

function getUserConnected(){
  $session = JFactory::getSession();
$user = $session->get('user');

return $user->id;
}

function getNameUserConnected(){
  $session = JFactory::getSession();
$user = $session->get('user');

return $user->name;
}

function getIdUserConnected(){
  $session = JFactory::getSession();
$user = $session->get('user');

return $user->id;
}

function getMailUser(){
  $session = JFactory::getSession();
  $user = $session->get('user');

  return $user->email;

}

function getStartDate(){

  $user = JFactory::getUser();
$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;
$value = $jinput->getValue('jos_dpcalendar_events___date');
$db = JFactory::getDBO();
$query = $db->getQuery(true);
$conditions = array(
    $db->quoteName('id') . ' = ' . $db->quote($value[0])
);
$query->select($db->quoteName('start_date'));
$query->from($db->quoteName('#__dpcalendar_events'));
$query->where($conditions);
$db->setQuery($query);
$result = $db->loadResult();

return $result;
}

function getEndDate(){

  $user = JFactory::getUser();
$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;
$value = $jinput->getValue('jos_dpcalendar_events___date');

$db = JFactory::getDBO();
$query = $db->getQuery(true);
$conditions = array(
    $db->quoteName('id') . ' = ' . $db->quote($value[0])
);
$query->select($db->quoteName('end_date'));
$query->from($db->quoteName('#__dpcalendar_events'));
$query->where($conditions);
$db->setQuery($query);
$result = $db->loadResult();

return $result;
}

function getMessageMail() {

  $startDate = $this->getStartDate();
  $endDate = $this->getEndDate();

  $user = $this->getNameUserConnected();
  $db = JFactory::getDBO();
  $query = $db->getQuery(true);
  $conditions = array(
    $db->quoteName('lbl') . ' = ' . $db->quote('request_interview')
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

    $msgs = str_replace('&nbsp;', '',$msg);

    $messages = str_replace('{startdate}', $startDate, $msgs);

    $msgFinal = str_replace('{enddate}', $endDate, $messages);

    return $msgFinal;   

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

function sendMail($calId){

  // Envoi mail
    /*
         * @var EmundusModelEmails $model
     *  */
    $this->updateQueryStartDateTags($calId);
    $this->updateQueryEndDateTags($calId);
    $current_user = JFactory::getUser();
       $model = new EmundusModelEmails;
    $email = $model->getEmail('request_interview');
    $mailCand = $this->getMailUser();
    $idCand = $this->getIdUserConnected();
    

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

function getFnum(){
  $user = $this->getUserConnected();
  $db = JFactory::getDBO();
  $query = $db->getQuery(true);
  $conditions = array(
    $db->quoteName('applicant_id') . ' = ' . $db->quote($user));

  $query->select($db->quoteName('fnum'));
  $query->from($db->quoteName('#__emundus_campaign_candidature'));
  $query->where($conditions);
  $db->setQuery($query);


  return $db->loadResult();
}

function bookInterview($calId){
  $user = $this->getUserConnected();
  $fnum = $this->getFnum();

   $db = JFactory::getDbo();
 
$query = $db->getQuery(true);
 
// Fields to update.
$fields = array(
    $db->quoteName('capacity_used') . ' = ' . $db->quote('1'),
    $db->quoteName('uid') . ' = ' . $db->quote($user),
    $db->quoteName('fnum') . ' = ' . $db->quote($fnum)
);
 
// Conditions for which records should be updated.
$conditions = array(
    $db->quoteName('id') . ' = ' . $db->quote($calId)
);
 
$query->update($db->quoteName('#__dpcalendar_events'))->set($fields)->where($conditions);
 
$db->setQuery($query);
 
$result = $db->execute();

$this->ticketInterview($calId);
$this->sendMail($calId);
}

function ticketInterview($calId){
   $user = $this->getUserConnected();
   $email = $this->getMailUser();
   $name = $this->getNameUserConnected();

  $db = JFactory::getDBO(); 

  $query = $db->getQuery(true);

  $columns = array('event_id','user_id','email','name','state');

  $values = array($db->quote($calId),$db->quote($user),$db->quote($email),$db->quote($name),$db->quote('1'));

  $query->insert($db->quoteName('#__dpcalendar_tickets'))->columns($db->quoteName($columns))->values(implode(',', $values)); 

  $db->setQuery($query);
  $db->execute();
}



}


?>

