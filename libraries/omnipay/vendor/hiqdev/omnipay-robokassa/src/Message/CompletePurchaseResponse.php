<?php
/**
 * RoboKassa driver for Omnipay PHP payment library.
 *
 * @link      https://github.com/hiqdev/omnipay-robokassa
 * @package   omnipay-robokassa
 * @license   MIT
 * @copyright Copyright (c) 2015-2017, HiQDev (http://hiqdev.com/)
 */

namespace Omnipay\RoboKassa\Message;

use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;

/**
 * RoboKassa Complete Purchase Response.
 */
class CompletePurchaseResponse extends AbstractResponse
{
    /** @var RequestInterface|CompletePurchaseRequest */
    protected $request;

    public function __construct(RequestInterface $request, $data)
    {
        $this->request = $request;
        $this->data    = $data;

        if (strtolower($this->getSignatureValue()) !== $this->generateSignature()) {
            throw new InvalidResponseException('Invalid hash');
        }
    }

    public function generateSignature()
    {
        $params = [
            $this->getAmount(),
            $this->getTransactionReference(),
            $this->request->getSecretKey2()
        ];

        foreach ($this->getCustomFields() as $field => $value) {
            $params[] = "$field=$value";
        }

        return md5(implode(':', $params));
    }

    public function getCustomFields()
    {
        $fields = array_filter([
            'Shp_TransactionId' => $this->getTransactionId(),
            'Shp_Client' => $this->getClient(),
            'Shp_Currency' => $this->getCurrency(),
        ]);

        ksort($fields);

        return $fields;
    }

    public function getSignatureValue()
    {
        return $this->data['SignatureValue'];
    }

    public function getClient()
    {
        return $this->data['Shp_Client'];
    }

    public function getAmount()
    {
        return $this->data['OutSum'];
    }

    public function getPayer()
    {
        return $this->data['PaymentMethod'];
    }

    public function getTransactionId()
    {
        return $this->data['Shp_TransactionId'];
    }

    public function getCurrency()
    {
        return $this->data['Shp_Currency'];
    }

    /**
     * RoboKassa does not provide real transaction reference (ID of payment on RoboKassa side) 😕
     *
     * @return string The InvId property, or 0 (zero) if InvId is not set.
     */
    public function getTransactionReference()
    {
        return $this->data['InvId'] ?? '0';
    }

    public function getInvId()
    {
        return $this->getTransactionReference();
    }

    public function isSuccessful()
    {
        return true;
    }
}
