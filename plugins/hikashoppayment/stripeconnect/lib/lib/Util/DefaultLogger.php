<?php

namespace Stripe\Util;

class DefaultLogger implements LoggerInterface
{
    public function error($message, array $context = [])
    {
        if (count($context) > 0) {
            throw new \Exception('DefaultLogger does not currently implement context. Please implement if you need it.');
        }
        error_log($message);
    }
}
