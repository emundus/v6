<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
use Joomla\CMS\Factory;

defined('_JEXEC') or die;

require_once( JPATH_SITE.'/components/com_falang/helpers/defines.php' );
require_once( JPATH_SITE.'/components/com_falang/helpers/falang.class.php' );
require_once( JPATH_SITE."/administrator/components/com_falang/classes/FalangManager.class.php");

include_once(dirname(__FILE__) . '/drivers/' . strtolower(Factory::getDBO()->name) . "x.php");

class JFalangDatabase extends JOverrideDatabase {

	/** @var array list of multi lingual tables */
	var $_mlTableList=null;
	/** @var Internal variable to hold array of unique tablenames and mapping data*/
	var $_refTables=null;

	/** @var Internal variable to hold flag about whether setRefTables is needed - JF queries don't need it */
	var $_skipSetRefTables = false;

	var $orig_limit	= 0;
	var $orig_offset	= 0;
    var $_table_prefix = null;

	var $profileData = array();

	//sbou4
	var $debug = false;


	public function __construct($options)
    {
            parent::__construct($options);
            $this->_table_prefix = $options['prefix'];
            $pfunc = $this->_profile();

            $query = "select distinct reference_table from #__falang_content";
            $this->setQuery( $query );
            $this->_skipSetRefTables = true;
            $this->_mlTableList = $this->loadColumn(0,false);
            $this->_skipSetRefTables = false;
            if( !isset($this->_mlTableList) ){
                    if ($this->getErrorNumber()>0){
                            JError::raiseWarning( 200, JTEXT::_('No valid table list:') .$this->getErrorMsg());
                    }
            }

            $pfunc = $this->_profile($pfunc);
    }

	/**
	 * Description
	 *
	 * @access public
	 * @return int The number of rows returned from the most recent query.
	 */
	function getNumRows( $cur=null, $translate=true, $language=null )
	{
		$count = parent::getNumRows($cur);
		if (!$translate) return $count;

		// setup falang plugins
		$dispatcher	   = JDispatcher::getInstance();
        jimport('joomla.plugin.helper');
		JPluginHelper::importPlugin('falang');

		// must allow fall back for contnent table localisation to work
		$allowfallback = true;
		$refTablePrimaryKey = "";
		$reference_table = "";
		$ids="";
		//$this->setLanguage($language);
		$registry = Factory::getConfig();
		$defaultLang = $registry->get("config.defaultlang");
		if ($defaultLang == $language){
			$rows = array($count);
			$dispatcher->trigger('onBeforeTranslation', array (&$rows, &$ids, $reference_table, $language, $refTablePrimaryKey, $this->getRefTables(), $this->sql, $allowfallback));
			$count = $rows[0];
			return $count;
		}

		$rows = array($count);

		$dispatcher->trigger('onBeforeTranslation', array (&$rows, &$ids, $reference_table, $language, $refTablePrimaryKey, $this->getRefTables(), $this->sql, $allowfallback));

		$dispatcher->trigger('onAfterTranslation', array (&$rows, &$ids, $reference_table, $language, $refTablePrimaryKey, $this->getRefTables(), $this->sql, $allowfallback));
		$count = $rows[0];
		return $count;
	}

	//sbou4 set debug function because no more present
	/**
	 * Set the Debug property.
	 *
	 * @param   boolean  $debug  The debug setting.
	 *
	 * @return  boolean  Previous value.
	 *
	 * @since   4.0
	 */

	public function setDebug($debug){
		$previous = $this->debug;
		$this->debug = (boolean) $debug;

		return $previous;
	}

    /**
     * Execute the SQL statement. New query() name since 2.5.5
     *
     * @return  mixed  A database cursor resource on success, boolean false on failure.
     *
     * @since   11.1
     * @throws  DatabaseException
     */
    public function execute()
    {
        $success = parent::execute();

	    if ($success && !$this->_skipSetRefTables){
            $this->setRefTables();
        }
        //sbou4
        //return $this->cursor;
	    return true;
    }

    /**
     * Get a new iterator on the current query.
     *
     * @param   string  $column  An option column to use as the iterator key.
     * @param   string  $class   The class of object that is returned.
     *
     * @return  DatabaseIterator
     *
     * @since   1.0
     */
    public function getIterator($column = null, $class = \stdClass::class)
    {
        if (!$this->executed)
        {
            $this->execute();
        }

        // Derive the class name from the driver.
        $iteratorClass = '\\Falang\\Database\\FDatabaseIterator';

        //TODO 4.0 fix this code
        // Make sure we have an iterator class for this driver.
        if (!class_exists($iteratorClass))
        {
            // We can work with the base iterator class so use that
            //$iteratorClass = FDatabaseIterator::class;
        }

        // Return a new iterator
        //TODO 4.0 remove this ugly require once
        require_once (JPATH_PLUGINS . '/system/falangdriver/drivers/FDatabaseIterator.php');
        return new \Falang\Database\FDatabaseIterator($this->statement, $column, $class);
    }


