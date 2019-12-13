<?php

// no direct access
defined('_JEXEC') || die;
jimport('joomla.filesystem.folder');

JLoader::register('JuupdaterHelper', JPATH_SITE . '/plugins/installer/juupdater/helper.php');

/**
 * Class DropfilesControllerJutoken
 */
class DropfilesControllerJutoken extends JControllerForm
{
    /**
     * Add token
     *
     * @return void
     * @since  version
     */
    public function juAddToken()
    {
        JuupdaterHelper::juAddToken();
    }

    /**
     * Remove token
     *
     * @return void
     * @since  version
     */
    public function juRemoveToken()
    {
        JuupdaterHelper::juRemoveToken();
    }

    /**
     * Display return
     *
     * @param boolean $status Response status
     * @param array   $datas  Response data
     *
     * @return void
     * @since  version
     */
    private function exitStatus($status, $datas = array())
    {
        JuupdaterHelper::exitStatus($status, $datas);
    }

    /**
     * Check config token
     *
     * @return integer
     * @since  version
     */
    public function checkConfigToken()
    {
        return JuupdaterHelper::checkConfigToken();
    }

    /**
     * Update config token
     *
     * @param string $token Token
     *
     * @return void
     * @since  version
     */
    public function juUpdateConfigToken($token)
    {
        JuupdaterHelper::juUpdateConfigToken($token);
    }

    /**
     * Update site token
     *
     * @param string $token Token
     *
     * @return void
     * @since  version
     */
    public function juUpdateSiteToken($token)
    {
        JuupdaterHelper::juUpdateSiteToken($token);
    }
}
