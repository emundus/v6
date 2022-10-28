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
 */

// no direct access
defined('_JEXEC') || die;

jimport('joomla.access.access');


/**
 * Class DropfilesModelFrontconfig
 */
class DropfilesModelFrontconfig extends JModelLegacy
{

    /**
     * Method get param config dropfile
     *
     * @param integer $id Id
     *
     * @return boolean
     * @since  version
     */
    public function getParams($id)
    {
        $dbo = $this->getDbo();
        $query = 'SELECT d.*, c.parent_id, c.level, c.access FROM #__dropfiles AS d LEFT JOIN #__categories AS c ON c.id = d.id  WHERE d.id = ' . (int)$id;
        $dbo->setQuery($query);
        if ($dbo->execute()) {
            $result = $dbo->loadObject();
            if (!empty($result)) {
                $dropfiles_params = JComponentHelper::getParams('com_dropfiles');
                $result->params = json_decode($result->params);
                if (!$result->params || empty($result->params)) {
                    $result->params = new \stdClass;
                }
                if ($dropfiles_params->get('categoryrestriction', 'accesslevel') !== 'accesslevel') {
                    $result->params->usergroup = $this->getTopParentGroup($result);
                }
                return $result;
            }
        }
        return false;
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
}
