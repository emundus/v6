<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\View\FixPermissions;

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Model\FixPermissions;
use FOF30\View\DataView\Html as BaseView;

class Html extends BaseView
{
	/**
	 * Do we need to perform more steps?
	 *
	 * @var  bool
	 */
	public $more;

	/**
	 * Percent complete
	 *
	 * @var  int
	 */
	public $percentage;

	protected function onBeforeBrowse()
	{
		/** @var FixPermissions $model */
		$model = $this->getModel();
		$state = $model->getState('scanstate', false);

		$total = $model->totalFolders;
		$done  = $model->doneFolders;

		$percent = 100;
		$more    = false;

		\JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');

		if ($state)
		{
			if ($total > 0)
			{
				$percent = min(max(round(100 * $done / $total), 1), 100);
			}

			$more = true;
		}

		$this->more       = $more;
		$this->percentage = $percent;

		$this->setLayout('default');

		$this->addJavascriptFile('admin://components/com_admintools/media/js/FixPermissions.min.js');
	}

	protected function onBeforeRun()
	{
		$this->onBeforeBrowse();
	}
}