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

use Exception;
use FOF40\Container\Container;
use FOF40\Update\Update;

class Updates extends Update
{
	public function __construct( $config = [] )
	{
		$oContainer = Container::getInstance( 'com_jchoptimize' );

		$config['update_component'] = 'pkg_jchoptimize';
		$config['update_paramskey'] = 'pro_downloadid';
		$config['update_container'] = $oContainer;

		$isPro = defined( 'JCH_PRO' ) ? JCH_PRO : 0;

		if ( $isPro )
		{
			$config['update_sitename'] = 'JCH Optimize Pro';
			$config['update_site']     = 'https://updates.jch-optimize.net/joomla-pro.xml';

			$sLicenseKey = $oContainer->params->get( 'pro_downloadid', '' );

			if ( $this->isValidLicenseKey( $this->sanitizeLicenseKey( $sLicenseKey ) ) )
			{
				$config['update_extraquery'] = 'dlid=' . $sLicenseKey;
			}
		}
		else
		{
			$config['update_sitename'] = 'JCH Optimize';
			$config['update_site']     = 'https://updates.jch-optimize.net/joomla-core.xml';
		}

		if ( defined( 'JCH_VERSION' ) )
		{
			$config['update_version'] = JCH_VERSION;
		}

		parent::__construct( $config );
	}


	/**
	 * Gets the ID of extension
	 * @return  int  Extension ID or 0 on failure
	 */
	private function findExtensionId()
	{
		$db    = $this->container->db;
		$query = $db->getQuery( true )
			->select( $db->qn( 'extension_id' ) )
			->from( $db->qn( '#__extensions' ) )
			->where( $db->qn( 'element' ) . ' = ' . $db->q( 'pkg_jchoptimize' ) )
			->where( $db->qn( 'type' ) . ' = ' . $db->q( 'package' ) );

		try
		{
			$id = $db->setQuery( $query, 0, 1 )->loadResult();
		}
		catch ( Exception $e )
		{
			$id = 0;
		}

		return empty( $id ) ? 0 : (int)$id;
	}
}