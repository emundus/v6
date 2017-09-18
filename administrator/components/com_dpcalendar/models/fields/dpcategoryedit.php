<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JFormHelper::addFieldPath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_categories' . DS . 'models' . DS . 'fields');
JFormHelper::loadFieldClass('categoryedit');

class JFormFieldDPCategoryEdit extends JFormFieldCategoryEdit
{

	public $type = 'DPCategoryEdit';

	protected function getOptions ()
	{
		$calendar = null;
		$id = JFactory::getApplication()->isAdmin() ? 0 : JRequest::getVar('id');
		if (! empty($id))
		{
			$calendar = DPCalendarHelper::getCalendar($this->value);
		}

		$options = array();
		if (empty($calendar) || ! $calendar->external)
		{
			$options = parent::getOptions();
		}

		if (empty($calendar) || $calendar->external)
		{
			JPluginHelper::importPlugin('dpcalendar');
			$tmp = JDispatcher::getInstance()->trigger('onCalendarsFetch',
					array(
							null,
							! empty($calendar->system) ? $calendar->system : null
					));
			if (! empty($tmp))
			{
				foreach ($tmp as $calendars)
				{
					foreach ($calendars as $externalCalendar)
					{
						if (! $externalCalendar->canCreate && ! $externalCalendar->canEdit)
						{
							continue;
						}
						$options[] = JHtml::_('select.option', $externalCalendar->id, '- ' . $externalCalendar->title);
					}
				}
			}
		}

		return $options;
	}
}
