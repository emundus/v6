<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\View\Redirections;

defined('_JEXEC') || die;

use Akeeba\AdminTools\Admin\Model\Redirections;
use Akeeba\AdminTools\Admin\View\Mixin\SystemPluginExists;
use FOF40\View\DataView\Html as BaseView;
use Joomla\CMS\Language\Text;

class Html extends BaseView
{
	use SystemPluginExists;

	/**
	 * Is the URL Redirection feature enabled?
	 *
	 * @var  bool
	 */
	public $urlredirection;

	/** @var  string    Order column */
	public $order = 'id';

	/** @var  string Order direction, ASC/DESC */
	public $order_Dir = 'DESC';

	/** @var  array    Sorting order options */
	public $sortFields = [];

	public $filters = [];

	protected function onBeforeBrowse()
	{
		/** @var Redirections $model */
		$model                = $this->getModel();
		$urlredirection       = $model->getRedirectionState();
		$this->urlredirection = $urlredirection;

		$hash = 'admintoolsredirections';

		// ...ordering
		$platform        = $this->container->platform;
		$input           = $this->input;
		$this->order     = $platform->getUserStateFromRequest($hash . 'filter_order', 'filter_order', $input, 'id');
		$this->order_Dir = $platform->getUserStateFromRequest($hash . 'filter_order_Dir', 'filter_order_Dir', $input, 'DESC');

		// ...filter state
		$this->filters['source']     = $platform->getUserStateFromRequest($hash . 'filter_source', 'source', $input);
		$this->filters['dest']       = $platform->getUserStateFromRequest($hash . 'filter_dest', 'dest', $input);
		$this->filters['keepParams'] = $platform->getUserStateFromRequest($hash . 'filter_keepurlparams', 'keepurlparams', $input);
		$this->filters['published']  = $platform->getUserStateFromRequest($hash . 'filter_published', 'published', $input);

		$this->populateSystemPluginExists();

		// Construct the array of sorting fields
		$this->sortFields = [
			'id'        => Text::_('ID'),
			'source'    => Text::_('COM_ADMINTOOLS_LBL_REDIRECTION_SOURCE'),
			'dest'      => Text::_('COM_ADMINTOOLS_LBL_REDIRECTION_DEST'),
			'published' => Text::_('JPUBLISHED'),
		];

		parent::onBeforeBrowse();
	}
}
