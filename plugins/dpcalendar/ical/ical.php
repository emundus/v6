<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

if (!JLoader::import('components.com_dpcalendar.helpers.dpcalendar', JPATH_ADMINISTRATOR)) {
	return;
}

class PlgDPCalendarIcal extends \DPCalendar\Plugin\SyncPlugin
{
	protected $identifier = 'i';

	protected function getIcalUrl($calendar)
	{
		return str_replace('webcal://', 'https://', $calendar->params->get('uri'));
	}
}
