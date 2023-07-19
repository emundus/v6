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
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

class Packer implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected $maxBoxesToBalanceWeight = 12;

    protected $items;

    protected $boxes;

    public function __construct()
    {
        $this->items = new ItemList();
        $this->boxes = new BoxList();

        $this->logger = new NullLogger();
    }

    public function addItem(Item $item, $qty = 1)
    {
        for ($i = 0; $i < $qty; ++$i) {
            $this->items->insert($item);
        }
        $this->logger->log(LogLevel::INFO, "added {$qty} x {$item->getDescription()}", ['item' => $item]);
    }

    public function setItems($items)
    {
        if ($items instanceof ItemList) {
            $this->items = clone $items;
        } else {
            $this->items = new ItemList();
            foreach ($items as $item) {
                $this->items->insert($item);
            }
        }
    }

    public function addBox(Box $box)
    {
        $this->boxes->insert($box);
        $this->logger->log(LogLevel::INFO, "added box {$box->getReference()}", ['box' => $box]);
    }

    public function setBoxes(BoxList $boxList)
    {
        $this->boxes = clone $boxList;
    }

    public function getMaxBoxesToBalanceWeight()
    {
        return $this->maxBoxesToBalanceWeight;
    }

    public function setMaxBoxesToBalanceWeight($maxBoxesToBalanceWeight)
    {
        $this->maxBoxesToBalanceWeight = $maxBoxesToBalanceWeight;
    }

    public function pack()
    {
        $packedBoxes = $this->doVolumePacking();

        if ($packedBoxes->count() > 1 && $packedBoxes->count() <= $this->maxBoxesToBalanceWeight) {
            $redistributor = new WeightRedistributor($this->boxes);
            $redistributor->setLogger($this->logger);
            $packedBoxes = $redistributor->redistributeWeight($packedBoxes);
        }

        $this->logger->log(LogLevel::INFO, "[PACKING COMPLETED], {$packedBoxes->count()} boxes");

        return $packedBoxes;
    }

    public function doVolumePacking()
    {
        $packedBoxes = new PackedBoxList();

        while ($this->items->count()) {
            $boxesToEvaluate = clone $this->boxes;
            $packedBoxesIteration = new PackedBoxList();

            while (!$boxesToEvaluate->isEmpty()) {
                $box = $boxesToEvaluate->extract();

                $volumePacker = new VolumePacker($box, clone $this->items);
                $volumePacker->setLogger($this->logger);
                $packedBox = $volumePacker->pack();
                if ($packedBox->getItems()->count()) {
                    $packedBoxesIteration->insert($packedBox);

                    if ($packedBox->getItems()->count() === $this->items->count()) {
                        break;
                    }
                }
            }

            if ($packedBoxesIteration->isEmpty()) {
                throw new ItemTooLargeException('Item '.$this->items->top()->getDescription().' is too large to fit into any box', $this->items->top());
            }


            $bestBox = $packedBoxesIteration->top();
            $unPackedItems = $this->items->asArray();
            foreach (clone $bestBox->getItems() as $packedItem) {
                foreach ($unPackedItems as $unpackedKey => $unpackedItem) {
                    if ($packedItem === $unpackedItem) {
                        unset($unPackedItems[$unpackedKey]);
                        break;
                    }
                }
            }
            $unpackedItemList = new ItemList();
            foreach ($unPackedItems as $unpackedItem) {
                $unpackedItemList->insert($unpackedItem);
            }
            $this->items = $unpackedItemList;
            $packedBoxes->insert($bestBox);
        }

        return $packedBoxes;
    }
}
