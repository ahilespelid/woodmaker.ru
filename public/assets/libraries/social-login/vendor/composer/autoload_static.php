<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit561ea6bf7d972f975a4f49f4d5a61084
{
    public static $prefixLengthsPsr4 = array (
        'H' => 
        array (
            'Hybridauth\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Hybridauth\\' => 
        array (
            0 => __DIR__ . '/..' . '/hybridauth/hybridauth/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit561ea6bf7d972f975a4f49f4d5a61084::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit561ea6bf7d972f975a4f49f4d5a61084::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit561ea6bf7d972f975a4f49f4d5a61084::$classMap;

        }, null, ClassLoader::class);
    }
}
