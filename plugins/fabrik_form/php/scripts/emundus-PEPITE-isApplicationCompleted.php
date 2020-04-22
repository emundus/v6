<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: isApplicationCompleted.php 89 20014-11-13 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description V�rification avant envoie du dossier que le dossier est bien complet
 */

$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;
$itemid = $jinput->get('Itemid');
$db = JFactory::getDBO();

if ($jinput->get('view') == 'form') {
	 require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');
	 //require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');

	$user = JFactory::getSession()->get('emundusUser');
	
	//$application = new EmundusModelApplication;
	//$attachments = $application->getAttachmentsProgress($user->profile, $user->fnum);
	//$forms = $application->getFormsProgress($user->profile, $user->fnum);


	$forms = @EmundusHelperMenu::buildMenuQuery($user->profile);

    $nb = 0;
    $q = 1;
    $formLst = array();
    foreach ($forms as $form) {
    	if($form->db_table_name == 'jos_emundus_pepite_projet1'){
    		$query = 'SELECT q'.$q.' FROM '.$form->db_table_name.' WHERE user = '.$user->id.' AND fnum like '.$db->Quote($user->fnum);
	        $db->setQuery( $query );
	        $val = $db->loadResult(); 
	        $cpt = (count($val)>0)?1:0;
    		$q++;

    	}
    	else {
	        $query = 'SELECT count(*) FROM '.$form->db_table_name.' WHERE user = '.$user->id.' AND fnum like '.$db->Quote($user->fnum);
	        $db->setQuery( $query );
	        $cpt = $db->loadResult(); 
	    }

	    if ($cpt==1)
            $nb++;
        else 
            $formLst[] = $form->label;
   
    }
    return  @floor(100*$nb/count($forms));

	if($forms < 100 ){
		$mainframe->redirect( "index.php?option=com_emundus&view=checklist&Itemid=".$itemid,JText::_('INCOMPLETE_APPLICATION'));
	}
}

?>