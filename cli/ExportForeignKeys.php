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
use Joomla\Filesystem\File;

const _JEXEC = 1;

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php')) {
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
class ExportForeignKeys extends JApplicationCli
{


	/**
	 * Entry point for the script
	 *
	 * @return  void
	 * php cli/ExportForeignKeys.php
	 * @since   2.5
	 */
	public function doExecute()
	{

		$db = JFactory::getDbo();

		echo ('Exporting foreign keys');

		echo ('This command will export your foreign keys.');
		$totalTime = microtime(true);

		$destinationFile = 'tmp/foreign_keys.xml';
		$tables = $db->getTableList();


		// open file for writing
		$opened = fopen($destinationFile, 'w');

		if (!$opened) {
			echo "\n" . sprintf('Error opening file %s', $destinationFile);
			exit;
		} else {
			echo "\n" . sprintf('File %s opened for writing', $destinationFile);

			// add xml header
			$dom = new \DOMDocument('1.0', 'utf-8');
			$dom->formatOutput = true;

			$xml = $dom->createElement('tables');

			foreach ($tables as $table) {
				$taskTime = microtime(true);

				echo "\n" . (sprintf('Processing the %s table', $table));

				$query = 'SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = "' . $table . '" AND REFERENCED_TABLE_NAME IS NOT NULL';
				$db->setQuery($query);
				$foreign_keys = $db->loadAssocList();

				if (!empty($foreign_keys)) {
					$xml_table = $dom->createElement('table');
					$xml_table->setAttribute('name', $table);

					foreach ($foreign_keys as $foreign_key) {
						$xml_row = $dom->createElement('row');
						$xml_row->setAttribute('constraint_name', $foreign_key['CONSTRAINT_NAME']);
						$xml_row->setAttribute('column_name', $foreign_key['COLUMN_NAME']);
						$xml_row->setAttribute('referenced_table_name', $foreign_key['REFERENCED_TABLE_NAME']);
						$xml_row->setAttribute('referenced_column_name', $foreign_key['REFERENCED_COLUMN_NAME']);

						$xml_table->appendChild($xml_row);
					}

					$xml->appendChild($xml_table);
				}

				echo "\n" . (sprintf('The %s table processed in %s seconds', $table, microtime(true) - $taskTime));
			}

			$dom->appendChild($xml);

			fwrite($opened, $dom->saveXML());

			echo "\n" . (sprintf('The foreign keys exported in %s seconds', microtime(true) - $totalTime));

			fclose($opened);
		}
	}

}

JApplicationCli::getInstance('ExportForeignKeys')->execute();
