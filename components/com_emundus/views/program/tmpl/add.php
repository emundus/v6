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
$document->addScript('media/com_emundus_onboard/chunk-vendors_onboard.js');
$document->addStyleSheet('media/com_emundus_onboard/app_onboard.css');

JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_PROGRAM');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_CHOOSEPROG');
JText::script('COM_EMUNDUS_ONBOARD_ADDPROGRAM');
JText::script('COM_EMUNDUS_ONBOARD_ADD_RETOUR');
JText::script('COM_EMUNDUS_ONBOARD_ADD_CONTINUER');
JText::script('COM_EMUNDUS_ONBOARD_FILTER_PUBLISH');
JText::script('COM_EMUNDUS_ONBOARD_DEPOTDEDOSSIER');
JText::script('COM_EMUNDUS_ONBOARD_PROGNAME');
JText::script('COM_EMUNDUS_ONBOARD_PROGCODE');
JText::script('COM_EMUNDUS_ONBOARD_CHOOSECATEGORY');
JText::script('COM_EMUNDUS_ONBOARD_NAMECATEGORY');
JText::script('COM_EMUNDUS_ONBOARD_EDITOR_UNDO');
JText::script('COM_EMUNDUS_ONBOARD_FORM_REQUIRED_NAME');
JText::script('COM_EMUNDUS_ONBOARD_PROG_REQUIRED_CODE');
JText::script('COM_EMUNDUS_ONBOARD_PROG_REQUIRED_CATEGORY');
JText::script('COM_EMUNDUS_ONBOARD_REQUIRED_FIELDS_INDICATE');
JText::script('COM_EMUNDUS_ONBOARD_PROGRAM_RESUME');
JText::script('COM_EMUNDUS_ONBOARD_ADD_QUITTER');
JText::script('COM_EMUNDUS_ONBOARD_PROGRAM_ADVANCED_SETTINGS');

## TUTORIAL ##
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_CAMPAIGN');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_FORM');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_FORMBUILDER');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_DOCUMENTS');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_PROGRAM');
## END ##

$lang = JFactory::getLanguage();
$actualLanguage = substr($lang->getTag(), 0, 2);
?>

<div id="em-addProgram-vue" prog="<?= $this->id ;?>" actualLanguage="<?= $actualLanguage ?>"></div>

<script src="media/com_emundus_onboard/app_onboard.js"></script>
