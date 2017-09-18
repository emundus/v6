<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\Registry\Registry;

// TCPDF variables
define('K_TCPDF_EXTERNAL_CONFIG', true);
define('K_TCPDF_THROW_EXCEPTION_ERROR', true);

JLoader::import('joomla.application.component.helper');
JTable::addIncludePath(JPATH_ADMINISTRATOR . 'components/com_dpcalendar/tables');
JLoader::import('components.com_dpcalendar.libraries.vendor.autoload', JPATH_ADMINISTRATOR);

class DPCalendarHelperBooking
{

	/**
	 * Creates a PDF for the given booking and tickets.
	 * If to file is set, then the PDF will be written to a file and the file
	 * name is returned. Otherwise it will be offered as download.
	 *
	 * @param stdClass $booking
	 * @param stdClass $tickets
	 * @param Registry $params
	 * @param string $toFile
	 *
	 * @return string
	 */
	public static function createInvoice($booking, $tickets, $params, $toFile = false)
	{
		try
		{
			$html = JLayoutHelper::render('booking.invoice',
					array(
							'booking' => $booking,
							'tickets' => $tickets,
							'params' => $params
					), null, array(
							'component' => 'com_dpcalendar',
							'client' => 0
					));

			// Disable notices (TCPDF is causing many of these)
			error_reporting(E_ALL ^ E_NOTICE);

			$pdf = new DPCalendarPDF($params);

			// set document information
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor('DPCalendar by joomla.digital-peak.com');
			$pdf->SetTitle($event->title);
			$pdf->SetSubject('DPCalendar Invoice');
			$pdf->SetKeywords('Invoice, DPCalendar, Digital Peak');

			// remove default header/footer
			$pdf->setPrintHeader(true);
			$pdf->setPrintFooter(true);

			// set default monospaced font
			$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

			// set margins
			$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
			$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
			$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

			// Font sizes
			$pdf->setHeaderFont(array(
					$pdf->getFontFamily(),
					'',
					9
			));
			$pdf->setFooterFont(array(
					$pdf->getFontFamily(),
					'',
					9
			));

			// Adding the content
			$pdf->AddPage();
			$pdf->writeHTML($html, true, false, true, false, '');

			$fileName = $booking->uid . '.pdf';
			if ($toFile)
			{
				$fileName = JPATH_ROOT . '/tmp/' . $fileName;
				JFile::delete($fileName);
			}
			$pdf->Output($fileName, $toFile ? 'F' : 'D');
			return $fileName;
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
			return null;
		}
	}

	/**
	 * Creates a PDF for the given ticket.
	 * If to file is set, then the PDF will be written to a file and the file
	 * name is returned. Otherwise it will be offered as download.
	 *
	 * @param stdClass $ticket
	 * @param Registry $params
	 * @param string $toFile
	 *
	 * @return string
	 */
	public static function createTicket($ticket, $params, $toFile = false)
	{
		try
		{
			DPCalendarHelper::increaseMemoryLimit(130 * 1024 * 1024);

			JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_dpcalendar/models');
			$model = JModelLegacy::getInstance('Event', 'DPCalendarModel', array(
					'ignore_request' => true
			));
			$event = $model->getItem($ticket->event_id);

			$html = JLayoutHelper::render('ticket.details', array(
					'ticket' => $ticket,
					'event' => $event,
					'params' => $params
			), null, array(
					'component' => 'com_dpcalendar',
					'client' => 0
			));

			// Disable notices (TCPDF is causing many of these)
			error_reporting(E_ALL ^ E_NOTICE);

			$pdf = new DPCalendarPDF($params);

			// set document information
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor('DPCalendar by joomla.digital-peak.com');
			$pdf->SetTitle($event->title);
			$pdf->SetSubject('DPCalendar Ticket');
			$pdf->SetKeywords('Invoice, DPCalendar, Digital Peak');

			// remove default header/footer
			$pdf->setPrintHeader(true);
			$pdf->setPrintFooter(true);

			// set default monospaced font
			$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

			// set margins
			$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
			$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
			$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

			// Font sizes
			$pdf->setHeaderFont(array(
					$pdf->getFontFamily(),
					'',
					9
			));
			$pdf->setFooterFont(array(
					$pdf->getFontFamily(),
					'',
					9
			));

			// Adding the content
			$pdf->AddPage();
			$pdf->writeHTML($html . '<br/>', true, false, true, false, '');

			$style = array(
					'border' => 2,
					'position' => 'C',
					'vpadding' => 'auto',
					'hpadding' => 'auto',
					'fgcolor' => array(
							0,
							0,
							0
					),
					'bgcolor' => false,
					'module_width' => 1,
					'module_height' => 1
			);
			$pdf->write2DBarcode(DPCalendarHelperRoute::getTicketRoute($ticket, true), 'QRCODE,L', 20, 200, 50, 50, $style, 'N');

			$fileName = $ticket->uid . '.pdf';
			if ($toFile)
			{
				$fileName = JPATH_ROOT . '/tmp/' . $fileName;
				JFile::delete($fileName);
			}
			$pdf->Output($fileName, $toFile ? 'F' : 'D');
			return $fileName;
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
			return null;
		}
	}

