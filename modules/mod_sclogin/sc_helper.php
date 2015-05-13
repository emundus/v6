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

class SCLibraryUtilities
{
    static function getAffiliateLink($affiliateID)
    {
        if($affiliateID)
            return 'http://www.shareasale.com/r.cfm?b=495360&u='.$affiliateID.'&m=46720&urllink=&afftrack=';
        else
            return 'http://www.sourcecoast.com/joomla-facebook/';
    }

    static function getLinkFromMenuItem($itemId, $isLogout)
    {
        $app =JFactory::getApplication();
        $menu =& $app->getMenu();
        $item =& $menu->getItem($itemId);

        if($item)
        {
            if($item->type == 'url') //External menu item
            {
                $redirect = $item->link;
            }
            else if($item->type == 'alias') //Alias menu item
            {
                $aliasedId = $item->params->get('aliasoptions');

                if($isLogout && SCLibraryUtilities::isMenuRegistered($aliasedId))
                    $link = 'index.php';
                else
                    $link = SCLibraryUtilities::getLinkWithItemId($item->link, $aliasedId);
                $redirect = JRoute::_($link, false);
            }
            else //Regular menu item
            {
                if($isLogout && SCLibraryUtilities::isMenuRegistered($itemId))
                    $link = 'index.php';
                else
                    $link = SCLibraryUtilities::getLinkWithItemId($item->link, $itemId);
                $redirect = JRoute::_($link, false);
            }
        }
        else
            $redirect = '';

        return $redirect;
    }

    static function getLinkWithItemId($link, $itemId)
    {
        $app =JFactory::getApplication();
        $router = $app->getRouter();

        if($link)
        {
            if ($router->getMode() == JROUTER_MODE_SEF)
                $url = 'index.php?Itemid=' . $itemId;
            else
                $url = $link . '&Itemid=' . $itemId;
        }
        else
            $url = '';

        return $url;
    }

    static function isMenuRegistered($menuItemId)
    {
        $db = JFactory::getDBO();
        $query = "SELECT * FROM #__menu WHERE id=" . $db->quote($menuItemId);
        $db->setQuery($query);
        $menuItem = $db->loadObject();
        return ($menuItem && $menuItem->access != "1");
    }
}