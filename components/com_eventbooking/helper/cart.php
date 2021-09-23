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

class EventbookingHelperCart
{
	public function __construct()
	{
		$session = Factory::getSession();
		$cart    = $session->get('eb_cart');

		if ($cart == null)
		{
			$cart = ['items' => [], 'quantities' => []];
			$session->set('eb_cart', $cart);
		}
	}

	/**
	 * Add an item to the cart
	 *
	 * @param   int  $id
	 */
	public function add($id)
	{
		$config     = EventbookingHelper::getConfig();
		$session    = Factory::getSession();
		$cart       = $session->get('eb_cart');
		$quantities = $cart['quantities'];
		$items      = $cart['items'];

		if (!in_array($id, $items))
		{
			array_push($items, $id);
			array_push($quantities, 1);
		}
		else
		{
			//Find the id
			for ($i = 0, $n = count($items); $i < $n; $i++)
			{
				if ($items[$i] == $i)
				{
					if ($config->prevent_duplicate_registration == 1)
					{
						$quantities[$i] = 1;
					}
					else
					{
						$event                = EventbookingHelperDatabase::getEvent($id);
						$capacityCheck        = !$event->event_capacity || (($event->event_capacity - $event->total_registrants) > $quantities[$i]);
						$maxGroupMembersCheck = !$event->max_group_number || ($event->max_group_number < $quantities[$i]);

						if ($capacityCheck && $maxGroupMembersCheck)
						{
							$quantities[$i] += 1;
						}

					}
					break;
				}
			}
		}

		$cart['items']      = $items;
		$cart['quantities'] = $quantities;
		$session->set('eb_cart', $cart);
	}

	/**
	 * Add several events into shopping cart
	 *
	 * @param   array  $cid
	 */
	public function addEvents($cid)
	{
		if (EventbookingHelper::isMethodOverridden('EventbookingHelperOverrideCart', 'addEvents'))
		{
			EventbookingHelperOverrideCart::addEvents($cid);

			return;
		}

		$config     = EventbookingHelper::getConfig();
		$session    = Factory::getSession();
		$cart       = $session->get('eb_cart');
		$quantities = $cart['quantities'];
		$items      = $cart['items'];

		if (count($cid))
		{
			foreach ($cid as $id)
			{
				if (!in_array($id, $items))
				{
					array_push($items, $id);
					array_push($quantities, 1);
				}
				else
				{
					//Find the id
					for ($i = 0, $n = count($items); $i < $n; $i++)
					{
						if ($items[$i] == $id)
						{
							$event = EventbookingHelperDatabase::getEvent($id);

							if ($event->prevent_duplicate_registration === '')
							{
								$preventDuplicateRegistration = $config->prevent_duplicate_registration;
							}
							else
							{
								$preventDuplicateRegistration = $event->prevent_duplicate_registration;
							}

							if ($preventDuplicateRegistration)
							{
								$quantities[$i] = 1;
							}
							else
							{
								$capacityCheck        = !$event->event_capacity || (($event->event_capacity - $event->total_registrants) > $quantities[$i]);
								$maxGroupMembersCheck = !$event->max_group_number || ($event->max_group_number < $quantities[$i]);

								if ($capacityCheck && $maxGroupMembersCheck)
								{
									$quantities[$i] += 1;
								}
							}

							break;
						}
					}
				}
			} //End Foreach
		}

		$cart['items']      = $items;
		$cart['quantities'] = $quantities;
		$session->set('eb_cart', $cart);
	}

	/**
	 * Remove an item from shopping cart
	 *
	 * @param   int  $id
	 */
	public function remove($id)
	{
		$session       = Factory::getSession();
		$cart          = $session->get('eb_cart');
		$items         = $cart['items'];
		$quantities    = $cart['quantities'];
		$newItems      = [];
		$newQuantities = [];

		for ($i = 0, $n = count($items); $i < $n; $i++)
		{
			if ($items[$i] != $id)
			{
				$newItems[]      = $items[$i];
				$newQuantities[] = $quantities[$i];
			}
		}

		$cart['items']      = $newItems;
		$cart['quantities'] = $newQuantities;
		$session->set('eb_cart', $cart);
	}

