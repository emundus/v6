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

JText::script('COM_EMUNDUS_ONBOARD_FROM');
JText::script('COM_EMUNDUS_ONBOARD_TO');
JText::script('COM_EMUNDUS_ONBOARD_SINCE');
JText::script('COM_EMUNDUS_ONBOARD_MODIFY');
JText::script('COM_EMUNDUS_ONBOARD_CAMPAIGN');
JText::script('COM_EMUNDUS_ONBOARD_CHOOSE_FORM');
JText::script('COM_EMUNDUS_ONBOARD_CHOOSE_EVALUATOR_GROUP');
JText::script('COM_EMUNDUS_ONBOARD_CHOOSE_EMAIL_TRIGGER');
JText::script('COM_EMUNDUS_ONBOARD_CHOOSE_EVALUATION_GRID');
JText::script('COM_EMUNDUS_ONBOARD_ADD_RETOUR');
JText::script('COM_EMUNDUS_ONBOARD_ADD_CONTINUER');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_STARTDATE');
JText::script('COM_EMUNDUS_ONBOARD_FORMDESCRIPTION');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_ENDDATE');
JText::script('COM_EMUNDUS_ONBOARD_DOCSDESCRIPTION');
JText::script('CREATE');
JText::script('RETRIEVE');
JText::script('UPDATE');
JText::script('DELETE');
JText::script('COM_EMUNDUS_ONBOARD_CHOOSE_PROFILE_WARNING');
JText::script('COM_EMUNDUS_ONBOARD_WARNING');
JText::script('COM_EMUNDUS_ONBOARD_FORM_AFFECTEDFILES');
JText::script('COM_EMUNDUS_ONBOARD_DUPLICATE');
JText::script('COM_EMUNDUS_ONBOARD_CREATE_DOCUMENT');
JText::script('COM_EMUNDUS_ONBOARD_LASTNAME');
JText::script('COM_EMUNDUS_ONBOARD_OK');
JText::script('COM_EMUNDUS_ONBOARD_MAXPERUSER');
JText::script('COM_EMUNDUS_ONBOARD_FILETYPE_ACCEPTED');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_DESCRIPTION');
JText::script('COM_EMUNDUS_ONBOARD_PROG_REQUIRED_LABEL');
JText::script('COM_EMUNDUS_ONBOARD_FILETYPE_ACCEPTED_REQUIRED');
JText::script('COM_EMUNDUS_ONBOARD_MAXPERUSER_REQUIRED');
JText::script('COM_EMUNDUS_ONBOARD_BUILDER_DELETEDOCUMENTTYPE');
JText::script('COM_EMUNDUS_ONBOARD_BUILDER_DELETEDOCUMENTTYPE_MESSAGE');
JText::script('COM_EMUNDUS_ONBOARD_CANCEL');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH');
JText::script('COM_EMUNDUS_ONBOARD_SUBMITTIONDESCRIPTION');

$lang = JFactory::getLanguage();
$actualLanguage = substr($lang->getTag(), 0, 2);
?>

<div id="em-addFormNextCampaign-vue" campaignId="<?= $this->cid ?>" actualLanguage="<?= $actualLanguage ?>" index="<?= $this->index ?>"></div>

<script src="media/com_emundus_onboard/app.js"></script>
