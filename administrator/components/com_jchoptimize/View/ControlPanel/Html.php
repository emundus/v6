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

namespace JchOptimize\Component\Admin\View\ControlPanel;

defined( '_JEXEC' ) or die();

use JchOptimize\Platform\Cache;
use JchOptimize\Platform\Paths;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route as JRoute;

include_once JPATH_ADMINISTRATOR . '/components/com_jchoptimize/version.php';

class Html extends \FOF40\View\DataView\Html
{
	protected function onBeforeMain()
	{
		$this->getCacheSize();

		$this->addCssFile( 'media://com_jchoptimize/css/admin.css', JCH_VERSION );
		$this->addCssFile( 'media://com_jchoptimize/css/admin-joomla.css', JCH_VERSION );
		$this->addCssFile( '//netdna.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.css' );

		$this->addJavascriptFile( 'media://com_jchoptimize/js/admin-joomla-comp.js', JCH_VERSION );

		$javascript = 'let configure_url = \'' . JRoute::_( 'index.php?option=com_jchoptimize&view=Configure', false, JROUTE::TLS_IGNORE, true ) . '\';';
		$this->addJavascriptInline( $javascript );

		$aOptions = [
			'trigger'   => 'hover focus',
			'placement' => 'bottom'
		];

		HTMLHelper::_( 'bootstrap.popover', '.hasPopover', $aOptions );
	}

	protected function getCacheSize()
	{
		$size     = 0;
		$no_files = 0;

		$cache_path = JPATH_SITE . '/cache/' . Cache::$sCacheGroup;
		$oModel     = $this->getModel();
		$oModel->getCacheSize( $cache_path, $size, $no_files );

		$cache_path = Paths::cachePath( false ) . '/css';
		$oModel->getCacheSize( $cache_path, $size, $no_files );

		$cache_path = Paths::cachePath( false ) . '/js';
		$oModel->getCacheSize( $cache_path, $size, $no_files );

		$decimals = 2;
		$sz       = 'BKMGTP';
		$factor   = (int)floor( ( strlen( $size ) - 1 ) / 3 );

		$this->size     = sprintf( "%.{$decimals}f", $size / pow( 1024, $factor ) ) . $sz[ $factor ];
		$this->no_files = number_format( $no_files );
	}
}
