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
 * @since     1.6
 */


defined('_JEXEC') || die;

/**
 * DropfilesCloudHelper class
 */
class DropfilesCloudHelper
{
    /*----------- OneDrive -----------------*/

    /**
     * All config onedrive
     *
     * @return array
     * @since  version
     */
    private static function oneDriveConfigs()
    {
        $default = array(
            'onedriveKey'            => '',
            'onedriveSecret'         => '',
            'onedriveCredentials'    => '',
            'onedriveSyncTime'       => '5',
            'onedriveSyncMethod'     => 'sync_page_curl',
            'onedrive_last_log'      => '',
            'onedriveBaseFolderId'   => 0,
            'onedriveBaseFolderName' => ''
        );

        return $default;
    }

    /**
     * All config old
     *
     * @return array
     * @since  version
     */
    private static function oneDriveConfigsOld()
    {
        $default = array(
            'onedriveKeyOld'            => '',
            'onedriveSecretOld'         => '',
            'onedriveCredentialsOld'    => '',
            'onedriveBaseFolderIdOld'   => '',
            'onedriveBaseFolderNameOld' => ''
        );

        return $default;
    }

    /**
     * Get all config params of onedrive
     *
     * @return array
     * @since  version
     */
    public static function getAllOneDriveConfigs()
    {
        $default = self::getParamsOneDrive(self::oneDriveConfigs());
        return $default;
    }

    /**
     * Save config onedriver
     *
     * @param array $data Onedrive params
     *
     * @return boolean
     * @since  version
     */
    public static function setParamsConfigs($data)
    {
        return DropfilesComponentHelper::setParams($data);
    }

    /**
     *  Get all old config params of onedrive
     *
     * @return array
     * @since  version
     */
    public static function getAllOneDriveConfigsOld()
    {
        $default = self::getParamsOneDrive(self::oneDriveConfigsOld());

        return $default;
    }

    /**
     * Save old config onedrive
     *
     * @param array $data OneDrive params
     *
     * @return boolean
     * @since  version
     */
    public static function setParamsConfigsOld($data)
    {
        $paramOld = self::oneDriveConfigsOld();
        foreach ($data as $key => $val) {
            if (array_key_exists($key . 'Old', $paramOld)) {
                $paramOld[$key . 'Old'] = $val;
            }
        }
        return DropfilesComponentHelper::setParams($paramOld);
    }

    /**
     * Get params OneDrive
     *
     * @param array $array OneDrive param
     *
     * @return array
     * @since  version
     */
    public static function getParamsOneDrive($array)
    {
        $lis_params = array();

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('params')->from('#__extensions')->where('element = "com_dropfiles"');
        $db->setQuery((string) $query);
        $params = new JRegistry;
        $results = $db->loadObject();
        if ($results) {
            $params->loadString($results->params, 'JSON');
        }

        foreach ($array as $key => $val) {
            if (!is_numeric($key)) {
                $lis_params[$key] = $params->get($key, $val);
            } else {
                $lis_params[$val] = $params->get($val);
            }
        }
        return $lis_params;
    }

    /**
     * Get data config by OneDrive
     *
     * @param string $name Cloud type name
     *
     * @return array|null
     * @since  version
     */
    public static function getDataConfigByOneDrive($name)
    {
        $OneDriveParams = array();
        if (self::getAllOneDriveConfigs()) {
            foreach (self::getAllOneDriveConfigs() as $key => $val) {
                if (strpos($key, 'onedrive') !== false) {
                    $OneDriveParams[$key] = $val;
                }
            }
            $result = null;
            switch ($name) {
                case 'onedrive':
                    $result = $OneDriveParams;
                    break;
            }
            return $result;
        }
    }

    /**
     * Replace special characters Id OneDrive
     *
     * @param string  $id         Google id
     * @param boolean $rplSpecial Replace special
     *
     * @return string
     * @since  version
     */
    public static function replaceIdOneDrive($id, $rplSpecial = true)
    {
        if ($rplSpecial) {
            return str_replace('!', '-', $id);
        } else {
            return str_replace('-', '!', $id);
        }
    }

    /**
     * Strips the last extension off of a file name
     *
     * @param string $file The file name
     *
     * @return string  The file name without the extension
     *
     * @since 11.1
     */
    public static function stripExt($file)
    {
        return preg_replace('#\.[^.]*$#', '', $file);
    }

    /**
     * Get ext of file
     *
     * @param string $file File name
     *
     * @return boolean|string
     * @since  version
     */
    public static function getExt($file)
    {
        $dot = strrpos($file, '.') + 1;

        return substr($file, $dot);
    }

