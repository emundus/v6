<?php
defined( '_JEXEC' ) or die();
/**
 * @version 3: isApplicationSent.php 89 2014-09-03 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2014 D�cision Publique. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description V�rification de l'autorisation de mettre � jour le formulaire
 */
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');

$user = JFactory::getSession()->get('emundusUser');
$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;
$eMConfig = JComponentHelper::getParams('com_emundus');
$can_edit_until_deadline = $eMConfig->get('can_edit_until_deadline', '0');
$id_applicants 			 = $eMConfig->get('id_applicants', '0');
$applicants 			 = explode(',',$id_applicants);

$fnum = $jinput->get('rowid', null);

if(!EmundusHelperAccess::asApplicantAccessLevel($user->id)) {
    if ($jinput->get('tmpl')=='component') {
        JHTML::stylesheet( JURI::base().'media/com_fabrik/css/fabrik.css' );
        JHTML::stylesheet( JURI::base().'media/system/css/modal.css' );
        $doc = JFactory::getDocument();
        $doc->addScript("media/com_fabrik/js/window-min.js");
        $doc->addScript("media/com_fabrik/js/lib/form_placeholder/Form.Placeholder.js");
        $doc->addScript("templates/rt_afterburner2/js/rokmediaqueries.js");
    }

    //echo "<script>$('rt-header').remove(); $('rt-footer').remove(); $('gf-menu-toggle').remove();</script>";
} else{
    if (($user->fnum != $fnum && $fnum != -1) && !empty($fnum)) {
        JError::raiseNotice('ERROR', JText::_('ERROR...'));
        $mainframe->redirect("index.php");
    }
}

//$registered = $db->loadResult();
if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)){
    $sid = $jinput->get('sid', null, 'ALNUM');
//	$student = JUser::getInstance($sid);
//	echo '<a href="index.php?option=com_emundus&view=application&sid='.$student_id.'"><h1>'.$student->name.'</h1></a>';
    echo !empty($rowid)?'<h4 style="text-align:right">#'.$fnum.'</h4>':'';

}
else {
    if (empty($user->fnum) && !isset($user->fnum) && EmundusHelperAccess::isApplicant($user->id))
        $mainframe->redirect("index.php?option=com_emundus&view=renew_application");

    if ($jinput->get('view') == 'form' && empty($fnum) && !isset($fnum)) {
        $itemid = $jinput->get('Itemid');
        // Si l'application Form a été envoyée par le candidat : ON LAISSSE LA POSSIBILITE DE MODIFIER
        if($user->candidature_posted > 0 && $user->candidature_incomplete == 0 && $can_edit_until_deadline == 0) {
            $mainframe->redirect("index.php?option=com_fabrik&view=form&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum);
        } elseif(strtotime(date("Y-m-d H:m:i")) > strtotime($user->end_date) && !in_array($user->id, $applicants) ) {
            JError::raiseNotice('CANDIDATURE_PERIOD_TEXT', utf8_encode(JText::sprintf('PERIOD', strftime("%d/%m/%Y %H:%M", strtotime($user->start_date) ), strftime("%d/%m/%Y %H:%M", strtotime($user->end_date) ))));
            $mainframe->redirect("index.php?option=com_fabrik&view=form&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum);
        } else {
            if (empty($fnum) && !isset($fnum)) {
                // redirection vers l'enregistrement du dossier
                $mainframe->redirect("index.php?option=com_fabrik&view=form&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=fnum&rowid=".$user->fnum);
                //$mainframe->redirect("index.php?option=com_fabrik&view=form&formid=".$jinput->get('formid')."&Itemid=".$itemid."&usekey=user&rowid=-1");
            }
        }
    } else {
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__emundus_campaign_candidature WHERE fnum like '.$db->Quote($fnum);
        try
        {
            $db->setQuery($query);
            $file =  $db->loadAssoc();
        }
        catch(Exception $e)
        {
            throw $e;
        }

        if ($file['submitted'] == 1) {
            $jdate = new JDate($file['date_submitted']);
            JError::raiseNotice('FILE_SUBMITTED', 'Votre dossier a été reçu le '.$jdate->format(JText::_('DATE_FORMAT_LC2')));
            $mainframe->redirect("/");
        }
    }
}
?>