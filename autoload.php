<?php
defined('ABSPATH') or die;

spl_autoload_register(function ($class) {

    $namespace = 'DebugLogConfigTool';
    if (!str_starts_with($class, $namespace)) {
        
        return;
    }
    $className = str_replace(
        array('\\', $namespace, strtolower($namespace)),
        array('/', 'app',''),
        $class
    );

    $basePath = plugin_dir_path(__FILE__);

    $file = $basePath.trim($className, '/').'.php';

    if (is_readable($file)) {
        include $file;
    }
});


