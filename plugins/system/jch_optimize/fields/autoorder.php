<?php

/**
 * JCH Optimize - Joomla! plugin to aggregate and minify external resources for
 * optmized downloads
 * @author Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
 * @license GNU/GPLv3, See LICENSE file
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
 */

defined('_JEXEC') or die;

use JchOptimize\Core\Admin;
use JchOptimize\Platform\Cache;
use JchOptimize\Platform\Paths;
use JchOptimize\Platform\Utility;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactory;

include_once dirname(__FILE__) . '/auto.php';

class JFormFieldAutoorder extends JFormFieldAuto
{

	protected $type = 'autoorder';

	public function __construct($form = null)
	{
		parent::__construct($form);

		switch (JFactory::getApplication()->input->get('jchtask'))
		{
		case 'orderplugins':
			$this->orderPlugins();
			;
			break;
		case 'cleancache':
			$this->cleanCache();
			break;
		case 'browsercaching':
			$this->leverageBrowserCaching();
			break;
		case 'filepermissions':
			$this->fixFilePermissions();
			break;
		default:
			break;
		}
	}

	protected function getInput()
	{
		$size = 0;
		$no_files = 0;

		$cache_path = JPATH_SITE . '/cache/plg_jch_optimize/';
		$this->getCacheSize($cache_path, $size, $no_files);

		$cache_path = Paths::cachePath(false) . '/css';
		$this->getCacheSize($cache_path, $size, $no_files);

		$cache_path = Paths::cachePath(false) . '/js';
		$this->getCacheSize($cache_path, $size, $no_files);

		$decimals = 2;
		$sz       = 'BKMGTP';
		$factor   = (int) floor((strlen($size) - 1) / 3);
		$size     = sprintf("%.{$decimals}f", $size / pow(1024, $factor)) . $sz[$factor];
		$no_files = number_format($no_files);

		$sField = parent::getInput();

		$sField .= '<div><br><div><em>' . JText::sprintf('JCH_FILES', $no_files) . '</em></div>'
			. '<div><em>' . JText::sprintf('JCH_SIZE', $size) . '</em></div></div>';


		return $sField;
	}

	protected function getCacheSize($cache_path, &$size, &$no_files)
	{
		if (file_exists($cache_path))
		{
			$fi = new FilesystemIterator($cache_path, FilesystemIterator::SKIP_DOTS);

			foreach ($fi as $file)
			{
				$size += $file->getSize();
			}

			$no_files += iterator_count($fi);
		}
	}

	protected function getButtons()
	{
		$aButtons               = array();
		$aButtons[4]['link']    = JURI::getInstance()->toString() . '&amp;jchtask=orderplugins';
		$aButtons[4]['icon']    = 'fa-sort-numeric-asc';
		$aButtons[4]['color']   = '#278EB1';
		$aButtons[4]['text']    = Utility::translate('Order Plugin');
		$aButtons[4]['script']  = '';
		$aButtons[4]['class']   = 'enabled';
		$aButtons[4]['tooltip'] = Utility::translate('The published order of the plugin is important! When you click on this icon, it will attempt to order the plugin correctly.');

		$icons = Admin::getUtilityIcons();
		array_splice($icons, 2, 0, $aButtons );

		return $icons;
	}

	/**
	 * 
	 */
	public static function cleanCache($install=false)
	{
		$deleted = Cache::deleteCache();

		if($install)
		{
			return;
		}

		$oController = new BaseController();

		if (!$deleted)
		{
			$oController->setMessage(JText::_('JCH_CACHECLEAN_FAILED'), 'error');
		}
		else
		{
			$oController->setMessage(JText::_('JCH_CACHECLEAN_SUCCESS'));
		}

		self::display($oController);
	}

	/**
	 * 
	 * @return type
	 */
	protected static function getPlugins()
	{
		$oDb    = JFactory::getDbo();
		$oQuery = $oDb->getQuery(TRUE);
		$oQuery->select($oDb->quoteName(array('extension_id', 'ordering', 'element')))
	 ->from($oDb->quoteName('#__extensions'))
	 ->where(array(
		 $oDb->quoteName('type') . ' = ' . $oDb->quote('plugin'),
		 $oDb->quoteName('folder') . ' = ' . $oDb->quote('system')
	 ), 'AND');

		$oDb->setQuery($oQuery);

		return $oDb->loadAssocList('element');
	}

