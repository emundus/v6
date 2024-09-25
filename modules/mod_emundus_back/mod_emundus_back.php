<?php
/**
 * @package		Joomla
 * @subpackage	eMundus
 * @copyright	Copyright (C) 2019 emundus.fr. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

$back_link = Uri::base();
if($params->get('back_type') == 'previous') {
	$back_link = "history.go(-1)";
} else if ($params->get('back_type') == 'link') {
	$menu_id = $params->get('link', 0);
	if(!empty($menu_id)) {
		$back_link = Factory::getApplication()->getMenu()->getItem($menu_id)->alias;
	}
}

require JModuleHelper::getLayoutPath('mod_emundus_back', 'default');