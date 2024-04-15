<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

defined('_JEXEC') or die;

require_once __DIR__ . '/helper.php';

$app = Factory::getApplication();


if(version_compare(JVERSION, '4.0', '>='))
{
	$wa = $app->getDocument()->getWebAssetManager();
	$wa->registerAndUseStyle('mod_emundus_setup', 'modules/mod_emundus_setup/css/mod_emundus_setup.css');
}
else {
	require_once (JPATH_SITE.'/components/com_emundus/helpers/cache.php');
	$hash = EmundusHelperCache::getCurrentGitHash();

	$document = JFactory::getDocument();
	$document->addStyleSheet("modules/mod_emundus_cas/css/mod_emundus_setup.css?" . $hash);
}

$layout           = $params->get('layout', 'default');
$intro            = $params->get('intro', '');

switch ($layout) {
	case 'referent':
		$paths = $params->get('referent_paths', []);
		$attachments = $params->get('referent_attachments', []);

		$setups = ModEmundusSetupHelper::getReferentSetup($paths, $attachments);
		break;
}

require ModuleHelper::getLayoutPath('mod_emundus_setup', $layout);
