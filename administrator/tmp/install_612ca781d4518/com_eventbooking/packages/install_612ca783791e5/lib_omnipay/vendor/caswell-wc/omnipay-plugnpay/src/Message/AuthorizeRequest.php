<?php

namespace Omnipay\PlugNPay\Message;


use Omnipay\Common\Exception\InvalidCreditCardException;
use Guzzle\Http\Message\Response as GuzzleResponse;

/**
 * Class AuthorizeRequest
 *
 * ##Run an authorize only transaction
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
 * // Do an authorize transaction on the gateway
 * try {
 *     $transaction = $gateway->authorize(array(
 *         'amount'        => '10.00',
 *         'currency'      => 'USD',
 *         'card'          => $card,
 *     ));
 *     $response = $transaction->send();
 *     $data = $response->getData();
 *     echo "Gateway authorize response data == " . print_r($data, true) . "\n";
 *
 *     if ($response->isSuccessful()) {
 *         echo "Authorize transaction was successful!\n";
 *     }
 * } catch (\Exception $e) {
 *     echo "Exception caught while attempting authorize.\n";
 *     echo "Exception type == " . get_class($e) . "\n";
 *     echo "Message == " . $e->getMessage() . "\n";
 * }
 * </code>
 *
 * @package Omnipay\PlugNPay\Message
 */
class AuthorizeRequest extends AbstractRequest
{
    /** @var string Tells PlugNPay to not mark the transaction for settlement */
    protected $authType = 'authonly';
    /** @var string Tells PlugNPay that this is an authorization */
    protected $mode = 'auth';

    /**
     * Get the data needed for an authorization.
     *
     * @return array
     */
    public function getData()
    {
        $this->validate('amount', 'card');
        $baseData = $this->getBaseData();
        $baseData['authtype'] = $this->authType;

        $card = $this->getCard();
        $card->validate();
        if (!$card->getName()) {
            throw new InvalidCreditCardException('Name missing from credit card.');
        }

        $billingData = [
            'card-name'=>$card->getName(),
            'card-number'=>$card->getNumber(),
            'card-exp'=>$card->getExpiryDate('m/y'),
            'card-cvv'=>$card->getCvv(),
            'card-amount'=>$this->getAmount(),
            'currency'=>$this->getCurrency(),
            'card-address1'=>$card->getAddress1(),
            'card-address2'=>$card->getAddress2(),
            'card-city'=>$card->getCity(),
            'card-state'=>$card->getState(),
            'card-zip'=>$card->getPostcode(),
            'card-country'=>$card->getCountry()
        ];

        $shippingData = [
            'shipname'=>$card->getShippingName(),
            'address1'=>$card->getShippingAddress1(),
            'address2'=>$card->getShippingAddress2(),
            'city'=>$card->getShippingCity(),
            'state'=>$card->getShippingState(),
            'zip'=>$card->getShippingPostcode(),
            'country'=>$card->getShippingCountry()
        ];

        return array_merge($baseData, $billingData, $shippingData);

    }

    /**
     * Instantiate a new AuthorizeResponse object which extends the Response object to add a few new functions.
     *
     * @param \Guzzle\Http\Message\Response $httpResponse
     *
     * @return \Omnipay\PlugNPay\Message\AuthorizeResponse
     */
    public function generateResponse(GuzzleResponse $httpResponse)
    {
        return $this->response = new AuthorizeResponse($this, $httpResponse->getBody());
    }
}
