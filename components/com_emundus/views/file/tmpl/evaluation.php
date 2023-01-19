<?php
/**
 * @version		$Id: default.php 14401 2014-09-16 14:10:00Z brivalland $
 * @package		Joomla
 * @subpackage	Emundus
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

JText::script('COM_EMUNDUS_FILES_EVALUATION');
JText::script('COM_EMUNDUS_FILES_TO_EVALUATE');
JText::script('COM_EMUNDUS_FILES_EVALUATED');
JText::script('COM_EMUNDUS_ONBOARD_FILE');
JText::script('COM_EMUNDUS_ONBOARD_STATUS');
JText::script('COM_EMUNDUS_FILES_APPLICANT_FILE');
JText::script('COM_EMUNDUS_FILES_ATTACHMENTS');
JText::script('COM_EMUNDUS_FILES_COMMENTS');
JText::script('COM_EMUNDUS_ONBOARD_NOFILES');
JText::script('COM_EMUNDUS_FILES_ELEMENTS_SELECTED');
JText::script('COM_EMUNDUS_FILES_UNSELECT');
JText::script('COM_EMUNDUS_FILES_OPEN_IN_NEW_TAB');
JText::script('COM_EMUNDUS_FILES_CANNOT_ACCESS');
JText::script('COM_EMUNDUS_FILES_CANNOT_ACCESS_DESC');
JText::script('COM_EMUNDUS_FILES_DISPLAY_PAGE');
JText::script('COM_EMUNDUS_FILES_NEXT_PAGE');
JText::script('COM_EMUNDUS_FILES_PAGE');

JText::script('COM_EMUNDUS_ERROR_OCCURED');
JText::script('COM_EMUNDUS_ACTIONS_CANCEL');
JText::script('COM_EMUNDUS_OK');

JHtml::styleSheet('components/com_emundus/src/assets/css/element-ui/theme-chalk/index.css');

$xmlDoc = new DOMDocument();
if ($xmlDoc->load(JPATH_SITE.'/administrator/components/com_emundus/emundus.xml')) {
    $release_version = $xmlDoc->getElementsByTagName('version')->item(0)->textContent;
}

$menu = JFactory::getApplication()->getMenu();
$current_menu = $menu->getActive();

JFactory::getSession()->set('current_menu_id',$current_menu->id);
$user = JFactory::getUser();
?>
<div id="em-files"
     user=<?= $user->id ?>
     type="evaluation"
>
</div>

<script src="media/com_emundus_vue/app_emundus.js?<?php echo $release_version ?>"></script>


