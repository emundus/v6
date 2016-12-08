<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\View\Redirections;

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Model\Redirections;
use Akeeba\AdminTools\Admin\View\Mixin\SystemPluginExists;
use FOF30\View\DataView\Form as BaseView;

class Form extends BaseView
{
	use SystemPluginExists;

	/**
	 * Is the URL Redirection feature enabled?
	 *
	 * @var  bool
	 */
	public $urlredirection;

	protected function onBeforeBrowse()
	{
		/** @var Redirections $model */
		$model                = $this->getModel();
		$urlredirection       = $model->getRedirectionState();
		$this->urlredirection = $urlredirection;

		$this->populateSystemPluginExists();

		parent::onBeforeBrowse();
	}
}