<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: evaluation_SOPRO.php 89 2017-11-16 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2018 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Mise à jours du statut de la candidature à une campagne
 */


$db = JFactory::getDbo();

$fnum = $fabrikFormData['fnum_raw'];
$step = $fabrikFormData['evaluation_raw'][0];

// The status step is the value of the evaluation element dropdown.
$query = 'UPDATE #__emundus_campaign_candidature SET status='.$step.' WHERE fnum like '. $db->Quote($fnum);

try {

    $db->setQuery($query);
    $db->execute();

} catch(Exception $e) {
    JLog::add('Error updating status in plugin/evaluation_SOPRO at query: '.$query, JLog::ERROR, 'com_emundus');
}