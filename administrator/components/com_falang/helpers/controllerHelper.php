<?php
/**
 * Joom!Fish - Multi Lingual extention and translation manager for Joomla!
 * Copyright (C) 2003 - 2011, Think Network GmbH, Munich
 *
 * All rights reserved.  The Joom!Fish project is a set of extentions for
 * the content management system Joomla!. It enables Joomla!
 * to manage multi lingual sites especially in all dynamic information
 * which are stored in the database.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307,USA.
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -----------------------------------------------------------------------------
 * $Id: controllerHelper.php 1551 2011-03-24 13:03:07Z akede $
 * @package joomfish
 * @subpackage controllerHelper
 *
*/


defined( '_JEXEC' ) or die( 'Restricted access' );


class  FalangControllerHelper  {

	/**
	 * Sets up ContentElement Cache - mainly used for data to determine primary key id for tablenames ( and for
	 * future use to allow tables to be dropped from translation even if contentelements are installed )
	 */
	static function _setupContentElementCache()
	{
		$db = JFactory::getDBO();
		// Make usre table exists otherwise create it.
		$db->setQuery( "CREATE TABLE IF NOT EXISTS `#__falang_tableinfo` ( `id` int(11) NOT NULL auto_increment, `joomlatablename` varchar(100) NOT NULL default '',  `tablepkID`  varchar(100) NOT NULL default '', PRIMARY KEY (`id`)) ENGINE=MyISAM");
		$db->query();
		// clear out existing data
		$db->setQuery( "DELETE FROM `#__falang_tableinfo`");
		$db->query();
		$falangManager = FalangManager::getInstance();
		$contentElements = $falangManager->getContentElements(true);
		$sql = "INSERT INTO `#__falang_tableinfo` (joomlatablename,tablepkID) VALUES ";
		$firstTime = true;
		foreach ($contentElements as $key => $jfElement){
			$tablename = $jfElement->getTableName();
			$refId = $jfElement->getReferenceID();
			$sql .= $firstTime?"":",";
			$sql .= " ('".$tablename."', '".$refId."')";
			$firstTime = false;
		}

		$db->setQuery( $sql);
		$db->query();

	}


	/**
	 * Testing state of the system bot
	 *
	 */
	public static function _testSystemBotState()
	{
		$db = JFactory::getDBO();
		$botState = false;
		$db->setQuery( "SELECT * FROM #__extensions WHERE type='plugin' AND element='falangdriver'");
		$db->query();
		$plugin = $db->loadObject();
		if ($plugin != null && $plugin->enabled == "1") {
			$botState = $plugin->extension_id;
		}
		return $botState;
	}

	public static function _checkDBCacheStructure (){

        //TODO : sbou revoir la methode de cache
        return true;
		JCacheStorageJfdb::setupDB();

		$db =  JFactory::getDBO();
		$sql = "SHOW COLUMNS FROM #__dbcache LIKE 'value'";
		$db->setQuery($sql);
		$data = $db->loadObject();
		if (isset($data) && strtolower($data->Type)!=="mediumblob"){
			$sql = "DROP TABLE #__dbcache";
			$db->setQuery($sql);
			$db->query();

			JCacheStorageJfdb::setupDB();
		}
	}

	public static function _checkDBStructure (){
		$db =  JFactory::getDBO();
		$sql = "SHOW INDEX FROM #__falang_content";// where key_name = 'jfContent'";
		$db->setQuery($sql);
		$data = $db->loadObjectList("Key_name");

        if (isset($data['combo'])){
            $sql = "ALTER TABLE `#__falang_content` DROP INDEX `combo`" ;
            $db->setQuery($sql);
            $db->query();
        }
        if (!isset($data['idxFalang1'])){

            $sql = "ALTER TABLE `#__falang_content` ADD INDEX `idxFalang1` ( `reference_id` , `reference_field` , `reference_table` )" ;
            $db->setQuery($sql);
            $db->query();
        }

		if (!isset($data['falangContent'])){
			$sql = "ALTER TABLE `#__falang_content` ADD INDEX `falangContent` ( `language_id` , `reference_id` , `reference_table` )" ;
			$db->setQuery($sql);
			$db->query();
		}

        if (!isset($data['falangContentLanguage'])){
            $sql = "ALTER TABLE `#__falang_content` ADD INDEX `falangContentLanguage` (`reference_id`, `reference_field`, `reference_table`, `language_id`)" ;
            $db->setQuery($sql);
            $db->query();
        }

		if (!isset($data['reference_id'])){
			$sql = "ALTER TABLE `#__falang_content` ADD INDEX `reference_id` (`reference_id`)" ;
			$db->setQuery($sql);
			$db->query();
        }
        if (!isset($data['language_id'])){
            $sql = "ALTER TABLE `#__falang_content` ADD INDEX `language_id` (`language_id`)" ;
            $db->setQuery($sql);
            $db->query();
        }
        if (!isset($data['reference_table'])){
            $sql = "ALTER TABLE `#__falang_content` ADD INDEX `reference_table` (`reference_table`)" ;
            $db->setQuery($sql);
            $db->query();
        }
        if (!isset($data['reference_field'])){
            $sql = "ALTER TABLE `#__falang_content` ADD INDEX `reference_field` (`reference_field`)" ;
            $db->setQuery($sql);
            $db->query();
        }




//		$sql = "ALTER TABLE `#__falang_content` CHANGE COLUMN `value` `value` mediumtext NOT NULL " ;
//		$db->setQuery($sql);
//		@$db->query();
//
//		$sql = "ALTER TABLE `#__falang_content` CHANGE COLUMN `original_text` `original_text` mediumtext NOT NULL " ;
//		$db->setQuery($sql);
//		@$db->query();

		
	}

}
