<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('_JEXEC') || die;

use FOF40\Model\Model;
use JDatabaseQuery;
use RuntimeException;

class ChangeDBCollation extends Model
{
	/**
	 * Change the collation of all tables in the database, even those with a different prefix than your Joomla
	 * installation.
	 *
	 * @param   string  $newCollation  The new collation, e.g. "utf8mb4_unicode_ci"
	 *
	 * @return  bool  False if you somehow managed to install Joomla! on MySQL 4.x (very much a leftover from the past)
	 */
	public function changeCollation($newCollation = 'utf8_general_ci')
	{
		// Make sure we have at least MySQL 4.1.2
		$db            = $this->container->db;
		$old_collation = $db->getCollation();

		if ($old_collation == 'N/A (mySQL < 4.1.2)')
		{
			// We can't change the collation on MySQL versions earlier than 4.1.2
			return false;
		}

		// Change the collation of the database itself
		$this->changeDatabaseCollation($newCollation);

		// Change the collation of each table
		$tables = $db->getTableList();

		// No tables to convert...?
		if (empty($tables))
		{
			return true;
		}

		foreach ($tables as $tableName)
		{
			$this->changeTableCollation($tableName, $newCollation);
		}

		return true;
	}

	/**
	 * Execute a query against the site's database
	 *
	 * @param   string|JDatabaseQuery  $query       The query to execute
	 * @param   bool                   $silentFail  True to suppress exceptions for SQL errors
	 *
	 * @return  void
	 * @throws  RuntimeException  When $silentFail is false and there's a DB error.
	 */
	private function query($query, $silentFail = true)
	{
		$db = $this->container->db;

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (RuntimeException $e)
		{
			if (!$silentFail)
			{
				throw $e;
			}
		}
	}

	/**
	 * Change the database collation.
	 *
	 * This tries to change the collation of the entire database, setting the default for newly created tables and
	 * columns. We have the reasonable expectation that this will fail on most live hosts.
	 *
	 * @param   string  $newCollation  The new collation, e.g. "utf8mb4_unicode_ci"
	 *
	 * @return  void
	 */
	private function changeDatabaseCollation($newCollation)
	{
		$db             = $this->container->db;
		$collationParts = explode('_', $newCollation);
		$charset        = $collationParts[0];
		$dbName         = $this->container->platform->getConfig()->get('db');

		$this->query(sprintf(
			"ALTER DATABASE %s CHARACTER SET = %s COLLATE = %s",
			$db->qn($dbName),
			$charset,
			$newCollation
		));
	}

	/**
	 * Changes the collation of a table and its text columns
	 *
	 * @param   string  $tableName
	 * @param   string  $newCollation
	 * @param   bool    $changeColumns
	 *
	 * @return  void
	 */
	private function changeTableCollation($tableName, $newCollation, $changeColumns = true)
	{
		$db             = $this->container->db;
		$collationParts = explode('_', $newCollation);
		$charset        = $collationParts[0];

		// Change the collation of the table itself.
		$this->query(sprintf(
			"ALTER TABLE %s CONVERT TO CHARACTER SET %s COLLATE %s",
			$db->qn($tableName),
			$charset,
			$newCollation
		));

		// Are we told not to bother with text columns?
		if (!$changeColumns)
		{
			return;
		}

		// Convert each text column
		try
		{
			$columns = $db->getTableColumns($tableName, false);
		}
		catch (RuntimeException $e)
		{
			$columns = [];
		}

		// The table is broken or MySQL cannot report any columns for it. Early return.
		if (!is_array($columns) || empty($columns))
		{
			return;
		}

		$modifyColumns = [];

		foreach ($columns as $col)
		{
			// Make sure we are redefining only columns which do support a collation
			if (empty($col->Collation))
			{
				continue;
			}

			$modifyColumns[] = sprintf("MODIFY COLUMN %s %s %s %s COLLATE %s",
				$db->qn($col->Field),
				$col->Type,
				(strtoupper($col->Null) == 'YES') ? 'NULL' : 'NOT NULL',
				is_null($col->Default) ? '' : sprintf('DEFAULT %s', $db->q($col->Default)),
				$newCollation
			);
		}

		// No text columns to modify? Return immediately.
		if (empty($modifyColumns))
		{
			return;
		}

		// Issue an ALTER TABLE statement which modifies all text columns.
		$this->query(sprintf(
			'ALTER TABLE %s %s',
			$db->qn($tableName),
			implode(', ', $modifyColumns
			)));
	}
}
