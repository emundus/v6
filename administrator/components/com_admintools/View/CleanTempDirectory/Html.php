<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\View\CleanTempDirectory;

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Model\CleanTempDirectory;
use FOF30\View\DataView\Html as BaseView;

class Html extends BaseView
{
	/**
	 * Do we have more processing to do?
	 *
	 * @var  bool
	 */
	public $more;

	/**
	 * Percentage complete, 0 to 100
	 *
	 * @var  int
	 */
	public $percentage;

	public function onBeforeBrowse()
	{
		/** @var CleanTempDirectory $model */
		$model = $this->getModel();
		$state = $model->getState('scanstate', false);

		$total   = max(1, $model->totalFolders);
		$done    = $model->doneFolders;
		$percent = 100;
		$more    = false;

		if ($state)
		{
			$more = true;

			if ($total > 0)
			{
				$percent = min(max(round(100 * $done / $total), 1), 100);
			}
		}

		$this->more       = $more;
		$this->percentage = $percent;

		$this->setLayout('default');

		$this->addJavascriptFile('admin://components/com_admintools/media/js/CleanTempDirectory.min.js');
	}
}