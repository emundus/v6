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

$path_admin_category = JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components';
$path_admin_category .= DIRECTORY_SEPARATOR . 'com_categories' . DIRECTORY_SEPARATOR . 'tables';
$path_admin_category .= DIRECTORY_SEPARATOR . 'category.php';
require_once($path_admin_category);

/**
 * Category Table class
 */
class DropfilesTableCategory extends CategoriesTableCategory
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

    /**
     * Method to delete category
     *
     * @param integer |null $pk       Pk
     * @param boolean       $children Children
     *
     * @return boolean
     */
//    public function delete($pk = null, $children = false)
//    {
//        return parent::delete($pk, $children);
//    }
}
