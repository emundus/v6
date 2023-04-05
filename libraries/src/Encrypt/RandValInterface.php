<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Encrypt;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface RandValInterface
 *
 * @since   4.0.0
 */
interface RandValInterface
{
    /**
     *
     * Returns a cryptographically secure random value.
     *
     * @return string
     *
     */
    public function generate();
}
