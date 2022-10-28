<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class EventbookingHelperCreditcard
{
	/**
	 * Validate this credit card. If the card is invalid, InvalidArgumentException is thrown.
	 *
	 * This method is called internally by gateways to avoid wasting time with an API call
	 * when the credit card is clearly invalid.
	 *
	 * Generally if you want to validate the credit card yourself with custom error
	 * messages, you should use your framework's validation library, not this method.
	 *
	 * @return void
	 * @throws InvalidArgumentException
	 *
	 */
	public static function validateCard($cardNumber, $expMonth, $expYear)
	{
		if (static::getExpiryDate('Ym', $expMonth, $expYear) < gmdate('Ym'))
		{
			throw new InvalidArgumentException('Card has expired');
		}

		if (!static::validateLuhn($cardNumber))
		{
			throw new InvalidArgumentException('Card number is invalid');
		}

		if (!preg_match('/^\d{12,19}$/i', $cardNumber))
		{
			throw new InvalidArgumentException('Card number should have 12 to 19 digits');
		}
	}

	/**
	 * Validate a card number according to the Luhn algorithm.
	 *
	 * @param   string  $number  The card number to validate
	 *
	 * @return boolean True if the supplied card number is valid
	 */
	public static function validateLuhn($number)
	{
		$str = '';
		foreach (array_reverse(str_split($number)) as $i => $c)
		{
			$str .= $i % 2 ? $c * 2 : $c;
		}

		return array_sum(str_split($str)) % 10 === 0;
	}

	/**
	 * Credit Card Type
	 *
	 * Iterates through known/supported card brands to determine the brand of this card
	 *
	 * @param   string  $cardNumber
	 *
	 * @return string
	 */
	public static function getCardType($cardNumber)
	{
		$supportedCardTypes = [
			'visa'               => '/^4\d{12}(\d{3})?$/',
			'mastercard'         => '/^(5[1-5]\d{4}|677189)\d{10}$/',
			'discover'           => '/^(6011|65\d{2}|64[4-9]\d)\d{12}|(62\d{14})$/',
			'amex'               => '/^3[47]\d{13}$/',
			'diners_club'        => '/^3(0[0-5]|[68]\d)\d{11}$/',
			'jcb'                => '/^35(28|29|[3-8]\d)\d{12}$/',
			'switch'             => '/^6759\d{12}(\d{2,3})?$/',
			'solo'               => '/^6767\d{12}(\d{2,3})?$/',
			'dankort'            => '/^5019\d{12}$/',
			'maestro'            => '/^(5[06-8]|6\d)\d{10,17}$/',
			'forbrugsforeningen' => '/^600722\d{10}$/',
			'laser'              => '/^(6304|6706|6709|6771(?!89))\d{8}(\d{4}|\d{6,7})?$/',
		];

		foreach ($supportedCardTypes as $brand => $val)
		{
			if (preg_match($val, $cardNumber))
			{
				return $brand;
			}
		}

		return;
	}

	/**
	 * Get the card expiry date, using the specified date format string.
	 *
	 * @param   string  $format
	 * @param   int     $expMonth
	 * @param   int     $expYear
	 *
	 * @return string
	 */
	public static function getExpiryDate($format, $expMonth, $expYear)
	{
		return gmdate($format, gmmktime(0, 0, 0, $expMonth, 1, $expYear));
	}
}
