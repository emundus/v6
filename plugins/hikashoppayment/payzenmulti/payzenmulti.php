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

// Load plugins translations.
$lang = JFactory::getLanguage();
$lang->load('plg_hikashoppayment_payzenmulti', JPATH_ADMINISTRATOR);

// Load gateway API.
if (! class_exists('PayzenApi')) {
    require_once rtrim(JPATH_ADMINISTRATOR, DS) . DS . 'components' . DS . 'com_payzen' . DS . 'classes' . DS .
         'payzen_api.php';
}

// Load plugin features class.
if (! class_exists('com_payzenInstallerScript')) {
    require_once rtrim(JPATH_ADMINISTRATOR, DS) . DS . 'components' . DS . 'com_payzen' . DS . 'script.install.php';
}

class plgHikashoppaymentPayzenmulti extends hikashopPaymentPlugin
{
    var $name = 'payzenmulti';

    var $accepted_currencies = array();

    var $doc_form = 'payzenmulti';

    var $multiple = true;

    function __construct(&$subject, $config)
    {
        foreach (PayzenApi::getSupportedCurrencies() as $currency) {
            // Currency alpha3 code.
            $this->accepted_currencies[] = $currency->getAlpha3();
        }

        // Plugin features.
        $this->plugin_features = com_payzenInstallerScript::$plugin_features;

        parent::__construct($subject, $config);
    }

    function onPaymentSave(&$cart, &$rates, &$payment_id)
    {
        $session = JFactory::getSession();
        $session->set('payzen_multi_option', JRequest::getVar('payzen_multi_option'));

        return parent::onPaymentSave($cart, $rates, $payment_id);
    }

