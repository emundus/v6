<?php

namespace Stripe;

class ApplicationFeeRefund extends ApiResource
{

    const OBJECT_NAME = "fee_refund";

    use ApiOperations\Update {
        save as protected _save;
    }

    public function instanceUrl()
    {
        $id = $this['id'];
        $fee = $this['fee'];
        if (!$id) {
            throw new Error\InvalidRequest(
                "Could not determine which URL to request: " .
                "class instance has invalid ID: $id",
                null
            );
        }
        $id = Util\Util::utf8($id);
        $fee = Util\Util::utf8($fee);

        $base = ApplicationFee::classUrl();
        $feeExtn = urlencode($fee);
        $extn = urlencode($id);
        return "$base/$feeExtn/refunds/$extn";
    }

    public function save($opts = null)
    {
        return $this->_save($opts);
    }
}
