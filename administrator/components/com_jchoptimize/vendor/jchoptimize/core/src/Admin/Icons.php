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

namespace JchOptimize\Core\Admin;

use JchOptimize\Platform\Paths;
use JchOptimize\Platform\Plugin;
use JchOptimize\Platform\Utility;

defined( '_JCH_EXEC' ) or die( 'Restricted access' );

class Icons
{

	/**
	 *
	 * @param   array  $aButtons
	 *
	 * @return string
	 * @deprecated
	 */
	public static function generateIcons( $aButtons )
	{
		$sField = '<div class="container-icons clearfix">';

		foreach ( $aButtons as $sButton )
		{
			$tooltip = isset( $sButton['tooltip'] ) ? 'class="hasTooltip" title="' . $sButton['tooltip'] . '"' : '';
			$sField  .= <<<JFIELD
<div class="icon {$sButton['class']}">
        <a class="btn" href="{$sButton['link']}"  {$sButton['script']}  >
                <div style="text-align: center;">
                        <i class="fa {$sButton['icon']} fa-3x" style="margin: 7px 0; color: {$sButton['color']}"></i>
                </div>
                <label {$tooltip}>
                        {$sButton['text']}
                </label><br>
                <i id="toggle" class="fa"></i>
        </a>
</div>
JFIELD;
		}

		$sField .= '</div>';

		return $sField;
	}

	public static function printIconsHTML( $aButtons )
	{
		$sIconsHTML = '';

		foreach ( $aButtons as $aButton )
		{
			$sContentAttr = version_compare( JVERSION, '3.99.99', '<' ) ? 'data-content' : 'data-bs-content';
			$sTooltip     = @$aButton['tooltip'] ? " class=\"hasPopover fig-caption\" title=\"{$aButton['name']}\" {$sContentAttr}=\"{$aButton['tooltip']}\" " : ' class="fig-caption"';
			$sIconSrc     = Paths::relIconPath() . '/' . $aButton['icon'];
			$sToggle      = '<i id="toggle" class="fa"></i>';

			if ( ! JCH_PRO && ! empty ( $aButton['proonly'] ) )
			{
				$aButton['link']   = '';
				$aButton['script'] = '';
				$aButton['class']  = 'disabled proonly';
				$sToggle           = '<span id="proonly-span"><em>(Pro Only)</em></span>';
			}

			$sIconsHTML .= <<<HTML
<figure id="{$aButton['id']}" class="icon {$aButton['class']}">
	<a href="{$aButton['link']}" class="btn" {$aButton['script']}>
		<img src="{$sIconSrc}" alt="" width="50" height="50" />
		<span{$sTooltip}>{$aButton['name']}</span>
		{$sToggle}
	</a>
</figure>

HTML;
		}

		return $sIconsHTML;
	}

	/**
	 *
	 * @return array
	 */
	public static function getSettingsIcons()
	{
		$aButtons = array();

		$aButtons[0]['link']   = '';
		$aButtons[0]['icon']   = 'fa-wrench';
		$aButtons[0]['text']   = 'Minimum';
		$aButtons[0]['color']  = '#FFA319';
		$aButtons[0]['script'] = 'onclick="applyAutoSettings(1, 0); return false;"';
		$aButtons[0]['class']  = 'enabled settings-1';

		$aButtons[1]['link']   = '';
		$aButtons[1]['icon']   = 'fa-cog';
		$aButtons[1]['text']   = 'Intermediate';
		$aButtons[1]['color']  = '#FF32C7';
		$aButtons[1]['script'] = 'onclick="applyAutoSettings(2, 0); return false;"';
		$aButtons[1]['class']  = 'enabled settings-2';

		$aButtons[2]['link']   = '';
		$aButtons[2]['icon']   = 'fa-cogs';
		$aButtons[2]['text']   = 'Average';
		$aButtons[2]['color']  = '#CE3813';
		$aButtons[2]['script'] = 'onclick="applyAutoSettings(3, 0); return false;"';
		$aButtons[2]['class']  = 'enabled settings-3';

		$aButtons[3]['link']   = '';
		$aButtons[3]['icon']   = 'fa-forward';
		$aButtons[3]['text']   = 'Deluxe';
		$aButtons[3]['color']  = '#E8CE0B';
		$aButtons[3]['script'] = 'onclick="applyAutoSettings(4, 2); return false;"';
		$aButtons[3]['class']  = 'enabled settings-4';

		$aButtons[4]['link']   = '';
		$aButtons[4]['icon']   = 'fa-fast-forward';
		$aButtons[4]['text']   = 'Premium';
		$aButtons[4]['color']  = '#9995FF';
		$aButtons[4]['script'] = 'onclick="applyAutoSettings(5, 1); return false;"';
		$aButtons[4]['class']  = 'enabled settings-5';

		$aButtons[5]['link']   = '';
		$aButtons[5]['icon']   = 'fa-dashboard';
		$aButtons[5]['text']   = 'Optimum';
		$aButtons[5]['color']  = '#60AF2C';
		$aButtons[5]['script'] = 'onclick="applyAutoSettings(6, 1); return false;"';
		$aButtons[5]['class']  = 'enabled settings-6';

		return $aButtons;
	}

