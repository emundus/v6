<?php
/**
 * @package         Regular Labs Library
 * @version         22.4.18687
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library\Condition;

defined('_JEXEC') or die;

/**
 * Class DateMonth
 * @package RegularLabs\Library\Condition
 */
class DateMonth extends Date
{
	public function pass()
	{
		$month = $this->date->format('m', true); // 01 (for January) through 12 (for December)

		return $this->passSimple((int) $month);
	}
}
