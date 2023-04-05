<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Site\Model;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * This models supports retrieving lists of contact categories.
 *
 * @since  1.6
 */
class CategoriesModel extends ListModel
{
    /**
     * Model context string.
     *
     * @var     string
     */
    public $_context = 'com_contact.categories';

    /**
     * The category context (allows other extensions to derived from this model).
     *
     * @var     string
     */
    protected $_extension = 'com_contact';

    /**
     * Parent category of the current one
     *
     * @var    CategoryNode|null
     */
    private $_parent = null;

    /**
     * Array of child-categories
     *
     * @var    CategoryNode[]|null
     */
    private $_items = null;

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication();
        $this->setState('filter.extension', $this->_extension);

        // Get the parent id if defined.
        $parentId = $app->input->getInt('id');
        $this->setState('filter.parentId', $parentId);

        $params = $app->getParams();
        $this->setState('params', $params);

        $this->setState('filter.published', 1);
        $this->setState('filter.access', true);
    }

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
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.extension');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.parentId');

        return parent::getStoreId($id);
    }

    /**
     * Redefine the function and add some properties to make the styling easier
     *
     * @return  mixed  An array of data items on success, false on failure.
     */
    public function getItems()
    {
        if ($this->_items === null) {
            $app = Factory::getApplication();
            $menu = $app->getMenu();
            $active = $menu->getActive();

            if ($active) {
                $params = $active->getParams();
            } else {
                $params = new Registry();
            }

            $options = [];
            $options['countItems'] = $params->get('show_cat_items_cat', 1) || !$params->get('show_empty_categories_cat', 0);
            $categories = Categories::getInstance('Contact', $options);
            $this->_parent = $categories->get($this->getState('filter.parentId', 'root'));

            if (is_object($this->_parent)) {
                $this->_items = $this->_parent->getChildren();
            } else {
                $this->_items = false;
            }
        }

        return $this->_items;
    }

    /**
     * Gets the id of the parent category for the selected list of categories
     *
     * @return   integer  The id of the parent category
     *
     * @since    1.6.0
     */
    public function getParent()
    {
        if (!is_object($this->_parent)) {
            $this->getItems();
        }

        return $this->_parent;
    }
}
