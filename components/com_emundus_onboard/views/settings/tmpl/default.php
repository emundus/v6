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

require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');

// GLOBAL
JText::script('COM_EMUNDUS_ONBOARD_ADD_RETOUR');
JText::script('COM_EMUNDUS_ONBOARD_ADD_CONTINUER');

// MENUS
JText::script('COM_EMUNDUS_ONBOARD_STATUSDESCRIPTION');
JText::script('COM_EMUNDUS_ONBOARD_STYLINGDESCRIPTION');
JText::script('COM_EMUNDUS_ONBOARD_TAGSDESCRIPTION');
JText::script('COM_EMUNDUS_ONBOARD_HOMEDESCRIPTION');
JText::script('COM_EMUNDUS_ONBOARD_SETTINGS_ADDTAG');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH');
JText::script('COM_EMUNDUS_ONBOARD_COLORS');
JText::script('COM_EMUNDUS_ONBOARD_UPDATE_LOGO');
JText::script('COM_EMUNDUS_ONBOARD_DROP_HERE');


$lang = JFactory::getLanguage();
$actualLanguage = substr($lang->getTag(), 0, 2);

$user = JFactory::getUser();
$coordinator_access = EmundusonboardHelperAccess::isCoordinator($user->id);
?>

<div id="em-globalSettings-vue" actualLanguage="<?= $actualLanguage ?>" coordinatorAccess="<?= $coordinator_access ?>"></div>

<script src="media/com_emundus_onboard/app.js"></script>
