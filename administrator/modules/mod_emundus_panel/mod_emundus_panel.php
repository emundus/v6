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

$xmlDoc = new DOMDocument();
if ($xmlDoc->load(JPATH_SITE.'/administrator/components/com_emundus/emundus.xml')) {
    $version = $xmlDoc->getElementsByTagName('version')->item(0)->textContent;
}

$git_file = JPATH_SITE . DS . '.git' . DS . 'FETCH_HEAD';
if(is_file($git_file)) {
    $last_updated = date("d/m/Y H:i", filemtime($git_file));
}

$confluence_link = $params->get('emundus_panel_confluence', '');

$h_panel = new ModEmundusPanelHelper();
$features = $h_panel->getFeaturesList();
$h_panel->checkup();

require JModuleHelper::getLayoutPath('mod_emundus_panel', $params->get('layout', 'default'));
