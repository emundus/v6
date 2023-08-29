<?php
/**
 * @package		Joomla
 * @subpackage	eMundus
 * @copyright	Copyright (C) 2019 emundus.fr. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

$app = Factory::getApplication();
if (version_compare(JVERSION, '4.0', '>'))
{
	$lang_tag = $app->getLanguage()->getTag();
	$document = $app->getDocument();
	$wa = $document->getWebAssetManager();
	$wa->useScript('jquery');
	$wa->registerAndUseStyle('mod_emundus_help','modules/mod_emundus_help/style/mod_emundus_help.css');
} else {
	$lang_tag = JFactory::getLanguage()->getTag();
	$document = JFactory::getDocument();
	$document->addStyleSheet("modules/mod_emundus_help/style/mod_emundus_help.css" );
}



// Get release version
$xmlDoc = new DOMDocument();
if ($xmlDoc->load(JPATH_SITE.'/administrator/components/com_emundus/emundus.xml')) {
    $file_version = $xmlDoc->getElementsByTagName('version')->item(0)->textContent;
}
//

$current_lang = substr($lang_tag, 0 , 2);

require JModuleHelper::getLayoutPath('mod_emundus_help', 'default');


