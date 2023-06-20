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

class BoxList extends \SplMinHeap
{
    public function compare($boxA, $boxB)
    {
        $boxAVolume = $boxA->getInnerWidth() * $boxA->getInnerLength() * $boxA->getInnerDepth();
        $boxBVolume = $boxB->getInnerWidth() * $boxB->getInnerLength() * $boxB->getInnerDepth();

        if ($boxBVolume > $boxAVolume) {
            return 1;
        }
        if ($boxAVolume > $boxBVolume) {
            return -1;
        }

        if ($boxB->getEmptyWeight() > $boxA->getEmptyWeight()) {
            return 1;
        }
        if ($boxA->getEmptyWeight() > $boxB->getEmptyWeight()) {
            return -1;
        }

        if ($boxB->getMaxWeight() > $boxA->getMaxWeight()) {
            return 1;
        }
        if ($boxA->getMaxWeight() > $boxB->getMaxWeight()) {
            return -1;
        }

        return 0;
    }
}
