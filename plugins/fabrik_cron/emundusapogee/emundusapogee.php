<?php
/**
 * A cron task to send data to Apogee server
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
require_once "ApogeeCustom.php";

/**
 * A cron task to send data to Apogee server
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

        /*
         * First of all, we get all fnums having OPI code - satisfying 3 following conditions:
         * 1. OPI code must exist (not null + not empty)
         * 2. Date of birth (DoB) must exist (not null + not empty)
         * 3. Firstname must exist (not null + not empty)
         * 4. Lastname must exist (not null + not empty)
         * */

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        # get WSDL url
        $wsdl_url = $params->get('webservice_url');

        # get authentication type (e.g: Basic Auth, Token, Digest, etc)
        $auth_type = $params->get('webservice_authentication');

        # get user login
        $login_username = $params->get('webservice_username');

        # get user password
        $login_password = $params->get('webservice_password');

        # and then, we group all authentication information into CREDENTIAL group (easy to manage)
        $credentials = new stdClass();
        $credentials->auth_type = $auth_type;
        $credentials->auth_user = $login_username;
        $credentials->auth_pwd = $login_password;

        # (optional) we define the status by which we send request (e.g: "Accepted", "Pre-accepted", etc)
        $sending_status = $params->get('status_to_send_request');

        /*
         * Grab all fnums has OPI code and status (step) is in $sending_status
         * */

        $query->clear()
            ->select('distinct #__emundus_campaign_candidature.fnum')
            ->from($db->quoteName('#__emundus_final_grade'))
            ->leftJoin($db->quoteName('#__emundus_campaign_candidature') . ' ON ' . $db->quoteName('#__emundus_campaign_candidature.fnum') . ' = ' . $db->quoteName('#__emundus_final_grade.fnum'))
            ->leftJoin($db->quoteName('#__emundus_personal_detail') . ' ON ' . $db->quoteName('#__emundus_campaign_candidature.fnum') . ' = ' . $db->quoteName('#__emundus_personal_detail.fnum'))
            ->leftJoin($db->quoteName('#__emundus_1001_00') . ' ON ' . $db->quoteName('#__emundus_campaign_candidature.fnum') . ' = ' . $db->quoteName('#__emundus_1001_00.fnum'))
            ->leftJoin($db->quoteName('#__emundus_users') . ' ON ' . $db->quoteName('#__emundus_campaign_candidature.applicant_id') . ' = ' . $db->quoteName('#__emundus_users.user_id'))
            ->leftJoin($db->quoteName('#__emundus_logs') . ' ON ' . $db->quoteName('#__emundus_campaign_candidature.fnum') . ' = ' . $db->quoteName('#__emundus_logs.fnum_to'))

            ->where($db->quoteName('#__emundus_final_grade.code_opi') . ' is not null')
            ->andWhere($db->quoteName('#__emundus_final_grade.code_opi') . " != ''")
            ->andWhere($db->quoteName('#__emundus_personal_detail.birth_date') . " != ''")
            ->andWhere($db->quoteName('#__emundus_personal_detail.birth_date') . " is not null")
            ->andWhere($db->quoteName('#__emundus_users.firstname') . " is not null")
            ->andWhere($db->quoteName('#__emundus_users.firstname') . " != ''")
            ->andWhere($db->quoteName('#__emundus_users.lastname') . " is not null")
            ->andWhere($db->quoteName('#__emundus_users.lastname') . " != ''");

        # if no status is defined, we get all
        if(!is_null($sending_status)) { $query->andWhere($db->quoteName('#__emundus_campaign_candidature.status') . ' IN ( ' . $sending_status . ' )'); }

        # logs: 1 (access file), 4 (access attachment), 5 (access evaluation), 10 (comment file), 14 (access tag), 24 (edit user), 28 (publish), 29 (decision), 32 (admission)
        $query->andWhere(
            (
            "jos_emundus_logs.action_id = 1 AND (jos_emundus_logs.verb in ('c', 'u', 'd'))
                    OR jos_emundus_logs.action_id = 4 AND (jos_emundus_logs.verb in ('c', 'u', 'd'))
                    OR jos_emundus_logs.action_id = 5 AND (jos_emundus_logs.verb in ('c', 'u', 'd'))
                    OR jos_emundus_logs.action_id = 10 AND (jos_emundus_logs.verb in ('c', 'u', 'd'))
                    OR jos_emundus_logs.action_id = 14 AND (jos_emundus_logs.verb in ('c', 'u', 'd'))
                    OR jos_emundus_logs.action_id = 24 AND (jos_emundus_logs.verb in ('c', 'u', 'd'))
                    OR jos_emundus_logs.action_id = 28 AND (jos_emundus_logs.verb in ('c', 'u', 'd'))
                    OR jos_emundus_logs.action_id = 29 AND (jos_emundus_logs.verb in ('c', 'u', 'd'))
                    OR jos_emundus_logs.action_id = 32 AND (jos_emundus_logs.verb in ('c', 'u', 'd'))
                "
            ));

        $query->andWhere('DATE (jos_emundus_logs.timestamp) = CURRENT_DATE()');

        # uncomment this line if you want to limit the records
        # $query->setLimit(3);       /* just local test */

        $db->setQuery($query);
        $available_fnums = $db->loadColumn();

        # next, we request description (schema) - json file
        $json_request_schema = $params->get('xml_description_json');

        # and, data description (data mapping schema) - json file
        $json_request_data = $params->get('xml_data_json');

        # now, it's time to build XML request
        $xmlSchemaObj = new XmlSchema($json_request_schema);
        $xmlSchemaRequest = $xmlSchemaObj->buildSoapRequest($json_request_schema);     # return type: XMLDocument

        # customize XML schema (uncomment these lines if needed)
        # $_xmlCustomSchema_schema = new ApogeeCustom($_xmlSchemaRequest);
        # $_xmlCustomSchema_schema->buildCustomSchema();

        # now, we fill data into XML request (using data description file)
        $xmlDataObj = new XmlDataFilling($json_request_data);

        foreach($available_fnums as $fnum) {
            # filling data for each fnum
            $xmlDataRequest = $xmlDataObj->fillData($xmlSchemaRequest, $xmlSchemaObj->getSchemaDescription(), $fnum);

            # invoke Apogee Custom
            $xmlCustomSchema_data = new ApogeeCustom($xmlDataRequest,$fnum);
            $xmlCustomSchema_data->buildCustomData();
            $xmlCustomSchema_data->setCustomVoeux();
            $xmlCustomSchema_data->validateVoeux();

            # prune raw xml tree (remove unnecessary elements)
            $xmlOutputRawString = $xmlSchemaObj->exportXMLString($xmlDataRequest);
            $xmlOutputString = $xmlDataObj->pruneXML($xmlOutputRawString);

            # connect to SOAP
            $soapConnectObj = new SoapConnect;

            # set HTTP request header with last xml output string
            $soapConnectObj->setSoapHeader($xmlOutputString->saveXML(),$credentials);

            # send request to Apogee server
            $soapConnectObj->sendRequest($soapConnectObj->webServiceConnect($wsdl_url,$xmlOutputString->saveXML(),$credentials));

            # uncomment this line if you want to export requests into XML file (** should be deactivate on PROD env **)
            $xmlSchemaObj->exportXMLFile($xmlOutputString, EMUNDUS_PATH_ABS . DS . $fnum);
        }
    }
}
