<?php
/**
 * Helper class for Hello World! module
 *
 * @package    Joomla.Tutorials
 * @subpackage Modules
 * @link http://docs.joomla.org/J3.x:Creating_a_simple_module/Developing_a_Basic_Module
 * @license        GNU/GPL, see LICENSE.php
 * mod_helloworld is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
use Joomla\CMS\Uri\Uri;

class ModEmundusSwitchFunnel
{
    /**
     * Retrieves the hello message
     *
     * @param   array  $params An object containing the module parameters
     *
     * @access public
     */
    public static function getRoute($params)
    {
        $uri = Uri::getInstance();
        return strpos($uri->getPath(), 'configuration');
    }

    public static function getCampaignsRoute(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('link').' LIKE '.$db->quote('index.php?option=com_emundus_onboard&view=campaign'));
        $db->setQuery($query);
        return $db->loadObject();
    }

}
