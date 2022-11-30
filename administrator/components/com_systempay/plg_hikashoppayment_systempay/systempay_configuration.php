<?php
/**
 * Copyright © Lyra Network.
 * This file is part of Systempay plugin for HikaShop. See COPYING.md for license details.
 *
 * @author    Lyra Network (https://www.lyra.com/)
 * @copyright Lyra Network
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL v3)
 */

defined('_JEXEC') or die('Restricted access');

$params = $this->element->payment_params;

if (! class_exists('com_systempayInstallerScript')) {
    require_once rtrim(JPATH_ADMINISTRATOR, DS) . DS . 'components' . DS . 'com_systempay' . DS . 'script.install.php';
}

$plugin_features = com_systempayInstallerScript::$plugin_features;

$displayKeyTest = '';
$disableCtxMode = '';

if ($plugin_features['qualif']) {
    $displayKeyTest = ' style="display: none;"';
    $disableCtxMode = ' disabled="disabled"';
}

$signAlgoDesc = JText::_('SYSTEMPAY_SIGN_ALGO_DESC');

if ($plugin_features['shatwo']) {
    // HMAC-SHA-256 already available, update field description.
    $signAlgoDesc = preg_replace('#<br /><b>[^<>]+</b>#', '', $signAlgoDesc);
}

// Get documentation links.
$docs = '' ;
$displayDoc = '';

$filenames = glob(rtrim(JPATH_ADMINISTRATOR, DS) . DS . 'components' . DS. 'com_systempay' . DS . 'installation_doc/Systempay_HikaShop_2.x-4.x_v2.1_*.pdf');

if (empty($filenames)) { // Hide if there are no doc files.
    $displayDoc = ' style="display: none;"';
} else {
    $languages = array(
        'fr' => 'Français',
        'en' => 'English',
        'es' => 'Español',
        'de' => 'Deutsch'
        // Complete when other languages are managed.
    );

    foreach ($filenames as $filename) {
        $base_filename = basename($filename, '.pdf');
        $lang = substr($base_filename, -2); // Extract language code.

        $docs .= '<a style="margin-left: 10px; text-decoration: none; text-transform: uppercase;" href="' . HIKASHOP_LIVE . 'administrator' . DS . 'components' . DS. 'com_systempay' . DS .
            'installation_doc/' . $base_filename . '.pdf" target="_blank">' . $languages[$lang] . '</a>';
    };
}
?>

<!------------------------------- Module information ------------------------------------------>
<tr>
    <td colspan="2">
        <fieldset>
            <legend><?php echo JText::_('SYSTEMPAY_MODULE_INFORMATION'); ?></legend>

            <table>
                <tr>
                    <td class="key">
                        <label><?php echo JText::_('SYSTEMPAY_DEVELOPED_BY'); ?></label>
                    </td>
                    <td>
                        <a href="https://www.lyra.com/" target="_blank">Lyra Network</a>
                    </td>
                </tr>

                <tr>
                    <td class="key">
                        <label><?php echo JText::_('SYSTEMPAY_CONTACT_EMAIL'); ?></label>
                    </td>
                    <td>
                        <a href="mailto:supportvad@lyra-network.com">supportvad@lyra-network.com</a>
                    </td>
                </tr>

                <tr>
                    <td class="key">
                        <label><?php echo JText::_('SYSTEMPAY_CONTRIB_VERSION'); ?></label>
                    </td>
                    <td>
                        <label>2.1.5</label>
                    </td>
                </tr>

                <tr>
                    <td class="key">
                        <label><?php echo JText::_('SYSTEMPAY_GATEWAY_VERSION'); ?></label>
                    </td>
                    <td>
                        <label>V2</label>
                    </td>
                </tr>

                <tr <?php echo $displayDoc; ?>>
                    <td colspan="2">
                       <label style="font-size: 12px; font-weight: bold; color: red; cursor: auto !important; text-transform: uppercase;">
                           <?php echo JText::_('SYSTEMPAY_DOCUMENTATION_TEXT'); ?>
                       </label>
                       <span><?php echo $docs; ?></span>
                    </td>
                </tr>
            </table>
        </fieldset>
    </td>
</tr>

