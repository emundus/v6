<?php

namespace Stripe;

class InvoiceItem extends ApiResource
{

    const OBJECT_NAME = "invoiceitem";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Delete;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;
}
