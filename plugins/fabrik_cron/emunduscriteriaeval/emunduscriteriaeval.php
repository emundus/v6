<?php
/**
 * A cron task to email a recall to incomplet applications
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.email
 * @copyright   Copyright (C) 2015 emundus.fr - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';
require_once (JPATH_SITE . DS. 'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');

/**
 * A cron task to email records to a give set of users (incomplete application)
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.emundusrecall
 * @since       3.0
 */

class PlgFabrik_Cronemunduscriteriaeval extends PlgFabrik_Cron {

    /**
     * Check if the user can use the plugin
     *
     * @param   string  $location  To trigger plugin on
     * @param   string  $event     To trigger plugin on
     *
     * @return  bool can use or not
     */
    public function canUse($location = null, $event = null) {
        return true;
    }

    /**
     * Do the plugin action
     *
     * @param array  &$data data
     *
     * @return  int  number of records updated
     * @throws Exception
     */
    public function process(&$data, &$listModel) {

        $params = $this->getParams();

        $calc01 = $params->get('reminder_calc01',null);
        $calc02 = $params->get('reminder_calc02', null);
        $calc03 = $params->get('reminder_calc03', null);
        $calc04 = $params->get('reminder_calc04', null);
        $calc05 = $params->get('reminder_calc05',null);
        $calc06 = $params->get('reminder_calc06', null);
        $calc07 = $params->get('reminder_calc07', null);

        $notSent = $params->get('reminder_notSent',0);

        $m_model = new EmundusModelEmails();
        $this->log = '';

        // Get list of applicants to notify
        $db = FabrikWorker::getDbo();


        //Select des dossiers dont les
        $query = 'SELECT u.id, ecc.fnum, ecc.applicant_id, esc.end_date
					FROM #__emundus_campaign_candidature as ecc
					LEFT JOIN #__users as u ON u.id=ecc.applicant_id
					LEFT JOIN #__emundus_users as eu ON eu.user_id=u.id
					LEFT JOIN #__emundus_setup_campaigns as esc ON esc.id=ecc.campaign_id
					LEFT JOIN #__emundus_setup_status as ess ON ess.id=ecc.status
					WHERE ecc.published = 1 AND u.block = 0 AND esc.published = 1 AND ecc.status NOT IN ('.$notSent.')';

        $db->setQuery($query);
        $applicants = $db->loadObjectList();


        // Generate emails from template and store it in message table
        if (!empty($applicants)) {

            foreach ($applicants as $applicant) {
                $effectiveDate = date('Y-m-d h:i:s', strtotime("+3 months", strtotime($applicant->end_date))); //Date trois mois après la date de fin de campagne

                if($applicant->end_date < $effectiveDate){

                    $tags = $m_model->setTags($applicant->id, null, $applicant->fnum, ''); //Récupération des tags et remplacement du tags fnum

                    foreach ($tags['patterns'] as $key=>$value) {
                        $tags['patterns'][$key] = str_replace('/','',stripslashes($value)); //suppression des slash et backslash dans la valeur
                    }

                    //Récupération de la key du pattern en fonction de la value du paramètre si celle-ci n'est pas null
                    if($calc01 !== null )
                    $value1 = array_search($calc01,$tags['patterns']);

                    if($calc02 !== null )
                    $value2 = array_search($calc02,$tags['patterns']);

                    if($calc03 !== null )
                    $value3 = array_search($calc03,$tags['patterns']);

                    if($calc04 !== null )
                    $value4 = array_search($calc04,$tags['patterns']);

                    if($calc05 !== null )
                    $value5 = array_search($calc05,$tags['patterns']);

                    if($calc06 !== null )
                    $value6 = array_search($calc06,$tags['patterns']);

                    if($calc07 !== null )
                    $value7 = array_search($calc07,$tags['patterns']);

                    //Update des champs dans campaign candidature
                    $query = $db->getQuery(true);

                    $fields = array(
                        $db->quoteName('calc01') . ' = ' . $db->quote($tags['replacements'][$value1]),
                        $db->quoteName('calc02') . ' = ' . $db->quote($tags['replacements'][$value2]),
                        $db->quoteName('calc03') . ' = ' . $db->quote($tags['replacements'][$value3]),
                        $db->quoteName('calc04') . ' = ' . $db->quote($tags['replacements'][$value4]),
                        $db->quoteName('calc05') . ' = ' . $db->quote($tags['replacements'][$value5]),
                        $db->quoteName('calc06') . ' = ' . $db->quote($tags['replacements'][$value6]),
                        $db->quoteName('calc07') . ' = ' . $db->quote($tags['replacements'][$value7])
                    );

                    $conditions = array(
                        $db->quoteName('fnum') . ' LIKE '. $db->quote($applicant->fnum),
                        $db->quoteName('applicant_id') . ' = '.$applicant->id,
                    );

                    $query->update($db->quoteName('#__emundus_campaign_candidature'))->set($fields)->where($conditions);

                    $db->setQuery($query);

                    $db->execute();

                }
            }

        }
    }


}
