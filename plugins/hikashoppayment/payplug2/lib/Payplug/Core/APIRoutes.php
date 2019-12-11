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
namespace Payplug\Core;

class APIRoutes
{
    public static $API_BASE_URL;

    const API_VERSION = 1;

    const PAYMENT_RESOURCE           = '/payments';
    const REFUND_RESOURCE            = '/payments/{PAYMENT_ID}/refunds';
    const KEY_RESOURCE               = '/keys';
    const ACCOUNT_RESOURCE           = '/account';
    const CARD_RESOURCE              = '/cards';
    const INSTALLMENT_PLAN_RESOURCE  = '/installment_plans';


    public static function getRoute($route, $resourceId = null, array $parameters = array(), array $pagination = array())
    {
        foreach ($parameters as $parameter => $value) {
            $route = str_replace('{' . $parameter . '}', $value, $route);
        }

        $resourceIdUrl = $resourceId ? '/' . $resourceId : '';

        $query_pagination = '';
        if (!empty($pagination))
            $query_pagination = '?' . http_build_query($pagination);

        return self::$API_BASE_URL . '/v' . self::API_VERSION . $route . $resourceIdUrl . $query_pagination;
    }

    public static function getTestRoute()
    {
        return APIRoutes::$API_BASE_URL . '/test';
    }
}

APIRoutes::$API_BASE_URL = 'https://api.payplug.com';
