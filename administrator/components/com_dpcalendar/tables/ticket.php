<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPCalendarTableTicket extends JTable
{

	public function __construct (&$db = null)
	{
		if ($db == null)
		{
			$db = JFactory::getDbo();
		}
		parent::__construct('#__dpcalendar_tickets', 'id', $db);

		$this->setColumnAlias('published', 'state');
	}

	public function check ()
	{
		if (! JFactory::getUser()->guest && empty($this->name))
		{
			$this->name = JFactory::getUser()->name;
		}

		if (empty($this->id))
		{
			$this->created = DPCalendarHelper::getDate()->toSql(false);
		}

		if (! $this->id && ! $this->remind_time)
		{
			$this->remind_time = 15;
			$this->remind_type = 1;
		}

		// Create the UID
		if (! $this->uid)
		{
			JLoader::import('components.com_dpcalendar.libraries.vendor.autoload', JPATH_ADMINISTRATOR);
			$this->uid = strtoupper(Sabre\VObject\UUIDUtil::getUUID());
		}

		return true;
	}
}
