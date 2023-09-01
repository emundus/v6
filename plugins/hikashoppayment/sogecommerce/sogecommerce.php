<?php
/**
 * Copyright © Lyra Network.
 * This file is part of Sogecommerce plugin for HikaShop. See COPYING.md for license details.
 *
 * @author    Lyra Network (https://www.lyra.com/)
 * @copyright Lyra Network
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL v3)
 */
defined('_JEXEC') or die('Restricted access');

// Load plugin translations.
$lang = JFactory::getLanguage();
$lang->load('plg_hikashoppayment_sogecommerce', JPATH_ADMINISTRATOR);

// Load gateway API.
if (! class_exists('SogecommerceApi')) {
    require_once rtrim(JPATH_ADMINISTRATOR, DS) . DS . 'components' . DS . 'com_sogecommerce' . DS . 'classes' . DS . 'sogecommerce_api.php';
}

// Load plugin features class.
if (! class_exists('com_sogecommerceInstallerScript')) {
    require_once rtrim(JPATH_ADMINISTRATOR, DS) . DS . 'components' . DS . 'com_sogecommerce' . DS . 'script.install.php';
}

class plgHikashoppaymentSogecommerce extends hikashopPaymentPlugin
{
    var $name = 'sogecommerce';

    var $accepted_currencies = array();

    var $doc_form = 'sogecommerce';

    var $multiple = true;

    function __construct(&$subject, $config)
    {
        foreach (SogecommerceApi::getSupportedCurrencies() as $currency) {
            // Currency alpha3 code.
            $this->accepted_currencies[] = $currency->getAlpha3();
        }

        // Plugin features.
        $this->plugin_features = com_sogecommerceInstallerScript::$plugin_features;

        parent::__construct($subject, $config);
    }

    /**
     * Called by Hikashop before redirect to payment gateway.
     * Construct array of parameters here.
     *
     * @param $order
     * @param $methods
     * @param $method_id
     */
    function onAfterOrderConfirm(&$order, &$methods, $method_id)
    {
        parent::onAfterOrderConfirm($order, $methods, $method_id);

        // Process shop language.
        $lang = JFactory::getLanguage();
        $langCode = strtoupper(substr($lang->get('tag'), 0, 2));
        $sogecommerceLanguage = SogecommerceApi::isSupportedLanguage($langCode) ? $langCode : $this->payment_params->sogecommerce_language;

        // Process currency.
        $sogecommerceCurrency = SogecommerceApi::findCurrencyByAlphaCode($this->currency->currency_code);

        // Amount.
        $price = $order->cart->full_total->prices[0];
        $amount = round($price->price_value_with_tax, hikashop_get('class.currency')->getRounding($price->price_currency_id));

        // 3DS activation according to amount.
        $threedsMpi = null;
        if (! empty($this->payment_params->sogecommerce_threeds_amount_min) &&
            ($amount < $this->payment_params->sogecommerce_threeds_amount_min)) {
            $threedsMpi = '2';
        }

        // Load config to retrieve HikaShop version.
        $config = hikashop_config();

        $this->vars = array(
            'amount' => $sogecommerceCurrency->convertAmountToInteger($amount),
            'contrib' => 'HikaShop_2.x-4.x_2.1.5/' . JVERSION . '_' . $config->get('version') . '/' . PHP_VERSION,
            'currency' => $sogecommerceCurrency->getNum(),
            'language' => $sogecommerceLanguage,
            'order_id' => $order->order_number,
            'threeds_mpi' => $threedsMpi,

            'cust_id' => $this->user->user_id,
            'cust_email' => $this->user->user_email,

            'cust_title' => @$order->cart->billing_address->address_title,
            'cust_first_name' => @$order->cart->billing_address->address_firstname,
            'cust_last_name' => @$order->cart->billing_address->address_lastname,
            'cust_address' => @$order->cart->billing_address->address_street . ' ' .
                @$order->cart->billing_address->address_street2,
            'cust_zip' => @$order->cart->billing_address->address_post_code,
            'cust_city' => @$order->cart->billing_address->address_city,
            'cust_state' => @$order->cart->billing_address->address_state->zone_name,
            'cust_country' => @$order->cart->billing_address->address_country->zone_code_2,
            'cust_phone' => @$order->cart->billing_address->address_telephone,

            'ship_to_first_name' => @$order->cart->shipping_address->address_firstname,
            'ship_to_last_name' => @$order->cart->shipping_address->address_lastname,
            'ship_to_street' => @$order->cart->shipping_address->address_street,
            'ship_to_street2' => @$order->cart->shipping_address->address_street2,
            'ship_to_city' => @$order->cart->shipping_address->address_city,
            'ship_to_state' => @$order->cart->shipping_address->address_state->zone_name,
            'ship_to_country' => @$order->cart->shipping_address->address_country->zone_code_2,
            'ship_to_phone_num' => @$order->cart->shipping_address->address_telephone,
            'ship_to_zip' => @$order->cart->shipping_address->address_post_code,

            'url_return' => HIKASHOP_LIVE .
                'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=sogecommerce&tmpl=component&Itemid=' .
                JFactory::getApplication()->input->getInt('Itemid'),
            'payment_method_id' => $method_id
        );

        $params = array(
            'site_id',
            'key_test',
            'key_prod',
            'ctx_mode',
            'sign_algo',
            'platform_url',
            'available_languages',
            'capture_delay',
            'validation_mode',
            'payment_cards',
            'redirect_enabled',
            'redirect_success_timeout',
            'redirect_success_message',
            'redirect_error_timeout',
            'redirect_error_message',
            'return_mode'
        );
        foreach ($params as $param) {
            $paramName = 'sogecommerce_' . $param;
            $this->vars[$param] = $this->payment_params->$paramName;
        }

        if ($this->plugin_features['qualif']) {
            // Tests will be made on qualif, no test mode available.
            $this->vars['ctx_mode'] = 'PRODUCTION';
        }

        return $this->showPage('end');
    }

