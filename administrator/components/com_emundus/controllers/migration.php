<?php
/**
 * @version		$Id: migration.php 750 2013-09-08 22:29:38Z brivalland $
 * @package		Joomla
 * @copyright	(C) 2008 - 2013 eMundus LLC. All rights reserved.
 * @license		GNU General Public License
 */

// ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( JText::_('RESTRICTED_ACCESS') );
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'access.php');
/**
 * Custom report controller
 * @package		Emundus
 */
class EmundusControllerMigration extends JControllerLegacy
{
	function display() {
		// Set a default view if none exists
		if (!JRequest::getCmd('view')) {
			$default = 'migration';
			JRequest::setVar('view', $default );
		}
		parent::display();

	}

	function check_table() {
		$table = JRequest::getVar('t', null, 'GET', 'none', 0);

		$migration = $this->getModel('migration');
		$col_names = $migration->getColumnsNameByTable($table);
		echo '<h1>'.JText::_('COM_EMUNDUS_MIGRATION_V4_V5').'</h1>';
		echo '<fieldset><legend><img src="/media/com_emundus/images/icones/documentary_properties_22x22.png" alt="'.JText::_('COM_EMUNDUS_MIGRATION_V4_V5').'"/> '.$table.'</legend>';
		echo "<h2><i>".implode(", ", $col_names)."</i></h1>";
		//echo "<h2>".implode(", ", $col_names)."</h2>";
		$cpt = 0;
		echo "<ul>";
		foreach ($col_names as $col) {
			$isRepeated = $migration->getIsRepeatedColumn($table, $col);
			$cpt += $isRepeated;
			echo "<li>";
			echo $col." : ".$isRepeated;
			echo "</li>";
		}
		echo "</ul>";
		echo "</fieldset>";
		//echo print_r($migration->getColumnsNameByTable($table));
		echo "<a href='index.php?option=com_emundus&view=migration&controller=migration'>".JText::_("COM_EMUNDUS_MIGRATE_RETURN")."</a> | ";
		if ($cpt > 0)
			echo "<a href='index.php?option=com_emundus&view=migration&controller=migration&task=migrate&t=".$table."'>".JText::_("COM_EMUNDUS_MIGRATE_TABLE")."</a> | ";
	}

	function migrate() {
		$table = JRequest::getVar('t', null, 'GET', 'none', 0);
		$migration = $this->getModel('migration');
		$col_names = $migration->getColumnsNameByTable($table);

		echo $migration->migrateTable($table, $col_names);

		echo "<a href='index.php?option=com_emundus&view=migration&controller=migration'>".JText::_("COM_EMUNDUS_MIGRATE_RETURN")."</a>";
	}

	/**
	* Used for migrating applications from older versions that have files linked to only one fnum yet mulltple fnums are present for the user
	* This means that we must duplicate the files to each fnum.
	* The first and possibly only use of this function will be when upgrading the ESA platform to Emundus 6
	*
	* @return bool
	*/
	function duplicatefilesforfnum() {
		jimport('joomla.log.log');
		JLog::addLogger(
		    array(
		        // Sets file name
		        'text_file' => 'com_emundus.mgr.php'
		    ),
		    // Sets messages of all log levels to be sent to the file
		    JLog::ALL,
		    // The log category/categories which should be recorded in this file
		    // In this case, it's just the one category from our extension, still
		    // we need to put it inside an array
		    array('com_emundus')
		);

		JLog::add('duplicatefilesforfnum START', JLog::ERROR, 'com_emundus');

		require_once (JPATH_COMPONENT_SITE.DS.'models'.DS.'users.php');

		$m_migration = $this->getModel('migration');
		$m_users = new EmundusModelUsers;

		// Get all users in campaign_candidatures table
		$users = $m_migration->getUsersInCC();

		if (!isset($users) || empty($users))
			return false;

		foreach ($users as $user) {
			JLog::add('USER : '.$user->applicant_id, JLog::ERROR, 'com_emundus');

			// For each user in campaign_candidature we have one or more fnums but only one is attached to a file with data
			// We need to find the file containing the data so that we can copy it to the others
			$emptyFnums = array();

			// Let's start by getting all of the fnums assosicated with the user
			$files = $m_users->getCampaignsCandidature($user->applicant_id);

			// Next step is finding the fnum that is assosicated to the data
			// For this we will go through all of the fnums and separate the one with data from the others
			$dataFnum = null;
			$emptyFnums = [];
			foreach ($files as $file) {

				if ($m_migration->testFnum($file->fnum))
					$dataFnum = $file->fnum;
				else
					$emptyFnums[] = $file->fnum;

				JLog::add('dataFnum : '.$dataFnum . '---- emptyFnums' .$file->fnum , JLog::ERROR, 'com_emundus');

			}

			// Next we will copy the one with data to the ones without
			if (!empty($dataFnum) && !empty($emptyFnums))
				$m_migration->copyFnumTablePicker($dataFnum, $emptyFnums);
		}

		return true;
	}

	/**
	* This function is meant to take all files that have been marked as validated and tag them with the 'validated' tag
	* Script is ran by calling /administrator/index.php?option=com_emundus&controller=migration&task=movevalidationtotags in URL
	*
	* @return void
	*/
	function movevalidationtotags() {
		$m_migration = $this->getModel('migration');

		$files = $m_migration->getValidatedFiles();

		if ($files === false)
			die('no files!');

		foreach ($files as $file) {
			$m_migration->tagValidations($file->fnum, $file->user);
		}

	}
}