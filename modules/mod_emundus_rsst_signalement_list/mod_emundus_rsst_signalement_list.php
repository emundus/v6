<?php

defined('_JEXEC') or die('Access Deny');

$document 	= JFactory::getDocument();
$document->addScript('media/mod_emundus_rsst_signalement_list/chunk-vendors.js');
$document->addStyleSheet('media/mod_emundus_rsst_signalement_list/app.css');

JText::script('COM_EMUNDUS_MOD_RSST_LIST_NO_DATA');

require(JModuleHelper::getLayoutPath('mod_emundus_rsst_signalement_list'));