    /**
     * Notify payment after callback from payment gateway.
     *
     * @param $statuses
     * @return boolean
     */
    function onPaymentNotification(&$statuses)
    {
        $app = JFactory::getApplication();

        if (JFactory::getApplication()->input->getVar('vads_hash') !== null) {
            // This is a server call.
            if ((($payCfg = JFactory::getApplication()->input->getVar('vads_payment_config')) && stripos($payCfg, 'MULTI') !== false) ||
                (($contrib = JFactory::getApplication()->input->getVar('vads_contrib')) && stripos($contrib, 'multi') !== false)) {

                // Multi payment : let multi module do the work.
                $data = hikashop_import('hikashoppayment', 'sogecommercemulti');
                if (! empty($data)) {
                    return $data->onPaymentNotification($statuses);
                }
            }
        }

        // Load payment method parameters.
        $pluginsClass = hikashop::get('class.plugins');
        $elements = $pluginsClass->getMethods('payment', 'sogecommerce');
        if (empty($elements)) {
            return false;
        }

        $urlItemId = JFactory::getApplication()->input->getInt('Itemid') ? '&Itemid=' . JFactory::getApplication()->input->getInt('Itemid') : '';

        require_once rtrim(JPATH_ADMINISTRATOR, DS) . DS . 'components' . DS . 'com_sogecommerce' . DS . 'classes' . DS .
            'sogecommerce_response.php';

        $data = isset($_POST['vads_order_id']) ? $_POST : $_GET;

        $payment_method_id = isset($data['vads_ext_info_payment_method_id']) ? $data['vads_ext_info_payment_method_id'] : '';

        $element = $this->getElement($elements, $payment_method_id);

        $sogecommerceResponse = new SogecommerceResponse(
            $data,
            $element->payment_params->sogecommerce_ctx_mode,
            $element->payment_params->sogecommerce_key_test,
            $element->payment_params->sogecommerce_key_prod,
            $element->payment_params->sogecommerce_sign_algo
        );

        $fromServer = ($sogecommerceResponse->get('hash') !== null);

        if (! $sogecommerceResponse->isAuthentified()) {
            $this->log("Received invalid response from return/IPN URL with data: " . print_r($data, true));
            $this->log('Signature algorithm selected in module settings must be the same as one selected in gateway Back Office.');

            if ($fromServer) {
                $this->log('SERVER URL PROCESS END');
                die($sogecommerceResponse->getOutputForGateway('auth_fail'));
            } else {
                $this->log('RETURN URL PROCESS END');
                $app->enqueueMessage(JText::_('SOGECOMMERCE_ERROR_MSG'), 'error');
                $app->redirect(hikashop_completeLink('order' . $urlItemId, false, true));
                die();
            }
        }

        // Retrieve order info from database.
        $orderClass = hikashop::get('class.order');
        $orderId = hikashop::decode($sogecommerceResponse->get('order_id'));
        $order = $orderClass->get((int) $orderId);

        if (empty($order)) {
            // Order not found.
            $this->log('Error: Order (' . $orderId . ') not found or key does not match received invoice ID.');

            if ($fromServer) {
                $this->log('SERVER URL PROCESS END');
                die($sogecommerceResponse->getOutputForGateway('order_not_found'));
            } else {
                $this->log('RETURN URL PROCESS END');
                $app->enqueueMessage(JText::_('SOGECOMMERCE_ERROR_MSG'), 'error');
                $app->redirect(hikashop_completeLink('order' . $urlItemId, false, true));
                die();
            }
        }

        if ($element->payment_params->sogecommerce_ctx_mode === 'TEST' && $this->plugin_features['prodfaq']) {
            $app->enqueueMessage(JText::_('SOGECOMMERCE_SHOP_TO_PROD_INFO'));
        }

        // Redirect to those URLs.
        $success_url = hikashop_completeLink('checkout&task=after_end&order_id=' . $order->order_id . $urlItemId, false, true);
        $error_url = hikashop_completeLink('order&task=cancel_order&order_id=' . $order->order_id . $urlItemId, false, true);

        // If unpaid order : reset order status.
        $unpaid_statuses = hikashop_config()->get('order_unpaid_statuses') ? explode(',',
            hikashop_config()->get('order_unpaid_statuses')) : array();
        if (hikashop_config()->get('allow_payment_button') && in_array($order->order_status, $unpaid_statuses)) {
            $order->order_status = hikashop_config()->get('order_created_status');
        }

        // Process according to order status and payment result.
        if ($order->order_status === hikashop_config()->get('order_created_status')) {
            // Order not processed yet.
            if ($sogecommerceResponse->isAcceptedPayment()) {
                $this->log("Payment successfull, let's save order #$orderId");

                if (method_exists($this, 'modifyOrder')) {
                    $history = $this->_createOrderHistory($sogecommerceResponse, $element, 1);
                    $this->modifyOrder($order->order_id, $element->payment_params->sogecommerce_verified_status, $history);
                } else {
                    $this->_confirmOrder($order, $element->payment_params->sogecommerce_verified_status, $element, $sogecommerceResponse, 1);
                }

                if ($fromServer) {
                    $this->log('Payment completed successfully by server URL call.');
                    $this->log('SERVER URL PROCESS END');
                    die($sogecommerceResponse->getOutputForGateway('payment_ok'));
                } else {
                    $this->log('Warning! IPN URL call has not worked. Payment completed by return URL call.');
                    if ($element->payment_params->sogecommerce_ctx_mode === 'TEST') {
                        // Test mode warning : check URL not correctly called.
                        $app->enqueueMessage(JText::_('SOGECOMMERCE_CHECK_URL_WARN') . '<br />' . JText::_('SOGECOMMERCE_CHECK_URL_WARN_DETAILS'), 'error');
                    }

                    $this->log('RETURN URL PROCESS END');
                    $app->redirect($success_url);
                    die();
                }
            } else {
                if (method_exists($this, 'modifyOrder')) {
                    $history = $this->_createOrderHistory($sogecommerceResponse, $element);
                    $this->modifyOrder($order->order_id, $element->payment_params->sogecommerce_invalid_status, $history);
                } else {
                    $this->_confirmOrder($order, $element->payment_params->sogecommerce_invalid_status, $element, $sogecommerceResponse);
                }

                $this->log('Payment failed or cancelled. ' . $sogecommerceResponse->getLogMessage());
                if ($fromServer) {
                    $this->log('SERVER URL PROCESS END');
                    die($sogecommerceResponse->getOutputForGateway('payment_ko'));
                } else {
                    $this->log('RETURN URL PROCESS END');
                    $app->enqueueMessage(JText::_('SOGECOMMERCE_FAILURE_MSG'), 'error');
                    $app->redirect($error_url);
                    die();
                }
            }
        } else {
            // Order already processed.
            $this->log("Order #$orderId is already processed. Just show payment result.");
            if ($sogecommerceResponse->isAcceptedPayment()
                && ($order->order_status === $element->payment_params->sogecommerce_verified_status)) {
                $this->log('Payment successfull reconfirmed.');
                if ($fromServer) {
                    $this->log('SERVER URL PROCESS END');
                    die($sogecommerceResponse->getOutputForGateway('payment_ok_already_done'));
                } else {
                    $this->log('RETURN URL PROCESS END');
                    $app->redirect($success_url);
                    die();
                }
            } elseif (! $sogecommerceResponse->isAcceptedPayment() &&
                ($order->order_status === $element->payment_params->sogecommerce_invalid_status)) {
                $this->log('Payment failed reconfirmed.');
                if ($fromServer) {
                    $this->log('SERVER URL PROCESS END');
                    die($sogecommerceResponse->getOutputForGateway('payment_ko_already_done'));
                } else {
                    $this->log('RETURN URL PROCESS END');
                    $app->enqueueMessage(JText::_('SOGECOMMERCE_FAILURE_MSG'), 'error');
                    $app->redirect($error_url);
                    die();
                }
            } else {
                $this->log('Error ! Invalid payment result received for already saved order. Payment result : ' .
                    $sogecommerceResponse->get('result') . ', Order status : ' . $order->order_status);
                if ($fromServer) {
                    $this->log('SERVER URL PROCESS END');
                    die($sogecommerceResponse->getOutputForGateway('payment_ko_on_order_ok'));
                } else {
                    $this->log('RETURN URL PROCESS END');
                    $app->enqueueMessage(JText::_('SOGECOMMERCE_ERROR_MSG'), 'error');
                    $app->redirect(hikashop_completeLink('order' . $urlItemId, false, true));
                    die();
                }
            }
        }
    }

