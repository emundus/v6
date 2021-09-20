<?php
/**
 * Dropfiles
 *
 * @copyright Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') || die;

jimport('joomla.application.component.modellist');


/**
 * Class DropfilesModelFrontcategories
 */
class DropfilesModelFrontcategories extends JModelList
{
    /**
     * Model context string.
     *
     * @var string
     */
    public $pContext = 'com_dropfiles.categories';

    /**
     * The category context (allows other extensions to derived from this model).
     *
     * @var string
     */
    protected $pExtension = 'com_dropfiles';

    /**
     * Parent
     *
     * @var null
     */
    private $pParent = null;

    /**
     * Items
     *
     * @var null
     */
    private $pItems = null;

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param null $ordering  Ordering
     * @param null $direction Direction
     *
     * @since    1.6
     * @internal param null $ordering
     * @internal param null $direction
     *
     * @return void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = JFactory::getApplication();
        $this->setState('filter.extension', $this->pExtension);

        // Get the parent id if defined.
        $parentId = $app->input->getInt('id');
        $this->setState('filter.parentId', $parentId);
        $this->setState('category.id', $parentId);

//      $params = $app->getParams();
//      $this->setState('params', $params);

        $this->setState('filter.published', 1);
        $this->setState('filter.access', true);
    }

    /**
     * Redefine the function an add some properties to make the styling more easy
     *
     * @return   mixed An array of data items on success, false on failure.
     * @internal param bool $recursive True if you want to return children recursively.
     * @since    1.6
     */
    public function getItems()
    {
            $app = JFactory::getApplication();
            $menu = $app->getMenu();
            $active = $menu->getActive();
            $params = new JRegistry();

        if ($active) {
            $params->loadString($active->getParams());
        }

        $options               = array();
        $options['countItems'] = 1;
        $jpath_root_models     = JPATH_ROOT . '/administrator/components/com_dropfiles/models/';
        JModelLegacy::addIncludePath($jpath_root_models, 'DropfilesModelCategories');
        $categories    = JModelLegacy::getInstance('Categories', 'dropfilesModel', $options);
        $this->pParent = $categories->get($this->getState('filter.parentId', 'root'));

        $categories->setState('category.id', $this->getState('category.id', 0));
        $categories->setState('category.frontcategories', true);
        $cats = $categories->getItems();


            $dropfiles_params = JComponentHelper::getParams('com_dropfiles');
            //check category restriction
            $user = JFactory::getUser();
        if ($dropfiles_params->get('categoryrestriction', 'accesslevel') === 'accesslevel') {
            $groups = $user->getAuthorisedViewLevels();

            if (count($cats)) {
                foreach ($cats as $key => $cat) {
                    if (!in_array($cat->access, $groups)) {
                        unset($cats[$key]);
                    }
                }
            }
        } else {
            $modelConfig = JModelLegacy::getInstance('Frontconfig', 'dropfilesModel');
            if (count($cats)) {
                $userAuthorisedGroups = $user->getAuthorisedGroups();
                foreach ($cats as $key => $cat) {
                    $params    = $modelConfig->getParams($cat->id);
                    $usergroup = isset($params->params->usergroup) ? $params->params->usergroup : array();
                    $result    = array_intersect($userAuthorisedGroups, $usergroup);
                    if (!count($result)) {
                        unset($cats[$key]);
                    }
                }
            }
        }

        if ($dropfiles_params->get('restrictfile', 0)) {
            $modelConfig = JModelLegacy::getInstance('Frontconfig', 'dropfilesModel');
            $user        = JFactory::getUser();
            $user_id     = (int) $user->id;

            if (count($cats)) {
                foreach ($cats as $key => $cat) {
                    $params = $modelConfig->getParams($cat->id);

                    $canViewCategory = isset($params->params->canview) ? (int) $params->params->canview : 0;
                    if ($user_id) {
                        if (!($canViewCategory === $user_id || $canViewCategory === 0)) {
                            unset($cats[$key]);
                        }
                    } else {
                        if ($canViewCategory !== 0) {
                            unset($cats[$key]);
                        }
                    }
                }
                $cats = array_values($cats);
            }
        }


            array_walk($cats, array($this, 'unsetValues'));

            $this->pItems = array_values($cats);

        return $this->pItems;
    }

    /**
     * Count Sub Categories
     *
     * @param integer $category_id Category id
     *
     * @return integer
     * @since  version
     */
    public function getSubCategoriesCount($category_id)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('count(id)')
            ->from($db->quoteName('#__categories'))
            ->where('parent_id = ' . $db->quote($category_id))
            ->group('id');
        $db->setQuery($query);
        $result = $db->loadResult();
        if ($result) {
            return $result;
        } else {
            return 0;
        }
    }

    /**
     * Unset values $item
     *
     * @param object $item Item
     *
     * @internal param $key
     *
     * @return void
     * @since  version
     */
    private function unsetValues(&$item)
    {
        unset($item->note);
        unset($item->published);
        unset($item->checked_out);
        unset($item->checked_out_time);
        unset($item->created_user_id);
        unset($item->path);
        unset($item->lft);
        unset($item->rgt);
        unset($item->editor);
        unset($item->access_level);
        unset($item->author_name);
    }

    /**
     * Get Parent item
     *
     * @return null
     * @since  x
     */
    public function getParent()
    {
        if (!is_object($this->pParent)) {
            $this->getItems();
        }
        return $this->pParent;
    }
}
