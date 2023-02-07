<?php
/**
 * @package         SCLogin
 * @copyright (c)   2009-2021 by SourceCoast - All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version         Release v9.0.215
 * @build-date      2022/09/06
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

class mod_scloginInstallerScript
{
    var $extReqs = array(
        array('name' => 'JFBConnect', 'version' => '9.0.138', 'element' => 'com_jfbconnect')
    );

    public function preflight($type, $parent)
    {
        foreach ($this->extReqs as $req)
        {
            $currentVersion = $this->getInstalledVersion($req['element']);
            if ($currentVersion && version_compare($currentVersion, $req['version'], '<'))
            {
                $installStr = 'SCLogin requires JFBConnect v9.0.138 or higher for Social Network functionality. Please upgrade JFBConnect to enable the Social Network login features.';
                Factory::getApplication()->enqueueMessage($installStr, 'error');
            }
        }
    }

    private function getInstalledVersion($element)
    {
        $db = Factory::getDBO();
        $query = $db->getQuery(true);
        $query->select('manifest_cache')->from('#__extensions')->where($db->qn('element') . '=' . $db->q($element));
        $db->setQuery($query);
        $manifest = $db->loadResult();
        if ($manifest)
        {
            $manifest = json_decode($manifest);
            return $manifest->version;
        }
        else
            return "";
    }
}