<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Form\Input;
use CCL\Content\Element\Basic\Link;
use CCL\Content\Element\Component\Icon;
use CCL\Content\Element\Basic\ListContainer;
use CCL\Content\Element\Basic\ListItem;

// The params
$params  = $this->params;

// Add the new event button when we can
if (DPCalendarHelper::canCreateEvent())
{
	// Determine the return url
	$return = JFactory::getApplication()->input->getInt('Itemid', null);
	if (! empty($return))
	{
		$return = JRoute::_('index.php?Itemid=' . $return);
	}

	// Create the link element with the icon
	$l = $this->root->addChild(new Link('add', DPCalendarHelperRoute::getFormRoute(0, $return)));
	$l->addChild(new Icon('add-icon', Icon::FILE, array(), array('title' => JText::_('JACTION_CREATE'))));
}

// The start value
$start = clone $this->startDate;
$start->modify('+ ' . $this->increment);

// The end value
$end = clone $this->endDate;
$end->modify('+ ' . $this->increment);

// The link to the next page
$nextLink = 'index.php?option=com_dpcalendar&view=list&Itemid=' . JFactory::getApplication()->input->getInt('Itemid') . '&date-start=' . $start->format('U') . '&date-end=' . $end->format('U');

// Modify the start for the prev link
$start->modify('- ' . $this->increment);
$start->modify('- ' . $this->increment);

// Modify the end for the prev link
$end->modify('- ' . $this->increment);
$end->modify('- ' . $this->increment);

// The link to the prev page
$prevLink = 'index.php?option=com_dpcalendar&view=list&Itemid=' . JFactory::getApplication()->input->getInt('Itemid') . '&date-start=' . $start->format('U') . '&date-end=' . $end->format('U');

// The pagination container
$c = $this->root->addChild(new ListContainer('pager', ListContainer::UNORDERED, array('pager', 'noprint')));
$c->setProtectedClass('pager');
$c->setProtectedClass('noprint');

$l = $c->addListItem(new ListItem('previous'));
$l->addClass('previous', true);
$l->addChild(new Link('previous-link', JRoute::_($prevLink)))->setContent('<');
$l = $c->addListItem(new ListItem('next'));
$l->addClass('next', true);
$l->addChild(new Link('next-link', JRoute::_($nextLink)))->setContent('>');
