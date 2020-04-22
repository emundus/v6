<?php

/**
 * JCH Optimize - Joomla! plugin to aggregate and minify external resources for
 *   optmized downloads
 * @author    Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall. All rights reserved.
 * @license   GNU/GPLv3, See LICENSE file
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 *
 * This plugin includes other copyrighted works. See individual
 * files for details.
 */

defined('_JEXEC') or die('Restricted access');

use JchOptimize\Core\Admin;
use JchOptimize\Core\Helper;
use Joomla\CMS\Installer\InstallerAdapter;

class PlgSystemjch_optimizeInstallerScript
{
	/**
	 *
	 * @param   string            $type
	 * @param   InstallerAdapter  $parent
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function preflight($type, $parent)
	{
		$app = JFactory::getApplication();

		if ($type == 'install')
		{
			if (version_compare(PHP_VERSION, '5.6.0', '<'))
			{
				$app->enqueueMessage('This plugin requires PHP 5.6.0 or greater to work. Your installed version is ' . PHP_VERSION, 'error');

				return false;
			}
		}

		$compatible      = true;
		$minimum_version = '3.7.0';

		if (version_compare(JVERSION, $minimum_version, '<'))
		{
			$compatible = false;
		}

		if (!$compatible)
		{
			$app->enqueueMessage('JCH Optimize is not compatible with your version of Joomla!. This plugin requires v' . $minimum_version . ' or greater to work. Your installed version is ' . JVERSION, 'error');

			return false;
		}

		$manifest    = $parent->getManifest();
		$new_variant = (string) $manifest->variant;

		$file = JPATH_PLUGINS . '/system/jch_optimize/jch_optimize.xml';

		if (file_exists($file))
		{
			$xml         = simplexml_load_file($file);
			$old_variant = (string) $xml->variant;

			if ($old_variant == 'PRO' && $new_variant == 'FREE')
			{
				$app->enqueueMessage('You are trying to install the FREE version of JCH Optimize but you currently have the PRO version installed. You must uninstall the PRO version before you can install the FREE version.', 'error');

				return false;
			}
		}
	}

	/**
	 *
	 * @param   string            $type
	 * @param   InstallerAdapter  $parent
	 */
	public function postflight($type, $parent)
	{
		if (!class_exists('JFormFieldAutoorder'))
		{
			require_once($parent->getParent()->getPath('source') . '/fields/autoorder.php');
		}

		if ($type == 'install')
		{
			JFormFieldAutoorder::fixFilePermissions(true);
			JFormFieldAutoorder::leverageBrowserCaching(true);
		}

		if ($type == 'update')
		{
			JFormFieldAutoorder::cleanCache(true);
			
		}

		JFormFieldAutoorder::orderPlugins(true);
	}

	/**
	 *
	 * @param   InstallerAdapter  $parent
	 */
	public function uninstall($parent)
	{
		jimport('joomla.filesystem.folder');

		if (!class_exists('JFormFieldAutoorder'))
		{
			require_once($parent->getParent()->getPath('extension_root') . '/fields/autoorder.php');
		}

		$sprites = JPATH_ROOT . '/images/jch-optimize';

		if (file_exists($sprites))
		{
			JFolder::delete($sprites);
		}

		JFormFieldAutoorder::cleanCache(true);
		Admin::cleanHtaccess();
	}

}