	/**
	 * 
	 */
	public static function leverageBrowserCaching($install=false)
	{
		$expires = Admin::leverageBrowserCaching();

		if($install)
		{ 
			return;
		}

		$oController = new BaseController();

		if ($expires === FALSE)
		{
			$oController->setMessage(JText::_('JCH_LEVERAGEBROWSERCACHE_FAILED'), 'error');
		}
		elseif ($expires == 'FILEDOESNTEXIST')
		{
			$oController->setMessage(JText::_('JCH_LEVERAGEBROWSERCACHE_FILEDOESNTEXIST'), 'warning');
		}
		elseif ($expires == 'CODEALREADYINFILE')
		{
			$oController->setMessage(JText::_('JCH_LEVERAGEBROWSERCACHE_CODEALREADYINFILE'), 'notice');
		}
		else
		{
			$oController->setMessage(JText::_('JCH_LEVERAGEBROWSERCACHE_SUCCESS'));
		}

		self::display($oController);
	}

	/**
	 * 
	 */
	public static function fixFilePermissions($install=false)
	{
		jimport('joomla.filesystem.folder');

		$wds = array(
			'plugins/system/jch_optimize',
			'media/plg_jchoptimize'
		);

		$result = true;

		foreach ($wds as $wd)
		{
			$files = JFolder::files(JPATH_ROOT . '/' . $wd, '.', TRUE, TRUE);

			foreach ($files as $file)
			{
				if (!chmod($file, 0644))
				{
					$result = false;

					break 2;
				}
			}

			$folders = JFolder::folders(JPATH_ROOT . '/' . $wd, '.', TRUE, TRUE);

			foreach ($folders as $folder)
			{
				if (!chmod($folder, 0755))
				{
					$result = false;

					break 2;
				}
			}
		}

		if($install)
		{
			return;
		}

		$oController = new BaseController();

		if ($result)
		{
			$oController->setMessage(JText::_('JCH_FIXFILEPERMISSIONS_SUCCESS'));
		}
		else
		{
			$oController->setMessage(JText::_('JCH_FIXFILEPERMISSIONS_FAIL'), 'error');
		}

		self::display($oController);
	}

	/**
	 * 
	 * @return type
	 */
	public static function orderPlugins($install=false)
	{
		//These plugins must be ordered last in this order; array of plugin elements
		$aOrder = array(
			'jscsscontrol',
			'eorisis_jquery',
			'jqueryeasy',
			'jch_optimize',
			'setcanonical',
			'canonical',
			'plugin_googlemap3',
			'jomcdn',
			'cdnforjoomla',
			'bigshotgoogleanalytics',
			'GoogleAnalytics',
			'pixanalytic',
			'ykhoonhtmlprotector',
			'jat3',
			'cache',
			'pagecacheextended',
			'homepagecache',
			'jSGCache',
			'j2pagecache',
			'jotcache',
			'lscache',
			'vmcache_last',
			'pixcookiesrestrict',
			'speedcache',
			'speedcache_last'
		);

		//Get an associative array of all installed system plugins with their extension id, ordering, and element
		$aPlugins = self::getPlugins();

		//Get an array of all the plugins that are installed that are in the array of specified plugin order above
		$aLowerPlugins = array_values(array_filter($aOrder,
			function($aVal) use ($aPlugins)
			{
				return (array_key_exists($aVal, $aPlugins));
			}
		));

		//Number of installed plugins
		$iNoPlugins      = count($aPlugins);
		//Number of installed plugins that needs to be ordered at the bottom of the order
		$iNoLowerPlugins = count($aLowerPlugins);
		$iBaseOrder      = $iNoPlugins - $iNoLowerPlugins;

		$cid   = array();
		$order = array();

		//Iterate through list of installed system plugins
		foreach ($aPlugins as $key => $value)
		{
			if (in_array($key, $aLowerPlugins))
			{
				$value['ordering'] = $iNoPlugins + 1 + array_search($key, $aLowerPlugins);
			}

			$cid[]   = $value['extension_id'];
			$order[] = $value['ordering'];
		}

		ArrayHelper::toInteger($cid);
		ArrayHelper::toInteger($order);

		if (version_compare(JVERSION, '4.0', 'ge'))
		{
			$oController = new BaseController(array(), new MVCFactory('Joomla\\Component\\Plugins\\'));
			$oPluginModel = $oController->getModel('Plugin');
		}
		else
		{
			$oController = new BaseController;
			$oController->addModelPath(JPATH_ADMINISTRATOR . '/components/com_plugins/models', 'PluginsModel');
			$oPluginModel = $oController->getModel('Plugin', 'PluginsModel');
		}

		$saved = $oPluginModel->saveorder($cid, $order);


		if($install)
		{
			return;
		}

		if ($saved === FALSE)
		{
			$oController->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_REORDER_FAILED', $oPluginModel->getError()), 'error');
		}
		else
		{
			$oController->setMessage(JText::_('JLIB_APPLICATION_SUCCESS_ORDERING_SAVED'));
		}

		self::display($oController);
	}
}


