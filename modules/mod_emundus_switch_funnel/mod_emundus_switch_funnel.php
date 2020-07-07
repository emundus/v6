<?php
/**
 * @package		Joomla.Site
 *
 * @subpackage	mod_emundus_switch_funnel
 * @copyright	Copyright (C) 2018 emundus.fr, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__) . '/helper.php';

$id = JFactory::getUser()->id;

$coordinatoraccess = EmundusHelperAccess::asCoordinatorAccessLevel($id);
$path = ModEmundusSwitchFunnel::getRoute($params);

$locallang = JFactory::getLanguage()->getTag();
$lang = '';

if($locallang == 'fr-FR'){
    $lang = 'fr';
}

require JModuleHelper::getLayoutPath('mod_emundus_switch_funnel');
