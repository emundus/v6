<?php
defined( '_JEXEC' ) or die();
/**
 * @version 3: isApplicationSent.php 89 2015-02-26 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2014 D�cision Publique. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Vérification de l'autorisation de mettre à jour le formulaire pour le programme EMploi étudiant
 */
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');

$user = JFactory::getUser();
$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;
$eMConfig = JComponentHelper::getParams('com_emundus');
$can_edit_until_deadline = 0;
$id_applicants 			 = $eMConfig->get('id_applicants', '0');
$applicants 			 = explode(',',$id_applicants);

$fnum = $mainframe->input->get('rowid', null, 'ALNUM');

if(!EmundusHelperAccess::asApplicantAccessLevel($user->id)) {
		echo "<script>$('rt-header').remove(); $('rt-footer').remove(); $('gf-menu-toggle').remove(); $('rt-secondmenu').remove();</script>";
    }
} else{
    if ($user->fnum != $fnum && !empty($fnum)) {
        JError::raiseNotice('ERROR', JText::_('ERROR'));
        $mainframe->redirect("index.php");
    }
}

//$registered = $db->loadResult();
if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)){
    $sid = $mainframe->input->get('sid', null, 'ALNUM');

    echo !empty($rowid)?'<h4 style="text-align:right">#'.$fnum.'</h4>':'';

}
else {
	if ($jinput->get('view') == 'form' && empty($fnum) && !isset($fnum)) {
		$itemid = $jinput->get('Itemid');
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
			}
		}
	}
}
?>