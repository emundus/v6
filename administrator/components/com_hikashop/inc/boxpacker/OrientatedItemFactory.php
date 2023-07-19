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

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class OrientatedItemFactory implements LoggerAwareInterface
{
    use LoggerAwareTrait;


    protected $box;

    protected static $emptyBoxCache = [];

    public function __construct(Box $box)
    {
        $this->box = $box;
    }

    public function getBestOrientation(
        Item $item,
        OrientatedItem $prevItem = null,
        ItemList $nextItems,
        $isLastItem,
        $widthLeft,
        $lengthLeft,
        $depthLeft,
        $rowLength,
        $x,
        $y,
        $z,
        PackedItemList $prevPackedItemList
    ) {
        $possibleOrientations = $this->getPossibleOrientations($item, $prevItem, $widthLeft, $lengthLeft, $depthLeft, $x, $y, $z, $prevPackedItemList);
        $usableOrientations = $this->getUsableOrientations($item, $possibleOrientations, $isLastItem);

        if (empty($usableOrientations)) {
            return null;
        }

        usort($usableOrientations, function (OrientatedItem $a, OrientatedItem $b) use ($widthLeft, $lengthLeft, $depthLeft, $nextItems, $rowLength, $x, $y, $z, $prevPackedItemList) {
            $orientationAWidthLeft = $widthLeft - $a->getWidth();
            $orientationALengthLeft = $lengthLeft - $a->getLength();
            $orientationADepthLeft = $depthLeft - $a->getDepth();
            $orientationBWidthLeft = $widthLeft - $b->getWidth();
            $orientationBLengthLeft = $lengthLeft - $b->getLength();
            $orientationBDepthLeft = $depthLeft - $b->getDepth();

            $orientationAMinGap = min($orientationAWidthLeft, $orientationALengthLeft);
            $orientationBMinGap = min($orientationBWidthLeft, $orientationBLengthLeft);

            if ($orientationAMinGap === 0 && ($orientationBMinGap !== 0 || PHP_MAJOR_VERSION > 5)) { // prefer A if it leaves no gap
                return -1;
            }
            if ($orientationBMinGap === 0) { // prefer B if it leaves no gap
                return 1;
            }

            if ($nextItems->count()) {
                $nextItemFitA = count($this->getPossibleOrientations($nextItems->top(), $a, $orientationAWidthLeft, $lengthLeft, $depthLeft, $x, $y, $z, $prevPackedItemList));
                $nextItemFitB = count($this->getPossibleOrientations($nextItems->top(), $b, $orientationBWidthLeft, $lengthLeft, $depthLeft, $x, $y, $z, $prevPackedItemList));
                if ($nextItemFitA && !$nextItemFitB) {
                    return -1;
                }
                if ($nextItemFitB && !$nextItemFitA) {
                    return 1;
                }

                $additionalPackedA = $this->calculateAdditionalItemsPackedWithThisOrientation($a, $nextItems, $widthLeft, $lengthLeft, $depthLeft, $rowLength);
                $additionalPackedB = $this->calculateAdditionalItemsPackedWithThisOrientation($b, $nextItems, $widthLeft, $lengthLeft, $depthLeft, $rowLength);
                if ($additionalPackedA > $additionalPackedB) {
                    return -1;
                }
                if ($additionalPackedA < $additionalPackedB) {
                    return 1;
                }
                if ($additionalPackedA === 0) {
                    return PHP_MAJOR_VERSION > 5 ? -1 : 1;
                }
            }
            return ($orientationADepthLeft - $orientationBDepthLeft) ?: ($orientationAMinGap - $orientationBMinGap) ?: ($a->getSurfaceFootprint() - $b->getSurfaceFootprint());
        });

        $bestFit = reset($usableOrientations);
        $this->logger->debug('Selected best fit orientation', ['orientation' => $bestFit]);

        return $bestFit;
    }

    public function getPossibleOrientations(
        Item $item,
        OrientatedItem $prevItem = null,
        $widthLeft,
        $lengthLeft,
        $depthLeft,
        $x,
        $y,
        $z,
        PackedItemList $prevPackedItemList
    ) {
        $orientations = [];

        if ($prevItem && $this->isSameDimensions($prevItem->getItem(), $item)) {
            $orientations[] = new OrientatedItem($item, $prevItem->getWidth(), $prevItem->getLength(), $prevItem->getDepth());
        } else {
            $orientations[] = new OrientatedItem($item, $item->getWidth(), $item->getLength(), $item->getDepth());
            $orientations[] = new OrientatedItem($item, $item->getLength(), $item->getWidth(), $item->getDepth());

            if (!$item->getKeepFlat()) {
                $orientations[] = new OrientatedItem($item, $item->getWidth(), $item->getDepth(), $item->getLength());
                $orientations[] = new OrientatedItem($item, $item->getLength(), $item->getDepth(), $item->getWidth());
                $orientations[] = new OrientatedItem($item, $item->getDepth(), $item->getWidth(), $item->getLength());
                $orientations[] = new OrientatedItem($item, $item->getDepth(), $item->getLength(), $item->getWidth());
            }
        }

        $orientations = array_unique($orientations);

        $orientations = array_filter($orientations, function (OrientatedItem $i) use ($widthLeft, $lengthLeft, $depthLeft) {
            return $i->getWidth() <= $widthLeft && $i->getLength() <= $lengthLeft && $i->getDepth() <= $depthLeft;
        });

        if ($item instanceof ConstrainedPlacementItem) {
            $box = $this->box;
            $orientations = array_filter($orientations, function (OrientatedItem $i) use ($box, $x, $y, $z, $prevPackedItemList) {

                $constrainedItem = $i->getItem();

                return $constrainedItem->canBePacked($box, $prevPackedItemList, $x, $y, $z, $i->getWidth(), $i->getLength(), $i->getDepth());
            });
        }

        return $orientations;
    }

    public function getPossibleOrientationsInEmptyBox(Item $item)
    {
        $cacheKey = $item->getWidth() .
            '|' .
            $item->getLength() .
            '|' .
            $item->getDepth() .
            '|' .
            ($item->getKeepFlat() ? '2D' : '3D') .
            '|' .
            $this->box->getInnerWidth() .
            '|' .
            $this->box->getInnerLength() .
            '|' .
            $this->box->getInnerDepth();

        if (isset(static::$emptyBoxCache[$cacheKey])) {
            $orientations = static::$emptyBoxCache[$cacheKey];
        } else {
            $orientations = $this->getPossibleOrientations(
                $item,
                null,
                $this->box->getInnerWidth(),
                $this->box->getInnerLength(),
                $this->box->getInnerDepth(),
                0,
                0,
                0,
                new PackedItemList()
            );
            static::$emptyBoxCache[$cacheKey] = $orientations;
        }

        return $orientations;
    }

    protected function getUsableOrientations(
        Item $item,
        $possibleOrientations,
        $isLastItem
    ) {
        $orientationsToUse = $stableOrientations = $unstableOrientations = [];

        foreach ($possibleOrientations as $orientation) {
            if ($orientation->isStable()) {
                $stableOrientations[] = $orientation;
            } else {
                $unstableOrientations[] = $orientation;
            }
        }

        if (count($stableOrientations) > 0) {
            $orientationsToUse = $stableOrientations;
        } elseif (count($unstableOrientations) > 0) {
            $stableOrientationsInEmptyBox = $this->getStableOrientationsInEmptyBox($item);

            if ($isLastItem || count($stableOrientationsInEmptyBox) === 0) {
                $orientationsToUse = $unstableOrientations;
            }
        }

        return $orientationsToUse;
    }

    protected function getStableOrientationsInEmptyBox(Item $item)
    {
        $orientationsInEmptyBox = $this->getPossibleOrientationsInEmptyBox($item);

        return array_filter(
            $orientationsInEmptyBox,
            function (OrientatedItem $orientation) {
                return $orientation->isStable();
            }
        );
    }

    protected function isSameDimensions(Item $itemA, Item $itemB)
    {
        $itemADimensions = [$itemA->getWidth(), $itemA->getLength(), $itemA->getDepth()];
        $itemBDimensions = [$itemB->getWidth(), $itemB->getLength(), $itemB->getDepth()];
        sort($itemADimensions);
        sort($itemBDimensions);

        return $itemADimensions === $itemBDimensions;
    }

    protected function calculateAdditionalItemsPackedWithThisOrientation(
        OrientatedItem $prevItem,
        ItemList $nextItems,
        $originalWidthLeft,
        $originalLengthLeft,
        $depthLeft,
        $currentRowLengthBeforePacking
    ) {
        $packedCount = 0;

        $currentRowLength = max($prevItem->getLength(), $currentRowLengthBeforePacking);

        $itemsToPack = $nextItems->topN(8); // cap lookahead as this gets recursive and slow

        $tempBox = new WorkingVolume($originalWidthLeft - $prevItem->getWidth(), $currentRowLength, $depthLeft, PHP_INT_MAX);
        $tempPacker = new VolumePacker($tempBox, clone $itemsToPack);
        $tempPacker->setLookAheadMode(true);
        $remainingRowPacked = $tempPacker->pack();

        foreach ($remainingRowPacked->getItems() as $packedItem) {
            $itemsToPack->remove($packedItem);
        }

        $tempBox = new WorkingVolume($originalWidthLeft, $originalLengthLeft - $currentRowLength, $depthLeft, PHP_INT_MAX);
        $tempPacker = new VolumePacker($tempBox, clone $itemsToPack);
        $tempPacker->setLookAheadMode(true);
        $nextRowsPacked = $tempPacker->pack();

        foreach ($nextRowsPacked->getItems() as $packedItem) {
            $itemsToPack->remove($packedItem);
        }

        $this->logger->debug('Lookahead with orientation', ['packedCount' => $packedCount, 'orientatedItem' => $prevItem]);

        return $nextItems->count() - $itemsToPack->count();
    }
}
