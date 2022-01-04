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

use JchOptimize\Core\Admin\Ajax\Ajax as AdminAjax;
use JchOptimize\Core\Admin\Icons;

class Configure extends \FOF40\Model\Model
{
	public function applyAutoSettings()
	{
		$aAutoParams = Icons::autoSettingsArrayMap();

		$sAutoSetting     = $this->getState( 'autosetting', 's1' );
		$aSelectedSetting = array_column( $aAutoParams, $sAutoSetting );

		$aSettingsToApply = array_combine( array_keys( $aAutoParams ), $aSelectedSetting );

		$this->container->params->setParams( $aSettingsToApply );
		$this->container->params->set('combine_files_enable', '1');
		$this->container->params->save();
	}

	public function toggleSetting()
	{
		$sSetting = $this->getState( 'setting', null );

		if ( is_null( $sSetting ) )
		{
			//@TODO some logging here
			return;
		}

		$iCurrentSetting = (int)$this->container->params->get( $sSetting );
		$sNewSetting     = (string)abs( $iCurrentSetting - 1 );

		if ( $sSetting == 'pro_remove_unused_css' && $sNewSetting == '1' )
		{
			$this->container->params->set( 'optimizeCssDelivery_enable', '1' );
		}

		if ( $sSetting == 'optimizeCssDelivery_enable' && $sNewSetting == '0' )
		{
			$this->container->params->set( 'pro_remove_unused_css', '0' );
		}

		if ( $sSetting == 'pro_smart_combine' )
		{
			if ( $sNewSetting == '1' )
			{
				$aSCValues = AdminAjax::getInstance( 'SmartCombine' )->run();
				$aValues   = array_merge( $aSCValues->data['css'], $aSCValues->data['js'] );

				$this->container->params->set( 'pro_smart_combine_values', json_encode( $aValues ) );
			}
			else
			{
				$this->container->params->set( 'pro_smart_combine_values', '' );
			}
		}

		$this->container->params->set( $sSetting, $sNewSetting );
		$this->container->params->save();
	}
}