<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf9083660f9fccca7aee78e0e5f0854c8
{
    public static $prefixLengthsPsr4 = array (
        'B' => 
        array (
            'Box\\Spout\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Box\\Spout\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/Spout',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitf9083660f9fccca7aee78e0e5f0854c8::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitf9083660f9fccca7aee78e0e5f0854c8::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}