<?php

/**
 * @copyright     Copyright (c) 2009-2021 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
class WFImgManagerExtPluginConfig
{
    public static function getConfig(&$settings)
    {
        require_once __DIR__ . '/imgmanager_ext.php';

        $plugin = new WFImgManagerExtPlugin();

        $config = $plugin->getImageProperties();

        $config['filetypes'] = $plugin->getFileTypes();

        if ($plugin->getParam('inline_upload', 1) && $plugin->getParam('upload', 1)) {
            
            $config['upload'] = array(
                'max_size' => $plugin->getParam('max_size', 1024),
                'filetypes' => $plugin->getFileTypes(),
                'inline' => true
            );
        }

        $config['always_include_dimensions'] = (bool) $plugin->getParam('always_include_dimensions', 1);

        $settings['imgmanager_ext'] = $config;
    }
}
