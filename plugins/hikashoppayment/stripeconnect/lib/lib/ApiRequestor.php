<?php

namespace Stripe;

class ApiRequestor
{
    private $_apiKey;

    private $_apiBase;

    private static $_httpClient;

    private static $requestTelemetry;

    public function __construct($apiKey = null, $apiBase = null)
    {
        $this->_apiKey = $apiKey;
        if (!$apiBase) {
            $apiBase = Stripe::$apiBase;
        }
        $this->_apiBase = $apiBase;
    }

    private static function _telemetryJson($requestTelemetry)
    {
        $payload = array(
            'last_request_metrics' => array(
                'request_id' => $requestTelemetry->requestId,
                'request_duration_ms' => $requestTelemetry->requestDuration,
        ));

        $result = json_encode($payload);
        if ($result != false) {
            return $result;
        } else {
            Stripe::getLogger()->error("Serializing telemetry payload failed!");
            return "{}";
        }
    }

    private static function _encodeObjects($d)
    {
        if ($d instanceof ApiResource) {
            return Util\Util::utf8($d->id);
        } elseif ($d === true) {
            return 'true';
        } elseif ($d === false) {
            return 'false';
        } elseif (is_array($d)) {
            $res = [];
            foreach ($d as $k => $v) {
                $res[$k] = self::_encodeObjects($v);
            }
            return $res;
        } else {
            return Util\Util::utf8($d);
        }
    }

    public function request($method, $url, $params = null, $headers = null)
    {
        $params = $params ?: [];
        $headers = $headers ?: [];
        list($rbody, $rcode, $rheaders, $myApiKey) =
        $this->_requestRaw($method, $url, $params, $headers);
        $json = $this->_interpretResponse($rbody, $rcode, $rheaders);
        $resp = new ApiResponse($rbody, $rcode, $rheaders, $json);
        return [$resp, $myApiKey];
    }

    public function handleErrorResponse($rbody, $rcode, $rheaders, $resp)
    {
        if (!is_array($resp) || !isset($resp['error'])) {
            $msg = "Invalid response object from API: $rbody "
              . "(HTTP response code was $rcode)";
            throw new Error\Api($msg, $rcode, $rbody, $resp, $rheaders);
        }

        $errorData = $resp['error'];

        $error = null;
        if (is_string($errorData)) {
            $error = self::_specificOAuthError($rbody, $rcode, $rheaders, $resp, $errorData);
        }
        if (!$error) {
            $error = self::_specificAPIError($rbody, $rcode, $rheaders, $resp, $errorData);
        }

        throw $error;
    }

    private static function _specificAPIError($rbody, $rcode, $rheaders, $resp, $errorData)
    {
        $msg = isset($errorData['message']) ? $errorData['message'] : null;
        $param = isset($errorData['param']) ? $errorData['param'] : null;
        $code = isset($errorData['code']) ? $errorData['code'] : null;
        $type = isset($errorData['type']) ? $errorData['type'] : null;

        switch ($rcode) {
            case 400:
                if ($code == 'rate_limit') {
                    return new Error\RateLimit($msg, $param, $rcode, $rbody, $resp, $rheaders);
                }
                if ($type == 'idempotency_error') {
                    return new Error\Idempotency($msg, $rcode, $rbody, $resp, $rheaders);
                }

            case 404:
                return new Error\InvalidRequest($msg, $param, $rcode, $rbody, $resp, $rheaders);
            case 401:
                return new Error\Authentication($msg, $rcode, $rbody, $resp, $rheaders);
            case 402:
                return new Error\Card($msg, $param, $code, $rcode, $rbody, $resp, $rheaders);
            case 403:
                return new Error\Permission($msg, $rcode, $rbody, $resp, $rheaders);
            case 429:
                return new Error\RateLimit($msg, $param, $rcode, $rbody, $resp, $rheaders);
            default:
                return new Error\Api($msg, $rcode, $rbody, $resp, $rheaders);
        }
    }

    private static function _specificOAuthError($rbody, $rcode, $rheaders, $resp, $errorCode)
    {
        $description = isset($resp['error_description']) ? $resp['error_description'] : $errorCode;

        switch ($errorCode) {
            case 'invalid_client':
                return new Error\OAuth\InvalidClient($errorCode, $description, $rcode, $rbody, $resp, $rheaders);
            case 'invalid_grant':
                return new Error\OAuth\InvalidGrant($errorCode, $description, $rcode, $rbody, $resp, $rheaders);
            case 'invalid_request':
                return new Error\OAuth\InvalidRequest($errorCode, $description, $rcode, $rbody, $resp, $rheaders);
            case 'invalid_scope':
                return new Error\OAuth\InvalidScope($errorCode, $description, $rcode, $rbody, $resp, $rheaders);
            case 'unsupported_grant_type':
                return new Error\OAuth\UnsupportedGrantType($errorCode, $description, $rcode, $rbody, $resp, $rheaders);
            case 'unsupported_response_type':
                return new Error\OAuth\UnsupportedResponseType($errorCode, $description, $rcode, $rbody, $resp, $rheaders);
        }

        return null;
    }

    private static function _formatAppInfo($appInfo)
    {
        if ($appInfo !== null) {
            $string = $appInfo['name'];
            if ($appInfo['version'] !== null) {
                $string .= '/' . $appInfo['version'];
            }
            if ($appInfo['url'] !== null) {
                $string .= ' (' . $appInfo['url'] . ')';
            }
            return $string;
        } else {
            return null;
        }
    }

