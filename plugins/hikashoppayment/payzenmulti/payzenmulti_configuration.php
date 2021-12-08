<?php
/**
 * Copyright © Lyra Network.
 * This file is part of PayZen plugin for HikaShop. See COPYING.md for license details.
 *
 * @author    Lyra Network (https://www.lyra.com/)
 * @copyright Lyra Network
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL v3)
 */

defined('_JEXEC') or die('Restricted access');

$params = $this->element->payment_params;

if (! class_exists('com_payzenInstallerScript')) {
    require_once rtrim(JPATH_ADMINISTRATOR, DS) . DS . 'components' . DS . 'com_payzen' . DS . 'script.install.php';
}

$plugin_features = com_payzenInstallerScript::$plugin_features;

$displayKeyTest = '';
$disableCtxMode = '';

if ($plugin_features['qualif']) {
    $displayKeyTest = ' style="display: none;"';
    $disableCtxMode = ' disabled="disabled"';
}

$signAlgoDesc = JText::_('PAYZENMULTI_SIGN_ALGO_DESC');

if ($plugin_features['shatwo']) {
    // HMAC-SHA-256 already available, update field description.
    $signAlgoDesc = preg_replace('#<br /><b>[^<>]+</b>#', '', $signAlgoDesc);
}

if ($plugin_features['restrictmulti']) {
    echo '<p style="background: none repeat scroll 0 0 #FFFFE0; border: 1px solid #E6DB55; font-size: 13px; margin: 0 0 20px; padding: 10px;">' .
         JText::_('PAYZENMULTI_RESTRICT_WARNING') . '</p>';
};

// Get documentation links.
$docs = '' ;
$displayDoc = '';

$filenames = glob(rtrim(JPATH_ADMINISTRATOR, DS) . DS . 'components' . DS. 'com_payzen' . DS . 'installation_doc/PayZen_HikaShop_2.x-3.x_v2.1_*.pdf');

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
        $lang = substr($base_filename, -2); // extract language code

        $docs .= '<a style="margin-left: 10px; text-decoration: none; text-transform: uppercase;" href="' . HIKASHOP_LIVE . 'administrator' . DS . 'components' . DS. 'com_payzen' . DS .
            'installation_doc/' . $base_filename . '.pdf" target="_blank">' . $languages[$lang] . '</a>';
    };
}
?>

