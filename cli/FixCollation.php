<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * A command line cron job to import language Tags to jo_emundus_setup_languages table
 */

// Initialize Joomla framework
const _JEXEC = 1;

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES')) {
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';
/**
 * Cron job to trash expired cache data.
 *
 * @since  2.5
 */
class FixCollation extends JApplicationCli {

	/**
	 * Entry point for the script
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function doExecute() {
		$this->out($this->colorLog("Fix collation of database tables\n"));

		$db = JFactory::getDbo();

		$db_name = JFactory::getConfig()->get('db');
		$db_tables        = $db->getTableList();

		$sql_engine = $db->setQuery("SHOW VARIABLES LIKE 'version_comment'")->loadAssoc();
		if(empty($sql_engine)) {
			$this->out($this->colorLog("Failed to get sql engine version\n",'e'));
			return;
		}

		$sql_engine = $sql_engine['Value'];
		$collation = 'utf8mb4_0900_ai_ci';
		if(strpos($sql_engine, 'MySQL') === false) {
			$collation = 'utf8mb4_unicode_ci';
		}

		if (empty($db_tables)) {
			$this->out($this->colorLog("No tables found in the database\n",'e'));
			return;
		}

		$views = $this->getViews($db, $db_name);
		$db_tables = array_diff($db_tables, $views);

		$this->out($this->colorLog("Fixing collation of database tables\n"));

		$db->setQuery('SET sql_mode = ""')->execute();
		$db->setQuery('SET FOREIGN_KEY_CHECKS = 0')->execute();

		$emundus_tables    = array_filter($db_tables, function ($table) {
			return strpos($table, 'jos_emundus_') !== false;
		});

		foreach ($emundus_tables as $table) {
			if($table == 'jos_emundus_version') {
				continue;
			}

			$this->out($this->colorLog("Fixing collation of table $table\n"));

			if (!$this->fixFnumLengthAndCollation($table, $db)) {
				$this->out($this->colorLog("Failed to fix fnum length and collation for table $table\n",'e'));
			}

			$this->out($this->colorLog("Converting table $table to ".$collation."\n"));
			if (!$this->convertToUtf8mb4($table, $db, $collation)) {
				$this->out($this->colorLog("Failed to convert table $table to ".$collation."\n",'e'));
			}
		}

		$db->setQuery('SET FOREIGN_KEY_CHECKS = 1')->execute();

		$this->out($this->colorLog("Collation of database tables fixed\n",'s'));
	}

	private function fixFnumLengthAndCollation($table, $db): bool
	{
		$fixed = true;

		$columns = $db->setQuery('SHOW COLUMNS FROM ' . $db->quoteName($table) . ' WHERE Field LIKE "fnum%"')->loadAssocList();
		if (!empty($columns)) {
			foreach ($columns as $column) {
				$fixed = $db->setQuery('ALTER TABLE ' . $db->quoteName($table) . ' MODIFY COLUMN ' . $db->quoteName($column['Field']) . ' varchar(28)')->execute();
			}
		}

		return $fixed;
	}

	private function convertToUtf8mb4($table, $db, $collation): bool
	{
		$converted = false;
		try {
			$query = 'ALTER TABLE ' . $db->quoteName($table) . ' CONVERT TO CHARACTER SET utf8mb4 COLLATE ' . $collation;
			$converted = $db->setQuery($query)->execute();
		}
		catch (\Exception $e) {
			$this->out($e->getMessage());
		}

		return $converted;
	}

	private function getViews($db, $db_name): array
	{
		$views = $db->setQuery('SHOW FULL TABLES WHERE Table_type = \'VIEW\'')->loadAssocList();

		return array_map(function ($view) use ($db_name) {
			return $view['Tables_in_' . $db_name];
		}, $views);
	}

	private function colorLog($str, $type = 'i')
	{
		$results = $str;
		switch ($type) {
			case 'e': //error
				$results = "\033[31m$str \033[0m";
				break;
			case 's': //success
				$results = "\033[32m$str \033[0m";
				break;
			case 'w': //warning
				$results = "\033[33m$str \033[0m";
				break;
			case 'i': //info
				$results = "\033[36m$str \033[0m";
				break;
			default:
				# code...
				break;
		}

		return $results;
	}
}

JApplicationCli::getInstance('FixCollation')->execute();

?>