<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2018 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Omnipay\Omnipay;

if (!JLoader::import('components.com_dpcalendar.helpers.dpcalendar', JPATH_ADMINISTRATOR)) {
	return;
}

class PlgDPCalendarPayManual extends \DPCalendar\Plugin\PaymentPlugin
{

	/**
	 *
	 * @return \Omnipay\Manual\Gateway
	 */
	protected function getPaymentGateway()
	{
		$gateway = Omnipay::create('Manual');

		return $gateway;
	}

	protected function getPaymentData($gateway, $transactionData, $booking)
	{
		$updates = array(
			'state'          => 4,
			'payer_email'    => $booking->email,
			'transaction_id' => md5($booking->id . 'dpcalendar')
		);

		return $updates;
	}

	public function onDPPaymentCallBack($bookingmethod, $data)
	{
		$return = parent::onDPPaymentCallBack($bookingmethod, $data);

		if ($return) {
			$text = \DPCalendar\Helper\DPCalendarHelper::getStringFromParams(
				'payment_statement',
				'PLG_DPCALENDARPAY_MANUAL_PAYMENT_STATEMENT_TEXT',
				$this->params
			);

			$booking = \JModelLegacy::getInstance('Booking', 'DPCalendarModel')->getItem($data['b_id']);

			// The vars for the message
			$vars                   = (array)$booking;
			$vars['currency']       = \DPCalendar\Helper\DPCalendarHelper::getComponentParameter('currency', 'USD');
			$vars['currencySymbol'] = \DPCalendar\Helper\DPCalendarHelper::getComponentParameter('currency_symbol', '$');

			$text = DPCalendarHelper::renderEvents(array(), $text, null, $vars);

			$mailer = JFactory::getMailer();
			$mailer->setSubject(JText::_('PLG_DPCALENDARPAY_MANUAL_CONFIRMATION_SUBJECT'));
			$mailer->setBody($text);
			$mailer->IsHTML(true);
			$mailer->addRecipient($booking->email);
			$mailer->Send();
		}

		return $return;
	}
}
