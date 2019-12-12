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
        $query = 'SELECT * FROM #__dropfiles WHERE id = ' . (int)$id;
        $dbo->setQuery($query);
        if ($dbo->query()) {
            $result = $dbo->loadObject();
            if (!empty($result)) {
                $result->params = json_decode($result->params);

                return $result;
            }
        }
        return false;
    }
}
