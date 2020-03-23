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

JText::script('COM_EMUNDUSONBOARD_ACTION');
JText::script('COM_EMUNDUSONBOARD_ACTION_PUBLISH');
JText::script('COM_EMUNDUSONBOARD_ACTION_UNPUBLISH');
JText::script('COM_EMUNDUSONBOARD_ACTION_DUPLICATE');
JText::script('COM_EMUNDUSONBOARD_ACTION_DELETE');
JText::script('COM_EMUNDUSONBOARD_FILTER');
JText::script('COM_EMUNDUSONBOARD_FILTER_ALL');
JText::script('COM_EMUNDUSONBOARD_FILTER_OPEN');
JText::script('COM_EMUNDUSONBOARD_FILTER_CLOSE');
JText::script('COM_EMUNDUSONBOARD_FILTER_PUBLISH');
JText::script('COM_EMUNDUSONBOARD_FILTER_UNPUBLISH');
JText::script('COM_EMUNDUSONBOARD_SELECT');
JText::script('COM_EMUNDUSONBOARD_DESELECT');
JText::script('COM_EMUNDUSONBOARD_TOTAL');
JText::script('COM_EMUNDUSONBOARD_ADD_CAMPAIGN');
JText::script('COM_EMUNDUSONBOARD_ADD_PROGRAM');
JText::script('COM_EMUNDUSONBOARD_ADD_EMAIL');
JText::script('COM_EMUNDUSONBOARD_ADD_FORM');
JText::script('COM_EMUNDUSONBOARD_ADD_FILES');
JText::script('COM_EMUNDUSONBOARD_SORT');
JText::script('COM_EMUNDUSONBOARD_SORT_CREASING');
JText::script('COM_EMUNDUSONBOARD_SORT_DECREASING');
JText::script('COM_EMUNDUSONBOARD_RESULTS');
JText::script('COM_EMUNDUSONBOARD_ALL_RESULTS');
JText::script('COM_EMUNDUSONBOARD_MODIFY');
JText::script('COM_EMUNDUSONBOARD_VISUALIZE');
JText::script('COM_EMUNDUSONBOARD_NOCAMPAIGN');
JText::script('COM_EMUNDUSONBOARD_NOPROGRAM');
JText::script('COM_EMUNDUSONBOARD_NOEMAIL');
JText::script('COM_EMUNDUSONBOARD_NOFORM');
JText::script('COM_EMUNDUSONBOARD_NOFILES');
JText::script('COM_EMUNDUSONBOARD_FROM');
JText::script('COM_EMUNDUSONBOARD_TO');
JText::script('COM_EMUNDUSONBOARD_SINCE');
JText::script('COM_EMUNDUSONBOARD_SEARCH');
JText::script('COM_EMUNDUSONBOARD_DOSSIERS_LASTNAME');
JText::script('COM_EMUNDUSONBOARD_DOSSIERS_FIRSTNAME');
JText::script('COM_EMUNDUSONBOARD_DOSSIERS_CAMPAIGN');
JText::script('COM_EMUNDUSONBOARD_DOSSIERS_PROGRAM');
JText::script('COM_EMUNDUSONBOARD_DOSSIERS_STATUS');
JText::script('COM_EMUNDUSONBOARD_DOSSIERS_START');
JText::script('COM_EMUNDUSONBOARD_DOSSIERS_END');
JText::script('COM_EMUNDUSONBOARD_DOSSIERS_CREATED');
JText::script('COM_EMUNDUSONBOARD_DOSSIERS_PROFILE');
JText::script('COM_EMUNDUSONBOARD_DOSSIERS_CLOSE');
JText::script('COM_EMUNDUSONBOARD_DOSSIERS_RESET');
JText::script('COM_EMUNDUSONBOARD_DOSSIERS_REMOVE');

$url = $_SERVER["REQUEST_URI"];

if (strpos($url, '/campaigns') !== false) {
    $type = "campaign";
}
else if (strpos($url, '/programs') !== false) {
    $type = "program";
}
else if (strpos($url, '/emails') !== false) {
    $type = "email";
}
else if (strpos($url, '/forms') !== false) {
    $type = "formulaire";
}
else if (strpos($url, '/dossiers') !== false) {
    $type = "files";
}
?>


<list id="em-list-vue" type="<?= $type ?>"></list>

<script src="media/com_emundus_onboard/app.js"></script>
