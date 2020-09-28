<?php

defined('_JEXEC') or die();
/**
 * @version 1: link_user_institution.php 89 2018-07-05 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2018 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Ratachement d'un utilisateur a une institution au moment de la création de son compte.
 */
$profile = $data['jos_emundus_users___profile_raw'][0];

$db = JFactory::getDBO();
// 1007 = researcher
// 1008 = municipality
if ($profile == 1007) {

	$institution_id = $data['jos_emundus_users___laboratoire_raw'][0];

	// Time to get the category ID from the inserted lab
	$query = $db->getQuery(true);
	$query->select($db->quoteName(['catid','name']))->from($db->quoteName('em_laboratoire'))->where($db->quoteName('id').' = '.$institution_id);
	$db->setQuery($query);
	try {
		$institution = $db->loadObject();
	} catch (Exception $e) {
		// TODO: What to do if error? block user from account creation?? Might be too late.
	}

	// If the lab does not have a cat_id: add it.
	if (empty($institution->catid)) {
		//The category is that added into the Joomla system.
		$parent_id = 107;

		// Initialize a new category
		$category = JTable::getInstance('Category');
		$category->extension = 'com_contact';
		$category->title = $data['jos_emundus_users___laboratoire'];
		$category->description = '';
		$category->published = 1;
		$category->access = 1;
		$category->params = '{"target":"","image":""}';
		$category->metadata = '{"page_title":"","author":"","robots":""}';
		$category->language = '*';
		// Set the location in the tree
		$category->setLocation($parent_id, 'last-child');
		// Check to make sure our data is valid
		if (!$category->check()) {
			JError::raiseNotice(500, $category->getError());
			return false;
		}
		// Now store the category
		if (!$category->store(true)) {
			JError::raiseNotice(500, $category->getError());
			return false;
		}
		// Build the path for our category
		$category->rebuildPath($category->id);

		// Add the cat_id to the lab.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update($db->quoteName('em_laboratoire'))->set($db->quoteName('catid').' = '.$category->id)->where('id = '.$institution_id);
		$db->setQuery($query);
		try {
			$db->execute();
		} catch (Exception $e) {
			// TODO: What to do if error? block user from account creation?? Might be too late.
			JLog::add('Error adding catid to the new municipality at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
		}

		// Set the catid so that we can add it to the user.
		$institution->catid = $category->id;
	}

} elseif ($profile == 1008) {

	$institution_id = $data['jos_emundus_users___nom_de_structure_raw'][0];

	// Time to get the category ID from the inserted lab
	$query = $db->getQuery(true);
	$query->select($db->quoteName(['catid','nom_de_structure']))->from($db->quoteName('em_municipalitees'))->where($db->quoteName('id').' = '.$institution_id);
	$db->setQuery($query);
	try {
		$institution = $db->loadObject();
	} catch (Exception $e) {
		// TODO: What to do if error? block user from account creation?? Might be too late.
	}

	// If the municipality does not have a cat_id: create and add it.
	if (empty($institution->catid)) {
		//The parent ID of the category to create.
		$parent_id = 106;

		// Initialize a new category
		$category = JTable::getInstance('Category');
		$category->extension = 'com_contact';
		$category->title = $data['jos_emundus_users___nom_de_structure'];
		$category->description = '';
		$category->published = 1;
		$category->access = 1;
		$category->params = '{"target":"","image":""}';
		$category->metadata = '{"page_title":"","author":"","robots":""}';
		$category->language = '*';
		// Set the location in the tree
		$category->setLocation($parent_id, 'last-child');
		// Check to make sure our data is valid
		if (!$category->check()) {
			JError::raiseNotice(500, $category->getError());
			return false;
		}
		// Now store the category
		if (!$category->store(true)) {
			JError::raiseNotice(500, $category->getError());
			return false;
		}
		// Build the path for our category
		$category->rebuildPath($category->id);

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update($db->quoteName('em_municipalitees'))->set($db->quoteName('catid').' = '.$category->id)->where('id = '.$institution_id);
		$db->setQuery($query);
		try {
			$db->execute();
		} catch (Exception $e) {
			// TODO: What to do if error? block user from account creation?? Might be too late.
			JLog::add('Error adding catid to the new municipality at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
		}

		// Set the catid so that we can add it to the user.
		$institution->catid = $category->id;

	}

} else {
	// Future PHd students don't need to be linked to an institution.
	return true;
}

$query = $db->getQuery(true);
$columns = ['user', 'institution', 'profile', 'cat_id'];
$values = [$data['jos_emundus_users___user_id_raw'], $institution_id, $profile, $institution->catid];
$query->insert($db->quoteName('#__emundus_users_institutions'))->columns($db->quoteName($columns))->values(implode(',', $db->quote($values)));
$db->setQuery($query);

try {
	$db->execute();
} catch (Exception $e) {
	JLog::add('Error adding user link to institution in plugin/link_user_institution at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
	// TODO: What to do if error? block user from account creation?? Might be too late.
	return false;
}


// Add the cat_id to the user table as a university ID.
$query = $db->getQuery(true);
$query->update($db->quoteName('#__emundus_users'))->set($db->quoteName('university_id').' = '.$institution->catid)->where('user_id = '.$data['jos_emundus_users___user_id_raw']);
$db->setQuery($query);
try {
	$db->execute();
} catch (Exception $e) {
	// TODO: What to do if error? block user from account creation?? Might be too late.
	JLog::add('Error adding catid to the new municipality at query: '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
}
