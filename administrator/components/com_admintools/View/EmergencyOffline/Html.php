<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\View\EmergencyOffline;

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Model\EmergencyOffline;
use FOF30\View\DataView\Html as BaseView;

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