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

class ItemList extends \SplMaxHeap
{
    public function compare($itemA, $itemB)
    {
        if ($itemA->getVolume() > $itemB->getVolume()) {
            return 1;
        } elseif ($itemA->getVolume() < $itemB->getVolume()) {
            return -1;
        } elseif ($itemA->getWeight() !== $itemB->getWeight()) {
            return $itemA->getWeight() - $itemB->getWeight();
        } elseif ($itemA->getDescription() < $itemB->getDescription()) {
            return 1;
        } else {
            return -1;
        }
    }

    public function asArray()
    {
        $return = [];
        foreach (clone $this as $item) {
            $return[] = $item;
        }

        return $return;
    }

    public function topN($n)
    {
        $workingList = clone $this;
        $topNList = new self();
        $i = 0;
        while(!$workingList->isEmpty() && $i < $n) {
            $topNList->insert($workingList->extract());
            $i++;
        }

        return $topNList;
    }

    public function remove(Item $item)
    {
        $workingSet = [];
        while (!$this->isEmpty()) {
            $workingSet[] = $this->extract();
        }

        $removed = false; // there can be multiple identical items, ensure that only 1 is removed
        foreach ($workingSet as $workingSetItem) {
            if (!$removed && $workingSetItem === $item) {
                $removed = true;
            } else {
                $this->insert($workingSetItem);
            }
        }

    }
}
