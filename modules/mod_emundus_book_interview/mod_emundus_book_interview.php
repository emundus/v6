<?php

defined('_JEXEC') or die('Access Deny');
require_once(dirname(__FILE__).DS.'helper.php');


JHtml::stylesheet('media/com_emundus/lib/bootstrap-336/css/bootstrap.min.css');
JHtml::stylesheet('media/com_emundus/css/mod_emundus_book_interview.css');

$session = JFactory::getSession();
$user = $session->get('emundusUser');
$helper = new modEmundusBookInterviewHelper;

// First we need to check if the user has booked.
// If the user has not, we will display a button that opens a modal allowing them to book an event (and so we need to get the event info).
// If the user has we will display the date of their interview.
$user_booked = $helper->hasUserbooked($user->id);

if ($user_booked) {

    $next_interview = $helper->getNextInterview($user);
    require(JModuleHelper::getLayoutPath('mod_emundus_book_interview','showInterview'));    

} else {

    $available_events = $helper->getEvents($user);
    require(JModuleHelper::getLayoutPath('mod_emundus_book_interview','default'));    

}
    
?>