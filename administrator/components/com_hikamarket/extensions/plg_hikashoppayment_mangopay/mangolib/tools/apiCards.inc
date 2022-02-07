<?php
namespace MangoPay;

/**
 * Class to management MangoPay API for cards
 */
class ApiCards extends ApiBase {
    
    /**
     * Get card
     * @param int $cardId Card identifier
     * @return \MangoPay\Card object returned from API
     */
    public function Get($cardId) {
        return $this->GetObject('card_get', $cardId, '\MangoPay\Card');
    }
    
    /**
     * Update card
     * @param \MangoPay\Card $card Card object to save
     * @return \MangoPay\Card Card object returned from API
     */
    public function Update($card) {
        return $this->SaveObject('card_save', $card, '\MangoPay\Card');
    }
    
    /**
     * WARNING!!
     * It's temporary entity and it will be removed in the future.
     * Please, contact with support before using these features or if you have any questions.
     * 
     * Create new temporary payment card
     * @param \MangoPay\TemporaryPaymentCard $paymentCard Payment card object to create
     * @return \MangoPay\TemporaryPaymentCard Card registration object returned from API
     */
    public function CreateTemporaryPaymentCard($paymentCard) {
        return $this->CreateObject('temp_paymentcards_create', $paymentCard, '\MangoPay\TemporaryPaymentCard');
    }
    
    /**
     * WARNING!!
     * It's temporary entity and it will be removed in the future.
     * Please, contact with support before using these features or if you have any questions.
     * 
     * Get temporary payment card
     * @param string $paymentCardId Card identifier
     * @return \MangoPay\TemporaryPaymentCard object returned from API
     */
    public function GetTemporaryPaymentCard($paymentCardId) {
        return $this->GetObject('temp_paymentcards_get', $paymentCardId, '\MangoPay\TemporaryPaymentCard');
    }
}