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


$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'Sample\\' => array($vendorDir . '/paypal/paypal-checkout-sdk/samples'),
    'PayPalHttp\\' => array($vendorDir . '/paypal/paypalhttp/lib/PayPalHttp'),
    'PayPalCheckoutSdk\\' => array($vendorDir . '/paypal/paypal-checkout-sdk/lib/PayPalCheckoutSdk'),
);