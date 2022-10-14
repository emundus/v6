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

namespace PayPalCheckoutSdk\Core;

use PayPalHttp\HttpRequest;

class RefreshTokenRequest extends HttpRequest
{
    public function __construct(PayPalEnvironment $environment, $authorizationCode)
    {
        parent::__construct("/v1/identity/openidconnect/tokenservice", "POST");
        $this->headers["Authorization"] = "Basic " . $environment->authorizationString();
        $this->headers["Content-Type"] = "application/x-www-form-urlencoded";
        $this->body = [
            "grant_type" => "authorization_code",
            "code" => $authorizationCode
        ];
    }
}
