<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit47a758d4959e4d82591bf48fb8d1137f
{
    public static $files = array (
        '89ff252b349d4d088742a09c25f5dd74' => __DIR__ . '/..' . '/yahnis-elsts/plugin-update-checker/plugin-update-checker.php',
        'cbd6bada88b6bca5d1b8b1b5733f514e' => __DIR__ . '/..' . '/wp-content-framework/core/autoload.php',
    );

    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'MatthiasMullie\\PathConverter\\' => 29,
            'MatthiasMullie\\Minify\\' => 22,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'MatthiasMullie\\PathConverter\\' => 
        array (
            0 => __DIR__ . '/..' . '/matthiasmullie/path-converter/src',
        ),
        'MatthiasMullie\\Minify\\' => 
        array (
            0 => __DIR__ . '/..' . '/matthiasmullie/minify/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'I' => 
        array (
            'Igo' => 
            array (
                0 => __DIR__ . '/..' . '/logue/igo-php/lib',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit47a758d4959e4d82591bf48fb8d1137f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit47a758d4959e4d82591bf48fb8d1137f::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit47a758d4959e4d82591bf48fb8d1137f::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
