<?php
namespace Payplug\Exception;

class HttpException extends PayplugException
{
    private $_httpResponse;

    public function __construct($message, $httpResponse = null, $code = 0)
    {
        $this->_httpResponse = $httpResponse;
        parent::__construct($message, $code);
    }

    public function __toString()
    {
        return parent::__toString() . "; HTTP Response: {$this->_httpResponse}";
    }

    public function getHttpResponse()
    {
        return $this->_httpResponse;
    }

    public function getErrorObject()
    {
        return json_decode($this->_httpResponse, true);
    }
}
