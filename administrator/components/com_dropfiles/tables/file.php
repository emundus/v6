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
 * Category Table class
 */
class DropfilesTableFile extends JTable
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
        parent::__construct('#__dropfiles_files', 'id', $db);
    }

    /**
     * Overridden JTable::store to set created/modified and user id.
     *
     * @param boolean $updateNulls True to update fields even if they are null.
     *
     * @return boolean  True on success.
     *
     * @since 11.1
     */
    public function store($updateNulls = false)
    {
        $date = JFactory::getDate();
        $user = JFactory::getUser();

        if (!$this->id) {
            // Existing category
            // $this->modified_time = $date->toSql();
            // $this->modified_user_id = $user->get('id');
        // } else {
            // New category
            $this->created_time = $date->toSql();
            $this->publish = $date->toSql();
            $this->author = $user->get('id');
        }

        // Set publish_up to null date if not set
        if (!$this->publish) {
            $this->publish = $this->_db->getNullDate();
        }

        // Set publish_down to null date if not set
        if (!$this->publish_down) {
            $this->publish_down = $this->_db->getNullDate();
        }
        return parent::store($updateNulls);
    }
}
