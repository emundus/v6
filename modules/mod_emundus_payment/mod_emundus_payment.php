<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

$app = Factory::getApplication();
$user = Factory::getSession()->get('emundusUser');

if (!empty($user) && !empty($user->fnum)) {
    require_once dirname(__FILE__).'/helper.php';
    require_once (JPATH_SITE . '/components/com_emundus/models/payment.php');
    $m_payment = new EmundusModelPayment();
    $helper = new modEmundusPaymentHelper();

    if ($helper->doINeedToPay($user->fnum) === true) {
        if ($helper->didIAlreadyPay($user->fnum) === false) {
            Text::script('MOD_EMUNDUS_PAYMENT_FILL_FORM_ERROR_TITLE');
            Text::script('MOD_EMUNDUS_PAYMENT_FILL_FORM_ERROR_TEXT');
            Text::script('MOD_EMUNDUS_PAYMENT_SWAL_TITLE_ERROR');
            Text::script('MOD_EMUNDUS_PAYMENT_SWAL_NO_FILE_UPLOADED');
            Text::script('MOD_EMUNDUS_PAYMENT_OK');
            Text::script('MOD_EMUNDUS_PAYMENT_SWAL_INVALID_FILE_TYPE');

            if (!empty($params['payment_methods'])) {
                $document = Factory::getDocument();
                $document->addStyleSheet(Uri::base(). '/modules/mod_emundus_payment/assets/css/default.scss');

                $params['payment_methods'] = json_decode($params['payment_methods'], true);
                $selected_payment_method = null;
				
                /**
                 * I need to know if I have to choose a payment method or not
                 * for that :
                 *  - More than one payment method configured
                 *  - No payment is initiated and just waiting for validation
                 *  - No payment_method in URL
                 */
                if (sizeof($params['payment_methods']['payment_method']) > 1) {
                    $paymentStarted = $helper->didIStartPayment($user->fnum);

                    if (!empty($paymentStarted) && $helper->waitForValidation($paymentStarted)) {
                        $selected_payment_method = $paymentStarted->order_type;
                    } else {
                        $method_url_param = $app->input->getString('payment_method', '');
                        if (!empty($method_url_param)) {
                            $selected_payment_method = $method_url_param;
                        } else {
                            $document->addStyleSheet(Uri::base(). '/modules/mod_emundus_payment/assets/css/selector.scss');
                            require ModuleHelper::getLayoutPath('mod_emundus_payment', 'select-payment-method');
                            return;
                        }
                    }
                } else {
                    $selected_payment_method = $params['payment_methods']['payment_method'][0];
                }

                $payment = $helper->getPaymentInfos($user->fnum);

                if (!empty($payment) && !empty($payment->product_id)) {
                    $isScholarshipHolder = $helper->isScholarshipStudent($user->fnum);

                    if ($isScholarshipHolder && $helper->doesScholarshipHoldersNeedToPay() === false) {
                        Log::add('User ('. $user->fnum .') is a scholarship holder and does not need to pay. He should not be here ', Log::WARNING, 'com_emundus');
                        $app->enqueueMessage(Text::_('MOD_EMUNDUS_PAYMENT_SCHOLARSHIP_HOLDERS_DO_NOT_NEED_TO_PAY'));
                        $app->redirect('/');
                    }

                    if (!empty($payment->scholarship_holder_product_id) && $isScholarshipHolder === true) {
                        $product = $helper->getProduct($payment->scholarship_holder_product_id);
                    } else {
                        $product = $helper->getProduct($payment->product_id);
                    }

                    if (!empty($product)) {
                        if ($product->product_sort_price < 1) {
                            // TODO: should we change status to paid ?

                            Log::add('Product price is 0 or less than 0 for fnum ('. $user->fnum .')', Log::INFO, 'com_emundus');
                            $app->enqueueMessage(Text::_('MOD_EMUNDUS_PAYMENT_PRODUCT_PRICE_ZERO'));
                            $app->redirect('/');
                        }

                        $layout = 'default';
                        $campaign = $helper->getCampaign($payment->campaign_id);

                        switch($selected_payment_method) {
                            case 'flywire':
                                $document->addStyleSheet(JUri::base(). '/modules/mod_emundus_payment/assets/css/flywire.scss');

                                $jinput = $app->input;
                                $status = $jinput->get('status', '', 'STRING');
                                $currentPayment = $helper->didIStartPayment($user->fnum);
                                $config = $helper->getFlywireConfig($user->fnum);

                                if (empty($status)) {
                                    if ($currentPayment->orderStatus == 'confirmed') {
                                        $status = 'confirmed';
                                    } else if (!empty($config) && $config['initiator'] == 'flywire') {
                                        // Si le paiement est initié côté Flywire, on ne doit pas affiché la page pour procéder au paiement
                                        if ($config['flywire_status'] != 'cancelled') {
                                            $status = 'pending';
                                        }
                                    }
                                }

                                switch($status) {
                                    case 'success':
                                    case 'initiated':
                                    case 'pending':
                                        $layout = 'flywire-success';
                                        break;
                                    case 'cancelled':
                                    case 'cancel':
                                    case 'error':
                                        $layout = 'flywire-cancelled';
                                        break;
                                    default:
                                        $layout = 'flywire';
                                        $countries = $helper->getCountryCodes();
                                        break;
                                }

                                break;
                            case 'transfer':
                                $layout = 'transfer';
								break;
                            case 'axepta':
                                $sort_price = str_replace(',', '', $product->product_sort_price);
                                $price = number_format((double)$sort_price, 2, '.', ' ');

                                $payment_url = $helper->getAxeptaConfig($params,$user->fnum,$product);

                                $layout = 'axepta';
								break;
                            case 'hikashop':
                            default:
                                // TODO: integration  of hikashop payment within the module
                                //$config = $helper->getHikashopConfig();
                                break;
                        }

                        require ModuleHelper::getLayoutPath('mod_emundus_payment', $layout);
                    } else {
                        Log::add('Error getting product infos from product_id ('. $payment->product_id .' / ' . $payment->scholarship_holder_product_id . ')', Log::ERROR, 'com_emundus');
                        $app->enqueueMessage(Text::_('MOD_EMUNDUS_PAYMENT_ERROR_PRODUCT_NOT_FOUND'), 'error');
                        $app->redirect('/');
                    }
                } else {
                    Log::add('Error getting payment infos from fnum ('. $user->fnum .')', Log::ERROR, 'com_emundus');
                    $app->enqueueMessage(Text::_('MOD_EMUNDUS_PAYMENT_ERROR_GETTING_PAYMENT_INFOS'), 'error');
                    $app->redirect('/');
                }
            } else {
                Log::add('No payment methods configured.', Log::ERROR, 'com_emundus');
                $app->enqueueMessage(Text::_('MOD_EMUNDUS_PAYMENT_NO_PAYMENT_METHODS_SET'), 'error');
                $app->redirect('/');
            }
        } else {
            Log::add('User ('. $user->fnum .') already paid. He should not be on this page.', Log::INFO, 'com_emundus');
            $app->enqueueMessage(Text::_('MOD_EMUNDUS_PAYMENT_ALREADY_PAID'), 'notice');
            $app->redirect('/');
        }
    } else {
        Log::add('User ('. $user->fnum .') does not need to pay, he should not be here', Log::WARNING, 'com_emundus');
        $app->enqueueMessage(Text::_('MOD_EMUNDUS_PAYMENT_NO_PAYMENT_NEEDED'), 'notice');
        $app->redirect('/');
    }
} else {
    Log::add('Missing user session for payment, redirect to homepage', Log::WARNING, 'com_emundus');
    $app->enqueueMessage(Text::_('MOD_EMUNDUS_PAYMENT_LOGIN_REQUIRED'), 'error');
    $app->redirect(Uri::base());
}
