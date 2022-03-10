<?php

// no direct access
defined('_JEXEC') or die('Restricted access');
/*
* @package    miniOrange
* @subpackage Plugins
* @license    GNU/GPLv3
* @copyright  Copyright 2015 miniOrange. All Rights Reserved.
*/
jimport('joomla.plugin.plugin');
jimport('miniorangesamlplugin.utility.SAML_Utilities');
require_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_saml' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo-saml-utility.php';

/**
 * miniOrange SAML System plugin
 */
class plgSystemSamlredirect extends JPlugin
{
    public function onAfterRender()
    {
        $app = JFactory::getApplication();
        $body = $app->getBody();
        $url = JURI::root();
        $tab = 0;
        $tables = JFactory::getDbo()->getTableList();
        foreach ($tables as $table) {
            if (strpos($table, "miniorange_saml_config") !== FALSE)
                $tab = $table;
        }
        if ($tab === 0)
            return;

        $attribute = new Mo_saml_Local_Util();
        $linkCheck = $attribute->_load_db_values('#__miniorange_saml_config');
        $linkChecked = isset($linkCheck['login_link_check']) && $linkCheck['login_link_check'] == 1;
        $dynamicLink = empty($linkCheck['dynamic_link']) || !isset($linkCheck['dynamic_link']) ? "Login with IDP" : $linkCheck['dynamic_link'];
        if ($linkChecked == 1 && $app->isClient('site')) {
            $linkCondition = <<<EOD
<button type="submit" tabindex="0" name="Submit" class="btn btn-primary login-button">
EOD;

            if (stristr($body, $linkCondition)) {
                if (stristr($body, "user.login")) {
                    $linkPosition = "</button><br><a href = " . $url . "?morequest=sso>" . $dynamicLink . " ";
                    $body = str_replace('</button>', $linkPosition . '</a>', $body);
                    $app->setBody($body);

                }
            }
        }
    }

    public function onAfterInitialise()
    {
        $get = JFactory::getApplication()->input->get->getArray();
        $post = JFactory::getApplication()->input->post->getArray();

        if (isset($post['mojsp_feedback'])) {
            $radio = $post['deactivate_plugin'];
            $data = $post['query_feedback'];
            $feedback_email = $post['feedback_email'];

            $database_name = '#__miniorange_saml_config';
            $updatefieldsarray = array(
                'uninstall_feedback' => 1,
            );
            $result = new Mo_saml_Local_Util();
            $result->generic_update_query($database_name, $updatefieldsarray);

            $current_user = JFactory::getUser();

            $customerResult = new Mo_saml_Local_Util();
            $customerResult = $customerResult->_load_db_values('#__miniorange_saml_customer_details');
            $admin_email = $customerResult['email'];
            $admin_phone = $customerResult['admin_phone'];
            $data1 = $radio . ' : ' . $data . '  <br><br> Email :  ' . $feedback_email;

            require_once JPATH_BASE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_saml' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo-saml-customer-setup.php';
            Mo_saml_Local_Customer::submit_feedback_form($admin_email, $admin_phone, $data1);
            require_once JPATH_SITE . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Installer' . DIRECTORY_SEPARATOR . 'Installer.php';

            foreach ($post['result'] as $fbkey) {
                $result = Mo_saml_Local_Util::loadDBValues('#__extensions', 'loadColumn','type',  'extension_id', $fbkey);
                $identifier = $fbkey;
                $type = 0;
                foreach ($result as $results) {
                    $type = $results;
                }

                if ($type) {
                    $cid = 0;
                    $installer = new JInstaller();
                    $installer->uninstall($type, $identifier, $cid);
                }
            }
        }
        $obj = new Mo_saml_Local_Util();
        if (isset($get['morequest']) && $get['morequest'] == 'sso') {
            $pluginconfig = $obj->_load_db_values('#__miniorange_saml_config');
            $this->sendSamlRequest($pluginconfig);
        } else if (isset($get['morequest']) && $get['morequest'] == 'metadata') {
            $pluginconfig = $obj->_load_db_values('#__miniorange_saml_config');
            $this->generateMetadata($pluginconfig);
        } else if (isset($get['morequest']) && $get['morequest'] == 'download_metadata') {
            $pluginconfig = $obj->_load_db_values('#__miniorange_saml_config');
            $download = true;
            $this->generateMetadata($pluginconfig, $download);
        } else if (isset($get['morequest']) && $get['morequest'] == 'acs') {
            $pluginconfig = $obj->_load_db_values('#__miniorange_saml_config');
            $this->getSamlResponse($pluginconfig);
        }
    }

