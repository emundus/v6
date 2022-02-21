<?php
namespace MangoPay;

/**
 * Class to management MangoPay API for pay-ins
 */
class ApiPayIns extends ApiBase {
    
    /**
     * Create new pay-in object
     * @param \MangoPay\PayIn $payIn \MangoPay\PayIn object
     * @return \MangoPay\PayIn Object returned from API
     */
    public function Create($payIn) {
        $paymentKey = $this->GetPaymentKey($payIn);
        $executionKey = $this->GetExecutionKey($payIn);
        return $this->CreateObject('payins_' . $paymentKey . '-' . $executionKey . '_create', $payIn, '\MangoPay\PayIn');
    }
    
    /**
     * Get pay-in object
     * @param $payInId Pay-in identifier
     * @return \MangoPay\PayIn Object returned from API
     */
    public function Get($payInId) {
        return $this->GetObject('payins_get', $payInId, '\MangoPay\PayIn');
    }
    
    /**
     * Create refund for pay-in object
     * @param type $payInId Pay-in identifier
     * @param \MangoPay\Refund $refund Refund object to create
     * @return \MangoPay\Refund Object returned by REST API
     */
    public function CreateRefund($payInId, $refund) {
        return $this->CreateObject('payins_createrefunds', $refund, '\MangoPay\Refund', $payInId);
    }

    /**
     * Get refund for pay-in object
     * @param type $payInId Pay-in identifier
     * @return \MangoPay\Refund Object returned by REST API
     */
    public function GetRefund($payInId) {
        return $this->GetObject('payins_getrefunds', $payInId, '\MangoPay\Refund');
    }
    
    /**
     * WARNING!!
     * It's temporary entity and it will be removed in the future.
     * Please, contact with support before using these features or if you have any questions.
     * 
     * Create new temporary immediate pay-in
     * @param \MangoPay\TemporaryImmediatePayIn $immediatePayIn Immediate pay-in object to create
     * @return \MangoPay\TemporaryImmediatePayIn Immediate pay-in object returned from API
     */
    public function CreateTemporaryImmediatePayIn($immediatePayIn) {
        return $this->CreateObject('temp_immediatepayins_create', $immediatePayIn, '\MangoPay\TemporaryImmediatePayIn');
    }
    
    private function GetPaymentKey($payIn) {
        
        if (!isset($payIn->PaymentDetails) || !is_object($payIn->PaymentDetails))
            throw new Exception ('Payment is not defined or it is not object type');
        
        $className = str_replace('MangoPay\\PayInPaymentDetails', '', get_class($payIn->PaymentDetails));
        return strtolower($className);
    }
    
    private function GetExecutionKey($payIn) {
        
        if (!isset($payIn->ExecutionDetails) || !is_object($payIn->ExecutionDetails))
            throw new Exception ('Execution is not defined or it is not object type');
        
        $className = str_replace('MangoPay\\PayInExecutionDetails', '', get_class($payIn->ExecutionDetails));
        return strtolower($className);
    }
}