<!------------------------------- Gateway access ------------------------------------------>
<tr>
    <td colspan="2">
        <fieldset>
            <legend><?php echo JText::_('SYSTEMPAY_GATEWAY_ACCESS'); ?></legend>

            <table>
                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="systempay_site_id"><?php echo JText::_('SYSTEMPAY_SITE_ID'); ?></label>
                    </td>
                    <td>
                        <input type="text" name="data[payment][payment_params][systempay_site_id]" value="<?php echo @$params->systempay_site_id; ?>" id="systempay_site_id" style="width: 120px;" autocomplete="off" /><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('SYSTEMPAY_SITE_ID_DESC'); ?></span>
                    </td>
                </tr>

                <tr <?php echo $displayKeyTest; ?> >
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="systempay_key_test"><?php echo JText::_('SYSTEMPAY_KEY_TEST'); ?></label>
                    </td>
                    <td>
                        <input type="text" name="data[payment][payment_params][systempay_key_test]" value="<?php echo @$params->systempay_key_test; ?>" id="systempay_key_test" style="width: 120px;" autocomplete="off" /><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('SYSTEMPAY_KEY_TEST_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="systempay_key_prod"><?php echo JText::_('SYSTEMPAY_KEY_PROD'); ?></label>
                    </td>
                    <td>
                        <input type="text" name="data[payment][payment_params][systempay_key_prod]" value="<?php echo @$params->systempay_key_prod; ?>" id="systempay_key_prod" style="width: 120px;" autocomplete="off" /><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('SYSTEMPAY_KEY_PROD_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="systempay_ctx_mode"><?php echo JText::_('SYSTEMPAY_CTX_MODE'); ?></label>
                    </td>
                    <td>
                        <select name="data[payment][payment_params][systempay_ctx_mode]" class="inputbox" id="systempay_ctx_mode" style="width: 122px;" <?php echo $disableCtxMode; ?> >
                            <option <?php if (@$params->systempay_ctx_mode === 'TEST') echo 'selected="selected"'; ?> value="TEST"><?php echo JText::_('SYSTEMPAY_CTX_MODE_TEST'); ?></option>
                            <option <?php if (@$params->systempay_ctx_mode === 'PRODUCTION') echo 'selected="selected"'; ?> value="PRODUCTION"><?php echo JText::_('SYSTEMPAY_CTX_MODE_PROD'); ?></option>
                        </select><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('SYSTEMPAY_CTX_MODE_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="systempay_sign_algo"><?php echo JText::_('SYSTEMPAY_SIGN_ALGO'); ?></label>
                    </td>
                    <td>
                        <select name="data[payment][payment_params][systempay_sign_algo]" class="inputbox" id="systempay_sign_algo" style="width: 122px;" >
                            <option <?php if (@$params->systempay_sign_algo === 'SHA-1') echo 'selected="selected"'; ?> value="SHA-1">SHA-1</option>
                            <option <?php if (@$params->systempay_sign_algo === 'SHA-256') echo 'selected="selected"'; ?> value="SHA-256">HMAC-SHA-256</option>
                        </select><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo $signAlgoDesc; ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label><?php echo JText::_('SYSTEMPAY_IPN_URL'); ?></label>
                    </td>
                    <td>
                        <label><span style="font-weight: bold;"><?php echo HIKASHOP_LIVE . 'index.php?option=com_hikashop&amp;ctrl=checkout&amp;task=notify&amp;notif_payment=systempay&amp;tmpl=component'; ?></span></label><br />
                        <img src="<?php echo HIKASHOP_LIVE . 'administrator' . DS . 'components' . DS . 'com_systempay' . DS  ; ?>images/warn.png">
                        <span style="font-size: 12px; font-style: italic; color: red; display: inline-block;"><?php echo JText::_('SYSTEMPAY_IPN_URL_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="systempay_platform_url"><?php echo JText::_('SYSTEMPAY_GATEWAY_URL'); ?></label>
                    </td>
                    <td>
                        <input type="text" name="data[payment][payment_params][systempay_platform_url]" value="<?php echo @$params->systempay_platform_url; ?>" id="systempay_platform_url" style="width: 300px;" /><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('SYSTEMPAY_GATEWAY_URL_DESC'); ?></span>
                    </td>
                </tr>
            </table>
        </fieldset>
    </td>
</tr>

<!------------------------------- Payment page ------------------------------------------>
<tr>
    <td colspan="2">
        <fieldset>
            <legend><?php echo JText::_('SYSTEMPAY_PAYMENT_PAGE'); ?></legend>

            <table>
                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="systempay_language"><?php echo JText::_('SYSTEMPAY_LANGUAGE'); ?></label>
                    </td>
                    <td>
                        <select name="data[payment][payment_params][systempay_language]" class="inputbox" id="systempay_language" style="width: 122px;" >
                            <?php
                            foreach (SystempayApi::getSupportedLanguages() as $code => $label) {
                                $selected = (@$params->systempay_language === $code) ? ' selected="selected"' : '';
                                echo '<option' . $selected . ' value="'. $code . '">' . JText::_('SYSTEMPAY_LANGUAGE_' . strtoupper($label)) . '</option>';
                            }
                            ?>
                        </select><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('SYSTEMPAY_LANGUAGE_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="systempay_available_languages"><?php echo JText::_('SYSTEMPAY_AVAILABLE_LANGUAGES'); ?></label>
                    </td>
                    <td>
                        <select name="data[payment][payment_params][systempay_available_languages][]" class="inputbox" multiple="multiple" size="8" id="systempay_available_languages" style="width: 122px;" >
                            <?php
                            $langs = @$params->systempay_available_languages ? explode(';', $params->systempay_available_languages) : array();
                            foreach (SystempayApi::getSupportedLanguages() as $code => $label) {
                                $selected = in_array($code, $langs) ? ' selected="selected"' : '';
                                echo '<option' . $selected . ' value="'. $code . '">' . JText::_('SYSTEMPAY_LANGUAGE_' . strtoupper($label)) . '</option>';
                            }
                            ?>
                        </select><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('SYSTEMPAY_AVAILABLE_LANGUAGES_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="systempay_capture_delay"><?php echo JText::_('SYSTEMPAY_CAPTURE_DELAY'); ?></label>
                    </td>
                    <td>
                        <input type="text" name="data[payment][payment_params][systempay_capture_delay]" value="<?php echo @$params->systempay_capture_delay; ?>" id="systempay_capture_delay" style="width: 120px;" /><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('SYSTEMPAY_CAPTURE_DELAY_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="systempay_validation_mode"><?php echo JText::_('SYSTEMPAY_VALIDATION_MODE'); ?></label>
                    </td>
                    <td>
                        <select name="data[payment][payment_params][systempay_validation_mode]" class="inputbox" id="systempay_validation_mode" style="width: 122px;" >
                            <option <?php if (@$params->systempay_validation_mode === '')  echo 'selected="selected"'; ?>value=''><?php echo JText::_('SYSTEMPAY_MODE_DEFAULT'); ?></option>
                            <option <?php if (@$params->systempay_validation_mode === '0') echo 'selected="selected"'; ?>value='0'><?php echo JText::_('SYSTEMPAY_MODE_AUTOMATIC'); ?></option>
                            <option <?php if (@$params->systempay_validation_mode === '1') echo 'selected="selected"'; ?>value='1'><?php echo JText::_('SYSTEMPAY_MODE_MANUAL'); ?></option>
                        </select><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('SYSTEMPAY_VALIDATION_MODE_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="systempay_payment_cards"><?php echo JText::_('SYSTEMPAY_PAYMENT_CARDS'); ?></label>
                    </td>
                    <td>
                        <select name="data[payment][payment_params][systempay_payment_cards][]" class="inputbox" multiple="multiple" size="8" id="systempay_payment_cards" style="width: 122px;" >
                            <?php
                            $cards = @$params->systempay_payment_cards ? explode(';', $params->systempay_payment_cards) : array();
                            foreach (SystempayApi::getSupportedCardTypes() as $code => $label) {
                                $selected = in_array($code, $cards) ? ' selected="selected"' : '';
                                echo '<option' . $selected . ' value="'. $code . '">' . $label . '</option>';
                            }
                            ?>
                        </select><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('SYSTEMPAY_PAYMENT_CARDS_DESC'); ?></span>
                    </td>
                </tr>
            </table>
        </fieldset>
    </td>
</tr>

<!------------------------------- Selective 3DS ------------------------------------------>
<tr>
    <td colspan="2">
        <fieldset>
            <legend><?php echo JText::_('SYSTEMPAY_SELECTIVE_3DS'); ?></legend>

            <table>
                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="systempay_threeds_amount_min"><?php echo JText::_('SYSTEMPAY_THREEDS_AMOUNT_MIN'); ?></label>
                    </td>
                    <td>
                        <input type="text" name="data[payment][payment_params][systempay_threeds_amount_min]" value="<?php echo @$params->systempay_threeds_amount_min; ?>" id="systempay_threeds_amount_min" style="width: 120px;" /><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('SYSTEMPAY_THREEDS_AMOUNT_MIN_DESC'); ?></span>
                    </td>
                </tr>
            </table>
        </fieldset>
    </td>
</tr>

<!------------------------------- Return to shop ------------------------------------------>
<tr>
    <td colspan="2">
        <fieldset>
            <legend><?php echo JText::_('SYSTEMPAY_RETURN_TO_SHOP'); ?></legend>

            <table>
                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="systempay_redirect_enabled"><?php echo JText::_('SYSTEMPAY_REDIRECT_ENABLED'); ?></label>
                    </td>
                    <td>
                        <?php echo JHTML::_('select.booleanlist', 'data[payment][payment_params][systempay_redirect_enabled]' , '', @$params->systempay_redirect_enabled); ?><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('SYSTEMPAY_REDIRECT_ENABLED_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="systempay_redirect_success_timeout"><?php echo JText::_('SYSTEMPAY_REDIRECT_SUCCESS_TIMEOUT'); ?></label>
                    </td>
                    <td>
                        <input type="text" name="data[payment][payment_params][systempay_redirect_success_timeout]" value="<?php echo @$params->systempay_redirect_success_timeout; ?>" id="systempay_redirect_success_timeout" style="width: 120px;" /><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('SYSTEMPAY_REDIRECT_SUCCESS_TIMEOUT_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="systempay_redirect_success_message"><?php echo JText::_('SYSTEMPAY_REDIRECT_SUCCESS_MESSAGE'); ?></label>
                    </td>
                    <td>
                        <input type="text" name="data[payment][payment_params][systempay_redirect_success_message]" value="<?php echo @$params->systempay_redirect_success_message; ?>" id="systempay_redirect_success_message" style="width: 300px;" /><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('SYSTEMPAY_REDIRECT_SUCCESS_MESSAGE_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="systempay_redirect_error_timeout"><?php echo JText::_('SYSTEMPAY_REDIRECT_ERROR_TIMEOUT'); ?></label>
                    </td>
                    <td>
                        <input type="text" name="data[payment][payment_params][systempay_redirect_error_timeout]" value="<?php echo @$params->systempay_redirect_error_timeout; ?>" id="systempay_redirect_error_timeout" style="width: 120px;" /><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('SYSTEMPAY_REDIRECT_ERROR_TIMEOUT_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="systempay_redirect_error_message"><?php echo JText::_('SYSTEMPAY_REDIRECT_ERROR_MESSAGE'); ?></label>
                    </td>
                    <td>
                        <input type="text" name="data[payment][payment_params][systempay_redirect_error_message]" value="<?php echo @$params->systempay_redirect_error_message; ?>" id="systempay_redirect_error_message" style="width: 300px;" /><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('SYSTEMPAY_REDIRECT_ERROR_MESSAGE_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="systempay_return_mode"><?php echo JText::_('SYSTEMPAY_RETURN_MODE'); ?></label>
                    </td>
                    <td>
                        <select  name="data[payment][payment_params][systempay_return_mode]" class="inputbox" id="systempay_return_mode" style="width: 122px;" >
                            <option<?php if (@$params->systempay_return_mode === 'GET') echo ' selected="selected"'; ?> value='GET'>GET</option>
                            <option<?php if (@$params->systempay_return_mode === 'POST') echo ' selected="selected"'; ?> value='POST'>POST</option>
                        </select><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('SYSTEMPAY_RETURN_MODE_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="datapaymentpayment_paramssystempay_verified_status"><?php echo JText::_('SYSTEMPAY_VERIFIED_STATUS'); ?></label>
                    </td>

                    <td>
                        <?php echo $this->data['order_statuses']->display('data[payment][payment_params][systempay_verified_status]', @$params->systempay_verified_status, 'style="width: 122px;"'); ?><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('SYSTEMPAY_VERIFIED_STATUS_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="datapaymentpayment_paramssystempay_invalid_status"><?php echo JText::_('SYSTEMPAY_INVALID_STATUS'); ?></label>
                    </td>
                    <td>
                        <?php echo $this->data['order_statuses']->display('data[payment][payment_params][systempay_invalid_status]', @$params->systempay_invalid_status, 'style="width: 122px;"'); ?><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('SYSTEMPAY_INVALID_STATUS_DESC'); ?></span>
                    </td>
                </tr>
            </table>
        </fieldset>
    </td>
</tr>
