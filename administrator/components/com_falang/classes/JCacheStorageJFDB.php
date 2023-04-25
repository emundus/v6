<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

//Register class that don't follow one file per class naming conventions
JLoader::register('JCacheStorage' , JPATH_LIBRARIES .DS. 'joomla'.DS.'cache' .DS.'storage.php');

/**
 * Joomfish Cache Class
 *
 * @author		Geraint Edwards (http://www.gwesystems.com)
 * @package		Joomfish
 * @subpackage	Cache
 * @since		2.0
 */
class JCacheStorageJfdb extends JCacheStorage
{

	var $db;
	var $profile_db;

	
	/* Constructor
	*
	* @access protected
	* @param array $options optional parameters
	*/
	function __construct( $options = array() )
	{
		static $expiredCacheCleaned;

		$this->profile_db =  JFactory::getDBO();
		$this->db = clone ($this->profile_db);		

		$this->_language	= (isset($options['language'])) ? $options['language'] : 'en-GB';
		$this->_lifetime	= (isset($options['lifetime'])) ? $options['lifetime'] : 60;
		$this->_now		= (isset($options['now'])) ? $options['now'] : time();

		$config			= JFactory::getConfig();
		$this->_hash	= $config->get('config.secret');

		// if its not the first instance of the joomfish db cache then check if it should be cleaned and otherwise garbage collect
		if (!isset($expiredCacheCleaned)) {
			// check a file in the 'file' cache to check if we should remove all our db cache entries since cache manage doesn't handle anything other than file caches
			$conf = JFactory::getConfig();
			$cachebase = $conf->get('cache_path',JPATH_ROOT.DS.'cache');
			$cachepath = $cachebase.DS."falang-cache";
			if (!JFolder::exists($cachepath)){
				JFolder::create($cachepath);
			}
			$cachefile = $cachepath.DS."cachetest.txt";
			jimport("joomla.filesystem.file");
			if (!JFile::exists($cachefile) || JFile::read($cachefile)!="valid"){
				// clean out the whole cache
				$this->cleanCache();
                                //sbou TODO uncomment write and solve problem
				$data = 'valid';
				JFile::write($cachefile,$data);
			}
			$this->gc();
		}
		$expiredCacheCleaned = true;
	}

	/**
	 * One time only DB setup function
	 *
	 */
	function setupDB() {
		$db =  JFactory::getDBO();
		$charset = ($db->hasUTF()) ? 'CHARACTER SET utf8 COLLATE utf8_general_ci' : '';
		$sql = "CREATE TABLE IF NOT EXISTS `#__dbcache` ("
		. "\n `id` varchar ( 32 )  NOT NULL default '',"
		. "\n `groupname` varchar ( 32 ) NOT NULL default '',"
		. "\n `expire` datetime NOT NULL default '0000-00-00 00:00:00',"
		//. "\n `value` MEDIUMTEXT NOT NULL default '',"
		. "\n `value` MEDIUMBLOB NOT NULL default '',"
		. "\n PRIMARY KEY ( `id`,`groupname` ),"
		. "\n KEY ( `expire`,`groupname` )"
		. "\n ) $charset";
		$db->setQuery( $sql );
		if (!$db->execute()){
            Factory::getApplication()->enqueueMessage($db->getErrorMsg(), 'error');
			//JError::raiseError(500, $db->getErrorMsg());
			//echo $db->_sql;
		}
	}

	/**
	 * Get cached data from a db by id and group
	 *
	 * @access	public
	 * @param	string	$id			The cache data id
	 * @param	string	$group		The cache data group
	 * @param	boolean	$checkTime	True to verify cache time expiration threshold
	 * @return	mixed	Boolean false on failure or a cached data string
	 * @since	1.5
	 */
	function get($id, $group, $checkTime = true)
	{
		
		if (method_exists($this->profile_db,"_profile")) $pfunc = $this->profile_db->_profile();

		$hashedid = md5($id.'-'.$this->_hash.'-'.$this->_language);

		$data = false;

		$sql = "SELECT  UNCOMPRESS(value) FROM `#__dbcache` WHERE id=".$this->db->quote($hashedid)." AND groupname=".$this->db->quote($group)." AND expire>FROM_UNIXTIME(".$this->db->quote($this->_now).")";
		
		$keepSQL = $this->db->_sql;
		$this->db->setQuery($sql);

		// Must set false to ensure that Joomfish doesn't get involved
		$data = $this->db->loadResult(false);
		
		$this->db->_sql = $keepSQL ;
		if (is_null($data)){
			$data = false;
		}
		else {
			//$data = base64_decode($data);
		}
		if (method_exists($this->profile_db,"_profile")) $pfunc = $this->profile_db->_profile($pfunc);

		return $data;
	}

