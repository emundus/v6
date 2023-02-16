<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\View\EmergencyOffline;

defined('_JEXEC') || die;

use Akeeba\AdminTools\Admin\Model\EmergencyOffline;
use FOF40\View\DataView\Html as BaseView;

class Html extends BaseView
{
	/** @var    bool    Is the site currently offline? */
	public $offline;

	/** @var  string    Htaccess contents */
	public $htaccess;

	public function onBeforeMain()
	{
		/** @var EmergencyOffline $model */
		$model = $this->getModel();

		$this->offline  = $model->isOffline();
		$this->htaccess = $model->getHtaccess();

		$this->setLayout('default');
	}
}