	public static function getSeriesEvents($event)
	{
		if (!$event)
		{
			return array();
		}

		$events = array(
				$event->id => $event
		);
		if ($event->original_id != '0')
		{
			$model = JModelLegacy::getInstance('Events', 'DPCalendarModel', array(
					'ignore_request' => true
			));
			if (!$model)
			{
				$model = JModelLegacy::getInstance('AdminEvents', 'DPCalendarModel', array(
						'ignore_request' => true
				));
			}
			$model->getState();
			$model->setState('filter.children', $event->original_id == -1 ? $event->id : $event->original_id);
			$model->setState('list.limit', 10000);
			$model->setState('filter.expand', true);

			$series = $model->getItems();
			foreach ($series as $e)
			{
				if (!DPCalendarHelperBooking::openForBooking($e) || key_exists($e->id, $events))
				{
					continue;
				}
				$events[$e->id] = $e;
			}
		}
		return $events;
	}

	public static function paymentRequired($event)
	{
		if (empty($event))
		{
			return false;
		}
		return $event->price != '0.00' && !empty($event->price);
	}

	public static function openForBooking($event)
	{
		if (!$event || DPCalendarHelper::isFree())
		{
			return false;
		}
		if ($event->capacity !== null && $event->capacity_used >= $event->capacity)
		{
			return false;
		}
		if (DPCalendarHelper::getDate($event->start_date)->format('U') < DPCalendarHelper::getDate()->format('U'))
		{
			return false;
		}
		if (isset($event->booking_closing_date) && $event->booking_closing_date &&
				 DPCalendarHelper::getDate($event->booking_closing_date)->format('U') < DPCalendarHelper::getDate()->format('U'))
		{
			return false;
		}
		$calendar = DPCalendarHelper::getCalendar($event->catid);
		if (!$calendar)
		{
			return false;
		}
		return $calendar->canBook;
	}

	/**
	 * Returns payment information for the given booking from the plugin the
	 * payment
	 * is made to.
	 *
	 * @param stdClass $booking
	 * @param Registry $params
	 * @return string
	 */
	public static function getPaymentStatementFromPlugin($booking, $params = null)
	{
		JPluginHelper::importPlugin('dpcalendarpay');
		$statement = JDispatcher::getInstance()->trigger('onDPPaymentStatement', array(
				$booking
		));

		$buffer = '';
		if ($statement)
		{
			if (!$params)
			{
				$params = JComponentHelper::getParams('com_dpcalendar');
			}

			$vars = (array)$booking;
			$vars['currency'] = DPCalendarHelper::getComponentParameter('currency', 'USD');
			$vars['currencySymbol'] = DPCalendarHelper::getComponentParameter('currency_symbol', '$');
			foreach ($statement as $b)
			{
				if ($b->status && $booking->type = $b->type)
				{
					$buffer .= DPCalendarHelper::renderEvents(array(), $b->statement, $params, $vars);
				}
			}
		}
		return $buffer;
	}

