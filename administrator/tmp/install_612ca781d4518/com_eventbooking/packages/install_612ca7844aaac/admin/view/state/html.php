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

class EventbookingViewStateHtml extends RADViewItem
{
	protected function prepareView()
	{
		parent::prepareView();

		$options   = [];
		$options[] = HTMLHelper::_('select.option', 0, ' - ' . Text::_('EB_SELECT_COUNTRY') . ' - ', 'id', 'name');
		$options   = array_merge($options, EventbookingHelperDatabase::getAllCountries());

		$this->lists['country_id'] = HTMLHelper::_('select.genericlist', $options, 'country_id', ' class="form-select"', 'id', 'name', $this->item->country_id);
	}
}
