<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPCalendarTableDavcalendar extends JTable
{

	public function __construct (&$db)
	{
		parent::__construct('#__dpcalendar_caldav_calendars', 'id', $db);
	}

	public function check ()
	{
		// Check for valid name
		if (trim($this->displayname) == '')
		{
			$this->setError(JText::_('COM_DPCALENDAR_LOCATION_ERR_TABLES_TITLE'));
			return false;
		}

		// Check for existing name
		$query = 'SELECT id FROM #__dpcalendar_caldav_calendars WHERE uri = ' . $this->_db->Quote($this->uri) . " and principaluri = 'principals/" .
				 JFactory::getUser()->username . "'";
		$this->_db->setQuery($query);

		$xid = (int) $this->_db->loadResult();
		if ($xid && $xid != (int) $this->id)
		{
			$this->setError(JText::_('COM_DPCALENDAR_LOCATION_ERR_TABLES_NAME'));
			return false;
		}

		if (empty($this->uri))
		{
			$this->uri = $this->displayname;
		}
		$this->uri = JApplication::stringURLSafe($this->uri);
		if (trim(str_replace('-', '', $this->uri)) == '')
		{
			$this->uri = JFactory::getDate()->format("Y-m-d-H-i-s");
		}

		$this->components = 'VEVENT,VTODO';
		$this->principaluri = 'principals/' . JFactory::getUser()->username;

		if ($this->ctag < 1)
		{
			$this->ctag = 1;
		}

		return true;
	}
}
