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
use JchOptimize\Core\Admin\Tasks;
use JchOptimize\Platform\Cache;
use JText;
use Joomla\CMS\Router\Route as JRoute;

class Utility extends \FOF40\Controller\Controller
{
	use PredefinedTaskList;

	public function __construct( Container $container, array $config = [] )
	{
		parent::__construct( $container, $config );

		$this->predefinedTaskList = [
			'browsercaching',
			'filepermissions',
			'cleancache',
			'orderplugins',
			'keycache',
			'restoreimages',
			'deletebackups'
		];
	}

	public function browsercaching()
	{
		$expires = Tasks::leverageBrowserCaching();

		if ( $expires === false )
		{
			$this->setMessage( JText::_( 'COM_JCHOPTIMIZE_LEVERAGEBROWSERCACHE_FAILED' ), 'error' );
		}
		elseif ( $expires === 'FILEDOESNTEXIST' )
		{
			$this->setMessage( JText::_( 'COM_JCHOPTIMIZE_LEVERAGEBROWSERCACHE_FILEDOESNTEXIST' ), 'warning' );
		}
		elseif ( $expires === 'CODEUPDATEDSUCCESS' )
		{
			$this->setMessage( JText::_( 'COM_JCHOPTIMIZE_LEVERAGEBROWSERCACHE_CODEUPDATEDSUCCESS' ) );
		}
		elseif ( $expires === 'CODEUPDATEDFAIL' )
		{
			$this->setMessage( JText::_( 'COM_JCHOPTIMIZE_LEVERAGEBROWSERCACHE_CODEUPDATEDFAIL' ), 'notice' );
		}
		else
		{
			$this->setMessage( JText::_( 'COM_JCHOPTIMIZE_LEVERAGEBROWSERCACHE_SUCCESS' ) );
		}

		$this->setRedirect( JRoute::_( 'index.php?option=com_jchoptimize', false ) );

		$this->redirect();
	}

	public function cleancache()
	{
		$deleted = Cache::deleteCache();

		if ( ! $deleted )
		{
			$this->setMessage( JText::_( 'COM_JCHOPTIMIZE_CACHECLEAN_FAILED' ), 'error' );
		}
		else
		{
			$this->setMessage( JText::_( 'COM_JCHOPTIMIZE_CACHECLEAN_SUCCESS' ) );
		}

		$this->setRedirect( JRoute::_( 'index.php?option=com_jchoptimize', false ) );

		$this->redirect();
	}

	public function keycache()
	{
		Tasks::generateNewCacheKey();

		$this->setMessage( JText::_( 'COM_JCHOPTIMIZE_CACHE_KEY_GENERATED' ) );
		$this->setRedirect( JRoute::_( 'index.php?option=com_jchoptimize', false ) );

		$this->redirect();
	}

	public function filepermissions()
	{
		/** @var \JchOptimize\Component\Admin\Model\Utility $oModel */
		$oModel = $this->getModel();
		$result = $oModel->fixFilePermissions();

		if ( $result )
		{
			$this->setMessage( JText::_( 'COM_JCHOPTIMIZE_FIXFILEPERMISSIONS_SUCCESS' ) );
		}
		else
		{
			$this->setMessage( JText::_( 'COM_JCHOPTIMIZE_FIXFILEPERMISSIONS_FAIL' ), 'error' );
		}

		$this->setRedirect( JRoute::_( 'index.php?option=com_jchoptimize', false ) );

		$this->redirect();
	}

	public function orderplugins()
	{
		/** @var \JchOptimize\Component\Admin\Model\Utility $oModel */
		$oModel  = $this->getModel();
		$deleted = $oModel->orderPlugins();

		if ( $saved === false )
		{
			$this->setMessage( JText::_( 'JLIB_APPLICATION_ERROR_REORDER_FAILED' ), 'error' );
		}
		else
		{
			$this->setMessage( JText::_( 'JLIB_APPLICATION_SUCCESS_ORDERING_SAVED' ) );
		}

		$this->setRedirect( JRoute::_( 'index.php?option=com_jchoptimize', false ) );

		$this->redirect();
	}

	public function restoreimages()
	{
		$mResult = Tasks::restoreBackupImages();

		if ( $mResult === 'SOMEIMAGESDIDNTRESTORE' )
		{
			$this->setMessage( JText::_( 'COM_JCHOPTIMIZE_SOMERESTOREIMAGE_FAILED' ), 'warning' );
		}
		elseif ( $mResult === 'BACKUPPATHDOESNTEXIST' )
		{
			$this->setMessage( JText::_( 'COM_JCHOPTIMIZE_BACKUPPATH_DOESNT_EXIST' ), 'warning' );
		}
		else
		{
			$this->setMessage( JText::_( 'COM_JCHOPTIMIZE_RESTOREIMAGE_SUCCESS' ) );
		}

		$this->setRedirect( JRoute::_( 'index.php?option=com_jchoptimize&view=OptimizeImage', false ) );

		$this->redirect();
	}

	public function deletebackups()
	{
		$mResult = Tasks::deleteBackupImages();

		if ( $mResult === false )
		{
			$this->setMessage( JText::_( 'COM_JCHOPTIMIZE_DELETEBACKUPS_FAILED' ), 'error' );
		}
		elseif ( $mResult === 'BACKUPPATHDOESNTEXIST' )
		{
			$this->setMessage( JText::_( 'COM_JCHOPTIMIZE_BACKUPPATH_DOESNT_EXIST' ), 'warning' );
		}
		else
		{
			$this->setMessage( JText::_( 'COM_JCHOPTIMIZE_DELETEBACKUPS_SUCCESS' ) );
		}

		$this->setRedirect( JRoute::_( 'index.php?option=com_jchoptimize&view=OptimizeImage', false ) );

		$this->redirect();
	}
}