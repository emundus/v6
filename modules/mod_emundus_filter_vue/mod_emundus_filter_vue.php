<?php

defined('_JEXEC') or die('Access Deny');
require_once(dirname(__FILE__).DS.'helper.php');

$document 	= JFactory::getDocument();
$document->addScript('media/mod_emundus_filter_vue/chunk-vendors_filter.js');
$document->addStyleSheet('media/mod_emundus_filter_vue/app_filter.css');

require(JModuleHelper::getLayoutPath('mod_emundus_filter_vue'));
