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
$document->addScript('media/com_emundus_workflow/chunk-vendors_workflow.js');
$document->addStyleSheet('media/com_emundus_workflow/app_workflow.css');
$document->addStyleSheet('templates/g5_helium/custom/scss/global.scss');
$document->addScript('media/com_emundus_workflow/app_workflow.js');

?>

<!-- init workflow dashboard in form of a HTML div -->
<div id="workflow-steps"></div>

<script src="media/com_emundus_workflow/app_workflow.js"></script>
