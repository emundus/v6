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

defined( '_JEXEC' ) or dir( 'Restricted Access' );

use FOF40\JoomlaAbstraction\CacheCleaner;
use FOF40\Model\Model;
use JchOptimize\Core\Admin\Tasks;
use JchOptimize\Platform\Cache;
use Joomla\CMS\Plugin\PluginHelper;

class ModeSwitcher extends Model
{
	public function setProduction()
	{
		$this->togglePluginState( 'jchoptimize' );

		if ( $this->container->params->get( 'pro_page_cache_integration_enable', '0' ) )
		{
			$this->togglePluginState( $this->container->params->get( 'pro_page_cache_select', 'cache' ) );
		}

		Tasks::generateNewCacheKey();
	}

	private function togglePluginState( $sElement, $bEnable = true )
	{
		$oDb    = $this->container->db;
		$oQuery = $oDb->getQuery( true )
			->update( '#__extensions' )
			->set( $oDb->quoteName( 'enabled' ) . ' = ' . ( $bEnable ? '1' : '0' ) )
			->where( $oDb->quoteName( 'type' ) . ' = ' . $oDb->quote( 'plugin' ) )
			->where( $oDb->quoteName( 'folder' ) . ' = ' . $oDb->quote( 'system' ) )
			->where( $oDb->quoteName( 'element' ) . ' = ' . $oDb->quote( $sElement ) );
		$oDb->setQuery( $oQuery );
		$oDb->execute();

		CacheCleaner::clearPluginsCache();
	}

	public function setDevelopment()
	{
		$oParams = $this->container->params;

		$this->togglePluginState( 'jchoptimize', false );

		if ( $oParams->get( 'pro_page_cache_integration_enable', '0' ) )
		{
			$this->togglePluginState( $oParams->get( 'pro_page_cache_select', 'cache' ), false );
		}

		Cache::deleteCache();
	}
}