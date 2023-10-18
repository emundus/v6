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

class PackedItemList extends \SplMaxHeap
{
    public function compare($itemA, $itemB)
    {
        if ($itemA->getItem()->getVolume() > $itemB->getItem()->getVolume()) {
            return 1;
        } elseif ($itemA->getItem()->getVolume() < $itemB->getItem()->getVolume()) {
            return -1;
        } else {
            return $itemA->getItem()->getWeight() - $itemB->getItem()->getWeight();
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

    public function asItemArray()
    {
        $return = [];
        foreach (clone $this as $item) {
            $return[] = $item->getItem();
        }

        return $return;
    }

    public function asItemList()
    {
        $return = new ItemList();
        foreach (clone $this as $packedItem) {
            $return->insert($packedItem->getItem());
        }

        return $return;
    }
}