	/**
	* Overwritten Database method to loads the first field of the first row returned by the query.
	*
	* @return The value returned in the query or null if the query failed.
	*/
	function loadResult( $translate=true, $language=null ) {
		if (!$translate){
			$this->_skipSetRefTables=true;
			$result = parent::loadResult();
			$this->_skipSetRefTables=false;
			return $result;
		}
		$result=null;
		$ret=null;

		$result = $this->_loadObject( $translate, $language );

		$pfunc = $this->_profile();

		if( $result != null ) {
			$fields = get_object_vars( $result );
			$ret = current($fields);
		}

		$pfunc = $this->_profile($pfunc);

		return $ret;
	}

    function loadResultArray($offset = 0,  $translate=true, $language=null){
        return $this->loadColumn($offset,$translate,$language);
    }

    /**
     * Overwritten Method to get an array of values from the <var>$offset</var> field in each row of the result set from
     * the database query.
     *
     * @param   integer  $offset  The row offset to use to build the result array.
     *
     * @return  mixed    The return value or null if the query failed.
     *
     * @since   11.1
     * @throws  DatabaseException
     */
    function loadColumn($offset = 0,  $translate=true, $language=null){
        if (!$translate){
            return parent::loadColumn($offset);
        }
        $results=array();
        $ret=array();
        $results = $this->loadObjectList( '','stdClass', $translate, $language );

        $pfunc = $this->_profile();

        if( $results != null && count($results)>0) {
            foreach ($results as $row) {
                $fields = get_object_vars( $row );
                $keycount = 0;
                foreach ($fields as $k=>$v) {
                    if ($keycount==$offset){
                        $key = $k;
                        break;
                    }
                }
				if(isset($key)){
					$ret[] = $fields[$key];
				}
            }
        }

        $pfunc = $this->_profile($pfunc);

        return $ret;
    }

    /**
     * Overwritten
     *
     * @access	public
     * @return The first row of the query.
     */
    function loadRow( $translate=true, $language=null)
    {
        if (!$translate){
            return parent::loadRow();
        }
        $result=null;
        $result = $this->_loadObject( $translate, $language );

        $pfunc = $this->_profile();

        $row = array();
        if( $result != null ) {
            $fields = get_object_vars( $result );
            foreach ($fields as $val) {
                $row[] = $val;
            }
            return $row;
        }
        return $row;
    }

    /**
    * Overwritten Load a list of database rows (numeric column indexing)
    *
    * @access public
    * @param string The field name of a primary key
    * @return array If <var>key</var> is empty as sequential list of returned records.
    * If <var>key</var> is not empty then the returned array is indexed by the value
    * the database key.  Returns <var>null</var> if the query fails.
    */
    function loadRowList( $key=null , $translate=true, $language=null)
    {
        if (!$translate){
            return parent::loadRowList($key);
        }
        $results=array();
        if (is_null($key)) $key="";
        $rows = $this->loadObjectList($key,'stdClass', $translate, $language );

        $pfunc = $this->_profile();

        $row = array();
        if( $rows != null ) {
            foreach ($rows as $row) {
                $fields = get_object_vars( $row );
                $result = array();
                foreach ($fields as $val) {
                    $result[] = $val;
                }
                if ($key!="") {
                    $results[$row->$key] = $result;
                }
                else {
                    $results[] = $result;
                }
            }
        }
        $pfunc = $this->_profile($pfunc);
        return $results;
    }


