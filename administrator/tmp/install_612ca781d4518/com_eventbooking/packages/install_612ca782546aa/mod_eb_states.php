<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

// Require library + register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

EventbookingHelper::loadLanguage();

EventbookingHelper::loadComponentCssForModules();

$config           = EventbookingHelper::getConfig();
$db               = Factory::getDbo();
$query            = $db->getQuery(true);
$numberLocations  = (int) $params->get('number_cities', 0);
$showNumberEvents = (int) $params->get('show_number_events', 1);

$query->select('a.state, COUNT(b.id) AS total_events')
	->from('#__eb_locations AS a')
	->innerJoin('#__eb_events AS b ON a.id = b.location_id')
	->where('a.published = 1')
	->where('b.published = 1')
	->where('b.hidden = 0')
	->where('b.access IN (' . implode(',', Factory::getUser()->getAuthorisedViewLevels()) . ')')
	->group('a.state')
	->order('a.state');

if ($config->hide_past_events)
{
	$currentDate = HTMLHelper::_('date', 'Now', 'Y-m-d');
	$query->where('DATE(b.event_date) >= ' . $db->quote($currentDate));
}

if ($numberLocations)
{
	$db->setQuery($query, 0, $numberLocations);
}
else
{
	$db->setQuery($query);
}
$rows = $db->loadObjectList();

$itemId = (int) $params->get('item_id');

if (!$itemId)
{
	$itemId = EventbookingHelper::getItemid();
}

require JModuleHelper::getLayoutPath('mod_eb_states', 'default');
