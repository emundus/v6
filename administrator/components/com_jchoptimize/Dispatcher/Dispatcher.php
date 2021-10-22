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

namespace JchOptimize\Component\Admin\Dispatcher;

defined( '_JEXEC' ) or die( 'Restricted Access' );

use FOF40\Container\Container;
use FOF40\Dispatcher\Mixin\ViewAliases;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

class Dispatcher extends \FOF40\Dispatcher\Dispatcher
{
	use ViewAliases
	{
		onBeforeDispatch as onBeforeDispatchViewAliases;
	}

	public $defaultView = 'ControlPanel';

	public function __construct( Container $container, array $config = [] )
	{
		parent::__construct( $container, $config );

		$this->viewNameAliases = [
			'cpanel' => 'ControlPanel',
		];
	}

	public function onBeforeDispatch(): bool
	{
		$this->container->rendererClass = '\\FOF40\\Render\\Joomla3';

		$this->container->renderer->setOptions( [
			'load_fef'      => 0,
			'fef_reset'     => false,
			'fef_dark'      => 0,
			'linkbar_style' => 'classic'
		] );

		@include_once( $this->container->backEndPath . '/autoload.php' );
		@include_once( $this->container->backEndPath . '/version.php' );

		if ( ! defined( 'JCH_DEBUG' ) )
		{
			define( 'JCH_DEBUG', ( $this->container->params->get( 'debug', 0 ) && JDEBUG ) );
		}

		return true;
	}

	protected function onAfterDispatch()
	{
		if ( ! PluginHelper::isEnabled('system', 'jchoptimize') )
		{
			$sEditUrl = Route::_( 'index.php?option=com_plugins&view=plugins&filter[folder]=system&filter[search]=JCH Optimize', false );
			$sMsg     = 'The JCH Optimize Pro plugin needs to be enabled for the component to work. <a href="' . $sEditUrl . '" target="_blank" >Click here to enable the plugin.</a>';
			Factory::getApplication()->enqueueMessage( $sMsg, 'warning' );
		}

		return true;
	}
}