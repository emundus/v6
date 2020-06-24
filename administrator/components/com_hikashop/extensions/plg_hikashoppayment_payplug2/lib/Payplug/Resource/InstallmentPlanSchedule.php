<?php
namespace Payplug\Resource;

class InstallmentPlanSchedule extends APIResource
{
    public static function fromAttributes(array $attributes)
    {
        $object = new InstallmentPlanSchedule();
        $object->initialize($attributes);
        return $object;
    }
}
