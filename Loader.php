<?php
/**
 * Description:
 * User: administere
 * Date: 2017/6/15
 * Time: 10:30
 */

namespace Guandaxia;

class Loader
{
    public static function autoLoader($class)
    {
        $class = str_replace("\\", "/", $class);
        $class = str_replace("Guandaxia/", __DIR__."/", $class);
        $file = $class . ".php";
        require $file;
    }

    public static function register($autoload = "")
    {
        spl_autoload_register($autoload ?: "guandaxia\\Loader::autoLoader", true, true);

        // Composer自动加载支持
        if (is_dir(__DIR__ . '/vendor')) {
            require_once __DIR__."/vendor/autoload.php";
        }
    }
}