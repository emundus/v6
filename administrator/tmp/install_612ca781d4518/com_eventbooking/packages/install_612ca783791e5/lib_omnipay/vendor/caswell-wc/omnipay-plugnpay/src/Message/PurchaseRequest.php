<?php

namespace Omnipay\PlugNPay\Message;

/**
 * Class PurchaseRequest
 *
 * ##Run a purchase transaction
 * Create your object as shown in the documentation for the Gateway class then run some variation of this code
 * <code>
 * //create the card object
 * $card = new CreditCard(array(
 *     'firstName'             => 'Example',
 *     'lastName'              => 'Customer',
 *     'number'                => '4111111111111111',
 *     'expiryMonth'           => '01',
 *     'expiryYear'            => '2020',
 *     'cvv'                   => '123',
 *     'billingAddress1'       => '1 Scrubby Creek Road',
 *     'billingCountry'        => 'AU',
 *     'billingCity'           => 'Scrubby Creek',
 *     'billingPostcode'       => '4999',
 *     'billingState'          => 'QLD',
 * ));
 *
 * // Do a purchase transaction on the gateway
 * try {
 *     $transaction = $gateway->purchase(array(
 *         'amount'        => '10.00',
 *         'currency'      => 'USD',
 *         'card'          => $card,
 *     ));
 *     $response = $transaction->send();
 *     $data = $response->getData();
 *     echo "Gateway purchase response data == " . print_r($data, true) . "\n";
 *
 *     if ($response->isSuccessful()) {
 *         echo "Purchase transaction was successful!\n";
 *     }
 * } catch (\Exception $e) {
 *     echo "Exception caught while attempting purchase.\n";
 *     echo "Exception type == " . get_class($e) . "\n";
 *     echo "Message == " . $e->getMessage() . "\n";
 * }
 * </code>
 *
 * @package Omnipay\PlugNPay\Message
 */
class PurchaseRequest extends AuthorizeRequest
{
    /** @var string Tells PlugNPay to mark it for settlement */
    protected $authType = 'authpostauth';
}
