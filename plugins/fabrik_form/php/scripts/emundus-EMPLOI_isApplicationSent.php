<?php
defined( '_JEXEC' ) or die();
/**
 * @version 3: isApplicationSent.php 89 2019-08-02 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2019 eMundus. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Vérification de l'autorisation de mettre à jour le formulaire pour le programme EMploi étudiant
 */
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');

$user = JFactory::getSession()->get('emundusUser');
$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;
$eMConfig = JComponentHelper::getParams('com_emundus');
$can_edit_until_deadline = 0;
$id_applicants = $eMConfig->get('id_applicants', '0');
$applicants = explode(',',$id_applicants);

$fnum = $jinput->get('rowid', null);

if (EmundusHelperAccess::asApplicantAccessLevel($user->id) && !EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
	if (($user->fnum != $fnum && $fnum != -1) && !empty($fnum)) {
		JError::raiseNotice('ERROR', JText::_('ERROR...'));
		$mainframe->redirect("index.php");
	}
} else {
	if ($jinput->get('tmpl') == 'component') {
		JHTML::stylesheet(JURI::base().'media/com_fabrik/css/fabrik.css');
		JHTML::stylesheet(JURI::base().'media/system/css/modal.css');
		$doc = JFactory::getDocument();
		$doc->addScript("media/com_fabrik/js/window-min.js");
		$doc->addScript("media/com_fabrik/js/lib/form_placeholder/Form.Placeholder.js");
		$doc->addScript("templates/rt_afterburner2/js/rokmediaqueries.js");
	}
}


if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
    $sid = $jinput->get('sid', null, 'ALNUM');
    echo !empty($rowid)?'<h4 style="text-align:right">#'.$fnum.'</h4>':'';
} else {
	if ($jinput->get('view') == 'form' && empty($fnum) && !isset($fnum)) {
		$itemid = $jinput->get('Itemid');
		// Si l'application Form a été envoyée par le candidat : affichage vue details
		if ($user->candidature_posted > 0  && $can_edit_until_deadline == 0) {
			$mainframe->redirect("index.php?option=com_fabrik&view=details&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum);
		} elseif (strtotime(date("Y-m-d H:m:i")) > strtotime($user->end_date) && !in_array($user->id, $applicants) ) {
			JError::raiseNotice('CANDIDATURE_PERIOD_TEXT', utf8_encode(JText::sprintf('PERIOD', strftime("%d/%m/%Y %H:%M", strtotime($user->start_date)), strftime("%d/%m/%Y %H:%M", strtotime($user->end_date)))));
			$mainframe->redirect("index.php?option=com_fabrik&view=details&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum);
		} else {
			if (empty($fnum) && !isset($fnum)) {
				// redirection vers l'enregistrement du dossier
				$mainframe->redirect("index.php?option=com_fabrik&view=form&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum);
			}
		}
	}
}
