<?php

namespace Stripe;

class Refund extends ApiResource
{

    const OBJECT_NAME = "refund";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;

    const FAILURE_REASON                     = 'expired_or_canceled_card';
    const FAILURE_REASON_LOST_OR_STOLEN_CARD = 'lost_or_stolen_card';
    const FAILURE_REASON_UNKNOWN             = 'unknown';

    const REASON_DUPLICATE             = 'duplicate';
    const REASON_FRAUDULENT            = 'fraudulent';
    const REASON_REQUESTED_BY_CUSTOMER = 'requested_by_customer';

    const STATUS_CANCELED  = 'canceled';
    const STATUS_FAILED    = 'failed';
    const STATUS_PENDING   = 'pending';
    const STATUS_SUCCEEDED = 'succeeded';
}
