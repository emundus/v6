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


namespace PayPalCheckoutSdk\Payments;

use PayPalHttp\HttpRequest;

class AuthorizationsVoidRequest extends HttpRequest
{
    function __construct($authorizationId)
    {
        parent::__construct("/v2/payments/authorizations/{authorization_id}/void?", "POST");

        $this->path = str_replace("{authorization_id}", urlencode($authorizationId), $this->path);
        $this->headers["Content-Type"] = "application/json";
    }


}
