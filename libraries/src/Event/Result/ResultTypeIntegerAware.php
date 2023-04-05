<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\Result;

use InvalidArgumentException;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * This Trait partially implements the ResultAwareInterface for type checking.
 *
 * Events using this Trait (and the ResultAware trait) will expect event handlers to set results
 * of an Integer type.
 *
 * @since  4.2.0
 */
trait ResultTypeIntegerAware
{
    /**
     * Can the result attribute values also be NULL?
     *
     * @var    boolean
     * @since  4.2.0
     */
    protected $resultIsNullable = false;

    /**
     * Can the result attribute values also be boolean FALSE?
     *
     * @var    boolean
     * @since  4.2.0
     *
     * @deprecated 5.0 You should use nullable values or exceptions instead of returning boolean false results.
     */
    protected $resultIsFalseable = false;

    /**
     * Checks the type of the data being appended to the result argument.
     *
     * @param   mixed  $data  The data to type check
     *
     * @return  void
     * @throws  InvalidArgumentException
     *
     * @internal
     * @since   4.2.0
     */
    public function typeCheckResult($data): void
    {
        if ($this->resultIsNullable && $data === null) {
            return;
        }

        if ($this->resultIsFalseable && $data === false) {
            return;
        }

        if (!is_int($data)) {
            throw new InvalidArgumentException(sprintf('Event %s only accepts Integer results.', $this->getName()));
        }
    }
}
