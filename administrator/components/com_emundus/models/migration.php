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

	// Gets all users in jos_emundus_campaign_cadidatures
	function getUsersInCC() {

		$query = "SELECT DISTINCT(applicant_id) FROM #__emundus_campaign_candidature ORDER BY applicant_id";

		try {

			$this->_db->setQuery($query);
			return $this->_db->loadObjectList();

		} catch (Exception $e) {
			return $e->getMessage;
		}
	}

	// Tests to see if an fnum is attached to data or not
	// This is done by looking at personal detail as it is the first form that would be filled by a user
	function testFnum($fnum) {

		$query = "SELECT 1 FROM ( SELECT fnum AS fnum FROM #__emundus_personal_detail ) a WHERE fnum = ".$this->_db->Quote($fnum);

		$this->_db->setQuery($query);

		if ($this->_db->loadResult() == 1)
			return true;
		else
			return false;
	}

	/* This function looks in all DB tables
	*  If $dataFnum is found in any of the tables then it calls the copy function for that table
	*/
	function copyFnumTablePicker($dataFnum, $emptyFnums) {

		// First we get a list of all tables containing an fnum collumn
		try {

			$query = "SELECT * FROM information_schema.columns WHERE TABLE_SCHEMA LIKE '". JFactory::getConfig()->get('db') ."' AND column_name = 'fnum' AND TABLE_NAME NOT IN ('jos_emundus_evaluations', 'jos_emundus_final_grade', 'jos_emundus_admission', 'jos_emundus_group_assoc', 'jos_emundus_comments', 'jos_emundus_emailalert', 'jos_emundus_files_request', 'jos_emundus_users_assoc',  'jos_emundus_campaign_candidature', 'jos_emundus_files_request')";
			$this->_db->setQuery($query);
			$tables = $this->_db->loadObjectList();

		} catch (Exception $e) {
			return $e->getMessage();
		}

		// Then for each of those tables we check the the $dataFnum is found in it
		foreach ($tables as $table) {
			$query = "SELECT 1 FROM ( SELECT fnum AS fnum FROM ".$table->TABLE_NAME." ) a WHERE fnum = ".$this->_db->Quote($dataFnum);
			$this->_db->setQuery($query);
			if ($this->_db->loadResult() == 1)
				$this->copyFnumData($dataFnum, $emptyFnums, $table->TABLE_NAME);
		}
	}

	// Here we look in the table defined by $tableName and find the row corresponding to $dataFnum
	// Then we create a new row for the $dataFnum and add a line containing that data for each of the $emptyFnums
	function copyFnumData($dataFnum, $emptyFnums, $tableName) {

		// Get all data from the row of the data Fnum
		$query = "SELECT * FROM ".$tableName." WHERE fnum = ".$this->_db->Quote($dataFnum);
		$this->_db->setQuery($query);
		$result = $this->_db->loadAssoc();

		if (count($result > 0)) {
			foreach ($emptyFnums as $emptyFnum) {

				// If the $emptyFnum is already in the table, then no need to copy it again.
				$query = "SELECT 1 FROM ( SELECT fnum AS fnum FROM ".$tableName." ) a WHERE fnum = ".$this->_db->Quote($emptyFnum);
				$this->_db->setQuery($query);
				if ($this->_db->loadResult() == 1)
					continue;


				unset($result['id']);
				$result['fnum'] = $emptyFnum;

				// Build a query to insert the data into the rows
				$query = 'INSERT INTO '.$tableName.' (`'.implode('`,`', array_keys($result)).'`) VALUES('.implode(',', $this->_db->Quote($result)).')';

				try {

					$this->_db->setQuery($query);
					$this->_db->Query();
					return true;

				} catch (Exception $e) {
					echo $e->getMessage();
				}

			}
		}


		// TODO: foreach empty fnum, put the data from above into the rows of the table (key -> value == column -> value)
		// Except for the fnum which is equal to the emptyfnum value

	}

	/**
	 * This function gets all fnums and users that are marked as validated.
	 * This is used in conjunction with the tagValidations() function to tag all falidated files.
	 *
	 * @return mixed,bool
	 */
	function getValidatedFiles() {

		try {

			$query = "SELECT fnum,user FROM #__emundus_declaration WHERE validated = 1";
			$this->_db->setQuery($query);
			return $this->_db->loadObjectList();

		} catch (Exception $e) {
			die($e->getMessage());
			return false;
		}

	}

	/**
	 * This function tags the given file with the tag 'Validated'.
	 * The user that runs this function (most likely Admin) will be the user that tags the file.
	 * The tag ID of 23 is specific to ESA, but that is probably the only case of this functions use.
	 *
	 * @param string $fnum The file number.
	 * @param int $user The ID pof the user to be tagged (can also be gotten by looking at the last 7 digits of the fnum).
	 *
	 * @return bool
	 */
	function tagValidations($fnum, $user) {

		$current_user = JFactory::getUser();

		try {

			$query = 'INSERT INTO #__emundus_tag_assoc(fnum, id_tag, applicant_id, user_id) VALUES ("'.$fnum.'", 23,'.$user.','.$current_user->id.'); ';
			$this->_db->setQuery($query);
			$this->_db->execute();
            return true;

		} catch (Exception $e) {
            die($e->getMessage());
            return false;
		}

	}
}
?>