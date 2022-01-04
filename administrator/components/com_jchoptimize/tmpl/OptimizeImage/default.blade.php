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

defined( '_JEXEC' ) or die( 'Restricted Access' );

use JchOptimize\Core\Admin\Icons;
use JchOptimize\Platform\Utility;
use Joomla\CMS\Router\Route as JRoute;

/** @var \JchOptimize\Component\Admin\View\OptimizeImage\Html $this */

$page = JRoute::_( 'index.php?option=com_jchoptimize&view=OptimizeImage&task=optimizeimage', false, JRoute::TLS_IGNORE, true );

$aAutoOptimize = [
	[
		'link'    => '',
		'icon'    => 'auto_optimize.png',
		'name'    => Utility::translate( 'Optimize Images' ),
		'script'  => 'onclick="jchIOptimizeApi.optimizeImages(\'' . $page . '\', \'auto\'); return false;"',
		'id'      => 'auto-optimize-images',
		'class'   => '',
		'proonly' => true
	]
];

$aManualOptimize = [
	[
		'link'    => '',
		'icon'    => 'manual_optimize.png',
		'name'    => Utility::translate( 'Optimize Images' ),
		'script'  => 'onclick="jchIOptimizeApi.optimizeImages(\'' . $page . '\', \'manual\'); return false;"',
		'id'      => 'manual-optimize-images',
		'class'   => '',
		'proonly' => true
	]
];
?>
<div class="jch-admin">
    <div class="row">
        <div class="optimize-image-panels">
            <div id="api2-utilities-block" class="admin-panel-block">
                <h4>@lang('COM_JCHOPTIMIZE_API2_UTILITY_SETTING')</h4>
                <p class="alert alert-info">@lang('COM_JCHOPTIMIZE_API2_UTILITY_SETTING_DESC')</p>
                <div class="icons-container">
                    {{Icons::printIconsHTML(Icons::compileUtilityIcons(Icons::getApi2utilityArray()))}}
                </div>
            </div>
            <div id="auto-optimize-block" class="admin-panel-block">
                <h4>@lang('COM_JCHOPTIMIZE_AUTO_OPTIMIZE_IMAGE')</h4>
                <p class="alert alert-info">@lang('COM_JCHOPTIMIZE_AUTO_OPTIMIZE_IMAGE_DESC')</p>
                <div class="icons-container">
                    {{Icons::printIconsHTML($aAutoOptimize)}}
                </div>
            </div>
            <div id="manual-optimize-block" class="admin-panel-block">
                <div id="api2-optimize-images-container"></div>
                <div id="optimize-images-container" class="">
                    <h4>@lang('COM_JCHOPTIMIZE_MANUAL_OPTIMIZE_IMAGE')</h4>
                    <p class="alert alert-info">@lang('COM_JCHOPTIMIZE_MANUAL_OPTIMIZE_IMAGE_DESC')</p>
                    <div id="file-tree-container" class=""></div>
                    <div id="files-container" class=""></div>
                    <div class="icons-container">
                        <div class="">{{Icons::printIconsHTML($aManualOptimize)}}</div>
                    </div>
                    <div style="clear:both"></div>
                </div>
            </div>
        </div>
    </div>
</div>