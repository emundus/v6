<?php
namespace Payplug\Core;
use Payplug;

class HttpClient
{
    public static $CACERT_PATH;

    public static $REQUEST_HANDLER = null;

    private static $defaultUserAgentProducts = array();

    private $_configuration;

    public function __construct(Payplug\Payplug $authentication = null)
    {
        $this->_configuration = $authentication;
    }

    public function post($resource, $data = null, $authenticated = true)
    {
        return $this->request('POST', $resource, $data, $authenticated);
    }

    public function patch($resource, $data = null)
    {
        return $this->request('PATCH', $resource, $data);
    }

    public function delete($resource, $data = null)
    {
        return $this->request('DELETE', $resource, $data);
    }

    public function get($resource, $data = null)
    {
        return $this->request('GET', $resource, $data);
    }

    public function testRemote() {
        return $this->request('GET', APIRoutes::getTestRoute(), null, false);
    }

    public static function addDefaultUserAgentProduct($product, $version = null, $comment = null)
    {
        self::$defaultUserAgentProducts[] = array($product, $version, $comment);
    }

    private static function formatUserAgentProduct($product, $version = null, $comment = null)
    {
        $productString = $product;
        if ($version) {
            $productString .= '/' . $version;
        }
        if ($comment) {
            $productString .= ' (' . $comment . ')';
        }
        return $productString;
    }

    public static function getUserAgent()
    {
        $curlVersion = curl_version(); // Do not move this inside $headers even if it is used only there.
        $userAgent = self::formatUserAgentProduct('PayPlug-PHP',
                                                  Payplug\Core\Config::LIBRARY_VERSION,
                                                  sprintf('PHP/%s; curl/%s', phpversion(), $curlVersion['version']));
        foreach (self::$defaultUserAgentProducts as $product) {
            $userAgent .= ' ' . self::formatUserAgentProduct($product[0], $product[1], $product[2]);
        }
        return $userAgent;
    }

    public function getVersion()
    {
        return Payplug\Core\Config::API_VERSION;
    }

    private function request($httpVerb, $resource, array $data = null, $authenticated = true)
    {
        if (self::$REQUEST_HANDLER === null) {
            $request = new CurlRequest();
        }
        else {
            $request = self::$REQUEST_HANDLER;
        }

        $userAgent = self::getUserAgent();
        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'User-Agent: ' . $userAgent
        );
        if ($authenticated) {
            $headers[] = 'Authorization: Bearer ' . $this->_configuration->getToken();
        }

        $headers[] = 'PayPlug-Version: ' . $this->getVersion();

        $request->setopt(CURLOPT_FAILONERROR, false);
        $request->setopt(CURLOPT_RETURNTRANSFER, true);
        $request->setopt(CURLOPT_CUSTOMREQUEST, $httpVerb);
        $request->setopt(CURLOPT_URL, $resource);
        $request->setopt(CURLOPT_HTTPHEADER, $headers);
        $request->setopt(CURLOPT_SSL_VERIFYPEER, true);
        $request->setopt(CURLOPT_SSL_VERIFYHOST, 2);
        $request->setopt(CURLOPT_CAINFO, self::$CACERT_PATH);
        if (!empty($data)) {
            $request->setopt(CURLOPT_POSTFIELDS, json_encode($data));
        }

        $result = array(
            'httpResponse'  => $request->exec(),
            'httpStatus'    => $request->getInfo(CURLINFO_HTTP_CODE)
        );

        $errorCode = $request->errno();
        $errorMessage = $request->error();

        $request->close();

        $curlStatusNotManage = array(
            0, // CURLE_OK
            22 // CURLE_HTTP_NOT_FOUND or CURLE_HTTP_RETURNED_ERROR
        );

        if (in_array($errorCode, $curlStatusNotManage) && substr($result['httpStatus'], 0, 1) !== '2') {
            $this->throwRequestException($result['httpResponse'], $result['httpStatus']);
        } // If there was an error with curl
        elseif ($result['httpResponse'] === false || $errorCode) {
            $this->throwConnectionException($result['httpStatus'], $errorMessage);
        }

        $result['httpResponse'] = json_decode($result['httpResponse'], true);

        if ($result['httpResponse'] === null) {
            throw new Payplug\Exception\UnexpectedAPIResponseException('API response is not valid JSON.', $result['httpResponse']);
        }

        return $result;
    }

    private function throwConnectionException($errorCode, $errorMessage)
    {
        throw new Payplug\Exception\ConnectionException(
            'Connection to the API failed with the following message: ' . $errorMessage, $errorCode
        );
    }

    private function throwRequestException($httpResponse, $httpStatus)
    {
        $exception = null;

        if (substr($httpStatus, 0, 1) === '5') {
            throw new Payplug\Exception\PayplugServerException('Unexpected server error during the request.',
                $httpResponse, $httpStatus
            );
        }

        switch ($httpStatus) {
            case 400:
                throw new Payplug\Exception\BadRequestException('Bad request.', $httpResponse, $httpStatus);
                break;
            case 401:
                throw new Payplug\Exception\UnauthorizedException('Unauthorized. Please check your credentials.',
                    $httpResponse, $httpStatus);
                break;
            case 403:
                throw new Payplug\Exception\ForbiddenException('Forbidden error. You are not allowed to access this resource.',
                    $httpResponse, $httpStatus);
                break;
            case 404:
                throw new Payplug\Exception\NotFoundException('The resource you requested could not be found.',
                    $httpResponse, $httpStatus);
                break;
            case 405:
                throw new Payplug\Exception\NotAllowedException('The requested method is not supported by this resource.',
                    $httpResponse, $httpStatus);
                break;
        }

        throw new Payplug\Exception\HttpException('Unhandled HTTP error.', $httpResponse, $httpStatus);
    }
}

HttpClient::$CACERT_PATH = realpath(__DIR__ . str_replace('/', DIRECTORY_SEPARATOR, '/../../certs/cacert.pem'));
