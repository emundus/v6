<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('components.com_dpcalendar.libraries.dpcalendar.view', JPATH_SITE);

class DPCalendarViewForm extends DPCalendarView
{

	public function init ()
	{
		JPluginHelper::importPlugin('dpcalendar');

		JFactory::getLanguage()->load('', JPATH_ADMINISTRATOR);

		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		$this->item = $this->get('Item');
		$this->form = $this->get('Form');
		$this->return_page = $this->get('ReturnPage');
		$this->user = $user;

		JForm::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/files/');

		$authorised = true;
		if (empty($this->item->id))
		{
			$tmp = JDispatcher::getInstance()->trigger('onCalendarsFetch', array(
					null,
					'cd'
			));
			$authorised = DPCalendarHelper::canCreateEvent() || ! empty($tmp);
		}

		if ($authorised !== true)
		{
			JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
			return false;
		}

		$requestParams = JRequest::getVar('jform', array());
		if (key_exists('start_date', $requestParams))
		{
			$this->form->setFieldAttribute('start_date', 'filter', null);
			$this->form->setFieldAttribute('start_date', 'formated', true);
			$this->form->setValue('start_date', null,
					$requestParams['start_date'] . (key_exists('start_date_time', $requestParams) ? ' ' . $requestParams['start_date_time'] : ''));
		}
		if (key_exists('end_date', $requestParams))
		{
			$this->form->setFieldAttribute('end_date', 'filter', null);
			$this->form->setFieldAttribute('end_date', 'formated', true);
			$this->form->setValue('end_date', null,
					$requestParams['end_date'] . (key_exists('end_date_time', $requestParams) ? ' ' . $requestParams['end_date_time'] : ''));
		}

		if (key_exists('title', $requestParams))
		{
			$this->form->setValue('title', null, $requestParams['title']);
		}
		if (key_exists('catid', $requestParams))
		{
			$this->form->setValue('catid', null, $requestParams['catid']);
		}

		$this->freeInformationText = '';
		if (DPCalendarHelper::isFree())
		{
			$this->freeInformationText = '<br/><small class="text-warning">' . JText::_('COM_DPCALENDAR_ONLY_AVAILABLE_SUBSCRIBERS') . '</small>';
		}
	}
}
