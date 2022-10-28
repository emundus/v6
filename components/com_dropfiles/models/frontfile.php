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
 * Class DropfilesModelFrontfile
 */
class DropfilesModelFrontfile extends JModelLegacy
{

    /**
     * Methode to retrieve file
     *
     * @param integer $id File id
     *
     * @return object file, false if an error occurs
     * @since  version
     */
    public function getFile($id)
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $query->select('*');
        $query->from('#__dropfiles_files');

        $query->where('id=' . (int)$id);
        // Filter by publish dates.
        $nullDate = $dbo->quote($dbo->getNullDate());
        $date = JFactory::getDate();

        $nowDate = $dbo->quote($date->toSql());

        $query->where('(publish = ' . $nullDate . ' OR publish <= ' . $nowDate . ')');
        $query->where('(publish_down = ' . $nullDate . ' OR publish_down >= ' . $nowDate . ')');
        $query->where('state = 1');
        $dbo->setQuery($query);

        if (!$dbo->execute()) {
            return false;
        }

        return $dbo->loadObject();
    }

    /**
     * Update number hits of a file
     *
     * @param integer $id File id
     *
     * @return boolean
     * @since  version
     */
    public function hit($id)
    {
        $dbo = $this->getDbo();
        $query = 'UPDATE #__dropfiles_files SET hits=(hits+1) WHERE id=' . (int)$id;

        $dbo->setQuery($query);

        if (!$dbo->execute()) {
            return false;
        }

        return true;
    }

    /**
     * Add a record to db
     *
     * @param integer $file_id Fileid
     * @param integer $userId  Userid
     * @param string  $date    Date
     *
     * @return boolean|mixed
     * @since  version
     */
    public function addChart($file_id, $userId, $date)
    {
        $dbo = $this->getDbo();
        $query = 'INSERT INTO #__dropfiles_statistics (related_id,related_users,type,date,count)';
        $query .= ' VALUES (' . $dbo->quote($file_id) . ',' . (int)$userId . ',"default","' . $date . '",1)';
        $dbo->setQuery($query);
        if ($dbo->execute()) {
            return $dbo->insertid();
        }

        return false;
    }

    /**
     * Add count chart for file
     *
     * @param integer $file_id File id
     * @param integer $userId  User id
     *
     * @return boolean
     * @since  version
     */
    public function addCountChart($file_id, $userId)
    {
        $date = date('Y-m-d');
        $dbo = $this->getDbo();
        $querycheck = 'SELECT * FROM #__dropfiles_statistics';
        $querycheck .= ' WHERE related_id=' . $dbo->quote($file_id) . ' AND related_users=' . (int)$userId . ' AND date=' . $dbo->quote($date) . '';
        $dbo->setQuery($querycheck);
        $object = $dbo->loadObject();

        if ($object) {
            $query = 'UPDATE #__dropfiles_statistics SET count=(count+1)';
            $query .= ' WHERE related_id=' . $dbo->quote($file_id) . ' AND related_users=' . (int)$userId . ' AND date=' . $dbo->quote($date) . '';
            $dbo->setQuery($query);

            if (!$dbo->execute()) {
                return false;
            }
        } else {
            $this->addChart($file_id, $userId, $date);
        }
        return true;
    }
}
