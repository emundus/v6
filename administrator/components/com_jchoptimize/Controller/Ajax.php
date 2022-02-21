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
use FOF40\Controller\Mixin\PredefinedTaskList;
use JchOptimize\Core\Admin\Ajax\Ajax as AdminAjax;

class Ajax extends Controller
{
	use PredefinedTaskList;

	public function __construct( Container $container, array $config = [] )
	{
		parent::__construct( $container, $config );

		$this->predefinedTaskList = [
			'filetree',
			'multiselect',
			'optimizeimage',
			'smartcombine',
			'garbagecron'
		];
	}

	public function filetree()
	{
		/*$oView = $this->getView();

		$oView->setLayout('filetree');

		$this->display(false);*/
		echo AdminAjax::getInstance( 'FileTree' )->run();

		$this->container->platform->closeApplication();
	}

	public function multiselect()
	{
		echo AdminAjax::getInstance( 'MultiSelect' )->run();

		$this->container->platform->closeApplication();
	}

	public function optimizeimage()
	{
		echo AdminAjax::getInstance( 'OptimizeImage' )->run();

		$this->container->platform->closeApplication();
	}

	public function smartcombine()
	{
		echo AdminAjax::getInstance( 'SmartCombine' )->run();

		$this->container->platform->closeApplication();
	}

	public function garbagecron()
	{
		echo AdminAjax::getInstance( 'GarbageCron' )->run();

		$this->container->platform->closeApplication();
	}
}