    /**
     * Generate uuid v4
     * https://www.php.net/manual/en/function.uniqid.php
     *
     * @return string
     * @since  5.2
     */
    public static function uniqidv4()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * Is google watch changes expiried
     *
     * @return boolean
     * @since  5.2
     */
    public static function isGoogleWatchExpiry()
    {
        $params = JComponentHelper::getParams('com_dropfiles');
        $watchData = $params->get('dropfiles_google_watch_data', '');

        if ($watchData === '') {
            return true;
        }
        $watchData = json_decode($watchData, true);
        if (!is_array($watchData) || !isset($watchData['expiration'])) {
            return true;
        }

        $expiration = (int) $watchData['expiration']; // Expiration time of watch change in milliseconds

        // todo: Time? UTC compare with what?
        if (time() < ($expiration/1000 - 3600)) { // Return expiry before 3600s
            return false;
        }

        return true;
    }

    /**
     * Cancel watch changes
     *
     * @return boolean
     * @since  5.2
     */
    public static function cancelWatchChanges()
    {
        $canDo = DropfilesHelper::getActions();

        if (!$canDo->get('core.admin')) {
            return false;
        }

        $params = JComponentHelper::getParams('com_dropfiles');
        $watchData = $params->get('dropfiles_google_watch_data', '');
        if ($watchData === '') {
            return false;
        }

        $watchData = json_decode($watchData, true);
        if (!is_array($watchData)) {
            return false;
        }

        if (!isset($watchData['id']) || !isset($watchData['resourceId'])) {
            return false;
        }

        $path_drf_google = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesGoogle.php';
        JLoader::register('DropfilesGoogle', $path_drf_google);

        $google = new DropfilesGoogle();

        $google->stopWatch($watchData['id'], $watchData['resourceId']);
        DropfilesComponentHelper::setParams(array('dropfiles_google_watch_data' => '', 'dropfiles_google_last_changes_token' => ''));


        return true;
    }

    /**
     * Watch changes from google drive
     *
     * @return boolean
     * @since  5.2
     */
    public static function watchChanges()
    {
        $canDo = DropfilesHelper::getActions();

        if ($canDo->get('core.admin')) {
            // Cancel any current watch
            self::cancelWatchChanges();

            //JLoader::register('DropfilesGoogle', JPATH_COMPONENT_ADMINISTRATOR . '/classes/dropfilesGoogle.php');

            $newGoogle = new DropfilesGoogle();
            $startPageToken = $newGoogle->getStartPageToken();

            if (!$startPageToken) {
                return false;
            }

            $uuid = self::uniqidv4();
            $watchResponse = $newGoogle->watchChanges($startPageToken, $uuid);

            if (!is_array($watchResponse)) {
                return false;
            }

            DropfilesComponentHelper::setParams(array('dropfiles_google_watch_data' => json_encode($watchResponse), 'dropfiles_google_last_changes_token' => $startPageToken));

            if (isset($watchResponse['error'])) {
                return false;
            }

            return true;
        }

        return false;
    }

    /*----------- OneDrive Business --------*/
    /**
     * Get all OneDrive Business config
     *
     * @return array
     */
    public static function getAllOneDriveBusinessConfigs()
    {
        $params  = JComponentHelper::getParams('com_dropfiles');
        $default = array(
            'onedriveBusinessKey'         => (isset($params['onedriveBusinessKey']) && (string) $params['onedriveBusinessKey'] !== '') ? $params['onedriveBusinessKey'] : '',
            'onedriveBusinessSecret'      => (isset($params['onedriveBusinessSecret']) && (string) $params['onedriveBusinessSecret'] !== '') ? $params['onedriveBusinessSecret'] : '',
            'onedriveBusinessSyncTime'    => isset($params['onedriveBusinessSyncTime']) ? $params['onedriveBusinessSyncTime'] : '30',
            'onedriveBusinessSyncMethod'  => isset($params['onedriveBusinessSyncMethod']) ? $params['onedriveBusinessSyncMethod'] : 'sync_page_curl',
            'onedriveBusinessConnectedBy' => (int)JFactory::getUser()->id,
            'state'                       =>  isset($params['onedriveBusinessState']) ? $params['onedriveBusinessState'] : array(),
            'onedriveBusinessBaseFolder'  => isset($params['onedriveBusinessBaseFolder']) ? $params['onedriveBusinessBaseFolder'] : array(),
            'connected'                   => (isset($params['onedriveBusinessConnected']) && (int)$params['onedriveBusinessConnected'] === 1) ? (int) $params['onedriveBusinessConnected'] : 0
        );

        return $default;
    }

    /**
     * Get onedrive by term id
     *
     * @param integer $categoryId Term id
     *
     * @return boolean
     */
    public static function getOneDriveBusinessIdByTermId($categoryId)
    {
        if (empty($categoryId)) {
            return false;
        }

        $categoryModel  = JModelLegacy::getInstance('Category', 'dropfilesModel');
        $category       = $categoryModel->getCategory($categoryId);
        $result         = $category->cloud_id;
        $type           = $category->type;

        if ($result && $type === 'onedrivebusiness') {
            return $result;
        }

        return false;
    }
}
