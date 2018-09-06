<?php
/**
 * @package   AllediaFramework
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC., All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\Framework;

defined('_JEXEC') or die();

class Exception extends \Exception
{
    /**
     * Set error message to include class::method() information. Could be used live
     * but very helpful during development.
     *
     * @return string
     */
    public function getTraceMessage()
    {
        $trace  = $this->getTrace();
        $caller = array_shift($trace);

        $result = '';
        if (!empty($caller['class'])) {
            $result .= $caller['class'] . '::';
        }
        if (!empty($caller['function'])) {
            $result .= $caller['function'] . '()';
        }

        return trim($result . ' ' . $this->message);
    }

    /**
     * Get single line listing of call stack
     *
     * @return array
     */
    public function getCallStack()
    {
        $trace = $this->getTrace();
        $stack = array();

        foreach ($trace as $caller) {
            $row = 'Line ' . (empty($caller['line']) ? '' : $caller['line'] . ' - ');
            if (!empty($caller['class'])) {
                $row .= $caller['class'] . '::';
            }
            if (!empty($caller['function'])) {
                $row .= $caller['function'] . '()';
            }

            if (!empty($caller['file'])) {
                $row .= ' [' . $caller['file'] . ']';
            }
            $stack[] = $row;
        }

        return $stack;
    }
}
