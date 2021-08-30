<?php

namespace Omnipay\PlugNPay\Message;


/**
 * Class RefundRequest
 *
 * #### Refund transaction
 *
 * Create your object as shown in the documentation for the Gateway class then run some variation of this code
 * <code>
 * try {
 *     $transaction = $gateway->refund(array(
 *         'amount'                 => '10.00',
 *         'transactionReference'   => '123456',
 *         'currency'               => 'USD'
 *     ));
 *     $response = $transaction->send();
 *     $data = $response->getData();
 *     echo "Gateway refund response data == " . print_r($data, true) . "\n";
 *
 *     if ($response->isSuccessful()) {
 *         echo "Refund transaction was successful!\n";
 *     }
 * } catch (\Exception $e) {
 *     echo "Exception caught while attempting refund.\n";
 *     echo "Exception type == " . get_class($e) . "\n";
 *     echo "Message == " . $e->getMessage() . "\n";
 * }
 * </code>
 *
 * @package Omnipay\PlugNPay\Message
 */
class RefundRequest extends AbstractTransactionAdminRequest
{
    /** @var string Tells PlugNPay that this is a return */
    protected $mode = 'return';

    /**
     * The return also includes the currency which may be required if your account has multicurrency.
     * @return array
     */
    public function getData()
    {
        $refundData = [
            'currency'=>$this->getCurrency()
        ];

        return array_merge($refundData, parent::getData());
    }
}