    /**
     * Called by HikaShop before redirect to payment gateway.
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
        $payzenmultiLanguage = PayzenApi::isSupportedLanguage($langCode) ? $langCode : $this->payment_params->payzenmulti_language;

        // Process currency.
        $payzenmultiCurrency = PayzenApi::findCurrencyByAlphaCode($this->currency->currency_code);

        // Amount.
        $price = $order->cart->full_total->prices[0];
        $amount = round(
            $price->price_value_with_tax,
            hikashop_get('class.currency')->getRounding($price->price_currency_id)
        );

        // 3DS activation according to amount.
        $threedsMpi = null;
        if ($this->payment_params->payzenmulti_threeds_amount_min &&
             $amount < $this->payment_params->payzenmulti_threeds_amount_min) {
            $threedsMpi = '2';
        }

        // Load config to retrieve hikashop version.
        $config = hikashop_config();

        $this->vars = array(
            'amount' => $payzenmultiCurrency->convertAmountToInteger($amount),
            'contrib' => 'HikaShop_2.x-3.x_2.1.3/' . JVERSION . '_' . $config->get('version') . '/' . PHP_VERSION,
            'currency' => $payzenmultiCurrency->getNum(),
            'language' => $payzenmultiLanguage,
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
                'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=payzenmulti&tmpl=component&Itemid=' .
                JRequest::getInt('Itemid'),
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
            $paramName = 'payzenmulti_' . $param;
            $this->vars[$param] = $this->payment_params->$paramName;
        }

        if ($this->plugin_features['qualif']) {
            // Tests will be made on qualif, no test mode available.
            $this->vars['ctx_mode'] ='PRODUCTION';
        }

        // Prepare payment in installements data.
        $multiOptions = $this->payment_params->payzen_multi_options;

        $session = JFactory::getSession();
        $selectedOption = $multiOptions[$session->get('payzen_multi_option')]; // The selected payment option.

        $configFirst = $selectedOption['first'];
        $first = ! empty($configFirst) ? $payzenmultiCurrency->convertAmountToInteger(($configFirst / 100) * $amount) : null;
        $this->multivars = array(
            'count' => $selectedOption['count'],
            'period' => $selectedOption['period'],
            'first' => $first
        );

        if (isset($selectedOption['contract']) && $selectedOption['contract']) {
            $this->multivars['contract'] = 'CB=' . $selectedOption['contract'];
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

        if (JRequest::getVar('vads_hash') !== null) {
            // This is a server call.
            if ((! ($payCfg = JRequest::getVar('vads_payment_config')) || stripos($payCfg, 'MULTI') === false) &&
                 (! ($contrib = JRequest::getVar('vads_contrib')) || stripos($contrib, 'multi') === false)) {

                // Single payment : let Single module do the work.
                $data = hikashop_import('hikashoppayment', 'payzen');
                if (! empty($data)) {
                    return $data->onPaymentNotification($statuses);
                }
            }
        }

        // Load payment method parameters.
        $pluginsClass = hikashop::get('class.plugins');
        $elements = $pluginsClass->getMethods('payment', 'payzenmulti');
        if (empty($elements)) {
            return false;
        }

        $urlItemId = JRequest::getInt('Itemid') ? '&Itemid=' . JRequest::getInt('Itemid') : '';

        require_once rtrim(JPATH_ADMINISTRATOR, DS) . DS . 'components' . DS . 'com_payzen' . DS . 'classes' . DS .
             'payzen_response.php';

        $data = isset($_POST['vads_order_id']) ? $_POST : $_GET;

        $payment_method_id = isset($data['vads_ext_info_payment_method_id']) ? $data['vads_ext_info_payment_method_id'] : '';

        $element = $this->getElement($elements, $payment_method_id);

        $payzenmultiResponse = new PayzenResponse(
            $data,
            $element->payment_params->payzenmulti_ctx_mode,
            $element->payment_params->payzenmulti_key_test,
            $element->payment_params->payzenmulti_key_prod,
            $element->payment_params->payzenmulti_sign_algo
         );

        $fromServer = ($payzenmultiResponse->get('hash') !== null);

        if (! $payzenmultiResponse->isAuthentified()) {
            $this->log("Received invalid response from return/IPN URL with data: " . print_r($data, true));
            $this->log('Signature algorithm selected in module settings must be the same as one selected in gateway Back Office.');

            if ($fromServer) {
                $this->log('SERVER URL PROCESS END');
                die($payzenmultiResponse->getOutputForGateway('auth_fail'));
            } else {
                $this->log('RETURN URL PROCESS END');
                $app->enqueueMessage(JText::_('PAYZENMULTI_ERROR_MSG'), 'error');
                $app->redirect(hikashop_completeLink('order' . $urlItemId, false, true));
                die();
            }
        }

        // Retrieve order info from database.
        $orderClass = hikashop::get('class.order');
        $orderId = hikashop::decode($payzenmultiResponse->get('order_id')); // order_id from order_number
        $order = $orderClass->get((int) $orderId);

        if (empty($order)) {
            // Order not found.
            $this->log('Error: Order (' . $orderId . ') not found or key does not match received invoice ID.');

            if ($fromServer) {
                $this->log('SERVER URL PROCESS END');
                die($payzenmultiResponse->getOutputForGateway('order_not_found'));
            } else {
                $this->log('RETURN URL PROCESS END');
                $app->enqueueMessage(JText::_('PAYZENMULTI_ERROR_MSG'), 'error');
                $app->redirect(hikashop_completeLink('order' . $urlItemId, false, true));
                die();
            }
        }

        if ($element->payment_params->payzenmulti_ctx_mode === 'TEST' && $this->plugin_features['prodfaq']) {
            $app->enqueueMessage(JText::_('PAYZENMULTI_SHOP_TO_PROD_INFO'));
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
            if ($payzenmultiResponse->isAcceptedPayment()) {
                $this->log('Payment successfull, let\'s save order #' . $orderId);

                if (method_exists($this, 'modifyOrder')) {
                    $history = $this->_createOrderHistory($payzenmultiResponse, $element, 1);
                    $this->modifyOrder($order->order_id, $element->payment_params->payzenmulti_verified_status,
                        $history);
                } else {
                    $this->_confirmOrder(
                        $order,
                        $element->payment_params->payzenmulti_verified_status,
                        $element,
                        $payzenmultiResponse,
                        1
                    );
                }

                if ($fromServer) {
                    $this->log('Payment completed successfully by server URL call.');
                    $this->log('SERVER URL PROCESS END');
                    die($payzenmultiResponse->getOutputForGateway('payment_ok'));
                } else {
                    $this->log('Warning ! IPN URL call has not worked. Payment completed by return URL call.');
                    if ($element->payment_params->payzenmulti_ctx_mode === 'TEST') {
                        // Test mode warning : check URL not correctly called.
                        $app->enqueueMessage(JText::_('PAYZENMULTI_CHECK_URL_WARN') . '<br />' . JText::_('PAYZENMULTI_CHECK_URL_WARN_DETAILS'), 'error');
                    }

                    $this->log('RETURN URL PROCESS END');
                    $app->redirect($success_url);
                    die();
                }
            } else {
                if (method_exists($this, 'modifyOrder')) {
                    $history = $this->_createOrderHistory($payzenmultiResponse, $element);
                    $this->modifyOrder($order->order_id, $element->payment_params->payzenmulti_invalid_status, $history);
                } else {
                    $this->_confirmOrder(
                        $order,
                        $element->payment_params->payzenmulti_invalid_status,
                        $element,
                        $payzenmultiResponse
                    );
                }

                $this->log('Payment failed or cancelled. ' . $payzenmultiResponse->getLogMessage());
                if ($fromServer) {
                    $this->log('SERVER URL PROCESS END');
                    die($payzenmultiResponse->getOutputForGateway('payment_ko'));
                } else {
                    $this->log('RETURN URL PROCESS END');
                    $app->enqueueMessage(JText::_('PAYZENMULTI_FAILURE_MSG'), 'error');
                    $app->redirect($error_url);
                    die();
                }
            }
        } else {
            // Order already processed.
            $this->log('Order #' . $orderId . ' is already processed. Just show payment result.');
            if ($payzenmultiResponse->isAcceptedPayment() &&
                 ($order->order_status === $element->payment_params->payzenmulti_verified_status)) {
                $this->log('Payment successfull reconfirmed.');
                if ($fromServer) {
                    $this->log('SERVER URL PROCESS END');
                    die($payzenmultiResponse->getOutputForGateway('payment_ok_already_done'));
                } else {
                    $this->log('RETURN URL PROCESS END');
                    $app->redirect($success_url);
                    die();
                }
            } elseif (! $payzenmultiResponse->isAcceptedPayment() &&
                 ($order->order_status === $element->payment_params->payzenmulti_invalid_status)) {
                $this->log('Payment failed reconfirmed.');
                if ($fromServer) {
                    $this->log('SERVER URL PROCESS END');
                    die($payzenmultiResponse->getOutputForGateway('payment_ko_already_done'));
                } else {
                    $this->log('RETURN URL PROCESS END');
                    $app->enqueueMessage(JText::_('PAYZENMULTI_FAILURE_MSG'), 'error');
                    $app->redirect($error_url);
                    die();
                }
            } else {
                $this->log(
                    'Error ! Invalid payment result received for already saved order. Payment result : ' .
                    $payzenmultiResponse->get('result') . ', Order status : ' . $order->order_status
                );

                if ($fromServer) {
                    $this->log('SERVER URL PROCESS END');
                    die($payzenmultiResponse->getOutputForGateway('payment_ko_on_order_ok'));
                } else {
                    $this->log('RETURN URL PROCESS END');
                    $app->enqueueMessage(JText::_('PAYZENMULTI_ERROR_MSG'), 'error');
                    $app->redirect(hikashop_completeLink('order' . $urlItemId, false, true));
                    die();
                }
            }
        }
    }

    // Private : create and save order.
    function _confirmOrder($orderData, $newStatus, $payment, $payzenmultiResponse, $notify = 0)
    {
        // Prepare order and history order.
        $order = new stdClass();
        $order->order_id = $orderData->order_id;
        $order->order_status = $newStatus;

        $order->old_status = new stdClass();
        $order->old_status->order_status = $orderData->order_status;

        $order->history = new stdClass();
        $history = $this->_createOrderHistory($payzenmultiResponse, $payment, $notify);
        foreach ($history as $key => $value) {
            $key = 'history_' . $key;
            $order->history->$key = $value;
        }

        // Save order and history order.
        $orderClass = hikashop::get('class.order');
        $orderClass->save($order);
    }

    function _createOrderHistory($payzenmultiResponse, $payment, $notify = 0)
    {
        $currencyCode = PayzenApi::findCurrencyByNumCode($payzenmultiResponse->get('currency'))->getAlpha3();
        $history = new stdClass();
        $history->amount = $payzenmultiResponse->getFloatAmount() . ' ' . $currencyCode;
        $history->reason = JText::_('AUTOMATIC_PAYMENT_NOTIFICATION');
        $history->payment_id = $payment->payment_id;
        $history->payment_method = $payment->payment_type;
        $history->type = 'payment';
        $history->notified = $notify;

        $info = JText::_('PAYZENMULTI_RESULT') . $payzenmultiResponse->getMessage();

        $info .= ' | ' . JText::_('PAYZENMULTI_TRANS_ID') . $payzenmultiResponse->get('trans_id');

        if ($payzenmultiResponse->get('card_brand')) {
            $info .= ' | ' . JText::_('PAYZENMULTI_CC_TYPE') . $payzenmultiResponse->get('card_brand');

            // Add  card brand user choice.
            if ($payzenmultiResponse->get('brand_management')) {
                $brand_info = json_decode($payzenmultiResponse->get('brand_management'));
                $msg_brand_choice = '';

                if (isset($brand_info->userChoice) && $brand_info->userChoice) {
                    $msg_brand_choice .= JText::_('PAYZENMULTI_CARD_BRAND_BUYER_CHOICE');
                } else {
                    $msg_brand_choice .= JText::_('PAYZENMULTI_CARD_BRAND_DEFAULT_CHOICE');
                }

                $info .= ' (' . $msg_brand_choice . ')';
            }
        }

        if ($payzenmultiResponse->get('card_number')) {
            $info .= ' | ' . JText::_('PAYZENMULTI_CC_NUMBER') . $payzenmultiResponse->get('card_number');
        }

        if ($payzenmultiResponse->get('expiry_month') && $payzenmultiResponse->get('expiry_year')) {
            $info .= ' | ' . JText::_('PAYZENMULTI_CC_EXPIRY') .
                 str_pad($payzenmultiResponse->get('expiry_month'), 2, '0', STR_PAD_LEFT) . ' / ' .
                 $payzenmultiResponse->get('expiry_year');
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
        $this->title = JText::_('PAYZENMULTI_CONFIG_PAGE_TITLE');
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
        $element->payment_name = JText::_('PAYZENMULTI_DEFAULT_TITLE');
        $element->payment_description = JText::_('PAYZENMULTI_DEFAULT_DESCRIPTION');
        $element->payment_images = 'payzenmulti_cards';

        // Default values.
        $element->payment_params->payzenmulti_site_id = '12345678';
        $element->payment_params->payzenmulti_key_test = '1111111111111111';
        $element->payment_params->payzenmulti_key_prod = '2222222222222222';
        $element->payment_params->payzenmulti_ctx_mode = 'TEST';
        $element->payment_params->payzenmulti_sign_algo = 'SHA-256';
        $element->payment_params->payzenmulti_platform_url = 'https://secure.payzen.eu/vads-payment/';
        $element->payment_params->payzenmulti_language = 'fr';
        $element->payment_params->payzenmulti_available_languages = '';
        $element->payment_params->payzenmulti_capture_delay = '';
        $element->payment_params->payzenmulti_validation_mode = '';
        $element->payment_params->payzenmulti_payment_cards = '';
        $element->payment_params->payzenmulti_threeds_amount_min = '';
        $element->payment_params->payzenmulti_redirect_enabled = 0;
        $element->payment_params->payzenmulti_redirect_success_timeout = '5';
        $element->payment_params->payzenmulti_redirect_success_message = JText::_('PAYZENMULTI_REDIRECT_SUCCESS_MESSAGE_DFEAULT');
        $element->payment_params->payzenmulti_redirect_error_timeout = '5';
        $element->payment_params->payzenmulti_redirect_error_message = JText::_('PAYZENMULTI_REDIRECT_ERROR_MESSAGE_DFEAULT');
        $element->payment_params->payzenmulti_return_mode = 'GET';
        $element->payment_params->payzenmulti_verified_status = 'confirmed';
        $element->payment_params->payzenmulti_invalid_status = 'cancelled';
    }

    /**
     * Called before save plugin configuration.
     *
     * @param $element
     * @return boolean
     */
    function onPaymentConfigurationSave(&$element)
    {
        $langs = @$element->payment_params->payzenmulti_available_languages;
        if (! is_array($langs)) {
            $langs = array();
        }

        $element->payment_params->payzenmulti_available_languages = implode(';', $langs);

        $cards = @$element->payment_params->payzenmulti_payment_cards;
        if (! is_array($cards)) {
            $cards = array();
        }

        $element->payment_params->payzenmulti_payment_cards = implode(';', $cards);

        // Configuration fields validation.
        $errors = array();
        $multiOptions = @$element->payment_params->payzen_multi_options;
        if (! is_array($multiOptions)) {
            $multiOptions = array();
        }

        $line = 1;
        foreach ($multiOptions as $option) {
            $count = $option['count'];
            $period = $option['period'];
            $first = $option['first'];

            if (! is_numeric($count) || $count < 0) {
                $errors[] = sprintf(JText::_('PAYZENMULTI_ERROR_SAVE_MULTI_OPTION'), JText::_('PAYZENMULTI_COUNT'),
                    $line);
            }

            if (! is_numeric($period) || $period < 0) {
                $errors[] = sprintf(JText::_('PAYZENMULTI_ERROR_SAVE_MULTI_OPTION'), JText::_('PAYZENMULTI_PERIOD'),
                    $line);
            }

            if ($first && (! is_numeric($first)) || $first < 0 || $first > 100) {
                $errors[] = sprintf(JText::_('PAYZENMULTI_ERROR_SAVE_MULTI_OPTION'), JText::_('PAYZENMULTI_FIRST'),
                    $line);
            }

            $line ++;
        }

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
            unset($params['3']); // ctx_mode
        }

