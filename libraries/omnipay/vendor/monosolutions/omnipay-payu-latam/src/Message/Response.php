<?php


namespace Omnipay\PayU\Message;


use Omnipay\Common\Message\AbstractResponse;

class Response extends AbstractResponse
{
    public function isSuccessful()
    {
        return false;
    }


}
