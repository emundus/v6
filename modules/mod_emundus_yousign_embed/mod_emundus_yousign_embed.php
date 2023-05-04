<?php
/**
 * @package		Joomla
 * @subpackage	eMundus
 * @copyright	Copyright (C) 2020 emundus.fr. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$session = JFactory::getSession();
$user = JFactory::getUser();

$table = JTable::getInstance('user', 'JTable');
$table->load($user->id);

$user_params = new JRegistry($table->params);
$yousignSession = JFactory::getSession()->get('YousignSession');

// Do not redirect the user if the param is not 'true'.
if (empty($yousignSession) && !empty($user_params->get('yousign_signer_id'))) {
    $yousignSession = new stdClass();
    $yousignSession->iframe_url = $user_params->get('yousign_url');
}

if (!empty($yousignSession->iframe_url)) {
    require JModuleHelper::getLayoutPath('mod_emundus_yousign_embed', 'default');
}
