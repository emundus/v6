<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.6.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2022 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

namespace PayPalHttp\Serializer;

use PayPalHttp\HttpRequest;
use PayPalHttp\Serializer;

class Form implements Serializer
{
    public function contentType()
    {
        return "/^application\/x-www-form-urlencoded$/";
    }

    public function encode(HttpRequest $request)
    {
        if (!is_array($request->body) || !$this->isAssociative($request->body))
        {
            throw new \Exception("HttpRequest body must be an associative array when Content-Type is: " . $request->headers["Content-Type"]);
        }

        return http_build_query($request->body);
    }

    public function decode($body)
    {
        throw new \Exception("CurlSupported does not support deserialization");
    }

    private function isAssociative(array $array)
    {
        return array_values($array) !== $array;
    }
}
