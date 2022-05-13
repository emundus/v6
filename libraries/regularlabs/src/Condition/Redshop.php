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

use Joomla\CMS\Factory as JFactory;
use RegularLabs\Library\Condition;

/**
 * Class Redshop
 * @package RegularLabs\Library\Condition
 */
abstract class Redshop extends Condition
{
	public function initRequest(&$request)
	{
		$request->item_id     = JFactory::getApplication()->input->getInt('pid', 0);
		$request->category_id = JFactory::getApplication()->input->getInt('cid', 0);
		$request->id          = $request->item_id ?: $request->category_id;
	}
}
