<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Site\Helper;

use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Language\Multilanguage;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Contact Component Route Helper
 *
 * @static
 * @package     Joomla.Site
 * @subpackage  com_contact
 * @since       1.5
 */
abstract class RouteHelper
{
    /**
     * Get the URL route for a contact from a contact ID, contact category ID and language
     *
     * @param   integer  $id        The id of the contact
     * @param   integer  $catid     The id of the contact's category
     * @param   mixed    $language  The id of the language being used.
     *
     * @return  string  The link to the contact
     *
     * @since   1.5
     */
    public static function getContactRoute($id, $catid, $language = 0)
    {
        // Create the link
        $link = 'index.php?option=com_contact&view=contact&id=' . $id;

        if ($catid > 1) {
            $link .= '&catid=' . $catid;
        }

        if ($language && $language !== '*' && Multilanguage::isEnabled()) {
            $link .= '&lang=' . $language;
        }

        return $link;
    }

    /**
     * Get the URL route for a contact category from a contact category ID and language
     *
     * @param   mixed  $catid     The id of the contact's category either an integer id or an instance of CategoryNode
     * @param   mixed  $language  The id of the language being used.
     *
     * @return  string  The link to the contact
     *
     * @since   1.5
     */
    public static function getCategoryRoute($catid, $language = 0)
    {
        if ($catid instanceof CategoryNode) {
            $id = $catid->id;
        } else {
            $id       = (int) $catid;
        }

        if ($id < 1) {
            $link = '';
        } else {
            // Create the link
            $link = 'index.php?option=com_contact&view=category&id=' . $id;

            if ($language && $language !== '*' && Multilanguage::isEnabled()) {
                $link .= '&lang=' . $language;
            }
        }

        return $link;
    }
}
