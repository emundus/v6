<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use CCL\Content\Element\Basic\Form;

// Load the required assets
DPCalendarHelper::loadLibrary(array('dpcalendar' => true));

// Add some CSS rules
JFactory::getDocument()->addStyleDeclaration('#dp-davcalendar-actions {margin-bottom: 10px}');

// The form element
$this->root = new Form(
	'dp-davcalendar',
	JRoute::_('index.php?option=com_dpcalendar&view=profile&c_id=' . (int)$this->item->id),
	'adminForm',
	'POST',
	array('form-validate')
);

// Load the header template
$this->loadTemplate('header');

// Render the form layout
DPCalendarHelper::renderLayout('content.form', array(
	'root'   => $this->root,
	'jform'  => $this->form,
	'return' => $this->return_page,
	'flat'   => true
));

// Render the element tree
echo DPCalendarHelper::renderElement($this->root, $this->params);