	function loadObjectList( $key='',$class = 'stdClass', $translate=true, $language=null ) {
                //sbou TODO check r�cursive pb
		if (!$translate) {
			$this->_skipSetRefTables=true;
			$result = parent::loadObjectList( $key ,empty($class)?'stdClass':$class);
			$this->_skipSetRefTables=false;
			return $result;
		}

		$result = parent::loadObjectList( $key, empty($class)?'stdClass':$class);


//		if( isset($jfManager)) {
//			$this->_setLanguage($language);
//		}

		// TODO check the impact of this on frontend translation
		// It does stop Joomfish plugins from working on missing translations e.g. regional content so disable for now
		// Don't do it for now since translation caching is so effective
		/*
		$registry = JFactory::getConfig();
		$defaultLang = $registry->getValue("config.defaultlang");
		if ($defaultLang == $language){
		$translate = false;
		}
		*/

                //sbou TODO this is not the right solution.
//		if( isset($jfManager)) {
                if (true){
			$doTranslate=false;
			$tables =$this->getRefTables();
			if ($tables == null) return $result; // an unstranslatable query to return result as is
			// if we don't have "fieldTablePairs" then we can't translate
			if (!array_key_exists("fieldTablePairs",$tables)){
				return $result;
			}
			foreach ($tables["fieldTablePairs"] as $i=>$table) {
				if ($this->translatedContentAvailable($table)) {
					$doTranslate=true;
					break;
				}
			}
			if ($doTranslate ) {
				$pfunc = $this->_profile();
                                //sbou TODO cache desactived
//				if ($jfManager->getCfg("transcaching",1)){
                                if (false) {
					// cache the results
					// TODO call based on config
					//$cache = JFactory::getCache('jfquery');
                    //$cache = $jfManager->getCache($language);
					$this->orig_limit	= $this->limit;
					$this->orig_offset	= $this->offset;
					//$result = $cache->get( array("FaLang", 'translateListCached'), array($result, $language, $this->getRefTables() ));
					$this->orig_limit	= 0;
					$this->orig_offset	= 0;
				}
				else {
					$this->orig_limit	= $this->limit;
					$this->orig_offset	= $this->offset;
					Falang::translateList( $result, $language, $this->getRefTables() );
					$this->orig_limit	= 0;
					$this->orig_offset	= 0;
				}
				$pfunc = $this->_profile($pfunc);
			}
		}
		return $result;
	}

	/**
	 * private function to handle the requirement to call different loadObject version based on class
	 *
	 * @param boolran $translate
	 * @param string $language
	 */
	function _loadObject( $translate=true, $language=null ) {
		return $this->loadObject();
	}




	function _profile($func = "", $forcestart=false){
		if (!$this->debug) return "";
		// start of function
		if ($func==="" || $forcestart){
			if (!$forcestart){
				$backtrace = debug_backtrace();
				if (count($backtrace)>1){
					if (array_key_exists("class",$backtrace[1])){
						$func = $backtrace[1]["class"]."::".$backtrace[1]["function"];
					}
					else {
						$func = $backtrace[1]["function"];
					}
				}
			}
			if (!array_key_exists($func,$this->profileData)){
				$this->profileData[$func]=array("total"=>0, "count"=>0);
			}
			if (!array_key_exists("start",$this->profileData[$func])) {
				$this->profileData[$func]["start"]=array();
			}
			list ($usec,$sec) = explode(" ", microtime());
			$this->profileData[$func]["start"][] = floatval($usec)+floatval($sec);
			$this->profileData[$func]["count"]++;
			return $func;
		}
		else {
			if (!array_key_exists($func,$this->profileData)){
				exit("JFProfile start not found for function $func");
			}
			list ($usec,$sec) = explode(" ", microtime());
			$laststart = array_pop($this->profileData[$func]["start"]);
			$this->profileData[$func]["total"] += (floatval($usec)+floatval($sec)) - $laststart;
		}
	}



	/**
	 * Public function to test if table has translated content available
	 *
	 * @param string $table : tablename to test
	 */
	function translatedContentAvailable($table){
		return in_array( $table, $this->_mlTableList) || $table=="content";
	}

	/** Internal function to return reference table names from an sql query
	 *
	 * @return	string	table name
	 */
	public function getRefTables(){
		return $this->_refTables;
	}

	/**
	* This global function loads the first row of a query into an object
	*/
	function loadObject( $class = 'stdClass', $translate=true, $language=null ) {
		$objects = $this->loadObjectList("",$class,$translate,$language);
		if (!is_null($objects) && count($objects)>0){
			return $objects[0];
		}
		return null;
	}

	/**
	* Overwritten Fetch a result row as an associative array
	*
	* @access	public
	* @return array
	*/
	function loadAssoc( $translate=true, $language=null) {
		if (!$translate){
			return parent::loadResult();
		}
		$result=null;
		$result = $this->_loadObject( $translate, $language );

		$pfunc = $this->_profile();

		if( $result != null ) {
			$fields = get_object_vars( $result );
			$pfunc = $this->_profile($pfunc);
			return $fields;
		}
		return $result;
	}

