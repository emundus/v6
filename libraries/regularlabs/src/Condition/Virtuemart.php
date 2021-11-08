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
 * Class Virtuemart
 * @package RegularLabs\Library\Condition
 */
abstract class Virtuemart extends Condition
{
	public function initRequest(&$request)
	{
		$virtuemart_product_id  = JFactory::getApplication()->input->get('virtuemart_product_id', [], 'array');
		$virtuemart_category_id = JFactory::getApplication()->input->get('virtuemart_category_id', [], 'array');

		$request->item_id     = $virtuemart_product_id[0] ?? null;
		$request->category_id = $virtuemart_category_id[0] ?? null;
		$request->id          = $request->item_id ?: $request->category_id;
	}
}
