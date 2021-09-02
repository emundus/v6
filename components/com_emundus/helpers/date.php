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
use Joomla\CMS\Date\Date;
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
    static function displayDate($date,$format = 'DATE_FORMAT_LC2') {
        $config = JFactory::getConfig();

        /**
         * @TODO
         * Get offset with emundus config Local or UTC
         */

        $offset = $config->get('offset');

        $date_time = new DateTime($date, new DateTimeZone($offset));
        $date_time->setTimezone(new DateTimeZone("UTC"));

        return HtmlHelper::date($date_time->format("Y-m-d H:i:s"), Text::_($format));
    }
}
