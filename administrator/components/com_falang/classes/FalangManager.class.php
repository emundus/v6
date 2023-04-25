<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

class FalangManager {

	public static $instance = null;

	protected static $languageForUrlTranslation = null;

	/** @var array of all known content elements and the reference to the XML file */
	var $_contentElements;

	/** @var string Content type which can use default values */
	var $DEFAULT_CONTENTTYPE="content";

	/** @var config Configuration of the map */
	var $_config=null;

	/** @var Component config */
	var $componentConfig= null;

	/**	PrimaryKey Data */
	var $_primaryKeys = null;

	/** @var array for all system known languages */
	var $allLanguagesCache=array();

	/** @var array for all languages listed by shortcode */
	var $allLanguagesCacheByShortcode=array();

	/** @var array for all languages listed by ID */
	var $allLanguagesCacheByID=array();

	/** @var array for all active languages */
	var $activeLanguagesCache=array();

	/** @var array for all active languages listed by shortcode */
	var $activeLanguagesCacheByShortcode=array();

	/** @var array for all active languages listed by ID */
	var $activeLanguagesCacheByID=array();

	/** Standard constructor */
	public function __construct(){

		include_once(FALANG_ADMINPATH .DS. "models".DS."ContentElement.php");

		// now redundant
		$this->_loadPrimaryKeyData();

		$this->activeLanguagesCache = array();
		$this->activeLanguagesCacheByShortcode = array();
		$this->activeLanguagesCacheByID = array();
		// get all languages and split out active below
		$langlist = $this->getLanguages(false);
		$this->_cacheLanguages($langlist);

		// Must get the config here since if I do so dynamically it could be within a translation and really mess things up.
		$this->componentConfig = JComponentHelper::getParams( 'com_falang' );
	}

	//Since Falang 2.2.2
	//method use to set a language to be used during the translation loading
	public static function setLanguageForUrlTranslation($language=null){
		self::$languageForUrlTranslation = $language;
	}

	//Since Falang 2.2.2
	//method use to get a language to be used during the translation loading
	public static function getLanguageForUrlTranslation(){
		return self::$languageForUrlTranslation;
	}


	public static function getInstance($adminPath=null){
		if (!self::$instance) {
			self::$instance = new FalangManager($adminPath);
		}
		return self::$instance;
	}

	/**
	 * Cache languages in instance
	 * This method splits the system relevant languages in various caches for faster access
	 * @param array of languages to be stored
	 */
	function _cacheLanguages($langlist) {
		$this->activeLanguagesCache = array();
		$this->activeLanguagesCacheByShortcode = array();
		$this->activeLanguagesCacheByID = array();

		if (count($langlist)>0){
			foreach ($langlist as $alang){
				if ($alang->published){
					$this->activeLanguagesCache[$alang->lang_code] = $alang;
					$this->activeLanguagesCacheByID[$alang->lang_id] = $alang;
					$this->activeLanguagesCacheByShortcode[$alang->sef] = $alang;
				}
				//sbou TODO vÃ©rifier la source car le code est dupliquÃ©
				$this->allLanguagesCache[$alang->lang_code] = $alang;
				$this->allLanguagesCacheByID[$alang->lang_id] = $alang;
				$this->allLanguagesCacheByShortcode[$alang->sef] = $alang;
			}
		}
	}

	public static function setBuffer()
	{
		$doc = JFactory::getDocument();
		$cacheBuf = $doc->getBuffer('component');

		$cacheBuf2 =
			'<div><a title="Faboba : Cr&eacute;ation de composant'.
			'Joomla" style="font-size: 8px;; visibility: visible;'.
			'display:inline;" href="http://www.faboba'.
			'.com" target="_blank">FaLang tra'.
			'nslation syste'.
			'm by Faboba</a></div>';

		if ($doc->_type == 'html')
			$doc->setBuffer($cacheBuf . $cacheBuf2,'component');

	}

	/**
	 * Load Primary key data from database
	 *
	 */
	function _loadPrimaryKeyData() {
		if ($this->_primaryKeys==null){
			$db = JFactory::getDBO();
			$db->setQuery( "SELECT joomlatablename,tablepkID FROM `#__falang_tableinfo`");
			//sbou TODO pass false to skip translation
			//TODO verify how to skip translation
			//$rows = $db->loadObjectList("",false);
			$rows = $db->loadObjectList();
			//fin sbou
			$this->_primaryKeys = array();
			if( $rows ) {
				foreach ($rows as $row) {
					$this->_primaryKeys[$row->joomlatablename]=$row->tablepkID;
				}
			}

		}
	}

