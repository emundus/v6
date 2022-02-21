<?php

namespace Stripe;

class CreditNote extends ApiResource
{

    const OBJECT_NAME = "credit_note";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;

    const REASON_DUPLICATE              = 'duplicate';
    const REASON_FRAUDULENT             = 'fraudulent';
    const REASON_ORDER_CHANGE           = 'order_change';
    const REASON_PRODUCT_UNSATISFACTORY = 'product_unsatisfactory';

    const STATUS_ISSUED = 'issued';
    const STATUS_VOID   = 'void';

    const TYPE_POST_PAYMENT = 'post_payment';
    const TYPE_PRE_PAYMENT  = 'pre_payment';

    public function voidCreditNote($params = null, $opts = null)
    {
        $url = $this->instanceUrl() . '/void';
        list($response, $opts) = $this->_request('post', $url, $params, $opts);
        $this->refreshFrom($response, $opts);
        return $this;
    }
}
