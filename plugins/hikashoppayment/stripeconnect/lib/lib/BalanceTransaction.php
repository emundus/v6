<?php

namespace Stripe;

class BalanceTransaction extends ApiResource
{

    const OBJECT_NAME = "balance_transaction";

    use ApiOperations\All;
    use ApiOperations\Retrieve;

    public static function classUrl()
    {
        return "/v1/balance/history";
    }
}
