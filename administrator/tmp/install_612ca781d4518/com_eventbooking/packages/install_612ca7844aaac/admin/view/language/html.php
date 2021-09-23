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
use Joomla\CMS\Toolbar\ToolbarHelper;

class EventbookingViewLanguageHtml extends RADViewHtml
{
	/**
	 * All language items
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * Model state
	 *
	 * @var RADModelState
	 */
	protected $state;

	public function display()
	{
		$this->state = $this->model->getState();
		$languages   = $this->model->getSiteLanguages();
		$options     = [];
		$options[]   = HTMLHelper::_('select.option', '', Text::_('Select Language'));

		foreach ($languages as $language)
		{
			$options[] = HTMLHelper::_('select.option', $language, $language);
		}

		$lists['filter_language'] = HTMLHelper::_('select.genericlist', $options, 'filter_language', ' class="form-select"  onchange="submit();" ', 'value', 'text', $this->state->filter_language);

		$options              = [];
		$options[]            = HTMLHelper::_('select.option', 'com_eventbooking', Text::_('EB_FRONT_END_LANGUAGE'));
		$options[]            = HTMLHelper::_('select.option', 'admin.com_eventbooking', Text::_('EB_BACK_END_LANGUAGE'));
		$lists['filter_item'] = HTMLHelper::_('select.genericlist', $options, 'filter_item', ' class="form-select"  onchange="submit();" ', 'value', 'text', $this->state->filter_item);

		$this->items = $this->model->getData();
		$this->lists = $lists;

		$this->addToolbar();

		parent::display();
	}

	protected function addToolbar()
	{
		ToolbarHelper::title(Text::_('Translation Management'), 'generic.png');
		ToolbarHelper::addNew('new_item', 'New Item');
		ToolbarHelper::apply('apply', 'JTOOLBAR_APPLY');
		ToolbarHelper::save('save');
		ToolbarHelper::cancel('cancel');
	}
}
