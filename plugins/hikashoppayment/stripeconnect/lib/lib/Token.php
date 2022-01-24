<?php

namespace Stripe;

class Token extends ApiResource
{

    const OBJECT_NAME = "token";

    use ApiOperations\Create;
    use ApiOperations\Retrieve;

    const TYPE_ACCOUNT      = 'account';
    const TYPE_BANK_ACCOUNT = 'bank_account';
    const TYPE_CARD         = 'card';
    const TYPE_PII          = 'pii';
}
