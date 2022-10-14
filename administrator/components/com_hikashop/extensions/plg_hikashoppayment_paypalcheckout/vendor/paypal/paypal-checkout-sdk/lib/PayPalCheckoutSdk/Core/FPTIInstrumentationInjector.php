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

use PayPalHttp\Injector;

class FPTIInstrumentationInjector implements Injector
{
    public function inject($request)
    {
        $request->headers["sdk_name"] = "Checkout SDK";
        $request->headers["sdk_version"] = "1.0.2";
        $request->headers["sdk_tech_stack"] = "PHP " . PHP_VERSION;
        $request->headers["api_integration_type"] = "PAYPALSDK";
    }
}
