<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsControllerDbtools extends F0FController
{
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->modelName = 'dbtools';
	}

	public function execute($task)
	{
		if (!in_array($task, array('purgesessions')))
		{
			$task = 'browse';
		}
		parent::execute($task);
	}

	public function browse()
	{
		$model = $this->getThisModel();
		$from = $this->input->getString('from', null);

		$tables = (array)$model->findTables();
		$lastTable = $model->repairAndOptimise($from);
		if (empty($lastTable))
		{
			$percent = 100;
		}
		else
		{
			$lastTableID = array_search($lastTable, $tables);
			$percent = round(100 * ($lastTableID + 1) / count($tables));
			if ($percent < 1)
			{
				$percent = 1;
			}
			if ($percent > 100)
			{
				$percent = 100;
			}
		}

		$this->getThisView()->table = $lastTable;
		$this->getThisView()->percent = $percent;

		$model->setState('lasttable', $lastTable);
		$model->setState('percent', $percent);

		$this->display(false);
	}

	public function purgesessions()
	{
		$model = $this->getThisModel();
		$model->purgeSessions();
		$this->setRedirect('index.php?option=com_admintools', JText::_('ATOOLS_LBL_PURGECOMPLETE'));
	}

	protected function onBeforeBrowse()
	{
		return $this->checkACL('admintools.maintenance');
	}

	protected function onBeforePurgesessions()
	{
		return $this->checkACL('admintools.maintenance');
	}
}
