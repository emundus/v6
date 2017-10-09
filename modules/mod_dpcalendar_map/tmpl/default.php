<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Container;

// Load the required JS libraries
DPCalendarHelper::loadLibrary(array('jquery' => true, 'maps' => true, 'dpcalendar' => true));
JHtml::_('script', 'com_dpcalendar/dpcalendar/views/map/default.js', false, true);

// Load the stylesheet
JHtml::_('stylesheet', 'mod_dpcalendar_map/default.css', array(), true);

// The root container
$root = new Container('dp-module-map-' . $module->id, array('root'), array('ccl-prefix' => 'dp-module-map-'));

// Set up the root container correctly
$root->addAttribute('data-popup', $params->get('show_as_popup'));
$root->addAttribute('data-popupwidth', $params->get('popup_width', 700));
$root->addAttribute('data-popupheight', $params->get('popup_height', 500));
$root->addClass('dpcalendar-map-container', true);
$root->addClass('dpcalendar-locations-container', true);

// Load the header
require JModuleHelper::getLayoutPath('mod_dpcalendar_map', 'default_header');

// Load the content
require JModuleHelper::getLayoutPath('mod_dpcalendar_map', 'default_content');

// Render the tree
echo DPCalendarHelper::renderElement($root, $params);
