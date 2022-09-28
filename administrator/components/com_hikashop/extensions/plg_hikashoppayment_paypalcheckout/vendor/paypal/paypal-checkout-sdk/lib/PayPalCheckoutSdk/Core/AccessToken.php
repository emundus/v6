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


class AccessToken
{
    public $token;
    public $tokenType;
    public $expiresIn;
    private $createDate;

    public function __construct($token, $tokenType, $expiresIn)
    {
        $this->token = $token;
        $this->tokenType = $tokenType;
        $this->expiresIn = $expiresIn;
        $this->createDate = time();
    }

    public function isExpired()
    {
        return time() >= $this->createDate + $this->expiresIn;
    }
}
