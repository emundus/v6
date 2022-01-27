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

require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');

## GLOBAL ##
JText::script('COM_EMUNDUS_ONBOARD_MODIFY');
JText::script('COM_EMUNDUS_ONBOARD_VISUALIZE');
JText::script('COM_EMUNDUS_ONBOARD_OK');
JText::script('COM_EMUNDUS_ONBOARD_CANCEL');
JText::script('COM_EMUNDUS_ONBOARD_ALL');
JText::script('COM_EMUNDUS_ONBOARD_SYSTEM');
JText::script('COM_EMUNDUS_ONBOARD_EMAILS');
JText::script('COM_EMUNDUS_ONBOARD_EMAILS_DESC');
JText::script('COM_EMUNDUS_ONBOARD_EMAIL_PREVIEWMODEL');
JText::script('COM_EMUNDUS_ONBOARD_CATEGORIES');
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

## EMAIL ##
JText::script('COM_EMUNDUS_ONBOARD_ADD_EMAIL');
JText::script('COM_EMUNDUS_ONBOARD_NOEMAIL');
JText::script('COM_EMUNDUS_ONBOARD_EMAILDELETE');
JText::script('COM_EMUNDUS_ONBOARD_EMAILDELETED');
JText::script('COM_EMUNDUS_ONBOARD_EMAILUNPUBLISHED');
JText::script('COM_EMUNDUS_ONBOARD_EMAILPUBLISHED');
## END ##

## TUTORIAL ##
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_CAMPAIGN');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_FORM');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_FORMBUILDER');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_DOCUMENTS');
JText::script('COM_EMUNDUS_ONBOARD_TUTORIAL_PROGRAM');
## END ##

JText::script('COM_EMUNDUS_ONBOARD_EMAIL_TAGS');
JText::script('COM_EMUNDUS_ONBOARD_EMAIL_DOCUMENT');

$user = JFactory::getUser();
$coordinator_access = EmundusonboardHelperAccess::isCoordinator($user->id);
$lang = JFactory::getLanguage();
$actualLanguage = substr($lang->getTag(), 0 , 2);
?>

<list id="em-list-vue" type="email" coordinatorAccess="<?= $coordinator_access ?>" actualLanguage="<?= $actualLanguage ?>"></list>

<script src="media/com_emundus_onboard/app_onboard.js"></script>
