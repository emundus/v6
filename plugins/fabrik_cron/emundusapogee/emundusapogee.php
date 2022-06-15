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
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
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
    public function process(&$data, &$listModel)
    {
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

        # get offset value (max limit by batch)
        $offset_limit = $params->get('offset_to_send_request', 20);              # set offset max 20 by default
        $offset_interval = $params->get('time_offset_to_send_request', 150);     # set offset interval 150 by default (2,5 min)

        # get specific date from logs section # by defaut : set CURRENT_DATE()
        $specific_date = $params->get('log_date', 'CURRENT_DATE()');

        # if no status is defined, we get all
        if(!empty(trim($sending_status))) {
            if(strpos($query, '{{status}}')) {
                // replace the template syntax {{status}} by SQL query
                $query = preg_replace('/{{status}}/', "jos_emundus_campaign_candidature.status IN (" . $sending_status . ")", $query);
            }
            else {
                if (!strpos($query, 'WHERE') && !strpos($query, 'where')) {
                    $query .= " WHERE";
                } else {
                    $query .= " AND";
                }
                $query .= " #__emundus_campaign_candidature.status IN (" . $sending_status . ")";
            }
        } else {
            if(strpos($query, '{{status}}'))
                $query = preg_replace('/{{status}}/', "", $query);
        }

        # build logs string
        if (!empty(trim($sending_logs))) {
            if(strpos($query, '{{logs_actions}}')) {
                $query = preg_replace('/{{logs_actions}}/', "jos_emundus_logs.action_id IN (" . $sending_logs . ')', $query);
            }
            else {
                $query .= " AND #__emundus_logs.action_id IN (" . $sending_logs . ')';
            }
        } else {
            if(strpos($query, '{{logs_actions}}'))
                $query = preg_replace('/{{logs_actions}}/', "", $query);
        }

        # build actions string
        if(!empty(trim($sending_actions))) {
            $actions = "";
            foreach (explode(',', $sending_actions) as $action) {
                $actions .= "'" . $action . "',";
            }
            $actions = substr($actions, 0, -1);
            if(strpos($query, '{{logs_verbs}}')) {
                $query = preg_replace('/{{logs_verbs}}/', "jos_emundus_logs.verb IN (" . $actions . ')', $query);
            } else {
                $query .= " AND #__emundus_logs.verb IN (" . $actions . ')';
            }
        } else {
            if(strpos($query, '{{logs_verbs}}'))
                $query = preg_replace('/{{logs_verbs}}/', "", $query);

        }

        # build sending date string
        if($sending_date == "1") {
            $specific_date = strpos($specific_date,'CURRENT_DATE()') === 0 ? $specific_date : $db->quote($specific_date);

            if(strpos($query, '{{logs_date}}')) {
                $query = preg_replace('/{{logs_date}}/', "DATE (jos_emundus_logs.timestamp) = " . $specific_date, $query);
            } else {
                $query .= "DATE (jos_emundus_logs.timestamp) = " . $specific_date;
            }
        } else {
            if(strpos($query, '{{logs_date}}'))
                $query = preg_replace('/{{logs_date}}/', "", $query);
        }

        # next, we request description (schema) - json file
        $json_request_schema = $params->get('xml_description_json');

        # now, it's time to build XML request
        $xmlSchemaObj = new XmlSchema($json_request_schema);
        $xmlSchemaRequest = $xmlSchemaObj->buildSoapRequest($json_request_schema);     # return type: XMLDocument

        # customize XML schema (uncomment these lines if needed)
        # $_xmlCustomSchema_schema = new ApogeeCustom($_xmlSchemaRequest);
        # $_xmlCustomSchema_schema->buildCustomSchema();

        $db->setQuery($query);
        $available_fnums = $db->loadColumn();
        $chunks = array_chunk($available_fnums, $offset_limit);

//        echo '<pre>'; var_dump($available_fnums); echo '</pre>'; die;

        foreach($chunks as $chunked_fnums) {
            foreach ($chunked_fnums as $fnum) {
                $profile = (EmundusModelFiles::getFnumInfos($fnum))['profile_id'];

                # and, data description (data mapping schema) - json file
                if(is_null($profile)) {
                    continue;
                } else {
                    $json_request_data = $params->get('xml_data_json') . '_' . $profile . '.json';
                }

                # now, we fill data into XML request (using data description file)
                $xmlDataObj = new XmlDataFilling($json_request_data);

                if(!$xmlDataObj->getDataMapping()) {
                    JLog::add('[emundusApogee] Json data mapping file not found: ' . $json_request_data, JLog::ERROR, 'com_emundus');
                    continue;
                }

                # filling data for each fnum
                $xmlDataRequest = $xmlDataObj->fillData($xmlSchemaRequest, $xmlSchemaObj->getSchemaDescription(), $fnum);

                if (!empty(trim($custom_php))) {
                    try {
                        eval($custom_php);
                    } catch (Exception $e) {
                        JLog::add('[emundusApogee] Error in custom PHP code: ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
                    }
                }

                # prune raw xml tree (remove unnecessary elements)
                $xmlOutputRawString = $xmlSchemaObj->exportXMLString($xmlDataRequest);
                $xmlOutputString = $xmlDataObj->pruneXML($xmlOutputRawString);

                $soapConnectObj = new SoapConnect;
                $soapConnectObj->setSoapHeader($xmlOutputString->saveXML(), $credentials);

                # send request to Apogee server
                $soapConnectObj->sendRequest($soapConnectObj->webServiceConnect($wsdl_url,$xmlOutputString->saveXML(),$credentials),$fnum);

                # uncomment this line if you want to export requests into XML file (** should be deactivate on PROD env **)
                $xmlSchemaObj->exportXMLFile($xmlOutputString, EMUNDUS_PATH_ABS . DS . $fnum);
            }

            sleep($offset_interval);
        }
    }
}
