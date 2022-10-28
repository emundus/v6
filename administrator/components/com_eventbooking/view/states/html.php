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

class EventbookingViewStatesHtml extends RADViewList
{
	protected function prepareView()
	{
		parent::prepareView();

		$options                          = [];
		$options[]                        = HTMLHelper::_('select.option', 0, ' - ' . Text::_('EB_SELECT_COUNTRY') . ' - ', 'id', 'name');
		$options                          = array_merge($options, EventbookingHelperDatabase::getAllCountries());
		$this->lists['filter_country_id'] = HTMLHelper::_('select.genericlist', $options, 'filter_country_id', ' class="form-select" onchange="submit();" ', 'id', 'name', $this->state->filter_country_id);

		return true;
	}
}
