<?php
/**
 * Dropfiles Updater
 *
 * @package    Juupdater.Plugin
 * @subpackage Installer.juupdater
 *
 * @copyright Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') || die;
// Use Joomla\Registry\Registry;
jimport('joomla.plugin.plugin');

/**
 * Juupdater Installer plugin
 */
class PlgInstallerJuupdater extends JPlugin
{
    /**
     * Installer before package download
     *
     * @param string $url URL
     *
     * @internal param $headers
     *
     * @return void
     * @throws \Exception Throw when application can not start
     * @since  version
     */
    public function onInstallerBeforePackageDownload($url)
    {
        if (strpos($url, 'infosite=joomunited')) {
            $url_checktoken = str_replace('task=download.download', 'task=download.checktoken', $url);
            $app = JFactory::getApplication();

            $http = JHttpFactory::getHttp();
            $response = $http->get($url_checktoken);
            $res_body = json_decode($response->body);
            if ($res_body->status === false) {
                if ($res_body->linkdownload !== '') {
                    $app->enqueueMessage($res_body->linkdownload, 'error');
                } else {
                    $app->enqueueMessage($res_body->datas, 'error');
                }
                $app->redirect(JUri::base() . 'index.php?option=com_installer&view=update');
            }
        }
    }
}
