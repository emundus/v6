<?php
/**
 * @version		$Id: mod_emundusflow.php
 * @package		Joomla
 * @copyright	Copyright (C) 2016 emundus.fr. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

$user = JFactory::getSession()->get('emundusUser');

if (isset($user->fnum) && !empty($user->fnum)) {

    require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');
    require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');
    require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'checklist.php');
    require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');
    require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
    require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'profile.php');
    require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
    require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');

    // Load Joomla framework classes
    $document = JFactory::getDocument();
    $jinput = JFactory::getApplication()->input;
    $db = JFactory::getDBO();

    // Parameters
    $show_programme = $params->get('show_programme', 1);
    $show_back_button = $params->get('show_back_button', 1);
    $show_document_step = $params->get('show_document_step', 1);
    $show_form_step = $params->get('show_form_step', 1);
    $show_status = $params->get('show_status', 1);
    $show_hikashop = $params->get('show_hikashop', 1);
    $show_deadline  = $params->get('show_deadline', 0);
    $admission  = $params->get('admission', 0);
    $layout = $params->get('layout', 'default');
    $offset = JFactory::getConfig()->get('offset');
    $home_link = EmundusHelperMenu::getHomepageLink($params->get('home_link', 'index.php'));
    $add_to_cart_icon = $params->get('add_to_cart_icon', 'large add to cart icon');
    $scholarship_icon = $params->get('scholarship_icon', 'large student icon');
    $file_tags = JText::_($params->get('tags', ''));

    // eMundus parameters
    $params_emundus = JComponentHelper::getParams('com_emundus');
    $applicant_can_renew = $params_emundus->get('applicant_can_renew', 0);
    $application_fee = $params_emundus->get('application_fee', 0);
    $scholarship_document = $params_emundus->get('scholarship_document_id', NULL);
    $id_profiles = $params_emundus->get('id_profiles', '0');
    $id_profiles = explode(',', $id_profiles);


    if($layout != '_:tchooz') {
	    require_once (JPATH_SITE.'/components/com_emundus/helpers/cache.php');
	    $hash = EmundusHelperCache::getCurrentGitHash();

        $document->addStyleSheet("modules/mod_emundusflow/style/emundus.css?".$hash );
    }

    $header_class = $params->get('header_class', '');
    if (!empty($header_class)) {
        $document->addStyleSheet("media/com_emundus/lib/Semantic-UI-CSS-master/components/site.".$header_class.".css" );
    }

    // Jinput
    $option = $jinput->get('option');
    $view = $jinput->get('view');
    if (EmundusHelperAccess::asAccessAction(1, 'c')) {
        $applicant_can_renew = 1;
    } else {
        foreach ($user->emProfiles as $profile) {
            if (in_array($profile->id, $id_profiles)) {
                $applicant_can_renew = 1;
                break;
            }
        }
    }

    // Models
    $m_checklist = new EmundusModelChecklist();
    $m_application = new EmundusModelApplication();
    $m_files = new EmundusModelFiles();
    $m_profile = new EmundusModelProfile();
    $m_emails = new EmundusModelEmails();
    $m_campaign = new EmundusModelCampaign();

    $current_application = $m_application->getApplication($user->fnum);

    $campaign_name = $current_application->label;

    if($layout != '_:tchooz') {
        $application_fee = (!empty($application_fee) && !empty($m_profile->getHikashopMenu($user->profile)));
        $paid = null;

        if ($application_fee) {
            $fnumInfos = $m_files->getFnumInfos($user->fnum);
            $order = $m_application->getHikashopOrder($fnumInfos);
            $paid = !empty($order);
            $cart = $m_application->getHikashopCartUrl($user->profile);
            $cartorder = null;

            if (!$paid || !empty($cart)) {

                // If students with a scholarship have a different fee.
                // The form ID will be appended to the URL, taking him to a different checkout page.
                if (isset($scholarship_document)) {

                    // See if applicant has uploaded the required scolarship form.
                    try {

                        $query = 'SELECT count(id) FROM #__emundus_uploads
								WHERE attachment_id = ' . $scholarship_document . '
								AND fnum LIKE ' . $db->Quote($user->fnum);

                        $db->setQuery($query);
                        $uploaded_document = $db->loadResult();

                    } catch (Exception $e) {
                        JLog::Add('Error in plugin/isApplicationCompleted at SQL query : ' . $query, Jlog::ERROR, 'plugins');
                    }

                    // If he hasn't, no discount for him.
                    if ($uploaded_document == 0) {
                        $scholarship_document = NULL;
                    } else {
                        $scholarship = true;
                    }

                }
                if (!empty($cart)) {
                    $cartorder = $m_application->getHikashopCart($fnumInfos);
                    $checkout_url = 'cart' . $user->profile;
                } elseif (!$paid) {
                    $orderCancelled = false;

                    $checkout_url = $m_application->getHikashopCheckoutUrl($user->profile . $scholarship_document);
                    if (strpos($checkout_url, '${') !== false) {
                        $checkout_url = $m_emails->setTagsFabrik($checkout_url, [$user->fnum]);
                    }
                    $checkout_url = 'index.php?option=com_hikashop&ctrl=product&task=cleancart&return_url=' . urlencode(base64_encode($checkout_url)) . '&usekey=fnum&rowid=' . $user->fnum;

                    $cancelled_orders = $m_application->getHikashopOrder($fnumInfos, true);

                    if (!empty($cancelled_orders)) {
                        $orderCancelled = true;
                    }
                }

            } else {
                $checkout_url = 'index.php';
            }
        }

        $attachments = $m_application->getAttachmentsProgress($user->fnum);
        $attachment_list = !empty($m_profile->getAttachments($user->profile, true));

        $forms = $m_application->getFormsProgress($user->fnum);
        $form_list = !empty($m_checklist->getFormsList());

        $sent = $m_checklist->getSent();

        $confirm_form_url = $m_checklist->getConfirmUrl() . '&usekey=fnum&rowid=' . $user->fnum;

        $app = JFactory::getApplication();
        $offset = $app->get('offset', 'UTC');
        try {
            $dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
            $dateTime = $dateTime->setTimezone(new DateTimeZone($offset));
            $now = $dateTime->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            echo $e->getMessage() . '<br />';
        }

        if (!empty($user->end_date)) {
            $is_dead_line_passed = (strtotime(date($now)) > strtotime($user->end_date)) ? true : false;
        }
    }

    $current_phase = $m_campaign->getCurrentCampaignWorkflow($user->fnum);
    if (!empty($current_phase) && !empty($current_phase->end_date)) {
        $deadline = new JDate($current_phase->end_date);
    } else {
        $deadline = !empty($admission) ? new JDate($user->fnums[$user->fnum]->admission_end_date) : new JDate($user->end_date);
    }

    require(JModuleHelper::getLayoutPath('mod_emundusflow', $layout));
}
