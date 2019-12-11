<?php

/**
 * @internal
 */

if(!function_exists('onedrive_api_php_client_autoload')){
    function onedrive_api_php_client_autoload($className)
    {
        $classPath = explode('_', $className);
        if ($classPath[0] != 'OneDrive') {
            return;
        }
        // Drop 'onedrive', and maximum class file path depth in this project is 3.
        $classPath = array_slice($classPath, 1, 2);

        $filePath = dirname(__FILE__) . '/' . implode('/', $classPath) . '.php';
        if (file_exists($filePath)) {
            require_once($filePath);
        }
    }

    spl_autoload_register('onedrive_api_php_client_autoload');
}