<?php
/**
 * @package     Joomla
 * @subpackage  com_emundus_onboard
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

JText::script("COM_EMUNDUS_WORKFLOW_WORKFLOW_CREATOR_MENU_WORKFLOW_NAME_PLACEHOLDER");
JText::script("COM_EMUNDUS_WORKFLOW_WORKFLOW_CREATOR_MENU_AVAILABLE_CAMPAIGNS_PLACEHOLDER");
JText::script("COM_EMUNDUS_WORKFLOW_WORKFLOW_CREATOR_MENU_TITLE_WORKFLOW_NAME");
JText::script("COM_EMUNDUS_WORKFLOW_WORKFLOW_CREATOR_MENU_TITLE_ASSOCIATED_CAMPAIGN");
JText::script("COM_EMUNDUS_WORKFLOW_COMMON_ADD_BUTTON_TITLE");

JText::script("COM_EMUNDUS_WORKFLOW_WORKFLOW_INFO_TABLE_WORKFLOW_ID");
JText::script("COM_EMUNDUS_WORKFLOW_WORKFLOW_INFO_TABLE_WORKFLOW_NAME");
JText::script("COM_EMUNDUS_WORKFLOW_WORKFLOW_INFO_TABLE_ASSOCIATED_CAMPAIGN");
JText::script("COM_EMUNDUS_WORKFLOW_WORKFLOW_INFO_TABLE_LAST_UPDATED_BY");
JText::script("COM_EMUNDUS_WORKFLOW_WORKFLOW_INFO_TABLE_LAST_UPDATED");
JText::script("COM_EMUNDUS_WORKFLOW_WORKFLOW_INFO_TABLE_CREATED_AT");
JText::script("COM_EMUNDUS_WORKFLOW_WORKFLOW_INFO_TABLE_LOGS");
JText::script("COM_EMUNDUS_WORKFLOW_WORKFLOW_INFO_TABLE_ACTIONS");

JText::script("COM_EMUNDUS_WORKFLOW_COMMON_OPEN_BUTTON_TITLE");
JText::script("COM_EMUNDUS_WORKFLOW_COMMON_REMOVE_BUTTON_TITLE");

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
$document = JFactory::getDocument();
$document->addScript('media/com_emundus_workflow/chunk-vendors.js');
$document->addStyleSheet('media/com_emundus_workflow/app.css');

?>

<div id="workflow-dashboard"></div>

<script src="media/com_emundus_workflow/app.js"></script>
