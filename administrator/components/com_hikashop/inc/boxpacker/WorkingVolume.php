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

class WorkingVolume implements Box, JsonSerializable
{
    private $width;

    private $length;

    private $depth;

    private $maxWeight;

    public function __construct(
        $width,
        $length,
        $depth,
        $maxWeight
    ) {
        $this->width = $width;
        $this->length = $length;
        $this->depth = $depth;
        $this->maxWeight = $maxWeight;
    }

    public function getReference()
    {
        return 'Working Volume';
    }

    public function getOuterWidth()
    {
        return $this->width;
    }

    public function getOuterLength()
    {
        return $this->length;
    }

    public function getOuterDepth()
    {
        return $this->depth;
    }

    public function getEmptyWeight()
    {
        return 0;
    }

    public function getInnerWidth()
    {
        return $this->width;
    }

    public function getInnerLength()
    {
        return $this->length;
    }

    public function getInnerDepth()
    {
        return $this->depth;
    }

    public function getMaxWeight()
    {
        return $this->maxWeight;
    }

    public function getInnerVolume()
    {
        return $this->width * $this->length * $this->depth;
    }

    public function jsonSerialize()
    {
        return [
            'reference' => $this->getReference(),
            'width' => $this->width,
            'length' => $this->length,
            'depth' => $this->depth,
            'maxWeight' => $this->maxWeight,
        ];
    }
}
