<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\Registry\Registry;

JLoader::import('components.com_dpcalendar.helpers.dpcalendar', JPATH_ADMINISTRATOR);
if (!class_exists('DPCalendarHelper')) {
	return;
}

$state = new Registry();
$app   = JFactory::getApplication();

$context = 'com_dpcalendar.map.';

$state->set('filter.search', $app->getUserStateFromRequest($context . 'search', 'filter-search'));
$state->set('filter.location', $app->getUserStateFromRequest($context . 'location', 'location'));
$state->set('filter.radius', $app->getUserStateFromRequest($context . 'radius', 'radius', $params->get('radius', 20)));
$state->set('filter.length-type', $app->getUserStateFromRequest($context . 'length-type', 'length-type', $params->get('length_type', 'mile')));

$state->set('list.start-date', $app->getUserStateFromRequest($context . 'start-date', 'start-date'));
$state->set('list.end-date', $app->getUserStateFromRequest($context . 'end-date', 'end-date'));

require JModuleHelper::getLayoutPath('mod_dpcalendar_map', $params->get('layout', 'default'));