	public static function getAutoSettingsArray()
	{
		return [
			[
				'name'    => 'Minimum',
				'icon'    => 'minimum.png',
				'setting' => 1,

			],
			[
				'name'    => 'Intermediate',
				'icon'    => 'intermediate.png',
				'setting' => 2
			],
			[
				'name'    => 'Average',
				'icon'    => 'average.png',
				'setting' => 3
			],
			[
				'name'    => 'Deluxe',
				'icon'    => 'deluxe.png',
				'setting' => 4
			],
			[
				'name'    => 'Premium',
				'icon'    => 'premium.png',
				'setting' => 5
			],
			[
				'name'    => 'Optimum',
				'icon'    => 'optimum.png',
				'setting' => 6
			]
		];
	}

	public static function compileAutoSettingsIcons( $aSettings )
	{
		$aButtons = array();

		for ( $i = 0; $i < count( $aSettings ); $i++ )
		{
			$aButtons[ $i ]['link']   = '';
			$aButtons[ $i ]['icon']   = $aSettings[ $i ]['icon'];
			$aButtons[ $i ]['name']   = $aSettings[ $i ]['name'];
			$aButtons[ $i ]['script'] = 'onclick="applyAutoSettings(' . $aSettings[ $i ]['setting'] . ', 1); return false;"';
			$aButtons[ $i ]['id']     = strtolower( str_replace( ' ', '-', trim( $aSettings[ $i ]['name'] ) ) );
			$aButtons[ $i ]['class']  = 'disabled';
		}

		$oParams             = Plugin::getPluginParams();
		$sCombineFilesEnable = $oParams->get( 'combine_files_enable', '0' );
		$aParamsArray        = $oParams->toArray();

		$aAutoSettings = self::autoSettingsArrayMap();

		$aAutoSettingsInit = array_map( function ( $a ) {
			return '0';
		}, $aAutoSettings );

		$aCurrentAutoSettings = array_intersect_key( $aParamsArray, $aAutoSettingsInit );
		//order array
		$aCurrentAutoSettings = array_merge( $aAutoSettingsInit, $aCurrentAutoSettings );

		if ( $sCombineFilesEnable )
		{
			for ( $j = 0; $j < 6; $j++ )
			{
				if ( array_values( $aCurrentAutoSettings ) === array_column( $aAutoSettings, 's' . ( $j + 1 ) ) )
				{
					$aButtons[ $j ]['class'] = 'enabled';

					break;
				}
			}
		}

		return $aButtons;
	}

