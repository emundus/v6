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

JText::script('COM_EMUNDUS_ONBOARD_ADDEMAIL_CHOOSETYPE');
JText::script('COM_EMUNDUS_ONBOARD_ADDEMAIL_NAME');
JText::script('COM_EMUNDUS_ONBOARD_ADDEMAIL_RECEIVER');
JText::script('COM_EMUNDUS_ONBOARD_ADDEMAIL_ADDRESS');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_PARAMETER');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_INFORMATION');
JText::script('COM_EMUNDUS_ONBOARD_CHOOSECATEGORY');
JText::script('COM_EMUNDUS_ONBOARD_ADD_RETOUR');
JText::script('COM_EMUNDUS_ONBOARD_ADD_CONTINUER');
JText::script('COM_EMUNDUS_ONBOARD_ADDEMAIL_RESUME');
JText::script('COM_EMUNDUS_ONBOARD_ADDEMAIL_CATEGORY');

$lang = JFactory::getLanguage();
$actualLanguage = substr($lang->getTag(), 0, 2);
?>

<div id="em-addEmail-vue" email="<?= $this->id ;?>" actualLanguage="<?= $actualLanguage ?>"></div>

<script src="media/com_emundus_onboard/app.js"></script>
