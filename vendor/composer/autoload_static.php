<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0b992a5c5d3a8633e6d653680842ef4e
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'Combodo\\iTop\\Extension\\TawkIntegration\\' => 39,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Combodo\\iTop\\Extension\\TawkIntegration\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Combodo\\iTop\\Extension\\TawkIntegration\\Extension\\ConsoleUIExtension' => __DIR__ . '/../..' . '/src/Hook/ConsoleUIExtension.php',
        'Combodo\\iTop\\Extension\\TawkIntegration\\Extension\\PortalUIExtension' => __DIR__ . '/../..' . '/src/Hook/PortalUIExtension.php',
        'Combodo\\iTop\\Extension\\TawkIntegration\\Helper\\ConfigHelper' => __DIR__ . '/../..' . '/src/Helper/ConfigHelper.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit0b992a5c5d3a8633e6d653680842ef4e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit0b992a5c5d3a8633e6d653680842ef4e::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit0b992a5c5d3a8633e6d653680842ef4e::$classMap;

        }, null, ClassLoader::class);
    }
}