	public static function autoSettingsArrayMap()
	{
		return [
			'css'                  => [ 's1' => '1', 's2' => '1', 's3' => '1', 's4' => '1', 's5' => '1', 's6' => '1' ],
			'javascript'           => [ 's1' => '1', 's2' => '1', 's3' => '1', 's4' => '1', 's5' => '1', 's6' => '1' ],
			'gzip'                 => [ 's1' => '0', 's2' => '1', 's3' => '1', 's4' => '1', 's5' => '1', 's6' => '1' ],
			'css_minify'           => [ 's1' => '0', 's2' => '1', 's3' => '1', 's4' => '1', 's5' => '1', 's6' => '1' ],
			'js_minify'            => [ 's1' => '0', 's2' => '1', 's3' => '1', 's4' => '1', 's5' => '1', 's6' => '1' ],
			'html_minify'          => [ 's1' => '0', 's2' => '1', 's3' => '1', 's4' => '1', 's5' => '1', 's6' => '1' ],
			'includeAllExtensions' => [ 's1' => '0', 's2' => '0', 's3' => '1', 's4' => '1', 's5' => '1', 's6' => '1' ],
			'replaceImports'       => [ 's1' => '0', 's2' => '0', 's3' => '0', 's4' => '1', 's5' => '1', 's6' => '1' ],
			'phpAndExternal'       => [ 's1' => '0', 's2' => '0', 's3' => '0', 's4' => '1', 's5' => '1', 's6' => '1' ],
			'inlineStyle'          => [ 's1' => '0', 's2' => '0', 's3' => '0', 's4' => '1', 's5' => '1', 's6' => '1' ],
			'inlineScripts'        => [ 's1' => '0', 's2' => '0', 's3' => '0', 's4' => '1', 's5' => '1', 's6' => '1' ],
			'bottom_js'            => [ 's1' => '0', 's2' => '0', 's3' => '0', 's4' => '0', 's5' => '1', 's6' => '1' ],
			'loadAsynchronous'     => [ 's1' => '0', 's2' => '0', 's3' => '0', 's4' => '0', 's5' => '0', 's6' => '1' ]
		];
	}

	/**
	 *
	 * @return array
	 */
	public static function getUtilityIcons()
	{
		$aButtons = array();

		$aButtons[1]['link']    = Paths::adminController( 'browsercaching' );
		$aButtons[1]['icon']    = 'fa-globe';
		$aButtons[1]['color']   = '#51A351';
		$aButtons[1]['text']    = Utility::translate( 'Optimize .htaccess' );
		$aButtons[1]['script']  = '';
		$aButtons[1]['class']   = 'enabled';
		$aButtons[1]['tooltip'] = Utility::translate( 'Use this button to add codes to your htaccess file to enable leverage browser caching and gzip compression.' );

		$aButtons[3]['link']    = Paths::adminController( 'filepermissions' );
		$aButtons[3]['icon']    = 'fa-file-text';
		$aButtons[3]['color']   = '#166BEC';
		$aButtons[3]['text']    = Utility::translate( 'Fix file permissions' );
		$aButtons[3]['script']  = '';
		$aButtons[3]['class']   = 'enabled';
		$aButtons[3]['tooltip'] = Utility::translate( "If your site has lost CSS formatting after enabling the plugin, the problem could be that the plugin files were installed with incorrect file permissions so the browser cannot access the cached combined file. Click here to correct the plugin's file permissions." );

		$aButtons[5]['link']    = Paths::adminController( 'cleancache' );
		$aButtons[5]['icon']    = 'fa-times-circle';
		$aButtons[5]['color']   = '#C0110A';
		$aButtons[5]['text']    = Utility::translate( 'Clean Cache' );
		$aButtons[5]['script']  = '';
		$aButtons[5]['class']   = 'enabled';
		$aButtons[5]['tooltip'] = Utility::translate( 'Click this button to clean the plugin\'s cache and page cache. If you have edited any CSS or javascript files you need to clean the cache so the changes can be visible.' );

		return $aButtons;
	}

	/**
	 * @return array
	 * @deprecated
	 */
	public static function getApi2UtilitiesIcons()
	{
		$aButtons = array();

		$aButtons[0]['link']    = Paths::adminController( 'restoreimages' );
		$aButtons[0]['icon']    = 'fa-undo';
		$aButtons[0]['color']   = '#51A351';
		$aButtons[0]['text']    = Utility::translate( 'Restore original images' );
		$aButtons[0]['script']  = '';
		$aButtons[0]['class']   = 'enabled';
		$aButtons[0]['tooltip'] = Utility::translate( 'If you\'re not satisfied with the images that were optimized you can restore the original ones by clicking this button if they were not deleted.' );

		$aButtons[1]['link']    = Paths::adminController( 'deletebackups' );
		$aButtons[1]['icon']    = 'fa-trash';
		$aButtons[1]['color']   = '#166BEC';
		$aButtons[1]['text']    = Utility::translate( 'Delete backup images' );
		$aButtons[1]['script']  = '';
		$aButtons[1]['class']   = 'enabled';
		$aButtons[1]['tooltip'] = Utility::translate( 'This will permanently delete the images that were backed up. There\'s no way to undo this so be sure you\'re satisfied with the ones that were optimized.' );

		return $aButtons;
	}

