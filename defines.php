<?php
/**
 * @package    Joomla.Site
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$parts = explode(DIRECTORY_SEPARATOR, __DIR__);
$jpath_root = implode(DIRECTORY_SEPARATOR, $parts);

// Defines.
define('EMUNDUS_PATH_ABS',     $jpath_root.DIRECTORY_SEPARATOR.'images/emundus/files/');
define('EMUNDUS_PATH_REL', 'images/emundus/files/');
define('EMUNDUS_PHOTO_AID', 10);
