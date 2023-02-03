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

$mainframe 	= JFactory::getApplication();
$jinput 	= $mainframe->input;
$itemid 	= $jinput->get('Itemid');

if ($jinput->get('view') == 'form') {
	 require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');
	 require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
	 require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
	 require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
     require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');

	$user = JFactory::getSession()->get('emundusUser');

	$params	= JComponentHelper::getParams('com_emundus');
    $scholarship_document_id 	= $params->get('scholarship_document_id', NULL);
	$application_fee = $params->get('application_fee', 0);

    $m_profile = new EmundusModelProfile;
    $application_fee = (!empty($application_fee) && !empty($m_profile->getHikashopMenu($user->profile)));

	$m_application = new EmundusModelApplication;
    $m_emails = new EmundusModelEmails;
	//$validations = $m_application->checkFabrikValidations($user->fnum, true, $itemid);
	$attachments = $m_application->getAttachmentsProgress($user->fnum);
	$forms = $m_application->getFormsProgress($user->fnum);

	if ($attachments < 100 || $forms < 100) {
		$mainframe->redirect( "index.php?option=com_emundus&view=checklist&Itemid=".$itemid, JText::_('INCOMPLETE_APPLICATION'));
	}

	if ($application_fee) {
		if($params->get('hikashop_session')) {
			// check if there is not another cart open
			$hikashop_user = JFactory::getSession()->get('emundusPayment');
			if (!empty($hikashop_user->fnum) && $hikashop_user->fnum != $user->fnum) {
				$user->fnum = $hikashop_user->fnum;
				JFactory::getSession()->set('emundusUser', $user);

				$mainframe->enqueueMessage(JText::_('ANOTHER_HIKASHOP_SESSION_OPENED'), 'error');
				$mainframe->redirect('/');
			}
		}

        $m_files = new EmundusModelFiles;
        $fnumInfos = $m_files->getFnumInfos($user->fnum);

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

			$pay_scholarship = $params->get('pay_scholarship', 0);

			// If he hasn't, no discount for him. If he has, exit to regular procedure.
			if (!empty($uploaded_document) && !$pay_scholarship) {
				return;
			}

			if (empty($uploaded_document)) {
				$scholarship_document_id = null;
            } else if (!empty($pay_scholarship)  && empty($m_application->getHikashopOrder($fnumInfos))) {
				$scholarship_product = $params->get('scholarship_product', 0);
				if (!empty($scholarship_product)) {
					$return_url = $m_application->getHikashopCheckoutUrl($user->profile);
					$return_url = preg_replace('/&product_id=[0-9]+/', "&product_id=$scholarship_product", $return_url);
					$checkout_url = 'index.php?option=com_hikashop&ctrl=product&task=cleancart&return_url=' . urlencode(base64_encode($return_url));
					$mainframe->redirect($checkout_url);
				}
			}
		}

		// This allows users who have started a bank transfer or cheque to go through even if it has not been marked as received yet.
		$accept_other_payments = $params->get('accept_other_payments', 0);

		if (count($fnumInfos) > 0) {
            $checkout_url = $m_application->getHikashopCheckoutUrl($user->profile . $scholarship_document_id);
            if(strpos($checkout_url,'${') !== false) {
                $checkout_url = $m_emails->setTagsFabrik($checkout_url, [$user->fnum], true);
            }
			// If $accept_other_payments is 2 : that means we do not redirect to the payment page.
			if ($accept_other_payments != 2 && empty($m_application->getHikashopOrder($fnumInfos)) && $attachments >= 100 && $forms >= 100) {
				// Profile number and document ID are concatenated, this is equal to the menu corresponding to the free option (or the paid option in the case of document_id = NULL)
				$checkout_url = 'index.php?option=com_hikashop&ctrl=product&task=cleancart&return_url=' . urlencode(base64_encode($checkout_url));
				$mainframe->redirect($checkout_url);
			}
		} else {
			$mainframe->redirect('index.php');
		}
	}
}

?>