	public static function getApi2UtilityArray()
	{
		return self::getUtilityArray( [ 'restoreimages', 'deletebackups' ] );
	}

	public static function getUtilityArray( $aActions = array() )
	{
		$aUtilities = [
			( $action = 'browsercaching' )  => [
				'action'  => $action,
				'icon'    => 'browser_caching.png',
				'name'    => 'Optimize .htaccess',
				'tooltip' => Utility::translate( 'Use this button to add codes to your htaccess file to enable leverage browser caching and gzip compression.' )
			],
			( $action = 'filepermissions' ) => [
				'action'  => $action,
				'icon'    => 'file_permissions.png',
				'name'    => 'Fix file permissions',
				'tooltip' => Utility::translate( "If your site has lost CSS formatting after enabling the plugin, the problem could be that the plugin files were installed with incorrect file permissions so the browser cannot access the cached combined file. Click here to correct the plugin's file permissions." )
			],
			( $action = 'cleancache' )      => [
				'action'  => $action,
				'icon'    => 'clean_cache.png',
				'name'    => 'Clean Cache',
				'tooltip' => Utility::translate( "Click this button to clean the plugin's cache and page cache. If you have edited any CSS or javascript files you need to clean the cache so the changes can be visible." )
			],
			( $action = 'orderplugins' )    => [
				'action'  => $action,
				'icon'    => 'order_plugin.png',
				'name'    => 'Order Plugin',
				'tooltip' => Utility::translate( 'The published order of the plugin is important! When you click on this icon, it will attempt to order the plugin correctly.' )
			],
			( $action = 'keycache' )        => [
				'action'  => $action,
				'icon'    => 'keycache.png',
				'name'    => 'Generate new cache key',
				'tooltip' => Utility::translate( "If you've made any changes to your files generate a new cache key to counter browser caching of the old content." )
			],
			( $action = 'restoreimages' )   => [
				'action'  => $action,
				'icon'    => 'restoreimages.png',
				'name'    => 'Restore Original Images,',
				'tooltip' => Utility::translate( "If you're not satisfied with the images that were optimized you can restore the original ones by clicking this button if they were not deleted. This will also remove any webp image created from the restored file." ),
				'proonly' => true
			],
			( $action = 'deletebackups' )   => [
				'action'  => $action,
				'icon'    => 'deletebackups.png',
				'name'    => 'Delete Backup Images',
				'tooltip' => Utility::translate( "This will permanently delete the images that were backed up. There's no way to undo this so be sure you're satisfied with the ones that were optimized before clicking this button." ),
				'proonly' => true
			]

		];

		if ( empty( $aActions ) )
		{
			return $aUtilities;
		}
		else
		{
			return array_intersect_key( $aUtilities, array_flip( $aActions ) );
		}
	}

	public static function compileUtilityIcons( $aUtilities )
	{
		$aIcons = [];
		$i      = 0;

		foreach ( $aUtilities as $aUtility )
		{
			$aIcons[ $i ]['link']    = Paths::adminController( $aUtility['action'] );
			$aIcons[ $i ]['icon']    = $aUtility['icon'];
			$aIcons[ $i ]['name']    = Utility::translate( $aUtility['name'] );
			$aIcons[ $i ]['id']      = strtolower( str_replace( ' ', '-', trim( $aUtility['name'] ) ) );
			$aIcons[ $i ]['tooltip'] = @$aUtility['tooltip'] ?: false;
			$aIcons[ $i ]['script']  = '';
			$aIcons[ $i ]['class']   = '';
			$aIcons[ $i ]['proonly'] = @$aUtility['proonly'] ?: false;

			$i++;
		}

		return $aIcons;
	}

