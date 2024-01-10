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

use Joomla\CMS\Factory;

JText::script('COM_EMUNDUS_FILES_EVALUATION');
JText::script('COM_EMUNDUS_FILES_TO_EVALUATE');
JText::script('COM_EMUNDUS_FILES_EVALUATED');
JText::script('COM_EMUNDUS_ONBOARD_FILE');
JText::script('COM_EMUNDUS_ONBOARD_STATUS');
JText::script('COM_EMUNDUS_FILES_APPLICANT_FILE');
JText::script('COM_EMUNDUS_FILES_ATTACHMENTS');
JText::script('COM_EMUNDUS_FILES_COMMENTS');
JText::script('COM_EMUNDUS_ONBOARD_NOFILES');
JText::script('COM_EMUNDUS_FILES_ELEMENT_SELECTED');
JText::script('COM_EMUNDUS_FILES_ELEMENTS_SELECTED');
JText::script('COM_EMUNDUS_FILES_UNSELECT');
JText::script('COM_EMUNDUS_FILES_OPEN_IN_NEW_TAB');
JText::script('COM_EMUNDUS_FILES_CANNOT_ACCESS');
JText::script('COM_EMUNDUS_FILES_CANNOT_ACCESS_DESC');
JText::script('COM_EMUNDUS_FILES_DISPLAY_PAGE');
JText::script('COM_EMUNDUS_FILES_NEXT_PAGE');
JText::script('COM_EMUNDUS_FILES_PAGE');
JText::script('COM_EMUNDUS_FILES_TOTAL');
JText::script('COM_EMUNDUS_FILES_ALL');
JText::script('COM_EMUNDUS_FILES_ADD_COMMENT');
JText::script('COM_EMUNDUS_FILES_CANNOT_ACCESS_COMMENTS');
JText::script('COM_EMUNDUS_FILES_CANNOT_ACCESS_COMMENTS_DESC');
JText::script('COM_EMUNDUS_FILES_COMMENT_TITLE');
JText::script('COM_EMUNDUS_FILES_COMMENT_BODY');
JText::script('COM_EMUNDUS_FILES_VALIDATE_COMMENT');
JText::script('COM_EMUNDUS_FILES_COMMENT_DELETE');
JText::script('COM_EMUNDUS_FILES_ASSOCS');
JText::script('COM_EMUNDUS_FILES_TAGS');
JText::script('COM_EMUNDUS_FILES_PAGE_ON');

JText::script('COM_EMUNDUS_ERROR_OCCURED');
JText::script('COM_EMUNDUS_ACTIONS_CANCEL');
JText::script('COM_EMUNDUS_OK');
JText::script('COM_EMUNDUS_FILES_FILTER_NO_ELEMENTS_FOUND');

JHtml::styleSheet('components/com_emundus/src/assets/css/element-ui/theme-chalk/index.css');

require_once (JPATH_ROOT . '/components/com_emundus/helpers/cache.php');
$hash = EmundusHelperCache::getCurrentGitHash();

$ratio = '66/33';
$menu = JFactory::getApplication()->getMenu();
$current_menu = !empty($menu->getActive()) ? $menu->getActive() : $menu->getDefault();
$params = $menu->getParams($current_menu->id)->get('params');
if (!empty($params) && !empty($params->ratio_modal)) {
	$ratio = $params->ratio_modal;
}

$app = Factory::getApplication();
$fnum = $app->input->getString('fnum', '');

JFactory::getSession()->set('current_menu_id',$current_menu->id);
$user = JFactory::getUser();
?>
<div id="em-files"
     user=<?= $user->id ?>
     ratio=<?= $ratio ?>
     type="evaluation"
     fnum=<?= $fnum ?>
></div>

<script src="media/com_emundus_vue/app_emundus.js?<?php echo $hash ?>"></script>


