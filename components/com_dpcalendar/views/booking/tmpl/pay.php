<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

// Import the payment plugins
JPluginHelper::importPlugin('dpcalendarpay');

// Trigger the payment plugins
$button = JFactory::getApplication()->triggerEvent('onDPPaymentNew', array(
	$this->plugin,
	$this->item
));

// Render the buttons
foreach ($button as $b)
{
	echo $b;
}
