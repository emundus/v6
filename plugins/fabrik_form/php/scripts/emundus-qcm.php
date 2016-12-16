<?php
defined( '_JEXEC' ) or die();
/**
 * @version 3: qcm.php 89 2016-12-07 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2016 eMundus. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Verification de la saisie réalisé du QCM + génération de la sélection des questions
 */
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');

jimport('joomla.log.log');
JLog::addLogger(
    array(
        // Sets file name
        'text_file' => 'com_emundus.qcm.php'
    ),
    JLog::ALL,
    array('com_emundus')
);

$user = JFactory::getUser();
$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;

$eMConfig = JComponentHelper::getParams('com_emundus');
$id_applicants 			 = $eMConfig->get('id_applicants', '0');
$applicants 			 = explode(',',$id_applicants);

$fnum = $jinput->get('rowid', null);
$itemid = $jinput->get('Itemid'); 
$reload = $jinput->get('rq', 0); 

if (empty($formModel->getRowId())) {
	try
	{
		$db 		= JFactory::getDBO();
		$purl = '';
		$i = 1;
		// Section A - FR
		$query = 'SELECT question_code FROM data_qcm WHERE section like "A" AND sub_section like "FR" group by num ORDER BY RAND() LIMIT 2';
		$db->setQuery( $query );
		$res = $db->loadColumn();
		foreach ($res as $key => $value) {
			$purl .= '&jos_emundus_qcm___a'.$i.'='.$value;
			$i++;
		}
		// Section A - UK
		$query = 'SELECT question_code FROM data_qcm WHERE section like "A" AND sub_section like "UK" group by num ORDER BY RAND() LIMIT 2';
		$db->setQuery( $query );
		$res = $db->loadColumn();
		foreach ($res as $key => $value) {
			$purl .= '&jos_emundus_qcm___a'.$i.'='.$value;
			$i++;
		}
		// Section A - SP
		$query = 'SELECT question_code FROM data_qcm WHERE section like "A" AND sub_section like "SP" group by num ORDER BY RAND() LIMIT 2';
		$db->setQuery( $query );
		$res = $db->loadColumn();
		foreach ($res as $key => $value) {
			$purl .= '&jos_emundus_qcm___a'.$i.'='.$value;
			$i++;
		}

		// Section B
		$i = 1;
		$query = 'SELECT question_code FROM data_qcm WHERE section like "B" group by num ORDER BY RAND() LIMIT 6';
		$db->setQuery( $query );
		$res = $db->loadColumn();
		foreach ($res as $key => $value) {
			$purl .= '&jos_emundus_qcm___b'.$i.'='.$value;
			$i++;
		}

		// Section C
		$i = 1;
		$query = 'SELECT question_code FROM data_qcm WHERE section like "C" group by num ORDER BY RAND() LIMIT 6';
		$db->setQuery( $query );
		$res = $db->loadColumn();
		foreach ($res as $key => $value) {
			$purl .= '&jos_emundus_qcm___c'.$i.'='.$value;
			$i++;
		}

		// Section D
		$i = 1;
		$query = 'SELECT question_code FROM data_qcm WHERE section like "D" group by num ORDER BY RAND() LIMIT 6';
		$db->setQuery( $query );
		$res = $db->loadColumn();
		foreach ($res as $key => $value) {
			$purl .= '&jos_emundus_qcm___d'.$i.'='.$value;
			$i++;
		}

		// Section E
		$i = 1;
		$query = 'SELECT question_code FROM data_qcm WHERE section like "E" group by num ORDER BY RAND() LIMIT 6';
		$db->setQuery( $query );
		$res = $db->loadColumn();
		foreach ($res as $key => $value) {
			$purl .= '&jos_emundus_qcm___e'.$i.'='.$value;
			$i++;
		}

		// Section F
		$i = 1;
		$query = 'SELECT question_code FROM data_qcm WHERE section like "F" group by num ORDER BY RAND() LIMIT 6';
		$db->setQuery( $query );
		$res = $db->loadColumn();
		foreach ($res as $key => $value) {
			$purl .= '&jos_emundus_qcm___f'.$i.'='.$value;
			$i++;
		}


		if ($reload == 0) {
			$reload = 1;
			$mainframe->redirect("index.php?option=com_fabrik&view=form&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum."&rq=".$reload.$purl);
		}
		
	}
	catch(Exception $e)
	{
	    $error = JUri::getInstance().' :: USER ID : '.$user->id.' -> '.$e->getMessage();
	    JLog::add($error, JLog::ERROR, 'com_emundus');
	}
} 
else {
	if ($reload == 0) {
		$reload = 2;
		JFactory::getApplication()->enqueueMessage(JText::_('QCM_ALREADY_DONE'), 'error');
		$mainframe->redirect("index.php?option=com_fabrik&view=details&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum."&rq=".$reload);
	}
}

?>