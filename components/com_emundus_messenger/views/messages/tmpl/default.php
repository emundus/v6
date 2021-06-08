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
$document->addScript('media/com_emundus_messenger/chunk-vendors.js');
$document->addStyleSheet('media/com_emundus_messenger/app.css');

JText::script('COM_EMUNDUS_MESSENGER_TITLE');
?>

<div id="em-messages-vue"></div>


<script src="media/com_emundus_messenger/app.js"></script>
