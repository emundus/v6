<?php
namespace Payplug\Core;

class CurlRequest implements IHttpRequest
{
    private $_curl;

    public function __construct()
    {
        $this->_curl = curl_init();
    }

    public function setopt($option, $value)
    {
        return curl_setopt($this->_curl, $option, $value);
    }

    public function getinfo($option)
    {
        return curl_getinfo($this->_curl, $option);
    }

    public function exec()
    {
        return curl_exec($this->_curl);
    }

    public function close()
    {
        curl_close($this->_curl);
    }

    public function error()
    {
        return curl_error($this->_curl);
    }

    public function errno()
    {
        return curl_errno($this->_curl);
    }
}
