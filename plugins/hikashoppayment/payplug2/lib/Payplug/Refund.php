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

class Refund {
    public static function create($payment, array $data = null, Payplug $payplug = null)
    {
    	return Resource\Refund::create($payment, $data, $payplug);
    }

    public static function retrieve($payment, $refundId, Payplug $payplug = null)
    {
    	return Resource\Refund::retrieve($payment, $refundId, $payplug);
    }

    public static function listRefunds($payment, Payplug $payplug = null)
    {
    	return Resource\Refund::listRefunds($payment, $payplug);
    }
}
