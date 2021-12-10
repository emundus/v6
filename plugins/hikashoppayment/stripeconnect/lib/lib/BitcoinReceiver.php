<?php

namespace Stripe;

class BitcoinReceiver extends ApiResource
{

    const OBJECT_NAME = "bitcoin_receiver";

    use ApiOperations\All;
    use ApiOperations\Retrieve;

    public static function classUrl()
    {
        return "/v1/bitcoin/receivers";
    }

    public function instanceUrl()
    {
        if ($this['customer']) {
            $base = Customer::classUrl();
            $parent = $this['customer'];
            $path = 'sources';
            $parentExtn = urlencode(Util\Util::utf8($parent));
            $extn = urlencode(Util\Util::utf8($this['id']));
            return "$base/$parentExtn/$path/$extn";
        } else {
            $base = BitcoinReceiver::classUrl();
            $extn = urlencode(Util\Util::utf8($this['id']));
            return "$base/$extn";
        }
    }
}
