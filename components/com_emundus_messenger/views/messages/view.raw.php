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
 * eMundus Messenger View
 *
 * @since  0.0.1
 */
class EmundusmessengerViewMessages extends JViewLegacy {
    /**
     * Display the Settings view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    function display($tpl = null) {
        $jinput = JFactory::getApplication()->input;

        $document = JFactory::getDocument();
        $document->addScript('media/com_emundus_messenger/chunk-vendors.js');
        $document->addStyleSheet('media/com_emundus_messenger/app.css');

        // Display the template
        $layout = $jinput->getString('layout', null);
        // Display the template
        parent::display($tpl);
    }
}
