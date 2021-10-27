<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Component\Admin\Model;

defined( '_JEXEC' ) or die( 'Restricted Access' );

use FOF40\Container\Container;
use Joomla\CMS\Filesystem\Folder as JFolder;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\LegacyFactory;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\Utilities\ArrayHelper;

class Utility extends \FOF40\Model\Model
{
	public function fixFilePermissions()
	{
		jimport( 'joomla.filesystem.folder' );

		$wds = array(
			'administrator/components/com_jchoptimize',
			'plugins/system/jchoptimize',
			'media/plg_jchoptimize'
		);

		$result = true;

		foreach ( $wds as $wd )
		{
			$files = JFolder::files( JPATH_ROOT . '/' . $wd, '.', true, true );

			foreach ( $files as $file )
			{
				if ( ! chmod( $file, 0644 ) )
				{
					$result = false;

					break 2;
				}
			}

			$folders = JFolder::folders( JPATH_ROOT . '/' . $wd, '.', true, true );

			foreach ( $folders as $folder )
			{
				if ( ! chmod( $folder, 0755 ) )
				{
					$result = false;

					break 2;
				}
			}
		}

		return $result;
	}

	public function orderPlugins()
	{
		//These plugins must be ordered last in this order; array of plugin elements
		$aOrder = array(
			'jscsscontrol',
			'eorisis_jquery',
			'jqueryeasy',
			'jchoptimize',
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
			'plg_gkcache',
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
		$aLowerPlugins = array_values( array_filter( $aOrder,
			function ( $aVal ) use ( $aPlugins ) {
				return ( array_key_exists( $aVal, $aPlugins ) );
			}
		) );

		//Number of installed plugins
		$iNoPlugins = count( $aPlugins );
		//Number of installed plugins that needs to be ordered at the bottom of the order
		$iNoLowerPlugins = count( $aLowerPlugins );
		$iBaseOrder      = $iNoPlugins - $iNoLowerPlugins;

		$cid   = array();
		$order = array();

		//Iterate through list of installed system plugins
		foreach ( $aPlugins as $key => $value )
		{
			if ( in_array( $key, $aLowerPlugins ) )
			{
				$value['ordering'] = $iNoPlugins + 1 + array_search( $key, $aLowerPlugins );
			}

			$cid[]   = $value['extension_id'];
			$order[] = $value['ordering'];
		}

		ArrayHelper::toInteger( $cid );
		ArrayHelper::toInteger( $order );

		/*$oPluginsContainer = Container::getInstance('com_plugins');
		$oPluginModel = $oPluginsContainer->factory->model('PluginsModelPlugin');*/


		$config = [
			'base_path' => JPATH_ADMINISTRATOR . '/components/com_plugins',
			'name'      => 'plugins'
		];

		//Joomla version 3.9 doesn't use a factory
		if ( version_compare( JVERSION, '3.10', 'lt' ) )
		{
			$oPluginsController = new BaseController( $config );
		}
		else
		{
			$factory = version_compare( JVERSION, '3.999.999', 'gt' ) ? new MVCFactory( 'Joomla\\Component\\Plugins' ) : new LegacyFactory();
			$oPluginsController = new BaseController( $config, $factory );
		}

		$oPluginModel = $oPluginsController->getModel( 'Plugin', '', $config );

		return $oPluginModel->saveorder( $cid, $order );

	}

	private function getPlugins()
	{
		$oDb = $this->getContainer()->db;

		$oQuery = $oDb->getQuery( true );
		$oQuery->select( $oDb->quoteName( array( 'extension_id', 'ordering', 'element' ) ) )
			->from( $oDb->quoteName( '#__extensions' ) )
			->where( array(
				$oDb->quoteName( 'type' ) . ' = ' . $oDb->quote( 'plugin' ),
				$oDb->quoteName( 'folder' ) . ' = ' . $oDb->quote( 'system' )
			), 'AND' );

		$oDb->setQuery( $oQuery );

		return $oDb->loadAssocList( 'element' );
	}
}