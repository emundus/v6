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

JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_PARAMETER');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_CAMPNAME');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_STARTDATE');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_ENDDATE');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_INFORMATION');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_RESUME');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_DESCRIPTION');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_PROGRAM');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_CHOOSEPROG');
JText::script('COM_EMUNDUS_ONBOARD_ADDCAMP_PICKYEAR');
JText::script('COM_EMUNDUS_ONBOARD_ADDPROGRAM');
JText::script('COM_EMUNDUS_ONBOARD_ADD_RETOUR');
JText::script('COM_EMUNDUS_ONBOARD_ADD_QUITTER');
JText::script('COM_EMUNDUS_ONBOARD_ADD_CONTINUER');
JText::script('COM_EMUNDUS_ONBOARD_FILTER_PUBLISH');
JText::script('COM_EMUNDUS_ONBOARD_FILTER_CLOSE');
JText::script('COM_EMUNDUS_ONBOARD_DEPOTDEDOSSIER');
JText::script('COM_EMUNDUS_ONBOARD_PROGNAME');
JText::script('COM_EMUNDUS_ONBOARD_PROGCODE');
JText::script('COM_EMUNDUS_ONBOARD_CHOOSECATEGORY');
JText::script('COM_EMUNDUS_ONBOARD_NAMECATEGORY');
JText::script('COM_EMUNDUS_ONBOARD_FORM_REQUIRED_NAME');
JText::script('COM_EMUNDUS_ONBOARD_REQUIRED_FIELDS_INDICATE');
JText::script('COM_EMUNDUS_ONBOARD_PROGRAM_RESUME');
JText::script('COM_EMUNDUS_ONBOARD_PROG_REQUIRED_LABEL');
JText::script('COM_EMUNDUS_ONBOARD_CAMP_REQUIRED_RESUME');
JText::script('COM_EMUNDUS_ONBOARD_OK');
JText::script('COM_EMUNDUS_ONBOARD_CANCEL');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH');
JText::script('COM_EMUNDUS_ONBOARD_TRANSLATETIP');
JText::script('COM_EMUNDUS_ONBOARD_TIP');

$lang = JFactory::getLanguage();
$actualLanguage = substr($lang->getTag(), 0 , 2);

$user = JFactory::getUser();
$coordinator_access = EmundusonboardHelperAccess::isCoordinator($user->id);
?>


<div id="em-addCampaign-vue" campaign="<?= $this->id ;?>" actualLanguage="<?= $actualLanguage ?>" coordinatorAccess="<?= $coordinator_access ?>"></div>

<script src="media/com_emundus_onboard/app.js"></script>
