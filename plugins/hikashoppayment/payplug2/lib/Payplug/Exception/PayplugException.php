<?php
namespace Payplug\Exception;

abstract class PayplugException extends \Exception
{
    public function __toString()
    {
        return get_class($this) . ": [{$this->code}]: {$this->message}";
    }
}
