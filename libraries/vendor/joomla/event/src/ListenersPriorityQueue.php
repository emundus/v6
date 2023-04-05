<?php
/**
 * Part of the Joomla Framework Event Package
 *
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Event;

/**
 * A class containing an inner listeners priority queue that can be iterated multiple times.
 *
 * @since  1.0
 * @internal
 */
final class ListenersPriorityQueue implements \IteratorAggregate, \Countable
{
	/**
	 * The listeners for an event.
	 *
	 * @var    array
	 * @since  2.0.0
	 */
	private $listeners = [];

	/**
	 * Add a listener with the given priority only if not already present.
	 *
	 * @param   callable  $callback  A callable function acting as an event listener.
	 * @param   integer   $priority  The listener priority.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function add(callable $callback, int $priority): self
	{
		$this->listeners[$priority][] = $callback;

		return $this;
	}

	/**
	 * Remove a listener from the queue.
	 *
	 * @param   callable  $callback  A callable function acting as an event listener.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function remove(callable $callback): self
	{
		foreach ($this->listeners as $priority => $listeners)
		{
			if (($key = array_search($callback, $listeners, true)) !== false)
			{
				unset($this->listeners[$priority][$key]);
			}
		}

		return $this;
	}

	/**
	 * Tell if the listener exists in the queue.
	 *
	 * @param   callable  $callback  A callable function acting as an event listener.
	 *
	 * @return  boolean  True if it exists, false otherwise.
	 *
	 * @since   1.0
	 */
	public function has(callable $callback): bool
	{
		foreach ($this->listeners as $priority => $listeners)
		{
			if (($key = array_search($callback, $listeners, true)) !== false)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the priority of the given listener.
	 *
	 * @param   callable  $callback  A callable function acting as an event listener.
	 * @param   mixed     $default   The default value to return if the listener doesn't exist.
	 *
	 * @return  mixed  The listener priority if it exists or the specified default value
	 *
	 * @since   1.0
	 */
	public function getPriority(callable $callback, $default = null)
	{
		foreach ($this->listeners as $priority => $listeners)
		{
			if (($key = array_search($callback, $listeners, true)) !== false)
			{
				return $priority;
			}
		}

		return $default;
	}

	/**
	 * Get all listeners contained in this queue, sorted according to their priority.
	 *
	 * @return  callable[]  An array of listeners.
	 *
	 * @since   1.0
	 */
	public function getAll(): array
	{
		if (empty($this->listeners))
		{
			return [];
		}

		krsort($this->listeners);

		return \call_user_func_array('array_merge', $this->listeners);
	}

	/**
	 * Get the priority queue.
	 *
	 * @return  \ArrayIterator
	 *
	 * @since   1.0
	 */
	#[\ReturnTypeWillChange]
	public function getIterator()
	{
		return new \ArrayIterator($this->getAll());
	}

	/**
	 * Count the number of listeners in the queue.
	 *
	 * @return  integer  The number of listeners in the queue.
	 *
	 * @since   1.0
	 */
	#[\ReturnTypeWillChange]
	public function count()
	{
		$count = 0;

		foreach ($this->listeners as $priority)
		{
			$count += \count($priority);
		}

		return $count;
	}
}
