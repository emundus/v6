<?php
/**
 * @package		Joomla
 * @subpackage	eMundus
 * @copyright	Copyright (C) 2019 emundus.fr. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$document = JFactory::getDocument();
$document->addStyleSheet("modules/mod_emundus_help/style/mod_emundus_help.css" );
$document->addStyleSheet('https://fonts.googleapis.com/icon?family=Material+Icons' );

// Get release version
$xmlDoc = new DOMDocument();
if ($xmlDoc->load(JPATH_SITE.'/administrator/components/com_emundus/emundus.xml')) {
    $file_version = $xmlDoc->getElementsByTagName('version')->item(0)->textContent;
}
//

require JModuleHelper::getLayoutPath('mod_emundus_help', 'default');


