<?php

require_once('AWLUtilities.php');

class SinglePart {
  private $content;
  private $type;
  private $otherHeaders;
  private $disposition;
  private $id;

  public static $crlf = "\r\n";
  
  function __construct( $content, $type='text/plain', $otherHeaders=array() ) {
    $this->content = $content;
    $this->type = $type;
    $this->otherHeaders = $otherHeaders;
  }
  
  function render() {
    $result = 'Content-Type: '.$this->type.self::$crlf;
    $encoded = false;
    foreach( $this->otherHeaders AS $header => $value ) {
      $result .= $header.': '.$value.self::$crlf;
      if ( $header == 'Content-Transfer-Encoding' ) $encoded = true;
    }

    if ( $encoded )
       return $result . self::$crlf . $content;

    return $result . 'Content-Transfer-Encoding: base64' . self::$crlf
                    . self::$crlf
                    . base64_encode($content);
  }
}


class Multipart {

  private $parts; // Always good for a giggle :-)
  private $boundary;

  function __construct() {
    $this->parts = array();
    $this->boundary = uuid();
  }

  function addPart() {
    $args = func_get_args();
    if ( is_string($args[0]) ) {
      $newPart = new SinglePart( $args[0], (isset($args[1])?$args[1]:'text/plain'), (isset($args[2])?$args[2]:array())); 
    }
    else
      $newPart = $args[0];
        
    $this->parts[] = $newPart;
    
    return $newPart;
  }

  
  function getMimeHeaders() {
    return 'MIME-Version: 1.0' . SinglePart::$crlf
          .'Content-Type: multipart/mixed; boundary='.$this->boundary . SinglePart::$crlf ;
  }

  function getMimeParts() {
    $result = '--' . $this->boundary . SinglePart::$crlf;
    foreach( $this->parts AS $part ) {
      $result .= $part->render() . SinglePart::$crlf . '--' . $this->boundary;
    }
    $result .= '--' . SinglePart::$crlf;
    return $result;
  }

}