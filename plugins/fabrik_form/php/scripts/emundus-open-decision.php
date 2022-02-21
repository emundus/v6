<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: open-decision.php 2018-06-18 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2018 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Log the opening of the decision form and display the student's name on the top of the form.
 */

require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'logs.php');


$jinput = JFactory::getApplication()->input;
$student_id = $jinput->get->get('student_id', null);
$fnum = $jinput->get('jos_emundus_final_grade___fnum', '')[0];


// Log decision opening action.
EmundusModelLogs::log(JFactory::getUser()->id, $student_id, $fnum, 29, 'r', 'COM_EMUNDUS_DECISION_READ');


$student = JUser::getInstance($student_id);
echo '<h1>'.$student->name.'</h1>';