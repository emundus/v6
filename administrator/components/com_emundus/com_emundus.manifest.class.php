<?php
/**
 * eMundus: Installer Manifest Class
 *
 * @package     Joomla
 * @subpackage  eMundus
 * @author      eMundus
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

class Com_EmundusInstallerScript
{
    /**
     * Run before installation or upgrade run
     *
     * @param   string $type   discover_install (Install unregistered extensions that have been discovered.)
     *                         or install (standard install)
     *                         or update (update)
     * @param   object $parent installer object
     *
     * @return  void
     */
    public function preflight($type, $parent)
    {

    }

    /**
     * Run when the component is installed
     *
     * @param   object $parent installer object
     *
     * @return bool
     */
    public function install($parent)
    {
        $parent->getParent()->setRedirectURL('index.php?option=com_emundus');

        return true;
    }

    /**
     * Run when the component is updated
     *
     * @param   object $parent installer object
     *
     * @return  bool
     */
    public function update($parent)
    {
        return true;
    }

    /**
     * Run when the component is uninstalled.
     *
     * @param   object $parent installer object
     *
     * @return  void
     */
    public function uninstall($parent)
    {
    }

    /**
     * Run after installation or upgrade run
     *
     * @param   string $type   discover_install (Install unregistered extensions that have been discovered.)
     *                         or install (standard install)
     *                         or update (update)
     * @param   object $parent installer object
     *
     * @return  bool
     */
    public function postflight($type, $parent)
    {
    }
}
