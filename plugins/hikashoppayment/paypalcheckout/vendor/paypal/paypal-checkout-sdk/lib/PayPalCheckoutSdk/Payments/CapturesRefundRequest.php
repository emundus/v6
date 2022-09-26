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

class CapturesRefundRequest extends HttpRequest
{
    function __construct($captureId)
    {
        parent::__construct("/v2/payments/captures/{capture_id}/refund?", "POST");

        $this->path = str_replace("{capture_id}", urlencode($captureId), $this->path);
        $this->headers["Content-Type"] = "application/json";
    }


    public function payPalRequestId($payPalRequestId)
    {
        $this->headers["PayPal-Request-Id"] = $payPalRequestId;
    }
    public function prefer($prefer)
    {
        $this->headers["Prefer"] = $prefer;
    }
}
