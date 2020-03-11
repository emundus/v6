<?php
/**
 * JCH Optimize - Joomla! plugin to aggregate and minify external resources for
 * optmized downloads
 * @author    Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
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
 */
// No direct access

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\HTML\HTMLHelper;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Ajax;
use JchOptimize\Core\Admin;
use JchOptimize\Core\Json;
use JchOptimize\Core\Browser;
use JchOptimize\Core\Optimize;
use JchOptimize\Core\Logger;
use JchOptimize\Platform\Settings;
use JchOptimize\Platform\Cache;
use JchOptimize\Platform\Utility;
use JchOptimize\Platform\Plugin;
use JchOptimize\Platform\Html;
use JchOptimize\Platform\Uri;

if (!defined('JCH_PLUGIN_DIR'))
{
	define('JCH_PLUGIN_DIR', dirname(__FILE__));
}

if (!defined('JCH_VERSION'))
{
	define('JCH_VERSION', '6.0.0');
}

include_once(dirname(__FILE__) . '/jchoptimize/loader.php');

class plgSystemJCH_Optimize extends CMSPlugin
{

	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		if (!defined('JCH_DEBUG'))
		{
			define('JCH_DEBUG', ($this->params->get('debug', 0) && JDEBUG));
		}
	}


	protected function isPluginDisabled()
	{
		try
		{
			$app = JFactory::getApplication();
		}
		catch (Exception $e)
		{
			return false;
		}

		$user = JFactory::getUser();

		$menuexcluded    = $this->params->get('menuexcluded', array());
		$menuexcludedurl = $this->params->get('menuexcludedurl', array());

		return (!$app->isClient('site')
			|| ($app->input->get('jchbackend', '', 'int') == 1)
			|| ($app->get('offline', '0') && $user->get('guest'))
			|| $this->isEditorLoaded()
			|| in_array($app->input->get('Itemid', '', 'int'), $menuexcluded)
			|| Helper::findExcludes($menuexcludedurl, Uri::getInstance()->toString()));
	}

	/**
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public function onAfterRender()
	{
		if ($this->isPluginDisabled())
		{
			return false;
		}

		if ($this->params->get('log', 0))
		{
			error_reporting(E_ALL & ~E_NOTICE);
		}

		$app   = JFactory::getApplication();
		$sHtml = $app->getBody();


		if (!Helper::validateHtml($sHtml))
		{
			return false;
		}

		if ($app->input->get('jchbackend') == '2')
		{
			echo $sHtml;
			while (@ob_end_flush()) ;
			exit;
		}

		try
		{
			$sOptimizedHtml = Optimize::optimize($this->params, $sHtml);
		}
		catch (Exception $ex)
		{
			Logger::log($ex->getMessage(), Settings::getInstance($this->params));

			$sOptimizedHtml = $sHtml;
		}

		$app->setBody($sOptimizedHtml);
	}

	/**
	 * Gets the name of the current Editor
	 *
	 * @staticvar string $sEditor
	 * @return string
	 */
	protected function isEditorLoaded()
	{
		$aEditors = JPluginHelper::getPlugin('editors');

		foreach ($aEditors as $sEditor)
		{
			if (class_exists('plgEditor' . $sEditor->name, false))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 *
	 */
	public function onAjaxGetmultiselect()
	{
		$aData = Utility::get('data', array(), 'array');

		$params = Plugin::getPluginParams();
		$oAdmin = new Admin($params, true);
		$oHtml  = new Html($params);

		try
		{
			$sHtml = $oHtml->getOriginalHtml();
			$oAdmin->getAdminLinks($sHtml);
		}
		catch (Exception $e)
		{
		}

		$response = array();

		foreach ($aData as $sData)
		{
			$options                = $oAdmin->prepareFieldOptions($sData['type'], $sData['param'], $sData['group'], false);
			$response[$sData['id']] = new Json($options);
		}

		return new Json($response);

	}

	/**
	 * Provide a hash for the default page cache plugin's key based on type of browser detected by Google font
	 *
	 *
	 * @return string $hash    Calculated hash of browser type
	 */
	public function onPageCacheGetKey()
	{
		$browser = Browser::getInstance();
		$hash    = $browser->getFontHash();

		return $hash;
	}

	public function onJchCacheExpired()
	{
		return Cache::deleteCache('plugin');
	}


	/**
	 *
	 */
	public function onAfterDispatch()
	{
		if ($this->params->get('lazyload_enable', '0') && !$this->isPluginDisabled())
		{
			$options = array(
			    'relative' => true
			);

			HTMLHelper::script('plg_jchoptimize/ls.loader.js', $options);

			if ($this->params->get('pro_lazyload_effects', '0'))
			{
				HTMLHelper::stylesheet('plg_jchoptimize/pro-ls.effects.css', $options);
				HTMLHelper::script('plg_jchoptimize/pro-ls.loader.effects.js', $options);
			}

			if ($this->params->get('lazyload_autosize', '0'))
			{
				HTMLHelper::script('plg_jchoptimize/ls.autosize.js', $options);
			}

			HTMLHelper::script('plg_jchoptimize/lazysizes.js', $options);
		}
	}

	
}
