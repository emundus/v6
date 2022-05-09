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
        $db = JFactory::getDbo();

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

        # (optional) get logs, actions from Back-Office
        $sending_logs = $params->get('logs_to_send_request');
        $sending_actions = $params->get('actions_to_send_request');

        # (optional) get logs day (today or not)
        $sending_date = $params->get('is_today');
        
        # (optional) get custom methods (ApogeeCustom.php)
        $custom_php = $params->get('plg-cron-emundusapogee-customs-php');

        # get native SQL query
        $query = $params->get('plg-cron-emundusapogee-sql-code');

        # if no status is defined, we get all
        if(!empty(trim($sending_status))) {
            if(!strpos($query, 'WHERE') and !strpos($query, 'where')) {
                $query .= " WHERE";
            } else {
                $query .= " AND";
            }

            $query .= " #__emundus_campaign_candidature.status IN (" . $sending_status . ")";
        }

        # build logs string
        if(!empty(trim($sending_logs))) {
            $query .= " AND #__emundus_logs.action_id IN (" . $sending_logs . ')';
        }

        # build actions string
        if(!empty(trim($sending_actions))) {
            $actions = "";
            foreach (explode(',', $sending_actions) as $action) {
                $actions .= "'" . $action . "',";
            }
            $actions = substr($actions, 0, -1);
            $query .= " AND #__emundus_logs.verb IN (" . $actions . ')';
        }

        # build sending date string
        if($sending_date == "1") { $query .= " AND DATE (#__emundus_logs.timestamp) = CURRENT_DATE()"; }

        # add LIMIT params --> necessary?
        $query .= " LIMIT 20";

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

            if(!empty(trim($custom_php))) {
                eval($custom_php);
            }

            # prune raw xml tree (remove unnecessary elements)
            $xmlOutputRawString = $xmlSchemaObj->exportXMLString($xmlDataRequest);
            $xmlOutputString = $xmlDataObj->pruneXML($xmlOutputRawString);

            # connect to SOAP
            $soapConnectObj = new SoapConnect;

            # set HTTP request header with last xml output string
            $soapConnectObj->setSoapHeader($xmlOutputString->saveXML(),$credentials);

            # send request to Apogee server
            # $soapConnectObj->sendRequest($soapConnectObj->webServiceConnect($wsdl_url,$xmlOutputString->saveXML(),$credentials));

            # uncomment this line if you want to export requests into XML file (** should be deactivate on PROD env **)
            $xmlSchemaObj->exportXMLFile($xmlOutputString, EMUNDUS_PATH_ABS . DS . $fnum);
        }
    }
}
