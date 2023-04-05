<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\Table;

use BadMethodCallException;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event class for JTable's onAfterStore event
 *
 * @since  4.0.0
 */
class AfterStoreEvent extends AbstractEvent
{
    /**
     * Constructor.
     *
     * Mandatory arguments:
     * subject      JTableInterface The table we are operating on
     * result       boolean         Did the save succeed?
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  BadMethodCallException
     */
    public function __construct($name, array $arguments = [])
    {
        if (!\array_key_exists('result', $arguments)) {
            throw new BadMethodCallException("Argument 'result' is required for event $name");
        }

        parent::__construct($name, $arguments);
    }

    /**
     * Setter for the result argument
     *
     * @param   boolean  $value  The value to set
     *
     * @return  boolean
     *
     * @throws  BadMethodCallException  if the argument is not of the expected type
     */
    protected function setResult($value)
    {
        return $value ? true : false;
    }
}
