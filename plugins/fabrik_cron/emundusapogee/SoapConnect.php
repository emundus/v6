<?php
require_once("XmlSchema.php");  /// include XMLSchema class

/// credential type == NoAuth // API Key // Bearer Token // Basic Auth // Digest Auth // OAuth 1.0 // OAuth 2.0 // Haw Authentication // AWS Signature // NTLM Authentication // Akamai EdgeGrid

class SoapConnect {
    var $wsdl;
    var $credentials;
    var $xmlTree;

    public function __construct($_wsdl,$_credentials) {
        $this->wsdl = $_wsdl;
        $this->credentials = $_credentials;         /// base64 encode
        //$this->xmlTree = $_xmlTree;
    }


    /// $credentials = ZU11bmR1czo1cWNtbkI9M1E5ckxHaUF5
    public function setSoapHeader() {
        $headers = array(
            'type' => 'Content-Type: text/xml; charset="utf-8"',
            'content' => 'Content-Length: ' . strlen($this->xmlTree),
            'accept' => 'Accept: text/xml',
            'cache' => 'Cache-Control: no-cache',
            'pragma' => 'Pragma: no-cache',
            'auth' => 'Authorization: ',
        );

        switch($this->credentials->type) {
            case 0:
                print_r('no authentication');
                break;
            case 1:
                print_r('bearer token');
                break;
            case 2:
                $headers['auth']  =  $headers['auth'] . 'Basic ' . base64_encode($this->credentials->username.':'.$this->credentials->password);            /// basic auth = base64_encode(username:password)
                break;
            case 3:
                print_r('digest authentication');
                break;
            default:
                exit;
        }

        return array_values($headers);
    }

    public function webServiceConnect() {
        /// set a connection via CURL
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $this->wsdl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->setSoapHeader());

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->xmlTree);

        switch($this->credentials->type) {
            case 1:
                echo '<pre>'; var_dump(1); echo '</pre>'; die;
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, base64_encode($this->credentials->username.':'.$this->credentials->password));
                break;
            case 2:
                echo '<pre>'; var_dump(2); echo '</pre>'; die;
                break;
        }

        return $ch;     // return a CURL
    }

    public function sendRequest($curl_obj) { return curl_exec($curl_obj); }
}

//$xmlTree = '';
//
///// replace by $jinput->authentication
//$authentication = new StdClass;
//$authentication->username = '<username>';
//$authentication->password = '<password>';
//$authentication->type = 'Basic Auth';
//
///// replace by $jinput->web service description
//$wsdl = 'https://applications-test.u-paris2.fr/ApoWs/services/OpiMetier?wsdl';
//
//try {
//    $apogee_paris2 = new ApogeeSOAP($wsdl, $authentication, $xmlTree);
//
//    /// connect to Web Service
//    $apogee_curl = $apogee_paris2->webServiceConnect();
//
//    /// execute CURL
//    print_r($apogee_paris2->sendRequest($apogee_curl));
//} catch(Exception $e) {
//    print_r($e->getMessage());
//}

