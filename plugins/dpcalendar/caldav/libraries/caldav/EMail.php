<?php
/**
* Lightweight class for sending an e-mail.
* @package awl
* @subpackage   EMail
* @author    Andrew McMillan <andrew@mcmillan.net.nz>
* @copyright Catalyst IT Ltd
* @license   http://gnu.org/copyleft/gpl.html GNU GPL v2 or later
*/

require_once("AWLUtilities.php");
/**
* Lightweight class for sending an e-mail.
* @package awl
*/
class EMail
{
  /**#@+
  * @access private
  */

  /**
  * A comma-separated list of addresses to send To
  * @var string
  */
  private $To;         // To:

  /**
  * The visible sender of the e-mail.
  * @var string
  */
  private $From;       // etc...

  /**
  * A comma-separated list of addresses to carbon-copy to
  * @var string
  */
  private $Cc;

  /**
  * A comma-separated list of addresses to blind carbon-copy to
  * @var string
  */
  private $Bcc;

  /**
  * A comma-separated list of addresses to set as the Errors-to: header
  * @var string
  */
  private $ErrorsTo;

  /**
  * A comma-separated list of addresses to set as the Reply-to: header
  * @var string
  */
  private $ReplyTo;

  /**
  * The address to set as the sender of the e-mail.
  * @var string
  */
  private $Sender;

  /**
  * The subject line of the email.
  * @var string
  */
  private $Subject;

  /**
  * The body of the email.
  * @var string
  */
  private $Body;
  /**#@-*/

  /**
  * Create the e-mail, optionally assigning the subject and primary recipient.
  * @param string $subject The subject line of the email.
  * @param string $to A comma-separated list of addresses for the primary recipient(s).
  */
  function __construct( $subject = "", $to = "" ) {
    // Initialise with some defaults
    $this->From    = "";
    $this->Subject = $subject;
    $this->To      = $to;
    $this->Cc      = "";
    $this->Bcc     = "";
    $this->ErrorsTo = "";
    $this->ReplyTo = "";
    $this->Sender  = "";
    $this->Body    = "";
  }

  /**
  * Append something with a comma delimter onto the existing referenced string
  * @param stringref &$onto The string we will be appending to.
  * @param string $extra What we will be appending
  * @return string The new string.
  */
  private function _AppendDelimited( &$onto, $extra ) {
    if ( !isset($extra) || $extra == "" ) return false;
    if ( $onto != "" ) $onto .= ", ";
    $onto .= $extra;
    return $onto;
  }

  /**
  * Add another recipient to the email
  * @param string $recipient The email address to append.
  * @return string The new recipient list.
  */
  function AddTo( $recipient ) {
    return $this->_AppendDelimited($this->To, $recipient);
  }

  /**
  * Get the current recipient list.
  * @return string The current recipient list.
  */
  function To() {
    return $this->To;
  }

  /**
  * Add another Cc recipient to the email
  * @param string $recipient The email address to append.
  * @return string The new Cc recipient list.
  */
  function AddCc( $recipient ) {
    return $this->_AppendDelimited($this->Cc, $recipient);
  }

  /**
  * Add another Bcc recipient to the email
  * @param string $recipient The email address to append.
  * @return string The new Bcc recipient list.
  */
  function AddBcc( $recipient ) {
    return $this->_AppendDelimited($this->Bcc, $recipient);
  }

  /**
  * Add another Reply-to address to the email
  * @param string $recipient The email address to append.
  * @return string The new Reply-to list.
  */
  function AddReplyTo( $recipient ) {
    return $this->_AppendDelimited($this->ReplyTo, $recipient);
  }

  /**
  * Add another Error recipient to the email
  * @param string $recipient The email address to append.
  * @return string The new Error recipient list.
  */
  function AddErrorsTo( $recipient ) {
    return $this->_AppendDelimited($this->ErrorsTo, $recipient);
  }


  /**
  * Set the visible From address for the e-mail.
  * @param string $recipient The visible From address
  * @return string The new From address
  */
  function SetFrom( $sender ) {
    $this->From = $sender;
    return $sender;
  }


  /**
  * Set the envelope sender address for the e-mail.
  * @param string $recipient The e-mail address for the sender
  * @return string The new envelope sender address.
  */
  function SetSender( $sender ) {
    $this->Sender = $sender;
    return $sender;
  }


  /**
  * Set the subject line for the email
  * @param string $recipient The new subject line.
  * @return string The new subject line.
  */
  function SetSubject( $subject ) {
    $this->Subject = $subject;
    return $subject;
  }


  /**
  * Set the body of the e-mail.
  * @param string $recipient The email address to append.
  * @return string The new body of the e-mail.
  */
  function SetBody( $body ) {
    $this->Body = $body;
    return $body;
  }


  /**
  * Actually send the email
  * @param string $additional_headers Any additional headers that are needed.
  */
  function Send( $additional_headers = "" ) {
    if ( !empty($this->From) )     $additional_headers .= "From: $this->From\r\n";
    if ( !empty($this->Cc) )       $additional_headers .= "Cc: $this->Cc\r\n";
    if ( !empty($this->Bcc) )      $additional_headers .= "Bcc: $this->Bcc\r\n";
    if ( !empty($this->ReplyTo) )  $additional_headers .= "Reply-To: $this->ReplyTo\r\n";
    if ( !empty($this->ErrorsTo) ) $additional_headers .= "Errors-To: $this->ErrorsTo\r\n";

    $additional_parameters = "";
    if ( !empty($this->Sender) ) $additional_parameters = "-f$this->Sender";
    mail( $this->To, $this->Subject, $this->Body, $additional_headers, $additional_parameters );
  }


  /**
  * Don't actually send the email, just log it.
  * @param string $additional_headers Any additional headers that are needed.
  */
  function PretendLog( $additional_headers = "" ) {
    if ( !empty($this->From) )     dbg_error_log('LOG', "From: $this->From");
    if ( !empty($this->Cc) )       dbg_error_log('LOG', "Cc: $this->Cc");
    if ( !empty($this->Bcc) )      dbg_error_log('LOG', "Bcc: $this->Bcc");
    if ( !empty($this->ReplyTo) )  dbg_error_log('LOG', "Reply-To: $this->ReplyTo");
    if ( !empty($this->ErrorsTo) ) dbg_error_log('LOG', "Errors-To: $this->ErrorsTo");

    $additional_parameters = "";
    if ( !empty($this->Sender) ) dbg_error_log('LOG', "Envelope Sender set to: $this->Sender");
    dbg_error_log('LOG', "To: $this->To");
    dbg_error_log('LOG', "Subject: $this->Subject");
    dbg_error_log('LOG', "Body: $this->Body");
  }

  /**
  * Don't actually send the email, just output it directly in the stream(!).  We use this method
  * when we're doing regression testing.
  * @param string $additional_headers Any additional headers that are needed.
  */
  function Pretend( $additional_headers = "" ) {
    if ( !empty($this->From) )     print("From: $this->From\r\n");
    if ( !empty($this->Cc) )       print("Cc: $this->Cc\r\n");
    if ( !empty($this->Bcc) )      print("Bcc: $this->Bcc\r\n");
    if ( !empty($this->ReplyTo) )  print("Reply-To: $this->ReplyTo\r\n");
    if ( !empty($this->ErrorsTo) ) print("Errors-To: $this->ErrorsTo\r\n");

    $additional_parameters = "";
    if ( !empty($this->Sender) ) print("Envelope Sender set to: $this->Sender\r\n");
    print("To: $this->To\r\n");
    print("Subject: $this->Subject\r\n");
    print("Body: $this->Body\r\n");
  }
}
