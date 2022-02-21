<?php
/**
 * @package         Regular Labs Library
 * @version         21.9.16879
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2021 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library\Condition;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use RegularLabs\Library\Condition;

/**
 * Class Language
 * @package RegularLabs\Library\Condition
 */
class Language extends Condition
{
	public function pass()
	{
		return $this->passSimple(JFactory::getLanguage()->getTag(), true);
	}
}
