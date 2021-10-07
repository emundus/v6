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
require_once "SoapConnect.php";
require_once "XmlSchema.php";
require_once "XmlDataFilling.php";

/**
 * A cron task to email records to a give set of users (incomplete application)
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.emundusapogee
 * @since       3.0
 */

class PlgFabrik_Cronemundusapogee extends PlgFabrik_Cron {

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
        jimport('joomla.mail.helper');

        $params = $this->getParams();
        $eMConfig = JComponentHelper::getParams('com_emundus');

        /*
         * First of all, get all fnums having OPI code (get from jos_emundus_final_grade // where opi_code is not null or empty)
         * */

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        ///get wsdl url
        $wsdl_url = $params->get('webservice_url');

        /// get authentication type
        $auth_type = $params->get('webservice_authentication');

        /// get username
        $login_username = $params->get('webservice_username');

        /// get password
        $login_password = $params->get('webservice_password');

        /// get status
        $sending_status =$params->get('status_to_send_request');

        /*
         * Grab all fnums has OPI code and status (step) is in $sending_status
         * */

        $query->clear()
            ->select('#__emundus_final_grade.fnum')
            ->from($db->quoteName('#__emundus_final_grade'))
            ->leftJoin($db->quoteName('#__emundus_personal_detail') . ' ON ' . $db->quoteName('#__emundus_personal_detail.fnum') . ' = ' . $db->quoteName('#__emundus_final_grade.fnum'))
            ->leftJoin($db->quoteName('#__emundus_campaign_candidature') . ' ON ' . $db->quoteName('#__emundus_campaign_candidature.fnum') . ' = ' . $db->quoteName('#__emundus_final_grade.fnum'))
            ->where($db->quoteName('#__emundus_final_grade.code_opi') . ' is not null')
            ->andWhere($db->quoteName('#__emundus_final_grade.code_opi') . " != ''")
            ->andWhere($db->quoteName('#__emundus_campaign_candidature.status') . ' IN ( ' . $sending_status . ' )');

        $db->setQuery($query);
        $available_fnums = $db->loadColumn();

        /// get request description filename
        $json_request_schema = $params->get('xml_description_json');

        /// get data desription filename
        $json_request_data = $params->get('xml_data_json');

        /// build XML Schema (input of DataFilling)
        $_xmlSchemaObject = new XmlSchema($json_request_schema);
        $_xmlSchemaRequest = $_xmlSchemaObject->buildSoapRequest($json_request_schema);       /// return : XML Tree

        /// filling data
        $_xmlDataObject = new XmlDataFilling($json_request_data);

        foreach($available_fnums as $fnum) {
            $_xmlDataRequest = $_xmlDataObject->fillData($_xmlSchemaRequest, $_xmlSchemaObject->getSchemaDescription(), $fnum);

            $_xmlSchemaObject->exportXMLFile($_xmlDataRequest, EMUNDUS_PATH_ABS . DS . $fnum);
        }
    }
}