        // Instanciate PayzenRequest to validate parameters.
        require_once rtrim(JPATH_ADMINISTRATOR, DS) . DS . 'components' . DS . 'com_payzen' . DS . 'classes' . DS .
             'payzen_request.php';
        $request = new PayzenRequest();

        foreach ($params as $param) {
            $paramName = 'payzenmulti_' . $param;
            $value = @$element->payment_params->$paramName;

            if (! $request->set($param, $value)) {
                $errors[] = sprintf(JText::_('PAYZENMULTI_ERROR_SAVE'), JText::_($paramName));
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

    // Apply amount resrictions of each option of the payment in installements then show the available options in
    // frontend.
    function onPaymentDisplay(&$order, &$methods, &$usable_methods)
    {
        if (isset($methods)) {
            $order_total = $order->full_total->prices[0]->price_value_with_tax;
            foreach ($methods as $key => $method) {
                if ($method->payment_type === $this->name) {
                    $multiOptions = property_exists($method->payment_params, "payzen_multi_options") ? $this->_getAvailbleMultiOptions($method->payment_params->payzen_multi_options,
                        $order_total) : array();

                    if (! count($multiOptions)) {
                        unset($methods[$key]);
                    } else {
                        $method->custom_html = $this->_getCustomHtml($multiOptions);
                    }
                }
            }
        }

        return parent::onPaymentDisplay($order, $methods, $usable_methods);
    }

    function _getAvailbleMultiOptions($options, $order_total = null)
    {
        if (! is_array($options) && ! count($options)) {
            return array();
        }

        if (! $order_total) {
            return $options;
        }

        $available_options = array();
        foreach ($options as $key => $option) {
            $multi_amount_min = $option['amount_min'];
            $multi_amount_max = $option['amount_max'];
            if (($multi_amount_max && $order_total > $multi_amount_max) ||
                 ($multi_amount_min && $order_total < $multi_amount_min)) {
                continue;
            }

            $available_options[$key] = $option;
        }

        return $available_options;
    }

    // Show payment in installements options in frontend.
    function _getCustomHtml($multiOptions)
    {
        $title = (count($multiOptions) === 1) ? JText::_('PAYZENMULTI_ONE_OPTION_SELECT_TITLE') :
            JText::_('PAYZENMULTI_SEVERAL_OPTIONS_SELECT_TITLE');

        $result = array();
        $selected = false;
        $first = true;
        foreach ($multiOptions as $key => $option) {
            $result[] = JHTML::_('select.option', $key, $option['label']);

            if ($first) {
                $selected = $key;
                $first = false;
            }
        }

        $session = JFactory::getSession();
        if (($key = $session->get('payzen_multi_option')) && isset($multiOptions[$key])) {
            $selected = $key;
        }

        $onclick = '';
        $config = hikashop_config();
        if ($config->get('auto_submit_methods', 1)) {
            $onclick = ' onclick="this.form.action=this.form.action+\'#hikashop_payment_methods\'; this.form.submit(); return false;"';
        }

        $html = '<div style="margin-left:10%; ">';
        $html .= '<span style="font-weight: bold;">' . $title . '</span>';
        $html .= JHTML::_('select.radiolist', $result, 'payzen_multi_option', 'class="inputbox" size="1" ' . $onclick,
            'value', 'text', $selected);
        $html .= '</div>';

        return $html;
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

        if (! (JFile::exists($destFolder . DS . 'payzenmulti_cards.png'))) {
            JFile::copy($sourceFolder . DS . 'payzenmulti_cards.png', $destFolder . DS . 'payzenmulti_cards.png');
        }

        if (! (JFile::exists($destFolder . DS . 'payzenmulti.png'))) {
            JFile::copy($sourceFolder . DS . 'payzenmulti.png', $destFolder . DS . 'payzenmulti.png');
        }
    }

    public static function getAvailableMultiCards()
    {
        $multi_cards = array(
            'AMEX',
            'CB',
            'DINERS',
            'DISCOVER',
            'E-CARTEBLEUE',
            'JCB',
            'MASTERCARD',
            'PRV_BDP',
            'PRV_BDT',
            'PRV_OPT',
            'PRV_SOC',
            'VISA',
            'VISA_ELECTRON',
            'VPAY'
        );

        $all_cards = PayzenApi::getSupportedCardTypes();
        $avail_cards = array();

        foreach ($all_cards as $key => $value) {
            if (in_array($key, $multi_cards)) {
                $avail_cards[$key] = $value;
            }
        }

        return $avail_cards;
    }

    function log($msg, $level = 'INFO')
    {
        $date = date('Y-m-d H:i:s', time());
        $fLog = @fopen(rtrim(JPATH_ADMINISTRATOR, DS) . DS . 'logs' . DS . 'payzenmulti.log', 'a');

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
