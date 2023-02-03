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

use Joomla\CMS\Factory as JFactory;
use RegularLabs\Library\Condition;

defined('_JEXEC') or die;

/**
 * Class Component
 * @package RegularLabs\Library\Condition
 */
class Component extends Condition
{
	public function pass()
	{
		$option = JFactory::getApplication()->input->get('option') == 'com_categories'
			? 'com_categories'
			: $this->request->option;

		return $this->passSimple(strtolower($option));
	}
}
