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
		if ( ! JRequest::getCmd( 'view' ) ) {
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
		if($cpt > 0)
			echo "<a href='index.php?option=com_emundus&view=migration&controller=migration&task=migrate&t=".$table."'>".JText::_("COM_EMUNDUS_MIGRATE_TABLE")."</a> | ";
	}

	function migrate() {
		$table = JRequest::getVar('t', null, 'GET', 'none', 0);
		$migration = $this->getModel('migration');
		$col_names = $migration->getColumnsNameByTable($table);

		echo $migration->migrateTable($table, $col_names);

		echo "<a href='index.php?option=com_emundus&view=migration&controller=migration'>".JText::_("COM_EMUNDUS_MIGRATE_RETURN")."</a>";
	}
}