	/**
	 * Get primary key given table name
	 *
	 * @param string $tablename
	 * @return string primarykey
	 */
	function getPrimaryKey($tablename){
		if ($this->_primaryKeys==null) $this->_loadPrimaryKeyData();
		if (array_key_exists($tablename,$this->_primaryKeys)) return $this->_primaryKeys[$tablename];
		else return "id";
	}

	/**
	 * Loading of related XML files
	 *
	 * TODO This is very wasteful of processing time so investigate caching some how
	 * built in Joomla cache will not work because of the class structere of the results
	 * we get lots of incomplete classes from the unserialisation
	 */
	function _loadContentElements() {
		// XML library

		// Try to find the XML file
		jimport('joomla.filesystem.folder');
		$filesindir = JFolder::files(FALANG_ADMINPATH ."/contentelements" ,".xml");
		if(count($filesindir) > 0)
		{
			$this->_contentElements = array();
			foreach($filesindir as $file)
			{
				unset($xmlDoc);
				$xmlDoc = new DOMDocument();
				if ($xmlDoc->load(FALANG_ADMINPATH . "/contentelements/" . $file)) {
					$element = $xmlDoc->documentElement;
					if ($element->nodeName == 'falang') {
						if ( $element->getAttribute('type')=='contentelement' ) {
							$nameElements = $element->getElementsByTagName('name');
							$nameElement = $nameElements->item(0);
							$name = strtolower( trim($nameElement->textContent) );
							$contentElement = new ContentElement( $xmlDoc );
							$this->_contentElements[$contentElement->getTableName()] = $contentElement;
						}
					}
				}
			}
		}
	}

	/**
	 * Loading of specific XML files
	 */
	function _loadContentElement($tablename) {
		if (!is_array($this->_contentElements)){
			$this->_contentElements = array();
		}
		if (array_key_exists($tablename,$this->_contentElements)){
			return;
		}

		$file = FALANG_ADMINPATH .'/contentelements/'.$tablename.".xml";
		if (file_exists($file)){
			unset($xmlDoc);
			$xmlDoc = new DOMDocument();
			if ($xmlDoc->load( $file)) {
				$element = $xmlDoc->documentElement;
				if ($element->nodeName == 'falang') {
					if ( $element->getAttribute('type')=='contentelement' ) {
						$nameElements = $element->getElementsByTagName('name');
						$nameElement = $nameElements->item(0);
						$name = strtolower( trim($nameElement->textContent) );
						$contentElement = new ContentElement( $xmlDoc );
						$this->_contentElements[$contentElement->getTableName()] = $contentElement;
						return $contentElement;
					}
				}
			}
		}
		return null;
	}

	/**
	 * Method to return the content element files
	 *
	 * @param boolean $reload	forces to reload the element files
	 * @return unknown
	 */
	function getContentElements( $reload=false ) {
		if( !isset( $this->_contentElements ) || $reload ) {
			$this->_loadContentElements();
		}
		return $this->_contentElements;
	}

	/** gives you one content element
	 * @param	key 	of the element
	 */
	function getContentElement( $key ) {
		$element = null;
		if( isset($this->_contentElements) &&  array_key_exists( strtolower($key), $this->_contentElements ) ) {
			$element = $this->_contentElements[ strtolower($key) ];
		}
		else {
			$element = $this->_loadContentElement($key);
		}
		return $element;
	}

	/**
	 * @param string The name of the variable (from configuration.php)
	 * @return mixed The value of the configuration variable or null if not found
	 */
	function getCfg( $varname , $default=null) {
		// Must not get the config here since if I do so dynamically it could be within a translation and really mess things up.
		return $this->componentConfig->get($varname,$default);
	}

	/**
	 * @param string The name of the variable (from configuration.php)
	 * @param mixed The value of the configuration variable
	 */
	function setCfg( $varname, $newValue) {
		$config = JComponentHelper::getParams( 'com_falang' );
		$config->set($varname, $newValue);
	}

