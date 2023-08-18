<?php

defined('_JEXEC') or die('Access Deny');

$document 	= JFactory::getDocument();
$document->addScript('media/mod_emundus_evaluations/chunk-vendors.js');
$document->addStyleSheet('media/mod_emundus_evaluations/app.css');
$document->addStyleSheet('media/mod_emundus_evaluations/app.css');
$document->addStyleSheet('media/com_emundus/css/emundus_files.css');

require(JModuleHelper::getLayoutPath('mod_emundus_evaluations'));
