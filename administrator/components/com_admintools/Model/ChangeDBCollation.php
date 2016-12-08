<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('_JEXEC') or die;

use FOF30\Model\Model;
use JFactory;

class ChangeDBCollation extends Model
{
	/**
	 * Get all tables starting with this site's prefix
	 *
	 * @return  array
	 */
	public function findTables()
	{
		/** @var DatabaseTools $dbtoolsModel */
		$dbtoolsModel = $this->container->factory->model('DatabaseTools')->tmpInstance();

		return $dbtoolsModel->findTables();
	}

	/**
	 * Change the collation of all tables in the database whose name begins with Joomla!'s prefix
	 *
	 * @param   string  $new_collation
	 *
	 * @return  bool  False if you somehow managed to install Joomla! 3 on MySQL 4.x (what the Hell?!)
	 */
	public function changeCollation($new_collation = 'utf8_general_ci')
	{
		// Make sure we have at least MySQL 4.1.2
		$db = $this->container->db;
		$old_collation = $db->getCollation();

		if ($old_collation == 'N/A (mySQL < 4.1.2)')
		{
			// We can't change the collation on MySQL versions earlier than 4.1.2
			return false;
		}

		// Get the character set's name from the collation
		$collationParts = explode('_', $new_collation);
		$charSet = $collationParts[0];

		// Get this database's name and try to change its collation
		$conf = JFactory::getConfig();
		$dbname = $db->qn($conf->get('db'));

		$queries = array(
			"ALTER DATABASE $dbname CHARACTER SET = $charSet COLLATE = $new_collation"
		);

		// We need to loop through all Joomla! tables
		$tables = $this->findTables();

		if (!empty($tables))
		{
			foreach ($tables as $tableName)
			{
				// Convert the table
				$quotedTableName = $db->qn($tableName);
				$sql = "ALTER TABLE $quotedTableName CONVERT TO CHARACTER SET $charSet COLLATE $new_collation";

				$queries[] .= $sql;

				// Convert each text column
				$sql = "SHOW FULL COLUMNS FROM $quotedTableName";
				$db->setQuery($sql);
				$columns = $db->loadAssocList();
				$mods = array(); // array to hold individual MODIFY COLUMN commands

				if (is_array($columns))
				{
					foreach ($columns as $column)
					{
						// Make sure we are redefining only columns which do support a collation
						$col = (object)$column;

						if (empty($col->Collation))
						{
							continue;
						}

						$null = $col->Null == 'YES' ? 'NULL' : 'NOT NULL';
						$default = is_null($col->Default) ? '' : "DEFAULT '" . $db->escape($col->Default) . "'";
						$mods[] = "MODIFY COLUMN `{$col->Field}` {$col->Type} $null $default COLLATE $new_collation";
					}
				}

				// Begin the modification statement
				$sql = "ALTER TABLE $quotedTableName ";

				// Add commands to modify columns
				if (!empty($mods))
				{
					$sql .= implode(', ', $mods) . ', ';
				}

				// Add commands to modify the table collation
				$queries[] = "DEFAULT CHARACTER SET $charSet COLLATE $new_collation;";
			}
		}

		// Finally, apply the changes
		foreach ($queries as $q)
		{
			$q = trim($q);

			if (!empty($q))
			{
				$db->setQuery($q);

				try
				{
					$db->execute();
				}
				catch (\Exception $e)
				{
					// Do not fail if this doesn't work
				}
			}
		}

		return true;
	}
}