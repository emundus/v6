<?php
/**
 * @version   $Id: overrides.php 24927 2014-12-30 18:53:22Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2014 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 */
defined('GANTRY_VERSION') or die();
//gantry_import('core.gantryjson');
gantry_import('core.config.gantryformnaminghelper');

/** @var $gantry Gantry */
        global $gantry;

$action = JFactory::getApplication()->input->getWord('action');
//if (!current_user_can('edit_theme_options')) die('-1');

/** @var $namehelper GantryFormNamingHelper */
$namehelper = GantryFormNamingHelper::getInstance();
if ($action == 'get_base_values') {
    $passed_array = array();
    foreach ($gantry->_working_params as $param) {
        if ($param['name'] == 'master') {
            continue;
        }
        $param_name                = $namehelper->get_field_id($param['name']);
        $passed_array[$param_name] = $param['value'];
        $passed_array[$param_name] = htmlentities($passed_array[$param_name], ENT_QUOTES);
    }
    $outdata = json_encode($passed_array);
    echo $outdata;
} else if ($action == 'get_default_values') {
    $passed_array = array();
    foreach ($gantry->_working_params as $param) {
        $param_name                = $namehelper->get_field_id($param['name']);
        $passed_array[$param_name] = $param['default'];
        $passed_array[$param_name] = htmlentities($passed_array[$param_name], ENT_QUOTES);
    }
    $outdata = json_encode($passed_array);
    echo $outdata;
} else {
    return "error";
}

die;
