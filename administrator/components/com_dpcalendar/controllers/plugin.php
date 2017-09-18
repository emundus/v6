<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPCalendarControllerPlugin extends JControllerLegacy
{

	public function action ()
	{
		$input = JFactory::getApplication()->input;
		DPCalendarHelper::doPluginAction($input->getWord('dpplugin', $input->getWord('plugin')), $input->getWord('action'));

		JFactory::getApplication()->close();
	}
}
