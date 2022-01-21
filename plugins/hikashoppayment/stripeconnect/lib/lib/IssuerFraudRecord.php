<?php

namespace Stripe;

class IssuerFraudRecord extends ApiResource
{

    const OBJECT_NAME = "issuer_fraud_record";

    use ApiOperations\All;
    use ApiOperations\Retrieve;
}
