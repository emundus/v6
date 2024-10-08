<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit64bcb066febeeee4aad48bd38e639b10
{
    public static $prefixLengthsPsr4 = array (
        'm' => 
        array (
            'mediaburst\\ClockworkSMS\\' => 24,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'mediaburst\\ClockworkSMS\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit64bcb066febeeee4aad48bd38e639b10::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit64bcb066febeeee4aad48bd38e639b10::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
