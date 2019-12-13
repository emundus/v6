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

        $query->select('c.type, c.cloud_id');
        $query->join('LEFT OUTER', '#__dropfiles AS c ON c.id=a.id');

        // Implement View Level Access
        $db->setQuery($query);
        if ($db->query()) {
            return $db->loadObject();
        }

        return null;
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
}
