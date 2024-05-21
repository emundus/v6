<?php

defined('_JEXEC') or die('Access Deny');

require_once (JPATH_SITE.'/components/com_emundus/helpers/cache.php');
$hash = EmundusHelperCache::getCurrentGitHash();

$document 	= JFactory::getDocument();
$document->addScript('media/mod_emundus_evaluations/chunk-vendors.js?'.$hash);
$document->addStyleSheet('media/mod_emundus_evaluations/app.css?'.$hash);
$document->addStyleSheet('media/com_emundus/css/emundus_files.css?'.$hash);

require(JModuleHelper::getLayoutPath('mod_emundus_evaluations'));
