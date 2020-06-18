<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php


jimport('joomla.application.component.helper');

require_once JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php';

class plgFinderHikashop extends FinderIndexerAdapter
{
	protected $context = 'Product';
	protected $extension = 'com_hikashop';
	protected $layout = 'product';
	protected $type_title = 'Product';
	protected $table = '#__hikashop_product';
	protected $state_field = 'product_published';

	public function onFinderCategoryChangeState($extension, $pks, $value)
	{
		if ($extension == 'com_hikashop')
		{
			$this->categoryStateChange($pks, $value);
		}
	}

	public function onFinderAfterDelete($context, $table)
	{
		if ($context == 'com_hikashop.product')
		{
			$id = $table->id;
		}
		else if ($context == 'com_finder.index')
		{
			$id = $table->link_id;
		}
		else
		{
			return true;
		}

		return $this->remove($id);
	}

	public function onFinderAfterSave($context, $row, $isNew)
	{
		if ($context == 'com_hikashop.product')
		{

			$this->reindex($row->id);
		}

		return true;
	}

	public function onFinderBeforeSave($context, $row, $isNew)
	{
		return true;
	}

	public function onFinderChangeState($context, $pks, $value)
	{
		if ($context == 'com_hikashop.product')
		{
			$this->itemStateChange($pks, $value);
		}
		if ($context == 'com_plugins.plugin' && $value === 0)
		{
			$this->pluginDisable($pks);
		}
	}
	protected function addAlias(&$element){
		if(empty($element->alias)){
			$element->alias = strip_tags(preg_replace('#<span class="hikashop_product_variant_subname">.*</span>#isU','',$element->title));
		}

		$config = JFactory::getConfig();
		if(!$config->get('unicodeslugs')){
			$lang = JFactory::getLanguage();
			$element->alias = str_replace(array(',', "'", '"'), array('-', '-', '-'), $lang->transliterate($element->alias));
		}
		$app = JFactory::getApplication();
		if(method_exists($app,'stringURLSafe')){
			$element->alias = $app->stringURLSafe($element->alias);
		}elseif(method_exists('JFilterOutput','stringURLUnicodeSlug')){
			$element->alias = JFilterOutput::stringURLUnicodeSlug($element->alias);
		}else{
			$element->alias = JFilterOutput::stringURLSafe($element->alias);
		}
	}

	protected function index(FinderIndexerResult $item, $format = 'html')
	{
		if (JComponentHelper::isEnabled($this->extension) == false)
		{
			return;
		}

		$registry = new JRegistry;
		$registry->loadString($item->params);
		$item->params = JComponentHelper::getParams('com_hikashop', true);
		$item->params->merge($registry);

		$registry = new JRegistry;
		$registry->loadString($item->metadata);
		$item->metadata = $registry;

		$item->summary = FinderIndexerHelper::prepareContent($item->summary, $item->params);
		$item->body    = FinderIndexerHelper::prepareContent($item->body, $item->params);

		$menusClass = hikashop_get('class.menus');
		$itemid = $menusClass->getPublicMenuItemId();
		$this->addAlias($item);
		$extra = '';
		if(!empty($itemid))
			$extra = '&Itemid='.$itemid;

		$item->url   = "index.php?option=com_hikashop&ctrl=product&task=show&cid=" . $item->id."&name=".$item->alias."&category_pathway=" . $item->catid.$extra;
		$item->route = "index.php?option=com_hikashop&ctrl=product&task=show&cid=" . $item->id."&name=".$item->alias."&category_pathway=" . $item->catid.$extra;
		$item->path  = FinderIndexerHelper::getContentPath($item->route);

		$title = $this->getItemMenuTitle($item->url);

		if (!empty($title) && $this->params->get('use_menu_title', true))
		{
			$item->title = $title;
		}

		$item->metaauthor = $item->metadata->get('author');

		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metakey');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metadesc');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metaauthor');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'author');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'created_by_alias');

		$item->state = $this->translateState($item->state, $item->cat_state);

		$item->addTaxonomy('Type', 'Product');

		$item->addTaxonomy('Category', 		$item->category, $item->cat_state, $item->cat_access);

		$item->addTaxonomy('Brand', 	$item->brand, 	$item->brand_state, $item->brand_access);

		$item->addTaxonomy('Language', 		$item->language);

		FinderIndexerHelper::getContentExtras($item);

