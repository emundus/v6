<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class EventbookingViewCategoryHtml extends RADViewItem
{
	protected function prepareView()
	{
		parent::prepareView();

		Factory::getDocument()->addScript(Uri::base(true) . '/components/com_eventbooking/assets/js/colorpicker/jscolor.js');

		$options               = [];
		$options[]             = HTMLHelper::_('select.option', '', Text::_('Default Layout'));
		$options[]             = HTMLHelper::_('select.option', 'table', Text::_('Table Layout'));
		$options[]             = HTMLHelper::_('select.option', 'timeline', Text::_('Timeline Layout'));
		$options[]             = HTMLHelper::_('select.option', 'columns', Text::_('Columns Layout'));
		$this->lists['layout'] = HTMLHelper::_('select.genericlist', $options, 'layout', 'class="form-select"', 'value', 'text', $this->item->layout);

		$this->lists['submit_event_access'] = HTMLHelper::_('access.level', 'submit_event_access', $this->item->submit_event_access, 'class="form-select"', false);
		$this->lists['parent']              = EventbookingHelperHtml::buildCategoryDropdown($this->item->parent, 'parent', 'class="form-select"');
	}
}
