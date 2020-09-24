<?php
defined('_JEXEC') or die();
/**
 * @version 1: new_municipality.php 89 2018-07-09 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2018 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Creation d'une nouvelle municipalité dans le registre com_categories Joomla.
 */

$extension = 'com_contact';
$id = $data['em_laboratoire___id'];
$title = $data['em_laboratoire___name'];


$db = JFactory::getDbo();
$query = $db->getQuery(true);
$query->select($db->quoteName('catid'))->from($db->quoteName('em_laboratoire'))->where('id = '.$id);
$db->setQuery($query);
try {
	if (!empty($db->loadResult()))
		return false;
} catch (Exception $e) {
	JLog::add('Error adding catid to the new municipality at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
	return false;
}


// Category ID 107 = lab
$parent_id = 107;

// Initialize a new category
$category = JTable::getInstance('Category');
$category->extension = $extension;
$category->title = $title;
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

$query = $db->getQuery(true);
$query->update($db->quoteName('em_laboratoire'))->set($db->quoteName('catid').' = '.$category->id)->where('id = '.$id);
$db->setQuery($query);
try {
	$db->execute();
} catch (Exception $e) {
	JLog::add('Error adding catid to the new municipality at query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
}