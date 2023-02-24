<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\View\TempSuperUsers;

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\AdminTools\Admin\Model\TempSuperUsers;
use Akeeba\AdminTools\Admin\View\Mixin\SystemPluginExists;
use FOF40\View\DataView\Html as BaseView;
use Joomla\CMS\Language\Text;

class Html extends BaseView
{
	use SystemPluginExists;

	/** @var  string    Order column */
	public $order = 'user_id';

	/** @var  string Order direction, ASC/DESC */
	public $order_Dir = 'DESC';

	/** @var  array    Sorting order options */
	public $sortFields = [];

	public $filters = [];

	public $userInfo = [];

	protected function onBeforeBrowse()
	{
		$this->populateSystemPluginExists();

		parent::onBeforeBrowse();

		$hash = 'admintoolstempsupers';

		// ...ordering
		$platform        = $this->container->platform;
		$input           = $this->input;
		$this->order     = $platform->getUserStateFromRequest($hash . 'filter_order', 'filter_order', $input, 'expiration');
		$this->order_Dir = $platform->getUserStateFromRequest($hash . 'filter_order_Dir', 'filter_order_Dir', $input, 'ASC');

		// ...filter state
		$this->filters['username'] = $platform->getUserStateFromRequest($hash . 'filter_username', 'username', $input);

		// Construct the array of sorting fields
		$this->sortFields = [
			'user_id'    => Text::_('COM_ADMINTOOLS_TEMPSUPERUSERS_FIELD_USER_ID'),
			'expiration' => Text::_('COM_ADMINTOOLS_TEMPSUPERUSERS_FIELD_EXPIRATION'),
		];
	}

	protected function onBeforeAdd()
	{
		/** @var TempSuperUsers $model */
		$model          = $this->getModel();
		$this->layout   = 'wizard';
		$this->userInfo = $model->getNewUserData();

		parent::onBeforeAdd();
	}
}
