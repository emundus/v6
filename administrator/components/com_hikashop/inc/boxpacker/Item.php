<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.6.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2022 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

namespace DVDoug\BoxPacker;

interface Item
{
    public function getDescription();

    public function getWidth();

    public function getLength();

    public function getDepth();

    public function getWeight();

    public function getVolume();

    public function getKeepFlat();
}
