<?php

/**
 * @copyright     Copyright (c) 2009-2021 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
class WFSourcePluginConfig
{
    public static function getConfig(&$settings)
    {
        $wf = WFApplication::getInstance();

        $settings['source_highlight'] = $wf->getParam('source.highlight', 1, 1, 'boolean');
        $settings['source_linenumbers'] = $wf->getParam('source.numbers', 1, 1, 'boolean');
        $settings['source_wrap'] = $wf->getParam('source.wrap', 1, 1, 'boolean');
        $settings['source_format'] = $wf->getParam('source.format', 1, 1, 'boolean');
        $settings['source_tag_closing'] = $wf->getParam('source.tag_closing', 1, 1, 'boolean');
        //$settings['source_selection_match'] = $wf->getParam('source.selection_match', 1, 1, 'boolean');

        $settings['source_font_size'] = $wf->getParam('source.font_size', '', '');
        $settings['source_theme'] = $wf->getParam('source.theme', 'codemirror', 'codemirror');

        $settings['source_validate_content'] = $wf->getParam('source.validate_content', 1, 1, 'boolean');
    }
}
