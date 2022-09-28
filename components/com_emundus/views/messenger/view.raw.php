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
class EmundusViewMessenger extends JViewLegacy {
    /**
     * Display the Settings view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    function display($tpl = null) {
        $jinput = JFactory::getApplication()->input;

        $xmlDoc = new DOMDocument();
if ($xmlDoc->load(JPATH_SITE.'/administrator/components/com_emundus/emundus.xml')) {
    $release_version = $xmlDoc->getElementsByTagName('version')->item(0)->textContent;
}

        JHTML::script( 'media/com_emundus_vue/app_emundus.js?'.$release_version);
        JHTML::script( 'media/com_emundus_vue/chunk-vendors_emundus.js');
        JHtml::stylesheet( 'media/com_emundus_vue/app_emundus.css');

        // Display the template
        $layout = $jinput->getString('layout', null);
        // Display the template
        parent::display($tpl);
    }
}
