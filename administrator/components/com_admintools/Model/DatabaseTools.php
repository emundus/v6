<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('_JEXEC') || die;

use Exception;
use FOF40\Model\Model;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\Session\SessionInterface;
use JSessionStorage;
use ReflectionObject;

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

			// On Joomla 4 we cannot run CHECK TABLE. Therefore we have to assume it's not OK.
			$isOK = false;
			$result = null;

			if (version_compare(JVERSION, '3.999.999', 'lt'))
			{
				// First, check the table
				$this->executeUnpreparedQuery('CHECK TABLE ' . $db->qn($table));
				$db->setQuery('CHECK TABLE ' . $db->qn($table));
				$result = $db->loadObjectList();
			}

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

				$this->executeUnpreparedQuery('REPAIR TABLE ' . $db->qn($table));
			}

			// Finally, optimize
			if ($echo)
			{
				echo "Optimizing $table\n";
			}

			$this->executeUnpreparedQuery('OPTIMIZE TABLE ' . $db->qn($table));
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
			$db->truncateTable('#__session');

			$db->setQuery('DELETE FROM ' . $db->qn('#__session'));
			$db->execute();

			$this->executeUnpreparedQuery('OPTIMIZE TABLE ' . $db->qn('#__session'));
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

	/**
	 * Executes an unprepared SQL statement.
	 *
	 * The PDO driver doesn't distinguish between prepared and unprepared statements. Therefore we can just run anything
	 * we please. The MySQLi driver, however, has a distinction between prepared and unprepared statements. We cannot
	 * run certain SQL comments (such as OPTIMIZE and REPAIR) over a prepared statement. The MySQLi driver has a handy
	 * method called executeUnpreparedStatement which is protected and which runs this kind of statements.
	 *
	 * This here method tries to figure out if the database driver object has that method and use it instead of the
	 * prepared statement.
	 *
	 * @param $sql
	 *
	 * @return bool|mixed
	 */
	private function executeUnpreparedQuery($sql)
	{
		/** @var \JDatabaseDriver $db */
		$db = $this->container->db;

		if (version_compare(JVERSION, '3.999.999', 'le'))
		{
			return $db->setQuery($sql)->execute();
		}

		$refObj = new ReflectionObject($db);

		try
		{
			$method = $refObj->getMethod('executeUnpreparedQuery');
			$method->setAccessible(true);
			return $method->invoke($db, $sql);
		}
		catch (\ReflectionException $e)
		{
			return $db->execute();
		}
	}

}
