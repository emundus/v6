<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Authentication\Password;

use Joomla\Authentication\Password\HandlerInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Password handler for PHPass hashed passwords
 *
 * @since       4.0.0
 * @deprecated  5.0  Support for PHPass hashed passwords will be removed
 */
class PHPassHandler implements HandlerInterface, CheckIfRehashNeededHandlerInterface
{
    /**
     * Check if the password requires rehashing
     *
     * @param   string  $hash  The password hash to check
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    public function checkIfRehashNeeded(string $hash): bool
    {
        return true;
    }

    /**
     * Generate a hash for a plaintext password
     *
     * @param   string  $plaintext  The plaintext password to validate
     * @param   array   $options    Options for the hashing operation
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function hashPassword($plaintext, array $options = [])
    {
        return $this->getPasswordHash()->HashPassword($plaintext);
    }

    /**
     * Check that the password handler is supported in this environment
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    public static function isSupported()
    {
        return class_exists(\PasswordHash::class);
    }

    /**
     * Validate a password
     *
     * @param   string  $plaintext  The plain text password to validate
     * @param   string  $hashed     The password hash to validate against
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    public function validatePassword($plaintext, $hashed)
    {
        return $this->getPasswordHash()->CheckPassword($plaintext, $hashed);
    }

    /**
     * Get an instance of the PasswordHash class
     *
     * @return  \PasswordHash
     *
     * @since   4.0.0
     */
    private function getPasswordHash(): \PasswordHash
    {
        return new \PasswordHash(10, true);
    }
}
