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

namespace JchOptimize\Component\Admin\Toolbar;

defined( '_JEXEC' ) or die( 'Restricted Access' );

use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper as JToolbarHelper;

class Toolbar extends \FOF40\Toolbar\Toolbar
{
	public function onOptimizeImages()
	{
		$this->onControlPanels();
	}

	public function onControlPanels()
	{
		$this->renderSubMenu();

		$option = $this->container->componentName;

		JToolbarHelper::title( Text::_( JCH_PRO ? 'COM_JCHOPTIMIZE_PRO' : 'COM_JCHOPTIMIZE' ), 'dashboard' );
		JToolbarHelper::preferences( $option );
	}

	public function renderSubMenu(): void
	{
		$views = [
			'ControlPanel',
			'OptimizeImage'
		];

		$activeView = $this->container->input->getCmd( 'view', 'ControlPanel' );

		foreach ( $views as $view )
		{
			$link   = 'index.php?option=' . $this->container->componentName . '&view=' . $view;
			$active = $view === $activeView;
			$name   = Text::_( 'COM_JCHOPTIMIZE_TOOLBAR_LABEL_' . strtoupper( $view ) );

			if ( $view == 'ControlPanel' )
			{
				$link = 'index.php?option=' . $this->container->componentName;
			}

			$this->appendLink( $name, $link, $active );
		}
	}
}