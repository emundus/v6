<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: isApplicationCompleted.php 89 20014-11-13 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2008 Décision Publique. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Vérification avant envoie du dossier que le dossier est bien complet
 */

$mainframe = JFactory::getApplication();
$jinput = $mainframe->input;
$itemid = $jinput->get('Itemid');

if ($jinput->get('view') == 'form') {
	 require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');
	 require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
	 require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
	 require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');

	$user = JFactory::getUser();

	$params = JComponentHelper::getParams('com_emundus');
	$application_fee  = $params->get('application_fee', 0);

	$application = new EmundusModelApplication;
	
	if ($application_fee == 1) {
		$fnumInfos = EmundusModelFiles::getFnumInfos($user->fnum);
		if ($application_fee == 1) {
			$paid = count($application->getHikashopOrder($fnumInfos))>0?1:0;

			if (!$paid)
				$mainframe->redirect( JRoute::_("index.php?option=com_hikashop&ctrl=product&task=updatecart&quantity=1&checkout=1&product_id=1"));
		}
	}

	
	$attachments = $application->getAttachmentsProgress($user->id, $user->profile, $user->fnum);
	$forms = $application->getFormsProgress($user->id, $user->profile, $user->fnum);

	if($attachments < 100 || $forms < 100 ){
		$mainframe->redirect( JRoute::_("index.php?option=com_emundus&view=checklist&Itemid=".$itemid), JText::_('INCOMPLETE_APPLICATION'));
	}
}

?>