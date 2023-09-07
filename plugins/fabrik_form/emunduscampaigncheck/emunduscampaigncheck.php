<?php
/**
 * @version 2: emunduscampaign 2019-04-11 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Création de dossier de candidature automatique.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
 * Create a Joomla user from the forms data
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.emunduscampaigncheck
 * @since       3.0
 */

class PlgFabrik_FormEmundusCampaignCheck extends plgFabrik_Form {

    /**
     * Get an element name
     *
     * @param   string  $pname  Params property name to look up
     * @param   bool    $short  Short (true) or full (false) element name, default false/full
     *
     * @return	string	element full name
     */
    public function getFieldName($pname, $short = false) {
        $params = $this->getParams();

        if ($params->get($pname) == '') {
            return '';
        }

        $elementModel = FabrikWorker::getPluginManager()->getElementPlugin($params->get($pname));

        return $short ? $elementModel->getElement()->name : $elementModel->getFullName();
    }

    /**
     * Get the fields value regardless of whether its in joined data or no
     *
     * @param   string  $pname    Params property name to get the value for
     * @param   mixed   $default  Default value
     *
     * @return  mixed  value
     */
    public function getParam($pname, $default = '') {
        $params = $this->getParams();

        if ($params->get($pname) == '') {
            return $default;
        }

        return $params->get($pname);
    }

    /**
     * Main script.
     *
     * @return Bool
     * @throws Exception
     */
    public function onBeforeStore() {

	    require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'access.php');

        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.campaign-check.php'), JLog::ALL, array('com_emundus.campaign-check'));

        $user = JFactory::getSession()->get('emundusUser');
        $app = JFactory::getApplication();
        $jinput = $app->input;

	    $eMConfig = JComponentHelper::getParams('com_emundus');
	    $id_profiles = $eMConfig->get('id_profiles', '0');
	    $id_profiles = explode(',', $id_profiles);

        $campaign_id = is_array($jinput->get('jos_emundus_campaign_candidature___campaign_id_raw')) ? $jinput->get('jos_emundus_campaign_candidature___campaign_id_raw')[0] : $jinput->get('jos_emundus_campaign_candidature___campaign_id_raw');

        $applicant_can_renew = $this->getParam('applicant_can_renew', 'em_config');

        if ($applicant_can_renew === 'em_config') {
            $applicant_can_renew = $eMConfig->get('applicant_can_renew', '0');
        }

