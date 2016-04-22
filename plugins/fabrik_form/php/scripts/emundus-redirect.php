<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: emundus-redirect.php 89 2015-07-01 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2016 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Redirection et chainage des formulaires suivant le profile de l'utilisateur
 */


/********************************************
 * 
 * Duplicate data on each applicant file for current campaigns
 */
jimport('joomla.log.log');
JLog::addLogger(
    array(
        // Sets file name
        'text_file' => 'com_emundus.duplicate.php'
    ),
    JLog::ALL,
    array('com_emundus')
);
//echo "<pre>";print_r($data);echo "<hr>";

$eMConfig = JComponentHelper::getParams('com_emundus');
$copy_application_form = $eMConfig->get('copy_application_form', 0);

$user 	= JFactory::getUser();
$db 	= JFactory::getDBO();

if ($copy_application_form == 1 && isset($user->fnum)) {
	// Get some form definition
	$table = explode('___', key($data));
	$table_name = $table[0];
	$table_key = $table[1];
	$table_key_value = array_values($data)[0];
	$fnums = $user->fnums;
	unset($fnums[$user->fnum]);

	$fabrik_repeat_group = array();
	if (!empty($data['fabrik_repeat_group'])) {
		foreach ($data['fabrik_repeat_group'] as $key => $value) {
			$fabrik_repeat_group[] = $key;
		}
	}
	// only repeated groups
	$fabrik_group_rowids = array();
	if (!empty($data['fabrik_group_rowids'])) {
		foreach ($data['fabrik_group_rowids'] as $key => $value) {
			$fabrik_group_rowids[] = $key;
		}
	}
	// get columns
	$query = 'SELECT id, name, group_id
				FROM #__fabrik_elements 
				WHERE published=1 AND group_id IN ('.implode(',', $fabrik_repeat_group).') AND name not in ("id", "user", "fnum", "parent_id")';
	$db->setQuery( $query );
	$elements = $db->loadAssocList('id');

	// construction of our data structure
	$bdd_def = array();
	foreach ($elements as $key => $element) {
		if (in_array($element['group_id'], $fabrik_group_rowids)) {
			$key = $table_name.'_'.$element['group_id'].'_repeat';
		} else {
			$key = $table_name;
		}
		$bdd_def[$key]['elements'][] = $element['name'];
		$bdd_def[$key]['values'][] = $data[$key.'___'.$element['name'].'_raw'];
		$bdd_def[$key]['update'][] = '`'.$element['name'].'` = '.$db->Quote($data[$key.'___'.$element['name'].'_raw']);
	}
	// Is that a new insertion ?
	if (isset($data['usekey_newrecord']) && $data['usekey_newrecord']==1) {
		// Parent table
		$data = implode(',', $db->Quote($bdd_def[$table_name]['values']));

		foreach ($fnums as $key => $fnum) {
			$query = 'INSERT INTO `'.$table_name.'` ('.implode(',', $bdd_def[$table_name]['elements']).', fnum, user) VALUES ';
			$query .= '('.$data.', '.$db->Quote($key).', '.$user->id.')';
		
			$db->setQuery( $query );
			try {
			    $db->execute();
			    $parent_id[] = $db->insertid();
			} catch (Exception $e) {
			    $error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$e->getMessage();
				JLog::add($error, JLog::ERROR, 'com_emundus');
			}
		}

		$query = '';
		unset($bdd_def[$table_name]);
		// Joined table (with repeated group)
		foreach ($bdd_def as $key => $bdd) {
			$query = 'INSERT INTO `'.$key.'` ('.implode(',', $bdd['elements']).', parent_id) VALUES ';
			foreach ($parent_id as $i => $parent) {	
				
				for($i=0 ; $i < count($bdd['values'][0]) ; $i++) {
					$data = array();
					foreach ($bdd['values'] as $key => $v) {
						$data[] = $v[$i];
					}
					$values[] = '('.implode(',', $db->Quote($data)).', '.$parent.')';
				}
		
				
			}
			$query .= implode(',', $values);
			$db->setQuery( $query );
			try {
			    $res = $db->execute();
			} catch (Exception $e) {
			    $error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$e->getMessage();
		        JLog::add($error, JLog::ERROR, 'com_emundus');
			}
		}
	} else {
		// Parent table
		$updated_fnum = array();
		foreach ($fnums as $key => $fnum) {	
			$query = 'UPDATE `'.$table_name.'` SET ';
			$query .= implode(',', $bdd_def[$table_name]['update']);
			$query .= ' WHERE fnum like '.$db->Quote($key);
			$db->setQuery( $query );
			try {
echo "<hr>";
var_dump($query);
			    $res = $db->execute();
			    $updated_fnum[] = $key;
			} catch (Exception $e) {
			    $error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$e->getMessage();
		        JLog::add($error, JLog::ERROR, 'com_emundus');
			}
		}

	}		
	// Joined table (with repeat group)
	if (count($updated_fnum) > 0) {
		$query = 'SELECT id FROM `'.$table_name.'` WHERE fnum IN ('.implode(',', $db->Quote($updated_fnum)).')';
		$db->setQuery( $query );
		$parent_id = $db->loadColumn();
	}
	
	//********//
	unset($bdd_def[$table_name]);
	foreach ($bdd_def as $key => $bdd) {
		$query = 'DELETE FROM `'.$key.'` WHERE parent_id IN ('.implode(',', $parent_id).')';
		$db->setQuery( $query );
		try {
			var_dump($query);
		    $res = $db->execute();
		} catch (Exception $e) {
		    $error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$e->getMessage();
	        JLog::add($error, JLog::ERROR, 'com_emundus');
		}

		$query = 'INSERT INTO `'.$key.'` ('.implode(',', $bdd['elements']).', parent_id) VALUES ';
		foreach ($parent_id as $i => $parent) {	
			
			for($i=0 ; $i < count($bdd['values'][0]) ; $i++) {
				$data = array();
				foreach ($bdd['values'] as $key => $v) {
					$data[] = $v[$i];
				}
				$values[] = '('.implode(',', $db->Quote($data)).', '.$parent.')';
			}
	
			
		}
		$query .= implode(',', $values);
		$db->setQuery( $query );
		try {
		    $res = $db->execute();
		} catch (Exception $e) {
		    $error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$e->getMessage();
	        JLog::add($error, JLog::ERROR, 'com_emundus');
		}
	}
}


