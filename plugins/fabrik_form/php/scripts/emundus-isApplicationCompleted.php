<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: isApplicationCompleted.php 89 2017-06-02 Benjamin Rivalland
 * @package Fabrik
 * @copyright Copyright (C) 2017 eMundus. All rights reserved.
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

if ($jinput->get('view') == 'form') {
	 require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');
	 require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
	 require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');

	$user = JFactory::getSession()->get('emundusUser');

	$params = JComponentHelper::getParams('com_emundus');
	$application_fee = $params->get('application_fee', 0);
	$scholarship_document_id = $params->gety('scholarship_document_id', NULL);

	$m_application = new EmundusModelApplication;
	$attachments = $m_application->getAttachmentsProgress($user->id, $user->profile, $user->fnum);
	$forms = $m_application->getFormsProgress($user->id, $user->profile, $user->fnum);

	// If students with a scholarship have a different fee.
	// The form ID will be appended to the URL, taking him to a different checkout page.
	if (isset($scholarship_document_id)) {

		$db = JFactory::getDbo();

		// See if applicant has uploaded the required scolarship form.
		try {

			$query = 'SELECT count(id) FROM #__emundus_uploads
						WHERE attachment_id = '.$scholarship_document_id.'
						AND fnum LIKE '.$db->Quote($user->fnum);

			$db->setQuery($query);
			$uploaded_document = $db->loadResult();

		} catch (Exception $e) {
			JLog::Add('Error in plugin/isApplicationCompleted at SQL query : '.$query, Jlog::ERROR, 'plugins');
		}

		// If he hasn't, no discount for him.
		if ($uploaded_document == 0)
			$scholarship_document_id == NULL;

	}

	if ($application_fee == 1) {
		$fnumInfos = EmundusModelFiles::getFnumInfos($user->fnum);
		if (count($fnumInfos) > 0) {
			$paid = count($m_application->getHikashopOrder($fnumInfos))>0?1:0;

			if (!$paid && $attachments >= 100 && $forms >= 100) {
				$checkout_url = 'index.php?option=com_hikashop&ctrl=product&task=cleancart&return_url='. urlencode(base64_encode($m_application->getHikashopCheckoutUrl($user->profile.$scholarship_document_id)));
				$mainframe->redirect(JRoute::_($checkout_url));
			}
		} else {
			$mainframe->redirect('index.php');
		}

	}

	if ($attachments < 100 || $forms < 100)
		$mainframe->redirect( "index.php?option=com_emundus&view=checklist&Itemid=".$itemid, JText::_('INCOMPLETE_APPLICATION'));
}

?>