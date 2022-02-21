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

namespace JchOptimize\Platform;

use FOF40\Container\Container;
use JchOptimize\Core\Interfaces\Plugin as PluginInterface;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;

defined( '_JEXEC' ) or die( 'Restricted access' );

class Plugin implements PluginInterface
{

	protected static $plugin = null;

	/**
	 *
	 * @return integer
	 */
	public static function getPluginId()
	{
		$plugin = static::loadjch();

		return $plugin->extension_id;
	}

	/**
	 *
	 * @return mixed|null
	 */
	private static function loadjch()
	{
		if ( self::$plugin !== null )
		{
			return self::$plugin;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery( true )
			->select( 'folder AS type, element AS name, params, extension_id' )
			->from( '#__extensions' )
			->where( 'element = ' . $db->quote( 'plg_system_jchoptimize' ) )
			->where( 'type = ' . $db->quote( 'plugin' ) )
			->where( 'folder = ' . $db->quote( 'system' ) );

		self::$plugin = $db->setQuery( $query )->loadObject();

		return self::$plugin;
	}

	/**
	 *
	 * @return mixed|null
	 */
	public static function getPlugin()
	{
		return static::loadjch();
	}

	/**
	 *
	 */
	public static function getPluginParams()
	{
		static $params = null;

		if ( is_null( $params ) )
		{
			$container = Container::getInstance( 'com_jchoptimize' );
			$params    = new Settings( new Registry( $container->params->getParams() ) );
		}

		return $params;
	}

	/**
	 *
	 * @param   Settings  $params
	 */
	public static function saveSettings(Settings $params )
	{
		$container = Container::getInstance( 'com_jchoptimize' );
		$container->params->setParams( $params->toArray() );
		$container->params->save();
	}

}
