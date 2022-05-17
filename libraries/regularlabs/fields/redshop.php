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

defined('_JEXEC') or die;

use RegularLabs\Library\DB as RL_DB;
use RegularLabs\Library\FieldGroup;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

class JFormFieldRL_RedShop extends FieldGroup
{
	public $type = 'RedShop';

	public function getCategories()
	{
		$query = $this->db->getQuery(true)
			->select('COUNT(*)')
			->from('#__redshop_category AS c')
			->where('c.published > -1');
		$this->db->setQuery($query);
		$total = $this->db->loadResult();

		if ($total > $this->max_list_count)
		{
			return -1;
		}

		$this->db->setQuery($this->getCategoriesQuery());
		$items = $this->db->loadObjectList();

		return $this->getOptionsTreeByList($items);
	}

	private function getCategoriesQuery()
	{
		$query = $this->db->getQuery(true)
			->select('c.id, c.parent_id, c.name AS title, c.published')
			->from('#__redshop_category AS c')
			->where('c.published > -1');

		if (RL_DB::tableExists('redshop_category_xref'))
		{
			$query->clear('select')
				->select('c.category_id as id, x.category_parent_id AS parent_id, c.category_name AS title, c.published')
				->join('LEFT', '#__redshop_category_xref AS x ON x.category_child_id = c.category_id')
				->group('c.category_id')
				->order('c.ordering, c.category_name');

			return $query;
		}

		$query
			->group('c.id')
			->order('c.ordering, c.name');

		return $query;
	}

	public function getProducts()
	{
		$query = $this->db->getQuery(true)
			->select('COUNT(*)')
			->from('#__redshop_product AS p')
			->where('p.published > -1');
		$this->db->setQuery($query);
		$total = $this->db->loadResult();

		if ($total > $this->max_list_count)
		{
			return -1;
		}

		$this->db->setQuery($this->getProductsQuery());
		$list = $this->db->loadObjectList();

		return $this->getOptionsByList($list, ['number', 'cat']);
	}

	private function getProductsQuery()
	{
		$query = $this->db->getQuery(true)
			->select('p.product_id as id, p.product_name AS name, p.product_number as number, c.name AS cat, p.published')
			->from('#__redshop_product AS p')
			->where('p.published > -1')
			->join('LEFT', '#__redshop_product_category_xref AS x ON x.product_id = p.product_id')
			->group('p.product_id')
			->order('p.product_name, p.product_number');

		if (RL_DB::tableExists('redshop_category_xref'))
		{
			$query->clear('select')
				->select('p.product_id as id, p.product_name AS name, p.product_number as number, c.category_name AS cat, p.published')
				->join('LEFT', '#__redshop_category AS c ON c.category_id = x.category_id');

			return $query;
		}

		$query
			->join('LEFT', '#__redshop_category AS c ON c.id = x.category_id');

		return $query;
	}

	protected function getInput()
	{
		$error = $this->missingFilesOrTables(['categories' => 'category', 'products' => 'product']);
		if ($error)
		{
			return $error;
		}

		return $this->getSelectList();
	}
}