    function onExtensionBeforeUninstall($id)
    {
        $post = JFactory::getApplication()->input->post->getArray();
        $tables = JFactory::getDbo()->getTableList();
        $result = Mo_saml_Local_Util::loadDBValues('#__extensions', 'loadColumn','extension_id',  'name', 'com_miniorange_saml');
        $tables = JFactory::getDbo()->getTableList();
        $tab = 0;
        foreach ($tables as $table) {
            if (strpos($table, "miniorange_saml_config"))
                $tab = $table;
        }

        if ($tab) {
            $fid = new Mo_saml_Local_Util();
            $fid = $fid->_load_db_values('#__miniorange_saml_config');
            $fid = $fid['uninstall_feedback'];
            $tpostData = $post;

            if ($fid == 0) {
                foreach ($result as $results) {
                    if ($results == $id) { ?>
                        <div class="form-style-6 " style="width:35% !important; margin-left:33%; margin-top: 4%;">
                            <h1> Feedback form for Joomla SAML SP</h1>
                            <form name="f" method="post" action="" id="mojsp_feedback"
                                  style="background: #f3f1f1; padding: 10px;">
                                <h3>What Happened? </h3>
                                <input type="hidden" name="mojsp_feedback" value="mojsp_feedback"/>
                                <div>
                                    <p style="margin-left:2%">
                                        <?php
                                        $deactivate_reasons = array(
                                            "Facing issues During Registration",
                                            "Does not have the features I'm looking for",
                                            "Not able to Configure",
                                            "It's a temporary deactivation",
                                            "The plugin didn't working",
                                            "Other Reasons:"
                                        );
                                        foreach ($deactivate_reasons

                                        as $deactivate_reasons) { ?>
                                    <div class="radio" style="padding:1px;margin-left:2%">
                                        <label style="font-weight:normal;font-size:14.6px;font-family: cursive;"
                                               for="<?php echo $deactivate_reasons; ?>">
                                            <input type="radio" name="deactivate_plugin"
                                                   value="<?php echo $deactivate_reasons; ?>" required>
                                            <?php echo $deactivate_reasons; ?></label>
                                    </div>

                                    <?php } ?>
                                    <br>

                                    <textarea id="query_feedback" name="query_feedback" rows="4"
                                              style="margin-left:3%;width: 91%" cols="50"
                                              placeholder="Write your query here"></textarea><br><br><br>
                                    <tr>
                                        <td width="20%"><b>Email<span style="color: #ff0000;">*</span>:</b></td>
                                        <td><input type="email" name="feedback_email" required
                                                   placeholder="Enter email to contact." style="width:55%"/></td>
                                    </tr>

                                    <?php
                                    foreach ($tpostData['cid'] as $key) { ?>
                                        <input type="hidden" name="result[]" value=<?php echo $key ?>>
                                    <?php } ?>
                                    <br><br>
                                    <div class="mojsp_modal-footer">
                                        <input style="cursor: pointer;font-size: large;" type="submit"
                                               name="miniorange_feedback_submit"
                                               class="button button-primary button-large" value="Submit"/>
                                    </div>
                                </div>
                            </form>
                            <form name="f" method="post" action="" id="mojsp_feedback_form_close">
                                <input type="hidden" name="option" value="mojsp_skip_feedback"/>
                            </form>
                        </div>
                        <script src="http://code.jquery.com/jquery-1.11.3.min.js"></script>
                        <script>
                            jQuery('input:radio[name="deactivate_plugin"]').click(function () {
                                var reason = jQuery(this).val();
                                jQuery('#query_feedback').removeAttr('required')

                                if (reason == 'Facing issues During Registration') {
                                    jQuery('#query_feedback').attr("placeholder", "Can you please describe the issue in detail?");
                                } else if (reason == "Does not have the features I'm looking for") {
                                    jQuery('#query_feedback').attr("placeholder", "Let us know what feature are you looking for");
                                } else if (reason == "Other Reasons:") {
                                    jQuery('#query_feedback').attr("placeholder", "Can you let us know the reason for deactivation");
                                    jQuery('#query_feedback').prop('required', true);
                                } else if (reason == "Not able to Configure") {
                                    jQuery('#query_feedback').attr("placeholder", "Not able to Configure? let us know so that we can improve the interface");
                                }
                            });

                            // When the user clicks on <span> (x), mojsp_close the mojsp_modal
                            var span = document.getElementsByClassName("mojsp_close")[0];
                            span.onclick = function () {
                                mojsp_modal.style.display = "none";
                                jQuery('#mojsp_feedback_form_close').submit();
                            }
                        </script>
                        <style>
                            .form-style-6 {
                                font: 95% Arial, Helvetica, sans-serif;
                                max-width: 400px;
                                margin: 10px auto;
                                padding: 16px;
                                background: #F7F7F7;
                            }

                            .form-style-6 h1 {
                                background: #43D1AF;
                                padding: 20px 0;
                                font-size: 140%;
                                font-weight: 300;
                                text-align: center;
                                color: #fff;
                            }

                            .form-style-6 input[type="text"],
                            .form-style-6 input[type="date"],
                            .form-style-6 input[type="datetime"],
                            .form-style-6 input[type="email"],
                            .form-style-6 input[type="number"],
                            .form-style-6 input[type="search"],
                            .form-style-6 input[type="time"],
                            .form-style-6 input[type="url"],
                            .form-style-6 textarea,
                            .form-style-6 select {
                                -webkit-transition: all 0.30s ease-in-out;
                                -moz-transition: all 0.30s ease-in-out;
                                -ms-transition: all 0.30s ease-in-out;
                                -o-transition: all 0.30s ease-in-out;
                                outline: none;
                                box-sizing: border-box;
                                -webkit-box-sizing: border-box;
                                -moz-box-sizing: border-box;
                                width: 100%;
                                background: #fff;
                                margin-bottom: 4%;
                                border: 1px solid #ccc;
                                padding: 3%;
                                color: #555;
                                font: 95% Arial, Helvetica, sans-serif;
                            }

                            .form-style-6 input[type="text"]:focus,
                            .form-style-6 input[type="date"]:focus,
                            .form-style-6 input[type="datetime"]:focus,
                            .form-style-6 input[type="email"]:focus,
                            .form-style-6 input[type="number"]:focus,
                            .form-style-6 input[type="search"]:focus,
                            .form-style-6 input[type="time"]:focus,
                            .form-style-6 input[type="url"]:focus,
                            .form-style-6 textarea:focus,
                            .form-style-6 select:focus {
                                box-shadow: 0 0 5px #43D1AF;
                                padding: 3%;
                                border: 1px solid #43D1AF;
                            }

                            .form-style-6 input[type="submit"],
                            .form-style-6 input[type="button"] {
                                box-sizing: border-box;
                                -webkit-box-sizing: border-box;
                                -moz-box-sizing: border-box;
                                width: 100%;
                                padding: 3%;
                                background: #43D1AF;
                                border-bottom: 2px solid #30C29E;
                                border-top-style: none;
                                border-right-style: none;
                                border-left-style: none;
                                color: #fff;
                            }

                            .form-style-6 input[type="submit"]:hover,
                            .form-style-6 input[type="button"]:hover {
                                background: #2EBC99;
                            }
                        </style>
                        <?php
                        exit;
                    }
                }
            }
        }
    }

