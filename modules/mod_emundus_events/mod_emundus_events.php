<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

defined('_JEXEC') or die('Access Deny');

// INCLUDES
require_once(dirname(__FILE__).DS.'helper.php');
require_once (JPATH_SITE.'/components/com_emundus/helpers/cache.php');
$hash = EmundusHelperCache::getCurrentGitHash();

$document = Factory::getDocument();
$document->addStyleSheet("modules/mod_emundus_events/css/mod_emundus_events.css?".$hash);

$table = $params->get('table', 'data_events');

$events = modEmundusEventsHelper::getEvents($table);
$bg_color = $params->get('background_color', '#FDFBFF');
$text_color = $params->get('text_color', '#622E68');
$border_color = $params->get('border_color', '#E8E2F5');

require(ModuleHelper::getLayoutPath('mod_emundus_events'));
?>
