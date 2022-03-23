<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_emundus_panel
 *
 */

defined('_JEXEC') or die;

// Include dependencies.
JLoader::register('ModEmunduspanelHelper', __DIR__ . '/helper.php');


require JModuleHelper::getLayoutPath('mod_emundus_panel', $params->get('layout', 'default'));
