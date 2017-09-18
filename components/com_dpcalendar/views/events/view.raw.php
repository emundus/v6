<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.view');
JLoader::import('libraries.fullcalendar.fullcalendar', JPATH_COMPONENT);

class DPCalendarViewEvents extends JViewLegacy
{

	public function display ($tpl = null)
	{
		// Don't display errors as we want to send them nicely in the ajax
		// response
		ini_set('display_errors', false);

		// Registering shutdown function to catch fatal errors
		register_shutdown_function(array(
				$this,
				'handleError'
		));

		JRequest::setVar('list.limit', 1000);

		$start = DPCalendarHelper::getDate(JRequest::getInt('date-start'), false, DPCalendarHelper::getDate()->getTimezone()->getName());
		// We always subtract the offset, even when it is negative already
		JRequest::setVar('date-start', $start->format('U') - str_replace('-', '', DPCalendarHelper::getDate()->getTimezone()->getOffset($start)));

		$end = DPCalendarHelper::getDate(JRequest::getInt('date-end'), false, DPCalendarHelper::getDate()->getTimezone()->getName());
		// We always subtract the offset, even when it is negative already
		JRequest::setVar('date-end', $end->format('U') + str_replace('-', '', DPCalendarHelper::getDate()->getTimezone()->getOffset($end)));

		$this->get('State')->set('filter.state', 1);
		$this->items = $this->get('Items');

		$tmp = clone JFactory::getApplication()->getParams();
		$tmp->merge($this->get('State')->params);
		$this->params = $tmp;

		$this->compactMode = JRequest::getVar('compact', 0);
		if ($this->compactMode == 1)
		{
			$this->setLayout('compact');
		}

		parent::display($tpl);
	}

	public function handleError ()
	{
		// Getting last error
		$error = error_get_last();
		if ($error && ($error['type'] == E_ERROR || $error['type'] == E_USER_ERROR))
		{
			ob_clean();
			echo json_encode(
					array(
							array(
									'data' => array(),
									'messages' => array(
											'error' => array(
													$error['message'] . ': <br/>' . $error['file'] . ' ' . $error['line']
											)
									)
							)
					));

			// We always send ok as we want to be able to handle the error by
			// our own
			header('Status: 200 Ok');
			header('HTTP/1.0 200 Ok');
			die();
		}
	}
}
