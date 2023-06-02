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

interface Box
{
    public function getReference();

    public function getOuterWidth();

    public function getOuterLength();

    public function getOuterDepth();

    public function getEmptyWeight();

    public function getInnerWidth();

    public function getInnerLength();

    public function getInnerDepth();

    public function getInnerVolume();

    public function getMaxWeight();
}
