<?php
/**
 * @package         SCLogin
 * @copyright (c)   2009-2014 by SourceCoast - All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version         Release v4.3.0
 * @build-date      2015/03/19
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class mod_scloginInstallerScript
{
    var $extReqs = array(
        array('name' => 'JFBConnect', 'version' => '6.1.0', 'element' => 'com_jfbconnect')
    );

    public function preflight($type, $parent)
    {
        foreach ($this->extReqs as $req)
        {
            $currentVersion = $this->getInstalledVersion($req['element']);
            if ($currentVersion && version_compare($currentVersion, $req['version'], '<'))
            {
                $installStr = 'SCLogin requires JFBConnect v6.1.0 or higher for Social Network functionality. Please upgrade JFBConnect to enable the Social Network login features.';
                JFactory::getApplication()->enqueueMessage($installStr, 'error');
            }
        }
    }

    private function getInstalledVersion($element)
    {
        $db = JFactory::getDBO();
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