<?php

namespace Omnipay\PlugNPay\Message;

/**
 * Class VoidRequest
 *
 * #### Void transaction
 *
 * Create your object as shown in the documentation for the Gateway class then run some variation of this code
 * <code>
 * try {
 *     $transaction = $gateway->void(array(
 *         'amount'                 => '10.00',
 *         'transactionReference'   => '123456'
 *     ));
 *     $response = $transaction->send();
 *     $data = $response->getData();
 *     echo "Gateway void response data == " . print_r($data, true) . "\n";
 *
 *     if ($response->isSuccessful()) {
 *         echo "Void transaction was successful!\n";
 *     }
 * } catch (\Exception $e) {
 *     echo "Exception caught while attempting void.\n";
 *     echo "Exception type == " . get_class($e) . "\n";
 *     echo "Message == " . $e->getMessage() . "\n";
 * }
 * </code>
 *
 * @package Omnipay\PlugNPay\Message
 */
class VoidRequest extends AbstractTransactionAdminRequest
{
    /** @var string Tells PlugNPay to do a void */
    protected $mode = 'void';

    /**
     * Voids also have a txn-type which they say to always set to 'auth'
     *
     * @return array
     */
    public function getData()
    {
        $voidData = [
            'txn-type'=>'auth'
        ];

        return array_merge($voidData, parent::getData());
    }
}
