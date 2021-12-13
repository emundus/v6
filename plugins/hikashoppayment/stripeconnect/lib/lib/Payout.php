<?php

namespace Stripe;

class Payout extends ApiResource
{

    const OBJECT_NAME = "payout";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;

    const FAILURE_ACCOUNT_CLOSED                = 'account_closed';
    const FAILURE_ACCOUNT_FROZEN                = 'account_frozen';
    const FAILURE_BANK_ACCOUNT_RESTRICTED       = 'bank_account_restricted';
    const FAILURE_BANK_OWNERSHIP_CHANGED        = 'bank_ownership_changed';
    const FAILURE_COULD_NOT_PROCESS             = 'could_not_process';
    const FAILURE_DEBIT_NOT_AUTHORIZED          = 'debit_not_authorized';
    const FAILURE_DECLINED                      = 'declined';
    const FAILURE_INCORRECT_ACCOUNT_HOLDER_NAME = 'incorrect_account_holder_name';
    const FAILURE_INSUFFICIENT_FUNDS            = 'insufficient_funds';
    const FAILURE_INVALID_ACCOUNT_NUMBER        = 'invalid_account_number';
    const FAILURE_INVALID_CURRENCY              = 'invalid_currency';
    const FAILURE_NO_ACCOUNT                    = 'no_account';
    const FAILURE_UNSUPPORTED_CARD              = 'unsupported_card';

    const METHOD_STANDARD = 'standard';
    const METHOD_INSTANT  = 'instant';

    const STATUS_CANCELED   = 'canceled';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_FAILED     = 'failed';
    const STATUS_PAID       = 'paid';
    const STATUS_PENDING    = 'pending';

    const TYPE_BANK_ACCOUNT = 'bank_account';
    const TYPE_CARD         = 'card';

    public function cancel()
    {
        $url = $this->instanceUrl() . '/cancel';
        list($response, $opts) = $this->_request('post', $url);
        $this->refreshFrom($response, $opts);
        return $this;
    }
}
