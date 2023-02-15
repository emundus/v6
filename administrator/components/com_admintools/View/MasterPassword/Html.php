<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\View\MasterPassword;

defined('_JEXEC') || die;

use Akeeba\AdminTools\Admin\Model\MasterPassword;
use FOF40\View\DataView\Html as BaseView;

class Html extends BaseView
{
	/**
	 * Current master password
	 *
	 * @var  string
	 */
	public $masterpw;

	/**
	 * List of views that could be password-protected
	 *
	 * @var  array
	 */
	public $items;

	public function onBeforeMain()
	{
		/** @var MasterPassword $model */
		$model          = $this->getModel();
		$this->masterpw = $model->getMasterPassword();
		$this->items    = $model->getItemList();

		$this->addJavascriptFile('admin://components/com_admintools/media/js/MasterPassword.min.js', $this->container->mediaVersion, 'text/javascript', true);
	}
}
