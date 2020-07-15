<?php
/**
 * @package     Joomla
 * @subpackage  com_emundus_onboard
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
$document = JFactory::getDocument();
$document->addScript('media/com_emundus_onboard/chunk-vendors.js');
$document->addStyleSheet('media/com_emundus_onboard/app.css');

// GLOBAL
JText::script('COM_EMUNDUS_ONBOARD_ADD_RETOUR');
JText::script('COM_EMUNDUS_ONBOARD_ADD_CONTINUER');

// MENUS
JText::script('COM_EMUNDUS_ONBOARD_STATUSDESCRIPTION');
JText::script('COM_EMUNDUS_ONBOARD_TAGSDESCRIPTION');
JText::script('COM_EMUNDUS_ONBOARD_HOMEDESCRIPTION');
JText::script('COM_EMUNDUS_ONBOARD_SETTINGS_ADDTAG');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH');


$lang = JFactory::getLanguage();
$actualLanguage = substr($lang->getTag(), 0, 2);
?>

<div id="em-globalSettings-vue" actualLanguage="<?= $actualLanguage ?>"></div>

<script src="media/com_emundus_onboard/app.js"></script>