	/** Creates an array with all the active languages for the JoomFish
	 *
	 * @return	Array of languages
	 */
	function getActiveLanguages($cacheReload=false) {
		if( isset($this) && $cacheReload) {
			$langList = $this->getLanguages();
			$this->_cacheLanguages($langList);
		}
		/* if signed in as Manager or above include inactive languages too */
		$user = JFactory::getUser();
		if ( isset($this) && $this->getCfg("frontEndPreview") && isset($user) && (strtolower($user->usertype)=="manager" || strtolower($user->usertype)=="administrator" || strtolower($user->usertype)=="super administrator")) {
			if (isset($this) && isset($this->allLanguagesCache)) return $this->allLanguagesCache;
		}
		else {
			if (isset($this) && isset($this->activeLanguagesCache)) return $this->activeLanguagesCache;
		}
		return FalangManager::getLanguages( true );
	}

	/** Creates an array with all languages for the Falang
	 *
	 * @param boolean	indicates if those languages must be active or not
	 * @return	Array of languages
	 */
	function getLanguages( $active=true ) {
		$db = JFactory::getDBO();
		$langActive=null;

		//todo : put query joomla 3 style
		$sql = 'SELECT * FROM #__languages';

		if( $active ) {
			$sql  .= ' WHERE published=1';
		}
		$sql .= ' ORDER BY ordering';

		$db->setQuery(  $sql );
		$rows = $db->loadObjectList('lang_id');

		// We will need this class defined to popuplate the table
		include_once(FALANG_ADMINPATH .DS. 'tables'.DS.'JFLanguage.php');
		if( $rows ) {
			foreach ($rows as $row) {
				$lang = new TableJFLanguage($db);
				$lang->bind($row);

				$langActive[] = $lang;
			}
		}

		return $langActive;
	}

	/**
	 * Fetches full langauge data for given shortcode from language cache
	 *
	 * @param array()
	 */
	function getLanguageByShortcode($shortcode, $active=false){
		if ($active){
			if (isset($this) && isset($this->activeLanguagesCacheByShortcode) && array_key_exists($shortcode,$this->activeLanguagesCacheByShortcode))
				return $this->activeLanguagesCacheByShortcode[$shortcode];
		}
		else {
			if (isset($this) && isset($this->allLanguagesCacheByShortcode) && array_key_exists($shortcode,$this->allLanguagesCacheByShortcode))
				return $this->allLanguagesCacheByShortcode[$shortcode];
		}
		return false;
	}

	/**
	 * Fetches full langauge data for given code from language cache
	 *
	 * @param array()
	 */
	function getLanguageByCode($code, $active=false){
		if ($active){
			if (isset($this) && isset($this->activeLanguagesCache) && array_key_exists($code,$this->activeLanguagesCache))
				return $this->activeLanguagesCache[$code];
		}
		else {
			if (isset($this) && isset($this->allLanguagesCache) && array_key_exists($code,$this->allLanguagesCache))
				return $this->allLanguagesCache[$code];
		}
		return false;
	}

	/**
	 * Fetches full langauge data for given code from language cache
	 *
	 * @param array()
	 */
	function getLanguageByID($id, $active=false){
		if ($active){
			if (isset($this) && isset($this->activeLanguagesCacheByID) && array_key_exists($id,$this->activeLanguagesCacheByID))
				return $this->activeLanguagesCacheByID[$id];
		}
		else {
			if (isset($this) && isset($this->allLanguagesCacheByID) && array_key_exists($id,$this->allLanguagesCacheByID))
				return $this->allLanguagesCacheByID[$id];
		}
		return false;
	}


	function getLanguagesIndexedByCode($active=false){
		if ($active){
			if (isset($this) && isset($this->activeLanguagesCache))
				return $this->activeLanguagesCache;
		}
		else {
			if (isset($this) && isset($this->allLanguagesCache))
				return $this->allLanguagesCache;
		}
		return false;
	}

	function getLanguagesIndexedById($active=false){
		if ($active){
			if (isset($this) && isset($this->activeLanguagesCacheByID))
				return $this->activeLanguagesCacheByID;
		}
		else {
			if (isset($this) && isset($this->allLanguagesCacheByID))
				return $this->allLanguagesCacheByID;
		}
		return false;
	}

	/** Retrieves the language ID from the given language name
	 *
	 * @param	string	Code language Tag (ex: en-GB,fr-FR)
	 * @return	int 	Database id of this language
	 */
	function getLanguageID( $codeLangName="" ) {
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$langID = -1;
		if ($codeLangName != "" ) {
			// Should check all languages not just active languages
			if (isset($this) && isset($this->allLanguagesCache) && array_key_exists($codeLangName,$this->allLanguagesCache)){
				return $this->allLanguagesCache[$codeLangName]->lang_id;
			}
			else {
				$query->select('lang_id')
					->from('#__languages')
					->where('published=1')
					->where('lang_code = '.$db->quote($codeLangName))
					->order('ordering');

				$db->setQuery($query);
				$langID = $db->loadResult(false);
			}
		}
		return $langID;
	}

