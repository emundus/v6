<?php

namespace Omnipay\PlugNPay\Message;


use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;

/**
 * Class Response
 * Note: This is used for voids and returns which plugnpay does not send response codes for so there is no need for a
 * getCode method.
 *
 * @package Omnipay\PlugNPay\Message
 */
class Response extends AbstractResponse
{

    /**
     * Response constructor.
     *
     * @param \Omnipay\Common\Message\RequestInterface $request
     * @param string                                   $data
     */
    public function __construct(RequestInterface $request, $data)
    {
        $this->request = $request;
        parse_str($data, $this->data);
    }

    /**
     * Determine if the transaction was successful
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->data['success'] == 'yes' || $this->data['FinalStatus'] == 'success';
    }

    /**
     * Get the message sent by PlugNPay
     *
     * @return string
     */
    public function getMessage()
    {
        if ($this->isSuccessful()) {
            if (!empty($this->data['aux-msg'])) {
                return $this->data['aux-msg'];
            }
            if (!empty($this->data['Aux-msg'])) {
                return $this->data['Aux-msg'];
            }
            return $this->data['FinalStatus'];
        }
        return $this->data['MErrMsg'];
    }
}
