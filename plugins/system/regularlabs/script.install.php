<?php
/**
 * @package         Regular Labs Library
 * @version         21.9.16879
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2021 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

if ( ! class_exists('PlgSystemRegularLabsInstallerScript'))
{
	require_once __DIR__ . '/script.install.helper.php';

	class PlgSystemRegularLabsInstallerScript extends PlgSystemRegularLabsInstallerScriptHelper
	{
		public $name           = 'REGULAR_LABS_LIBRARY';
		public $alias          = 'regularlabs';
		public $extension_type = 'plugin';
		public $show_message   = false;
		public $soft_break     = true;

		public function onBeforeInstall($route)
		{
			if ( ! parent::onBeforeInstall($route))
			{
				return false;
			}

			return $this->isNewer();
		}

		public function uninstall($adapter)
		{
			$this->uninstallLibrary($this->extname);
		}
	}
}
