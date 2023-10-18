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

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class VolumePacker implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected $box;

    protected $boxWidth;

    protected $boxLength;

    protected $items;

    protected $skippedItems;

    protected $remainingWeight;

    protected $boxRotated = false;

    protected $layers = [];

    protected $lookAheadMode = false;

    public function __construct(Box $box, ItemList $items)
    {
        $this->box = $box;
        $this->items = $items;

        $this->boxWidth = max($this->box->getInnerWidth(), $this->box->getInnerLength());
        $this->boxLength = min($this->box->getInnerWidth(), $this->box->getInnerLength());
        $this->remainingWeight = $this->box->getMaxWeight() - $this->box->getEmptyWeight();
        $this->skippedItems = new ItemList();
        $this->logger = new NullLogger();

        if ($this->box->getInnerWidth() !== $this->boxWidth || $this->box->getInnerLength() !== $this->boxLength) {
            $this->boxRotated = true;
        }
    }

    public function setLookAheadMode($lookAhead)
    {
        $this->lookAheadMode = $lookAhead;
    }

    public function pack()
    {
        $this->logger->debug("[EVALUATING BOX] {$this->box->getReference()}", ['box' => $this->box]);

        while (count($this->items) > 0) {
            $layerStartDepth = $this->getCurrentPackedDepth();
            $this->packLayer($layerStartDepth, $this->boxWidth, $this->boxLength, $this->box->getInnerDepth() - $layerStartDepth);
        }

        if ($this->boxRotated) {
            $this->rotateLayersNinetyDegrees();
        }

        if (!$this->lookAheadMode) {
            $this->stabiliseLayers();
        }

        $this->logger->debug('done with this box');

        return PackedBox::fromPackedItemList($this->box, $this->getPackedItemList());
    }

    protected function packLayer($startDepth, $widthLeft, $lengthLeft, $depthLeft)
    {
        $this->layers[] = $layer = new PackedLayer();
        $prevItem = null;
        $x = $y = $rowWidth = $rowLength = $layerDepth = 0;

        while (count($this->items) > 0) {
            $itemToPack = $this->items->extract();

            if (!$this->checkConstraints($itemToPack)) {
                $this->rebuildItemList();
                continue;
            }

            $orientatedItem = $this->getOrientationForItem($itemToPack, $prevItem, $this->items, $this->hasItemsLeftToPack(), $widthLeft, $lengthLeft, $depthLeft, $rowLength, $x, $y, $startDepth);

            if ($orientatedItem instanceof OrientatedItem) {
                $packedItem = PackedItem::fromOrientatedItem($orientatedItem, $x, $y, $startDepth);
                $layer->insert($packedItem);
                $this->remainingWeight -= $orientatedItem->getItem()->getWeight();
                $widthLeft -= $orientatedItem->getWidth();

                $rowWidth += $orientatedItem->getWidth();
                $rowLength = max($rowLength, $orientatedItem->getLength());
                $layerDepth = max($layerDepth, $orientatedItem->getDepth());

                $stackableDepth = $layerDepth - $orientatedItem->getDepth();
                $this->tryAndStackItemsIntoSpace($layer, $prevItem, $this->items, $orientatedItem->getWidth(), $orientatedItem->getLength(), $stackableDepth, $x, $y, $startDepth + $orientatedItem->getDepth(), $rowLength);
                $x += $orientatedItem->getWidth();

                $prevItem = $packedItem;
                $this->rebuildItemList();
            } elseif (count($layer->getItems()) === 0) { // zero items on layer
                $this->logger->debug("doesn't fit on layer even when empty, skipping for good");
                continue;
            } elseif ($widthLeft > 0 && count($this->items) > 0) { // skip for now, move on to the next item
                $this->logger->debug("doesn't fit, skipping for now");
                $this->skippedItems->insert($itemToPack);
            } elseif ($x > 0 && $lengthLeft >= min($itemToPack->getWidth(), $itemToPack->getLength(), $itemToPack->getDepth())) {
                $this->logger->debug('No more fit in width wise, resetting for new row');
                $widthLeft += $rowWidth;
                $lengthLeft -= $rowLength;
                $y += $rowLength;
                $x = $rowWidth = $rowLength = 0;
                $this->rebuildItemList($itemToPack);
                $prevItem = null;
                continue;
            } else {
                $this->logger->debug('no items fit, so starting next vertical layer');
                $this->rebuildItemList($itemToPack);

                return;
            }
        }
    }

    public function stabiliseLayers()
    {
        $stabiliser = new LayerStabiliser();
        $this->layers = $stabiliser->stabilise($this->layers);
    }

    protected function getOrientationForItem(
        Item $itemToPack,
        PackedItem $prevItem = null,
        ItemList $nextItems,
        $isLastItem,
        $maxWidth,
        $maxLength,
        $maxDepth,
        $rowLength,
        $x,
        $y,
        $z
    ) {
        $this->logger->debug(
            "evaluating item {$itemToPack->getDescription()} for fit",
            [
                'item' => $itemToPack,
                'space' => [
                    'maxWidth' => $maxWidth,
                    'maxLength' => $maxLength,
                    'maxDepth' => $maxDepth,
                ],
            ]
        );

        $prevOrientatedItem = $prevItem ? $prevItem->toOrientatedItem() : null;
        $prevPackedItemList = $itemToPack instanceof ConstrainedPlacementItem ? $this->getPackedItemList() : new PackedItemList(); // don't calculate it if not going to be used

        $orientatedItemFactory = new OrientatedItemFactory($this->box);
        $orientatedItemFactory->setLogger($this->logger);
        $orientatedItemDecision = $orientatedItemFactory->getBestOrientation($itemToPack, $prevOrientatedItem, $nextItems, $isLastItem, $maxWidth, $maxLength, $maxDepth, $rowLength, $x, $y, $z, $prevPackedItemList);

        return $orientatedItemDecision;
    }

    protected function tryAndStackItemsIntoSpace(
        PackedLayer $layer,
        PackedItem $prevItem = null,
        ItemList $nextItems,
        $maxWidth,
        $maxLength,
        $maxDepth,
        $x,
        $y,
        $z,
        $rowLength
    ) {
        while (count($this->items) > 0 && $this->checkNonDimensionalConstraints($this->items->top())) {
            $stackedItem = $this->getOrientationForItem(
                $this->items->top(),
                $prevItem,
                $nextItems,
                $this->items->count() === 1,
                $maxWidth,
                $maxLength,
                $maxDepth,
                $rowLength,
                $x,
                $y,
                $z
            );
            if ($stackedItem) {
                $this->remainingWeight -= $this->items->top()->getWeight();
                $layer->insert(PackedItem::fromOrientatedItem($stackedItem, $x, $y, $z));
                $this->items->extract();
                $maxDepth -= $stackedItem->getDepth();
                $z += $stackedItem->getDepth();
            } else {
                break;
            }
        }
    }

    protected function checkConstraints(
        Item $itemToPack
    ) {
        return $this->checkNonDimensionalConstraints($itemToPack) &&
               $this->checkDimensionalConstraints($itemToPack);
    }

    protected function checkNonDimensionalConstraints(Item $itemToPack)
    {
        $weightOK = $itemToPack->getWeight() <= $this->remainingWeight;

        if ($itemToPack instanceof ConstrainedItem) {
            return $weightOK && $itemToPack->canBePackedInBox($this->getPackedItemList()->asItemList(), $this->box);
        }

        return $weightOK;
    }

    protected function checkDimensionalConstraints(Item $itemToPack)
    {
        $orientatedItemFactory = new OrientatedItemFactory($this->box);
        $orientatedItemFactory->setLogger($this->logger);

        return (bool) $orientatedItemFactory->getPossibleOrientationsInEmptyBox($itemToPack);
    }

    protected function rebuildItemList(Item $currentItem = null)
    {
        if (count($this->items) === 0) {
            $this->items = $this->skippedItems;
            $this->skippedItems = new ItemList();
        }

        if ($currentItem instanceof Item) {
            $this->items->insert($currentItem);
        }
    }

    protected function rotateLayersNinetyDegrees()
    {
        foreach ($this->layers as $i => $originalLayer) {
            $newLayer = new PackedLayer();
            foreach ($originalLayer->getItems() as $item) {
                $packedItem = new PackedItem($item->getItem(), $item->getY(), $item->getX(), $item->getZ(), $item->getLength(), $item->getWidth(), $item->getDepth());
                $newLayer->insert($packedItem);
            }
            $this->layers[$i] = $newLayer;
        }
    }

    protected function hasItemsLeftToPack()
    {
        return count($this->skippedItems) + count($this->items) === 0;
    }

    protected function getPackedItemList()
    {
        $packedItemList = new PackedItemList();
        foreach ($this->layers as $layer) {
            foreach ($layer->getItems() as $packedItem) {
                $packedItemList->insert($packedItem);
            }
        }

        return $packedItemList;
    }

    protected function getCurrentPackedDepth()
    {
        $depth = 0;
        foreach ($this->layers as $layer) {
            $depth += $layer->getDepth();
        }

        return $depth;
    }
}
