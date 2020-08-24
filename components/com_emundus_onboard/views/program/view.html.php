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

/**
 * eMundus Onboard View
 *
 * @since  0.0.1
 */
class EmundusonboardViewProgram extends JViewLegacy {
	/**
	 * Display the Program view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	function display($tpl = null) {
        $jinput = JFactory::getApplication()->input;

		// Display the template
        $layout = $jinput->getString('layout', null);

        if ($layout == 'add' || $layout == 'advancedsettings') {
            $this->id = $jinput->getString('pid', null);
		}
		parent::display($tpl);
	}
}
