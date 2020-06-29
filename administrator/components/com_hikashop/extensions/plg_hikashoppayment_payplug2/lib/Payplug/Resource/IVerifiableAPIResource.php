<?php
namespace Payplug\Resource;
use Payplug;

interface IVerifiableAPIResource
{
    function getConsistentResource(Payplug\Payplug $payplug = null);
}
