<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Controller\Mixin\CustomACL;
use Akeeba\AdminTools\Admin\Controller\Mixin\PredefinedTaskList;
use FOF30\Container\Container;
use FOF30\Controller\Controller;

class FixPermissions extends Controller
{
	use PredefinedTaskList, CustomACL;

	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->predefinedTaskList = ['browse', 'run'];
	}

	public function browse()
	{
		/** @var \Akeeba\AdminTools\Admin\Model\FixPermissions $model */
		$model = $this->getModel();
		$state = $model->startScanning();
		$model->setState('scanstate', $state);

		$this->display(false);
	}

	public function run()
	{
		/** @var \Akeeba\AdminTools\Admin\Model\FixPermissions $model */
		$model = $this->getModel();
		$state = $model->run();
		$model->setState('scanstate', $state);

		$this->display(false);
	}
}