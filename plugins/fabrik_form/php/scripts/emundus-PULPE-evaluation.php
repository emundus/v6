<?php
defined('_JEXEC') or die();
/**
 * @version 1: PULPE-evaluation.php 89 2018-12-05 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2018 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Updates student's status based on a dropdown in the form.
 */

jimport('joomla.log.log');
JLog::addLogger(['text_file' => 'com_emundus.evaluation.php'], JLog::ALL, ['com_emundus']);

$db	= JFactory::getDbo();
$jinput = JFactory::getApplication()->input;
$fnum = $jinput->post->get('jos_emundus_evaluations___fnum');
$status = $jinput->post->get('jos_emundus_evaluations___status')[0];


try {

	// Update status to the one selected in the form.
	$query = $db->getQuery(true);
	$query->update($db->quoteName('#__emundus_campaign_candidature'))
			->set($db->quoteName('status').' = '.$status)
			->where($db->quoteName('fnum').' LIKE '.$db->quote($fnum));
	$db->setQuery($query);
	$db->execute();

} catch (Exception $e) {
	JLog::add('Error setting status in plugin/PULPE-evaluation at query : '.preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus');
}
