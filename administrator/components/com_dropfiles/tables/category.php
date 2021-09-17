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
class DropfilesTableCategory extends JTableCategory
{
    /**
     * Method to store category
     *
     * @param boolean $updateNulls Update null
     *
     * @return mixed
     */
    public function store($updateNulls = false)
    {
        $meta = json_decode($this->metadata);
        if (isset($meta->tags)) {
            $meta->tags = array();
        }
        $this->metadata = json_encode($meta);
        return parent::store($updateNulls);
    }
}
