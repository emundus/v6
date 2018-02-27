<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Container;

DPCalendarHelper::loadLibrary(array('dpcalendar' => true));

// Load the event stylesheet
JHtml::_('stylesheet', 'com_dpcalendar/dpcalendar/views/list/default.min.css', ['relative' => true]);

// Load the language file
JFactory::getLanguage()->load('com_dpcalendar', JPATH_ADMINISTRATOR . '/components/com_dpcalendar');

// User timezone
DPCalendarHelper::renderLayout('user.timezone', array('root' => $this->root));

// The text before content
$this->root->addChild(new Container('text-before'))->setContent(JHtml::_('content.prepare', $this->params->get('list_textbefore')));

// Load the map
$this->loadTemplate('map');

// Load the navigation
$this->loadTemplate('navigation');

// Load the header
$this->loadTemplate('header');

// Load the form
$this->loadTemplate('form');

// Load the content
$this->loadTemplate('content');

// The text after content
$this->root->addChild(new Container('text-after'))->setContent(JHtml::_('content.prepare', $this->params->get('list_textafter')));

// Render the tree
echo DPCalendarHelper::renderElement($this->root, $this->params);
