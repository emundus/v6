<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Element;

$params = $this->params;

if ($params->get('list_show_map', 1) != 1)
{
	return;
}

// Load the JS files
DPCalendarHelper::loadLibrary(array('maps' => true));

// Add the map element
$map = $this->root->addChild(
	new Element(
		'map',
		array('dpcalendar-map', 'dpcalendar-fixed-map'),
		array(
			'style'          => 'width: ' . $params->get('list_map_width', '100%') . ';height: ' . $params->get('list_map_height', '350px'),
			'data-zoom'      => $params->get('list_map_zoom', 6),
			'data-latitude'  => $params->get('list_map_lat', 47),
			'data-longitude' => $params->get('list_map_long', 4)
		)
	)
);
$map->setProtectedClass('dpcalendar-map');
$map->setProtectedClass('dpcalendar-fixed-map');

// Set the locations container class on the root container
$this->root->addClass('dpcalendar-locations-container', true);
