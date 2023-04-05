<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Log\Logger;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Log\LogEntry;
use Joomla\CMS\Log\Logger;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla MessageQueue logger class.
 *
 * This class is designed to output logs to a specific MySQL database table. Fields in this
 * table are based on the Syslog style of log output. This is designed to allow quick and
 * easy searching.
 *
 * @since  1.7.0
 */
class MessagequeueLogger extends Logger
{
    /**
     * Method to add an entry to the log.
     *
     * @param   LogEntry  $entry  The log entry object to add to the log.
     *
     * @return  void
     *
     * @since   1.7.0
     */
    public function addEntry(LogEntry $entry)
    {
        switch ($entry->priority) {
            case Log::EMERGENCY:
            case Log::ALERT:
            case Log::CRITICAL:
            case Log::ERROR:
                Factory::getApplication()->enqueueMessage($entry->message, 'error');
                break;
            case Log::WARNING:
                Factory::getApplication()->enqueueMessage($entry->message, 'warning');
                break;
            case Log::NOTICE:
                Factory::getApplication()->enqueueMessage($entry->message, 'notice');
                break;
            case Log::INFO:
                Factory::getApplication()->enqueueMessage($entry->message, 'message');
                break;
            default:
                // Ignore other priorities.
                break;
        }
    }
}
