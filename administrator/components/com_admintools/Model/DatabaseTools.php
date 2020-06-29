<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('_JEXEC') or die;

use Exception;
use FOF30\Model\Model;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\Session\SessionInterface;
use JSessionStorage;

class DatabaseTools extends Model
{
	/** @var float The time the process started */
	private $startTime = null;

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
			$db        = $this->container->db;
			$prefix    = $db->getPrefix();
			$plen      = strlen($prefix);
			$allTables = $db->getTableList();

			if (empty($prefix))
			{
				$ret = $allTables;
			}
			else
			{
				$ret = [];
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

	/**
	 * Ask Joomla! to garbage collect expired session.
	 *
	 * @return  void
	 *
	 * @since   5.0.0
	 */
	public function garbageCollectSessions()
	{
		if (version_compare(JVERSION, '3.999.999', 'lt'))
		{
			$this->gcSessionJoomla3();

			return;
		}

		$this->gcSessionJoomla4();
	}

	/**
	 * Clean and optimize the #__sessions table. The idea is that the sessions table may get corrupt over time due to
	 * the number of read / write operations and / or ending up with stuck phantom session records.
	 *
	 * @return  void
	 */
	public function purgeSessions()
	{
		$db = $this->container->db;

		try
		{
			$db->setQuery('TRUNCATE TABLE ' . $db->qn('#__session'));
			$db->execute();

			$db->setQuery('DELETE FROM ' . $db->qn('#__session'));
			$db->execute();

			$db->setQuery('OPTIMIZE TABLE ' . $db->qn('#__session'));
			$db->execute();
		}
		catch (Exception $e)
		{
			return;
		}
	}

	/**
	 * Returns the current timestampt in decimal seconds
	 */
	private function microtime_float()
	{
		[$usec, $sec] = explode(" ", microtime());

		return ((float) $usec + (float) $sec);
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
		$now     = $this->microtime_float();
		$elapsed = abs($now - $this->startTime);

		return $elapsed < 3;
	}

	/**
	 * Asks the Joomla! 3 session storage handler to garbage collect any open sessions. This SHOULD remove expired
	 * sessions, as long as Joomla implements this feature for the given handler.
	 *
	 * @return  void
	 *
	 * @since   5.7.0
	 */
	private function gcSessionJoomla3()
	{
		$options = [];
		$conf    = $this->container->platform->getConfig();
		$handler = $conf->get('session_handler', 'none');

		// config time is in minutes
		$options['expire'] = ($conf->get('lifetime')) ? $conf->get('lifetime') * 60 : 900;

		$storage = JSessionStorage::getInstance($handler, $options);
		$storage->gc($options['expire']);
	}

	/**
	 * Asks the Joomla 4 session object to garbage collect any open sessions. This SHOULD remove expired sessions, as
	 * long as Joomla implements this feature for the session handler used internally in the object.
	 *
	 * @return  void
	 *
	 * @since   5.7.0
	 */
	private function gcSessionJoomla4()
	{
		try
		{
			$app = Factory::getApplication();

			if (!($app instanceof CMSApplication))
			{
				return;
			}

			$session = $app->getSession();

			if (!($session instanceof SessionInterface))
			{
				return;
			}

			$session->gc();
		}
		catch (Exception $e)
		{
			// It's OK if we fail. No harm, no foul.
		}
	}
}
