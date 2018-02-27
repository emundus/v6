<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();


class DPCalendarViewProfile extends \DPCalendar\View\BaseView
{

	protected $calendars = array();

	protected $readMembers = array();

	protected $writeMembers = array();

	protected $events = array();

	protected $users = array();

	protected $pagination = null;

	public function init ()
	{
		$user = JFactory::getUser();
		if ($user->guest)
		{
			JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode(JFactory::getURI())),
					JText::_('COM_DPCALENDAR_NOT_LOGGED_IN'), 'warning');
			return;
		}

		$this->calendars = $this->get('Items');
		$this->readMembers = $this->get('ReadMembers');
		$this->writeMembers = $this->get('WriteMembers');
		$this->events = $this->get('Events');
		$this->users = $this->get('Users');
		$this->pagination = $this->get('Pagination');
	}
}
