<?php
/**
* @version   $Id: default_articles.php 26100 2015-01-27 14:16:12Z james $
* @author    RocketTheme http://www.rockettheme.com
* @copyright Copyright (C) 2007 - 2015 RocketTheme, LLC
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('_JEXEC') or die;
$gantry_lib_path = JPATH_SITE . '/libraries/gantry/gantry.php';
if (!file_exists($gantry_lib_path)) {
    echo 'This template requires the Gantry Template Framework.  Please download and install from <a href="http://www.gantry-framework.org/download">http://www.gantry-framework.org/download</a>';
    die;
}
include(JPATH_LIBRARIES.'/gantry/gantry.php');
$gantry->init();
include JPATH_SITE.'/templates/'.$gantry->getCurrentTemplate().'/html/base_override.php';