	/**
	* Overwritten Load a assoc list of database rows
	*
	* @access	public
	* @param string The field name of a primary key
	* @return array If <var>key</var> is empty as sequential list of returned records.
	*/
	function loadAssocList( $key = null, $column = null, $translate=true, $language=null )
	{
		if (!$translate){
			return parent::loadAssocList($key, $column = null);
		}
		$result=null;
		$rows = $this->loadObjectList($key,'stdClass', $translate, $language );

		$pfunc = $this->_profile();
		$return = array();
		if( $rows != null ) {
			foreach ($rows as $row) {
                $vars = get_object_vars( $row );
                $value = ($column) ? (isset($vars[$column]) ? $vars[$column] : $vars) : $vars;
                if ($key) {
                    $return[$vars[$key]] = $value;
                }
                else {
                    $return[] = $value;
                }
			}
			$pfunc = $this->_profile($pfunc);
		}
		return $return;
	}


    /**
     * Overwritten insert function to enable storage of material created in non-default language.
     * Note that this creates a translation which is identical to the original - when we update
     * the original in the default language we then keep the translation (although it will appread out of date!).
     *
     * @param	string	table name
     * @param	object	instance with information to store
     * @param	string	primary key name of table
     * @param	boolean	debug info printed or not
     * @param	boolean	passthru without storing information in a translation table
     */
    function updateObject( $table, &$object, $keyName, $updateNulls=true, $passthru=false ) {

        $params = JComponentHelper::getParams('com_falang');
        //1.4.5 if fronEdition is set to off , keep previous system
        if ($params->get('frontEndEdition',0) == 0){
            return parent::updateObject( $table, $object, $keyName, $updateNulls );
        }

        $pfunc = $this->_profile();

        $falangManager = FalangManager::getInstance();

        $current_lang = JFactory::getLanguage()->getTag();

        //default site language
        $default_lang	= JComponentHelper::getParams('com_languages')->get('site');

        //we update if content language is set to all language or if content language is current_langauge
        // check if marked langage of content is the detaul language:
        if ($table=='#__content' && isset($object->id) &&$object->id>0 ){
            $test = JTable::getInstance("Content");
            $test->load($object->id);
            if ( $test->language == '*'){
                if ($current_lang == $default_lang) {
                    return parent::updateObject( $table, $object, $keyName, $updateNulls );
                }
            }
            if ( $test->language == $current_lang) {
                return parent::updateObject( $table, $object, $keyName, $updateNulls );
            }
        }

        if (isset($falangManager) && ($table != "#__falang_content")){
            $tableName = preg_replace( '/^#__/', '', $table);
            $contentElement = $falangManager->getContentElement( $tableName );
            if( isset( $contentElement ) && $contentElement->getFrontEdit() == 1 ) {
                include_once(JPATH_ADMINISTRATOR."/components/com_falang/models/ContentObject.php");
                $actContentObject = new ContentObject( $falangManager->getLanguageID($current_lang), $contentElement );

                $objectLanguague = isset($object->language)? $object->language:null;
                //the object language must be a language tag
                $languageIsoList = array("*");
                foreach ($falangManager->getLanguagesIndexedByCode(false) as $language)
                {
                    $languageIsoList[] =$language->lang_code;
                }
                $validLanguage = in_array($objectLanguague,$languageIsoList);


                // 1 - si object * => objet joomla si langue cournate et langue en cours sont pareil
                // 2 - si object a une langue , alors object joolma si la langue de l'object = language en cours
                $joomlaObject = false;
                if ($objectLanguague == "*"){
                    if ($current_lang == $default_lang) {
                        $joomlaObject = true;
                    }
                }
                if ( $objectLanguague == $current_lang) {
                    $joomlaObject = true;
                }
                //hikashop woraround //
                $lkeyName = method_exists($object,'getKeyName')?$object->getKeyName():null;
                if( !empty( $lkeyName) && $validLanguage && isset($objectLanguague) && !$joomlaObject ) {
                    $actContentObject->loadFromContentID( $object->id );
                    $actContentObject->updateMLContent( $object );
                    if( isset( $object->state ) ) {
                        $actContentObject->published = ($object->state == 1) ? true : false;
                    } else if ( isset( $object->published ) ) {
                        $actContentObject->published = ($object->published == 1) ? true : false;
                    }
                    $actContentObject->setPublished($actContentObject->published);
                    $actContentObject->store();
                    return true;
                } else {
                    //hikashop workaround this update hit.
                    return parent::updateObject( $table, $object, $keyName, $updateNulls );
                }
            } else { //content element not exist
                return parent::updateObject( $table, $object, $keyName, $updateNulls );
            }

        } else {
            return parent::updateObject( $table, $object, $keyName, $updateNulls );
        }
    }

}