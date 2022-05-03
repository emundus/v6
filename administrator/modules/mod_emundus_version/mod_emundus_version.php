<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_emundus_version
 *
 */

defined('_JEXEC') or die;

// Get release version
$version = file_get_contents(JPATH_SITE . DS . 'version.txt');
//

require JModuleHelper::getLayoutPath('mod_emundus_version', $params->get('layout', 'default'));
