<?php
/**
 * eMundus Campaign model
 * 
 * @package    	Joomla
 * @subpackage 	eMundus
 * @link       	http://www.emundus.fr
 * @copyright	Copyright (C) 2008 - 2013 Décision Publique. All rights reserved.
 * @license    	GNU/GPL
 * @author     	Decision Publique - Benjamin Rivalland
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );

class EmundusModelMigration extends JModelList
{
	var $_user = null;
	var $_db = null;

	function __construct()
	{
		parent::__construct();
		global $option;
		
		$mainframe = JFactory::getApplication();
		
		$this->_db = JFactory::getDBO();
		$this->_user = JFactory::getUser();
		
		// Get pagination request variables
		$filter_order			= $mainframe->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'label', 'cmd' );
        $filter_order_Dir		= $mainframe->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', 'desc', 'word' );
        $limit 					= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart 			= $mainframe->getUserStateFromRequest('global.list.limitstart', 'limitstart', 0, 'int');
        $limitstart 			= ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		
 		$this->setState('filter_order', $filter_order);
        $this->setState('filter_order_Dir', $filter_order_Dir);
        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
	}

	function getRepeatTableList()
	{
		// Lets load the data if it doesn't already exist
		$query = $this->_buildQuery();
		$query .= $this->_buildContentOrderBy();
	//echo str_replace('#_', 'jos',$query).'<br /><br />';
		return $this->_getList( $query, $this->getState('limitstart'), $this->getState('limit'));
	} 
	
	function _buildQuery(){
		$query = 'SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME like "%_repeat%"';
		return $query;
	}
	
	function _buildContentOrderBy()
	{ 
        global $option;

		$mainframe = JFactory::getApplication();
 
        $orderby = '';
		$filter_order     = $this->getState('filter_order');
       	$filter_order_Dir = $this->getState('filter_order_Dir');

		$can_be_ordering = array ('TABLE_NAME');
        /* Error handling is never a bad thing*/
        if(!empty($filter_order) && !empty($filter_order_Dir) && in_array($filter_order, $can_be_ordering)){
        	$orderby = ' ORDER BY '.$filter_order.' '.$filter_order_Dir;
		}

        return $orderby;
	}

	function getColumnsNameByTable($tbl_name)
	{
		$query = 'SELECT DISTINCT(COLUMN_NAME)  
				  FROM INFORMATION_SCHEMA.COLUMNS
				  WHERE table_name = "'.$tbl_name.'"';
		$this->_db->setQuery( $query );

		return $this->_db->loadResultArray();
	}

	function getIsRepeatedColumn($tbl_name, $col_name)
	{
		$query = 'SELECT count(id) 
					FROM '.$tbl_name.' 
					WHERE '.$col_name.' LIKE "%//..*..//%"';
		$this->_db->setQuery( $query );
		return $this->_db->loadResult();
	}

	function migrateTable($tbl_name, $col_name)
	{ 
		$query = 'SELECT * FROM '.$tbl_name;
		$this->_db->setQuery( $query );
//echo str_replace("#_", "jos", $query);
		$table = $this->_db->loadAssocList();

		foreach ($table as $key => $value) {
			$multi = 0;
			$i = 0;
			$c = array();
			$id = '';
			foreach ($col_name as $col) { 
				
				//echo  $value[$col]."<br>--<br>**>".var_dump(explode("//..*..//", $value[$col]))."<hr>";
				if($col == "id") {
					$id = $value[$col];
				} else {
					$c[] = $col;
					$data["parent_id"] = $id;
					$data[$col] = explode("//..*..//", $value[$col]);
				}
				if (count($data[$col]) > 1)
					$multi = count($data[$col]);
			}
			if ($multi >= 1) {
				for ($i=0; $i < $multi ; $i++) { 
					$v = array();
					foreach ($col_name as $col) { 
						if($col == "parent_id")
							$v[] = $id;
						elseif($col != "id")
							$v[] = $this->_db->Quote($data[$col][$i]);
					}
					$query = "INSERT INTO ".$tbl_name." (".implode(",", $c).") VALUES (".implode(",", $v).");";
					$this->_db->setQuery( $query );
					$this->_db->Query();
					echo "<hr>".$query.'<br>';

					$query = "DELETE FROM ".$tbl_name." WHERE id=".$id;
					$this->_db->setQuery( $query );
					$this->_db->Query();

					echo $query;
				}
				
				/*var_dump($data); echo "<hr>".$multi."<hr>".$query."<hr>";
				return;*/
			}
		}
		
	}

	
	function getPagination()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}
	
	function getTotal()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_total)) {
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);    
		}
		return $this->_total;
	}
	
}
?>