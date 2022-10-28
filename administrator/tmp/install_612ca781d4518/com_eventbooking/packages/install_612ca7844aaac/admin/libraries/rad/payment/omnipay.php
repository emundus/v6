<?php
/**
 * Part of the Ossolution Payment Package
 *
 * @copyright  Copyright (C) 2015 - 2016 Ossolution Team. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_LIBRARIES . '/omnipay/vendor/autoload.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Ossolution\Payment\OmnipayPayment;


/**
 * Payment class which use Omnipay payment class for processing payment
 *
 * @since 1.0
 */
class RADPaymentOmnipay extends OmnipayPayment
{
	use RADPaymentCommon;

	/**
	 * Flag to determine whether this payment method has payment processing fee
	 *
	 * @var bool
	 */
	public $paymentFee;


	/**
	 * This method need to be implemented by the payment plugin class. It needs to set url which users will be
	 * redirected to after a successful payment. The url is stored in paymentSuccessUrl property
	 *
	 * @param   int    $id
	 * @param   array  $data
	 *
	 * @return void
	 */
	protected function setPaymentSuccessUrl($id, $data = [])
	{
		$input  = Factory::getApplication()->input;
		$task   = $input->getCmd('task');
		$Itemid = $input->getInt('Itemid', EventbookingHelper::getItemid());

		$row = Table::getInstance('Registrant', 'EventbookingTable');
		$row->load($id);

		if ($task == 'process')
		{
			$this->paymentSuccessUrl = Route::_('index.php?option=com_eventbooking&view=payment&layout=complete&Itemid=' . $Itemid, false);
		}
		else
		{
			$this->paymentSuccessUrl = Route::_('index.php?option=com_eventbooking&view=complete&registration_code=' . $row->registration_code . '&Itemid=' . $Itemid, false);
		}
	}

	/**
	 * This method need to be implemented by the payment plugin class. It needs to set url which users will be
	 * redirected to when the payment is not success for some reasons. The url is stored in paymentFailureUrl property
	 *
	 * @param   int    $id
	 * @param   array  $data
	 *
	 * @return void
	 */
	protected function setPaymentFailureUrl($id, $data = [])
	{
		$input = Factory::getApplication()->input;

		if (empty($id))
		{
			$id = $input->getInt('id', 0);
		}

		$Itemid = $input->getInt('Itemid', EventbookingHelper::getItemid());

		$task = $input->getCmd('task');

		if ($task == 'process')
		{
			$this->paymentFailureUrl = Route::_('index.php?option=com_eventbooking&view=failure&Itemid=' . $Itemid, false, false);
		}
		else
		{
			$this->paymentFailureUrl = Route::_('index.php?option=com_eventbooking&view=failure&id=' . $id . '&Itemid=' . $Itemid, false, false);
		}
	}

	/**
	 * This method need to be implemented by the payment gateway class. It needs to init the JTable order record,
	 * update it with transaction data and then call onPaymentSuccess method to complete the order.
	 *
	 * @param   int     $id
	 * @param   string  $transactionId
	 *
	 * @return mixed
	 */
	protected function onVerifyPaymentSuccess($id, $transactionId)
	{
		$row = Table::getInstance('Registrant', 'EventbookingTable');
		$row->load($id);

		if (!$row->id)
		{
			return false;
		}

		if ($row->published == 1 && $row->payment_status)
		{
			return false;
		}

		$this->onPaymentSuccess($row, $transactionId);
	}

	/**
	 * This method is usually called by payment method class to add additional data
	 * to the request message before that message is actually sent to the payment gateway
	 *
	 * @param   \Omnipay\Common\Message\AbstractRequest  $request
	 * @param   JTable                                   $row
	 * @param   array                                    $data
	 */
	protected function beforeRequestSend($request, $row, $data)
	{
		parent::beforeRequestSend($request, $row, $data);

		// Set return, cancel and notify URL
		$Itemid  = Factory::getApplication()->input->getInt('Itemid', 0);
		$siteUrl = Uri::base();
		$request->setCancelUrl($siteUrl . 'index.php?option=com_eventbooking&task=cancel&id=' . $row->id . '&Itemid=' . $Itemid);
		$request->setReturnUrl($siteUrl . 'index.php?option=com_eventbooking&task=payment_confirm&id=' . $row->id . '&payment_method=' . $this->name . '&Itemid=' . $Itemid);
		$request->setNotifyUrl($siteUrl . 'index.php?option=com_eventbooking&task=payment_confirm&id=' . $row->id . '&payment_method=' . $this->name . '&notify=1&Itemid=' . $Itemid);
		$request->setAmount($data['amount']);
		$request->setCurrency($data['currency']);
		$request->setDescription($data['item_name']);

		if (empty($this->redirectHeading))
		{
			$language    = Factory::getLanguage();
			$languageKey = 'EB_WAIT_' . strtoupper(substr($this->name, 3));

			if ($language->hasKey($languageKey))
			{
				$redirectHeading = Text::_($languageKey);
			}
			else
			{
				$redirectHeading = Text::sprintf('EB_REDIRECT_HEADING', $this->getTitle());
			}

			$this->setRedirectHeading($redirectHeading);
		}
	}
}