    function sendSamlRequest($pluginconfig)
    {
        $get = JFactory::getApplication()->input->get->getArray();

        $siteUrl = JURI::root();
        $sp_base_url = $siteUrl;

        $result = new Mo_saml_Local_Util();
        $result = $result->_load_db_values('#__miniorange_saml_config');

        $sp_entity_id = isset($result['sp_entity_id']) ? $result['sp_entity_id'] : "";
        if ($sp_entity_id == '') {
            $sp_entity_id = $siteUrl . 'plugins/authentication/miniorangesaml';
        }

        if (!defined('_JDEFINES')) {
            require_once JPATH_BASE . '/includes/defines.php';
        }
        require_once JPATH_BASE . '/includes/framework.php';

        // Instantiate the application.
        $app = JFactory::getApplication('site');

        $jCmsVersion = SAML_Utilities::getJoomlaCmsVersion();
        $jCmsVersion = substr($jCmsVersion, 0, 3);

        if ($jCmsVersion < 4.0) {
            $app->initialise();
        }
        $login_url = $sp_base_url;

        $user = JFactory::getUser(); #Get current user info

        $acsUrl = $sp_base_url . '?morequest=acs';
        $ssoUrl = $pluginconfig['single_signon_service_url'];
        $sso_binding_type = $pluginconfig['binding'];
        $name_id_format = $pluginconfig['name_id_format'];

        $sendRelayState = $this->getRelayState($sp_base_url, $_REQUEST);

        $samlRequest = SAML_Utilities::createAuthnRequest($acsUrl, $sp_entity_id, $ssoUrl, $name_id_format, 'false', $sso_binding_type);

        if (isset($get['q'])) {
            if ($get['q'] == "sso") {
                $this->mo_saml_show_SAML_log($samlRequest, "displaySAMLRequest");
            }
        }

        $samlRequest = SAML_Utilities::samlRequestBind($samlRequest, $sso_binding_type);
        $this->sendSamlRequestByBindingType($samlRequest, $sso_binding_type, $sendRelayState, $ssoUrl);
    }

