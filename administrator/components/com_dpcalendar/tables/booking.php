<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPCalendarTableBooking extends JTable
{

	public function __construct (&$db = null)
	{
		if ($db == null)
		{
			$db = JFactory::getDbo();
		}
		parent::__construct('#__dpcalendar_bookings', 'id', $db);
	}

	public function check ()
	{
		if (! JFactory::getUser()->guest && empty($this->user_id) && empty($this->id))
		{
			$this->user_id = JFactory::getUser()->id;
		}
		if (! JFactory::getUser()->guest && empty($this->email))
		{
			$this->email = JFactory::getUser()->email;
		}
		if (! JFactory::getUser()->guest && empty($this->name))
		{
			$this->name = JFactory::getUser()->name;
		}

		if (empty($this->id))
		{
			$this->book_date = DPCalendarHelper::getDate()->toSql(false);
		}

		// Create the UID
		if (! $this->uid)
		{
			JLoader::import('components.com_dpcalendar.libraries.vendor.autoload', JPATH_ADMINISTRATOR);
			$this->uid = strtoupper(Sabre\VObject\UUIDUtil::getUUID());
		}

		// Check for valid name
		if (trim($this->email) == '' && $this->user_id < 1)
		{
			$this->setError(JText::_('COM_DPCALENDAR_BOOKING_ERR_TABLES_EMAIL'));
			return false;
		}

		return true;
	}

	public function publish ($pks = null, $state = 1, $userId = 0)
	{
		$k = $this->_tbl_key;

		// Sanitize input.
		JArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state = (int) $state;

		// If there are no primary keys set check to see if the instance key is
		// set.
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array(
						$this->$k
				);
			}
			else
			{
				$this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
				return false;
			}
		}

		// Build the WHERE clause for the primary keys.
		$where = $k . '=' . implode(' OR ' . $k . '=', $pks);

		// Determine if there is checkin support for the table.
		if (! property_exists($this, 'checked_out') || ! property_exists($this, 'checked_out_time'))
		{
			$checkin = '';
		}
		else
		{
			$checkin = ' AND (checked_out = 0 OR checked_out = ' . (int) $userId . ')';
		}

		// Update the publishing state for rows with the given primary keys.
		$this->_db->setQuery(
				'UPDATE ' . $this->_db->quoteName($this->_tbl) . ' SET ' . $this->_db->quoteName('state') . ' = ' . (int) $state . ' WHERE (' . $where .
						 ')' . $checkin);

		try
		{
			$this->_db->execute();
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());
			return false;
		}

		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
		{
			// Checkin the rows.
			foreach ($pks as $pk)
			{
				$this->checkin($pk);
			}
		}

		// If the JTable instance value is in the list of primary keys that were
		// set, set the instance.
		if (in_array($this->$k, $pks))
		{
			$this->state = $state;
		}

		$this->setError('');
		return true;
	}
}
