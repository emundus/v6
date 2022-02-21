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
use Joomla\CMS\Router\Route as JRoute;

class Configure extends \FOF40\Controller\Controller
{
	use PredefinedTaskList;

	public function __construct( Container $container, array $config = [] )
	{
		parent::__construct( $container, $config );

		$this->predefinedTaskList = [
			'applyAutoSetting',
			'toggleSetting'
		];
	}

	public function applyAutoSetting()
	{
		/** @var \JchOptimize\Component\Admin\Model\Configure $oModel */
		$oModel = $this->getModel();
		$oModel->applyAutoSettings();

		$this->setMessage('Settings saved');
		$this->setRedirect(JRoute::_('index.php?option=com_jchoptimize', false));

		$this->redirect();
	}

	public function toggleSetting()
	{
		/** @var \JchOptimize\Component\Admin\Model\Configure $oModel */
		$oModel = $this->getModel();
		$oModel->toggleSetting();

		$this->setMessage( 'Settings saved');
		$this->setRedirect(JRoute::_('index.php?option=com_jchoptimize', false));

		$this->redirect();
	}
}