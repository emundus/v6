<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.2.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2019 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
namespace Payplug;

class Authentication
{
    public static function getKeysByLogin($email, $password)
    {
        $httpClient = new Core\HttpClient(null);
        $response = $httpClient->post(
            Core\APIRoutes::getRoute(Core\APIRoutes::KEY_RESOURCE),
            array('email' => $email, 'password' => $password),
            false
        );
        return $response;
    }

    public static function getAccount(Payplug $payplug = null)
    {
        if ($payplug === null) {
            $payplug = Payplug::getDefaultConfiguration();
        }

        $httpClient = new Core\HttpClient($payplug);
        $response = $httpClient->get(Core\APIRoutes::getRoute(Core\APIRoutes::ACCOUNT_RESOURCE));

        return $response;
    }

    public static function getPermissions(Payplug $payplug = null)
    {
        if ($payplug === null) {
            $payplug = Payplug::getDefaultConfiguration();
        }

        $httpClient = new Core\HttpClient($payplug);
        $response = $httpClient->get(Core\APIRoutes::getRoute(Core\APIRoutes::ACCOUNT_RESOURCE));

        return $response['httpResponse']['permissions'];
    }

    public static function getPermissionsByLogin($email, $password)
    {
        $keys = self::getKeysByLogin($email, $password);
        $payplug = Payplug::setSecretKey($keys['httpResponse']['secret_keys']['live']);
        $httpClient = new Core\HttpClient($payplug);
        $response = $httpClient->get(Core\APIRoutes::getRoute(Core\APIRoutes::ACCOUNT_RESOURCE));

        return $response['httpResponse']['permissions'];
    }
}
