<?php

namespace Stripe;

class BankAccount extends ApiResource
{

    const OBJECT_NAME = "bank_account";

    use ApiOperations\Delete;
    use ApiOperations\Update;

    public function instanceUrl()
    {
        if ($this['customer']) {
            $base = Customer::classUrl();
            $parent = $this['customer'];
            $path = 'sources';
        } elseif ($this['account']) {
            $base = Account::classUrl();
            $parent = $this['account'];
            $path = 'external_accounts';
        } else {
            $msg = "Bank accounts cannot be accessed without a customer ID or account ID.";
            throw new Error\InvalidRequest($msg, null);
        }
        $parentExtn = urlencode(Util\Util::utf8($parent));
        $extn = urlencode(Util\Util::utf8($this['id']));
        return "$base/$parentExtn/$path/$extn";
    }

    public static function retrieve($_id, $_opts = null)
    {
        $msg = "Bank accounts cannot be accessed without a customer ID or account ID. " .
               "Retrieve a bank account using \$customer->sources->retrieve('bank_account_id') or " .
               "\$account->external_accounts->retrieve('bank_account_id') instead.";
        throw new Error\InvalidRequest($msg, null);
    }

    public static function update($_id, $_params = null, $_options = null)
    {
        $msg = "Bank accounts cannot be accessed without a customer ID or account ID. " .
               "Call save() on \$customer->sources->retrieve('bank_account_id') or " .
               "\$account->external_accounts->retrieve('bank_account_id') instead.";
        throw new Error\InvalidRequest($msg, null);
    }

    public function verify($params = null, $options = null)
    {
        $url = $this->instanceUrl() . '/verify';
        list($response, $opts) = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);
        return $this;
    }
}
