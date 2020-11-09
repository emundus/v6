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

// Do not redirect the user if the param is not 'true'.
if (!empty($user_params->get('yousignMemberId'))) {
	$yousign_member_id = $user_params->get('yousignMemberId');
} elseif ($session->has('youSignTmp')) {
	$yousign_member_id = $session->get('youSignTmp');
}

if (!empty($yousign_member_id)) {
	$signature_ui = $params->get('signature_ui');
	require JModuleHelper::getLayoutPath('mod_emundus_yousign_embed', 'default');
}
