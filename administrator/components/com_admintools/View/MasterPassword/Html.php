<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\View\MasterPassword;

defined('_JEXEC') or die;

use FOF30\View\DataView\Html as BaseView;

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
		/** @var \Akeeba\AdminTools\Admin\Model\MasterPassword $model */
		$model          = $this->getModel();
		$this->masterpw = $model->getMasterPassword();
		$this->items    = $model->getItemList();

		$this->addJavascriptFile('admin://components/com_admintools/media/js/MasterPassword.min.js');
	}
}