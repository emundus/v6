<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

defined('_JEXEC') || die;

use Akeeba\AdminTools\Admin\Controller\Mixin\CustomACL;
use Akeeba\AdminTools\Admin\Controller\Mixin\PredefinedTaskList;
use FOF40\Container\Container;
use FOF40\Controller\Controller;
use Joomla\CMS\Language\Text;

class DatabaseTools extends Controller
{
	use PredefinedTaskList, CustomACL;

	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->predefinedTaskList = ['browse', 'purgesessions'];
	}

	public function browse()
	{
		/** @var \Akeeba\AdminTools\Admin\Model\DatabaseTools $model */
		$model = $this->getModel();
		$from  = $this->input->getString('from', null);

		$tables    = (array) $model->findTables();
		$lastTable = $model->repairAndOptimise($from);

		if (empty($lastTable))
		{
			$percent = 100;
		}
		else
		{
			$lastTableID = array_search($lastTable, $tables);
			$percent     = round(100 * ($lastTableID + 1) / count($tables));

			if ($percent < 1)
			{
				$percent = 1;
			}

			if ($percent > 100)
			{
				$percent = 100;
			}
		}

		$model->setState('lasttable', $lastTable);
		$model->setState('percent', $percent);

		$this->display(false);
	}

	public function purgesessions()
	{
		/** @var \Akeeba\AdminTools\Admin\Model\DatabaseTools $model */
		$model = $this->getModel();
		$model->garbageCollectSessions();
		$model->purgeSessions();
		$this->setRedirect('index.php?option=com_admintools', Text::_('COM_ADMINTOOLS_LBL_DATABASETOOLS_PURGESESSIONS_COMPLETE'));
	}
}
