<?php


namespace Composer\Autoload;

class ComposerStaticInitfc1d5d3d63475f61da80cfc0a550720c
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Sample\\' => 7,
        ),
        'P' => 
        array (
            'PayPalHttp\\' => 11,
            'PayPalCheckoutSdk\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Sample\\' => 
        array (
            0 => __DIR__ . '/..' . '/paypal/paypal-checkout-sdk/samples',
        ),
        'PayPalHttp\\' => 
        array (
            0 => __DIR__ . '/..' . '/paypal/paypalhttp/lib/PayPalHttp',
        ),
        'PayPalCheckoutSdk\\' => 
        array (
            0 => __DIR__ . '/..' . '/paypal/paypal-checkout-sdk/lib/PayPalCheckoutSdk',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitfc1d5d3d63475f61da80cfc0a550720c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitfc1d5d3d63475f61da80cfc0a550720c::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitfc1d5d3d63475f61da80cfc0a550720c::$classMap;

        }, null, ClassLoader::class);
    }
}
