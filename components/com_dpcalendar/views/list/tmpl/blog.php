<?php
/**
 * @package    DPCalendar
* @author     Digital Peak http://www.digital-peak.com
* @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
* @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
*/
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Container;

DPCalendarHelper::loadLibrary(array('jquery' => true));

// Load the event stylesheet
JHtml::_('stylesheet', 'com_dpcalendar/dpcalendar/views/list/blog.css', array(), true);

// User timezone
DPCalendarHelper::renderLayout('user.timezone', array('root' => $this->root));

// The text before content
$this->root->addChild(new Container('text-before'))->setContent(JHtml::_('content.prepare', $this->params->get('list_textbefore')));

// Load the map
$this->loadTemplate('map');

// Load the content
$this->loadTemplate('content');

// Load the footer
$this->loadTemplate('footer');

// The text after content
$this->root->addChild(new Container('text-after'))->setContent(JHtml::_('content.prepare', $this->params->get('list_textafter')));

// Render the tree
echo DPCalendarHelper::renderElement($this->root, $this->params);
