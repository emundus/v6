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
namespace Payplug\Resource;
use Payplug;

class Card extends APIResource
{
    public static function fromAttributes(array $attributes)
    {
        $object = new Card();
        $object->initialize($attributes);
        return $object;
    }

    public static function deleteCard($card, Payplug\Payplug $payplug = null)
    {
        if ($payplug === null) {
            $payplug = Payplug\Payplug::getDefaultConfiguration();
        }

        if ($card instanceof Card) {
            $card = $card->id;
        }

        $httpClient = new Payplug\Core\HttpClient($payplug);
        $response = $httpClient->delete(Payplug\Core\APIRoutes::getRoute(Payplug\Core\APIRoutes::CARD_RESOURCE, $card));

        return $response;
    }

    public function delete(Payplug\Payplug $payplug = null)
    {
        self::deleteCard($this->id, $payplug);
    }
}
