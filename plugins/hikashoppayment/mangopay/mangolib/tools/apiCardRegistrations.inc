<?php
namespace MangoPay;

/**
 * Class to management MangoPay API for card registrations
 */
class ApiCardRegistrations extends ApiBase {

    /**
     * Create new card registration
     * @param \MangoPay\CardRegistration $cardRegistration Card registration object to create
     * @return \MangoPay\CardRegistration Card registration object returned from API
     */
    public function Create($cardRegistration) {
        return $this->CreateObject('cardregistration_create', $cardRegistration, '\MangoPay\CardRegistration');
    }
    
    /**
     * Get card registration
     * @param int $cardRegistrationId Card Registration identifier
     * @return \MangoPay\CardRegistration Card registration  object returned from API
     */
    public function Get($cardRegistrationId) {
        return $this->GetObject('cardregistration_get', $cardRegistrationId, '\MangoPay\CardRegistration');
    }
    
    /**
     * Update card registration
     * @param \MangoPay\CardRegistration $cardRegistration Card registration object to save
     * @return \MangoPay\CardRegistration Card registration object returned from API
     */
    public function Update($cardRegistration) {
        return $this->SaveObject('cardregistration_save', $cardRegistration, '\MangoPay\CardRegistration');
    }
}