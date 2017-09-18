<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

DPCalendarHelper::loadLibrary(array(
		'jquery' => true
));

$dispatcher = JDispatcher::getInstance();
JPluginHelper::importPlugin('dpcalendarpay');
$button = $dispatcher->trigger('onDPPaymentNew', array(
		$this->plugin,
		$this->item
));
foreach ($button as $b)
{
	echo $b;
}
