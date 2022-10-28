<?php
/**
 * Dropfiles
 *
 * @package    Joomla.Site
 * @subpackage Templates.protostar
 *
 * @copyright Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') || die;

/**
 * This is a file to add template specific chrome to module rendering.  To use it you would
 * set the style attribute for the given module(s) include in your template to use the style
 * for each given modChrome function.
 *
 * Eg. To render a module mod_test in the submenu style, you would use the following include:
 * <jdoc:include type="module" name="test" style="submenu" />
 *
 * This gives template designers ultimate control over how modules are rendered.
 *
 * NOTICE: All chrome wrapping methods should be named: modChrome_{STYLE} and take the same
 * two arguments.
 *
 * @param object $module  Module
 * @param object $params  Params
 * @param array  $attribs Attributes
 *
 * @return void
 */
function modChrome_no($module, &$params, &$attribs)
{
    if ($module->content) {
        echo $module->content;
    }
}

/**
 * Mod Chrome
 *
 * @param object $module  Module
 * @param object $params  Params
 * @param array  $attribs Attributes
 *
 * @return void
 * @since  version
 */
function modChrome_well($module, &$params, &$attribs)
{
    $moduleTag = $params->get('module_tag', 'div');
    $bootstrapSize = (int)$params->get('bootstrap_size', 0);
    $moduleClass = $bootstrapSize !== 0 ? ' span' . $bootstrapSize : '';
    $headerTag = htmlspecialchars($params->get('header_tag', 'h3'), ENT_QUOTES, 'UTF-8');
    $headerClass = htmlspecialchars($params->get('header_class', 'page-header'), ENT_QUOTES, 'UTF-8');

    if ($module->content) {
        echo '<' . $moduleTag . ' class="well ' .
            htmlspecialchars($params->get('moduleclass_sfx'), ENT_QUOTES, 'UTF-8') . $moduleClass . '">';

        if ($module->showtitle) {
            echo '<' . $headerTag . ' class="' . $headerClass . '">' . $module->title . '</' . $headerTag . '>';
        }

        echo $module->content;
        echo '</' . $moduleTag . '>';
    }
}
