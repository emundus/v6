<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('_JEXEC') or die;

use FOF30\Model\Model;

class DatabaseTools extends Model
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
			$db = $this->container->db;
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

	public function repairAndOptimise($fromTable = null, $echo = false)
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

		$db = $this->container->db;

		while (count($tables) && $this->haveEnoughTime())
		{
			$table = array_shift($tables);

			// First, check the table
			$db->setQuery('CHECK TABLE ' . $db->qn($table));
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
				if ($echo)
				{
					echo "Repairing $table\n";
				}

				$db->setQuery('REPAIR TABLE ' . $db->qn($table));
				$db->execute();
			}

			// Finally, optimize
			if ($echo)
			{
				echo "Optimizing $table\n";
			}

			$db->setQuery('OPTIMIZE TABLE ' . $db->qn($table));
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
		$db = $this->container->db;

		$db->setQuery('TRUNCATE TABLE ' . $db->qn('#__session'));
		$db->execute();

		$db->setQuery('DELETE FROM ' . $db->qn('#__session'));
		$db->execute();

		$db->setQuery('OPTIMIZE TABLE ' . $db->qn('#__session'));
		$db->execute();
	}
}