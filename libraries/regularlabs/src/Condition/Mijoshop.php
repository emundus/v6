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
use MijoShop as MijoShopClass;
use RegularLabs\Library\Condition;

/**
 * Class Mijoshop
 * @package RegularLabs\Library\Condition
 */
abstract class Mijoshop extends Condition
{
	public function initRequest(&$request)
	{
		$input = JFactory::getApplication()->input;

		$category_id = $input->getCmd('path', 0);

		if (strpos($category_id, '_'))
		{
			$category_parts = explode('_', $category_id);
			$category_id    = end($category_parts);
		}

		$request->item_id     = $input->getInt('product_id', 0);
		$request->category_id = $category_id;
		$request->id          = $request->item_id ?: $request->category_id;

		$view = $input->getCmd('view', '');

		if (empty($view))
		{
			$mijoshop = JPATH_ROOT . '/components/com_mijoshop/mijoshop/mijoshop.php';

			if ( ! file_exists($mijoshop))
			{
				return;
			}

			require_once $mijoshop;

			$route = $input->getString('route', '');
			$view  = MijoShopClass::get('router')->getView($route);
		}

		$request->view = $view;
	}
}
