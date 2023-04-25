<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

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
	public function __construct(&$db) {
		parent::__construct( '#__languages', 'lang_id', $db );
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

