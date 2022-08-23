<?php
/**
 * @package		Joomla
 * @subpackage	eMundus
 * @copyright	Copyright (C) 2019 emundus.fr. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

require_once dirname(__FILE__).'/helper.php';

$lang = JFactory::getLanguage();
$actualLanguage = substr($lang->getTag(), 0 , 2);

$release_note = file_get_contents('release_note_' . $actualLanguage . '.md');
if(empty($release_note)){
    $release_note = file_get_contents('release_note_en.md');
}
$parsedown = new Parsedown();
$release_note = trim($parsedown->text($release_note));
$release_note = str_replace(array("\r", "\n"), '', $release_note);

$git_file = JPATH_SITE . DS . '.git' . DS . 'FETCH_HEAD';
if(is_file($git_file)) {
    $last_updated = date("Y-m-d H:i:s", filemtime($git_file));
}

$old_version = ModEmundusVersionHelper::getOldVersion();
$xmlDoc = new DOMDocument();
if ($xmlDoc->load(JPATH_SITE.'/administrator/components/com_emundus/emundus.xml')) {
    $current_version = $xmlDoc->getElementsByTagName('version')->item(0)->textContent;
}

if(empty($old_version)){
    ModEmundusVersionHelper::insertVersion($current_version,$last_updated);
} elseif($old_version != $current_version) {
    ModEmundusVersionHelper::updateVersion($current_version,$last_updated);
}

require JModuleHelper::getLayoutPath('mod_emundus_version', 'default');
