<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit98c95e5ba58866e8940f02b131cb7987
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInit98c95e5ba58866e8940f02b131cb7987', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit98c95e5ba58866e8940f02b131cb7987', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit98c95e5ba58866e8940f02b131cb7987::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
