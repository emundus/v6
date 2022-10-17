<?php

defined('_JEXEC') or die('Access Deny');

$document 	= JFactory::getDocument();
$document->addScript('media/mod_emundus_rsst_signalement_list/chunk-vendors.js');
$document->addStyleSheet('media/mod_emundus_rsst_signalement_list/app.css');
$document->addStyleSheet('https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined');

require(JModuleHelper::getLayoutPath('mod_emundus_rsst_signalement_list'));