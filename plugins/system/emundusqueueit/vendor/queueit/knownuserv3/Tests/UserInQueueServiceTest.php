<?php
#has already been included in TestSuite.php
#require __DIR__ . '/vendor/simpletest/simpletest/autorun.php';

require_once( __DIR__ . '/../UserInQueueStateCookieRepository.php');
require_once( __DIR__ . '/../Models.php');
require_once( __DIR__ . '/../UserInQueueService.php');

error_reporting(E_ALL);

class UserInQueueStateRepositoryMockClass implements QueueIT\KnownUserV3\SDK\IUserInQueueStateRepository 
{
    public $arrayFunctionCallsArgs;
    public $arrayReturns;

    function __construct() {
        $this->arrayFunctionCallsArgs = array(
            'store' => array(),
            'getState' => array(),
            'cancelQueueCookie' => array(),
            'extendQueueCookie' => array()
        );

        $this->arrayReturns = array(
            'store' => array(),
            'getState' => array(),
            'cancelQueueCookie' => array(),
            'extendQueueCookie' => array()
        );
    }

    public function store($eventId, $queueId, $fixedCookieValidityMinutes, $cookieDomain, $isCookieHttpOnly, $isCookieSecure, $redirectType, $secretKey) {
        array_push(
        $this->arrayFunctionCallsArgs['store'],
        array(
            $eventId,
            $queueId,
            $fixedCookieValidityMinutes,
            $cookieDomain,
            $isCookieHttpOnly,
            $isCookieSecure,
            $redirectType,
            $secretKey)
        );
    }

    public function getState($eventId, $cookieValidityMinutes, $secretKey, $validateTime) {
        array_push(
            $this->arrayFunctionCallsArgs['getState'],
            array(
                $eventId,
                $cookieValidityMinutes,
                $secretKey,
                $validateTime)
        );

        return $this->arrayReturns['getState'][count($this->arrayFunctionCallsArgs['getState']) - 1];
    }

    public function cancelQueueCookie($eventId, $cookieDomain, $isCookieHttpOnly, $isCookieSecure) {
        array_push(
            $this->arrayFunctionCallsArgs['cancelQueueCookie'],
            array(
                $eventId,
                $cookieDomain,
                $isCookieHttpOnly,
                $isCookieSecure)
        );
    }

    public function reissueQueueCookie($eventId, $cookieValidityMinutes, $cookieDomain, $isCookieHttpOnly, $isCookieSecure, $secretKey) {
        array_push(
            $this->arrayFunctionCallsArgs['store'],
            array(
                $eventId,
                $cookieValidityMinutes,
                $cookieDomain,
                $isCookieHttpOnly,
                $isCookieSecure,
                $secretKey)
        );
    }

