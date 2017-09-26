<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Form;

// Load the required assets
DPCalendarHelper::loadLibrary(array('dpcalendar' => true));

// Add some styles
JFactory::getDocument()->addStyleDeclaration('#dp-bookings-actions-limit {float:right}
@media print {
	.noprint, .dp-share-button {
		display: none !important;
	}
	a:link:after, a:visited:after {
		display: none;
		content: "";
	}
}');

// The form element
$tmpl = JFactory::getApplication()->input->getCmd('tmpl');
if($tmpl)
{
	$tmpl = '&tmpl=' . $tmpl;
}
$this->root = new Form(
	'dp-bookings',
	JRoute::_('index.php?option=com_dpcalendar&view=bookings&Itemid=' . JFactory::getApplication()->input->getInt('Itemid') . $tmpl),
	'adminForm',
	'POST',
	array('form-validate')
);

// User timezone
DPCalendarHelper::renderLayout('user.timezone', array('root' => $this->root));

// Load the header template
$this->loadTemplate('header');

// Load the content template
$this->loadTemplate('content');

// Load the footer template
$this->loadTemplate('footer');

// Render the tree
echo DPCalendarHelper::renderElement($this->root, $this->params);
