<?php
namespace Payplug;

class Payplug
{
    private static $_defaultConfiguration = null;

    private $_token;

    public function __construct($token)
    {
        if (!is_string($token)) {
            throw new Exception\ConfigurationException('Expected string values for token.');
        }
        $this->_token = $token;
    }

    public static function setSecretKey($token)
    {
        if (!is_string($token)) {
            throw new Exception\ConfigurationException('Expected string values for the token.');
        }

        $clientConfiguration = new Payplug($token);

        self::setDefaultConfiguration($clientConfiguration);

        return $clientConfiguration;
    }

    public function getToken()
    {
        return $this->_token;
    }

    public static function getDefaultConfiguration()
    {
        if (self::$_defaultConfiguration === null) {
                throw new Exception\ConfigurationNotSetException('Unable to find an authentication.');
        }

        return self::$_defaultConfiguration;
    }

    public static function setDefaultConfiguration($defaultConfiguration)
    {
        self::$_defaultConfiguration = $defaultConfiguration;
    }
}