    public function expectCall($functionName, $secquenceNo, array $argument) {
        if (count($this->arrayFunctionCallsArgs[$functionName]) >= $secquenceNo) {

            $argArr = $this->arrayFunctionCallsArgs[$functionName][$secquenceNo - 1];
            if (count($argument) != count($argArr)) {
                return false;
            }

            for ($i = 0; $i <= count($argArr) - 1;  ++$i) {
                //print($argArr[$i]."\xA".$argument[$i]);
                if ($argArr[$i] !== $argument[$i]) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    public function expectCallAny($functionName) {
        if (count($this->arrayFunctionCallsArgs[$functionName]) >= 1) {
            return true;
        }
        return false;
    }
}

class UserInQueueServiceTest extends UnitTestCase 
{
    public function test_validateQueueRequest_ValidState_ExtendableCookie_NoCookieExtensionFromConfig_DoNotRedirectDoNotStoreCookieWithExtension() {
        $eventConfig = new QueueIT\KnownUserV3\SDK\QueueEventConfig();
        $eventConfig->eventId = "e1";
        $eventConfig->queueDomain = "testDomain";
        $eventConfig->cookieDomain = "testDomain";	
        $eventConfig->cookieValidityMinute = 10;
        $eventConfig->extendCookieValidity = false;
        $eventConfig->actionName = "QueueAction";
        $cookieProviderMock = new UserInQueueStateRepositoryMockClass ();

        array_push($cookieProviderMock->arrayReturns['getState'], new QueueIT\KnownUserV3\SDK\StateInfo(true, true, "queueId", null, "idle"));
        $testObject = new QueueIT\KnownUserV3\SDK\UserInQueueService($cookieProviderMock);

        $result = $testObject->validateQueueRequest("url", "token", $eventConfig, "customerid", "key");
        
        $this->assertTrue(!$result->doRedirect());
        $this->assertTrue($result->queueId == "queueId");
        $this->assertFalse($cookieProviderMock->expectCallAny('store'));
        $this->assertTrue($cookieProviderMock->expectCall('getState', 1, array("e1", 10, 'key', true)));
        $this->assertTrue(strtolower($result->actionName) == strtolower('QueueAction'));
    }
    
    public function test_validateQueueRequest_ValidState_ExtendableCookie_CookieExtensionFromConfig_DoNotRedirectDoStoreCookieWithExtension() {
        $eventConfig = new QueueIT\KnownUserV3\SDK\QueueEventConfig();
        $eventConfig->eventId = "e1";
        $eventConfig->queueDomain = "testDomain.com";
        $eventConfig->cookieDomain = "testDomain";
        $eventConfig->isCookieHttpOnly = false;
        $eventConfig->isCookieSecure = false;
        $eventConfig->cookieValidityMinute=10;
        $eventConfig->extendCookieValidity=true;
        $eventConfig->actionName = "QueueAction";
        
        $cookieProviderMock = new UserInQueueStateRepositoryMockClass ();
        array_push($cookieProviderMock->arrayReturns['getState'], new QueueIT\KnownUserV3\SDK\StateInfo(true, true, "queueId", null, "disabled"));
    
        $testObject = new QueueIT\KnownUserV3\SDK\UserInQueueService($cookieProviderMock);
    
        $result = $testObject->validateQueueRequest("url", "token", $eventConfig, "customerid", "key");
        $this->assertTrue(!$result->doRedirect());
        $this->assertTrue($result->eventId == 'e1');
        $this->assertTrue($result->queueId == "queueId");
     
        $this->assertTrue($cookieProviderMock->expectCall('store', 1, array("e1", 'queueId', null, 'testDomain', false, false, "disabled", "key")));
        $this->assertTrue(strtolower($result->actionName) == strtolower('QueueAction'));
    }
    
    public function test_validateQueueRequest_ValidState_NoExtendableCookie_DoNotRedirectDoNotStoreCookieWithExtension() {
        $eventConfig = new QueueIT\KnownUserV3\SDK\QueueEventConfig();
        $eventConfig->eventId = "e1";
        $eventConfig->queueDomain = "testDomain";
        $eventConfig->cookieValidityMinute = 10;
        $eventConfig->extendCookieValidity = true;
        $eventConfig->actionName = "QueueAction";
    
        $cookieProviderMock = new UserInQueueStateRepositoryMockClass ();
        array_push($cookieProviderMock->arrayReturns['getState'], new QueueIT\KnownUserV3\SDK\StateInfo(true, true, "queueId", 3, "idle"));
    
        $testObject = new QueueIT\KnownUserV3\SDK\UserInQueueService($cookieProviderMock);
    
        $result = $testObject->validateQueueRequest("url", "token", $eventConfig, "customerid", "key");
        $this->assertTrue(!$result->doRedirect());
        $this->assertTrue($result->eventId == 'e1');
        $this->assertTrue($result->queueId == "queueId");
        $this->assertFalse($cookieProviderMock->expectCallAny('store', 1));
        $this->assertTrue(strtolower($result->actionName) == strtolower('QueueAction'));
    }
    
    public function test_validateQueueRequest_NoCookie_TampredToken_RedirectToErrorPageWithHashError_DoNotStoreCookie() {
        $key = "4e1db821-a825-49da-acd0-5d376f2068db";
        $eventConfig = new QueueIT\KnownUserV3\SDK\QueueEventConfig();
        $eventConfig->eventId = "e1";
        $eventConfig->queueDomain = "testDomain.com";
        $eventConfig->cookieValidityMinute = 10;
        $eventConfig->extendCookieValidity = true;
        $eventConfig->version = 11;
        $url = "http://test.test.com?b=h";
        $eventConfig->actionName = "QueueAction";
         
        $cookieProviderMock = new UserInQueueStateRepositoryMockClass ();
        array_push($cookieProviderMock->arrayReturns['getState'], new QueueIT\KnownUserV3\SDK\StateInfo(false, false, null, null, null));
            
        $token = $this->generateHash('e1','queueId', strval(time() + (3 * 60)), 'False', null, 'idle', $key);
        $token = str_replace("False", 'True', $token);
        $expectedErrorUrl = "https://testDomain.com/error/hash/?c=testCustomer&e=e1" .
                "&ver=".QueueIT\KnownUserV3\SDK\UserInQueueService::getSDKVersion()
                . "&cver=11"
                . "&man=" . rawurlencode($eventConfig->actionName)
                . "&queueittoken=" . $token
                . "&t=" . rawurlencode($url);
    
        $testObject = new QueueIT\KnownUserV3\SDK\UserInQueueService($cookieProviderMock);
        $result = $testObject->validateQueueRequest($url, $token, $eventConfig, "testCustomer", $key);
        $this->assertFalse($cookieProviderMock->expectCallAny('store'));
    
        $this->assertTrue($result->doRedirect());
        $this->assertTrue($result->eventId == 'e1');
        $matches = array();
        preg_match("/&ts=[^&]*/", $result->redirectUrl, $matches);
        $timestamp = str_replace("&ts=", "", $matches[0]);
        $timestamp = str_replace("&", "", $timestamp);
        $this->assertTrue(time() - intval($timestamp) < 100);
        $urlWithoutTimeStamp = preg_replace("/&ts=[^&]*/", "", $result->redirectUrl);
        $this->assertTrue(strtolower($urlWithoutTimeStamp) == strtolower($expectedErrorUrl));
        $this->assertTrue(strtolower($result->actionName) == strtolower($eventConfig->actionName));
    }
    
    public function test_validateQueueRequest_NoCookie_ExpiredTimeStampInToken_RedirectToErrorPageWithTimeStampError_DoNotStoreCookie() {
        $key = "4e1db821-a825-49da-acd0-5d376f2068db";
        $eventConfig = new QueueIT\KnownUserV3\SDK\QueueEventConfig();
        $eventConfig->eventId = "e1";
        $eventConfig->queueDomain = "testDomain.com";
        $eventConfig->cookieValidityMinute = 10;
        $eventConfig->extendCookieValidity = false;
        $eventConfig->version = 11;
        $eventConfig->actionName = "Queue Action (._~-) !*|'\"";
        $url = "http://test.test.com?b=h";
        $cookieProviderMock = new UserInQueueStateRepositoryMockClass ();
        array_push($cookieProviderMock->arrayReturns['getState'], new QueueIT\KnownUserV3\SDK\StateInfo(false, false, null, null, null));
        $token = $this->generateHash('e1','queueId', strval(time() - (3 * 60)), 'False', null, 'queue', $key);
    
        $expectedErrorUrl = "https://testDomain.com/error/timestamp/?c=testCustomer&e=e1" .
                  "&ver=".QueueIT\KnownUserV3\SDK\UserInQueueService::getSDKVersion()
                . "&cver=11"
                . "&man=" . rawurlencode($eventConfig->actionName)
                . "&queueittoken=" . $token
                . "&t=" . rawurlencode($url);
    
        $testObject = new QueueIT\KnownUserV3\SDK\UserInQueueService($cookieProviderMock);
        $result = $testObject->validateQueueRequest($url, $token, $eventConfig, "testCustomer", $key);
        $this->assertFalse($cookieProviderMock->expectCallAny('store'));
    
        $this->assertTrue($result->doRedirect());
        $this->assertTrue($result->eventId == 'e1');
        $matches = array();
        preg_match("/&ts=[^&]*/", $result->redirectUrl, $matches);
        $timestamp = str_replace("&ts=", "", $matches[0]);
        $timestamp = str_replace("&", "", $timestamp);
        $this->assertTrue(time() - intval($timestamp) < 100);
    
        $urlWithoutTimeStamp = preg_replace("/&ts=[^&]*/", "", $result->redirectUrl);
        $this->assertTrue(strtolower($urlWithoutTimeStamp) == strtolower($expectedErrorUrl));
        $this->assertTrue($result->actionName == $eventConfig->actionName);
    }
    
    public function test_validateQueueRequest_NoCookie_EventIdMismatch_RedirectToErrorPageWithEventIdMissMatchError_DoNotStoreCookie() {
        $key = "4e1db821-a825-49da-acd0-5d376f2068db";
        $eventConfig = new QueueIT\KnownUserV3\SDK\QueueEventConfig();
        $eventConfig->eventId = "e2";
        $eventConfig->queueDomain = "testDomain.com";
        $eventConfig->cookieValidityMinute = 10;
        $eventConfig->extendCookieValidity = true;
        $eventConfig->version = 10;
        $eventConfig->actionName = "Queue Action (._~-) !*|'\"";
        $url = "http://test.test.com?b=h";
        $cookieProviderMock = new UserInQueueStateRepositoryMockClass ();
        array_push($cookieProviderMock->arrayReturns['getState'], new QueueIT\KnownUserV3\SDK\StateInfo(false, false, null, null, null));
        $token = $this->generateHash('e1', 'queueId',strval(time() - (3 * 60)), 'False', null, 'queue', $key);
    
        $expectedErrorUrl = "https://testDomain.com/error/eventid/?c=testCustomer&e=e2" .
                "&ver=".QueueIT\KnownUserV3\SDK\UserInQueueService::getSDKVersion()
                . "&cver=10"
                . "&man=" . rawurlencode($eventConfig->actionName)
                . "&queueittoken=" . $token
                . "&t=" . rawurlencode($url);
    
        $testObject = new QueueIT\KnownUserV3\SDK\UserInQueueService($cookieProviderMock);
        $result = $testObject->validateQueueRequest($url, $token, $eventConfig, "testCustomer", $key);
        $this->assertFalse($cookieProviderMock->expectCallAny('store'));
    
        $this->assertTrue($result->doRedirect());
        $this->assertTrue($result->eventId == 'e2');
        $matches = array();
        preg_match("/&ts=[^&]*/", $result->redirectUrl, $matches);
        $timestamp = str_replace("&ts=", "", $matches[0]);
        $timestamp = str_replace("&", "", $timestamp);
        $this->assertTrue(time() - intval($timestamp) < 100);
    
        $urlWithoutTimeStamp = preg_replace("/&ts=[^&]*/", "", $result->redirectUrl);
        $this->assertTrue(strtolower($urlWithoutTimeStamp) == strtolower($expectedErrorUrl));
        $this->assertTrue($result->actionName == $eventConfig->actionName);
    }
    
    public function test_validateQueueRequest_NoCookie_ValidToken_ExtendableCookie_DoNotRedirect_StoreExtendableCookie() {
        $key = "4e1db821-a825-49da-acd0-5d376f2068db";
        $eventConfig = new QueueIT\KnownUserV3\SDK\QueueEventConfig();
        $eventConfig->eventId = "e1";
        $eventConfig->queueDomain = "testDomain.com";
        $eventConfig->cookieValidityMinute = 10;
        $eventConfig->cookieDomain = "testDomain";
        $eventConfig->isCookieHttpOnly = false;
        $eventConfig->isCookieSecure = false;
        $eventConfig->extendCookieValidity = true;
        $eventConfig->version = 11;
        //$eventConfig->actionName = "QueueAction";
        $url = "http://test.test.com?b=h";
        $cookieProviderMock = new UserInQueueStateRepositoryMockClass ();
        array_push($cookieProviderMock->arrayReturns['getState'], new QueueIT\KnownUserV3\SDK\StateInfo(false, false, null, null, null));
        
        $token = $this->generateHash('e1', 'queueId',strval(time() + (3 * 60)), 'true', null, 'queue', $key);
        $testObject = new QueueIT\KnownUserV3\SDK\UserInQueueService($cookieProviderMock);
        $result = $testObject->validateQueueRequest($url, $token, $eventConfig, "testCustomer", $key);
        $this->assertTrue(!$result->doRedirect());
        $this->assertTrue($result->eventId == 'e1');
        $this->assertTrue($result->queueId == 'queueId');
        $this->assertTrue($result->redirectType == 'queue');
        $this->assertTrue($cookieProviderMock->expectCall('store', 1, array("e1",'queueId', null, 'testDomain', false, false, 'queue', $key)));
    }
    
    public function test_validateQueueRequest_NoCookie_ValidToken_CookieValidityMinuteFromToken_DoNotRedirect_StoreNonExtendableCookie() {
        $key = "4e1db821-a825-49da-acd0-5d376f2068db";
        $eventConfig = new QueueIT\KnownUserV3\SDK\QueueEventConfig();
        $eventConfig->eventId = "e1";
        $eventConfig->queueDomain = "testDomain.com";
        $eventConfig->cookieValidityMinute = 30;
        $eventConfig->cookieDomain = "testDomain";
        $eventConfig->isCookieHttpOnly = false;
        $eventConfig->isCookieSecure = false;
        $eventConfig->extendCookieValidity = true;
        $eventConfig->version = 11;
        //$eventConfig->actionName = "QueueAction";
        $url = "http://test.test.com?b=h";
        $cookieProviderMock = new UserInQueueStateRepositoryMockClass ();
        array_push($cookieProviderMock->arrayReturns['getState'], new QueueIT\KnownUserV3\SDK\StateInfo(false, false, null, null, null));
        $token = $this->generateHash('e1', 'queueId',strval(time() + (3 * 60)), 'false', 3, 'DirectLink', $key);
    
        $testObject = new QueueIT\KnownUserV3\SDK\UserInQueueService($cookieProviderMock);
        $result = $testObject->validateQueueRequest($url, $token, $eventConfig, "testCustomer", $key);
        $this->assertTrue(!$result->doRedirect());
        $this->assertTrue($result->eventId == 'e1');
        $this->assertTrue($result->queueId == 'queueId');
        $this->assertTrue($result->redirectType == 'DirectLink');
        $this->assertTrue($cookieProviderMock->expectCall('store', 1, array("e1",'queueId', 3, 'testDomain', false, false, 'DirectLink', $key)));
    }
    
    public function test_NoCookie_NoValidToken_WithoutToken_RedirectToQueue() {
        $key = "4e1db821-a825-49da-acd0-5d376f2068db";
        $eventConfig = new QueueIT\KnownUserV3\SDK\QueueEventConfig();
        $eventConfig->eventId = "e1";
        $eventConfig->queueDomain = "testDomain.com";
        $eventConfig->cookieValidityMinute = 10;
        $eventConfig->extendCookieValidity = true;
        $eventConfig->version = 11;
        $eventConfig->actionName = "Queue Action (._~-) !*|'\"";
        $eventConfig->culture = 'en-US';
        $eventConfig->layoutName = 'testlayout';
    
        $url = "http://test.test.com?b=h";
        $cookieProviderMock = new UserInQueueStateRepositoryMockClass ();
        array_push($cookieProviderMock->arrayReturns['getState'], new QueueIT\KnownUserV3\SDK\StateInfo(false, false, null, null, null));
        $token = "";
    
        $expectedRedirectUrl = "https://testDomain.com/?c=testCustomer&e=e1" .
                "&ver=".QueueIT\KnownUserV3\SDK\UserInQueueService::getSDKVersion()
                . "&cver=11"
                . "&man=" . rawurlencode($eventConfig->actionName)
                . "&cid=en-US"
                . "&l=testlayout"
                . "&t=" . rawurlencode($url);
    
        $testObject = new QueueIT\KnownUserV3\SDK\UserInQueueService($cookieProviderMock);
        $result = $testObject->validateQueueRequest($url, $token, $eventConfig, "testCustomer", $key);
        $this->assertFalse($cookieProviderMock->expectCallAny('store'));
    
        $this->assertTrue($result->doRedirect());
        $this->assertTrue($result->eventId == 'e1');
        $this->assertTrue($result->queueId == null);
        $this->assertTrue(strtolower($result->redirectUrl) == strtolower($expectedRedirectUrl));
    }
    
    public function test_ValidateRequest_NoCookie_WithoutToken_RedirectToQueue_NotargetUrl() {
        $key = "4e1db821-a825-49da-acd0-5d376f2068db";
        $eventConfig = new QueueIT\KnownUserV3\SDK\QueueEventConfig();
        $eventConfig->eventId = "e1";
        $eventConfig->queueDomain = "testDomain.com";
        $eventConfig->cookieValidityMinute = 10;
        $eventConfig->extendCookieValidity = false;
        $eventConfig->version = 10;
        $eventConfig->actionName = "Queue Action (._~-) !*|'\"";
        $eventConfig->culture = null;
        $eventConfig->layoutName = 'testlayout';
    
        $url = "http://test.test.com?b=h";
        $cookieProviderMock = new UserInQueueStateRepositoryMockClass ();
        array_push($cookieProviderMock->arrayReturns['getState'], new QueueIT\KnownUserV3\SDK\StateInfo(false, false, null, null, null));
        $token = "";
    
        $expectedRedirectUrl = "https://testDomain.com/?c=testCustomer&e=e1" .
                "&ver=".QueueIT\KnownUserV3\SDK\UserInQueueService::getSDKVersion()
                . "&cver=10"
                . "&man=" . rawurlencode($eventConfig->actionName)
                . "&l=testlayout";
    
        $testObject = new QueueIT\KnownUserV3\SDK\UserInQueueService($cookieProviderMock);
        $result = $testObject->validateQueueRequest(null, $token, $eventConfig, "testCustomer", $key);
        $this->assertFalse($cookieProviderMock->expectCallAny('store'));
    
        $this->assertTrue($result->doRedirect());
        $this->assertTrue($result->eventId == 'e1');
        $this->assertTrue($result->queueId == null);
        $this->assertTrue(strtolower($result->redirectUrl) == strtolower($expectedRedirectUrl));
        $this->assertTrue(strtolower($result->actionName) == strtolower( $eventConfig->actionName));
    }

    public function test_ValidateRequest_InvalidCookie_WithoutToken_RedirectToQueue_CancelCookie() {
        $key = "4e1db821-a825-49da-acd0-5d376f2068db";
        $eventConfig = new QueueIT\KnownUserV3\SDK\QueueEventConfig();
        $eventConfig->eventId = "e1";
        $eventConfig->queueDomain = "testDomain.com";
        $eventConfig->cookieValidityMinute = 10;
        $eventConfig->extendCookieValidity = false;
        $eventConfig->version = 10;
        $eventConfig->actionName = "Queue Action (._~-) !*|'\"";
        $eventConfig->culture = null;
        $eventConfig->layoutName = 'testlayout';
    
        $url = "http://test.test.com?b=h";
        $cookieProviderMock = new UserInQueueStateRepositoryMockClass ();
        array_push($cookieProviderMock->arrayReturns['getState'], new QueueIT\KnownUserV3\SDK\StateInfo(true, false, null, null, null));
        $token = "";
    
        $expectedRedirectUrl = "https://testDomain.com/?c=testCustomer&e=e1" .
                "&ver=".QueueIT\KnownUserV3\SDK\UserInQueueService::getSDKVersion()
                . "&cver=10"
                . "&man=" . rawurlencode($eventConfig->actionName)
                . "&l=testlayout";
    
        $testObject = new QueueIT\KnownUserV3\SDK\UserInQueueService($cookieProviderMock);
        $result = $testObject->validateQueueRequest(null, $token, $eventConfig, "testCustomer", $key);
        $this->assertFalse($cookieProviderMock->expectCallAny('store'));
    
        $this->assertTrue($result->doRedirect());
        $this->assertTrue($result->eventId == 'e1');
        $this->assertTrue($result->queueId == null);
        $this->assertTrue(strtolower($result->redirectUrl) == strtolower($expectedRedirectUrl));
        $this->assertTrue(strtolower($result->actionName) == strtolower( $eventConfig->actionName));
        $this->assertTrue($cookieProviderMock->expectCallAny('cancelQueueCookie'));
    }

    public function test_validateQueueRequest_NoCookie_InValidToken() {
        $key = "4e1db821-a825-49da-acd0-5d376f2068db";
        $eventConfig = new QueueIT\KnownUserV3\SDK\QueueEventConfig();
        $eventConfig->eventId = "e1";
        $eventConfig->queueDomain = "testDomain.com";
        $eventConfig->cookieValidityMinute = 10;
        $eventConfig->extendCookieValidity = true;
        $eventConfig->version = 11;
        $eventConfig->actionName = "QueueAction";
        $eventConfig->culture = 'en-US';
        $eventConfig->layoutName = 'testlayout';
        
        $url = "http://test.test.com?b=h";
        $cookieProviderMock = new UserInQueueStateRepositoryMockClass ();
        array_push($cookieProviderMock->arrayReturns['getState'], new QueueIT\KnownUserV3\SDK\StateInfo(false, false, null, null, null));
        $token = "";
    
        $testObject = new QueueIT\KnownUserV3\SDK\UserInQueueService($cookieProviderMock);
        $result = $testObject->validateQueueRequest($url, "ts_sasa~cv_adsasa~ce_falwwwse~q_944c1f44-60dd-4e37-aabc-f3e4bb1c8895", $eventConfig, "testCustomer", $key);
        $this->assertFalse($cookieProviderMock->expectCallAny('store'));
    
        $this->assertTrue($result->doRedirect());
        $this->assertTrue($result->eventId == 'e1');
        $this->assertTrue($result->queueId == null);	
        $this->assertTrue(strpos($result->redirectUrl, "https://testDomain.com/error/hash/?c=testCustomer&e=e1") == 0);
        $this->assertTrue(strtolower($result->actionName) == strtolower('QueueAction'));
    }
    
    public function test_validateQueueRequest_InvalidCookie_InvalidToken_CancelCookie() {
        $key = "4e1db821-a825-49da-acd0-5d376f2068db";
        $eventConfig = new QueueIT\KnownUserV3\SDK\QueueEventConfig();
        $eventConfig->eventId = "e1";
        $eventConfig->queueDomain = "testDomain.com";
        $eventConfig->cookieValidityMinute = 10;
        $eventConfig->extendCookieValidity = true;
        $eventConfig->version = 11;
        $eventConfig->actionName = "QueueAction";
        $eventConfig->culture = 'en-US';
        $eventConfig->layoutName = 'testlayout';
        
        $url = "http://test.test.com?b=h";
        $cookieProviderMock = new UserInQueueStateRepositoryMockClass ();
        array_push($cookieProviderMock->arrayReturns['getState'], new QueueIT\KnownUserV3\SDK\StateInfo(true, false, null, null, null));
        $token = "";
    
        $testObject = new QueueIT\KnownUserV3\SDK\UserInQueueService($cookieProviderMock);
        $result = $testObject->validateQueueRequest($url, "ts_sasa~cv_adsasa~ce_falwwwse~q_944c1f44-60dd-4e37-aabc-f3e4bb1c8895", $eventConfig, "testCustomer", $key);
        $this->assertFalse($cookieProviderMock->expectCallAny('store'));
    
        $this->assertTrue($result->doRedirect());
        $this->assertTrue($result->eventId == 'e1');
        $this->assertTrue($result->queueId == null);	
        $this->assertTrue(strpos($result->redirectUrl, "https://testDomain.com/error/hash/?c=testCustomer&e=e1") == 0);
        $this->assertTrue(strtolower($result->actionName) == strtolower('QueueAction'));
        $this->assertTrue($cookieProviderMock->expectCallAny('cancelQueueCookie'));
    }

    public function test_validateCancelRequest() {
        $key = "4e1db821-a825-49da-acd0-5d376f2068db";
        $eventConfig = new QueueIT\KnownUserV3\SDK\CancelEventConfig();
        $eventConfig->eventId = "e1";
        $eventConfig->queueDomain = "testDomain.com";
        $eventConfig->cookieDomain = "testdomain";
        $eventConfig->version = 10;
        $eventConfig->actionName = "QueueAction";      
        $url = "http://test.test.com?b=h";
        $cookieProviderMock = new UserInQueueStateRepositoryMockClass ();
        array_push($cookieProviderMock->arrayReturns['getState'], new QueueIT\KnownUserV3\SDK\StateInfo(true, true, "queueid", 3, "idle"));
        $token = "";
    
        $testObject = new QueueIT\KnownUserV3\SDK\UserInQueueService($cookieProviderMock);
        $expectedUrl = "https://testDomain.com/cancel/testCustomer/e1/queueid"
         ."?c=testCustomer"
         ."&e=e1"
         ."&ver=".QueueIT\KnownUserV3\SDK\UserInQueueService::getSDKVersion()
         ."&cver=10"
         ."&man=" . $eventConfig->actionName 
         ."&r=http%3A%2F%2Ftest.test.com%3Fb%3Dh";
        $result = $testObject->validateCancelRequest($url, $eventConfig, "testCustomer", $key);
        $this->assertFalse($cookieProviderMock->expectCallAny('store'));
    
        $this->assertTrue($result->doRedirect());
        $this->assertTrue($result->eventId == 'e1');
        $this->assertTrue($result->queueId == "queueid");
    
        $this->assertTrue($result->redirectUrl == $expectedUrl);
        $this->assertTrue(strtolower($result->actionName) == strtolower($eventConfig->actionName));
    }

    public function test_getIgnoreActionResult() {
        $testObject = new QueueIT\KnownUserV3\SDK\UserInQueueService(new UserInQueueStateRepositoryMockClass ());
        $actionName = "IgnoreAction";
        $result = $testObject->getIgnoreActionResult($actionName);
    
        $this->assertFalse($result->doRedirect());
        $this->assertTrue($result->eventId == NULL);
        $this->assertTrue($result->queueId == NULL);
        $this->assertTrue($result->redirectUrl == NULL);
        $this->assertTrue($result->actionType == "Ignore");
        $this->assertTrue($result->actionName == "IgnoreAction");
    }

    public function generateHash($eventId, $queueId, $timestamp, $extendableCookie, $cookieValidityMinutes, $redirectType, $secretKey) {
        $token = 'e_' . $eventId . '~ts_' . $timestamp . '~ce_' . $extendableCookie. '~q_'. $queueId;
        if (isset($cookieValidityMinutes))
            $token = $token . '~cv_' . $cookieValidityMinutes;
        if (isset($redirectType))
            $token = $token . '~rt_' . $redirectType;		
        return $token . '~h_' . hash_hmac('sha256', $token, $secretKey);
    }
}