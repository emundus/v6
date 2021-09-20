<?php


namespace Omnipay\PayU\Message\Latam;


use Guzzle\Http\Exception\ServerErrorResponseException;
use Omnipay\Common\Message\ResponseInterface;

class PurchaseRequest extends AbstractRequest
{

    public function getData()
    {
        $this->validate(
            "merchantId",
            "accountId",
            "siteName",
            "transactionReference",
            "currency",
            "buyerEmail",
            "apiKey"
        );
        $tax     = $this->getParameter("tax");
        $taxBase = $this->getParameter("taxBase");
        return [
            "merchantId"      => $this->getParameter("merchantId"),
            "signature"       => $this->generateSignature(),
            "accountId"       => $this->getParameter("accountId"),
            "referenceCode"   => $this->getTransactionReference(),
            "description"     => "{$this->getDescription()}",
            "amount"          => str_replace(".00", "", $this->getAmount()),
            "tax"             => str_replace(".00", "", $this->formatCurrency($tax ? $tax : 0)),
            "taxReturnBase"   => str_replace(".00", "", $this->formatCurrency($taxBase ? $taxBase : 0)),
            "buyerEmail"      => $this->getParameter("buyerEmail"),
            "test"            => "" . intval($this->getTestMode()),
            "responseUrl"     => $this->getReturnUrl(),
            "confirmationUrl" => $this->getNotifyUrl(),
            "currency" => $this->getCurrency(),
        ];
    }

    protected function generateSignature()
    {
        $amount = $this->getAmount();
        $amount = str_replace(".00", "", $amount);
        $args   = [
            $this->getParameter("apiKey"),
            $this->getParameter("merchantId"),
            $this->getTransactionReference(),
            $amount,
            $this->getCurrency()
        ];

        return md5(implode("~", $args));
    }

    public function setMerchantId($merchantId)
    {
        $this->parameters->set("merchantId", $merchantId);
    }

    public function setAccountId($accountId)
    {
        $this->parameters->set("accountId", $accountId);
    }

    public function setSiteName($siteName)
    {
        $this->parameters->set("siteName", $siteName);
    }

    public function setBuyerEmail($buyerEmail)
    {
        $this->parameters->set("buyerEmail", $buyerEmail);
    }

    public function setApiKey($apiKey)
    {
        $this->parameters->set("apiKey", $apiKey);
    }

    public function sendData($data)
    {
        return $this->createResponse($data, "Omnipay\\PayU\\Message\\Latam\\PurchaseResponse");
    }


}