    function mo_saml_show_SAML_log($samlRequestResponceXML, $type)
    {
        header("Content-Type: text/html");
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->loadXML($samlRequestResponceXML);

        if ($type == 'displaySAMLRequest')
            $show_value = 'SAML Request';
        else
            $show_value = 'SAML Response';
        $out = $doc->saveXML();

        $out1 = htmlentities($out);
        $out1 = rtrim($out1);
        $xml = simplexml_load_string($out);
        $json = json_encode($xml);
        $array = json_decode($json);

        echo '<link rel="stylesheet" type="text/css" href="' . JURI::root() . 'media/com_miniorange_saml/css/style_settings.css"/>
            <div class="mo-display-logs" >
                <p type="text"   id="SAML_type">' . $show_value . '</p>
            </div>
            <div type="text" id="SAML_display" class="mo-display-block">
                <pre class=\'brush: xml;\'>' . $out1 . '</pre>
            </div><br>
            <div style="margin:3%;display:block;text-align:center;">
                <div style="margin:3%;display:block;text-align:center;" ></div>
                <button id="copy" onclick="copyDivToClipboard()" class="mo_saml_logs_css">Copy</button>&nbsp;
                <input id="dwn-btn" class="mo_saml_download_css "type="button" value="Download">
            </div>
            </div>';

        ob_end_flush(); ?>

        <script>

            function copyDivToClipboard() {
                var aux = document.createElement("input");
                aux.setAttribute("value", document.getElementById("SAML_display").textContent);
                document.body.appendChild(aux);
                aux.select();
                document.execCommand("copy");
                document.body.removeChild(aux);
                document.getElementById('copy').textContent = "Copied";
                document.getElementById('copy').style.background = "grey";
                window.getSelection().selectAllChildren(document.getElementById("SAML_display"));
            }

            function download(filename, text) {
                var element = document.createElement('a');
                element.setAttribute('href', 'data:Application/octet-stream;charset=utf-8,' + encodeURIComponent(text));
                element.setAttribute('download', filename);

                element.style.display = 'none';
                document.body.appendChild(element);

                element.click();

                document.body.removeChild(element);
            }

            document.getElementById("dwn-btn").addEventListener("click", function () {

                var filename = document.getElementById("SAML_type").textContent + ".xml";
                var node = document.getElementById("SAML_display");
                htmlContent = node.innerHTML;
                text = node.textContent;
                console.log(text);
                download(filename, text);
            }, false);

        </script>
        <?php
        exit;
    }

    function sendSamlRequestByBindingType($samlRequest, $sso_binding_type, $sendRelayState, $ssoUrl)
    {

        if (empty($sso_binding_type) || $sso_binding_type == 'HttpRedirect') {

            $samlRequest = "SAMLRequest=" . $samlRequest . "&RelayState=" . $sendRelayState;

            $param = array('type' => 'private');

            $redirect = $ssoUrl;
            if (strpos($ssoUrl, '?') !== false) {
                $redirect .= '&';
            } else {
                $redirect .= '?';
            }
            $redirect .= $samlRequest;

            header('Location: ' . $redirect);
            exit();
        }
    }

    function getRelayState($sp_base_url, $request)
    {

        $sendRelayState = $sp_base_url;

        if (isset($request['q'])) {
            if ($request['q'] == 'test_config') {
                $sendRelayState = 'testValidate';
            }
        } else if (isset($request['RelayState']) && $request['RelayState'] != '/' && $request['RelayState'] != '') {
            $sendRelayState = $request['RelayState'];
        } else if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != '') {
            $sendRelayState = $_SERVER['HTTP_REFERER'];
        }


