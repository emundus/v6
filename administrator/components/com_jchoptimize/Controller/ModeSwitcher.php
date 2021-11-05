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

class ModeSwitcher extends Controller
{
	use PredefinedTaskList;

	public function __construct( Container $container, array $config = [] )
	{
		parent::__construct( $container, $config );

		$this->predefinedTaskList = [
			'setProduction',
			'setDevelopment'
		];
	}

	public function setProduction()
	{
		/** @var \JchOptimize\Component\Admin\Model\ModeSwitcher $oModel */
		$oModel = $this->getModel();
		$oModel->setProduction();

		$this->setMessage( 'JCH Optimize set in production mode' );
		$sReturnUrl = base64_decode( $oModel->getState( 'return' ) );
		$this->setRedirect( $sReturnUrl );

		$this->redirect();
	}

	public function setDevelopment()
	{
		/** @var \JchOptimize\Component\Admin\Model\ModeSwitcher $oModel */
		$oModel = $this->getModel();
		$oModel->setDevelopment();

		$this->setMessage( 'JCH Optimize set in development mode', 'warning' );
		$sReturnUrl = base64_decode( $oModel->getState( 'return' ) );
		$this->setRedirect( $sReturnUrl );

		$this->redirect();
	}
}