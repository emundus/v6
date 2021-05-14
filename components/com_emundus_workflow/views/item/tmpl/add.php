<?php
/**
 * @package     Joomla
 * @subpackage  com_emundus_onboard
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
JText::script('COM_EMUNDUS_WORKFLOW_STEP_MODAL_STEP_LABEL');
JText::script('COM_EMUNDUS_WORKFLOW_STEP_MODAL_STEP_INPUT_STATUS');
JText::script('COM_EMUNDUS_WORKFLOW_STEP_MODAL_STEP_OUTPUT_STATUS');
JText::script('COM_EMUNDUS_WORKFLOW_STEP_MODAL_STEP_BEGIN_DATE');
JText::script('COM_EMUNDUS_WORKFLOW_STEP_MODAL_STEP_END_DATE');
JText::script('COM_EMUNDUS_WORKFLOW_STEP_MODAL_STEP_SUPPLEMENTARY_INFORMATION');
JText::script('COM_EMUNDUS_WORKFLOW_STEP_MODAL_STEP_FAVORITE_COLOR');

JText::script('COM_EMUNDUS_WORKFLOW_STEP_PLACEHOLDER_OUTPUT_STATUS');
JText::script('COM_EMUNDUS_WORKFLOW_STEP_PLACEHOLDER_SUPPLEMENTARY_INFORMATION');

JText::script('COM_EMUNDUS_WORKFLOW_STEP_BUTTON_SAVE_PARAMS');
JText::script('COM_EMUNDUS_WORKFLOW_STEP_BUTTON_EXIT_PARAMS');

JText::script('COM_EMUNDUS_WORKFLOW_STEP_SWEET_ALERT_CONGRATULATION_TITLE');
JText::script('COM_EMUNDUS_WORKFLOW_STEP_SWEET_ALERT_SUCCESS_MESSAGE');
JText::script('COM_EMUNDUS_WORKFLOW_STEP_SWEET_ALERT_ERROR_TITLE');
JText::script('COM_EMUNDUS_WORKFLOW_STEP_SWEET_ALERT_MISSING_PARAMS_MESSAGE');

JText::script('COM_EMUNDUS_WORKFLOW_ELEMENT_CONFIGURATION_TITLE');
JText::script('COM_EMUNDUS_WORKFLOW_ELEMENT_CONFIGURATION_BREADCRUMB');
JText::script('COM_EMUNDUS_WORKFLOW_ELEMENT_BUTTON_SAVE_PARAMS');
JText::script('COM_EMUNDUS_WORKFLOW_ELEMENT_BUTTON_EXIT_PARAMS');
JText::script('COM_EMUNDUS_WORKFLOW_ELEMENT_SWEET_ALERT_CONGRATULATION_TITLE');
JText::script('COM_EMUNDUS_WORKFLOW_ELEMENT_SWEET_ALERT_SUCCESS_MESSAGE');

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
$document = JFactory::getDocument();
$document->addScript('media/com_emundus_workflow/chunk-vendors.js');
$document->addStyleSheet('media/com_emundus_workflow/app.css');

?>

<div id="add-item"></div>

<script src="media/com_emundus_workflow/app.js"></script>