	/**
	 * Store the data to a file by id and group
	 *
	 * @access	public
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @param	string	$data	The data to store in cache
	 * @return	boolean	True on success, false otherwise
	 * @since	1.5
	 */
	function store($id, $group, $data)
	{
		//$this->db = JFactory::getDBO();
		if (method_exists($this->profile_db,"_profile")) $pfunc = $this->profile_db->_profile();

		$hashedid = md5($id.'-'.$this->_hash.'-'.$this->_language);

		//$data = base64_encode($data);
		$sql = "REPLACE INTO `#__dbcache` (id, groupname,expire,value) VALUES (".$this->db->quote($hashedid).",".$this->db->quote($group).",FROM_UNIXTIME(".$this->db->quote($this->_now + $this->_lifetime)."),COMPRESS(".$this->db->quote($data)."))";
		$this->db->setQuery($sql);

		$res =  $this->db->execute();
		if (method_exists($this->profile_db,"_profile")) $pfunc = $this->profile_db->_profile($pfunc);
		return $res;
	}

	/**
	 * Remove a cached data file by id and group
	 *
	 * @access	public
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @return	boolean	True on success, false otherwise
	 * @since	1.5
	 */
	function remove($id, $group)
	{
		//$this->db = JFactory::getDBO();
		if (method_exists($this->profile_db,"_profile")) $pfunc = $this->profile_db->_profile();
		$sql = "DELETE FROM `#__dbcache` WHERE id=".$this->db->quote($id)." AND groupname=".$this->db->quote($group);
		$this->db->_skipSetRefTables=true;
		$this->db->setQuery($sql);
		$this->db->_skipSetRefTables=false;
		if (method_exists($this->profile_db,"_profile")) $pfunc = $this->profile_db->_profile($pfunc);
		return $this->db->execute();
	}

	/**
	 * Clean cache for a group given a mode.
	 *
	 * group mode		: cleans all cache in the group
	 * notgroup mode	: cleans all cache not in the group
	 *
	 * @access	public
	 * @param	string	$group	The cache data group
	 * @param	string	$mode	The mode for cleaning cache [group|notgroup]
	 * @return	boolean	True on success, false otherwise
	 * @since	1.5
	 */
	function clean($group, $mode = null)
	{
		//$this->db = JFactory::getDBO();
		if (method_exists($this->profile_db,"_profile")) $pfunc = $this->profile_db->_profile();
		$result = false;
		switch (trim($mode))
		{
			case 'group':
				$sql = "DELETE FROM `#__dbcache` ";
				$this->db->setQuery($sql);
				break;
			default:
				$sql = "DELETE FROM `#__dbcache` WHERE groupname=".$this->db->quote($group);
				$this->db->setQuery($sql);
				break;
		}
		$this->db->_skipSetRefTables=true;
		$result = $this->db->execute();
		$this->db->_skipSetRefTables=false;
		
		if (method_exists($this->profile_db,"_profile")) $pfunc = $this->profile_db->_profile($pfunc);
		return $result;
	}

	/**
	 * Clean the whole cache
	 *
	 * @return	boolean	True on success, false otherwise
	 */
	function cleanCache()
	{
		//$this->db = JFactory::getDBO();
		if (method_exists($this->profile_db,"_profile")) $pfunc = $this->profile_db->_profile();
		$result = false;
		$sql = "DELETE FROM `#__dbcache` ";
		$this->db->setQuery($sql);
		$this->db->_skipSetRefTables=true;
		$result = $this->db->execute();
		$this->db->_skipSetRefTables=false;
		if (method_exists($this->profile_db,"_profile")) $pfunc = $this->profile_db->_profile($pfunc);
		return $result;
	}

	/**
	 * Garbage collect expired cache data
	 *
	 * @access public
	 * @return boolean  True on success, false otherwise.
	 */
	function gc()
	{
		//$this->db = JFactory::getDBO();
		if (method_exists($this->profile_db,"_profile")) $pfunc = $this->profile_db->_profile();
		$sql = "DELETE FROM `#__dbcache` WHERE expire< FROM_UNIXTIME(".$this->db->quote($this->_now).")";
		$this->db->setQuery($sql);
		$this->db->_skipSetRefTables=true;
		$result = $this->db->execute();
		$this->db->_skipSetRefTables=false;
		// if we can't delete then the database table probably doesn't exist so create it
		if (!$result && $this->db->getErrorNum()==1146){
			$this->setupDB();
		}
		if (method_exists($this->profile_db,"_profile")) $pfunc = $this->profile_db->_profile($pfunc);
	}

	/**
	 * Test to see if the cache storage is available.
	 *
	 * @static
	 * @access public
	 * @return boolean  True on success, false otherwise.
	 */
        //sbou
	//function test()
	static function test()
        //fin sbou
	{
		return true;
	}

}
