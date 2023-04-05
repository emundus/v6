<?php
/*
* PHP Subnet Calculator v1.3.
* Copyright 01/11/2012 Randy McAnally
* Released under GNU GPL.
* Special thanks to krischan at jodies.cx for ipcalc.pl http://jodies.de/ipcalc
* The presentation and concept was mostly taken from ipcalc.pl.
* Modified by Jose A. Luque for the component Securitycheck Pro
*/

defined('_JEXEC') or die('Restricted access');

// Load library
require_once JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'loader.php';

class SecuritycheckProsModelIP extends SecuritycheckproModel
{

    function __construct()
    {
        parent::__construct();

    }
    
    /* Extract info of an ip and cidr */
    public function get_ip_info($ip, $cidr = null)
    {
        $maxSubNets = '2048'; // Stop memory leak from invalid input or large ranges
        $superNet = $ip;
        $superNetMask = ''; // optional
        $subNetCdr = $cidr;
        $subNetMask = ''; // optional
        
        // Array which contain all the info needed
        $ip_info = array (
        "network"    => '',
        "netmask"    => '',
        "wildcard"    => '',
        "broadcast"    => '',
        "hostmax"    => '',
        "hostmin"    => ''
        );
        
        /* Are we providing a cidr? */
        $existe_barra = strstr($superNet, "/");
                
        // Calculate supernet mask and cdr
        if ($existe_barra != false) {  //if cidr type mask
            $charHost = inet_pton(strtok($superNet, '/'));
            $charMask = $this->_cdr2Char(strtok('/'), strlen($charHost));
        } else
        {
            $charHost = inet_pton($superNet);
            $charMask = inet_pton($superNetMask);
        }
        
        // Single host mask used for hostmin and hostmax bitwise operations
        $charHostMask = substr($this->_cdr2Char(127), -strlen($charHost));
        $charWC = ~$charMask; // Supernet wildcard mask
        $charNet = $charHost & $charMask; // Supernet network address
        $charBcst = $charNet | ~$charMask; // Supernet broadcast
        $charHostMin = $charNet | ~$charHostMask; // Minimum host
        $charHostMax = $charBcst & $charHostMask; // Maximum host
        
        // Store the info
        //$ip_info["network"] = inet_ntop($charNet)."/".$this->_char2Cdr($charMask);
        $ip_info["network"] = inet_ntop($charNet);
        $ip_info["netmask"] = inet_ntop($charMask)." = /".$this->_char2Cdr($charMask);
        $ip_info["wildcard"] = inet_ntop($charWC);
        $ip_info["broadcast"] = inet_ntop($charBcst);
        $ip_info["hostmin"] = inet_ntop($charHostMin);
        $ip_info["hostmax"] = inet_ntop($charHostMax);
        
        return $ip_info;    
        
    }
    
    // Check if an IPv4 address is in subnet
    public function cidr_match($ip,$network,$cidr)
    {
        if ((ip2long($ip) & ~((1 << (32 - $cidr)) - 1) ) == ip2long($network)) {
            return true;
        }

        return false;
    }
    
    // Check if an IPv4 address is in subnet
    function ip_in_range( $ip, $range )
    {
        if (strpos($range, '/') == false) {
            $range .= '/32';
        }
        // $range is in IP/CIDR format eg 127.0.0.1/24
        list( $range, $netmask ) = explode('/', $range, 2);
        $range_decimal = ip2long($range);
        $ip_decimal = ip2long($ip);
        $wildcard_decimal = pow(2, ( 32 - $netmask )) - 1;
        $netmask_decimal = ~ $wildcard_decimal;
        return ( ( $ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) );
    }    
    
    public function checkIPv6WithinRange($ipv6, $range)
    {
        list ($net, $mask) = preg_split("/\//", $range);

        if ($mask % 4) {
            return false; //"Only masks divisible by 4 are supported"
        }
            
        $stripChars = (128-$mask)/4;

        $hexNet = bin2hex(inet_pton($net));
        $reducedNet = substr($hexNet, 0, 0 - $stripChars);

        $hexIp = bin2hex(inet_pton($ipv6));
        $reducedIp = substr($hexIp, 0, 0 - $stripChars);

        return $reducedIp === $reducedNet;
    }

    // Convert array of short unsigned integers to binary
    function _packBytes($array)
    {
        foreach ( $array as $byte )
        {
            $chars .= pack('C', $byte);
        }
        return $chars;
    }
    
    // Convert binary to array of short integers
    function _unpackBytes($string)
    {
        return unpack('C*', $string);
    }
    
    // Add array of short unsigned integers
    function _addBytes($array1,$array2)
    {
        $result = array();
        $carry = 0;
        foreach ( array_reverse($array1, true) as $value1 ) {
            $value2 = array_pop($array2);
            if (empty($result) ) { $value2++; 
            }
            $newValue = $value1 + $value2 + $carry;
            if ($newValue > 255) {
                 $newValue = $newValue - 256;
                 $carry = 1;
            } else {
                $carry = 0;
            }
            array_unshift($result, $newValue);
        }
        return $result;
    }
    
    /* Useful Functions */
    function _cdr2Bin($cdrin,$len=4)
    {
        if ($len > 4 || $cdrin > 32) { // Are we ipv6?
            return str_pad(str_pad("", $cdrin, "1"), 128, "0");
        } else
        {
            return str_pad(str_pad("", $cdrin, "1"), 32, "0");
        }
    }
    
    function _bin2Cdr($binin)
    {
        return strlen(rtrim($binin, "0"));
    }
    
    function _cdr2Char($cdrin,$len=4)
    {
        $hex = $this->_bin2Hex($this->_cdr2Bin($cdrin, $len));
        return $this->_hex2Char($hex);
    }
    
    function _char2Cdr($char)
    {
        $bin = $this->_hex2Bin($this->_char2Hex($char));
        return $this->_bin2Cdr($bin);
    }
    
    function _hex2Char($hex)
    {
        return pack('H*', $hex);
    }
    
    function _char2Hex($char)
    {
        $hex = unpack('H*', $char);
        return array_pop($hex);
    }
    
    function _hex2Bin($hex)
    {
        $bin='';
        for($i=0;$i<strlen($hex);$i++) {
            $bin.=str_pad(decbin(hexdec($hex[$i])), 4, '0', STR_PAD_LEFT);
        }
        return $bin;
    }
    
    function _bin2Hex($bin)
    {
        $hex='';
        for($i=strlen($bin)-4;$i>=0;$i-=4) {
            $hex.=dechex(bindec(substr($bin, $i, 4)));
        }
        return strrev($hex);
    }
    
}