/*
 * REDIRECTION ONCE DUPLICATION IS DONE
*/

require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');

$user 	=  JFactory::getUser();
$jinput = JFactory::getApplication()->input;
$formid = $jinput->get('formid');

$db 	= JFactory::getDBO();

if (EmundusHelperAccess::asApplicantAccessLevel($user->id)){
	$query = 'SELECT CONCAT(link,"&Itemid=",id) 
			FROM #__menu 
			WHERE published=1 AND menutype = "'.$user->menutype.'" 
			AND parent_id != 1
			AND lft = 2+(
					SELECT menu.lft 
					FROM `#__menu` AS menu 
					WHERE menu.published=1 AND menu.parent_id>1 AND menu.menutype="'.$user->menutype.'" 
					AND SUBSTRING_INDEX(SUBSTRING(menu.link, LOCATE("formid=",menu.link)+7, 3), "&", 1)='.$formid.')';

	$db->setQuery( $query );
	$link = $db->loadResult();

	if(empty($link)) {
		$query = 'SELECT CONCAT(link,"&Itemid=",id) 
		FROM #__menu 
		WHERE published=1 AND menutype = "'.$user->menutype.'" AND type!="separator" AND published=1 AND alias LIKE "checklist%"';
		$db->setQuery( $query );
		$link = $db->loadResult();
	}	
} else { 
	$query = 'SELECT db_table_name FROM `#__fabrik_lists` WHERE `form_id` ='.$formid;
	$db->setQuery( $query );
	$db_table_name = $db->loadResult();

	$fnum 		= $jinput->get($db_table_name.'___fnum');
	$s1 = JRequest::getVar($db_table_name.'___user', null, 'POST'); 
	$s2 = JRequest::getVar('sid', '', 'GET');
	$student_id = !empty($s2)?$s2:$s1; 

	$sid = is_array($student_id)?$student_id[0]:$student_id;
	$query = 'UPDATE '.$db_table_name.' SET user='.$sid.' WHERE fnum like '.$db->Quote($fnum); 
	$db->setQuery( $query );
	$db->execute();

	$link = JRoute::_('index.php?option=com_fabrik&view=form&formid='.$formid.'&usekey=fnum&rowid='.$fnum);
	//$link = "index.php?option=com_emundus&view=application&sid=".$sid.'&fnum='.$fnum;
}

header('Location: '.$link);
exit();
 ?>