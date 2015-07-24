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
 * $Id: languages.php 1551 2011-03-24 13:03:07Z akede $
 * @package joomfish
 * @subpackage Models
 *
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

JLoader::register('JFModel', FALANG_ADMINPATH .DS. 'models' .DS. 'JFModel.php' );

/**
 * @package		Joom!Fish
 * @subpackage	CPanel
 */
class LanguagesModelLanguages extends JFModel
{
	/**
	 * @var string	name of the current model
	 * @access private
	 */
	var $_modelName = 'languages';

	/**
	 * @var array	set of languages found in the system
	 * @access private
	 */
	var $_languages = null;

	/**
	 * default constrcutor
	 */
	function __construct() {
		parent::__construct();

		$this->addTablePath(FALANG_ADMINPATH .DS. 'tables');

		$app	= JFactory::getApplication();
		$option = JRequest::getVar('option', '');
		// Get the pagination request variables
		$limit		= $app->getUserStateFromRequest( 'global.list.limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$limitstart	= $app->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}


	/**
	 * return the model name
	 */
	function getName() {
		return $this->_modelName;
	}

	/**
	 * generic method to load the language related data
	 * @return array of languages
	 */
	function getData() {
		if($this->_languages == null) {
			$this->_loadLanguages();
		}
		return $this->_languages;
	}

	/**
	 * Method to store language information
	 */
	function store($cid, $data) {
		if( is_array($cid) && count($cid)>0 ) {
			for ($i=0; $i<count($cid); $i++) {
				$jfLang = $this->getTable('JFLanguage');
				$jfLang->id =$cid[$i];
				$jfLang->name = $data['name'][$i];

				// The checkbox is only filled when it was active - so we have to check if
				// one box is fitting to your language
				if( isset($data['active']) ) {
					foreach( $data['active'] as $activeLanguageID ) {
						if( $activeLanguageID == $jfLang->id ) {
							$jfLang->active = true;
							break;
						}
					}
				}
				$jfLang->iso = $data['iso'][$i];
				$jfLang->shortcode = $data['shortCode'][$i];
				$jfLang->code = $data['code'][$i];
				$jfLang->image = $data['image'][$i];
				$jfLang->ordering = $data['order'][$i];
				$jfLang->fallback_code = $data['fallbackCode'][$i];
				$jfLang->params = $data['params'][$i];

				if( !$jfLang->store() ) {
					$this->setError($jfLang->getError());
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Method to remove a language completely
	 */
	function remove($cid, $data) {
		if( is_array($cid) && count($cid)>0 ) {
			for ($i=0; $i<count($cid); $i++) {
				$jfLang = $this->getTable('JFLanguage');
				if( !$jfLang->delete($cid[$i]) ) {
					$this->setError($jfLang->getError());
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Method to load the languages in the system
	 * This is based on the languages currently configured in the Joom!Fish and all frontend languages installed in the system<br />
	 * Loaded languages will be stored in the private variable _languages
	 *
	 * @return void
	 */
	function _loadLanguages(){
            //sbou TODO change this method to load joomla site language
		global $option;
		$db = JFactory::getDBO();

		$filter_order		= $this->getUserStateFromRequest( $option.'filter_order',		'filter_order',		'l.ordering',	'cmd' );
		$filter_order_Dir	= $this->getUserStateFromRequest( $option.'filter_order_Dir',	'filter_order_Dir',	'',				'word' );

		// 1. read all known languages from the database
		$sql = "SELECT l.*"
		. "\nFROM #__languages AS l";

		if ($filter_order != ''){
			$sql .= ' ORDER BY ' .$filter_order .' '. $filter_order_Dir;
		}
		$db->setQuery( $sql	);
		$languages = $db->loadObjectList();
		if ($db->getErrorNum()) {
			JError::raiseWarning( 400,$db->getErrorMsg());
		}

		// Read the languages dir to find new installed languages
		// This method returns a list of JLanguage objects with the related inforamtion
		$systemLanguages = JLanguage::getKnownLanguages(JPATH_SITE);

		// Generate a compability list of known languages
		$backwardLanguages = array();
		foreach ($systemLanguages as $jLanguage) {
			$backwardLanguages[$jLanguage['backwardlang']] = $jLanguage;
		}

		// We convert any language of the table into a JFLanguage object. As within the language list the key will be the language code
		// All Joomla! system languages not known will be added to the result list
		$this->_languages = array();
		foreach($languages as $language) {
			$jfLanguage = $this->getTable('JFLanguage');
			$jfLanguage->bind($language);

			// solving [12658] language codes might be determint and maped also using the compability field!
			foreach (array_keys($backwardLanguages) as $backwardlang) {
				if($backwardlang == substr($jfLanguage->code, 0, strlen($backwardlang))) {
					$jLanguage = $backwardLanguages[$backwardlang];
					$jfLanguage->set('code', $jLanguage['tag']);
					$jfLanguage->set('iso',  $jLanguage['locale']);
				}
			}
			$this->_languages[$jfLanguage->code] = $jfLanguage;
		}

		foreach ($systemLanguages as $jLanguage) {
			if($jLanguage != null && !array_key_exists($jLanguage['tag'], $this->_languages)) {
					$jfLanguage = $this->getTable('JFLanguage');
					$jfLanguage->bindFromJLanguage($jLanguage);
					$this->_languages[$jfLanguage->code] = $jfLanguage;
			}
		}
	}
}

