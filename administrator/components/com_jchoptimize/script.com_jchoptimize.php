<?php

/**
 * JCH Optimize - Aggregate and minify external resources for optmized downloads
 *
 * @author    Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
 * @license   GNU/GPLv3, See LICENSE file
 *
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

// Protect from unauthorized access
defined( '_JEXEC' ) or die();

use FOF40\Container\Container;
use FOF40\InstallScript\Component;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Adapter\ComponentAdapter;
use Joomla\CMS\Filesystem\File;
use Joomla\Registry\Registry;

// Load FOF if not already loaded
if ( ! defined( 'FOF40_INCLUDED' ) && ! @include_once( JPATH_LIBRARIES . '/fof40/include.php' ) )
{
	throw new RuntimeException( 'FOF 4.0 is not installed' );
}

class Com_JchoptimizeInstallerScript extends Component
{
	/**
	 * The component's name
	 *
	 * @var   string
	 */
	public $componentName = 'com_jchoptimize';

	/**
	 * The title of the component (printed on installation and uninstallation messages)
	 *
	 * @var string
	 */
	protected $componentTitle = 'JCH Optimize';

	/**
	 * The minimum PHP version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumPHPVersion = '7.2.0';

	/**
	 * The minimum Joomla! version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumJoomlaVersion = '3.9.0';

	/**
	 * The maximum Joomla! version this extension can be installed on
	 *
	 * @var   string
	 */
	protected $maximumJoomlaVersion = '4.0.999';

	/**
	 * Obsolete files and folders to remove from both paid and free releases. This is used when you refactor code and
	 * some files inevitably become obsolete and need to be removed.
	 *
	 * @var   array
	 */
	protected $removeFilesAllVersions = [
		'files'   => [
			// Use pathnames relative to your site's root, e.g.
			// 'administrator/components/com_foobar/helpers/whatever.php'
			'administrator/language/en-GB/en-GB.plg_system_jch_optimize.ini',
			'administrator/language/en-GB/en-GB.plg_system_jch_optimize.sys.ini',
			'administrator/manifests/packages/pkg_jch_optimize.xml'
		],
		'folders' => [
			// Use pathnames relative to your site's root, e.g.
			// 'administrator/components/com_foobar/baz'
			'plugins/system/jch_optimize',
			'media/plg_jchoptimize',
			'administrator/components/com_jch_optimize'
		],
	];

	/**
	 * Runs after install, update or discover_update
	 *
	 * @param   string                      $type  install, update or discover_update
	 * @param   JInstallerAdapterComponent  $parent
	 *
	 * @return  boolean  True to let the installation proceed, false to halt the installation
	 */
	public function postflight( string $type, ComponentAdapter $parent ): void
	{
		parent::postflight( $type, $parent ); // TODO: Change the autogenerated stub

		if ( ! in_array( $type, [ 'install', 'update' ] ) )
		{
			return;
		}

		if ( version_compare( JVERSION, '3.99.99', '>' ) )
		{
			$config_j4 = $parent->getParent()->getPath( 'source' ) . '/backend/config_j4.xml';
			$config    = JPATH_ADMINISTRATOR . '/components/com_jchoptimize/config.xml';

			File::delete( $config );
			if ( ! File::copy( $config_j4, $config ) )
			{
				$msg = "<p>Couldn't copy the config.xml file</p>";
				JLog::add( $msg, JLog::WARNING, 'jerror' );
			}

			File::delete( JPATH_ADMINISTRATOR . '/components/com_jchoptimize/config_j4.xml' );
		}

		@include_once( JPATH_LIBRARIES . '/fof40/include.php' );
		$container = Container::getInstance( 'com_jchoptimize' );

		/**
		 * Move params from plugin to component on upgrade
		 */
		$comp_params = $container->params->getParams();

		if ( empty( $comp_params ) )
		{
			$plugin = $this->loadOldJCHPlugin();

			if ( is_object( $plugin ) )
			{
				$plugin_params = new Registry( $plugin->params );
				$container->params->setParams( $plugin_params->toArray() );
				//convert smart combine to json
				$smart_combine_values = $container->params->get( 'pro_smart_combine_values' );

				if ( ! empty( $smart_combine_values ) && is_array( $smart_combine_values ) )
				{
					$container->params->set( 'pro_smart_combine_values', json_encode( $smart_combine_values ) );
				}

				$container->params->save();

				try
				{
					//Remove old extensions
					$db     = $container->db;
					$oQuery = $db->getQuery( true )
						->delete( '#__extensions' )
						->where( $db->quoteName( 'type' ) . ' = ' . $db->quote( 'package' ) . ' AND '
							. $db->quoteName( 'element' ) . ' = ' . $db->quote( 'pkg_jch_optimize' ), 'OR' )
						->where( $db->quoteName( 'type' ) . ' = ' . $db->quote( 'component' ) . ' AND '
							. $db->quoteName( 'element' ) . ' = ' . $db->quote( 'com_jch_optimize' ) )
						->where( $db->quoteName( 'type' ) . ' = ' . $db->quote( 'plugin' ) . ' AND '
							. $db->quoteName( 'element' ) . ' = ' . $db->quote( 'jch_optimize' ) . ' AND '
							. $db->quoteName( 'folder' ) . ' = ' . $db->quote( 'system' ) );
					$db->setQuery( $oQuery );
					$db->execute();

					//Enable new plugin
					$oQuery->clear()
						->update( '#__extensions' )
						->set( $db->qn( 'enabled' ) . ' = ' . $db->q( 1 ) )
						->where( 'type = ' . $db->quote( 'plugin' ) )
						->where( 'element = ' . $db->quote( 'jchoptimize' ) )
						->where( 'folder = ' . $db->quote( 'system' ) );
					$db->setQuery( $oQuery );
					$db->execute();
				}
				catch ( \Exception $e )
				{
				}
			}
		}
	}

	/**
	 * Loads the JCH Optimize plugin
	 */
	private function loadOldJCHPlugin()
	{
		$db = Factory::getDbo();

		try
		{
			$query   = $db->getQuery( true )
				->select( 'folder AS type, element AS name, params, extension_id, enabled, package_id' )
				->from( '#__extensions' )
				->where( $db->quoteName( 'element' ) . ' = ' . $db->quote( 'jch_optimize' ) )
				->where( $db->quoteName( 'type' ) . ' = ' . $db->quote( 'plugin' ) )
				->where( $db->quoteName( 'folder' ) . ' = ' . $db->quote( 'system' ) );
			$oPlugin = $db->setQuery( $query )->loadObject();
		}
		catch ( Exception $e )
		{
			return null;
		}

		return $oPlugin;
	}

}
