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
use Psr\Log\NullLogger;

class LayerStabiliser implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    public function stabilise(array $packedLayers)
    {
        $stabilisedLayers = [];
        usort($packedLayers, [$this, 'compare']);

        $currentZ = 0;
        foreach ($packedLayers as $oldZLayer) {
            $oldZStart = $oldZLayer->getStartDepth();
            $newZLayer = new PackedLayer();
            foreach ($oldZLayer->getItems() as $oldZItem) {
                $newZ = $oldZItem->getZ() - $oldZStart + $currentZ;
                $newZItem = new PackedItem($oldZItem->getItem(), $oldZItem->getX(), $oldZItem->getY(), $newZ, $oldZItem->getWidth(), $oldZItem->getLength(), $oldZItem->getDepth());
                $newZLayer->insert($newZItem);
            }

            $stabilisedLayers[] = $newZLayer;
            $currentZ += $newZLayer->getDepth();
        }

        return $stabilisedLayers;
    }

    private function compare(PackedLayer $layerA, PackedLayer $layerB)
    {
        return ($layerB->getFootprint() - $layerA->getFootprint()) ?: ($layerB->getDepth() - $layerA->getDepth());
    }
}
