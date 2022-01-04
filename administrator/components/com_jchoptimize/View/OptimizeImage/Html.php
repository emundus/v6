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

namespace JchOptimize\Component\Admin\View\OptimizeImage;

defined( '_JEXEC' ) or die();

use JchOptimize\Component\Admin\Helper\OptimizeImage;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route as JRoute;

include_once JPATH_ADMINISTRATOR . '/components/com_jchoptimize/version.php';

class Html extends \FOF40\View\DataView\Html
{

	protected function onBeforeMain()
	{
		$this->addCssFile( 'media://com_jchoptimize/css/admin.css', JCH_VERSION );
		$this->addCssFile( 'media://com_jchoptimize/css/admin-joomla.css', JCH_VERSION );
		$this->addCssFile( 'media://com_jchoptimize/css/jquery.filetree.css', JCH_VERSION );

		HTMLHelper::_( 'jquery.framework' );

		$this->addJavascriptFile( 'media://com_jchoptimize/js/jquery.filetree.js', JCH_VERSION );

		$ajax_filetree = JRoute::_( 'index.php?option=com_jchoptimize&view=OptimizeImage&task=filetree', false );

		$sScript = <<<JS
		
jQuery(document).ready( function() {
	jQuery("#file-tree-container").fileTree({
		root: "",
		script: "$ajax_filetree",
		expandSpeed: 100,
		collapseSpeed: 100,
		multiFolder: false
	}, function(file) {});
});
JS;
		$this->addJavascriptInline( $sScript );

		if ( JCH_PRO )
		{
			OptimizeImage::loadResources( $this );
		}

		$aOptions = [
			'trigger'   => 'hover focus',
			'placement' => 'bottom'
		];

		HTMLHelper::_( 'bootstrap.popover', '.hasPopover', $aOptions );
	}
}
