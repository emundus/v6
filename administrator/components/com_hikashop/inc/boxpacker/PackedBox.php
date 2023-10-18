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

use RuntimeException;

class PackedBox
{
    protected $box;

    protected $items;

    protected $weight;

    protected $itemWeight;

    protected $remainingWidth;

    protected $remainingLength;

    protected $remainingDepth;

    protected $remainingWeight;

    protected $usedWidth;

    protected $usedLength;

    protected $usedDepth;

    protected $packedItemList;

    public function getBox()
    {
        return $this->box;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function getWeight()
    {
        return $this->box->getEmptyWeight() + $this->getItemWeight();
    }

    public function getItemWeight()
    {
        if (!is_null($this->itemWeight)) {
            return $this->itemWeight;
        }
        $this->itemWeight = 0;

        foreach (clone $this->items as $item) {
            $this->itemWeight += $item->getWeight();
        }

        return $this->itemWeight;
    }

    public function getRemainingWidth()
    {
        return $this->remainingWidth;
    }

    public function getRemainingLength()
    {
        return $this->remainingLength;
    }

    public function getRemainingDepth()
    {
        return $this->remainingDepth;
    }

    public function getUsedWidth()
    {
        return $this->usedWidth;
    }

    public function getUsedLength()
    {
        return $this->usedLength;
    }

    public function getUsedDepth()
    {
        return $this->usedDepth;
    }

    public function getRemainingWeight()
    {
        return $this->remainingWeight;
    }

    public function getInnerVolume()
    {
        return $this->box->getInnerWidth() * $this->box->getInnerLength() * $this->box->getInnerDepth();
    }

    public function getUsedVolume()
    {
        $volume = 0;

        foreach (clone $this->items as $item) {
            $volume += $item->getVolume();
        }

        return $volume;
    }

    public function getUnusedVolume()
    {
        return $this->getInnerVolume() - $this->getUsedVolume();
    }

    public function getVolumeUtilisation()
    {
        $itemVolume = 0;


        foreach (clone $this->items as $item) {
            $itemVolume += $item->getVolume();
        }

        return round($itemVolume / $this->box->getInnerVolume() * 100, 1);
    }

    public function getPackedItems()
    {
        if (!$this->packedItemList instanceof PackedItemList) {
            throw new RuntimeException('No PackedItemList was set. Are you using the old constructor?');
        }
        return $this->packedItemList;
    }

    public function setPackedItems(PackedItemList $packedItemList)
    {
        $this->packedItemList = $packedItemList;
    }

    public function __construct(
        Box $box,
        ItemList $itemList,
        $remainingWidth,
        $remainingLength,
        $remainingDepth,
        $remainingWeight,
        $usedWidth,
        $usedLength,
        $usedDepth
    ) {
        $this->box = $box;
        $this->items = $itemList;
        $this->remainingWidth = $remainingWidth;
        $this->remainingLength = $remainingLength;
        $this->remainingDepth = $remainingDepth;
        $this->remainingWeight = $remainingWeight;
        $this->usedWidth = $usedWidth;
        $this->usedLength = $usedLength;
        $this->usedDepth = $usedDepth;
    }

    public static function fromPackedItemList(Box $box, PackedItemList $packedItems)
    {
        $maxWidth = $maxLength = $maxDepth = $weight = 0;

        foreach (clone $packedItems as $item) {
            $maxWidth = max($maxWidth, $item->getX() + $item->getWidth());
            $maxLength = max($maxLength, $item->getY() + $item->getLength());
            $maxDepth = max($maxDepth, $item->getZ() + $item->getDepth());
            $weight += $item->getItem()->getWeight();
        }

        $packedBox = new self(
            $box,
            $packedItems->asItemList(),
            $box->getInnerWidth() - $maxWidth,
            $box->getInnerLength() - $maxLength,
            $box->getInnerDepth() - $maxDepth,
            $box->getMaxWeight() - $box->getEmptyWeight() - $weight,
            $maxWidth,
            $maxLength,
            $maxDepth
        );
        $packedBox->setPackedItems($packedItems);

        return $packedBox;
    }
}
