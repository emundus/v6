<?php
namespace MangoPay;

/**
* Authorization token manager
*/
class AuthorizationTokenManager extends ApiBase {

    /**
     * Storage object
     * @var \MangoPay\IStorageStrategy
     */
    private $_storageStrategy;
    
    function __construct($root) {
        $this->_root = $root;

        $this->RegisterCustomStorageStrategy(new DefaultStorageStrategy($this->_root->Config));
    }
    
    /**
     * Gets the current authorization token.
     * In the very first call, this method creates a new token before returning.
     * If currently stored token is expired, this method creates a new one.
     * @return \MangoPay\OAuthToken Valid OAuthToken instance.
     */
    public function GetToken() {
        $token = $this->_storageStrategy->get();
        
        if (is_null($token) || $token->IsExpired()) {
            $this->storeToken($this->_root->AuthenticationManager->createToken());
        }
    
        return $this->_storageStrategy->get();
    }
    
    /**
     * Stores authorization token passed as an argument in the underlying
     * storage strategy implementation.
     * @param \MangoPay\OAuthToken $token Token instance to be stored.
     */
    public function StoreToken($token) {
        $this->_storageStrategy->Store($token);
    }
    
    /**
     * Registers custom storage strategy implementation.
     * By default, the DefaultStorageStrategy instance is used. 
     * There is no need to explicitly call this method until some more complex 
     * storage implementation is needed.
     * @param \MangoPay\IStorageStrategy $customStorageStrategy IStorageStrategy interface implementation.
     */
    public function RegisterCustomStorageStrategy($customStorageStrategy) {
        $this->_storageStrategy = $customStorageStrategy;
    }
}