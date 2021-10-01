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

        ///get wsdl url
        $wsdl_url = $params->get('webservice_url');

        /// get authentication type
        $auth_type = $params->get('webservice_authentication');

        /// get username
        $login_username = $params->get('webservice_username');

        /// get password
        $login_password = $params->get('webservice_password');

        /// get request description filename
        $json_request_schema = $params->get('xml_description_json');

        /// get data desription filename
        $json_request_data = $params->get('xml_data_json');

        /// build XML Schema (input of DataFilling)
        $_xmlSchemaObject = new XmlSchema($json_request_schema);
        $_xmlSchemaRequest = $_xmlSchemaObject->buildSoapRequest($json_request_schema);       /// return : XML Tree

        /// filling data
        $_xmlDataObject = new XmlDataFilling($json_request_data);
        $_xmlDataRequest = $_xmlDataObject->fillData($_xmlSchemaRequest, $_xmlSchemaObject->getSchemaDescription(), ['2021030112462400000300000227']);

        $_xmlSchemaObject->exportXMLFile($_xmlDataRequest, EMUNDUS_PATH_ABS .DS . 'duy_1234');die;

    }
}
