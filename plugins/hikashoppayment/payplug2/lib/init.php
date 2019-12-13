<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.2.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2019 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
spl_autoload_register(function ($class) {
    if (strpos($class, 'Payplug') !== 0) {
        return;
    }

    $file = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

    if (file_exists($file)) {
        require($file);
    }
});
