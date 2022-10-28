<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class EventbookingViewLocationHtml extends RADViewItem
{
	protected function prepareView()
	{
		parent::prepareView();

		$options               = [];
		$options[]             = HTMLHelper::_('select.option', '', Text::_('Default Layout'));
		$options[]             = HTMLHelper::_('select.option', 'table', Text::_('Table Layout'));
		$options[]             = HTMLHelper::_('select.option', 'timeline', Text::_('Timeline Layout'));
		$this->lists['layout'] = HTMLHelper::_('select.genericlist', $options, 'layout', ' class="form-select" ', 'value', 'text', $this->item->layout);

		$this->config = EventbookingHelper::getConfig();
	}
}