    // Private: create and save order.
    function _confirmOrder($orderData, $newStatus, $payment, $sogecommerceResponse, $notify = 0)
    {
        // Prepare order and history order.
        $order = new stdClass();
        $order->order_id = $orderData->order_id;
        $order->order_status = $newStatus;

        $order->old_status = new stdClass();
        $order->old_status->order_status = $orderData->order_status;

        $order->history = new stdClass();
        $history = $this->_createOrderHistory($sogecommerceResponse, $payment, $notify);
        foreach ($history as $key => $value) {
            $key = 'history_' . $key;
            $order->history->$key = $value;
        }

        // Save order and history order.
        $orderClass = hikashop::get('class.order');
        $orderClass->save($order);
    }

    function _createOrderHistory($sogecommerceResponse, $payment, $notify = 0)
    {
        $currencyCode = SogecommerceApi::findCurrencyByNumCode($sogecommerceResponse->get('currency'))->getAlpha3();
        $history = new stdClass();
        $history->amount = $sogecommerceResponse->getFloatAmount() . ' ' . $currencyCode;
        $history->reason = JText::_('AUTOMATIC_PAYMENT_NOTIFICATION');
        $history->payment_id = $payment->payment_id;
        $history->payment_method = $payment->payment_type;
        $history->type = 'payment';
        $history->notified = $notify;

        $info = JText::_('SOGECOMMERCE_RESULT') . $sogecommerceResponse->getMessage();

        $info .= ' | ' . JText::_('SOGECOMMERCE_TRANS_ID') . $sogecommerceResponse->get('trans_id');

        if ($sogecommerceResponse->get('card_brand')) {
            $info .= ' | ' . JText::_('SOGECOMMERCE_CC_TYPE') . $sogecommerceResponse->get('card_brand');

            // Add card brand user choice.
            if ($sogecommerceResponse->get('brand_management')) {
                $brand_info = json_decode($sogecommerceResponse->get('brand_management'));
                $msg_brand_choice = '';

                if (isset($brand_info->userChoice) && $brand_info->userChoice) {
                    $msg_brand_choice .= JText::_('SOGECOMMERCE_CARD_BRAND_BUYER_CHOICE');
                } else {
                    $msg_brand_choice .= JText::_('SOGECOMMERCE_CARD_BRAND_DEFAULT_CHOICE');
                }

                $info .= ' (' . $msg_brand_choice . ')';
            }
        }

        if ($sogecommerceResponse->get('card_number')) {
            $info .= ' | ' . JText::_('SOGECOMMERCE_CC_NUMBER') . $sogecommerceResponse->get('card_number');
        }

        if ($sogecommerceResponse->get('expiry_month') && $sogecommerceResponse->get('expiry_year')) {
            $info .= ' | ' . JText::_('SOGECOMMERCE_CC_EXPIRY') . str_pad($sogecommerceResponse->get('expiry_month'), 2, '0', STR_PAD_LEFT) .
                ' / ' . $sogecommerceResponse->get('expiry_year');
        }

        $history->data = $info;

        return $history;
    }

