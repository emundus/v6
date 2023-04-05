<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\MultiFactor;

use DomainException;
use Joomla\CMS\Event\AbstractImmutableEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Concrete Event class for the onUserMultifactorCallback event
 *
 * @since 4.2.0
 */
class Callback extends AbstractImmutableEvent
{
    /**
     * Public constructor
     *
     * @param   string  $method  The MFA method name
     *
     * @since 4.2.0
     */
    public function __construct(string $method)
    {
        parent::__construct('onUserMultifactorCallback', ['method' => $method]);
    }

    /**
     * Validate the value of the 'method' named parameter
     *
     * @param   string|null  $value  The value to validate
     *
     * @return  string
     * @throws  DomainException
     * @since   4.2.0
     */
    public function setMethod(string $value): string
    {
        if (empty($value)) {
            throw new DomainException(sprintf("Argument 'method' of event %s must be a non-empty string.", $this->name));
        }

        return $value;
    }
}
