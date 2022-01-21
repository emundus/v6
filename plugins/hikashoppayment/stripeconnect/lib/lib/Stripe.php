<?php

namespace Stripe;

class Stripe
{
    public static $apiKey;

    public static $clientId;

    public static $apiBase = 'https://api.stripe.com';

    public static $connectBase = 'https://connect.stripe.com';

    public static $apiUploadBase = 'https://files.stripe.com';

    public static $apiVersion = null;

    public static $accountId = null;

    public static $caBundlePath = null;

    public static $verifySslCerts = true;

    public static $appInfo = null;

    public static $logger = null;

    public static $maxNetworkRetries = 0;

    public static $enableTelemetry = false;

    private static $maxNetworkRetryDelay = 2.0;

    private static $initialNetworkRetryDelay = 0.5;

    const VERSION = '6.32.0';

    public static function getApiKey()
    {
        return self::$apiKey;
    }

    public static function getClientId()
    {
        return self::$clientId;
    }

    public static function getLogger()
    {
        if (self::$logger == null) {
            return new Util\DefaultLogger();
        }
        return self::$logger;
    }

    public static function setLogger($logger)
    {
        self::$logger = $logger;
    }

    public static function setApiKey($apiKey)
    {
        self::$apiKey = $apiKey;
    }

    public static function setClientId($clientId)
    {
        self::$clientId = $clientId;
    }

    public static function getApiVersion()
    {
        return self::$apiVersion;
    }

    public static function setApiVersion($apiVersion)
    {
        self::$apiVersion = $apiVersion;
    }

    private static function getDefaultCABundlePath()
    {
        return realpath(dirname(__FILE__) . '/../data/ca-certificates.crt');
    }

    public static function getCABundlePath()
    {
        return self::$caBundlePath ?: self::getDefaultCABundlePath();
    }

    public static function setCABundlePath($caBundlePath)
    {
        self::$caBundlePath = $caBundlePath;
    }

    public static function getVerifySslCerts()
    {
        return self::$verifySslCerts;
    }

    public static function setVerifySslCerts($verify)
    {
        self::$verifySslCerts = $verify;
    }

    public static function getAccountId()
    {
        return self::$accountId;
    }

    public static function setAccountId($accountId)
    {
        self::$accountId = $accountId;
    }

    public static function getAppInfo()
    {
        return self::$appInfo;
    }

    public static function setAppInfo($appName, $appVersion = null, $appUrl = null, $appPartnerId = null)
    {
        self::$appInfo = self::$appInfo ?: [];
        self::$appInfo['name'] = $appName;
        self::$appInfo['partner_id'] = $appPartnerId;
        self::$appInfo['url'] = $appUrl;
        self::$appInfo['version'] = $appVersion;
    }

    public static function getMaxNetworkRetries()
    {
        return self::$maxNetworkRetries;
    }

    public static function setMaxNetworkRetries($maxNetworkRetries)
    {
        self::$maxNetworkRetries = $maxNetworkRetries;
    }

    public static function getMaxNetworkRetryDelay()
    {
        return self::$maxNetworkRetryDelay;
    }

    public static function getInitialNetworkRetryDelay()
    {
        return self::$initialNetworkRetryDelay;
    }

    public static function getEnableTelemetry()
    {
        return self::$enableTelemetry;
    }

    public static function setEnableTelemetry($enableTelemetry)
    {
        self::$enableTelemetry = $enableTelemetry;
    }
}
