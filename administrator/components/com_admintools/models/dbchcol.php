<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.model');

/**
 * Database collation changer model
 * @author nicholas
 */
class AdmintoolsModelDbchcol extends FOFModel
{
	/**
	 * Get all tables starting with this site's prefix
	 * @return array
	 */
	public function findTables()
	{
		JLoader::import('models.dbtools', JPATH_COMPONENT_ADMINISTRATOR);
		if(interface_exists('JModel')) {
			$dbtoolsModel = JModelLegacy::getInstance('Dbtools','AdmintoolsModel');
		} else {
			$dbtoolsModel = JModel::getInstance('Dbtools','AdmintoolsModel');
		}
		return $dbtoolsModel->findTables();
	}
	
	public function changeCollation($new_collation = 'utf8_general_ci')
	{
		// Make sure we have at least MySQL 4.1.2
		$db = $this->getDBO();
		$old_collation = $db->getCollation();
		if($old_collation == 'N/A (mySQL < 4.1.2)')
		{
			// We can't change the collation on MySQL versions earlier than 4.1.2
			return false;
		}

		// Get this database's name and try to change its collation
		$conf = JFactory::getConfig();
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$dbname = $conf->get('db');
		} else {
			$dbname = $conf->getValue('config.db');
		}
		$sql = "ALTER DATABASE `$dbname` DEFAULT COLLATE $new_collation";
		$db->setQuery($sql);
		$db->execute();
		
		// Get all tables
		$tables = $this->findTables();
		$queryStack = '';
		if(!empty($tables)) foreach($tables as $tableName) {
			$sql = 'SHOW FULL COLUMNS FROM `'.$tableName.'`';
			$db->setQuery($sql);
			$columns = $db->loadAssocList();
			$mods = array(); // array to hold individual MODIFY COLUMN commands
			if(is_array($columns)) foreach($columns as $column)
			{
				// Make sure we are redefining only columns which do support a collation
				$col = (object)$column;
				if( empty($col->Collation) ) continue;

				$null = $col->Null == 'YES' ? 'NULL' : 'NOT NULL';
				$default = is_null($col->Default) ? '' : "DEFAULT '".$db->escape($col->Default)."'";
				$mods[] = "MODIFY COLUMN `{$col->Field}` {$col->Type} $null $default COLLATE $new_collation";
			}

			// Begin the modification statement
			$sql = "ALTER TABLE `$tableName` ";

			// Add commands to modify columns
			if(!empty($mods))
			{
				$sql .= implode(', ', $mods).', ';
			}

			// Add commands to modify the table collation
			$sql .= 'DEFAULT CHARACTER SET UTF8 COLLATE utf8_general_ci;';
			$queryStack .= "$sql\n";
		}
		
		if(!empty($queryStack)) {
			if(version_compare(JVERSION, '3.0', 'ge')) {
				// Joomla! 3.0 and later... God help us!
				$queries = explode(';', $queryStack);
				foreach($queries as $q) {
					$q = trim($q);
					if(!empty($q)) {
						$db->setQuery($q);
						$db->execute();
					}
				}
			} else {
				// Execute the stacked queries in a transaction
				$db->setQuery($queryStack);
				$db->queryBatch(false, true);
			}
				
		}
	}
}