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

require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'access.php');

## GLOBAL ##
JText::script('COM_EMUNDUS_ONBOARD_MODIFY');
JText::script('COM_EMUNDUS_ONBOARD_VISUALIZE');
JText::script('COM_EMUNDUS_ONBOARD_OK');
JText::script('COM_EMUNDUS_ONBOARD_CANCEL');
JText::script('COM_EMUNDUS_ONBOARD_ALL');
JText::script('COM_EMUNDUS_ONBOARD_SYSTEM');
JText::script('COM_EMUNDUS_ONBOARD_CAMPAIGNS');
JText::script('COM_EMUNDUS_ONBOARD_CAMPAIGNS_DESC');
JText::script('COM_EMUNDUS_ONBOARD_FILES');
JText::script('COM_EMUNDUS_ONBOARD_FILE');
## END ##

## ACTIONS ##
JText::script('COM_EMUNDUS_ONBOARD_ACTION');
JText::script('COM_EMUNDUS_ONBOARD_ACTION_PUBLISH');
JText::script('COM_EMUNDUS_ONBOARD_ACTION_UNPUBLISH');
JText::script('COM_EMUNDUS_ONBOARD_ACTION_DUPLICATE');
JText::script('COM_EMUNDUS_ONBOARD_ACTION_DELETE');
## END ##

## FILTERS ##
JText::script('COM_EMUNDUS_ONBOARD_FILTER');
JText::script('COM_EMUNDUS_ONBOARD_FILTER_ALL');
JText::script('COM_EMUNDUS_ONBOARD_FILTER_OPEN');
JText::script('COM_EMUNDUS_ONBOARD_FILTER_CLOSE');
JText::script('COM_EMUNDUS_ONBOARD_FILTER_PUBLISH');
JText::script('COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH');
JText::script('COM_EMUNDUS_ONBOARD_SELECT');
JText::script('COM_EMUNDUS_ONBOARD_DESELECT');
JText::script('COM_EMUNDUS_ONBOARD_TOTAL');
JText::script('COM_EMUNDUS_ONBOARD_SORT');
JText::script('COM_EMUNDUS_ONBOARD_SORT_CREASING');
JText::script('COM_EMUNDUS_ONBOARD_SORT_DECREASING');
JText::script('COM_EMUNDUS_ONBOARD_RESULTS');
JText::script('COM_EMUNDUS_ONBOARD_ALL_RESULTS');
JText::script('COM_EMUNDUS_ONBOARD_SEARCH');
## END ##

## CAMPAIGN ##
JText::script('COM_EMUNDUS_ONBOARD_ADD_CAMPAIGN');
JText::script('COM_EMUNDUS_ONBOARD_NOCAMPAIGN');
JText::script('COM_EMUNDUS_ONBOARD_FROM');
JText::script('COM_EMUNDUS_ONBOARD_TO');
JText::script('COM_EMUNDUS_ONBOARD_SINCE');
JText::script('COM_EMUNDUS_ONBOARD_CAMPDELETE');
JText::script('COM_EMUNDUS_ONBOARD_CAMPDELETED');
JText::script('COM_EMUNDUS_ONBOARD_CAMPAIGNUNPUBLISH');
JText::script('COM_EMUNDUS_ONBOARD_CAMPAIGNUNPUBLISHED');
JText::script('COM_EMUNDUS_ONBOARD_CAMPAIGNPUBLISHED');
JText::script('COM_EMUNDUS_ONBOARD_CAMPAIGNDUPLICATE');
JText::script('COM_EMUNDUS_ONBOARD_CAMPAIGNDUPLICATED');
JText::script('COM_EMUNDUS_ONBOARD_PROGRAM_ADVANCED_SETTINGS');
JText::script('COM_EMUNDUS_ONBOARD_DOSSIERS_PROGRAM');
JText::script('COM_EMUNDUS_ONBOARD_DOSSIERS_COUNT');
## END #

## TUTORIAL ##
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_CAMPAIGN');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_FORM');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_FORMBUILDER');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_DOCUMENTS');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_PROGRAM');
## END ##

$user = JFactory::getUser();
$coordinator_access = EmundusonboardHelperAccess::isCoordinator($user->id);
?>


<list id="em-list-vue" type="campaign" actualLanguage="<?= $actualLanguage ?>" coordinatorAccess="<?= $coordinator_access ?> "></list>

<script src="media/com_emundus_onboard/app_onboard.js"></script>