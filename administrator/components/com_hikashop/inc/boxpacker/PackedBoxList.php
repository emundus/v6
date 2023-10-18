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

class PackedBoxList extends \SplMinHeap
{
    protected $meanWeight;

    public function compare($boxA, $boxB)
    {
        $choice = $boxA->getItems()->count() - $boxB->getItems()->count();
        if ($choice == 0) {
            $choice = $boxA->getVolumeUtilisation() - $boxB->getVolumeUtilisation();
        }
        if ($choice == 0) {
            $choice = $boxA->getUsedVolume() - $boxB->getUsedVolume();
        }
        if ($choice == 0) {
            $choice = $boxA->getWeight() - $boxB->getWeight();
        }

        return $choice;
    }

    public function reverseCompare($boxA, $boxB)
    {
        $choice = $boxB->getItems()->count() - $boxA->getItems()->count();
        if ($choice === 0) {
            $choice = $boxA->getBox()->getInnerVolume() - $boxB->getBox()->getInnerVolume();
        }
        if ($choice === 0) {
            $choice = $boxB->getWeight() - $boxA->getWeight();
        }

        return $choice;
    }

    public function getMeanWeight()
    {
        if (!is_null($this->meanWeight)) {
            return $this->meanWeight;
        }

        foreach (clone $this as $box) {
            $this->meanWeight += $box->getWeight();
        }

        return $this->meanWeight /= $this->count();
    }

    public function getWeightVariance()
    {
        $mean = $this->getMeanWeight();

        $weightVariance = 0;
        foreach (clone $this as $box) {
            $weightVariance += pow($box->getWeight() - $mean, 2);
        }

        return round($weightVariance / $this->count(), 1);
    }

    public function getVolumeUtilisation()
    {
        $itemVolume = 0;
        $boxVolume = 0;


        foreach (clone $this as $box) {
            $boxVolume += $box->getBox()->getInnerVolume();


            foreach (clone $box->getItems() as $item) {
                $itemVolume += $item->getVolume();
            }
        }

        return round($itemVolume / $boxVolume * 100, 1);
    }

    public function insertFromArray(array $boxes)
    {
        foreach ($boxes as $box) {
            $this->insert($box);
        }
    }
}
