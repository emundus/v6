<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// include('../../administrator/comp');

/**
 * Script file of emundus_onboard component.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_emundus_onboard
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
class com_emundus_messengerInstallerScript
{
	/**
	 * This method is called after a component is installed.
	 *
	 * @param  \stdClass $parent - Parent object calling this method.
	 *
	 * @return void
	 */
	public function install($parent)
	{
		$parent->getParent();
	}

	/**
	 * This method is called after a component is uninstalled.
	 *
	 * @param  \stdClass $parent - Parent object calling this method.
	 *
	 * @return void
	 */
	public function uninstall($parent)
	{
		echo '<p>Composant Onboarding désinstallé avec succès.</p>';
	}

	/**
	 * This method is called after a component is updated.
	 *
	 * @param  \stdClass $parent - Parent object calling object.
	 *
	 * @return void
	 */
	public function update($parent)
	{
	}

	/**
	 * Runs just before any installation action is performed on the component.
	 * Verifications and pre-requisites should run in this function.
	 *
	 * @param  string    $type   - Type of PreFlight action. Possible values are:
	 *                           - * install
	 *                           - * update
	 *                           - * discover_install
	 * @param  \stdClass $parent - Parent object calling object.
	 *
	 * @return void
	 */
	public function preflight($type, $parent)
	{
		$user = JFactory::getUser();
		$app = JFactory::getApplication();

		$token = JSession::getFormToken();
	}

	/**
	 * Runs right after any installation action is performed on the component.
	 *
	 * @param  string    $type   - Type of PostFlight action. Possible values are:
	 *                           - * install
	 *                           - * update
	 *                           - * discover_install
	 * @param  \stdClass $parent - Parent object calling object.
	 *
	 * @return void
	 */
	function postflight($type, $parent)
	{
		echo '<strong>Installation terminée avec succès</strong>';

	}
}
