<?php

/**
 * @copyright     Copyright (c) 2009-2021 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
class WFColumnsPluginConfig
{
    public static function getConfig(&$settings)
    {
        $wf = WFApplication::getInstance();

        $settings['columns_framework']  = $wf->getParam('columns.framework', '', '');
        $settings['columns_stack']      = $wf->getParam('columns.stack', 'medium', 'medium');
        $settings['columns_gap']        = $wf->getParam('columns.gap', 'medium', 'medium');
        $settings['columns_layout']     = $wf->getParam('columns.layout', '', '');
    }
}