	/**
	 * Returns the discounted price if there are discounts to apply.
	 * If the early bird index is set, only the early bird with that index is
	 * used.
	 * If the user group index is set, only the user group discount with that
	 * index is
	 * used.
	 *
	 * @param decimal $price
	 * @param DPCalendarTableEvent $event
	 * @param integer $earlyBirdIndex
	 * @param integer $userGroupIndex
	 * @return number
	 */
	public static function getPriceWithDiscount($price, $event, $earlyBirdIndex = -1, $userGroupIndex = -1)
	{
		$newPrice = $price;

		$now = DPCalendarHelper::getDate();

		if (is_object($event->earlybird) && isset($event->earlybird->value) && is_array($event->earlybird->value))
		{
			foreach ($event->earlybird->value as $index => $value)
			{
				if ($earlyBirdIndex == -2 || ($earlyBirdIndex >= 0 && $earlyBirdIndex != $index))
				{
					continue;
				}
				$limit = $event->earlybird->date[$index];
				$date = DPCalendarHelper::getDate($event->start_date);
				if (strpos($limit, '-') === 0 || strpos($limit, '+') === 0)
				{
					// Relative date
					$date->modify(str_replace('+', '-', $limit));
				}
				else
				{
					// Absolute date
					$date = DPCalendarHelper::getDate($limit);
				}
				if ($date->format('U') < $now->format('U'))
				{
					continue;
				}

				if ($event->earlybird->type[$index] == 'value')
				{
					$newPrice = $newPrice - $value;
				}
				else
				{
					$newPrice = $newPrice - (($newPrice / 100) * $value);
				}

				if ($newPrice < 0)
				{
					$newPrice = 0;
				}

				break;
			}
		}
		$userGroups = JAccess::getGroupsByUser(JFactory::getUser()->id);
		if (is_object($event->user_discount) && isset($event->user_discount->value) && is_array($event->user_discount->value))
		{
			foreach ($event->user_discount->value as $index => $value)
			{
				if ($userGroupIndex == -2 || ($userGroupIndex >= 0 && $userGroupIndex != $index))
				{
					continue;
				}
				$groups = $event->user_discount->discount_groups[$index];
				if (!array_intersect($userGroups, $groups))
				{
					continue;
				}

				if ($event->user_discount->type[$index] == 'value')
				{
					$newPrice = $newPrice - $value;
				}
				else
				{
					$newPrice = $newPrice - (($newPrice / 100) * $value);
				}

				if ($newPrice < 0)
				{
					$newPrice = 0;
				}

				break;
			}
		}

		return $newPrice;
	}

	public static function getStatusLabel($booking)
	{
		$status = 'COM_DPCALENDAR_BOOKING_FIELD_STATE_UNPUBLISHED';
		switch ($booking->state)
		{
			case 0:
				$status = 'COM_DPCALENDAR_BOOKING_FIELD_STATE_UNPUBLISHED';
				break;
			case 1:
				$status = 'COM_DPCALENDAR_BOOKING_FIELD_STATE_PUBLISHED';
				break;
			case 2:
				$status = 'JARCHIVED';
				break;
			case 3:
				$status = 'COM_DPCALENDAR_BOOKING_FIELD_STATE_NEED_PAYMENT';
				break;
			case 4:
				$status = 'COM_DPCALENDAR_BOOKING_FIELD_STATE_HOLD';
				break;
			case 5:
				$status = 'COM_DPCALENDAR_BOOKING_FIELD_STATE_INVITED';
				break;
			case -2:
				$status = 'JTRASHED';
				break;
		}
		return JText::_($status);
	}
}

class DPCalendarPDF extends TCPDF
{

	private $params;

	public function __construct(Registry $params)
	{
		parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		$this->params = $params;
	}

	// Page header
	public function Header()
	{
		$this->Cell(0, 0, $this->params->get('invoice_header'), 'B', false, 'L', 0, '', 0, false, 'M', 'M');
	}

	// Page footer
	public function Footer()
	{
		$date = DPCalendarHelper::getDate($booking->book_date)->format(
				$this->params->get('event_date_format', 'm.d.Y') . ' ' . $this->params->get('event_time_format', 'g:i a'));
		$this->Cell(30, 0, $date, 'T', false, 'L', 0, '', 0, false, 'T', 'M');
		$this->Cell(120, 0, $this->params->get('invoice_footer'), 'T', false, 'C', 0, '', 0, false, 'T', 'C');
		$this->Cell(0, 0, $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 'T', false, 'R', 0, '', 0, false, 'T', 'M');
	}
}
