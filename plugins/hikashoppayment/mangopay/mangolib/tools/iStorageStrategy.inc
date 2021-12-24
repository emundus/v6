<?php
namespace MangoPay;

/**
 * Storage strategy interface.
 */
interface IStorageStrategy {
    
    /**
     * Gets the current authorization token.
     * @return \MangoPay\OAuthToken Currently stored token instance or null.
     */
    function Get();
    
    /**
     * Stores authorization token passed as an argument.
     * @param \MangoPay\OAuthToken $token Token instance to be stored.
     */
    function Store($token);
}