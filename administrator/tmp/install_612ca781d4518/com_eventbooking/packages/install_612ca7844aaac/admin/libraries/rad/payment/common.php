<?php
/**
 * Part of the Ossolution Payment Package
 *
 * @copyright  Copyright (C) 2015 - 2016 Ossolution Team. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

trait RADPaymentCommon
{
	/**
	 * Method to check if the payment plugin supports refund payment
	 *
	 * @return bool
	 */
	public function supportRefundPayment()
	{
		return method_exists($this, 'refund');
	}

	/**
	 * Method to check whether we need to show card type on form for this payment method.
	 * Always return false as when use Omnipay, we don't need card type parameter. It can be detected automatically
	 * from given card number
	 *
	 * @return bool|int
	 */
	public function getCardType()
	{
		return 0;
	}

	/**
	 * Method to check whether we need to show card holder name in the form
	 *
	 * @return bool|int
	 */
	public function getCardHolderName()
	{
		return $this->type;
	}

	/**
	 * Method to check whether we need to show card cvv input on form
	 *
	 * @return bool|int
	 */
	public function getCardCvv()
	{
		return $this->type;
	}

	/**
	 * Method to check if this payment method is a CreditCard based payment method
	 *
	 * @return int
	 */
	public function getCreditCard()
	{
		return $this->type;
	}

	/**
	 * Get name of the payment method
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get title of the payment method
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Set title of the payment method
	 *
	 * @param $title String
	 */

	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 *  This method is called when payment for the registration is success, it needs to be used by all payment class
	 *
	 * @param   EventbookingTableRegistrant  $row
	 * @param   string                       $transactionId
	 */
	protected function onPaymentSuccess($row, $transactionId)
	{
		$config = EventbookingHelper::getConfig();

		if ($row->process_deposit_payment)
		{
			$row->payment_processing_fee         += $row->deposit_payment_processing_fee;
			$row->amount                         += $row->deposit_payment_processing_fee;
			$row->deposit_payment_transaction_id = $transactionId;
			$row->payment_status                 = 1;

			$row->store();

			PluginHelper::importPlugin('eventbooking');
			Factory::getApplication()->triggerEvent('onDepositPaymentSuccess', [$row]);
			EventbookingHelper::callOverridableHelperMethod('Mail', 'sendDepositPaymentEmail', [$row, $config]);
		}
		else
		{
			$row->transaction_id = $transactionId;
			$row->payment_date   = gmdate('Y-m-d H:i:s');
			$row->published      = 1;
			$row->store();

			if ($row->is_group_billing)
			{
				EventbookingHelperRegistration::updateGroupRegistrationRecord($row->id);
			}

			PluginHelper::importPlugin('eventbooking');
			Factory::getApplication()->triggerEvent('onAfterPaymentSuccess', [$row]);

			EventbookingHelper::callOverridableHelperMethod('Mail', 'sendEmails', [$row, $config]);
		}
	}
}