        return $sendRelayState;
    }

    function getSamlResponse($pluginconfig)
    {
        $post = JFactory::getApplication()->input->post->getArray();

        if (!defined('_JDEFINES')) {
            require_once JPATH_BASE . '/includes/defines.php';
        }
        require_once JPATH_BASE . '/includes/framework.php';

        $authBase = JPATH_BASE . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'authentication' . DIRECTORY_SEPARATOR . 'miniorangesaml';
        include_once $authBase . DIRECTORY_SEPARATOR . 'saml2' . DIRECTORY_SEPARATOR . 'Response.php';

        jimport('miniorangesamlplugin.utility.encryption');
        jimport('joomla.application.application');
        jimport('joomla.html.parameter');

        $sp_base_url = "";
        $sp_entity_id = "";
        if (isset($pluginconfig['sp_base_url'])) {
            $sp_base_url = $pluginconfig['sp_base_url'];
            $sp_entity_id = $pluginconfig['sp_entity_id'];
        }

        if (isset($pluginconfig['sp_entity_id'])) {

            $sp_entity_id = $pluginconfig['sp_entity_id'];

        }

        $siteUrl = JURI::root();

        if (empty($sp_base_url))
            $sp_base_url = $siteUrl;

        if (empty($sp_entity_id))
            $sp_entity_id = $siteUrl . 'plugins/authentication/miniorangesaml';

        $app = JFactory::getApplication('site');

        $jCmsVersion = SAML_Utilities::getJoomlaCmsVersion();
        $jCmsVersion = substr($jCmsVersion, 0, 3);

        if ($jCmsVersion < 4.0) {
            $app->initialise();
        }
        $get = JFactory::getApplication()->input->get->getArray();

        if (array_key_exists('SAMLResponse', $post)) {

            $this->validateSamlResponse($post, $sp_base_url, $sp_entity_id, $pluginconfig, $app);
        } else {
            throw new Exception ('Missing SAMLRequest or SAMLResponse parameter.');
        }
    }

    function validateSamlResponse($post, $sp_base_url, $sp_entity_id, $attribute, $app)
    {
        $samlResponse = $post ['SAMLResponse'];

        if (array_key_exists('RelayState', $_REQUEST) && ($_REQUEST['RelayState'] != '') && ($_REQUEST['RelayState'] != '/')) {
            $relayState = $_REQUEST ['RelayState'];
        } else {
            $relayState = $sp_base_url;
        }

        $samlResponse = base64_decode($samlResponse);

        $document = new DOMDocument ();
        $document->loadXML($samlResponse);
        $samlResponseXml = $document->firstChild;
        $doc = $document->documentElement;

        $xpath = new DOMXpath($document);
        $xpath->registerNamespace('samlp', 'urn:oasis:names:tc:SAML:2.0:protocol');
        $xpath->registerNamespace('saml', 'urn:oasis:names:tc:SAML:2.0:assertion');

        $status = $xpath->query('/samlp:Response/samlp:Status/samlp:StatusCode', $doc);
        $statusString = $status->item(0)->getAttribute('Value');
        $statusChildString = '';
        if ($status->item(0)->firstChild !== null) {
            $statusChildString = $status->item(0)->firstChild->getAttribute('Value');
        }

        $stat = explode(":", $statusString);
        $status = $stat[7];

        if ($relayState == "response") {

            $this->mo_saml_show_SAML_log($samlResponse, "displaySAMLResponse");
        }

        if ($status != "Success") {
            if (!empty($statusChildString)) {
                $stat = explode(":", $statusChildString);
                $status = $stat[7];
            }
            $this->show_error_message($status, $relayState);
        }


        $acsUrl = $sp_base_url . '?morequest=acs';

        $certFromPlugin = $attribute['certificate'];
        if (!empty($certFromPlugin)) {
            $certFromPlugin = SAML_Utilities::sanitize_certificate($certFromPlugin);
        }
        $certfpFromPlugin = XMLSecurityKey::getRawThumbprint($certFromPlugin);
        $samlResponse = new SAML2_Response ($samlResponseXml);

        $responseSignatureData = $samlResponse->getSignatureData();


        $assertionSignatureData = current($samlResponse->getAssertions())->getSignatureData();
        /* convert to UTF-8 character encoding */
        $certfpFromPlugin = iconv("UTF-8", "CP1252//IGNORE", $certfpFromPlugin);

        /* remove whitespaces */
        $certfpFromPlugin = preg_replace('/\s+/', '', $certfpFromPlugin);

        // /* Validate signature */
        if (!empty($certfpFromPlugin)) {
            if (!empty($responseSignatureData)) {
                $validSignature = SAML_Utilities::processResponse($acsUrl, $certfpFromPlugin, $responseSignatureData, $samlResponse, $certFromPlugin, $relayState);
                if ($validSignature === FALSE) {
                    echo "Invalid signature in the SAML Response.<br><br>";
                    exit;
                }
            }

            if (!empty($assertionSignatureData)) {
                $validSignature = SAML_Utilities::processResponse($acsUrl, $certfpFromPlugin, $assertionSignatureData, $samlResponse, $certFromPlugin, $relayState);
                if ($validSignature === FALSE) {
                    echo "Invalid signature in the SAML Assertion.<br><br>";
                    exit;
                }
            }
        }

        $db = JFactory::getDbo();
        $appdata = new Mo_saml_Local_Util();
        $appdata = $appdata->_load_db_values('#__miniorange_saml_config');

        if ($appdata['userslim'] < $appdata['usrlmt'])
            $userslimitexeed = 0;
        else
            $userslimitexeed = 1;


        // verify the issuer and audience from saml response
        $issuer = $attribute['idp_entity_id'];

        SAML_Utilities::validateIssuerAndAudience($samlResponse, $sp_entity_id, $issuer, $relayState);

        $username = current(current($samlResponse->getAssertions())->getNameId());
        $attrs = current($samlResponse->getAssertions())->getAttributes();
        $attrs ['NameID'] = current(current($samlResponse->getAssertions())->getNameId());

        if ($relayState == 'testValidate') {
            SAML_Utilities::mo_saml_show_test_result($username, $attrs, $sp_base_url);
        }

        $sessionIndex = current($samlResponse->getAssertions())->getSessionIndex();
        $attrs ['ASSERTION_SESSION_INDEX'] = $sessionIndex;

        $email = $username;
        $name = '';
        $saml_groups = '';

        $NameMapping = (string)$attribute['name'];
        $usernameMapping = $attribute['username'];
        $mailMapping = $attribute['email'];

        if (!empty($usernameMapping) && isset($attrs[$usernameMapping]) && !empty($attrs[$usernameMapping])) {
            $username = $attrs[$usernameMapping];
            if (is_array($username))
                $username = $username[0];
        }
        if (!empty($mailMapping) && isset($attrs[$mailMapping]) && !empty($attrs[$mailMapping])) {
            $email = $attrs[$mailMapping];
            if (is_array($email))
                $email = $email[0];
        }

        if (!empty($NameMapping) && isset($attrs[$NameMapping]) && !empty($attrs[$NameMapping])) {
            $name = $attrs[$NameMapping];

        }
        if (is_array($name)) {
            $name = $name[0];
        }

        if (!empty($groupsMapping) && isset($attrs[$groupsMapping]) && !empty($attrs[$groupsMapping])) {
            $saml_groups = $attrs[$groupsMapping];
        } else {
            $saml_groups = array();
        }

        if (isset($attribute['enable_email']) && $attribute['enable_email'] == 0) {
            $matcher = 'username';
        } else {
            $matcher = 'email';
        }


        $result = SAML_Utilities::get_user_from_joomla($matcher, $username, $email);

        $login_url = isset($relayState) ? $relayState : $sp_base_url;

        if ($result) {
            $this->loginCurrentUser($result, $attrs, $login_url, $name, $username, $email, $matcher, $app);
        } else if ($userslimitexeed) {
            echo '<div style="font-family:Calibri;padding:0 3%;">';
            echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ERROR</div>
                              <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>Error: </strong>New User could not be created.</p>
                              <p><strong>Cause:</strong>Current version of the plugin reached auto user creation limit. Please upgrade to Standard or Premium or enterprise version of the plugin to creare more users and experinse more features.</p></div></div><br>';
            $home_link = JURI::root();
            echo '<p align="center"><a href=' . $home_link . ' type="button" style="color: white; background: #185b91; padding: 10px 20px;">Back to Website</a><p>';
            exit;
        } else {
            $this->RegisterCurrentUser($attrs, $login_url, $name, $username, $email, $saml_groups, $matcher, $app);
        }
    }

    function loginCurrentUser($result, $attrs, $login_url, $name, $username, $email, $matcher, $app)
    {
        $user = JUser::getInstance($result->id);
        SAML_Utilities::updateCurrentUserName($user->id, $name);

        $role_mapping = (new Mo_saml_Local_Util)->_load_db_values('#__miniorange_saml_role_mapping');

        if (isset($role_mapping['enable_saml_role_mapping'])) {
            if ($role_mapping['enable_saml_role_mapping'] == 1)
                $enable_rolemapping = 1;
            else
                $enable_rolemapping = 0;

        } else {
            $enable_rolemapping = 0;
        }

        jimport('joomla.user.helper');
        if ($enable_rolemapping) {
            if (isset($role_mapping['mapping_value_default']))
                $default_group = $role_mapping['mapping_value_default'];
            JUserHelper::addUserToGroup($user->id, $default_group);

            foreach ($user->groups as $existinggroup) {
                if ($existinggroup != $default_group && $existinggroup != 7 && $existinggroup != 8)
                    JUserHelper::removeUserFromGroup($user->id, $existinggroup);
            }
        }

        $session = JFactory::getSession(); #Get current session vars
        // Register the needed session variables
        $session->set('user', $user);
        $session->set('MO_SAML_NAMEID', isset($attrs['NAME_ID']) ? $attrs['NAME_ID'] : '');
        $session->set('MO_SAML_SESSION_INDEX', isset($attrs['ASSERTION_SESSION_INDEX']) ? $attrs['ASSERTION_SESSION_INDEX'] : '');

        $app->checkSession();
        $sessionId = $session->getId();
        SAML_Utilities::updateUsernameToSessionId($user->id, $user->username, $sessionId);
        $user->setLastVisit();
        $app->redirect(urldecode($login_url));
    }

    function show_error_message($statusCode, $relayState)
    {
        if ($relayState == 'testValidate') {

            echo '<div style="font-family:Calibri;padding:0 3%;">';
            echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ERROR</div>
            <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>Error: </strong> Invalid SAML Response Status.</p>
            <p><strong>Causes</strong>: Identity Provider has sent \'' . $statusCode . '\' status code in SAML Response. </p>
                            <p><strong>Reason</strong>: ' . $this->get_status_message($statusCode) . '</p><br>
            </div>

            <div style="margin:3%;display:block;text-align:center;">
            <div style="margin:3%;display:block;text-align:center;"><input style="padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="button" value="Done" onClick="self.close();"></div>';
            exit;
        } else {
            if ($statusCode == 'RequestDenied') {
                echo 'You are not allowed to login into the site. Please contact your Administrator.';
                exit;
            } else {
                echo 'We could not sign you in. Please contact your Administrator.';
                exit;
            }

        }
    }

    function get_status_message($statusCode)
    {
        switch ($statusCode) {
            case 'RequestDenied':
                return 'You are not allowed to login into the site. Please contact your Administrator.';
                break;
            case 'Requester':
                return 'The request could not be performed due to an error on the part of the requester.';
                break;
            case 'Responder':
                return 'The request could not be performed due to an error on the part of the SAML responder or SAML authority.';
                break;
            case 'VersionMismatch':
                return 'The SAML responder could not process the request because the version of the request message was incorrect.';
                break;
            default:
                return 'Unknown';
        }
    }

    function generateMetadata($attribute, $download = false)
    {
        $sp_base_url = "";
        $sp_entity_id = "";
        $name_id_format = "";


        if (isset($attribute['sp_base_url'])) {
            $sp_base_url = $attribute['sp_base_url'];
            $sp_entity_id = $attribute['sp_entity_id'];
            $name_id_format = $attribute['name_id_format'];
        }

        if (isset($attribute['sp_entity_id']))
            $sp_entity_id = $attribute['sp_entity_id'];

        $siteUrl = JURI::root();

        if (empty($sp_base_url))
            $sp_base_url = $siteUrl;

        if (empty($sp_entity_id))
            $sp_entity_id = $siteUrl . 'plugins/authentication/miniorangesaml';

        $acs_url = $sp_base_url . '?morequest=acs';
        $logout_url = $sp_base_url . 'index.php?option=com_users&amp;task=logout';

        $certificate = JPATH_BASE . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'authentication' . DIRECTORY_SEPARATOR . 'miniorangesaml' . DIRECTORY_SEPARATOR . 'saml2' . DIRECTORY_SEPARATOR . 'cert' . DIRECTORY_SEPARATOR . 'sp-certificate.crt';
        $certificate = file_get_contents($certificate);
        $certificate = SAML_Utilities::desanitize_certificate($certificate);

        if ($download) {
            header('Content-Disposition: attachment; filename="Metadata.xml"');
        } else {
            header('Content-Type: text/xml');
        }
        echo '<?xml version="1.0"?>
        <md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" validUntil="2022-08-04T23:59:59Z" cacheDuration="PT1446808792S" entityID="' . $sp_entity_id . '">
          <md:SPSSODescriptor AuthnRequestsSigned="false" WantAssertionsSigned="true" protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
            <md:NameIDFormat>urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified</md:NameIDFormat>
            
            <md:AssertionConsumerService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" Location="' . $acs_url . '" index="1"/>
          </md:SPSSODescriptor>
          <md:Organization>
            <md:OrganizationName xml:lang="en-US">miniOrange</md:OrganizationName>
            <md:OrganizationDisplayName xml:lang="en-US">miniOrange</md:OrganizationDisplayName>
            <md:OrganizationURL xml:lang="en-US">http://miniorange.com</md:OrganizationURL>
          </md:Organization>
          <md:ContactPerson contactType="technical">
            <md:GivenName>miniOrange</md:GivenName>
            <md:EmailAddress>info@xecurify.com</md:EmailAddress>
          </md:ContactPerson>
          <md:ContactPerson contactType="support">
            <md:GivenName>miniOrange</md:GivenName>
            <md:EmailAddress>info@xecurify.com</md:EmailAddress>
          </md:ContactPerson>
        </md:EntityDescriptor>';
        exit();
    }

    function RegisterCurrentUser($attrs, $login_url, $name, $username, $email, $saml_groups, $matcher, $app)
    {
        $role_mapping = (new Mo_saml_Local_Util)->_load_db_values('#__miniorange_saml_role_mapping');
        $enable_saml_role_mapping = 0;
        if (isset($role_mapping['enable_saml_role_mapping']))
            $enable_saml_role_mapping = json_decode($role_mapping['enable_saml_role_mapping']);

        // user data
        $data['name'] = (isset($name) && !empty($name)) ? $name : $username;
        $data['username'] = $username;
        $data['email'] = $data['email1'] = $data['email2'] = JStringPunycode::emailToPunycode($email);
        $data['password'] = $data['password1'] = $data['password2'] = JUserHelper::genRandomPassword();
        $data['activation'] = '0';
        $data['block'] = '0';

        if ($enable_saml_role_mapping == 1)
        {
            $data['groups'][] = isset($role_mapping['mapping_value_default']) ? $role_mapping['mapping_value_default'] : 2;
        }
        else
        {
            $data['groups'][] = 2;
        }

        $result = (new Mo_saml_Local_Util)->_load_db_values('#__miniorange_saml_config');

        $usrlim = $result['userslim'] + 1;

        $database_name = '#__miniorange_saml_config';
        $updatefieldsarray = array(
            'userslim' => $usrlim,
        );
        $result = new Mo_saml_Local_Util();
        $result->generic_update_query($database_name, $updatefieldsarray);

        // Get the model and validate the data.
        jimport('joomla.application.component.model');

        if (!defined('JPATH_COMPONENT')) {
            define('JPATH_COMPONENT', JPATH_BASE . '/components/');
        }

        $user = new JUser;
        //Write to database
        if (!$user->bind($data)) {
            throw new Exception("Could not bind data. Error: " . $user->getError());
        }

        $result = (new Mo_saml_Local_Util)->_load_db_values('#__miniorange_saml_config');
        $uCLmt = hexdec('1CB') - hexdec('1BF');

        if ($result['userslim'] < $uCLmt)
            $blkatocr = 0;
        else
            $blkatocr = 1;


        if ($blkatocr) {
            $home_link = JURI::root();
            echo '<div style="font-family:Calibri;padding:0 3%;">';
            echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ERROR</div>
                              <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>Error: </strong>New User could not be created.</p>
                              <p><strong>Cause:</strong>Current version of the plugin reached auto user creation limit. Please upgrade to Standard or Premium or enterprise version of the plugin to creare more users and experinse more features.</p></div></div><br>';
            echo '<p align="center"><a href=' . $home_link . ' type="button" style="color: white; background: #185b91; padding: 10px 20px;">Back to Website</a><p>';
            exit;
        }

        if (!$user->save()) {
            $siteUrl = JURI::root();
            ob_end_clean();
            $siteUrl = $siteUrl . '/plugins/authentication/miniorangesaml';
            echo '<div style="font-family:Calibri;padding:0 3%;">';
            echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;">
            <img style="width:15px;"src="' . $siteUrl . 'images/wrong.png"> ERROR</div>
            <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p><strong>Error: </strong>Could not save user. ' . $user->getError() . '</p>
            <p>You are receiving this error because your email address is invalid.</p>
            <p>Please make sure the Name-ID format should be emailAddress in <b>Service Provider Setup</b> tab of the plugin.</p>
            <p>If you have checked your email address and the error still persists then please report following error to your System Administrator:
                <ul>
                <li>Attribute name for e-mail should be NAME_ID only.</li>
                <li>Please change the attribute name in your IdP.</li>
                 <li>You can Upgrade to <b>Premium</b> version if you wish to create custom attribute name for e-mail.</li></ul>
            </p>
           </div>
                
                <div style="text-align:center;"><a href="index.php" type="button" style="color: white; background: #185b91; padding: 10px 20px;" target="_blank">Back to Home</a></div>';
            exit;

        }
        $result = SAML_Utilities::get_user_from_joomla($matcher, $username, $email);
        if ($result) {
            $user = JUser::getInstance($result->id);

            $session = JFactory::getSession(); #Get current session vars
            // Register the needed session variables
            $session->set('user', $user);
            if (isset($attrs['NAME_ID']))
                $session->set('MO_SAML_NAMEID', $attrs['NAME_ID']);
            $session->set('MO_SAML_SESSION_INDEX', $attrs['ASSERTION_SESSION_INDEX']);

            $app->checkSession();
            $sessionId = $session->getId();
            SAML_Utilities::updateUsernameToSessionId($user->id, $user->username, $sessionId);

            /* Update Last Visit Date */
            $user->setLastVisit();
            $app->redirect(urldecode($login_url));
        }

    }

    function mo_get_version_informations()
    {
        $array_version = array();
        $array_version["PHP_version"] = phpversion();
        $array_version["OPEN_SSL"] = $this->mo_saml_is_openssl_installed();
        $array_version["CURL"] = $this->mo_saml_is_curl_installed();
        $array_version["ICONV"] = $this->mo_saml_is_iconv_installed();
        $array_version["DOM"] = $this->mo_saml_is_dom_installed();
        return $array_version;
    }

    function mo_saml_is_openssl_installed()
    {
        if (in_array('openssl', get_loaded_extensions())) {
            return 1;
        } else {
            return 0;
        }
    }

    function mo_saml_is_curl_installed()
    {
        if (in_array('curl', get_loaded_extensions())) {
            return 1;
        } else {
            return 0;
        }
    }

    function mo_saml_is_iconv_installed()
    {
        if (in_array('iconv', get_loaded_extensions())) {
            return 1;
        } else {
            return 0;
        }
    }

    function mo_saml_is_dom_installed()
    {
        if (in_array('dom', get_loaded_extensions())) {
            return 1;
        } else {
            return 0;
        }
    }

}