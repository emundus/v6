<?php

namespace Stripe;

class AlipayAccount extends ApiResource
{

    const OBJECT_NAME = "alipay_account";

    use ApiOperations\Delete;
    use ApiOperations\Update;

    public function instanceUrl()
    {
        if ($this['customer']) {
            $base = Customer::classUrl();
            $parent = $this['customer'];
            $path = 'sources';
        } else {
            $msg = "Alipay accounts cannot be accessed without a customer ID.";
            throw new Error\InvalidRequest($msg, null);
        }
        $parentExtn = urlencode(Util\Util::utf8($parent));
        $extn = urlencode(Util\Util::utf8($this['id']));
        return "$base/$parentExtn/$path/$extn";
    }

    public static function retrieve($_id, $_opts = null)
    {
        $msg = "Alipay accounts cannot be accessed without a customer ID. " .
               "Retrieve an Alipay account using \$customer->sources->retrieve('alipay_account_id') instead.";
        throw new Error\InvalidRequest($msg, null);
    }

    public static function update($_id, $_params = null, $_options = null)
    {
        $msg = "Alipay accounts cannot be accessed without a customer ID. " .
               "Call save() on \$customer->sources->retrieve('alipay_account_id') instead.";
        throw new Error\InvalidRequest($msg, null);
    }
}
