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
 * Class FlexicontentPagetype
 * @package RegularLabs\Library\Condition
 */
class FlexicontentPagetype extends Flexicontent
{
	public function pass()
	{
		return $this->passByPageType('com_flexicontent', $this->selection, $this->include_type);
	}
}
