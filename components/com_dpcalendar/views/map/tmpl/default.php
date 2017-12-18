<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Container;

// The params
$params = $this->params;

// Load the JS libraries
JHtml::_('behavior.core');
DPCalendarHelper::loadLibrary(array('maps' => true, 'url' => true));

if ($params->get('map_show_event_as_popup')) {
	DPCalendarHelper::loadLibrary(array('modal' => true));
}

JHtml::_('script', 'com_dpcalendar/dpcalendar/views/map/default.min.js', ['relative' => true], ['defer' => true]);
JHtml::_('stylesheet', 'com_dpcalendar/dpcalendar/views/map/default.min.css', ['relative' => true]);

// Load the language files
JFactory::getLanguage()->load('', JPATH_ADMINISTRATOR);
JText::script('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_NO_EVENT_TEXT');

// User timezone
DPCalendarHelper::renderLayout('user.timezone', array('root' => $this->root));

// Set up the root container correctly
$this->root->addAttribute('data-popup', $params->get('map_show_event_as_popup'));
$this->root->addAttribute('data-popupwidth', $params->get('map_popup_width'));
$this->root->addAttribute('data-popupheight', $params->get('map_popup_height', 500));
$this->root->addClass('dpcalendar-map-container', true);
$this->root->addClass('dpcalendar-locations-container', true);

// The text before content
$this->root->addChild(new Container('text-before'))->setContent(JHtml::_('content.prepare', $params->get('map_textbefore')));

// Load the header
$this->loadTemplate('header');

// Load the content
$this->loadTemplate('content');

// The text after content
$this->root->addChild(new Container('text-after'))->setContent(JHtml::_('content.prepare', $params->get('map_textafter')));

// Render the tree
echo DPCalendarHelper::renderElement($this->root, $this->params);
