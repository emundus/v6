<?php
/**
 * @package    Joomla.Site
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
define('JPATH_BASE', __DIR__);

$parts = explode(DIRECTORY_SEPARATOR, JPATH_BASE);

// Defines.
define('JPATH_ROOT',          implode(DIRECTORY_SEPARATOR, $parts));
define('EMUNDUS_PATH_ABS',     JPATH_ROOT.DIRECTORY_SEPARATOR.'images/emundus/files/');
define('EMUNDUS_PATH_REL', 'images/emundus/files/');
define('EMUNDUS_PHOTO_AID', 10);
