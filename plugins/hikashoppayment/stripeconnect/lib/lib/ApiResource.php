<?php

namespace Stripe;

abstract class ApiResource extends StripeObject
{
    use ApiOperations\Request;

    public static function getSavedNestedResources()
    {
        static $savedNestedResources = null;
        if ($savedNestedResources === null) {
            $savedNestedResources = new Util\Set();
        }
        return $savedNestedResources;
    }

    public $saveWithParent = false;

    public function __set($k, $v)
    {
        parent::__set($k, $v);
        $v = $this->$k;
        if ((static::getSavedNestedResources()->includes($k)) &&
            ($v instanceof ApiResource)) {
            $v->saveWithParent = true;
        }
        return $v;
    }

    public function refresh()
    {
        $requestor = new ApiRequestor($this->_opts->apiKey, static::baseUrl());
        $url = $this->instanceUrl();

        list($response, $this->_opts->apiKey) = $requestor->request(
            'get',
            $url,
            $this->_retrieveOptions,
            $this->_opts->headers
        );
        $this->setLastResponse($response);
        $this->refreshFrom($response->json, $this->_opts);
        return $this;
    }

    public static function baseUrl()
    {
        return Stripe::$apiBase;
    }

    public static function classUrl()
    {
        $base = str_replace('.', '/', static::OBJECT_NAME);
        return "/v1/${base}s";
    }

    public static function resourceUrl($id)
    {
        if ($id === null) {
            $class = get_called_class();
            $message = "Could not determine which URL to request: "
               . "$class instance has invalid ID: $id";
            throw new Error\InvalidRequest($message, null);
        }
        $id = Util\Util::utf8($id);
        $base = static::classUrl();
        $extn = urlencode($id);
        return "$base/$extn";
    }

    public function instanceUrl()
    {
        return static::resourceUrl($this['id']);
    }
}
