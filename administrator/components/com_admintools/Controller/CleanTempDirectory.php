<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

defined('_JEXEC') || die;

use Akeeba\AdminTools\Admin\Controller\Mixin\CustomACL;
use Akeeba\AdminTools\Admin\Controller\Mixin\PredefinedTaskList;
use FOF40\Container\Container;
use FOF40\Controller\Controller;

class CleanTempDirectory extends Controller
{
	use PredefinedTaskList, CustomACL;

	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->predefinedTaskList = ['browse', 'run'];
	}

	public function browse()
	{
		/** @var \Akeeba\AdminTools\Admin\Model\CleanTempDirectory $model */
		$model = $this->getModel();
		$state = $model->startScanning();
		$model->setState('scanstate', $state);

		$this->display(false);
	}

	public function run()
	{
		/** @var \Akeeba\AdminTools\Admin\Model\CleanTempDirectory $model */
		$model = $this->getModel();
		$state = $model->run();
		$model->setState('scanstate', $state);

		$this->display(false);
	}
}
