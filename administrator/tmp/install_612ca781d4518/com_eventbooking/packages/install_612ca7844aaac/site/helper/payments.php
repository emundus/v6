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
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

class EventbookingHelperPayments
{
	public static $methods = null;

	/**
	 * Get list of payment methods
	 *
	 * @param $methodIds string
	 *
	 * @return array
	 */
	public static function getPaymentMethods($methodIds = null, $loadOffline = true)
	{
		if (!self::$methods)
		{
			$path  = JPATH_ROOT . '/components/com_eventbooking/payments/';
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*')
				->from('#__eb_payment_plugins')
				->where('published=1')
				->where('`access` IN (' . implode(',', Factory::getUser()->getAuthorisedViewLevels()) . ')')
				->order('ordering');

			if ($methodIds)
			{
				$methodIds = ArrayHelper::toInteger(explode(',', $methodIds));

				if ($methodIds[0] != 0)
				{
					$query->where('id IN (' . implode(',', $methodIds) . ')');
				}
			}

			if (!$loadOffline)
			{
				$query->where('NAME NOT LIKE "os_offline%"');
			}

			$db->setQuery($query);
			$rows = $db->loadObjectList();

			$baseUri = Uri::base(true);

			foreach ($rows as $row)
			{
				if (file_exists($path . $row->name . '.php'))
				{
					require_once $path . $row->name . '.php';

					$params = new Registry($row->params);
					$method = new $row->name($params);
					$method->setTItle($row->title);

					if ($params->get('payment_fee_amount') > 0 || $params->get('payment_fee_percent'))
					{
						$method->paymentFee = true;
					}
					else
					{
						$method->paymentFee = false;
					}

					$iconUri = '';

					if ($icon = $params->get('icon'))
					{
						if (file_exists(JPATH_ROOT . '/media/com_eventbooking/assets/images/paymentmethods/' . $icon))
						{
							$iconUri = $baseUri . '/media/com_eventbooking/assets/images/paymentmethods/' . $icon;
						}
						elseif (file_exists(JPATH_ROOT . '/' . $icon))
						{
							$iconUri = $baseUri . '/' . $icon;
						}
					}

					$method->iconUri = $iconUri;

					self::$methods[] = $method;
				}
			}
		}

		return self::$methods;
	}

	/**
	 * Write the javascript objects to show the page
	 *
	 * @return string
	 */
	public static function writeJavascriptObjects()
	{
		$methods  = static::getPaymentMethods();
		$jsString = " methods = new PaymentMethods();\n";

		if (count($methods))
		{
			foreach ($methods as $method)
			{
				$jsString .= " method = new PaymentMethod('" . $method->getName() . "'," . $method->getCreditCard() . "," . $method->getCardType() . "," . $method->getCardCvv() . "," . $method->getCardHolderName() . ");\n";
				$jsString .= " methods.Add(method);\n";
			}
		}

		Factory::getDocument()->addScriptDeclaration($jsString);
	}

	/**
	 * Load information about the payment method
	 *
	 * @param   string  $name  Name of the payment method
	 *
	 * @return object
	 */
	public static function loadPaymentMethod($name)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_payment_plugins')
			->where('name = ' . $db->quote($name));
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Get default payment gateway
	 *
	 * @param $methodIds   string Ids of the available payment method
	 * @param $loadOffline bool   Load offline payment method or not
	 *
	 * @return string
	 */
	public static function getDefautPaymentMethod($methodIds = null, $loadOffline = true)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('name')
			->from('#__eb_payment_plugins')
			->where('published=1')
			->where('`access` IN (' . implode(',', Factory::getUser()->getAuthorisedViewLevels()) . ')')
			->order('ordering');

		if ($methodIds)
		{
			$methodIds = ArrayHelper::toInteger(explode(',', $methodIds));

			if ($methodIds[0] != 0)
			{
				$query->where('id IN (' . implode(',', $methodIds) . ')');
			}
		}

		if (!$loadOffline)
		{
			$query->where('NAME NOT LIKE "os_offline%"');
		}

		$db->setQuery($query, 0, 1);

		return $db->loadResult();
	}

	/**
	 * Get the payment method object based on it's name
	 *
	 * @param   string  $name
	 *
	 * @return object
	 */
	public static function getPaymentMethod($name)
	{
		$methods = static::getPaymentMethods();

		foreach ($methods as $method)
		{
			if ($method->getName() == $name)
			{
				return $method;
			}
		}

		return;
	}
}