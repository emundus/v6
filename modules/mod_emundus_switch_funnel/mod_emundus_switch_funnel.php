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

$path = ModEmundusSwitchFunnel::getRoute($params);

$lang = JFactory::getLanguage()->getTag();
if($lang == 'fr-FR'){
    $lang = 'fr';
} else {
    $lang = '';
}

$route = ModEmundusSwitchFunnel::getCampaignsRoute();

require JModuleHelper::getLayoutPath('mod_emundus_switch_funnel');
