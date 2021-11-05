<?php
require_once("XmlSchema.php");

# credential types : NoAuth // API Key // Bearer Token // Basic Auth // Digest Auth // OAuth 1.0 // OAuth 2.0 // Haw Authentication // AWS Signature // NTLM Authentication // Akamai EdgeGrid

class SoapConnect {
    public function setSoapHeader($xmlTree, $credentials) {
        $headers = array(
            'type' => 'Content-Type: text/xml; charset="utf-8"',
            'content' => 'Content-Length: ' . strlen($xmlTree),
            'accept' => 'Accept: text/xml',
            'cache' => 'Cache-Control: no-cache',
            'pragma' => 'Pragma: no-cache',
            'auth' => 'Authorization: ',
        );

        switch($credentials->auth_type) {
            case 0:
                # not available at this moment
                break;
            case 1:
                # not available at this moment
                break;
            case 2:
                $headers['auth']  =  $headers['auth'] . 'Basic ' . base64_encode($credentials->auth_user.':'.$credentials->auth_pwd);     # basic auth = base64_encode(username:password) - more info: https://developer.mozilla.org/en-US/docs/Web/HTTP/Authentication
                break;
            case 3:
                # not available at this moment
                break;
            default:
                exit;
        }

        return array_values($headers);
    }

    public function webServiceConnect($wsdl,$xmlTree,$credentials) {
        # init connection to web server with CURL
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $wsdl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->setSoapHeader($xmlTree,$credentials));

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlTree);

        switch($credentials->auth_type) {
            case 0:     /// no auth
                break;

            case 2:
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, base64_encode($credentials->auth_user.':'.$credentials->auth_pwd));
                break;
        }

        return $ch;
    }

    public function sendRequest($curl_obj) {
        # send request
        curl_exec($curl_obj);
    }
}


