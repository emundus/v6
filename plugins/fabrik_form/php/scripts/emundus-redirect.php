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

if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)){
	echo "<hr>";
	echo '<h1><img src="'.JURI::Base().'/media/com_emundus/images/icones/admin_val.png" width="80" height="80" align="middle" /> '.JText::_("SAVED").'</h1>';
	echo "<hr>";
	exit();
}

// duplication is defined
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
	$fabrik_group_rowids_key = array();
	if (!empty($data['fabrik_group_rowids'])) {
		foreach ($data['fabrik_group_rowids'] as $key => $value) {
			$repeat_table_name = $table_name.'_'.$key.'_repeat';
			$query = 'SELECT id FROM '.$repeat_table_name.' WHERE parent_id='.$data['rowid'];
			$db->setQuery( $query );
			$fabrik_group_rowids_key[$key] = $db->loadColumn();
		}
	}

//////////////////////////////
	// Only if other application files found
	if (count($fnums) > 0) {
		$query = 'SELECT * FROM '.$table_name.' WHERE id='.$data['rowid'];
		$db->setQuery( $query );
		$parent_data = $db->loadAssoc();
		unset($parent_data['fnum']);
		unset($parent_data['id']);

		// new record
		if (isset($data['usekey_newrecord']) && $data['usekey_newrecord']==1) {
			// Parent table
			$parent_id = array();
			foreach ($fnums as $key => $fnum) {
				$query = 'INSERT INTO `'.$table_name.'` (`'.implode('`,`', array_keys($parent_data)).'`, `fnum`) VALUES ';
				$query .= '('.implode(',', $db->Quote($parent_data)).', '.$db->Quote($key).')';
				$db->setQuery( $query );
				try {
				    $db->execute();
				    $parent_id[] = $db->insertid();
				} catch (Exception $e) {
				    $error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$e->getMessage();
					JLog::add($error, JLog::ERROR, 'com_emundus');
				}
			}
			// Repeated table
			foreach ($fabrik_group_rowids_key as $key => $rowids) {
				if (count($rowids) > 0) {
				
					$repeat_table_name = $table_name.'_'.$key.'_repeat';

					$query = 'SELECT * FROM `'.$repeat_table_name.'` WHERE id IN ('.implode(',', $rowids).')';
					
					try {
						$db->setQuery( $query );
						$repeat_data = $db->loadAssocList();
					} catch (Exception $e) {
					    $error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$e->getMessage();
						JLog::add($error, JLog::ERROR, 'com_emundus');
					}
					if (count($repeat_data) > 0) {
						foreach ($parent_id as $parent) {
							$parent_data = array();
							foreach ($repeat_data as $key => $d) {
								unset($d['parent_id']);
								unset($d['id']);
								$columns = '`'.implode('`,`', array_keys($d)).'`';
								$parent_data[] = '('.implode(',', $db->Quote($d)).', '.$parent.')';
							}
							$query = 'INSERT INTO `'.$repeat_table_name.'` ('.$columns.', `parent_id`) VALUES ';
							$query .= implode(',', $parent_data);
							$db->setQuery( $query );
							try {
						   		$db->execute();
							} catch (Exception $e) {
							    $error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$e->getMessage();
								JLog::add($error, JLog::ERROR, 'com_emundus');
							}
						}
					}
				}
			}
		} else{
			// Parent table
			$updated_fnum = array();
			foreach ($fnums as $fnum => $f) {
				$query = 'UPDATE `'.$table_name.'` SET ';
				$parent_update = array();
				foreach ($parent_data as $key => $value) {
					$parent_update[] = '`'.$key.'`='.$db->Quote($value);
				}
				$query .= implode(',', $parent_update);
				$query .= ' WHERE fnum like '.$db->Quote($fnum);
				$db->setQuery( $query );
				try {
				    $res = $db->execute();
				    $updated_fnum[] = $fnum;
				} catch (Exception $e) {
				    $error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$e->getMessage();
			        JLog::add($error, JLog::ERROR, 'com_emundus');
				}
			}
			if (count($updated_fnum) > 0) {
				$query = 'SELECT id FROM `'.$table_name.'` WHERE fnum IN ('.implode(',', $db->Quote($updated_fnum)).')';
				$db->setQuery( $query );
				$parent_id = $db->loadColumn();
			}

			// Repeated table
			foreach ($fabrik_group_rowids_key as $key => $rowids) {

				if (count($rowids) > 0) {
					$repeat_table_name = $table_name.'_'.$key.'_repeat';

					$query = 'SELECT * FROM `'.$repeat_table_name.'` WHERE id IN ('.implode(',', $rowids).')';
					try{
						$db->setQuery( $query );
						$repeat_data = $db->loadAssocList('id');
					} catch (Exception $e) {
					    $error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$e->getMessage();
						JLog::add($error, JLog::ERROR, 'com_emundus');
					}

					if (count($parent_id) > 0) {
						$query = 'DELETE FROM `'.$repeat_table_name.'` WHERE parent_id IN ('.implode(',', $parent_id).')';
						$db->setQuery( $query );
						try {
						    $res = $db->execute();
						} catch (Exception $e) {
						    $error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$e->getMessage();
					        JLog::add($error, JLog::ERROR, 'com_emundus');
						}
						if (count($repeat_data) > 0) {
							foreach ($parent_id as $parent) {
								$parent_data = array();
								foreach ($repeat_data as $key => $d) {
									unset($d['parent_id']);
									unset($d['id']);
									$columns = '`'.implode('`,`', array_keys($d)).'`';
									$parent_data[] = '('.implode(',', $db->Quote($d)).', '.$parent.')';
								}
								$query = 'INSERT INTO `'.$repeat_table_name.'` ('.$columns.', `parent_id`) VALUES ';
								$query .= implode(',', $parent_data);
								$db->setQuery( $query );
								try {
							   		$db->execute();
								} catch (Exception $e) {
								    $error = JUri::getInstance().' :: USER ID : '.$user->id.'\n -> '.$e->getMessage();
									JLog::add($error, JLog::ERROR, 'com_emundus');
								}
							}
						}
					}
				}
			}
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
			WHERE published=1 AND menutype = "'.$user->menutype.'" 
			AND parent_id != 1
			AND lft = 4+(
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
	$query = 'UPDATE `'.$db_table_name.'` SET `user`='.$sid.' WHERE fnum like '.$db->Quote($fnum); 
	$db->setQuery( $query );
	$db->execute();

	$link = JRoute::_('index.php?option=com_fabrik&view=form&formid='.$formid.'&usekey=fnum&rowid='.$fnum);
	//$link = "index.php?option=com_emundus&view=application&sid=".$sid.'&fnum='.$fnum;
}

header('Location: '.$link);
exit();
 ?>