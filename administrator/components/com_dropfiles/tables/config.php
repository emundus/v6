<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 * @since     1.5
 */

// No direct access
defined('_JEXEC') || die;

/**
 * Class DropfilesTableConfig
 */
class DropfilesTableConfig extends JTable
{
    /**
     * Constructor
     *
     * @param JDatabase $db A database connector object
     *
     * @since 1.5
     */
    public function __construct(&$db)
    {
        parent::__construct('#__dropfiles', 'id', $db);
    }


    /**
     * Overloaded bind function to pre-process the params.
     *
     * @param array  $array  Params
     * @param string $ignore Ignore
     *
     * @return mixed
     * @since  version
     */
    public function bind($array, $ignore = '')
    {
        if (isset($array['params']) && is_array($array['params'])) {
            $registry = new JRegistry();
            $registry->loadArray($array['params']);
            $array['params'] = (string)$registry;
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Overload the store method for the table.
     *
     * @param boolean $updateNulls Toggle whether null values should be updated.
     *
     * @return boolean True on success, false on failure.
     *
     * @since 1.6
     */
    public function store($updateNulls = false)
    {
        $date = JFactory::getDate();
        if ($this->id) {
            // Existing item
            $this->modified = $date->toSql();
        } else {
            // so we don't touch either of these if they are set.
            if (!intval($this->created)) {
                $this->created = $date->toSql();
            }
        }

        // Attempt to store the user data.
        return parent::store($updateNulls);
    }
}
