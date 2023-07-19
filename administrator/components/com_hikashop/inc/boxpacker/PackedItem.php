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

class PackedItem
{
    protected $x;

    protected $y;

    protected $z;

    protected $item;

    protected $width;

    protected $length;

    protected $depth;

    public function __construct(Item $item, $x, $y, $z, $width, $length, $depth)
    {
        $this->item = $item;
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->width = $width;
        $this->length = $length;
        $this->depth = $depth;
    }

    public function getX()
    {
        return $this->x;
    }

    public function getY()
    {
        return $this->y;
    }

    public function getZ()
    {
        return $this->z;
    }

    public function getItem()
    {
        return $this->item;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getLength()
    {
        return $this->length;
    }

    public function getDepth()
    {
        return $this->depth;
    }

    public function getVolume()
    {
        return $this->width * $this->length * $this->depth;
    }

    public static function fromOrientatedItem(OrientatedItem $orientatedItem, $x, $y, $z)
    {
        return new static(
            $orientatedItem->getItem(),
            $x,
            $y,
            $z,
            $orientatedItem->getWidth(),
            $orientatedItem->getLength(),
            $orientatedItem->getDepth()
        );
    }

    public function toOrientatedItem()
    {
        return new OrientatedItem($this->item, $this->width, $this->length, $this->depth);
    }
}
