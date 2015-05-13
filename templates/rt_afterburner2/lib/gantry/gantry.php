<?php
/**
 * @version   $Id: gantry.php 5318 2012-11-20 23:07:37Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('JPATH_BASE') or die();
global $gantry;

$gantry_lib_path = JPATH_SITE . '/libraries/gantry/gantry.php';
if (!file_exists($gantry_lib_path)) {
    echo JText::_('GANTRY_BOOTSTRAP_CANT_FIND_LIBRARY');
    die;
}
$backtrace = debug_backtrace();
$gantry_calling_file = $backtrace[0]['file'];
include($gantry_lib_path);