<?php
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
