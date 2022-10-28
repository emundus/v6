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

jimport('joomla.application.component.modellegacy');


/**
 * Class DropfilesModelTokens
 */
class DropfilesModelTokens extends JModelLegacy
{
    /**
     * Ceate new token
     *
     * @return boolean|string
     */
    public function createToken()
    {
        $dbo = $this->getDbo();
        $token = md5(uniqid(mt_rand(), true));
        $user = JFactory::getUser();
        if ($user->guest) {
            $id_user = 0;
        } else {
            $id_user = (int) $user->id;
        }
        $query = 'INSERT INTO #__dropfiles_tokens (id_user,time,token) ';
        $query .= ' values (' . $id_user . ',' . (int)time() . ',' . $dbo->quote($token) . ')';
        $dbo->setQuery($query);
        if (!$dbo->execute()) {
            return false;
        }

        return $token;
    }


    /**
     * Methode to check if a token exists
     *
     * @param string $token Token string
     *
     * @return boolean|integer Object file, false if an error occurs
     */
    public function tokenExists($token)
    {
        $dbo = $this->getDbo();
        $query = 'SELECT id FROM #__dropfiles_tokens WHERE token=' . $dbo->quote($token);
        $dbo->setQuery($query);
        if (!$dbo->execute()) {
            return false;
        }
        if ($dbo->getNumRows()) {
            return $dbo->loadResult();
        }

        return 0;
    }

    /**
     * Methode to update a token
     *
     * @param integer $id Token id
     *
     * @return boolean Object file, false if an error occurs
     */
    public function updateToken($id)
    {
        $dbo = $this->getDbo();
        $query = 'UPDATE #__dropfiles_tokens SET time=' . (int)time() . ' WHERE id=' . (int)$id;
        $dbo->setQuery($query);
        if (!$dbo->execute()) {
            return false;
        }

        return (bool)$dbo->getAffectedRows();
    }


    /**
     * Method to delete all tokens
     *
     * @return boolean
     */
    public function removeTokens()
    {
        $config = JFactory::getConfig();
        $lifetime = (int)$config->get('lifetime');

        $time = time() - $lifetime * 60;

        $dbo = $this->getDbo();
        $query = 'DELETE FROM #__dropfiles_tokens WHERE time < ' . (int)$time;
        $dbo->setQuery($query);
        if (!$dbo->execute()) {
            return false;
        }

        return $dbo->getAffectedRows();
    }
}
