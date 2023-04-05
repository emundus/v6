<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   integer  $level  The level of the item in the tree like structure.
 *
 * @since  3.6.0
 */

if ($level > 1) {
    echo '<span class="text-muted">' . str_repeat('&#8942;&nbsp;&nbsp;&nbsp;', (int) $level - 2) . '</span>&ndash;&nbsp;';
}
