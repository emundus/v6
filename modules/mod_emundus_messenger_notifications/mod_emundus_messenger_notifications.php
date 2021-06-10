<?php

defined('_JEXEC') or die('Access Deny');

require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');

JHtml::script('media/com_emundus/js/jquery.cookie.js');
JHtml::script('media/jui/js/bootstrap.min.js');

$user = JFactory::getUser();

$document 	= JFactory::getDocument();
$document->addStyleSheet("modules/mod_emundus_messenger_notifications/src/assets/mod_emundus_messenger_notifications.css" );
$document->addScript('media/mod_emundus_messenger_notifications/chunk-vendors.js');
$document->addStyleSheet('media/mod_emundus_messenger_notifications/app.css');

$applicant = !EmundusHelperAccess::asPartnerAccessLevel($user->id);

require(JModuleHelper::getLayoutPath('mod_emundus_messenger_notifications'));
