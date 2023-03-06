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

use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;

class SCLibraryUtilities
{
    static function getAffiliateLink($affiliateID)
    {
        return 'http://www.sourcecoast.com/joomla-facebook/';
    }

    static function getLinkFromMenuItem($itemId, $isLogout)
    {
        $app = Factory::getApplication();
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
                $redirect = Route::_($link, false);
            }
            else //Regular menu item
            {
                if($isLogout && SCLibraryUtilities::isMenuRegistered($itemId))
                    $link = 'index.php';
                else
                    $link = SCLibraryUtilities::getLinkWithItemId($item->link, $itemId);
                $redirect = Route::_($link, false);
            }
        }
        else
            $redirect = '';

        return $redirect;
    }

    static function getLinkWithItemId($link, $itemId)
    {
        $app =Factory::getApplication();
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
        $db = Factory::getDBO();
        $query = "SELECT * FROM #__menu WHERE id=" . $db->quote($menuItemId);
        $db->setQuery($query);
        $menuItem = $db->loadObject();
        return ($menuItem && $menuItem->access != "1");
    }
}