<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_emundus_panel
 *
 */

defined('_JEXEC') or die;

// Include dependencies.
JLoader::register('ModEmunduspanelHelper', __DIR__ . '/helper.php');

$sitename = JFactory::getConfig()->get('sitename');
$version = file_get_contents(JPATH_SITE . DS . 'version.txt');

$git_file = JPATH_SITE . DS . '.git' . DS . 'FETCH_HEAD';
if(is_file($git_file)) {
    $last_updated = date("d/m/Y H:i", filemtime($git_file));
}

$confluence_link = $params->get('emundus_panel_confluence', '');

$features = ModEmunduspanelHelper::getFeaturesList();

require JModuleHelper::getLayoutPath('mod_emundus_panel', $params->get('layout', 'default'));
