<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.2.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2019 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
namespace Payplug;

class Notification
{
    public static function treat($requestBody, $authentication = null)
    {
        $postArray = json_decode($requestBody, true);

        if ($postArray === null) {
            throw new Exception\UnknownAPIResourceException('Request body is not valid JSON.');
        }

        $unsafeAPIResource = Resource\APIResource::factory($postArray);
        return $unsafeAPIResource->getConsistentResource($authentication);
    }
}
