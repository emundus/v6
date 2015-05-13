<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsControllerFixperms extends F0FController
{
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->modelName = 'fixperms';
	}

	public function execute($task)
	{
		if ($task != 'run')
		{
			$task = 'browse';
		}
		parent::execute($task);
	}

	public function browse()
	{
		$model = $this->getThisModel();
		$state = $model->startScanning();
		$model->setState('scanstate', $state);

		$this->display(false);
	}

	public function run()
	{
		$model = $this->getThisModel();
		$state = $model->run();
		$model->setState('scanstate', $state);

		$this->display(false);
	}

	protected function onBeforeBrowse()
	{
		return $this->checkACL('admintools.maintenance');
	}

	protected function onBeforeRun()
	{
		return $this->checkACL('admintools.maintenance');
	}
}