    /**
     * Called before load plugin configuration page.
     *
     * @param $element
     */
    function onPaymentConfiguration(&$element)
    {
        $this->title = JText::_('SOGECOMMERCE_CONFIG_PAGE_TITLE');
        $this->_copyImages();

        parent::onPaymentConfiguration($element);
    }

    /**
     * Called by onPaymentConfiguration to initialise module parameters.
     *
     * @param $element
     */
    function getPaymentDefaultValues(&$element)
    {
        $element->payment_name = JText::_('SOGECOMMERCE_DEFAULT_TITLE');
        $element->payment_description = JText::_('SOGECOMMERCE_DEFAULT_DESCRIPTION');
        $element->payment_images = 'sogecommerce_cards';

        // Default values.
        $element->payment_params->sogecommerce_site_id = '12345678';
        $element->payment_params->sogecommerce_key_test = '1111111111111111';
        $element->payment_params->sogecommerce_key_prod = '2222222222222222';
        $element->payment_params->sogecommerce_ctx_mode = 'TEST';
        $element->payment_params->sogecommerce_sign_algo = 'SHA-256';
        $element->payment_params->sogecommerce_platform_url = 'https://sogecommerce.societegenerale.eu/vads-payment/';
        $element->payment_params->sogecommerce_language = 'fr';
        $element->payment_params->sogecommerce_available_languages = '';
        $element->payment_params->sogecommerce_capture_delay = '';
        $element->payment_params->sogecommerce_validation_mode = '';
        $element->payment_params->sogecommerce_payment_cards = '';
        $element->payment_params->sogecommerce_threeds_amount_min = '';
        $element->payment_params->sogecommerce_redirect_enabled = 0;
        $element->payment_params->sogecommerce_redirect_success_timeout = '5';
        $element->payment_params->sogecommerce_redirect_success_message = JText::_('SOGECOMMERCE_REDIRECT_SUCCESS_MESSAGE_DFEAULT');
        $element->payment_params->sogecommerce_redirect_error_timeout = '5';
        $element->payment_params->sogecommerce_redirect_error_message = JText::_('SOGECOMMERCE_REDIRECT_ERROR_MESSAGE_DFEAULT');
        $element->payment_params->sogecommerce_return_mode = 'GET';
        $element->payment_params->sogecommerce_verified_status = 'confirmed';
        $element->payment_params->sogecommerce_invalid_status = 'cancelled';
    }

