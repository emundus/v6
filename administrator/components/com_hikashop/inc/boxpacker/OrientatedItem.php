<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

namespace DVDoug\BoxPacker;

use JsonSerializable;

class OrientatedItem implements JsonSerializable
{
    protected $item;

    protected $width;

    protected $length;

    protected $depth;

    protected static $tippingPointCache = [];

    public function __construct(Item $item, $width, $length, $depth)
    {
        $this->item = $item;
        $this->width = $width;
        $this->length = $length;
        $this->depth = $depth;
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

    public function getSurfaceFootprint()
    {
        return $this->width * $this->length;
    }

    public function getTippingPoint()
    {
        $cacheKey = $this->width . '|' . $this->length . '|' . $this->depth;

        if (isset(static::$tippingPointCache[$cacheKey])) {
            $tippingPoint = static::$tippingPointCache[$cacheKey];
        } else {
            $tippingPoint = atan(min($this->length, $this->width) / ($this->depth ?: 1));
            static::$tippingPointCache[$cacheKey] = $tippingPoint;
        }

        return $tippingPoint;
    }

    public function isStable()
    {
        return $this->getTippingPoint() > 0.261;
    }

    public function jsonSerialize()
    {
        return [
            'item' => $this->item,
            'width' => $this->width,
            'length' => $this->length,
            'depth' => $this->depth,
        ];
    }

    public function __toString()
    {
        return $this->width . '|' . $this->length . '|' . $this->depth;
    }
}
