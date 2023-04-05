<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Prototype item model.
 *
 * @since  1.6
 */
abstract class ItemModel extends BaseDatabaseModel implements ItemModelInterface
{
    /**
     * An item.
     *
     * @var    array
     * @since  1.6
     */
    protected $_item = null;

    /**
     * Model context string.
     *
     * @var    string
     * @since  1.6
     */
    protected $_context = 'group.type';

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     *
     * @since   1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        return md5($id);
    }
}
