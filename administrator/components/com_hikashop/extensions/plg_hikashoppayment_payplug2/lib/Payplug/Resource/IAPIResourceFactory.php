<?php
namespace Payplug\Resource;

interface IAPIResourceFactory
{
    static function fromAttributes(array $attributes);
}
