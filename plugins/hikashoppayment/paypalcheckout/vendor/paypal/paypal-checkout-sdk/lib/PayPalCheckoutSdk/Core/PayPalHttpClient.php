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

use PayPalHttp\HttpClient;

class PayPalHttpClient extends HttpClient
{
    private $refreshToken;
    public $authInjector;

    public function __construct(PayPalEnvironment $environment, $refreshToken = NULL)
    {
        parent::__construct($environment);
        $this->refreshToken = $refreshToken;
        $this->authInjector = new AuthorizationInjector($this, $environment, $refreshToken);
        $this->addInjector($this->authInjector);
        $this->addInjector(new GzipInjector());
        $this->addInjector(new FPTIInstrumentationInjector());
    }

    public function userAgent()
    {
        return UserAgent::getValue();
    }
}