	/**
	 * Reset the cart
	 */
	public function reset()
	{
		$session = Factory::getSession();
		$cart    = ['items' => [], 'quantities' => []];
		$session->set('eb_cart', $cart);
	}

	/**
	 * Get all items from cart
	 * @return array
	 */
	public function getItems()
	{
		$session = Factory::getSession();
		$cart    = $session->get('eb_cart');

		if (isset($cart['items']))
		{
			return $cart['items'];
		}

		return [];
	}

	/**
	 * Get quantities
	 * @return array
	 */
	public function getQuantities()
	{
		$session = Factory::getSession();
		$cart    = $session->get('eb_cart');

		if (isset($cart['quantities']))
		{
			return $cart['quantities'];
		}

		return [];
	}

	/**
	 * Get item count
	 *
	 * @return int
	 */
	public function getCount()
	{
		$session = Factory::getSession();
		$cart    = $session->get('eb_cart');

		if (isset($cart['items']))
		{
			return count($cart['items']);
		}

		return 0;
	}

	/**
	 * Update cart with new quantities
	 *
	 * @param   array  $eventIds
	 * @param   array  $quantities
	 *
	 * @return bool
	 */
	public function updateCart($eventIds, $quantities)
	{
		if (EventbookingHelper::isMethodOverridden('EventbookingHelperOverrideCart', 'updateCart'))
		{
			return EventbookingHelperOverrideCart::updateCart($eventIds, $quantities);
		}

		$session       = Factory::getSession();
		$config        = EventbookingHelper::getConfig();
		$newItems      = [];
		$newQuantities = [];

		for ($i = 0, $n = count($eventIds); $i < $n; $i++)
		{
			if (($eventIds[$i] > 0) && ($quantities[$i] > 0))
			{
				$event = EventbookingHelperDatabase::getEvent($eventIds[$i]);

				if ($event->prevent_duplicate_registration === '')
				{
					$preventDuplicateRegistration = $config->prevent_duplicate_registration;
				}
				else
				{
					$preventDuplicateRegistration = $event->prevent_duplicate_registration;
				}

				$newItems[] = $eventIds[$i];

				if ($preventDuplicateRegistration)
				{
					$newQuantities[] = 1;
				}
				elseif ($event->max_group_number > 0 && $event->max_group_number < $quantities[$i])
				{
					$newQuantities[] = $event->max_group_number;
				}
				else
				{
					$newQuantities[] = $quantities[$i];
				}
			}
		}

		$cart = ['items' => $newItems, 'quantities' => $newQuantities];
		$session->set('eb_cart', $cart);

		return true;
	}

	/**
	 * Calculate total price of the registration
	 *
	 * @return float
	 */
	public function calculateTotal()
	{
		$items      = $this->getItems();
		$quantities = $this->getQuantities();
		$total      = 0;

		for ($i = 0, $n = count($items); $i < $n; $i++)
		{
			$total += $quantities[$i] * EventbookingHelper::callOverridableHelperMethod('Registration', 'getRegistrationRate', [$items[$i], $quantities[$i]]);
		}

		return $total;
	}

