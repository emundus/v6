<?php
namespace Payplug\Core;
use Payplug\Exception;

class Config
{
    const LIBRARY_VERSION = '2.6.0';

    const API_VERSION = '2019-06-14';

    const PHP_MIN_VERSION = '5.3.0';

    public static $REQUIRED_FUNCTIONS = array(
        'json_decode'   => 'php5-json',
        'json_encode'   => 'php5-json',
        'curl_version'  => 'php5-curl'
    );
}

if (version_compare(phpversion(), Config::PHP_MIN_VERSION, "<")) {
    throw new \RuntimeException('This library requires PHP ' . Config::PHP_MIN_VERSION . ' or newer.');
}

foreach(Config::$REQUIRED_FUNCTIONS as $key => $value) {
    if (!function_exists($key)) {
        throw new Exception\DependencyException('This library requires ' . $value . '.');
    }
}

if (!defined('CURL_SSLVERSION_DEFAULT')) {
    define('CURL_SSLVERSION_DEFAULT', 0);
}
if (!defined('CURL_SSLVERSION_TLSv1')) {
    define('CURL_SSLVERSION_TLSv1', 1);
}
