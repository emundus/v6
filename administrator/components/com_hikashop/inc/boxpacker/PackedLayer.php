<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
namespace DVDoug\BoxPacker;

class PackedLayer
{
    protected $items = [];

    public function insert(PackedItem $packedItem)
    {
        $this->items[] = $packedItem;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function getFootprint()
    {
        $layerWidth = 0;
        $layerLength = 0;

        foreach ($this->items as $item) {
            $layerWidth = max($layerWidth, $item->getX() + $item->getWidth());
            $layerLength = max($layerLength, $item->getY() + $item->getLength());
        }

        return $layerWidth * $layerLength;
    }

    public function getStartDepth()
    {
        $startDepth = PHP_INT_MAX;

        foreach ($this->items as $item) {
            $startDepth = min($startDepth, $item->getZ());
        }

        return $startDepth;
    }

    public function getDepth()
    {
        $layerDepth = 0;

        foreach ($this->items as $item) {
            $layerDepth = max($layerDepth, $item->getZ() + $item->getDepth());
        }

        return $layerDepth - $this->getStartDepth();
    }
}
