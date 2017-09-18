<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.modellist');

class DPCalendarModelTools extends JModelLegacy
{

	public function getResourcesFromTransifex ()
	{
		JLoader::import('components.com_dpcalendar.helpers.transifex', JPATH_ADMINISTRATOR);

		$resources = DPCalendarHelperTransifex::getData('resources');

		$data = json_decode($resources['data']);
		usort($data, function  ($r1, $r2)
		{
			return strcmp($r1->name, $r2->name);
		});

		return $data;
	}
}
