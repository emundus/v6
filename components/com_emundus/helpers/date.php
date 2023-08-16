<?php
/**
 * @version		$Id: query.php 14401 2010-01-26 14:10:00Z guillossou $
 * @package		Joomla
 * @subpackage	Emundus
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
/**
 * Content Component Query Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class EmundusHelperDate {

    /**
     * Return actual date formatted in UTC timezone
     * @param $timezone
     *
     * @return string
     *
     * @throws Exception
     * @since version 1.36.7
     */
    static function getNow($timezone = 'UTC') {
        $now = new DateTime();
        $now = $now->setTimezone(new DateTimeZone($timezone));

        return $now->format('Y-m-d H:i:s');
    }

    /**
     * Return a saved date formatted to the current timezone
     * @param $date
     * @param $format
     * @param $local
     *
     * @return string
     *
     * @throws Exception
     * @since version 1.28.0
     */
    static function displayDate($date, $format = 'DATE_FORMAT_LC2', $local = 1) {
        $display_date = '';

        if (!EmundusHelperDate::isNull($date)) {
            if ($local) {
                $config = JFactory::getConfig();
                $offset = $config->get('offset');

                $date_time = new DateTime($date, new DateTimeZone($offset));
                $date_time->setTimezone(new DateTimeZone('UTC'));
            } else {
                $date_time = new DateTime($date);
            }

            $display_date = HtmlHelper::date($date_time->format('Y-m-d H:i:s'), Text::_($format));
        }

        return $display_date;
    }

    /**
     * Check if date is null
     * @param $date
     *
     * @return bool
     *
     * @since version 1.34.0
     */
    static function isNull($date) {
        return (empty($date) || $date === '0000-00-00 00:00:00' || $date === '0000-00-00');
    }
}
