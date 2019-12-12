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

class Payment
{
    public static function retrieve($paymentId, Payplug $payplug = null)
    {
    	return Resource\Payment::retrieve($paymentId, $payplug);
    }

    public static function abort($paymentId, Payplug $payplug = null)
    {
        $payment = Resource\Payment::fromAttributes(array('id' => $paymentId));
    	return $payment->abort($payplug);
    }

    public static function capture($paymentId, Payplug $payplug = null)
    {
        $payment = Resource\Payment::fromAttributes(array('id' => $paymentId));
        return $payment->capture($payplug);
    }

    public static function create(array $data, Payplug $payplug = null)
    {
    	return Resource\Payment::create($data, $payplug);
    }

    public static function listPayments($perPage = null, $page = null, Payplug $payplug = null)
    {
    	return Resource\Payment::listPayments($perPage, $page, $payplug);
    }    
};
