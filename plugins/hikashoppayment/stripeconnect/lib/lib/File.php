<?php

namespace Stripe;

class File extends ApiResource
{
    const OBJECT_NAME = "file";
    const OBJECT_NAME_ALT = "file_upload";

    use ApiOperations\All;
    use ApiOperations\Create {
        create as protected _create;
    }
    use ApiOperations\Retrieve;

    public static function classUrl()
    {
        return '/v1/files';
    }

    public static function create($params = null, $options = null)
    {
        $opts = \Stripe\Util\RequestOptions::parse($options);
        if (is_null($opts->apiBase)) {
            $opts->apiBase = Stripe::$apiUploadBase;
        }
        $flatParams = [];
        foreach (\Stripe\Util\Util::flattenParams($params) as $pair) {
            $flatParams[$pair[0]] = $pair[1];
        }
        return static::_create($flatParams, $opts);
    }
}
