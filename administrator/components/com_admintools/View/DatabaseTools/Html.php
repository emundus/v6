<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\View\DatabaseTools;

defined('_JEXEC') || die;

use FOF40\View\DataView\Html as BaseView;

class Html extends BaseView
{
	/**
	 * Table being processed
	 *
	 * @var  string
	 */
	public $table;

	/**
	 * Percent complete
	 *
	 * @var  int
	 */
	public $percent;

	protected function onBeforeBrowse()
	{
		$model     = $this->getModel();
		$lastTable = $model->getState('lasttable', '');
		$percent   = $model->getState('percent', '');

		$this->table   = $lastTable;
		$this->percent = $percent;

		$this->setLayout('optimize');

		$this->addJavascriptFile('admin://components/com_admintools/media/js/DatabaseTools.min.js', $this->container->mediaVersion, 'text/javascript', true);
	}
}
