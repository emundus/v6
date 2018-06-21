<?php
defined('_JEXEC') or die();
/**
 * @version 1: open_admission.php 89 2018-06-15 Hugo Moracchini
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

$jinput = JFactory::getApplication()->input;
$fnum = $jinput->get('jos_emundus_admission___fnum', '')[0];

// Log admission opening action.
EmundusModelLogs::log(JFactory::getUser()->id, (int)substr($fnum, -7), $fnum, 32, 'r', 'COM_EMUNDUS_LOGS_OPEN_ADMISSION');