<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_emundus_version
 *
 */

defined('_JEXEC') or die;

// Get release version
$xmlDoc = new DOMDocument();
if ($xmlDoc->load(JPATH_SITE.'/administrator/components/com_emundus/emundus.xml')) {
    $version = $xmlDoc->getElementsByTagName('version')->item(0)->textContent;
}
//

require JModuleHelper::getLayoutPath('mod_emundus_version', $params->get('layout', 'default'));
