<?php

/**
 * @copyright     Copyright (c) 2009-2021 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
class WFMediamanagerPluginConfig
{
    public static function getConfig(&$settings)
    {
        $wf = WFApplication::getInstance();

        require_once __DIR__ . '/mediamanager.php';

        $plugin = new WFMediaManagerPlugin();

        $config = array(
            'quickmedia' => array(),
        );

        if ($plugin->getParam('aggregator.youtube.enable', 1) || $plugin->getParam('aggregator.vimeo.enable', 1)) {
            $settings['invalid_elements'] = array_diff($settings['invalid_elements'], array('iframe'));

            $media_iframes = (int) $wf->getParam('media.iframes', 0);

            // iframes_allow_supported as minimum restriction
            if (!$media_iframes || $media_iframes == 2) {
                $settings['iframes_allow_supported'] = true;
            }
        }

        if (!$plugin->getParam('aggregator.youtube.enable', 1)) {
            $config['quickmedia']['youtube'] = false;
        }

        if (!$plugin->getParam('aggregator.vimeo.enable', 1)) {
            $config['quickmedia']['vimeo'] = false;
        }

        // get the list of filetypes supported
        $filetypes = array_values($plugin->getFileTypes());

        // only allow a limited set that are support by the <video> and <audio> tags
        $filetypes_set = array_intersect($filetypes, array('mp3', 'oga', 'm4a', 'mp4', 'm4v', 'ogg', 'webm', 'ogv'));

        if ($plugin->getParam('inline_upload', 1) && $plugin->getParam('upload', 1)) {
            $config['upload'] = array(
                'max_size' => $plugin->getParam('max_size', 1024),
                'filetypes' => array_values($filetypes_set),
                'inline' => true,
            );
        }

        if ($plugin->getParam('quickmedia', 1) == 0) {
            $config['quickmedia'] = false;
        }

        if ($plugin->getParam('basic_dialog', 0) == 1) {
            $config['basic_dialog'] = true;

            if ($plugin->getParam('basic_dialog_filebrowser', 1) == 1) {
                $config['basic_dialog_filebrowser'] = true;
                $config['filetypes'] = array_values($filetypes_set);
            }

            $config['attributes'] = $plugin->getDefaultAttributes();
        }

        $custom_embed = JFactory::getApplication()->triggerEvent('onWfGetCustomEmbedData');

        if (!empty($custom_embed)) {
            $config['custom_embed'] = array();

            foreach ($custom_embed as $item) {
                foreach ($item as $key => $values) {
                    $config['custom_embed'][$key] = $values;
                }
            }
        }

        $settings['mediamanager'] = $config;

        // ensure the media plugin is added and invalid elements updated
        if (!in_array('media', $settings['plugins'])) {
            $settings['plugins'][] = 'media';
            $settings['invalid_elements'] = array_diff($settings['invalid_elements'], array('video', 'audio', 'source', 'object', 'embed', 'param'));
        }
    }
}
