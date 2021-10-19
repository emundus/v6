<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core\Admin\Ajax;

use JchOptimize\Core\Admin\Json;
use JchOptimize\Core\Admin\MultiSelectItems;
use JchOptimize\Platform\Html;
use JchOptimize\Platform\Plugin;
use JchOptimize\Platform\Utility;

defined( '_JCH_EXEC' ) or die( 'Restricted access' );

class MultiSelect extends Ajax
{
	public function run()
	{
		$aData = Utility::get( 'data', array(), 'array' );

		$params = Plugin::getPluginParams();
		$oAdmin = new MultiSelectItems( $params );
		$oHtml  = new Html( $params );

		try
		{
			$sHtml = $oHtml->getHomePageHtml();
			$oAdmin->getAdminLinks( $sHtml );
		}
		catch ( \Exception $e )
		{
		}

		$response = array();

		foreach ( $aData as $sData )
		{
			$options = $oAdmin->prepareFieldOptions( $sData['type'], $sData['param'], $sData['group'], false );

			$response[$sData['id']] = new Json( $options );
		}

		return new Json( $response );
	}
}