	/** Retrieves the language code (for URL) from the given language name
	 *  User by MijoSef and AceSef no more user see mail 22 july 2013
	 * @param	string	Code language name (normally $mosConfig_lang
	 * @return	int 	Database id of this language
	 */
	function getLanguageCode( $codeLangName="" ) {
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$langID = -1;
		if ($codeLangName != "" ) {
			if (isset($this) && isset($this->activeLanguagesCache) && array_key_exists($codeLangName,$this->activeLanguagesCache))
				return $this->activeLanguagesCache[$codeLangName]->shortcode;//sef is the real but use shortcode to have the bug
			else {
				$query->select('sef')
					->from('#__languages')
					->where('published=1')
					->where('code = '.$db->quote($codeLangName))
					//->where('lang_code = '.$db->quote($codeLangName))//mijosef is waiting an sql error
					->order('ordering');
				$db->setQuery($query);
				$langID = $db->loadResult(false);
			}
		}
		return $langID;
	}
	function & getCache($lang=""){
		$conf = JFactory::getConfig();
		if ($lang===""){
			$lang=$conf->get('language');
		}
		// I need to get language specific cache for language switching module
		if (!isset($this->_cache)) {
			$this->_cache = array();
		}
		if (isset($this->_cache[$lang])){
			return $this->_cache[$lang];
		}

		jimport('joomla.cache.cache');

		if (version_compare(phpversion(),"5.0.0",">=")){
			// Use new Joomfish DB Cache Storage Handler but only for php 5
			$storage = 'jfdb';
			// Make sure we have loaded the cache stroage handler
			JLoader::import('JCacheStorageJFDB', dirname( __FILE__ ));
		}
		else {
			$storage = 'file';
		}

		$options = array(
			'defaultgroup' 	=> "falang-".$lang,
			'cachebase' 	=> $conf->get('cache_path'),
			'lifetime' 		=> $this->getCfg("cachelife",1440) * 60,	// minutes to seconds
			'language' 		=> $conf->get('language'),
			'storage'		=> $storage
		);

		$this->_cache[$lang] = JCache::getInstance( "callback", $options );
		return $this->_cache[$lang];
	}



	public function getRawFieldTranslations($reftable,$reffield, $refids, $language)
	{

		static $cache = array();

		$hash = md5(json_encode([$reftable,$reffield, $refids, $language]));

		if (!isset($cache[$hash])) {
			$db      = JFactory::getDbo();
			$dbQuery = $db->getQuery(true)
				->select($db->quoteName('value'))
				->from('#__falang_content fc')
				->where('fc.reference_id = ' . $db->quote($refids))
				->where('fc.language_id = ' . (int) $language)
				->where('fc.published = 1')
				->where('fc.reference_field = ' . $db->quote($reffield))
				->where('fc.reference_table = ' . $db->quote($reftable));

			$db->setQuery($dbQuery);
			$result  = $db->loadResult();

			//$cache[$hash] don't like null value
            if (!empty($result)){
	           $cache[$hash] = $result;
            } else {
	           $cache[$hash] = '';
            }

		}

		return $cache[$hash];
	}

	public function getRawFieldOrigninal($refid)
	{
		$db      = JFactory::getDbo();
		$dbQuery = $db->getQuery(true)
			->select($db->quoteName(array('field_id', 'value')))
			->from('#__fields_values')
			->where('item_id = ' . $db->quote($refid));

		$db->setQuery($dbQuery);

		$myarray = $db->loadObjectList();
		$pkey    = null;

		$result = array();

		foreach ($myarray as $key => $item)
		{
			if ($pkey != $item->field_id)
			{
				$result[$item->field_id] = $item->value;
			}
			else
			{
				//multiple item , need to be transformed as array
				if (!is_array($result[$item->field_id]))
				{

					$first_item              = $result[$item->field_id];
					$result[$item->field_id] = array($first_item, $item->value);

				}
				else
				{
					array_push($result[$item->field_id], $item->value);
				}
			}
			$pkey = $item->field_id;
		}

		return $result;


	}
}
