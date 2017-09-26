<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Element;

// The params
$params = $this->params;

// Create the element which holds the map
$mc = $this->root->addChild(
	new Element(
		'container',
		array('dpcalendar-map', 'dpcalendar-fixed-map'),
		array(
			'style'          => 'width: ' . $params->get('map_view_width', '100%') . ';height: ' . $params->get('map_view_height', '600px'),
			'data-zoom'      => $params->get('map_view_zoom', 4),
			'data-latitude'  => $params->get('map_view_lat', 47),
			'data-longitude' => $params->get('map_view_long', 4)
		)
	)
);
$mc->setProtectedClass('dpcalendar-map');
$mc->setProtectedClass('dpcalendar-fixed-map');
