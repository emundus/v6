<?php
namespace MangoPay;

/**
 * Class to manage debug logs in MangoPay SDK
 */
class Logs {
    
    public static function Debug($message, $data){
        
        print '<pre>';
        print $message . ': ';
        print_r($data);
        print '<br/>-------------------------------</pre>';
    }
}