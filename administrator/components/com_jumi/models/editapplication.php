<?php
/**
 * Joomla! 1.5 component sexy_polling
 *
 * @version $Id: manageanswers.php 2012-04-05 14:30:25 svn $
 * @author Simon Poghosyan
 * @package Joomla
 * @subpackage sexy_polling
 * @license GNU/GPL
 *
 * Sexy Polling
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Import Joomla! libraries
jimport('joomla.application.component.model');
jimport( 'joomla.utilities.date' );

if(JV == 'j2') {
	//j2 stuff here///////////////////////////////////////////////////////////////////////////////////////////////////////
	class JumiModeleditApplication extends JModel {
		
		/**
		 * Constructor that retrieves the ID from the request
		 *
		 * @access	public
		 * @return	void
		 */
		function __construct()
		{
			parent::__construct();
	
			$array = JRequest::getVar('cid',  0, '', 'array');
			$this->setId((int)$array[0]);
		}
	
		/**
		 * Method to set the hello identifier
		 *
		 * @access	public
		 * @param	int Hello identifier
		 * @return	void
		 */
		function setId($id)
		{
			// Set id and wipe data
			$this->_id		= $id;
			$this->_data	= null;
			
		}
	
		/**
		 * Method to get a data
		 * @return object with data
		 */
		function &getData()
		{
			// Load the data
			if (empty( $this->_data )) {
				$query = 'SELECT * FROM #__jumi WHERE id = '.$this->_id;
				$this->_db->setQuery( $query );
				$this->_data = $this->_db->loadObject();
			}
			if (!$this->_data) {
				$this->_data = new stdClass();
				$this->_data->id = 0;
				$this->_data->name = null;
			}
			JFilterOutput::objectHTMLSafe($this->_data,ENT_QUOTES);
			return $this->_data;
		}
		
	
		/**
		 * Method to store a record
		 *
		 * @access	public
		 * @return	boolean	True on success
		 */
		function store()
		{
			$array = JRequest::getVar('cid',  0, '', 'array');
			$applid = (int)$array[0];
					
			$title         = $this->_db->Quote(JRequest::getString('title'));
			$alias         = $this->_db->Quote(JRequest::getString('alias'));
			$custom_script = $this->_db->Quote(stripslashes($_POST['custom_script']));
			$path          = $this->_db->Quote(JRequest::getString('path'));
			if($applid == 0) {
				$query = "insert into #__jumi (title, alias, custom_script, path) values($title,$alias,$custom_script,$path)";
				$this->_db->setQuery($query);
				if(!$this->_db->query())
					return false;
			} else {
				$query = "update #__jumi set title = $title, alias = $alias, custom_script = $custom_script, path = $path where id = $applid";
				$this->_db->setQuery($query);
				if(!$this->_db->query())
					return false;
			}
		
			return true;
		}
	
		/**
		 * Method to delete record(s)
		 *
		 * @access	public
		 * @return	boolean	True on success
		 */
		function delete()
		{
			$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );
	
			if (count( $cids )) {
				foreach($cids AS $id) {
	 				$query = "delete from #__jumi where id = $id"; 
					$this->_db->setQuery($query);
					$this->_db->query();
					if($this->_db->getErrorMsg())
						return false;
				}
			}
			
			return true;
		}
		
		/**
		 * Method to delete record(s)
		 *
		 * @access	public
		 * @return	boolean	True on success
		 */
		function publish($publish)
		{
			$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );
			JArrayHelper::toInteger($cids);
			$cids_sql = implode(',',$cids);
			
			if (count( $cids )) {
				$query = "UPDATE #__jumi SET published = ".(int) $publish." WHERE id in ($cids_sql)";
				$this->_db->setQuery( $query );
				if (!$this->_db->query())
					return false;
			}
			
			return true;
		}
		
	}
}
else {
	//j3 stuff here///////////////////////////////////////////////////////////////////////////////////////////////////////
	class JumiModeleditApplication extends JModelLegacy {
	
		/**
		 * Constructor that retrieves the ID from the request
		 *
		 * @access	public
		 * @return	void
		 */
		function __construct()
		{
			parent::__construct();
	
			$array = JRequest::getVar('cid',  0, '', 'array');
			$this->setId((int)$array[0]);
		}
	
		/**
		 * Method to set the hello identifier
		 *
		 * @access	public
		 * @param	int Hello identifier
		 * @return	void
		 */
		function setId($id)
		{
			// Set id and wipe data
			$this->_id		= $id;
			$this->_data	= null;
				
		}
	
		/**
		 * Method to get a data
		 * @return object with data
		 */
		function &getData()
		{
			// Load the data
			if (empty( $this->_data )) {
				$query = 'SELECT * FROM #__jumi WHERE id = '.$this->_id;
				$this->_db->setQuery( $query );
				$this->_data = $this->_db->loadObject();
			}
			if (!$this->_data) {
				$this->_data = new stdClass();
				$this->_data->id = 0;
				$this->_data->name = null;
			}
			JFilterOutput::objectHTMLSafe($this->_data,ENT_QUOTES);
			return $this->_data;
		}
	
	
		/**
		 * Method to store a record
		 *
		 * @access	public
		 * @return	boolean	True on success
		 */
		function store()
		{
			$array = JRequest::getVar('cid',  0, '', 'array');
			$applid = (int)$array[0];
				
			$title         = $this->_db->Quote(JRequest::getString('title'));
			$alias         = $this->_db->Quote(JRequest::getString('alias'));
			$custom_script = $this->_db->Quote(stripslashes($_POST['custom_script']));
			$path          = $this->_db->Quote(JRequest::getString('path'));
			if($applid == 0) {
				$query = "insert into #__jumi (title, alias, custom_script, path) values($title,$alias,$custom_script,$path)";
				$this->_db->setQuery($query);
				if(!$this->_db->query())
					return false;
			} else {
				$query = "update #__jumi set title = $title, alias = $alias, custom_script = $custom_script, path = $path where id = $applid";
				$this->_db->setQuery($query);
				if(!$this->_db->query())
					return false;
			}
	
			return true;
		}
	
		/**
		 * Method to delete record(s)
		 *
		 * @access	public
		 * @return	boolean	True on success
		 */
		function delete()
		{
			$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );
	
			if (count( $cids )) {
				foreach($cids AS $id) {
					$query = "delete from #__jumi where id = $id";
					$this->_db->setQuery($query);
					$this->_db->query();
					if($this->_db->getErrorMsg())
						return false;
				}
				}
					
				return true;
		}
	
		/**
		* Method to delete record(s)
		 *
		 * @access	public
		 * @return	boolean	True on success
		 */
		function publish($publish) {
		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );
				JArrayHelper::toInteger($cids);
				$cids_sql = implode(',',$cids);
					
				if (count( $cids )) {
				$query = "UPDATE #__jumi SET published = ".(int) $publish." WHERE id in ($cids_sql)";
				$this->_db->setQuery( $query );
				if (!$this->_db->query())
					return false;
				}
					
				return true;
		}
	}
}