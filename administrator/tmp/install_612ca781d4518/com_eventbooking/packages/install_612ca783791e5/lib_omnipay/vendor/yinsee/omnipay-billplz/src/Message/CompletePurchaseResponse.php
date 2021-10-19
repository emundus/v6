<?php

namespace Omnipay\Billplz\Message;

use Omnipay\Common\Message\AbstractResponse;

class CompletePurchaseResponse extends AbstractResponse
{
    protected $statusCode;

    public function __construct($request, $data, $statusCode = 200)
    {
        parent::__construct($request, $data);
        $this->statusCode = $statusCode;
    }

    public function isSuccessful()
    {
        return (isset($this->data['state']) && $this->data['state'] == 'paid');
    }
}