<!------------------------------- Module information ------------------------------------------>
<tr>
    <td colspan="2">
        <fieldset>
            <legend><?php echo JText::_('PAYZENMULTI_MODULE_INFORMATION'); ?></legend>

            <table>
                <tr>
                    <td class="key">
                        <label><?php echo JText::_('PAYZENMULTI_DEVELOPED_BY'); ?></label>
                    </td>
                    <td>
                        <a href="https://www.lyra.com/" target="_blank">Lyra Network</a>
                    </td>
                </tr>

                <tr>
                    <td class="key">
                        <label><?php echo JText::_('PAYZENMULTI_CONTACT_EMAIL'); ?></label>
                    </td>
                    <td>
                        <a href="mailto:support@payzen.eu">support@payzen.eu</a>
                    </td>
                </tr>

                <tr>
                    <td class="key">
                        <label><?php echo JText::_('PAYZENMULTI_CONTRIB_VERSION'); ?></label>
                    </td>
                    <td>
                        <label>2.1.3</label>
                    </td>
                </tr>

                <tr>
                    <td class="key">
                        <label><?php echo JText::_('PAYZENMULTI_GATEWAY_VERSION'); ?></label>
                    </td>
                    <td>
                        <label>V2</label>
                    </td>
                </tr>

                <tr <?php echo $displayDoc; ?>>
                    <td colspan="2">
                       <label style="font-size: 12px; font-weight: bold; color: red; cursor: auto !important; text-transform: uppercase;">
                           <?php echo JText::_('PAYZENMULTI_DOCUMENTATION_TEXT'); ?>
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
            <legend><?php echo JText::_('PAYZENMULTI_GATEWAY_ACCESS'); ?></legend>

            <table>
                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="payzenmulti_site_id"><?php echo JText::_('PAYZENMULTI_SITE_ID'); ?></label>
                    </td>
                    <td>
                        <input type="text" name="data[payment][payment_params][payzenmulti_site_id]" value="<?php echo @$params->payzenmulti_site_id; ?>" id="payzenmulti_site_id" style="width: 120px;" autocomplete="off" /><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('PAYZENMULTI_SITE_ID_DESC'); ?></span>
                    </td>
                </tr>

                <tr <?php echo $displayKeyTest; ?> >
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="payzenmulti_key_test"><?php echo JText::_('PAYZENMULTI_KEY_TEST'); ?></label>
                    </td>
                    <td>
                        <input type="text" name="data[payment][payment_params][payzenmulti_key_test]" value="<?php echo @$params->payzenmulti_key_test; ?>" id="payzenmulti_key_test" style="width: 120px;" autocomplete="off" /><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('PAYZENMULTI_KEY_TEST_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="payzenmulti_key_prod"><?php echo JText::_('PAYZENMULTI_KEY_PROD'); ?></label>
                    </td>
                    <td>
                        <input type="text" name="data[payment][payment_params][payzenmulti_key_prod]" value="<?php echo @$params->payzenmulti_key_prod; ?>" id="payzenmulti_key_prod" style="width: 120px;" autocomplete="off" /><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('PAYZENMULTI_KEY_PROD_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="payzenmulti_ctx_mode"><?php echo JText::_('PAYZENMULTI_CTX_MODE'); ?></label>
                    </td>
                    <td>
                        <select name="data[payment][payment_params][payzenmulti_ctx_mode]" class="inputbox" id="payzenmulti_ctx_mode" style="width: 122px;" <?php echo $disableCtxMode; ?> >
                            <option <?php if (@$params->payzenmulti_ctx_mode === 'TEST') echo 'selected="selected"'; ?> value="TEST"><?php echo JText::_('PAYZENMULTI_CTX_MODE_TEST'); ?></option>
                            <option <?php if (@$params->payzenmulti_ctx_mode === 'PRODUCTION') echo 'selected="selected"'; ?> value="PRODUCTION"><?php echo JText::_('PAYZENMULTI_CTX_MODE_PROD'); ?></option>
                        </select><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('PAYZENMULTI_CTX_MODE_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="payzenmulti_sign_algo"><?php echo JText::_('PAYZENMULTI_SIGN_ALGO'); ?></label>
                    </td>
                    <td>
                        <select name="data[payment][payment_params][payzenmulti_sign_algo]" class="inputbox" id="payzenmulti_sign_algo" style="width: 122px;" >
                            <option <?php if (@$params->payzenmulti_sign_algo === 'SHA-1') echo 'selected="selected"'; ?> value="SHA-1">SHA-1</option>
                            <option <?php if (@$params->payzenmulti_sign_algo === 'SHA-256') echo 'selected="selected"'; ?> value="SHA-256">HMAC-SHA-256</option>
                        </select><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo $signAlgoDesc; ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label><?php echo JText::_('PAYZENMULTI_IPN_URL'); ?></label>
                    </td>
                    <td>
                        <label><span style="font-weight: bold;"><?php echo HIKASHOP_LIVE . 'index.php?option=com_hikashop&amp;ctrl=checkout&amp;task=notify&amp;notif_payment=payzenmulti&amp;tmpl=component'; ?></span></label><br />
                        <img src="<?php echo HIKASHOP_LIVE . 'administrator' . DS . 'components' . DS . 'com_payzen' . DS  ; ?>images/warn.png">
                        <span style="font-size: 12px; font-style: italic; font-weight: bold; color: red; display: inline-block;"><?php echo JText::_('PAYZENMULTI_IPN_URL_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="payzenmulti_platform_url"><?php echo JText::_('PAYZENMULTI_GATEWAY_URL'); ?></label>
                    </td>
                    <td>
                        <input type="text" name="data[payment][payment_params][payzenmulti_platform_url]" value="<?php echo @$params->payzenmulti_platform_url; ?>" id="payzenmulti_platform_url" style="width: 300px;" /><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('PAYZENMULTI_GATEWAY_URL_DESC'); ?></span>
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
            <legend><?php echo JText::_('PAYZENMULTI_PAYMENT_PAGE'); ?></legend>

            <table>
                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="payzenmulti_language"><?php echo JText::_('PAYZENMULTI_LANGUAGE'); ?></label>
                    </td>
                    <td>
                        <select name="data[payment][payment_params][payzenmulti_language]" class="inputbox" id="payzenmulti_language" style="width: 122px;" >
                            <?php
                            foreach (PayzenApi::getSupportedLanguages() as $code => $label) {
                                $selected = (@$params->payzenmulti_language === $code) ? ' selected="selected"' : '';
                                echo '<option' . $selected . ' value="'. $code . '">' . JText::_('PAYZENMULTI_LANGUAGE_' . strtoupper($label)) . '</option>';
                            }
                            ?>
                        </select><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('PAYZENMULTI_LANGUAGE_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="payzenmulti_available_languages"><?php echo JText::_('PAYZENMULTI_AVAILABLE_LANGUAGES'); ?></label>
                    </td>
                    <td>
                        <select name="data[payment][payment_params][payzenmulti_available_languages][]" class="inputbox" multiple="multiple" size="8" id="payzenmulti_available_languages" style="width: 122px;" >
                            <?php
                            $langs = @$params->payzenmulti_available_languages ? explode(';', $params->payzenmulti_available_languages) : array();
                            foreach (PayzenApi::getSupportedLanguages() as $code => $label) {
                                $selected = in_array($code, $langs) ? ' selected="selected"' : '';
                                echo '<option' . $selected . ' value="'. $code . '">' . JText::_('PAYZENMULTI_LANGUAGE_' . strtoupper($label)) . '</option>';
                            }
                            ?>
                        </select><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('PAYZENMULTI_AVAILABLE_LANGUAGES_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="payzenmulti_capture_delay"><?php echo JText::_('PAYZENMULTI_CAPTURE_DELAY'); ?></label>
                    </td>
                    <td>
                        <input type="text" name="data[payment][payment_params][payzenmulti_capture_delay]" value="<?php echo @$params->payzenmulti_capture_delay; ?>" id="payzenmulti_capture_delay" style="width: 120px;" /><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('PAYZENMULTI_CAPTURE_DELAY_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="payzenmulti_validation_mode"><?php echo JText::_('PAYZENMULTI_VALIDATION_MODE'); ?></label>
                    </td>
                    <td>
                        <select name="data[payment][payment_params][payzenmulti_validation_mode]" class="inputbox" id="payzenmulti_validation_mode" style="width: 122px;" >
                            <option <?php if (@$params->payzenmulti_validation_mode === '')  echo 'selected="selected"'; ?>value=''><?php echo JText::_('PAYZENMULTI_MODE_DEFAULT'); ?></option>
                            <option <?php if (@$params->payzenmulti_validation_mode === '0') echo 'selected="selected"'; ?>value='0'><?php echo JText::_('PAYZENMULTI_MODE_AUTOMATIC'); ?></option>
                            <option <?php if (@$params->payzenmulti_validation_mode === '1') echo 'selected="selected"'; ?>value='1'><?php echo JText::_('PAYZENMULTI_MODE_MANUAL'); ?></option>
                        </select><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('PAYZENMULTI_VALIDATION_MODE_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="payzenmulti_payment_cards"><?php echo JText::_('PAYZENMULTI_PAYMENT_CARDS'); ?></label>
                    </td>
                    <td>
                        <select name="data[payment][payment_params][payzenmulti_payment_cards][]" class="inputbox" multiple="multiple" size="8" id="payzenmulti_payment_cards" style="width: 122px;" >
                            <?php
                            $cards = @$params->payzenmulti_payment_cards ? explode(';', $params->payzenmulti_payment_cards) : array();
                            foreach (plgHikashoppaymentPayzenmulti::getAvailableMultiCards() as $code => $label) {
                                $selected = in_array($code, $cards) ? ' selected="selected"' : '';
                                echo '<option' . $selected . ' value="'. $code . '">' . $label . '</option>';
                            }
                            ?>
                        </select><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('PAYZENMULTI_PAYMENT_CARDS_DESC'); ?></span>
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
            <legend><?php echo JText::_('PAYZENMULTI_SELECTIVE_3DS'); ?></legend>

            <table>
                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="payzenmulti_threeds_amount_min"><?php echo JText::_('PAYZENMULTI_THREEDS_AMOUNT_MIN'); ?></label>
                    </td>
                    <td>
                        <input type="text" name="data[payment][payment_params][payzenmulti_threeds_amount_min]" value="<?php echo @$params->payzenmulti_threeds_amount_min; ?>"  style="width: 120px;" /><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('PAYZENMULTI_THREEDS_AMOUNT_MIN_DESC'); ?></span>
                    </td>
                </tr>
            </table>
        </fieldset>
    </td>
</tr>

<!------------------------- Multi payment options ----------------------------------------->
<tr>
    <td colspan="2">
        <fieldset>
            <legend><?php echo JText::_('PAYZENMULTI_MULTI_OPTIONS'); ?></legend>
            <?php
            $multi_options = @$params->payzen_multi_options;

            $js = 'function payzenAddOption(first, deleteText, contract) {
                       if (first) {
                           jQuery("#payzen_multi_options_btn").css("display", "none");
                           jQuery("#payzen_multi_options_table").css("display", "");
                       }

                       var timestamp = new Date().getTime();
                       var optionLine = "<tr id=\"payzen_multi_option_" + timestamp + "\">" +
                                        "    <td><input name=\"data[payment][payment_params][payzen_multi_options][" + timestamp + "][label]\" style=\"width: 150px;\" type=\"text\" /></td>" +
                                        "    <td><input name=\"data[payment][payment_params][payzen_multi_options][" + timestamp + "][amount_min]\" style=\"width: 80px;\" type=\"text\" /></td>" +
                                        "    <td><input name=\"data[payment][payment_params][payzen_multi_options][" + timestamp + "][amount_max]\" style=\"width: 80px;\" type=\"text\" /></td>" ;
                       if (contract) {
                          optionLine += "<td><input name=\"data[payment][payment_params][payzen_multi_options][" + timestamp + "][contract]\" style=\"width: 70px;\" type=\"text\" /></td>";
                       }
		               optionLine += "    <td><input name=\"data[payment][payment_params][payzen_multi_options][" + timestamp + "][count]\" style=\"width: 70px;\" type=\"text\" /></td>" +
                                     "    <td><input name=\"data[payment][payment_params][payzen_multi_options][" + timestamp + "][period]\" style=\"width: 70px;\" type=\"text\" /></td>" +
                                     "    <td><input name=\"data[payment][payment_params][payzen_multi_options][" + timestamp + "][first]\" style=\"width: 70px;\" type=\"text\" /></td>" +
                                     "    <td><button type=\"button\" onclick= \"payzenDeleteOption(" + timestamp + ");\">" + deleteText + " </td>" +
                                     "</tr>";

                       jQuery(optionLine).insertBefore("#payzen_multi_option_add");
                   };

                   function payzenDeleteOption(key) {
                       jQuery("#payzen_multi_option_" + key).remove();

                       if (jQuery("#payzen_multi_options_table tbody tr").length === 1) {
                           jQuery("#payzen_multi_options_btn").css("display", "");
                           jQuery("#payzen_multi_options_table").css("display", "none");
                       }
                   };';

            $doc = JFactory::getDocument();
            $doc->addScriptDeclaration($js);

            $cb_avail = key_exists('CB', plgHikashoppaymentPayzenmulti::getAvailableMultiCards());
            $str_cb_avail = $cb_avail ? 'true' : 'false';
            ?>

            <button id="payzen_multi_options_btn" type="button" style="<?php if (!empty($multi_options)) echo 'display: none;'; else echo ''; ?>"
                    onclick= "payzenAddOption(true, '<?php echo JText::_('PAYZENMULTI_MULTI_OPTIONS_DELETE'); ?>', <?php echo $str_cb_avail; ?>);" >
                    <?php echo JText::_('PAYZENMULTI_MULTI_OPTIONS_ADD'); ?>
            </button>
            <br />

            <table id="payzen_multi_options_table" style="<?php if (empty($multi_options)) echo 'display: none;'; else echo ''; ?>" class="payzen-table" >
                <thead>
                    <tr>
                        <th><?php echo JText::_('PAYZENMULTI_LABEL'); ?></th>
                        <th><?php echo JText::_('PAYZENMULTI_MIN_AMOUNT'); ?></th>
                        <th><?php echo JText::_('PAYZENMULTI_MAX_AMOUNT'); ?></th>
                        <?php if ($cb_avail) echo '<th>'.JText::_('PAYZENMULTI_MAX_CONTRACT').'</th>'; ?>
                        <th><?php echo JText::_('PAYZENMULTI_COUNT'); ?></th>
                        <th><?php echo JText::_('PAYZENMULTI_PERIOD'); ?></th>
                        <th><?php echo JText::_('PAYZENMULTI_FIRST'); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>

    <?php
    if (! empty($multi_options)) {
        foreach ($multi_options as $key => $option) {
            echo '<tr id="payzen_multi_option_' . $key . '">
                      <td>' . payzen_create_text('data[payment][payment_params][payzen_multi_options][' . $key . '][label]', $option['label'], 'style="width: 150px;"') . '</td>
                      <td>' . payzen_create_text('data[payment][payment_params][payzen_multi_options][' . $key . '][amount_min]', $option['amount_min'], 'style="width: 80px;"') . '</td>
                      <td>' . payzen_create_text('data[payment][payment_params][payzen_multi_options][' . $key . '][amount_max]', $option['amount_max'], 'style="width: 80px;"') . '</td>';

            if ($cb_avail) {
                echo '<td>' . payzen_create_text('data[payment][payment_params][payzen_multi_options][' . $key . '][contract]', $option['contract'], 'style="width: 70px;"') . '</td>';
            }

            echo '    <td>' . payzen_create_text('data[payment][payment_params][payzen_multi_options][' . $key . '][count]', $option['count'], 'style="width: 70px;"') . '</td>
                      <td>' . payzen_create_text('data[payment][payment_params][payzen_multi_options][' . $key . '][period]', $option['period'], 'style="width: 70px;"') . '</td>
                      <td>' . payzen_create_text( 'data[payment][payment_params][payzen_multi_options][' . $key . '][first]', $option['first'], 'style="width: 70px;"') . '</td>
                      <td><button type="button" onclick="payzenDeleteOption(' . $key . ');">' . JText::_('PAYZENMULTI_MULTI_OPTIONS_DELETE') . '</button></td>
                  </tr>';
        }
    }

    function payzen_create_text($name, $value, $extra_attributes = '')
    {
        $output = '<input type="text" name="' . $name . '" value="' . $value . '" ' . $extra_attributes . '>';
        return $output;
    }

    ?>

                    <tr id="payzen_multi_option_add">
                        <td colspan="<?php $colspan = $cb_avail ? '7' : '6'; echo $colspan; ?>"></td>
                        <td>
                            <button type="button" onclick="payzenAddOption(false, '<?php echo JText::_('PAYZENMULTI_MULTI_OPTIONS_DELETE'); ?>', <?php echo $str_cb_avail; ?>);" >
                                <?php echo JText::_('PAYZENMULTI_MULTI_OPTIONS_ADD'); ?>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <span style="font-size: 10px; font-style: italic;"><?php $numdesc = $cb_avail ? '1' : '2';echo JText::_('PAYZENMULTI_MULTI_OPTIONS_DESC'.$numdesc); ?></span>
        </fieldset>
    </td>
</tr>

<!------------------------------- Return to shop ------------------------------------------>
<tr>
    <td colspan="2">
        <fieldset>
            <legend><?php echo JText::_('PAYZENMULTI_RETURN_TO_SHOP'); ?></legend>

            <table>
                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="payzenmulti_redirect_enabled"><?php echo JText::_('PAYZENMULTI_REDIRECT_ENABLED'); ?></label>
                    </td>
                    <td>
                        <?php echo JHTML::_('select.booleanlist', 'data[payment][payment_params][payzenmulti_redirect_enabled]' , '', @$params->payzenmulti_redirect_enabled); ?><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('PAYZENMULTI_REDIRECT_ENABLED_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="payzenmulti_redirect_success_timeout"><?php echo JText::_('PAYZENMULTI_REDIRECT_SUCCESS_TIMEOUT'); ?></label>
                    </td>
                    <td>
                        <input type="text" name="data[payment][payment_params][payzenmulti_redirect_success_timeout]" value="<?php echo @$params->payzenmulti_redirect_success_timeout; ?>" id="payzenmulti_redirect_success_timeout" style="width: 120px;" /><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('PAYZENMULTI_REDIRECT_SUCCESS_TIMEOUT_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="payzenmulti_redirect_success_message"><?php echo JText::_('PAYZENMULTI_REDIRECT_SUCCESS_MESSAGE'); ?></label>
                    </td>
                    <td>
                        <input type="text" name="data[payment][payment_params][payzenmulti_redirect_success_message]" value="<?php echo @$params->payzenmulti_redirect_success_message; ?>" id="payzenmulti_redirect_success_message" style="width: 300px;" /><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('PAYZENMULTI_REDIRECT_SUCCESS_MESSAGE_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="payzenmulti_redirect_error_timeout"><?php echo JText::_('PAYZENMULTI_REDIRECT_ERROR_TIMEOUT'); ?></label>
                    </td>
                    <td>
                        <input type="text" name="data[payment][payment_params][payzenmulti_redirect_error_timeout]" value="<?php echo @$params->payzenmulti_redirect_error_timeout; ?>" id="payzenmulti_redirect_error_timeout" style="width: 120px;" /><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('PAYZENMULTI_REDIRECT_ERROR_TIMEOUT_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="payzenmulti_redirect_error_message"><?php echo JText::_('PAYZENMULTI_REDIRECT_ERROR_MESSAGE'); ?></label>
                    </td>
                    <td>
                        <input type="text" name="data[payment][payment_params][payzenmulti_redirect_error_message]" value="<?php echo @$params->payzenmulti_redirect_error_message; ?>" id="payzenmulti_redirect_error_message" style="width: 300px;" /><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('PAYZENMULTI_REDIRECT_ERROR_MESSAGE_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="payzenmulti_return_mode"><?php echo JText::_('PAYZENMULTI_RETURN_MODE'); ?></label>
                    </td>
                    <td>
                        <select  name="data[payment][payment_params][payzenmulti_return_mode]" class="inputbox" id="payzenmulti_return_mode" style="width: 122px;" >
                            <option<?php if (@$params->payzenmulti_return_mode === 'GET') echo ' selected="selected"'; ?> value='GET'>GET</option>
                            <option<?php if (@$params->payzenmulti_return_mode === 'POST') echo ' selected="selected"'; ?> value='POST'>POST</option>
                        </select><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('PAYZENMULTI_RETURN_MODE_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="datapaymentpayment_paramspayzenmulti_verified_status"><?php echo JText::_('PAYZENMULTI_VERIFIED_STATUS'); ?></label>
                    </td>

                    <td>
                        <?php echo $this->data['order_statuses']->display('data[payment][payment_params][payzenmulti_verified_status]', @$params->payzenmulti_verified_status, 'style="width: 122px;"'); ?><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('PAYZENMULTI_VERIFIED_STATUS_DESC'); ?></span>
                    </td>
                </tr>

                <tr>
                    <td class="key" style="vertical-align: top; white-space: normal !important;">
                        <label for="datapaymentpayment_paramspayzenmulti_invalid_status"><?php echo JText::_('PAYZENMULTI_INVALID_STATUS'); ?></label>
                    </td>
                    <td>
                        <?php echo $this->data['order_statuses']->display('data[payment][payment_params][payzenmulti_invalid_status]', @$params->payzenmulti_invalid_status, 'style="width: 122px;"'); ?><br />
                        <span style="font-size: 10px; font-style: italic;"><?php echo JText::_('PAYZENMULTI_INVALID_STATUS_DESC'); ?></span>
                    </td>
                </tr>
            </table>
        </fieldset>
    </td>
</tr>
