<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsModelDbtools extends F0FModel
{
	/** @var float The time the process started */
	private $startTime = null;

	/**
	 * Returns the current timestampt in decimal seconds
	 */
	private function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());

		return ((float)$usec + (float)$sec);
	}

	/**
	 * Starts or resets the internal timer
	 */
	private function resetTimer()
	{
		$this->startTime = $this->microtime_float();
	}

	/**
	 * Makes sure that no more than 3 seconds since the start of the timer have
	 * elapsed
	 *
	 * @return bool
	 */
	private function haveEnoughTime()
	{
		$now = $this->microtime_float();
		$elapsed = abs($now - $this->startTime);

		return $elapsed < 3;
	}

	/**
	 * Finds all tables using the current site's prefix
	 *
	 * @return array
	 */
	public function findTables()
	{
		static $ret = null;

		if (is_null($ret))
		{
			$db = $this->getDBO();
			$prefix = $db->getPrefix();
			$plen = strlen($prefix);
			$allTables = $db->getTableList();

			if (empty($prefix))
			{
				$ret = $allTables;
			}
			else
			{
				$ret = array();
				foreach ($allTables as $table)
				{
					if (substr($table, 0, $plen) == $prefix)
					{
						$ret[] = $table;
					}
				}
			}
		}

		return $ret;
	}

	public function repairAndOptimise($fromTable = null)
	{
		$this->resetTimer();
		$tables = $this->findTables();

		if (!empty($fromTable))
		{
			$table = '';
			while ($table != $fromTable)
			{
				$table = array_shift($tables);
			}
		}

		$db = $this->getDBO();

		while (count($tables) && $this->haveEnoughTime())
		{
			$table = array_shift($tables);

			// First, check the table
			$db->setQuery('CHECK TABLE ' . $db->quoteName($table));
			$result = $db->loadObjectList();

			$isOK = false;
			if (!empty($result))
			{
				foreach ($result as $row)
				{
					if (($row->Msg_type == 'status') && (
							($row->Msg_text == 'OK') ||
							($row->Msg_text == 'Table is already up to date')
						)
					)
					{
						$isOK = true;
					}
				}
			}

			// Run a repair only if it is required
			if (!$isOK)
			{
				// The table needs repair
				$db->setQuery('REPAIR TABLE ' . $db->quoteName($table));
				$db->execute();
			}

			// Finally, optimize
			$db->setQuery('OPTIMIZE TABLE ' . $db->quoteName($table));
			$db->execute();
		}

		if (!count($tables))
		{
			return '';
		}

		return $table;
	}

	public function purgeSessions()
	{
		$db = $this->getDBO();

		$db->setQuery('TRUNCATE TABLE ' . $db->quoteName('#__session'));
		$db->execute();

		$db->setQuery('DELETE FROM ' . $db->quoteName('#__session'));
		$db->execute();

		$db->setQuery('OPTIMIZE TABLE ' . $db->quoteName('#__session'));
		$db->execute();
	}
}