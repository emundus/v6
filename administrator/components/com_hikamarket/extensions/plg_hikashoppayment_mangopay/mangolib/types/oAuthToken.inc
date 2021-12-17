<?php
namespace MangoPay;

/**
 * OAuthToken
 */
class OAuthToken extends Dto {
    
    /**
     * Created time
     * @var int
     */
    private $create_time;
    
    /**
     * Value of token
     * @var string
     */
    public $access_token;
    
    /**
     * Token type
     * @var string
     */
    public $token_type;
    
    /**
     * Expires time for the token
     * @var int
     */
    public $expires_in;
    
    /**
     * Constructor
     */
    function __construct() {
        $this->create_time = time() - 5;
    }
    
    /**
     * Check that current tokens are expire and return true if yes
     * @return bool
     */
    public function IsExpired() {
        return (time() >= ($this->create_time + $this->expires_in));
    }
}