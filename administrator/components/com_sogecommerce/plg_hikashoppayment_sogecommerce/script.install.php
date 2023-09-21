<?php
/**
 * Copyright © Lyra Network.
 * This file is part of Sogecommerce plugin for HikaShop. See COPYING.md for license details.
 *
 * @author    Lyra Network (https://www.lyra.com/)
 * @copyright Lyra Network
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL v3)
 */

defined('_JEXEC') or die('Restricted access');

class plghikashoppaymentsogecommerceInstallerScript
{
    /**
     * Called after any type of action.
     *
     * @param string $route Which action is happening (install|uninstall|discover_install|update)
     * @param JAdapterInstance $adapter The object responsible for running this script
     *
     * @return boolean True on success
     */
    function postflight($route, $adapter)
    {
        if ($route != 'install' && $route != 'update' && $route != 'discover_install') {
            return;
        }

        // Get the client info.
        jimport('joomla.application.helper');
        $client = JApplicationHelper::getClientInfo(- 1);

        // Here we set the folder we are going to rename manifest from.
        if ($client) {
            $path = $adapter->getParent()->getPath('extension_' . $client->name);
        } else {
            $path = $adapter->getParent()->getPath('extension_root');
        }

        JFile::move('sogecommerce_j3.xml', 'sogecommerce.xml', $path);
    }

    function preflight($type, $adapter)
    {
        if ($type === 'uninstall') {
            require_once rtrim(JPATH_ADMINISTRATOR, DS) . DS . 'components' . DS . 'com_hikashop' . DS . 'helpers' . DS .
                 'helper.php';
            jimport('joomla.filesystem.folder');
            jimport('joomla.filesystem.file');

            $targetFolder = HIKASHOP_IMAGES . 'payment';

            if (JFile::exists($targetFolder . DS . 'sogecommerce_cards.png')) {
                JFile::delete($targetFolder . DS . 'sogecommerce_cards.png');
            }

            if (JFile::exists($targetFolder . DS . 'sogecommerce.png')) {
                JFile::delete($targetFolder . DS . 'sogecommerce.png');
            }
        }
    }
}
