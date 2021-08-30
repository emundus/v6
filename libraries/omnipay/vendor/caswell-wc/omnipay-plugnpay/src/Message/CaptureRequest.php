<?php

namespace Omnipay\PlugNPay\Message;

/***
 * Class CaptureRequest
 *
 * #### Auth Only Completion
 *
 * Create your object as shown in the documentation for the Gateway class then run some variation of this code
 * <code>
 * try {
 *     $transaction = $gateway->capture(array(
 *         'amount'                 => '10.00',
 *         'transactionReference'   => '123456'
 *     ));
 *     $response = $transaction->send();
 *     $data = $response->getData();
 *     echo "Gateway capture response data == " . print_r($data, true) . "\n";
 *
 *     if ($response->isSuccessful()) {
 *         echo "Capture transaction was successful!\n";
 *     }
 * } catch (\Exception $e) {
 *     echo "Exception caught while attempting capture.\n";
 *     echo "Exception type == " . get_class($e) . "\n";
 *     echo "Message == " . $e->getMessage() . "\n";
 * }
 * </code>
 *
 * @package Omnipay\PlugNPay\Message
 */
class CaptureRequest extends AbstractTransactionAdminRequest
{
    /** @var string Tells PlugNPay that this is a mark request */
    protected $mode = 'mark';
}
