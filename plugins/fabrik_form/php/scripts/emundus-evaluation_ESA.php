<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: evaluation_ESA.php 89 2017-11-16 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2017 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Mise à jours du statut de la candidature à une campagne
 */

$db	= JFactory::getDbo();

// To determine if the candidate was interviewed, we check if his evaluation form has been filled out with a grade for his oral.
$interviewed = !empty(@$_REQUEST['jos_emundus_evaluations___oral'][0]);
$fnum = $_REQUEST['jos_emundus_evaluations___fnum'][0];

if ($interviewed) {

	try {

		$query = 'UPDATE #__emundus_campaign_candidature
					SET status = 5
					WHERE fnum LIKE '.$db->Quote($fnum);
		$db->setQuery($query);
		$db->execute();

	} catch (Exception $e) {
		JLog::add('Error in plugin evaluation-ESA on query : '.$query, JLog::ERROR, 'com_emundus');
	}

} else {

	try {

		$query = 'UPDATE #__emundus_campaign_candidature
					SET status = 3
					WHERE fnum LIKE '.$db->Quote($fnum);
		$db->setQuery($query);
		$db->execute();

	} catch (Exception $e) {
		JLog::add('Error in plugin evaluation-ESA on query : '.$query, JLog::ERROR, 'com_emundus');
	}

}
?>