        // Check if the campaign limit has been obtained
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'campaign.php');
        $m_campaign = new EmundusModelCampaign;
        $isLimitObtained = $m_campaign->isLimitObtained($campaign_id);

        if ($isLimitObtained === true) {
            JLog::add('User: '.$user->id.' Campaign limit is obtained', JLog::ERROR, 'com_emundus.campaign-check');
            $this->getModel()->formErrorMsg = '';
            $this->getModel()->getForm()->error = JText::_('LIMIT_OBTAINED');
            return false;
        }

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

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        switch ($applicant_can_renew) {

            // Cannot create new campaigns at all.
            case 0:
                $query->select('COUNT('.$db->quoteName('id').')')
                    ->from($db->quoteName('#__emundus_campaign_candidature'))
                    ->where($db->quoteName('applicant_id') . ' = ' . $user->id)
                    ->andWhere($db->quoteName('published').' <> '.$db->quote('-1'));

                $db->setQuery($query);
                $files = $db->loadResult();

                if ($files > 0) {
                    JLog::add('User: '.$user->id.' already has a file.', JLog::ERROR, 'com_emundus.campaign-check');
                    $this->getModel()->formErrorMsg = '';
                    $this->getModel()->getForm()->error = JText::_('CANNOT_HAVE_MULTI_FILE');
                    $app->redirect('index.php', JText::_('CANNOT_HAVE_MULTI_FILE'));
                }

                break;

            // If the applicant can only have one file per campaign.
            case 2:
                $config = JFactory::getConfig();

                $timezone = new DateTimeZone( $config->get('offset') );
                $now = JFactory::getDate()->setTimezone($timezone);

                $query
                    ->select($db->quoteName('campaign_id'))
                    ->from($db->quoteName('#__emundus_campaign_candidature'))
                    ->where($db->quoteName('applicant_id') . ' = ' . $user->id)
                    ->andWhere($db->quoteName('published').' <> '.$db->quote('-1'));

                try {

                    $db->setQuery($query);

                    $user_campaigns = $db->loadColumn();

                    $query
                        ->clear()
                        ->select($db->quoteName('id'))
                        ->from($db->quoteName('#__emundus_setup_campaigns'))
                        ->where($db->quoteName('published') . ' = 1')
                        ->andWhere($db->quoteName('end_date') . ' >= ' . $db->quote($now))
                        ->andWhere($db->quoteName('start_date') . ' <= ' . $db->quote($now))
                        ->andWhere($db->quoteName('id') . ' NOT IN (' . implode(',', $user_campaigns). ')');

                    $db->setQuery($query);
                    if (!in_array($campaign_id, $db->loadColumn())) {
                        JLog::add('User: '.$user->id.' already has a file for campaign id: '.$campaign_id, JLog::ERROR, 'com_emundus.campaign-check');
                        $this->getModel()->formErrorMsg = '';
                        $this->getModel()->getForm()->error = JText::_('USER_HAS_FILE_FOR_CAMPAIGN');
                        $app->redirect('index.php', JText::_('USER_HAS_FILE_FOR_CAMPAIGN'));
                    }

                } catch (Exception $e) {
                    JLog::add('plugin/emundus_campaign SQL error at query :'. preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus.campaign-check');
                    $this->getModel()->formErrorMsg = '';
                    $this->getModel()->getForm()->error = JText::_('ERROR');
                }

                break;

            // If the applicant can only have one file per school year.
            case 3:

                $config = JFactory::getConfig();

                $timezone = new DateTimeZone( $config->get('offset') );
                $now = JFactory::getDate()->setTimezone($timezone);

                $query
                    ->select($db->quoteName('sc.year'))
                    ->from($db->quoteName('#__emundus_campaign_candidature', 'cc'))
                    ->leftJoin($db->quoteName('#__emundus_setup_campaigns','sc').' ON '.$db->quoteName('sc.id').' = '.$db->quoteName('cc.campaign_id'))
                    ->where($db->quoteName('applicant_id') . ' = ' . $user->id);

                try {

                    $db->setQuery($query);

                    $user_years = $db->loadColumn();

                    $query
                        ->clear()
                        ->select($db->quoteName('id'))
                        ->from($db->quoteName('#__emundus_setup_campaigns'))
                        ->where($db->quoteName('published') . ' = 1')
                        ->andWhere($db->quoteName('end_date') . ' >= ' . $db->quote($now))
                        ->andWhere($db->quoteName('start_date') . ' <=  ' . $db->quote($now))
                        ->andWhere($db->quoteName('year') . ' NOT IN (' . implode(',', $db->q($user_years)). ')');

                    $db->setQuery($query);
                    if (!in_array($campaign_id, $db->loadColumn())) {
                        JLog::add('User: '.$user->id.' already has a file for year belong to campaign: '.$campaign_id, JLog::ERROR, 'com_emundus.campaign-check');
                        $this->getModel()->formErrorMsg = '';
                        $this->getModel()->getForm()->error = JText::_('USER_HAS_FILE_FOR_YEAR');
	                    $app->redirect('index.php', JText::_('USER_HAS_FILE_FOR_YEAR'));
                    }

                } catch (Exception $e) {
                    JLog::add('plugin/emundus_campaign SQL error at query :'. preg_replace("/[\r\n]/"," ",$query->__toString()), JLog::ERROR, 'com_emundus.campaign-check');
                    $this->getModel()->formErrorMsg = '';
                    $this->getModel()->getForm()->error = JText::_('ERROR');
                }

                break;
        }
    }
}
