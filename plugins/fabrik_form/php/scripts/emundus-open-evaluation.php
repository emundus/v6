<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: open_evaluation.php 89 2018-06-15 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2018 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Check if the evaluation being opened is the user's own or not.
 */

require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');


$app = JFactory::getApplication();
$db = JFactory::getDBO();
$user = JFactory::getUser();
$jinput = $app->input;

$r = $jinput->get('r', 0);
$formid = $jinput->get('formid', 284);
$fnum = $jinput->get('jos_emundus_evaluations___fnum', '')[0];

// Log evaluation opening action.
EmundusModelLogs::log($user->id, (int)substr($fnum, -7), $fnum, 5, 'r', 'COM_EMUNDUS_ACCESS_EVALUATION_READ');

// Check if we are opening an evaluation created by the user.
$query = $db->getQuery(true);
$query->select($db->quoteName('id'))
	->from($db->quoteName('jos_emundus_evaluations'))
	->where($db->quoteName('user').'='.$user->id.' AND '.$db->quoteName('fnum')." LIKE '{jos_emundus_evaluations___fnum}'");
$db->setQuery($query);
$id = $db->loadResult();

// If we are opening an evaluation that already exists or not in readonly, redirect to the correct form.
if ($id > 0 && $r != 1) {
	$app->redirect('index.php?option=com_fabrik&c=form&view=form&formid='.$formid.'&tmpl=component&iframe=1&rowid='.$id.'&r=1');
}


