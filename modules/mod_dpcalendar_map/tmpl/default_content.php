<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Element;
use CCL\Content\Element\Basic\Container;
use CCL\Content\Element\Basic\Frame;

if ($params->get('show_as_popup'))
{
	// Load the required JS libraries
	DPCalendarHelper::loadLibrary(array('jquery' => true, 'dpcalendar' => true));
	JHtml::_('behavior.modal', '.dp-module-map-event-link-invalid');

	// The root container for the modal iframe
	$m = $root->addChild(new Container('modal', array('modal')));
	$m->addClass('dp-module-map-modal', true);

	// Add the iframe which holds the content
	$m->addChild(new Frame('frame', ''));
}

// Create the element which holds the map
$mc = $root->addChild(
	new Element(
		'container',
		array('dpcalendar-map', 'dpcalendar-fixed-map'),
		array(
			'style'          => 'width: ' . $params->get('width', '100%') . ';height: ' . $params->get('height', '600px'),
			'data-zoom'      => $params->get('zoom', 4),
			'data-latitude'  => $params->get('lat', 47),
			'data-longitude' => $params->get('long', 4)
		)
	)
);
$mc->setProtectedClass('dpcalendar-map');
$mc->setProtectedClass('dpcalendar-fixed-map');
