<?php

namespace Stripe\ApiOperations;

trait Request
{
    protected static function _validateParams($params = null)
    {
        if ($params && !is_array($params)) {
            $message = "You must pass an array as the first argument to Stripe API "
               . "method calls.  (HINT: an example call to create a charge "
               . "would be: \"Stripe\\Charge::create(['amount' => 100, "
               . "'currency' => 'usd', 'source' => 'tok_1234'])\")";
            throw new \Stripe\Error\Api($message);
        }
    }

    protected function _request($method, $url, $params = [], $options = null)
    {
        $opts = $this->_opts->merge($options);
        list($resp, $options) = static::_staticRequest($method, $url, $params, $opts);
        $this->setLastResponse($resp);
        return [$resp->json, $options];
    }

    protected static function _staticRequest($method, $url, $params, $options)
    {
        $opts = \Stripe\Util\RequestOptions::parse($options);
        $baseUrl = isset($opts->apiBase) ? $opts->apiBase : static::baseUrl();
        $requestor = new \Stripe\ApiRequestor($opts->apiKey, $baseUrl);
        list($response, $opts->apiKey) = $requestor->request($method, $url, $params, $opts->headers);
        $opts->discardNonPersistentHeaders();
        return [$response, $opts];
    }
}
