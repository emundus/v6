<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

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
		$db->execute();
		// clear out existing data
		$db->setQuery( "DELETE FROM `#__falang_tableinfo`");
		$db->execute();
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
		$db->execute();

	}


	public static function _checkDBCacheStructure (){

        //TODO : sbou revoir la methode de cache
        return true;
/*
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
*/
	}

	public static function _checkDBStructure (){
		$db =  JFactory::getDBO();
		$sql = "SHOW INDEX FROM #__falang_content";// where key_name = 'jfContent'";
		$db->setQuery($sql);
		$data = $db->loadObjectList("Key_name");

        if (isset($data['combo'])){
            $sql = "ALTER TABLE `#__falang_content` DROP INDEX `combo`" ;
            $db->setQuery($sql);
            $db->execute();
        }
        if (!isset($data['idxFalang1'])){

            $sql = "ALTER TABLE `#__falang_content` ADD INDEX `idxFalang1` ( `reference_id` , `reference_field` , `reference_table` )" ;
            $db->setQuery($sql);
            $db->execute();
        }

		if (!isset($data['falangContent'])){
			$sql = "ALTER TABLE `#__falang_content` ADD INDEX `falangContent` ( `language_id` , `reference_id` , `reference_table` )" ;
			$db->setQuery($sql);
			$db->execute();
		}

        if (!isset($data['falangContentLanguage'])){
            $sql = "ALTER TABLE `#__falang_content` ADD INDEX `falangContentLanguage` (`reference_id`, `reference_field`, `reference_table`, `language_id`)" ;
            $db->setQuery($sql);
            $db->execute();
        }

		if (!isset($data['reference_id'])){
			$sql = "ALTER TABLE `#__falang_content` ADD INDEX `reference_id` (`reference_id`)" ;
			$db->setQuery($sql);
			$db->execute();
        }
        if (!isset($data['language_id'])){
            $sql = "ALTER TABLE `#__falang_content` ADD INDEX `language_id` (`language_id`)" ;
            $db->setQuery($sql);
            $db->execute();
        }
        if (!isset($data['reference_table'])){
            $sql = "ALTER TABLE `#__falang_content` ADD INDEX `reference_table` (`reference_table`)" ;
            $db->setQuery($sql);
            $db->execute();
        }
        if (!isset($data['reference_field'])){
            $sql = "ALTER TABLE `#__falang_content` ADD INDEX `reference_field` (`reference_field`)" ;
            $db->setQuery($sql);
            $db->execute();
        }
	}

	/**
	 * Check Plugin Order since Joomla 3.6.2, language filter need to be set before FalangDatabaseDriver plgin
	 *
	 * @since version 2.7.0
	 */

	public static function _checkPlugin(){
	    return;//since Joomla 4.0

		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);

		//language filter must be before falang database driver
		$query->select('extension_id,element,ordering ');
		$query->from('#__extensions');

		$query->where($query->quoteName('type') . '=' . $query->quote('plugin'));
		$query->where($query->quoteName('folder') . '=' . $query->quote('system'));
		$query->where($query->quoteName('element') . 'IN ("languagefilter","falangdriver")');
		$query->order('ordering ASC');

		$db->setQuery($query);
		$list = $db->loadObjectList('element');

		if (isset($list['languagefilter']) and isset($list['falangdriver'])){
			if ((int)$list['languagefilter']->ordering >=  (int)$list['falangdriver']->ordering){
				//we have to fix the order
				$pks = array((int)$list['languagefilter']->extension_id,(int)$list['falangdriver']->extension_id);
				//set order to 1 and 2 - other plugin set to -1 stay at -1
				$order = array(1,2);

				//jimport('joomla.application.component.model');
				//JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_plugins/models');
                //$pluginsModel = JModelLegacy::getInstance( 'Plugin', 'PluginsModel' );

				$pluginsModel = \Joomla\Component\Plugins\Administrator\Model\PluginsModel::getInstance('Plugin');
				//$pluginsModel->save


				// Save the ordering
				//sbou4 descatived the saveorder
				//$return = $pluginsModel->saveorder($pks, $order);
				$return = true;

				$application = JFactory::getApplication();
				if ($return === false){
					JFactory::getApplication()->enqueueMessage(JText::_('COM_FALANG_PLUGINS_SYSTEM_ORDER_FAILED'), 'error');
				} else {
					JFactory::getApplication()->enqueueMessage(JText::_('COM_FALANG_PLUGINS_SYSTEM_ORDER_FIXED'), 'notice');
				}
			}
		}

	}

	/**
	 * Check Advanced Routeur not available in Free Falang version but can be set in Content Parameter
	 *
	 * @since version 3.3.0
	 */

	public static function _checkAdvancedRouter(){

		$falang_advanced_router = JComponentHelper::getParams('com_falang')->get('advanced_router',false);
		$content_advanced_router = JComponentHelper::getParams('com_content')->get('sef_advanced',false);

		$isFreeVersion = false;

		include_once( JPATH_ADMINISTRATOR . '/components/com_falang/version.php');
		$version = new FalangVersion();
		if ($version->_versiontype == 'free'  ) {
			$isFreeVersion = true;
		}

		if ($content_advanced_router && !$falang_advanced_router){
			if ($isFreeVersion){
				JFactory::getApplication()->enqueueMessage(JText::_('COM_FALANG_ADVANCED_ROUTER_ENABLED_FREE_FALANG'), 'error');
			} else {

				JFactory::getApplication()->enqueueMessage(JText::_('COM_FALANG_ADVANCED_ROUTER_ENABLED'), 'error');
			}
		}
	}


}