		$this->indexer->index($item);
	}

	protected function setup()
	{
		$this->_setup();
		return true;
	}

	protected function _setup() {

		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php');
	}

	protected function getUrl($id, $extension, $view)
	{
		static $extra = null;
		if(is_null($extra)) {
			$this->_setup();
			$menusClass = hikashop_get('class.menus');
			$itemid = $menusClass->getPublicMenuItemId();
			if($itemid)
				$extra = '&Itemid='.$itemid;
			else
				$extra = '';
		}
		return 'index.php?option=' . $extension . 'ctrl=' . $view . '&task=show&product_id=' . $id . $extra;
	}

	protected function getListQuery($query = null)
	{
		$db = JFactory::getDbo();
		$query = $query instanceof JDatabaseQuery ? $query : $db->getQuery(true)
			->select('a.product_id AS id, c.category_id AS catid, a.product_name AS title, a.product_alias AS alias, "" AS link, a.product_description AS summary')
			->select('a.product_keywords AS metakey, a.product_meta_description AS metadesc, "" AS metadata, a.product_access AS access')
			->select('"" AS created_by_alias, a.product_modified AS modified, "" AS modified_by')
			->select('a.product_sale_start AS publish_start_date, a.product_sale_end AS publish_end_date')
			->select('a.product_published AS state, a.product_sale_start AS start_date, 1 AS access')
			->select('c.category_name AS category, c.category_alias as categoryalias, c.category_published AS cat_state, 1 AS cat_access')
			->select('brand.category_name AS brand, brand.category_alias as brandalias, brand.category_published AS brand_state, 1 AS brand_access');

		$case_when_item_alias = ' CASE WHEN a.product_alias != "" THEN a.product_alias ELSE a.product_name END as slug';
		$query->select($case_when_item_alias);

		$case_when_category_alias = ' CASE WHEN c.category_alias != "" THEN c.category_alias ELSE c.category_name END as catslug';
		$query->select($case_when_category_alias)

			->from('#__hikashop_product AS a')
			->join('LEFT', '#__hikashop_product_category AS pc ON a.product_id = pc.product_id')
			->join('LEFT', '#__hikashop_category AS c ON pc.category_id = c.category_id')
			->join('LEFT', '#__hikashop_category AS brand ON a.product_manufacturer_id = brand.category_id')
			->where( $db->quoteName('a.product_published') . ' = 1' );
		return $query;
	}
	protected function getItem($id)
	{
		$query = $this->getListQuery();
		$query->where('a.product_id = ' . (int) $id);

		$this->db->setQuery($query);
		$row = $this->db->loadAssoc();

		$item = ArrayHelper::toObject((array) $row, 'FinderIndexerResult');

		$item->type_id = $this->type_id;

		$item->layout = $this->layout;

		return $item;
	}

	protected function categoryStateChange($pks, $value)
	{
		foreach ($pks as $pk)
		{
			$query = clone $this->getStateQuery();
			$query->where('c.category_id = ' . (int) $pk);

			$this->db->setQuery($query);
			$items = $this->db->loadObjectList();

			foreach ($items as $item)
			{
				$temp = $this->translateState($item->state, $value);

				$this->change($item->id, 'state', $temp);

				$this->reindex($item->id);
			}
		}
	}

	protected function checkItemAccess($row)
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('access'))
			->from($this->db->quoteName($this->table))
			->where($this->db->quoteName('product_id') . ' = ' . (int) $row->id);
		$this->db->setQuery($query);

		$this->old_access = $this->db->loadResult();
	}
	protected function itemStateChange($pks, $value)
	{
		foreach ($pks as $pk)
		{
			$query = clone $this->getStateQuery();
			$query->where('a.product_id = ' . (int) $pk);

			$this->db->setQuery($query);
			$item = $this->db->loadObject();

			$temp = $this->translateState($value, $item->cat_state);

			$this->change($pk, 'state', $temp);

			$this->reindex($pk);
		}
	}

	protected function getUpdateQueryByTime($time)
	{
		$query = $this->db->getQuery(true)
			->where('a.product_modified >= ' . $this->db->quote($time));

		return $query;
	}

	protected function getUpdateQueryByIds($ids)
	{
		$query = $this->db->getQuery(true)
			->where('a.product_id IN(' . implode(',', $ids) . ')');

		return $query;
	}

	protected function getStateQuery()
	{
		$query = $this->db->getQuery(true);

		$query->select('a.product_id AS id, c.category_id AS catid');

		$query->select('a.product_published AS state, c.category_published AS cat_state');
		$query->select('1 AS access,  1 AS cat_access')
			->from($this->table . ' AS a')
			->join('LEFT', '#__hikashop_product_category AS pcc ON a.product_id = pc.product_id')
			->join('LEFT', '#__hikashop_category AS c ON pc.category_id = c.category_id');

		return $query;
	}
}
