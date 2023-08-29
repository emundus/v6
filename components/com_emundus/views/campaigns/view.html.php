<?php
/**
 * @package     Joomla
 * @subpackage  com_emunudus_onboard
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

/**
 * eMundus Onboard Campaign View
 *
 * @since  0.0.1
 */
class EmundusViewCampaigns extends JViewLegacy {

	function display($tpl = null) {
        $jinput = Factory::getApplication()->input;

        // Display the template
        $layout = $jinput->getString('layout', null);
        if ($layout == 'add') {
            $this->id = $jinput->getString('cid', null);
		}
		// Display the template
		parent::display($tpl);
	}
}
