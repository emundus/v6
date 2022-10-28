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
 * Class HikashopCategory
 * @package RegularLabs\Library\Condition
 */
class HikashopCategory extends Hikashop
{
	public function pass()
	{
		if ($this->request->option != 'com_hikashop')
		{
			return $this->_(false);
		}

		$pass = (
			($this->params->inc_categories
				&& ($this->request->view == 'category' || $this->request->layout == 'listing')
			)
			|| ($this->params->inc_items && $this->request->view == 'product')
		);

		if ( ! $pass)
		{
			return $this->_(false);
		}

		$cats = $this->getCategories();

		$pass = $this->passSimple($cats, false, 'include');

		if ($pass && $this->params->inc_children == 2)
		{
			return $this->_(false);
		}

		if ( ! $pass && $this->params->inc_children)
		{
			foreach ($cats as $cat)
			{
				$cats = array_merge($cats, $this->getCatParentIds($cat));
			}
		}

		return $this->passSimple($cats);
	}

	private function getCategories()
	{
		switch (true)
		{
			case (($this->request->view == 'category' || $this->request->layout == 'listing') && $this->request->id):
				return [$this->request->id];

			case ($this->request->view == 'category' || $this->request->layout == 'listing'):
				include_once JPATH_ADMINISTRATOR . '/components/com_hikashop/helpers/helper.php';
				$menuClass = hikashop_get('class.menus');
				$menuData  = $menuClass->get($this->request->Itemid);

				return $this->makeArray($menuData->hikashop_params['selectparentlisting']);

			case ($this->request->id):
				$query = $this->db->getQuery(true)
					->select('c.category_id')
					->from('#__hikashop_product_category AS c')
					->where('c.product_id = ' . (int) $this->request->id);
				$this->db->setQuery($query);
				$cats = $this->db->loadColumn();

				return $this->makeArray($cats);

			default:
				return [];
		}
	}

	private function getCatParentIds($id = 0)
	{
		return $this->getParentIds($id, 'hikashop_category', 'category_parent_id', 'category_id');
	}
}