    /**
     * Called before save plugin configuration.
     *
     * @param $element
     * @return boolean
     */
    function onPaymentConfigurationSave(&$element)
    {
        $langs = @$element->payment_params->sogecommerce_available_languages;
        if (! is_array($langs)) {
            $langs = array();
        }

        $element->payment_params->sogecommerce_available_languages = implode(';', $langs);

        $cards = @$element->payment_params->sogecommerce_payment_cards;
        if (! is_array($cards)) {
            $cards = array();
        }

        $element->payment_params->sogecommerce_payment_cards = implode(';', $cards);

        // Configuration fields validation.
        $errors = array();

        $params = array(
            'site_id',
            'key_test',
            'key_prod',
            'ctx_mode',
            'sign_algo',
            'platform_url',
            'capture_delay',
            'validation_mode',
            'redirect_enabled',
            'redirect_success_timeout',
            'redirect_success_message',
            'redirect_error_timeout',
            'redirect_error_message',
            'return_mode'
        );

        if ($this->plugin_features['qualif']) {
            // Tests will be made on qualif, no test mode available.
            unset($params['3']); // ctx_mode.
        }

        // Instanciate SogecommerceRequest to validate parameters.
        require_once rtrim(JPATH_ADMINISTRATOR, DS) . DS . 'components' . DS . 'com_sogecommerce' . DS . 'classes' . DS .
            'sogecommerce_request.php';
        $request = new SogecommerceRequest();

        foreach ($params as $param) {
            $paramName = 'sogecommerce_' . $param;
            $value = @$element->payment_params->$paramName;

            if (! $request->set($param, $value)) {
                $errors[] = sprintf(JText::_('SOGECOMMERCE_ERROR_SAVE'), JText::_($paramName));
            }
        }

        if (! empty($errors)) {
            $app = JFactory::getApplication();
            foreach ($errors as $error) {
                $app->enqueueMessage($error, 'error');
            }

            return false;
        }

        return true;
    }