	public static function getToggleSettings()
	{
		$oParams = Plugin::getPluginParams();

		$aSettings = [
			[
				'name'    => 'Add Image Attributes',
				'setting' => ( $setting = 'img_attributes_enable' ),
				'icon'    => 'img_attributes.png',
				'enabled' => $oParams->get( $setting, '0' )
			],
			[
				'name'    => 'Sprite Generator',
				'setting' => ( $setting = 'csg_enable' ),
				'icon'    => 'sprite_gen.png',
				'enabled' => $oParams->get( $setting, '0' )
			],
			[
				'name'    => 'Http/2 Push',
				'setting' => ( $setting = 'http2_push_enable' ),
				'icon'    => 'http2_push.png',
				'enabled' => $oParams->get( $setting, '0' )
			],
			[
				'name'    => 'Lazy Load Images',
				'setting' => ( $setting = 'lazyload_enable' ),
				'icon'    => 'lazyload.png',
				'enabled' => $oParams->get( $setting, '0' )
			],
			[
				'name'    => 'Optimize CSS Delivery',
				'setting' => ( $setting = 'optimizeCssDelivery_enable' ),
				'icon'    => 'optimize_css_delivery.png',
				'enabled' => $oParams->get( $setting, '0' )
			],
			[
				'name'    => 'Optimize Google Fonts',
				'setting' => ( $setting = 'pro_optimize_gfont_enable' ),
				'icon'    => 'optimize_gfont.png',
				'enabled' => $oParams->get( $setting, '0' ),
				'proonly' => true
			],
			[
				'name'    => 'CDN',
				'setting' => ( $setting = 'cookielessdomain_enable' ),
				'icon'    => 'cdn.png',
				'enabled' => $oParams->get( $setting, '0' )
			],
			[
				'name'    => 'Smart Combine',
				'setting' => ( $setting = 'pro_smart_combine' ),
				'icon'    => 'smart_combine.png',
				'enabled' => $oParams->get( $setting, '0' ),
				'proonly' => true
			]
		];

		return $aSettings;
	}

	public static function getCombineFilesEnableSetting()
	{
		$oParams = Plugin::getPluginParams();

		return [
			[
				'name'    => 'Combine Files Enable',
				'setting' => ( $setting = 'combine_files_enable' ),
				'icon'    => 'combine_files_enable.png',
				'enabled' => $oParams->get( $setting, '1' )
			]
		];
	}

	public static function getAdvancedToggleSettings()
	{
		$oParams = Plugin::getPluginParams();

		$aSettings = [
			[
				'name'    => 'Remove Unused CSS',
				'setting' => ( $setting = 'pro_remove_unused_css' ),
				'icon'    => 'remove_unused_css.png',
				'enabled' => $oParams->get( $setting, '0' ),
				'proonly' => true
			],
			[
				'name'    => 'Reduce DOM',
				'setting' => ( $setting = 'pro_reduce_dom' ),
				'icon'    => 'reduce_dom.png',
				'enabled' => $oParams->get( $setting, '0' ),
				'proonly' => true
			],
	/*		[
				'name'    => 'Remove Unused Javascript',
				'setting' => ( $setting = 'pro_remove_unused_js_enable' ),
				'icon'    => 'remove_unused_js.png',
				'enabled' => $oParams->get( $setting, '0' ),
				'proonly' => true
			]   */
		];

		return $aSettings;
	}

	public static function compileToggleFeaturesIcons( $aSettings )
	{
		$aButtons = array();

		for ( $i = 0; $i < count( $aSettings ); $i++ )
		{
			$aButtons[ $i ]['link']    = '';
			$aButtons[ $i ]['icon']    = $aSettings[ $i ]['icon'];
			$aButtons[ $i ]['name']    = Utility::translate( $aSettings[ $i ]['name'] );
			$aButtons[ $i ]['script']  = 'onclick="toggleSetting(\'' . $aSettings[ $i ]['setting'] . '\'); return false;"';
			$aButtons[ $i ]['id']      = strtolower( str_replace( ' ', '-', trim( $aSettings[ $i ]['name'] ) ) );
			$aButtons[ $i ]['class']   = $aSettings[ $i ]['enabled'] ? 'enabled' : 'disabled';
			$aButtons[ $i ]['proonly'] = ! empty( $aSettings[ $i ]['proonly'] ) ? true : false;
		}

		return $aButtons;
	}
}