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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

// Require library + register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';
require_once dirname(__FILE__) . '/helper.php';

// Load component language
EventbookingHelper::loadLanguage();

$document = Factory::getDocument();
$rootUri  = Uri::root(true);

// Load css
$document->addStyleSheet($rootUri . '/modules/mod_eb_googlemap/asset/style.css');
EventbookingHelper::loadComponentCssForModules();

HTMLHelper::_('jquery.framework');

$document->addScript($rootUri . '/media/com_eventbooking/assets/js/eventbookingjq.js');

$config = EventbookingHelper::getConfig();

// Module parameters
$width     = $params->get('width', 100);
$height    = $params->get('height', 400);
$zoomLevel = (int) $params->get('zoom_level', 14) ?: 14;
$Itemid    = (int) $params->get('Itemid') ?: EventbookingHelper::getItemid();

if (file_exists(JPATH_ROOT . '/modules/mod_eb_googlemap/asset/marker/map_marker.png'))
{
	$markerUri = $rootUri . '/modules/mod_eb_googlemap/asset/marker/map_marker.png';
}
else
{
	$markerUri = $rootUri . '/modules/mod_eb_googlemap/asset/marker/marker.png';
}

$locations = modEventBookingGoogleMapHelper::loadAllLocations($params, $Itemid);

if (empty($locations))
{
	echo Text::_('EB_NO_EVENTS');

	return;
}

// Calculate center location of the map
$option = Factory::getApplication()->input->getCmd('option');
$view   = Factory::getApplication()->input->getCmd('view');

if ($option == 'com_eventbooking' && $view == 'location')
{
	$activeLocation = EventbookingHelperDatabase::getLocation(Factory::getApplication()->input->getInt('location_id'));

	if ($activeLocation)
	{
		$homeCoordinates = $activeLocation->lat . ',' . $activeLocation->long;
	}
}

if (empty($homeCoordinates))
{
	if (trim($params->get('center_coordinates')))
	{
		$homeCoordinates = trim($params->get('center_coordinates'));
	}
	else
	{
		$homeCoordinates = $locations[0]->lat . ',' . $locations[0]->long;
	}
}

if ($config->get('map_provider', 'googlemap') == 'googlemap')
{
	$layout = 'default';
	$document->addScript('https://maps.googleapis.com/maps/api/js?key=' . $config->get('map_api_key', ''))
		->addScript($rootUri . '/media/com_eventbooking/js/mod-eb-googlemap.min.js');
}
else
{
	$layout = 'openstreetmap';
	$document->addScript($rootUri . '/media/com_eventbooking/assets/js/leaflet/leaflet.js')
		->addStyleSheet($rootUri . '/media/com_eventbooking/assets/js/leaflet/leaflet.css')
		->addScript($rootUri . '/media/com_eventbooking/js/mod-eb-openstreetmap.min.js');
}

$document->addScriptOptions('mapLocations', $locations)
	->addScriptOptions('homeCoordinates', explode(',', $homeCoordinates))
	->addScriptOptions('zoomLevel', $zoomLevel)
	->addScriptOptions('moduleId', $module->id)
	->addScriptOptions('markerUri', $markerUri);

require JModuleHelper::getLayoutPath('mod_eb_googlemap');
