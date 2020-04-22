<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: emundus_cleanRepeatedGroups.php 89 2017-09-13 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2016 eMundus. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Clean value fromrepeated groups before update
 */

$user 	= JFactory::getUser();

jimport('joomla.log.log');
JLog::addLogger([
		// Sets file name
		'text_file' => 'com_emundus.clean.php'
	],
	JLog::ALL,
	['com_emundus']
);


$db   	= JFactory::getDBO();


$parent_id = $data['rowid'];

$query = 'SELECT table_join FROM `jos_fabrik_joins` WHERE list_id='.$data['listid'];
$db->setQuery($query);
$table_join = $db->loadColumn();


// Get the table names 
foreach ($table_join as $t) {	

	$query = 'DELETE FROM  `'.$t.'` WHERE parent_id='.$parent_id;
	$db->setQuery($query);
	$db->execute();
}

// Look through the data var to build insert queries that contain the data.
$column = array();
$queryArray = array();
foreach ($data as $dkey => $dvalue) {
	
	// The tables and columns are written as table___column and so we must separate them.
	$dkey 		= explode("___", $dkey);
	$table 		= $dkey[0];
	$column 	= $dkey[1];
	$values 	= array();
	

	// If the table name is in the table_join array then we know that this is data to insert.
	// We also need to check if the column name ends in _raw, as that is the true value to insert and not the label
	if (in_array($table, $table_join) && substr($column,-4) == "_raw") {

		$column = substr($column, 0, -4);
	
		$i = 0;
		// Values are stored as arrays inside the tableName___column key.
		foreach ($dvalue as $dv) {

			// Ocasionnally the values are an array deeper for some strange reason.
			if (is_array($dv)) {

				// Amazing variable name below.
				foreach ($dv as $dvv) {
					$values[] = $dvv;
				}

			} else {

				// We add the values to an array which we will later implode to build the query.
				$values[] = $dv;

			}
		
		}

		// We need to wrangle the mess that is the object in order to be able to build the query.
		foreach ($values as $val) {
			$realValues[$table][$i][] = $val;
			$i++;
		 }

		// This is an array which will contain all table names and collumn names folled by the data associated to it.
		$queryArray[$table][] = $column;
	}

}





// Now we go through our array previously created and its time to build some queries.
foreach ($queryArray as $table => $columnArr) {

	// Building the values for the query.
	for ($j = 0; $j < sizeof($realValues[$table]); $j++) {
		$valuesArray[$table][] = "('".implode("','",$realValues[$table][$j])."')";
	}

	$query = "INSERT INTO ".$table." (".implode(",",$columnArr).") VALUES ".implode(",",$valuesArray[$table]);
	
	try {
	
		// And this is where we pray.
		$db->setQuery($query);
		$db->execute();
	
	} catch (Exception $e) {
		echo $e->getMessage();
	}
	
}

foreach ($data as $key => $value) {
	$formModel->updateFormData($key, $value, true);
}

?>