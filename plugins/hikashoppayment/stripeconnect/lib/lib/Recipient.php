<?php

namespace Stripe;

class Recipient extends ApiResource
{

    const OBJECT_NAME = "recipient";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Delete;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;

    public function transfers($params = null)
    {
        $params = $params ?: [];
        $params['recipient'] = $this->id;
        $transfers = Transfer::all($params, $this->_opts);
        return $transfers;
    }
}
