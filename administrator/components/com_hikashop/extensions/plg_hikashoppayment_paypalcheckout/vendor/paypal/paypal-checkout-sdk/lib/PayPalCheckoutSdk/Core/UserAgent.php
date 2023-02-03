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

class UserAgent
{
    public static function getValue()
    {
        $featureList = array(
            'platform-ver=' . PHP_VERSION,
            'bit=' . self::_getPHPBit(),
            'os=' . str_replace(' ', '_', php_uname('s') . ' ' . php_uname('r')),
            'machine=' . php_uname('m')
        );
        if (defined('OPENSSL_VERSION_TEXT')) {
            $opensslVersion = explode(' ', OPENSSL_VERSION_TEXT);
            $featureList[] = 'crypto-lib-ver=' . $opensslVersion[1];
        }
        if (function_exists('curl_version')) {
            $curlVersion = curl_version();
            $featureList[] = 'curl=' . $curlVersion['version'];
        }
        return sprintf("PayPalSDK/%s %s (%s)", "Checkout-PHP-SDK", Version::VERSION, implode('; ', $featureList));
    }
    private static function _getPHPBit()
    {
        switch (PHP_INT_SIZE) {
            case 4:
                return '32';
            case 8:
                return '64';
            default:
                return PHP_INT_SIZE;
        }
    }
}
