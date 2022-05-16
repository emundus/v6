<?php
/**
 * Copyright © Lyra Network.
 * This file is part of PayZen plugin for HikaShop. See COPYING.md for license details.
 *
 * @author    Lyra Network (https://www.lyra.com/)
 * @copyright Lyra Network
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL v3)
 */

defined('_JEXEC') or die('Restricted access');

if (! class_exists('com_payzenInstallerScript')) {
    class com_payzenInstallerScript
    {
        static $plugin_features = array(
            'qualif' => false,
            'prodfaq' => true,
            'restrictmulti' => false,
            'shatwo' => true,

            'multi' => true
        );

        function install()
        {
            JInstaller::getInstance()->install(realpath(dirname(__FILE__)) . DS . 'plg_hikashoppayment_payzen');

            if (self::$plugin_features['multi']) {
                JInstaller::getInstance()->install(realpath(dirname(__FILE__)) . DS . 'plg_hikashoppayment_payzenmulti');
            }

            JInstaller::getInstance()->install(realpath(dirname(__FILE__)));
        }
    }
}

// Joomla 1.5.
if (function_exists('com_install')) {
    function com_install()
    {
        $installClass = new com_payzenInstallerScript();
        $installClass->install();
    }
}
