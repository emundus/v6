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
 * $Id: JFLanguage.php 1580 2011-04-16 17:11:41Z akede $
 * @package joomfish
 * @subpackage Tables
 *
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Database class for handling the languages within the component
 *
 * @package joomfish
 * @subpackage administrator
 * @copyright 2003 - 2011, Think Network GmbH, Munich
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Revision: 1580 $
 * @author Alex Kempkens <joomfish@thinknetwork.com>
 */
class TableJFLanguage extends JTable  {
	/** @var int Primary key */
	var $id=null;
	/** @var string The full name of the language*/
	var $name=null;
	/** @var int Flag if the language is activated for this site*/
	var $active=false;
	/** @var string short code for URL or language switching */
	var $shortcode=null;
	/**
	 * @var iso	string used to store the ISO code of a language
	 * @deprecated 2.0 as Joomla! 1.5 includes this information in the language file
	 */
	var $iso=null;
	/** 
	 * @var string The name Joomla is using for this language
	 * In Joomla! 1.5 this code is now a valid ISO code. This is why we removed the column ISO and replaced all calls to redirect to code instead
	 * Be aware that code (Joomla! iso code) inlucdes the country-language names!
	*/
	var $code=null;
	/** @var string Order of the languages within the lists*/
	var $ordering=0;
	/** @var string Image reference if there is any*/
	var $image="";
	/** @var string optional code of language to fall back on if translation is missing */
	var $fallback_code=0;
	/** @var string parameter set base on key=value pairs */
	var $params=null;

	/** Standard constructur
	*/
	function TableJFLanguage( &$db ) {
//sbou
		parent::__construct( '#__languages', 'lang_id', $db );
// fin sbou
	}

	/**
	 *	Loads the language by it's code name
	 *	@param string $code iso name of the language
	 *	@return any result from the database operation
	 */
	function loadByJoomla( $code=null ) {
		if ($code === null) {
			return false;
		}
		$jfm = FalangManager::getInstance();
		$langdata = $jfm->getLanguageByCode($code,$active);
		return $langdata;
	}

	/**
	 *	Creates a new language by it's iso name
	 *	@param string $iso iso name of the language
	 *	@return object new language instance or null
	 */
	function createByJoomla( $code, $active=true ) {
		$db = JFactory::getDBO();

		$lang = new TableJFLanguage($db);
		$jfm = FalangManager::getInstance();
		$langdata = $jfm->getLanguageByCode($code,$active);

		if( !$lang->bind($langdata) ) {
			$lang = null;
		}
		return $lang;
	}

	/**
	 *	Loads the language by it's iso name
	 *	@param string $iso iso name of the language
	 *	@return any result from the database operation
	 */
	function loadByISO( $iso=null ) {
		if ($iso === null) {
			return false;
		}
		$jfm = FalangManager::getInstance();
		$langdata = $jfm->getLanguageByCode($code,$active);
	}

	/**
	 * Creats the language by it's short code
	 * @param string	$shortcode name of the language
	 * @return object	language class or null
	 */
	function createByShortcode( $shortcode, $active=true ) {
		$db = JFactory::getDBO();
		if ($shortcode === null || $shortcode=='') {
			return null;
		}
		$lang = new TableJFLanguage($db);
		$jfm = FalangManager::getInstance();
		$langdata = $jfm->getLanguageByShortcode($shortcode,$active);
		// if we allow Joomfish to attempt to translate this object then the language is loaded 
		// too early by JFactory::getLanguage();  This then breaks everything!!!
		if( !$lang->bind($langdata) ) {
			$lang = null;
		}
		return $lang;
	}

	/**
	 *	Loads the language by it's iso name
	 *	@param string $iso iso name of the language
	 *	@return any result from the database operation
	 */
	function createByISO( $iso, $active=true ) {
		$db = JFactory::getDBO();

		if ($iso === null) {
			return false;
		}
		$lang = new TableJFLanguage($db);
		$jfm = FalangManager::getInstance();
		$langdata = $jfm->getLanguageByCode($iso,$active);

		if( !$lang->bind($langdata) ) {
			$lang = null;
		}
		return $lang;
	}


	/**
	 * Return the language code for the urls (shortcode)
	 * @return string	short code of the language
	 */
	function getLanguageCode() {
		return ($this->sef!='') ? $this->sef : $this->lang_code;
	}

	/**
	 * Validate language information
	 * Name and Code name are mandatory
	 * activated will automatically set to false if not set
	 */
	function check() {
		if (trim( $this->name ) == '') {
			$this->_error = "You must enter a name.";
			return false;
		}

		if (trim( $this->code ) == '') {
			$this->_error = "You must enter a corresponding language code.";
			return false;
		}

		// check for existing language code
		$this->_db->setQuery( "SELECT lang_id FROM #__languages "
//sbou
		. "\nWHERE code='$this->code' AND lang_id!='$this->id'"
//fin sbou
		);

		$xid = intval( $this->_db->loadResult() );
		if ($xid && $xid != intval( $this->id )) {
			$this->_error = "There is already a language with the code you provided, please try again.";
			return false;
		}

		return true;
	}

	/**
	 * Bind the content of the newValues to the object. Overwrite to make it possible
	 * to use also objects here
	 */
	function bindFromJLanguage( $jLanguage ) {
		$retval = false;
		if (is_array( $jLanguage )) {
			$this->active = false;
			$this->name = $jLanguage['name'];
			$this->code = $jLanguage['tag'];
			$this->iso = $jLanguage['locale'];
			$this->shortcode= strpos($jLanguage['tag'], '-') > 0 ? substr($jLanguage['tag'], 0, strpos($jLanguage['tag'], '-')) : $jLanguage['tag'];
			$this->fallback_code = '';
			$retval = true;
		}
		return $retval;
	}
	
}

