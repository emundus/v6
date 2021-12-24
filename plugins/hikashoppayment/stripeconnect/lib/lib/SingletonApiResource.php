<?php

namespace Stripe;

abstract class SingletonApiResource extends ApiResource
{
    protected static function _singletonRetrieve($options = null)
    {
        $opts = Util\RequestOptions::parse($options);
        $instance = new static(null, $opts);
        $instance->refresh();
        return $instance;
    }

    public static function classUrl()
    {
        $base = str_replace('.', '/', static::OBJECT_NAME);
        return "/v1/${base}";
    }

    public function instanceUrl()
    {
        return static::classUrl();
    }
}
