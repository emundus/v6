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
$helper = new ModDropfilesSearchHelper();
$helper->loadResource();
$categories = $helper->getCategories();

$comParams = JComponentHelper::getParams('com_dropfiles');
$catTags   = json_decode($comParams->get('cat_tags'), true);
JLoader::register('DropfilesComponentHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/component.php');
$allTagsFiles = DropfilesComponentHelper::getAllTagsFiles($catTags);

if (!isset($params)) {
    $params = null;
}

$aTags = $params->get('atags', array());
if (!(count($aTags) === 0 || (count($aTags) === 1 && $aTags[0] === ''))) {
    $allTagsFiles = json_encode($aTags);
}
$set_Itemid = (int) $params->get('set_itemid', 0);
$mitemid    = $set_Itemid > 0 ? $set_Itemid : $app->input->getInt('Itemid');

$filters = $helper->getInputs();

require(JModuleHelper::getLayoutPath('mod_dropfiles_search', $params->get('layout', 'default')));
