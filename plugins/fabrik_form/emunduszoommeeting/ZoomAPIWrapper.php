<?php

class ZoomAPIWrapper {
    private $errors;
    private $apiKey;
    private $baseUrl;
    private $timeout;

    public function __construct( $apiKey) {
        $this->apiKey = $apiKey;

        $this->baseUrl = 'https://api.zoom.us/v2';
        $this->timeout = 30;
    }

    private function pathReplace( $path, $requestParams ){
        $errors = array();
        $path = preg_replace_callback( '/\\{(.*?)\\}/',function( $matches ) use( $requestParams,$errors ) {
            if (!isset($requestParams[$matches[1]])) {
                $this->errors[] = 'Required path parameter was not specified: '.$matches[1];
                return '';
            }
            return rawurlencode($requestParams[$matches[1]]);
        }, $path);

        if (count($errors)) $this->errors = array_merge( $this->errors, $errors );
        return $path;
    }

    private function headers() {
        return array(
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        );
    }

    public function doRequest($method, $path, $queryParams=array(), $pathParams=array(), $body='') {
        if (is_array($body)) {
            // Treat an empty array in the body data as if no body data was set
            if (!count($body)) $body = '';
            else $body = json_encode( $body );
        }

        $this->errors = array();
        $this->responseCode = 0;

        $path = $this->pathReplace( $path, $pathParams );

        if (count($this->errors)) return false;

        $method = strtoupper($method);
        $url = $this->baseUrl.$path;

        // Add on any query parameters
        if (count($queryParams)) $url .= '?'.http_build_query($queryParams);

        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$this->headers());
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch,CURLOPT_TIMEOUT,$this->timeout);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

        if (in_array($method,array('DELETE','PATCH','POST','PUT'))) {

            // All except DELETE can have a payload in the body
            if ($method!='DELETE' && strlen($body)) {
                curl_setopt($ch, CURLOPT_POST, true );
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body );
            }

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        $result = curl_exec($ch);

        $contentType = curl_getinfo($ch,CURLINFO_CONTENT_TYPE);
        $this->responseCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);

        curl_close($ch);

        return json_decode($result,true);
    }

    // Returns the errors responseCode returned from the last call to doRequest
    function requestErrors() {
        JLog::add('Cannot invoke Zoom API, doRequest may have error : ' . JLog::ERROR, 'com_emundus');
        return $this->errors;
    }

    // Returns the responseCode returned from the last call to doRequest
    function responseCode() {
        return $this->responseCode;
    }
}