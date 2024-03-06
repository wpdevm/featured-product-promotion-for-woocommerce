<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc4b128bbd216db31251aefb8ebc9875f
{
    public static $prefixLengthsPsr4 = array (
        'V' => 
        array (
            'VladyslavParshyn\\FeaturedProductPromotion\\' => 42,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'VladyslavParshyn\\FeaturedProductPromotion\\' => 
        array (
            0 => __DIR__ . '/../..' . '/',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitc4b128bbd216db31251aefb8ebc9875f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitc4b128bbd216db31251aefb8ebc9875f::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitc4b128bbd216db31251aefb8ebc9875f::$classMap;

        }, null, ClassLoader::class);
    }
}