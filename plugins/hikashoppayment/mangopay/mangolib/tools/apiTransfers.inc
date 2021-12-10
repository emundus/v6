<?php
namespace MangoPay;

/**
 * Class to management MangoPay API for transfers
 */
class ApiTransfers extends ApiBase {
    
    /**
     * Create new transfer
     * @param \MangoPay\Transfer $transfer
     * @return \MangoPay\Transfer Transfer object returned from API
     */
    public function Create($transfer) {
        return $this->CreateObject('transfers_create', $transfer, '\MangoPay\Transfer');
    }
    
    /**
     * Get transfer
     * @param type $transferId Transfer identifier
     * @return \MangoPay\Transfer Transfer object returned from API
     */
    public function Get($transfer) {
        return $this->GetObject('transfers_get', $transfer, '\MangoPay\Transfer');
    }
    
    /**
     * Create refund for transfer object
     * @param type $transferId Transfer identifier
     * @param \MangoPay\Refund $refund Refund object to create
     * @return \MangoPay\Refund Object returned by REST API
     */
    public function CreateRefund($transferId, $refund) {
        return $this->CreateObject('transfers_createrefunds', $refund, '\MangoPay\Refund', $transferId);
    }
    
    /**
     * Get refund for transfer object
     * @param type $transferId Transfer identifier
     * @return \MangoPay\Refund Object returned by REST API
     */
    public function GetRefund($transferId) {
        return $this->GetObject('transfers_getrefunds', $transferId, '\MangoPay\Refund');
    }
}
