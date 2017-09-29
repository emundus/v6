<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2017 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class DPCalendarViewBooking extends \DPCalendar\View\BaseView
{

	public function display($tpl = null)
	{
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/models');
		$model = JModelLegacy::getInstance('Booking', 'DPCalendarModel');
		$this->setModel($model, true);

		return parent::display($tpl);
	}

	public function init()
	{
		$app = $this->app;

		if ($this->getLayout() == 'cancel') {
			return parent::init();
		}

		if (in_array($this->getLayout(), array('pay', 'order', 'complete'))) {
			JPluginHelper::importPlugin('dpcalendarpay');

			$this->plugin = $app->input->get('type');
			$this->item   = $this->get('Item');

			if ($this->item->id == null) {
				$this->setError(JText::_('JERROR_ALERTNOAUTHOR'));

				return false;
			}
			$this->tickets = $this->getModel()->getTickets($this->item->id);
		} else {
			$this->item = $this->getModel()->getItem(array('uid' => $app->input->get('uid')));

			if (!$this->item || $this->item->id == null) {
				$user = JFactory::getUser();
				if ($user->guest) {
					JFactory::getApplication()->redirect(
						JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode(JFactory::getURI())),
						JText::_('COM_DPCALENDAR_NOT_LOGGED_IN'), 'warning'
					);

					return false;
				}

				$this->setError(JText::_('JERROR_ALERTNOAUTHOR'));

				return false;
			}
			$this->tickets = $this->getModel()->getTickets($this->item->id);
		}

		$this->item->text = '';
		JFactory::getApplication()->triggerEvent('onContentPrepare', array('com_dpcalendar.booking', &$this->item, &$this->params, 0));

		return parent::init();
	}
}
