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

JText::script('COM_EMUNDUSONBOARD_ADDCAMP_PARAMETER');
JText::script('COM_EMUNDUSONBOARD_ADDCAMP_CAMPNAME');
JText::script('COM_EMUNDUSONBOARD_ADDCAMP_STARTDATE');
JText::script('COM_EMUNDUSONBOARD_ADDCAMP_ENDDATE');
JText::script('COM_EMUNDUSONBOARD_ADDCAMP_PASDEFIN');
JText::script('COM_EMUNDUSONBOARD_ADDCAMP_INFORMATION');
JText::script('COM_EMUNDUSONBOARD_ADDCAMP_RESUME');
JText::script('COM_EMUNDUSONBOARD_ADDCAMP_DESCRIPTION');
JText::script('COM_EMUNDUSONBOARD_ADDCAMP_PROGRAM');
JText::script('COM_EMUNDUSONBOARD_ADDCAMP_CHOOSEPROG');
JText::script('COM_EMUNDUSONBOARD_ADDCAMP_PICKYEAR');
JText::script('COM_EMUNDUSONBOARD_ADDPROGRAM');
JText::script('COM_EMUNDUSONBOARD_ADDCAMP_CHOOSEPROFILE');
JText::script('COM_EMUNDUSONBOARD_ADD_RETOUR');
JText::script('COM_EMUNDUSONBOARD_ADD_CONTINUER');
JText::script('COM_EMUNDUSONBOARD_FILTER_PUBLISH');
JText::script('COM_EMUNDUSONBOARD_FILTER_CLOSE');
JText::script('COM_EMUNDUSONBOARD_DEPOTDEDOSSIER');
JText::script('COM_EMUNDUSONBOARD_PROGNAME');
JText::script('COM_EMUNDUSONBOARD_PROGCODE');
JText::script('COM_EMUNDUSONBOARD_CHOOSECATEGORY');
JText::script('COM_EMUNDUSONBOARD_NAMECATEGORY');

$lang = JFactory::getLanguage();
$actualLanguage = substr($lang->getTag(), 0 , 2);
?>


<div id="em-addCampaign-vue" campaign="<?= $this->id ;?>" actualLanguage="<?= $actualLanguage ?>"></div>

<script src="media/com_emundus_onboard/app.js"></script>
