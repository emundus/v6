<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPCalendarController extends JControllerLegacy
{

	public function display ($cachable = false, $urlparams = false)
	{
		$view = JRequest::setVar('view', JRequest::getCmd('view', 'cpanel'));
		$layout = JRequest::getCmd('layout', 'default');
		$id = JRequest::getInt('id');

		if ($view != 'event' && $view != 'location' && $view != 'booking')
		{
			DPCalendarHelper::addSubmenu(JRequest::getCmd('view', 'cpanel'));
		}

		// Check for edit form.
		if ($view == 'event' && $layout == 'edit' && ! $this->checkEditId('com_dpcalendar.edit.event', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_dpcalendar&view=events', false));

			return false;
		}

		parent::display();

		return $this;
	}
}
