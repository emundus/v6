<?php
namespace MangoPay;

/**
 * WARNING!!
 * It's temporary entity and it will be removed in the future.
 * Please, contact with support before using these features or if you have any questions.
 * 
 * Temporary Payment Card entity.
 */
class TemporaryPaymentCard extends EntityBase {
    
    /**
     * User Id
     * @var string
     */
    public $UserId;
    
    /**
     * Culture
     * @var string 
     */
    public $Culture;
    
    /**
     * Return URL
     * @var string 
     */
    public $ReturnURL;
    
    /**
     * Template URL
     * @var string 
     */
    public $TemplateURL;
    
    /**
     * Redirect URL
     * @var string 
     */
    public $RedirectURL;
    
    /**
     * Alias
     * @var string 
     */
    public $Alias;
}