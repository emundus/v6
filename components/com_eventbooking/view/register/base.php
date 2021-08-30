<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class EventbookingViewRegisterBase extends RADViewHtml
{
	/**
	 * Bootstrap helper
	 *
	 * @var \EventbookingHelperBootstrap
	 */
	protected $bootstrapHelper;

	/**
	 * Array contains Html Select List which will be displayed on registration form
	 *
	 * @var array
	 */
	protected $lists = [];

	/**
	 * Messages
	 *
	 * @var RADConfig
	 */
	protected $message;

	/**
	 * Field suffix, use on multilingual website
	 *
	 * @var string
	 */
	protected $fieldSuffix = null;

	/**
	 * Set common data for registration form
	 *
	 * @param RADConfig $config
	 * @param array     $data
	 */
	protected function setCommonViewData($config, &$data, $paymentTypeChange = "showDepositAmount(this);")
	{
		$user        = Factory::getUser();
		$input       = $this->input;
		$paymentType = $input->post->getInt('payment_type', $config->get('default_payment_type', 0));

		if ($user->id && !isset($data['first_name']))
		{
			//Load the name from Joomla default name
			$name = $user->name;

			if ($name)
			{
				$pos = strpos($name, ' ');

				if ($pos !== false)
				{
					$data['first_name'] = substr($name, 0, $pos);
					$data['last_name']  = substr($name, $pos + 1);
				}
				else
				{
					$data['first_name'] = $name;
					$data['last_name']  = '';
				}
			}
		}

		if ($user->id && !isset($data['email']))
		{
			$data['email'] = $user->email;
		}

		if ($config->get('auto_populate_form_data') === '0' && !$this->input->getInt('captcha_invalid'))
		{
			$data = [];
		}

		if (empty($data['country']))
		{
			$data['country'] = $config->default_country;
		}

		$expMonth                 = $input->post->getInt('exp_month', date('m'));
		$expYear                  = $input->post->getInt('exp_year', date('Y'));
		$this->lists['exp_month'] = HTMLHelper::_('select.integerlist', 1, 12, 1, 'exp_month', 'class="input-small form-select d-inline-block w-auto"', $expMonth, '%02d');

		$currentYear             = date('Y');
		$this->lists['exp_year'] = HTMLHelper::_('select.integerlist', $currentYear, $currentYear + 10, 1, 'exp_year', 'class="input-small form-select d-inline-block w-auto"', $expYear);

		$options                  = [];
		$options[]                = HTMLHelper::_('select.option', 'Visa', 'Visa');
		$options[]                = HTMLHelper::_('select.option', 'MasterCard', 'MasterCard');
		$options[]                = HTMLHelper::_('select.option', 'Discover', 'Discover');
		$options[]                = HTMLHelper::_('select.option', 'Amex', 'American Express');
		$this->lists['card_type'] = HTMLHelper::_('select.genericlist', $options, 'card_type', ' class="form-select" ', 'value', 'text');

		$options                     = [];
		$options[]                   = HTMLHelper::_('select.option', 0, Text::_('EB_FULL_PAYMENT'));
		$options[]                   = HTMLHelper::_('select.option', 1, Text::_('EB_DEPOSIT_PAYMENT'));
		$this->lists['payment_type'] = HTMLHelper::_('select.genericlist', $options, 'payment_type', ' class="input-large form-select" onchange="' . $paymentTypeChange . '" ', 'value', 'text',
			$paymentType);

		$this->message     = EventbookingHelper::getMessages();
		$this->fieldSuffix = EventbookingHelper::getFieldSuffix();
	}

	/**
	 * Get ID of terms and conditions article for the given event
	 *
	 * @param EventbookingTableEvent $event
	 * @param RADConfig              $config
	 *
	 * @return int
	 */
	protected function getTermsAndConditionsArticleId($event, $config)
	{
		if ($event->enable_terms_and_conditions != 2)
		{
			$enableTermsAndConditions = $event->enable_terms_and_conditions;
		}
		else
		{
			$enableTermsAndConditions = $config->accept_term;
		}

		if ($enableTermsAndConditions)
		{
			return $event->article_id ?: $config->article_id;
		}

		return 0;
	}
}