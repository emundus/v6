<?php

namespace Stripe\Util;

interface LoggerInterface
{
    public function error($message, array $context = []);
}
