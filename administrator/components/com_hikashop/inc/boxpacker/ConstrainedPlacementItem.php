<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.4.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
namespace DVDoug\BoxPacker;

interface ConstrainedPlacementItem extends Item
{
    public function canBePacked(
        Box $box,
        PackedItemList $alreadyPackedItems,
        $proposedX,
        $proposedY,
        $proposedZ,
        $width,
        $length,
        $depth
    );
}
