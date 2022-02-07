<?php
namespace MangoPay;

/**
 * Class to management MangoPay API for refunds
 */
class ApiRefunds extends ApiBase {
   
    /**
     * Get refund object
     * @param string $refundId Refund Id
     * @return \MangoPay\Refund Refund object returned from API
     */
    public function Get($refundId) {
        return $this->GetObject('refunds_get', $refundId, '\MangoPay\Refund');
    }
}