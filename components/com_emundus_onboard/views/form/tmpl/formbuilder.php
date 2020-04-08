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

JText::script('COM_EMUNDUS_ONBOARD_SIDEMENUHELP');
JText::script('COM_EMUNDUS_ONBOARD_BUILDMENU');
JText::script('COM_EMUNDUS_ONBOARD_PREVIEW');
JText::script('COM_EMUNDUS_ONBOARD_ELEMENTTOOLTIPS');
JText::script('COM_EMUNDUS_ONBOARD_ADD_RETOUR');
JText::script('COM_EMUNDUS_ONBOARD_ADD_CONTINUER');



?>

<div id="em-formBuilder-vue" prid="<?= $this->prid; ?>" index="<?= $this->index; ?>" fid="<?= $this->fid; ?>"></div>

<script src="media/com_emundus_onboard/app.js"></script>