    private static function _defaultHeaders($apiKey, $clientInfo = null)
    {
        $uaString = 'Stripe/v1 PhpBindings/' . Stripe::VERSION;

        $langVersion = phpversion();
        $uname = php_uname();

        $appInfo = Stripe::getAppInfo();
        $ua = [
            'bindings_version' => Stripe::VERSION,
            'lang' => 'php',
            'lang_version' => $langVersion,
            'publisher' => 'stripe',
            'uname' => $uname,
        ];
        if ($clientInfo) {
            $ua = array_merge($clientInfo, $ua);
        }
        if ($appInfo !== null) {
            $uaString .= ' ' . self::_formatAppInfo($appInfo);
            $ua['application'] = $appInfo;
        }

        $defaultHeaders = [
            'X-Stripe-Client-User-Agent' => json_encode($ua),
            'User-Agent' => $uaString,
            'Authorization' => 'Bearer ' . $apiKey,
        ];
        return $defaultHeaders;
    }

    private function _requestRaw($method, $url, $params, $headers)
    {
        $myApiKey = $this->_apiKey;
        if (!$myApiKey) {
            $myApiKey = Stripe::$apiKey;
        }

        if (!$myApiKey) {
            $msg = 'No API key provided.  (HINT: set your API key using '
              . '"Stripe::setApiKey(<API-KEY>)".  You can generate API keys from '
              . 'the Stripe web interface.  See https://stripe.com/api for '
              . 'details, or email support@stripe.com if you have any questions.';
            throw new Error\Authentication($msg);
        }

        $clientUAInfo = null;
        if (method_exists($this->httpClient(), 'getUserAgentInfo')) {
            $clientUAInfo = $this->httpClient()->getUserAgentInfo();
        }

        $absUrl = $this->_apiBase.$url;
        $params = self::_encodeObjects($params);
        $defaultHeaders = $this->_defaultHeaders($myApiKey, $clientUAInfo);
        if (Stripe::$apiVersion) {
            $defaultHeaders['Stripe-Version'] = Stripe::$apiVersion;
        }

        if (Stripe::$accountId) {
            $defaultHeaders['Stripe-Account'] = Stripe::$accountId;
        }

        if (Stripe::$enableTelemetry && self::$requestTelemetry != null) {
            $defaultHeaders["X-Stripe-Client-Telemetry"] = self::_telemetryJson(self::$requestTelemetry);
        }

        $hasFile = false;
        $hasCurlFile = class_exists('\CURLFile', false);
        foreach ($params as $k => $v) {
            if (is_resource($v)) {
                $hasFile = true;
                $params[$k] = self::_processResourceParam($v, $hasCurlFile);
            } elseif ($hasCurlFile && $v instanceof \CURLFile) {
                $hasFile = true;
            }
        }

        if ($hasFile) {
            $defaultHeaders['Content-Type'] = 'multipart/form-data';
        } else {
            $defaultHeaders['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        $combinedHeaders = array_merge($defaultHeaders, $headers);
        $rawHeaders = [];

        foreach ($combinedHeaders as $header => $value) {
            $rawHeaders[] = $header . ': ' . $value;
        }

        $requestStartMs = Util\Util::currentTimeMillis();

        list($rbody, $rcode, $rheaders) = $this->httpClient()->request(
            $method,
            $absUrl,
            $rawHeaders,
            $params,
            $hasFile
        );

        if (array_key_exists('request-id', $rheaders)) {
            self::$requestTelemetry = new RequestTelemetry(
                $rheaders['request-id'],
                Util\Util::currentTimeMillis() - $requestStartMs
            );
        }

        return [$rbody, $rcode, $rheaders, $myApiKey];
    }

    private function _processResourceParam($resource, $hasCurlFile)
    {
        if (get_resource_type($resource) !== 'stream') {
            throw new Error\Api(
                'Attempted to upload a resource that is not a stream'
            );
        }

        $metaData = stream_get_meta_data($resource);
        if ($metaData['wrapper_type'] !== 'plainfile') {
            throw new Error\Api(
                'Only plainfile resource streams are supported'
            );
        }

        if ($hasCurlFile) {
            return new \CURLFile($metaData['uri']);
        } else {
            return '@'.$metaData['uri'];
        }
    }

    private function _interpretResponse($rbody, $rcode, $rheaders)
    {
        $resp = json_decode($rbody, true);
        $jsonError = json_last_error();
        if ($resp === null && $jsonError !== JSON_ERROR_NONE) {
            $msg = "Invalid response body from API: $rbody "
              . "(HTTP response code was $rcode, json_last_error() was $jsonError)";
            throw new Error\Api($msg, $rcode, $rbody);
        }

        if ($rcode < 200 || $rcode >= 300) {
            $this->handleErrorResponse($rbody, $rcode, $rheaders, $resp);
        }
        return $resp;
    }

    public static function setHttpClient($client)
    {
        self::$_httpClient = $client;
    }

    public static function resetTelemetry()
    {
        self::$requestTelemetry = null;
    }

    private function httpClient()
    {
        if (!self::$_httpClient) {
            self::$_httpClient = HttpClient\CurlClient::instance();
        }
        return self::$_httpClient;
    }
}
