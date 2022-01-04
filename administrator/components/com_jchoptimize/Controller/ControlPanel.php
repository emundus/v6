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

namespace JchOptimize\Component\Admin\Controller;

defined( '_JEXEC' ) or die( 'Restricted Access' );

use FOF40\Container\Container;
use FOF40\Controller\Controller;

class ControlPanel extends Controller
{
	public function __construct( Container $container, array $config = [] )
	{
		parent::__construct( $container, $config );
	}

	protected function onBeforeDefault()
	{
		/** @var \JchOptimize\Component\Admin\Model\Updates $oUpdatesModel */
		$oUpdatesModel = $this->getModel( 'Updates' );
		$oUpdatesModel->refreshUpdateSite();
	}
}