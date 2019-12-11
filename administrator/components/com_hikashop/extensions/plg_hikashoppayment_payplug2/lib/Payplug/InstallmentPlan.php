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

class InstallmentPlan
{
    public static function retrieve($installmentPlanId, Payplug $payplug = null)
    {
        return Resource\InstallmentPlan::retrieve($installmentPlanId, $payplug);
    }

    public static function abort($installmentPlanId, Payplug $payplug = null)
    {
        $installmentPlan = Resource\InstallmentPlan::fromAttributes(array('id' => $installmentPlanId));
        return $installmentPlan->abort($payplug);
    }

    public static function create(array $data, Payplug $payplug = null)
    {
    	return Resource\InstallmentPlan::create($data, $payplug);
    }
};
