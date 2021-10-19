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
use FOF40\Controller\Mixin\PredefinedTaskList;
use JchOptimize\Core\Admin\Ajax\Ajax as AdminAjax;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Router\Route as JRoute;

class OptimizeImage extends \FOF40\Controller\Controller
{
	use PredefinedTaskList;

	public function __construct( Container $container, array $config = [] )
	{
		parent::__construct( $container, $config );

		$this->predefinedTaskList = [
			'filetree',
			'optimizeimage',
			'default'
		];
	}

	public function filetree()
	{
		echo AdminAjax::getInstance( 'FileTree' )->run();

		$this->container->platform->closeApplication();
	}

	public function optimizeimage()
	{
		$oModel = $this->getModel();
		$oModel->savestate( false );

		$status = $oModel->getState( 'status', null );

		if ( is_null( $status ) )
		{
			echo AdminAjax::getInstance( 'OptimizeImage' )->run();

			$this->container->platform->closeApplication();
		}
		else
		{
			if ( $status == 'success' )
			{
				$dir = rtrim( $oModel->getState( 'dir', '' ), '/' ) . '/';
				$cnt = $oModel->getState( 'cnt', '' );

				$this->setMessage( sprintf( JText::_( '%1$d images optimized in %2$s' ), $cnt, $dir ) );
			}
			else
			{
				$msg = $oModel->getState( 'msg', '' );
				$this->setMessage( JText::_( 'The Optimize Image function failed with message "' . $msg ), 'error' );
			}

			$this->setRedirect( JRoute::_( 'index.php?option=com_jchoptimize&view=OptimizeImage', false ) );
			$this->redirect();
		}
	}

}