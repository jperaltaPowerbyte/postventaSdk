<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf4dd1bd09e07bb889d64b1db86576920
{
    public static $classMap = array (
        'InesPostventa\\InesPostventa' => __DIR__ . '/../..' . '/InesPostventa.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInitf4dd1bd09e07bb889d64b1db86576920::$classMap;

        }, null, ClassLoader::class);
    }
}