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

    $user = JFactory::getSession()->get('emundusUser');

    $params 					= JComponentHelper::getParams('com_emundus');
    $scholarship_document_id 	= $params->get('scholarship_document_id', NULL);
    $application_fee 			= $params->get('application_fee', 0);

    $m_profile = new EmundusModelProfile;
    $application_fee  		= (!empty($application_fee) && !empty($m_profile->getHikashopMenu($user->profile)));

    $m_application 	= new EmundusModelApplication;
    $attachments 	= $m_application->getAttachmentsProgress($user->fnum);
    $forms 			= $m_application->getFormsProgress($user->fnum);

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
        }

    }

    if ($application_fee) {

        $m_files = new EmundusModelFiles;
        $fnumInfos = $m_files->getFnumInfos($user->fnum);

        // This allows users who have started a bank transfer or cheque to go through even if it has not been marked as received yet.
        $accept_other_payments = $params->get('accept_other_payments', 0);

        $order = $m_application->getHikashopCartOrder($fnumInfos,false,true);

        if (count($fnumInfos) > 0) {
            // If $accept_other_payments is 2 : that means we do not redirect to the payment page.
            if ($accept_other_payments != 2 && empty($order) && $attachments >= 100 && $forms >= 100) {
                $url_checkout = $m_application->getHikashopCheckoutUrl($user->profile . $scholarship_document_id);
                $url_cart = $m_application->getHikashopCartUrl($user->profile);
                if(!empty($url_cart)){
                    // Profile number and document ID are concatenated, this is equal to the menu corresponding to the free option (or the paid option in the case of document_id = NULL)
                    $checkout_url = $url_cart;
                } else{
                    // Profile number and document ID are concatenated, this is equal to the menu corresponding to the free option (or the paid option in the case of document_id = NULL)
                    $checkout_url = 'index.php?option=com_hikashop&ctrl=product&task=cleancart&return_url=' . urlencode(base64_encode($url_checkout));
                }
                $mainframe->redirect($checkout_url);
            }
        } else {
            $mainframe->redirect('index.php');
        }

    }

    if ($attachments < 100 || $forms < 100)
        $mainframe->redirect( "index.php?option=com_emundus&view=checklist&Itemid=".$itemid, JText::_('INCOMPLETE_APPLICATION'));
}

?>
