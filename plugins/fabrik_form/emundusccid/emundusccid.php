<?php
/**
 * @version 2: emundusattachment
 * @package Fabrik
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Valide l'envoie d'un dossier de candidature et change le statut.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
 * Create a Joomla user from the forms data
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.juseremundus
 * @since       3.0
 */

class PlgFabrik_FormEmundusccid extends plgFabrik_Form
{

	public function onBeforeStore()
	{
		require_once JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'events.php';
		$h_events = new EmundusHelperEvents();

		$formModel = $this->getModel();
		$h_events->updateCcidFormData($formModel);
	}
}