    // Copy images to right place.
    function _copyImages()
    {
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');

        $destFolder = HIKASHOP_IMAGES . 'payment';
        $sourceFolder = realpath(dirname(__FILE__)) . DS . 'images';

        if (! (JFolder::exists($destFolder))) {
            JFolder::create($destFolder);
        }

        if (! (JFile::exists($destFolder . DS . 'sogecommerce_cards.png'))) {
            JFile::copy($sourceFolder . DS . 'sogecommerce_cards.png', $destFolder . DS . 'sogecommerce_cards.png');
        }

        if (! (JFile::exists($destFolder . DS . 'sogecommerce.png'))) {
            JFile::copy($sourceFolder . DS . 'sogecommerce.png', $destFolder . DS . 'sogecommerce.png');
        }
    }

    function log($msg, $level = 'INFO')
    {
        $date = date('Y-m-d H:i:s', time());
        $fLog = @fopen(rtrim(JPATH_ADMINISTRATOR, DS) . DS . 'logs' . DS . 'sogecommerce.log', 'a');

        if ($fLog) {
            fwrite($fLog, "$date - $level : $msg\n");
            fclose($fLog);
        }
    }

    function getElement($elements, $payment_id)
    {
        if ($payment_id) {
            foreach ($elements as $elem) {
                if ($elem->payment_id === $payment_id) {
                    return $elem;
                }
            }
        }

        return reset($elements);
    }
}
