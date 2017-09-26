<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();


class DPCalendarViewMap extends \DPCalendar\View\BaseView
{
	public function init()
	{
		$model = JModelLegacy::getInstance('Calendar', 'DPCalendarModel', array(
				'ignore_request' => true
		));
		// Initialise variables
		$model->setState('filter.parentIds', $this->params->get('ids', array(
				'root'
		)));
		$model->setState('category.recursive', true);

		$items = $model->getItems();

		if ($items === false)
		{
			return JError::raiseError(404, JText::_('JGLOBAL_CATEGORY_NOT_FOUND'));
		}

		$this->items = $items;
	}
}
