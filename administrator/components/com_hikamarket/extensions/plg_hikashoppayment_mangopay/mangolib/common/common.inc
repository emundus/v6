<?php
/**
 * Function to auto include files with entities and types
 * @param String $className
 */
function mangoPay_Autoload($className){
    $fileName = substr(strrchr($className, '\\'), 1);
    
    // include entities files
    if (mangoPay_AutoloadCheckAndInclude($fileName, 'entities'))
        return;
    
    // include types files
    if (mangoPay_AutoloadCheckAndInclude($fileName, 'types'))
        return;
    
    // include tools files
    if (mangoPay_AutoloadCheckAndInclude($fileName, 'tools'))
        return;
    
    // include exceptions files
    if (mangoPay_AutoloadCheckAndInclude($fileName, 'types/exceptions'))
        return;
}

function mangoPay_AutoloadCheckAndInclude($fileName, $module) {
    
    $path = __DIR__ . '/../' . $module . '/' . lcfirst($fileName) . '.inc';
    if (file_exists($path)){
        include_once $path;
        return true;
    }
    
    return false;
}

// register auto load function
spl_autoload_register('mangoPay_Autoload');

// include file with enums
require_once __DIR__ . '/../tools/enums.inc';
