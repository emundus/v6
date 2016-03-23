<?php
/**
 * @version   $Id: module.php 6306 2013-01-05 05:39:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('JPATH_BASE') or die();
jimport('joomla.application.module.helper');
/** @var $gantry Gantry */
		global $gantry;

$module_name = JFactory::getApplication()->input->getString('module', null);
$module_id   = JFactory::getApplication()->input->getInt('moduleid', null);

$db = JFactory::getDBO();
if (isset($module_name)) {
	$query = sprintf("SELECT DISTINCT * from #__modules where title='%s'", $db->Quote($module_name));
} else {
	$query = sprintf("SELECT DISTINCT * from #__modules where id=%d", $module_id);
}

$db->setQuery($query);
$result = $db->loadObject();

if ($result) {
	$module = JModuleHelper::getModule(substr_replace($result->module, '', 0, 4), $result->title);
	echo JModuleHelper::renderModule($module, array('style' => "raw"));
}
