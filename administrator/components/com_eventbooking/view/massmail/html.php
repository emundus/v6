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

class EventbookingViewMassmailHtml extends RADViewHtml
{
	public function display()
	{
		$config = EventbookingHelper::getConfig();

		$options   = [];
		$options[] = HTMLHelper::_('select.option', -1, Text::_('EB_DEFAULT_STATUS'));
		$options[] = HTMLHelper::_('select.option', 0, Text::_('EB_PENDING'));
		$options[] = HTMLHelper::_('select.option', 1, Text::_('EB_PAID'));

		if ($config->activate_waitinglist_feature)
		{
			$options[] = HTMLHelper::_('select.option', 3, Text::_('EB_WAITING_LIST'));
		}

		$options[] = HTMLHelper::_('select.option', 2, Text::_('EB_CANCELLED'));

		$lists['published'] = HTMLHelper::_('select.genericlist', $options, 'published', 'class="form-select input-xlarge"', 'value', 'text', $this->input->getInt('published', -1));
		$lists['event_id']  = EventbookingHelperHtml::getEventsDropdown(EventbookingHelperDatabase::getAllEvents(), 'event_id', 'class="form-select input-xlarge"');

		$this->lists   = $lists;
		$this->config  = $config;
		$this->message = EventbookingHelper::getMessages();

		parent::display();
	}
}
