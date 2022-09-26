<?php

/**
 * @copyright     Copyright (c) 2009-2021 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
class WFTemplateManagerPluginConfig
{
    public static function getConfig(&$settings)
    {
        $wf = WFApplication::getInstance();

        $config = array();

        $config['selected_content_classes'] = $wf->getParam('templatemanager.selected_content_classes', '');
        $config['cdate_classes'] = $wf->getParam('templatemanager.cdate_classes', 'cdate creationdate', 'cdate creationdate');
        $config['mdate_classes'] = $wf->getParam('templatemanager.mdate_classes', 'mdate modifieddate', 'mdate modifieddate');
        $config['cdate_format'] = $wf->getParam('templatemanager.cdate_format', '%m/%d/%Y : %H:%M:%S', '%m/%d/%Y : %H:%M:%S');
        $config['mdate_format'] = $wf->getParam('templatemanager.mdate_format', '%m/%d/%Y : %H:%M:%S', '%m/%d/%Y : %H:%M:%S');

        $config['content_url'] = $wf->getParam('templatemanager.content_url', '');

        require_once __DIR__ . '/templatemanager.php';

        $plugin = new WFTemplateManagerPlugin();

        // associative array of template items
        $list = array();

        if ($wf->getParam('templatemanager.template_list', 1)) {
            $templates = $wf->getParam('templatemanager.templates', array());

            if (is_string($templates)) {
                $templates = json_decode(htmlspecialchars_decode($templates), true);
            }

            if (!empty($templates)) {
                foreach ($templates as $template) {
                    $value      = "";
                    $thumbnail  = "";

                    extract($template);

                    if (empty($url) && empty($html)) {
                        continue;
                    }

                    if (!empty($url)) {
                        if (preg_match("#\.(htm|html|txt)$#", $url) && strpos('://', $url) === false) {
                            $url = trim($url, '/');
                            
                            $file = JPATH_SITE . '/' . $url;
                            
                            if (is_file($file)) {
                                $value = JURI::root() . $url;

                                $filename = WFUtility::stripExtension($url);

                                if (!$thumbnail && is_file(JPATH_SITE . '/' . $filename . '.jpg')) {
                                    $thumbnail = $filename . '.jpg';
                                }
                            }
                        }
                    } else if (!empty($html)) {
                        $value = htmlspecialchars_decode($html);
                    }

                    if ($thumbnail) {
                        $thumbnail = JURI::root(true) . '/' . $thumbnail;
                    }

                    $list[$name] = array(
                        'data'  => $value,
                        'image' => $thumbnail
                    );
                }
            }

            // a default list of template files
            if (empty($list)) {
                $list = $plugin->getTemplateList();
            }
        }

        if ($plugin->getParam('inline_upload', 1)) {
            $config['upload'] = array(
                'max_size' => $plugin->getParam('max_size', 1024),
                'filetypes' => $plugin->getFileTypes(),
                'inline' => true
            );
        }

        if (!empty($list)) {
            $config['templates'] = $list;
        }

        if ($plugin->getParam('text_editor', 0)) {
            $config['text_editor'] = 1;
        }

        $settings['templatemanager'] = $config;
    }
}
