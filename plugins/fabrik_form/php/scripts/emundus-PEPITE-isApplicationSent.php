<?php
defined( '_JEXEC' ) or die();
/**
 * @version 3: isApplicationSent.php 89 2014-09-03 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2014 Décision Publique. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Vérification de l'autorisation de mettre à jour le formulaire
 */
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');

$user = JFactory::getUser();
$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;
$eMConfig = JComponentHelper::getParams('com_emundus');
$can_edit_until_deadline = $eMConfig->get('can_edit_until_deadline', '0');
$id_applicants 			 = $eMConfig->get('id_applicants', '0');
$applicants 			 = explode(',',$id_applicants);
$fnum = JRequest::getVar('rowid', null, 'get');

if(!EmundusHelperAccess::isApplicant($user->id)){
	echo "<script>$('rt-header').remove(); $('rt-footer').remove();</script>";
} else {
	$db = JFactory::getDBO();
	$query = 'SELECT * FROM #__emundus_campaign_candidature WHERE fnum like '.$db->Quote($fnum);
	try
		{
			if(!empty($fnum)) {
				$db->setQuery($query);
				$file =  $db->loadResultArray();
			} else return '';
		}
	catch(Exception $e)
	{
		throw $e;
	}
	if ($file['submitted'] == 1) {
		die('Votre dossier a été reçu le '.$file['date_submitted']);
	}
}


//$registered = $db->loadResult();
if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)){
	$rowid = JRequest::getVar('rowid', null, 'get');
	$sid = JRequest::getVar('sid', null, 'get');

	echo !empty($rowid)?'<h4 style="text-align:right">#'.$rowid.'</h4>':'';

}

if (empty($user->fnum) && !isset($user->fnum) && EmundusHelperAccess::isApplicant($user->id))
		$mainframe->redirect("index.php?option=com_emundus&view=renew_application");

if ($jinput->get('view') == 'form' && empty($fnum) && !isset($fnum)) {
	$itemid = $jinput->get('Itemid');
//var_dump($user); die();	
	// Si l'application Form a été envoyée par le candidat : affichage vue details
	if($user->candidature_posted > 0  && $can_edit_until_deadline == 0) {
		$mainframe->redirect("index.php?option=com_fabrik&view=details&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum);
	} elseif(strtotime(date("Y-m-d H:m:i")) > strtotime($user->end_date) && !in_array($user->id, $applicants) ) {
		JError::raiseNotice('CANDIDATURE_PERIOD_TEXT', utf8_encode(JText::sprintf('PERIOD', strftime("%d/%m/%Y %H:%M", strtotime($user->start_date) ), strftime("%d/%m/%Y %H:%M", strtotime($user->end_date) ))));
		$mainframe->redirect("index.php?option=com_fabrik&view=details&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum);
	} else {
		if (empty($fnum) && !isset($fnum)) {
			// redirection vers l'enregistrement du dossier
			$mainframe->redirect("index.php?option=com_fabrik&view=form&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum);
			//$mainframe->redirect("index.php?option=com_fabrik&view=form&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=user&rowid=-1");
		}
	}
}

?>