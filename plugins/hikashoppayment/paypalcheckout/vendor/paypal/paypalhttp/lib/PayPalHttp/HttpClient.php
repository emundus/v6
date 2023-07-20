<?php

namespace PayPalHttp;

class HttpClient
{
    public $environment;

    public $injectors = [];

    public $encoder;

    function __construct(Environment $environment)
    {
        $this->environment = $environment;
        $this->encoder = new Encoder();
        $this->curlCls = Curl::class;
    }

    public function addInjector(Injector $inj)
    {
        $this->injectors[] = $inj;
    }

    public function execute(HttpRequest $httpRequest)
    {
        $requestCpy = clone $httpRequest;
        $curl = new Curl();

        foreach ($this->injectors as $inj) {
            $inj->inject($requestCpy);
        }

        $url = $this->environment->baseUrl() . $requestCpy->path;
        $formattedHeaders = $this->prepareHeaders($requestCpy->headers);
        if (!array_key_exists("user-agent", $formattedHeaders)) {
            $requestCpy->headers["user-agent"] = $this->userAgent();
        }

        $body = "";
        if (!is_null($requestCpy->body)) {
            $rawHeaders = $requestCpy->headers;
            $requestCpy->headers = $formattedHeaders;
            $body = $this->encoder->serializeRequest($requestCpy);
            $requestCpy->headers = $this->mapHeaders($rawHeaders,$requestCpy->headers);
        }

        $curl->setOpt(CURLOPT_URL, $url);
        $curl->setOpt(CURLOPT_CUSTOMREQUEST, $requestCpy->verb);
        $curl->setOpt(CURLOPT_HTTPHEADER, $this->serializeHeaders($requestCpy->headers));
        $curl->setOpt(CURLOPT_RETURNTRANSFER, 1);
        $curl->setOpt(CURLOPT_HEADER, 0);

        if (!is_null($requestCpy->body)) {
            $curl->setOpt(CURLOPT_POSTFIELDS, $body);
        }

        if (strpos($this->environment->baseUrl(), "https://") === 0) {
            $curl->setOpt(CURLOPT_SSL_VERIFYPEER, true);
            $curl->setOpt(CURLOPT_SSL_VERIFYHOST, 2);
        }

        if ($caCertPath = $this->getCACertFilePath()) {
            $curl->setOpt(CURLOPT_CAINFO, $caCertPath);
        }

        $response = $this->parseResponse($curl);
        $curl->close();

        return $response;
    }

    public function prepareHeaders($headers){
        $preparedHeaders = array_change_key_case($headers);
        if (array_key_exists("content-type", $preparedHeaders)) {
            $preparedHeaders["content-type"] = strtolower($preparedHeaders["content-type"]);
        }
        return $preparedHeaders;
    }

    public function mapHeaders($rawHeaders, $formattedHeaders){
        $rawHeadersKey = array_keys($rawHeaders);
        foreach ($rawHeadersKey as $array_key) {
            if(array_key_exists(strtolower($array_key), $formattedHeaders)){
                $rawHeaders[$array_key] = $formattedHeaders[strtolower($array_key)];
            }
        }
        return $rawHeaders;
    }

    public function userAgent()
    {
        return "PayPalHttp-PHP HTTP/1.1";
    }

    protected function getCACertFilePath()
    {
        return null;
    }

    protected function setCurl(Curl $curl)
    {
        $this->curl = $curl;
    }

    protected function setEncoder(Encoder $encoder)
    {
        $this->encoder = $encoder;
    }

    private function serializeHeaders($headers)
    {
        $headerArray = [];
        if ($headers) {
            foreach ($headers as $key => $val) {
                $headerArray[] = $key . ": " . $val;
            }
        }

        return $headerArray;
    }

    private function parseResponse($curl)
    {
        $headers = [];
        $curl->setOpt(CURLOPT_HEADERFUNCTION,
            function($curl, $header) use (&$headers)
            {
                $len = strlen($header);

                $k = "";
                $v = "";

                $this->deserializeHeader($header, $k, $v);
                $headers[$k] = $v;

                return $len;
            });

        $responseData = $curl->exec();
        $statusCode = $curl->getInfo(CURLINFO_HTTP_CODE);
        $errorCode = $curl->errNo();
        $error = $curl->error();

        if ($errorCode > 0) {
            throw new IOException($error, $errorCode);
        }

        $body = $responseData;

        if ($statusCode >= 200 && $statusCode < 300) {
            $responseBody = NULL;

            if (!empty($body)) {
                $responseBody = $this->encoder->deserializeResponse($body, $this->prepareHeaders($headers));
            }

            return new HttpResponse(
                $errorCode === 0 ? $statusCode : $errorCode,
                $responseBody,
                $headers
            );
        } else {
            throw new HttpException($body, $statusCode, $headers);
        }
    }

    private function deserializeHeader($header, &$key, &$value)
    {
        if (strlen($header) > 0) {
            if (empty($header) || strpos($header, ':') === false) {
                return NULL;
            }

            list($k, $v) = explode(":", $header);
            $key = trim($k);
            $value = trim($v);
        }
    }
}
