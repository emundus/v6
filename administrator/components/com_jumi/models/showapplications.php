<?php
/**
 * Joomla! component sexy_polling
 *
 * @version $Id: sexyanswers.php 2012-04-05 14:30:25 svn $
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
jimport( 'joomla.database.database' );

if(JV == 'j2') {
	//j2 stuff here///////////////////////////////////////////////////////////////////////////////////////////////////////
	class JumiModelshowApplications extends JModel {
		var $_data, $_total, $_pagination, $_filter;
		
		function __construct() {
			 
			parent::__construct();
			 
			$this->loadFilter();
			 
			$mainframe = JFactory::getApplication();
			 
			$option = 'com_jumi';
			 
			$this->_filter->filter_order     = $mainframe->getUserStateFromRequest("$option.filter_order",'filter_order','m.id');
			$this->_filter->filter_order_Dir = $mainframe->getUserStateFromRequest("$option.filter_order_Dir",'filter_order_Dir','');
			$this->_filter->filter_state     = $mainframe->getUserStateFromRequest("$option.filter_state",'filter_state','*');
			$search           = $mainframe->getUserStateFromRequest("$option.search",'search','');
			$search           = $this->_db->escape(trim(JString::strtolower($search)));
			$this->_filter->search = $search;
			
			if (!in_array($this->_filter->filter_order, array('m.title','m.path','m.published','g.name','m.id'))) {
				$this->_filter->filter_order = 'm.id';
			}
			if (!in_array(strtoupper($this->_filter->filter_order_Dir), array('ASC', 'DESC'))) {
				$this->_filter->filter_order_Dir = '';
			}
			 
			
			//limits
			$limit      = $mainframe->getUserStateFromRequest( $option.'.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
			$limitstart = $mainframe->getUserStateFromRequest( $option.JRequest::getCmd( 'view').'.limitstart', 'limitstart', 0, 'int' );
			if($limitstart > $this->getTotal()) $limitstart = 0;
			 
			$this->setState('limit', $limit);
			$this->setState('limitstart', $limitstart);
		}
		
		function loadFilter() {
			$this->_filter = new JObject();
		
		}
		
		function getFilter() {
			return $this->_filter;
		}
		
		/**
		 * Returns the query
		 * @return string The query to be used to retrieve the rows from the database
		 */
		function _buildQuery() {
		
		
			$where = array();
			 
			if( $this->_filter->filter_state )
			{
				if( $this->_filter->filter_state  == 'P')
					$where[] = 'm.published = 1';
				elseif( $this->_filter->filter_state  == 'U')
				$where[] = 'm.published = 0';
			}
			if($this->_filter->search)
				$where[] = 'LOWER(m.title) LIKE '.$this->_db->Quote( '%'.$this->_db->getEscaped( $this->_filter->search, true ).'%', false );
			
			$where 		= ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );
		
			//create ordering
		
			$orderby 	= ' ORDER BY '. $this->_filter->filter_order .' '. $this->_filter->filter_order_Dir;
		
			$query = "SELECT m.* FROM #__jumi as m ";
		
			$query_res = $query .  $where . $orderby;
			return $query_res;
		}
		
		/**
		 * Retrieves the hello data
		 * @return array Array of objects containing the data from the database
		 */
		function getData() {
			// Lets load the data if it doesn't already exist
			if (empty( $this->_data )) {
				$query = $this->_buildQuery();
				$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
			}
		
			return $this->_data;
		}
		
		function getTotal()
		{
			//-- Load the content if it doesn't already exist
			if(empty($this->_total))
			{
				$this->_total = $this->_getListCount($this->_buildQuery());
			}
		
			return $this->_total;
		}//function
		
		function getPagination()
		{
			//-- Load the content if it doesn't already exist
			if(empty($this->_pagination))
			{
				jimport('joomla.html.pagination');
				$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
			}
		
			return $this->_pagination;
		}//function
	}
}
else {
	//j3 stuff here///////////////////////////////////////////////////////////////////////////////////////////////////////
	class JumiModelshowApplications extends JModelLegacy {
		var $_data, $_total, $_pagination, $_filter;
	
		function __construct() {
	
			parent::__construct();
	
			$this->loadFilter();
	
			$mainframe = JFactory::getApplication();
	
			$option = 'com_jumi';
	
			$this->_filter->filter_order     = $mainframe->getUserStateFromRequest("$option.filter_order",'filter_order','m.id');
			$this->_filter->filter_order_Dir = $mainframe->getUserStateFromRequest("$option.filter_order_Dir",'filter_order_Dir','');
			$this->_filter->filter_state     = $mainframe->getUserStateFromRequest("$option.filter_state",'filter_state','*');
			$search           = $mainframe->getUserStateFromRequest("$option.search",'search','');
			$search           = $this->_db->escape(trim(JString::strtolower($search)));
			$this->_filter->search = $search;
				
			if (!in_array($this->_filter->filter_order, array('m.title','m.path','m.published','g.name','m.id'))) {
				$this->_filter->filter_order = 'm.id';
			}
			if (!in_array(strtoupper($this->_filter->filter_order_Dir), array('ASC', 'DESC'))) {
				$this->_filter->filter_order_Dir = '';
			}
	
				
			//limits
			$limit      = $mainframe->getUserStateFromRequest( $option.'.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
			$limitstart = $mainframe->getUserStateFromRequest( $option.JRequest::getCmd( 'view').'.limitstart', 'limitstart', 0, 'int' );
			if($limitstart > $this->getTotal()) $limitstart = 0;
	
			$this->setState('limit', $limit);
			$this->setState('limitstart', $limitstart);
		}
	
		function loadFilter() {
			$this->_filter = new JObject();
	
		}
	
		function getFilter() {
			return $this->_filter;
		}
	
		/**
		 * Returns the query
		 * @return string The query to be used to retrieve the rows from the database
		 */
		function _buildQuery() {
	
	
			$where = array();
	
			if( $this->_filter->filter_state )
			{
				if( $this->_filter->filter_state  == 'P')
					$where[] = 'm.published = 1';
				elseif( $this->_filter->filter_state  == 'U')
				$where[] = 'm.published = 0';
			}
			if($this->_filter->search)
				$where[] = 'LOWER(m.title) LIKE '.$this->_db->Quote( '%'.$this->_db->escape( $this->_filter->search, true ).'%', false );
				
			$where 		= ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );
	
			//create ordering
	
			$orderby 	= ' ORDER BY '. $this->_filter->filter_order .' '. $this->_filter->filter_order_Dir;
	
			$query = "SELECT m.* FROM #__jumi as m ";
	
			$query_res = $query .  $where . $orderby;
			return $query_res;
		}
	
		/**
		 * Retrieves the hello data
		 * @return array Array of objects containing the data from the database
		 */
		function getData() {
			// Lets load the data if it doesn't already exist
			if (empty( $this->_data )) {
				$query = $this->_buildQuery();
				$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
			}
	
			return $this->_data;
		}
	
		function getTotal()
		{
			//-- Load the content if it doesn't already exist
			if(empty($this->_total))
			{
				$this->_total = $this->_getListCount($this->_buildQuery());
			}
	
			return $this->_total;
		}//function
	
		function getPagination()
		{
			//-- Load the content if it doesn't already exist
			if(empty($this->_pagination))
			{
				jimport('joomla.html.pagination');
				$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
			}
	
			return $this->_pagination;
		}//function
	}
}