	/**
	 * Get list of events in the cart
	 *
	 * return array
	 */
	public function getEvents()
	{
		$db          = Factory::getDbo();
		$query       = $db->getQuery(true);
		$items       = $this->getItems();
		$quantities  = $this->getQuantities();
		$quantityArr = [];
		$events      = [];

		for ($i = 0, $n = count($items); $i < $n; $i++)
		{
			$quantityArr[$items[$i]] = $quantities[$i];
		}

		if (count($items))
		{
			$config      = EventbookingHelper::getConfig();
			$user        = Factory::getUser();
			$nullDate    = $db->getNullDate();
			$fieldSuffix = EventbookingHelper::getFieldSuffix();
			$currentDate = $db->quote(EventbookingHelper::getServerTimeFromGMTTime());
			$query->select('a.*')
				->select("TIMESTAMPDIFF(MINUTE, $currentDate, early_bird_discount_date) AS date_diff")
				->select('SUM(b.number_registrants) AS total_registrants')
				->from('#__eb_events AS a')
				->leftJoin('#__eb_registrants AS b ON (a.id = b.event_id AND b.group_id = 0 AND (b.published = 1 OR (b.published = 0 AND b.payment_method LIKE "os_offline%")))')
				->where('a.id IN (' . implode(',', $items) . ')')
				->group('a.id')
				->order('FIND_IN_SET(a.id, "' . implode(',', $items) . '")');

			if ($fieldSuffix)
			{
				EventbookingHelperDatabase::getMultilingualFields($query, ['a.title'], $fieldSuffix);
			}

			$db->setQuery($query);
			$events = $db->loadObjectList();

			for ($i = 0, $n = count($events); $i < $n; $i++)
			{
				$event       = $events[$i];
				$event->rate = EventbookingHelper::callOverridableHelperMethod('Registration', 'getRegistrationRate', [$event->id, $quantityArr[$event->id]]);

				if ($config->show_discounted_price)
				{
					$discount = 0;

					if (($event->early_bird_discount_date != $nullDate) && ($event->date_diff >= 0))
					{
						if ($event->early_bird_discount_type == 1)
						{
							$discount += $event->rate * $event->early_bird_discount_amount / 100;
						}
						else
						{
							$discount += $event->early_bird_discount_amount;
						}
					}

					if ($user->id)
					{
						$discountRate = EventbookingHelperRegistration::calculateMemberDiscount($event->discount_amounts, $event->discount_groups);

						if ($discountRate > 0)
						{
							if ($event->discount_type == 1)
							{
								$discount += $event->rate * $discountRate / 100;
							}
							else
							{
								$discount += $discountRate;
							}
						}
					}

					if ($discount > $event->rate)
					{
						$discount = $event->rate;
					}

					$event->discounted_rate = $event->rate - $discount;
				}

				$event->quantity = $quantityArr[$event->id];
			}
		}

		return $events;
	}

	/**
	 * Calculate total discount for the registration
	 * @return float
	 */
	public function calculateTotalDiscount()
	{
		$user          = Factory::getUser();
		$db            = Factory::getDbo();
		$nullDate      = $db->getNullDate();
		$events        = $this->getEvents();
		$totalDiscount = 0;

		if (isset($_SESSION['coupon_id']))
		{
			$query = $db->getQuery(true);
			$query->select('*')
				->from('#__eb_coupons')
				->where('id=' . (int) $_SESSION['coupon_id']);
			$db->setQuery($query);
			$coupon = $db->loadObject();
		}

		for ($i = 0, $n = count($events); $i < $n; $i++)
		{
			$event                 = $events[$i];
			$registrantTotalAmount = $event->rate * $event->quantity;
			$registrantDiscount    = 0;

			// Member discount
			if ($user->id)
			{
				$discountRate = EventbookingHelperRegistration::calculateMemberDiscount($event->discount_amounts, $event->discount_groups);

				if ($discountRate > 0)
				{
					if ($event->discount_type == 1)
					{
						$registrantDiscount = $registrantTotalAmount * $discountRate / 100;
					}
					else
					{
						$registrantDiscount = $event->quantity * $discountRate;
					}
				}
			}

			//Calculate the coupon discount
			if (isset($coupon))
			{
				if ($coupon && ($coupon->event_id == 0 || $coupon->event_id == $event->id))
				{
					if ($coupon->coupon_type == 0)
					{
						$registrantDiscount += $registrantTotalAmount * $coupon->discount / 100;
					}
					else
					{
						$registrantDiscount += $registrantDiscount + $coupon->discount;
					}
				}
			}

			//Early bird discount
			if ($event->early_bird_discount_amount > 0
				&& $event->early_bird_discount_date != $nullDate
				&& $event->date_diff >= 0
			)
			{
				if ($event->early_bird_discount_type == 1)
				{
					$registrantDiscount += $registrantTotalAmount * $event->early_bird_discount_amount / 100;
				}
				else
				{
					$registrantDiscount += $event->quantity * $event->early_bird_discount_amount;
				}
			}

			$totalDiscount += $registrantDiscount;
		}

		return $totalDiscount;
	}
}
