<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') || die;

require_once(dirname(__FILE__) . '/helper.php');
$helper = new ModDropfilesLatestHelper();
$helper->loadResource();

if (!isset($params)) {
    $params = null;
}

//order by
$fType  = $params->get('file_type', 'uploaded');
$oderby = 'created_time';

if ($fType === 'updated') {
    $oderby = 'modified_time';
} elseif ($fType === 'downloaded') {
    $oderby = 'hits';
} elseif ($fType === 'size') {
    $oderby = 'size';
}
if ($params->get('ordering', 'desc') === 'random') {
    $oderby = ' RAND() ';
}

$filters               = array();
$filters['catid']      = null;
$filters['fCount']     = (int) $params->get('file_count', 10);
$filters['fOrderType'] = $oderby;
$filters['fOrdering']  = $params->get('file_type', 'uploaded');
$filters['fDer']       = $params->get('ordering', 'desc');


$color      = $params->get('download_priview_color', '#444444');
$mode_style = '.mod_dropfiles_latest i.zmdi{ color:' . $color . '!important;}';

$result = array();
if (!class_exists('DropfilesModelFrontsearch')) {
    JLoader::import('frontsearch', JPATH_BASE . '/components/com_dropfiles/models');
}
$model      = new DropfilesModelFrontsearch();
$categories = array_unique($params->get('categories', array()));
$result     = $model->getLatestFiles($categories, $filters);
if ($params->get('ordering', 'desc') === 'random') {
    shuffle($result);
}
require(JModuleHelper::getLayoutPath('mod_dropfiles_latest', $params->get('layout', 'default')));
