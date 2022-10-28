<?php
/**
 * Dropfiles
 *
 * @package    Joomla.Site
 * @subpackage com_dropfiles
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @since      1.5
 */

defined('_JEXEC') || die;

jimport('joomla.application.component.modelitem');

/**
 * This models supports retrieving a category, the files associated with the category,
 * sibling, child and parent categories.
 */
class DropfilesModelFrontcategory extends JModelItem
{
    /**
     * Get category
     *
     * @param string $idcat Category id
     *
     * @return null
     * @since  version
     */
    public function getCategory($idcat = null)
    {
        if ($idcat === null) {
            $app = JFactory::getApplication();
            $idcat = $app->input->getInt('id', 0);
        }
        $user = JFactory::getUser();
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->from('#__categories as a');

        $query->select('a.level, a.id, a.title, a.parent_id, a.access, a.created_user_id');

        $query->where('a.published=1');
        $query->where('a.extension=' . $db->quote('com_dropfiles'));
        $query->where('a.id=' . (int)$idcat);

        $query->select('b.title as parent_title');
        $query->select('b.id as parent_id');
        $query->join('LEFT OUTER', '#__categories AS b ON b.id=a.parent_id AND b.extension='
            . $db->quote('com_dropfiles'));

        $query->select('c.type, c.cloud_id, c.params');
        $query->join('LEFT OUTER', '#__dropfiles AS c ON c.id=a.id');

        // Implement View Level Access
        $db->setQuery($query);
        if ($db->execute()) {
            $result = $db->loadObject();
            if (isset($result->params)) {
                $dropfiles_params = JComponentHelper::getParams('com_dropfiles');
                $result->params = json_decode($result->params);
                if (!$result->params || empty($result->params)) {
                    $result->params = new \stdClass;
                }
                if ($dropfiles_params->get('categoryrestriction', 'accesslevel') === 'accesslevel') {
                    if (isset($result->params->access) && intval($result->params->access) === -1) {
                        $result->access = $this->getTopParentAccess($result);
                    }
                } else {
                    $result->params->usergroup = $this->getTopParentGroup($result);
                }
            }

            if (isset($result) && isset($result->type) && $result->type === 'default') {
                $catetitle = (isset($result->title)) ? trim(preg_replace('/[^a-z0-9-]+/', '-', strtolower($result->title)), '-') : '';
                $cateid    = (isset($result->id)) ? $result->id : 0;
                $result->linkdownload_cat = $this->urlBtnDownloadCat($cateid, $catetitle);
            }

            return $result;
        }

        return null;
    }

    /**
     * Method to get data.
     *
     * @param integer $pk The id of the category.
     *
     * @return object|boolean|JException  Menu item data object on success, boolean false or JException instance on error
     */
    public function getItem($pk = null)
    {
        $pk = (!empty($pk)) ? $pk : (int) $this->getState('article.id');

        if ($this->_item === null) {
            $this->_item = array();
        }

        if (!isset($this->_item[$pk])) {
            try {
                    $db = $this->getDbo();
                //   $this->_item[$pk] = $data;
            } catch (Exception $e) {
                if ($e->getCode() === 404) {
                    // Need to go thru the error handler to allow Redirect to work.
                    JError::raiseError(404, $e->getMessage());
                } else {
                    $this->setError($e);
                    $this->_item[$pk] = false;
                }
            }
        }

        return $this->_item[$pk];
    }

    /**
     * Get top parent group
     *
     * @param object $category Category object
     *
     * @return array
     *
     * @since 5.5
     */
    public function getTopParentGroup($category)
    {
        $usergroup = isset($category->params->usergroup) ? $category->params->usergroup : array();
        if (isset($category->level) && intval($category->level) === 1) {
            $usergroup = array_diff($usergroup, array('-1'));
            if (empty($usergroup)) {
                return array('1'); // Return default public usergroup
            }
        }
        if (!in_array('-1', $usergroup)) {
            if (empty($usergroup)) {
                return array('1'); // Return default public usergroup
            }
            return $usergroup;
        }
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $this->setState('filter.access', null);
        // Filter by extension
        $extension = $this->getState('filter.extension');
        if ($extension) {
            $query->where('c.extension = ' . $db->quote($extension));
        }

        $query->select('d.params, c.level, c.parent_id');
        $query->from('#__categories as c');
        $query->leftJoin('#__dropfiles as d on d.id = c.id');
        $query->where('c.id = ' . (int) $category->parent_id);
        $db->setQuery($query);
        if ($db->execute()) {
            $parent = $db->loadObject();
            $parent->params = (isset($parent->params) && !empty($parent->params)) ? json_decode($parent->params) : false;
            if (isset($parent->params->usergroup) && is_array($parent->params->usergroup) && in_array('-1', $parent->params->usergroup)) {
                $usergroup = $this->getTopParentGroup($parent);
                if (empty($usergroup)) {
                    return array('1'); // Return default public usergroup
                }
                return $usergroup;
            } else {
                $usergroup = isset($parent->params->usergroup) ? $parent->params->usergroup : array('1');
                if (empty($usergroup)) {
                    return array('1'); // Return default public usergroup
                }
                return $usergroup;
            }
        }

        $usergroup = array_diff($usergroup, array('-1'));
        if (empty($usergroup)) {
            return array('1'); // Return default public usergroup
        }
        return $usergroup;
    }

    /**
     * Get top parent access
     *
     * @param object $category Category object
     *
     * @return array
     *
     * @since 5.5
     */
    public function getTopParentAccess($category)
    {
        $access = isset($category->access) ? $category->access : 1;
        // Always return top category access
        if (isset($category->level) && intval($category->level) === 1) {
            return (string) $access;
        }
        if (intval($category->level) > 1) {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $this->setState('filter.access', null);
            // Filter by extension
            $extension = $this->getState('filter.extension');
            if ($extension) {
                $query->where('c.extension = ' . $db->quote($extension));
            }

            $query->select('c.access, d.params, c.level, c.parent_id');
            $query->from('#__categories as c');
            $query->leftJoin('#__dropfiles as d on d.id = c.id');
            $query->where('c.id = ' . (int) $category->parent_id);
            $db->setQuery($query);
            if ($db->execute()) {
                $parent = $db->loadObject();
                $params = isset($parent->params) ? json_decode($parent->params) : false;
                unset($parent->params);
                $pAccess = ($params && isset($params->access)) ? intval($params->access) : -1;
                if ($pAccess === -1) {
                    $access = $this->getTopParentAccess($parent);
                } else {
                    return (string) $parent->access;
                }
            }
        }

        return (string) $access;
    }
    /**
     * Get category by id
     *
     * @param string $cloud_id Cloud id
     *
     * @return integer
     * @since  version
     */
    public function getCategoryIDbyCloudId($cloud_id)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('a.id');
        $query->from('#__dropfiles as a');
        $query->where('a.cloud_id =' . $db->quote($cloud_id));
        $db->setQuery($query);

        return $db->loadResult();
    }
    /**
     * Get url download cat
     *
     * @param integer $catid   Category id
     * @param string  $catname Category name
     *
     * @return string
     */
    public function urlBtnDownloadCat($catid, $catname)
    {
        $linkdownloadCat = JURI::root() . 'index.php?option=com_dropfiles&task=frontfile.downloadCategory&cate_id='. $catid . '&cate_name='. $catname;
        $linkdownloadCat = JRoute::_($linkdownloadCat);

        return $linkdownloadCat;
    }
}
