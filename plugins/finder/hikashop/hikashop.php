<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php


jimport('joomla.application.component.helper');

require_once JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php';

abstract class plgFinderHikashopBridge extends FinderIndexerAdapter
{
	protected $context = 'Product';
	protected $extension = 'com_hikashop';
	protected $layout = 'product';
	protected $type_title = 'Product';
	protected $table = '#__hikashop_product';
	protected $state_field = 'product_published';
	protected $item = null;
	public function __construct(&$subject, $config) {
		if(!isset($this->params)) {
			$plugin = JPluginHelper::getPlugin('finder', 'hikashop');
			$this->params = new JRegistry(@$plugin->params);
		}

		parent::__construct($subject, $config);
	}

	public function onFinderCategoryChangeState($extension, $pks, $value)
	{
		if ($extension == 'com_hikashop')
		{
			$this->categoryStateChange($pks, $value);
		}
	}

	public function onFinderAfterDelete($context, $table)
	{
		if ($context == 'com_hikashop.product' && !empty($table->product_id))
		{
			$id = $table->product_id;
		}
		else if ($context == 'com_finder.index' && !empty($table->link_id))
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

			if(!empty($row->categories)) {
				$query = 'SELECT category_id FROM #__hikashop_category WHERE category_id IN('.implode(',', $row->categories).') AND category_published=1;';
				$db = JFactory::getDBO();
				$db->setQuery($query);
				$res = $db->loadResult();

				if(!$res) {
					return $this->remove($row->product_id);
				}
			}

			$this->reindex($row->product_id);
		}

		return true;
	}

	public function onFinderBeforeSave($context, $row, $isNew)
	{
		return true;
	}

	protected function translateState($item, $category = null)
	{
		if(!empty($this->item->id)) {
			$query = 'SELECT c.category_id FROM #__hikashop_category AS c LEFT JOIN #__hikashop_product_category AS pc ON pc.category_id = c.category_id WHERE c.category_published=1 AND pc.product_id ='.$this->item->id;
			$db = JFactory::getDBO();
			$db->setQuery($query);
			$res = $db->loadResult();
			if($res)
				$category = 1;
			else
				$category = 0;
		}

		return parent::translatestate($item, $category);
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
		$productClass = hikashop_get('class.product');
		$item = $productClass->get($id);
		if($item->product_type == 'variant') {
			$parent = $productClass->get($item->product_parent_id);
			if($parent)
				$item->alias = $parent->alias;
		}
		return 'index.php?option=' . $extension . '&ctrl=' . $view . '&task=show&cid=' . $id ."&name=".$item->alias. $extra;
	}

	protected function getListQuery($query = null)
	{
		$category = (bool)$this->params->get('index_per_category');
		$db = JFactory::getDbo();
		$query = $query instanceof JDatabaseQuery ? $query : $db->getQuery(true)
			->select('a.*')
			->select('a.product_id AS id, a.product_name AS title, a.product_alias AS alias, "" AS link, a.product_description AS summary')
			->select('a.product_keywords AS metakey, a.product_meta_description AS metadesc, "" AS metadata, a.product_access AS access')
			->select('"" AS created_by_alias, a.product_modified AS modified, "" AS modified_by')
			->select('a.product_sale_start AS publish_start_date, a.product_sale_end AS publish_end_date')
			->select('a.product_published AS state, a.product_sale_start AS start_date, 1 AS access')
			->select('brand.category_name AS brand, brand.category_alias as brandalias, brand.category_published AS brand_state, 1 AS brand_access');
		if($category) {
			$query->select('c.category_name AS category, c.category_alias as categoryalias, c.category_published AS cat_state, 1 AS cat_access');
		}

		$case_when_item_alias = ' CASE WHEN a.product_alias != "" THEN a.product_alias ELSE a.product_name END as slug';
		$query->select($case_when_item_alias);
		if($category) {
			$case_when_category_alias = 'c.category_id AS catid, CASE WHEN c.category_alias != "" THEN c.category_alias ELSE c.category_name END as catslug';
			$query->select($case_when_category_alias);
		}

		$query->from('#__hikashop_product AS a')
			->join('LEFT', '#__hikashop_category AS brand ON a.product_manufacturer_id = brand.category_id');

		if($category) {
			$query->join('LEFT', '#__hikashop_product_category AS pc ON a.product_id = pc.product_id')
				->join('LEFT', '#__hikashop_category AS c ON pc.category_id = c.category_id');
		}
		return $query;
	}
	protected function getItem($id)
	{
		$query = $this->getListQuery();
		$query->where('a.product_id = ' . (int) $id);

		$this->db->setQuery($query);
		$row = $this->db->loadAssoc();

		if(empty($row))
			$row = array();

		if(HIKASHOP_J30) {
			$item = Joomla\Utilities\ArrayHelper::toObject($row, 'FinderIndexerResult');
		} else {
			$item = ArrayHelper::toObject((array) $row, 'FinderIndexerResult');
		}

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
			->join('LEFT', '#__hikashop_product_category AS pc ON a.product_id = pc.product_id')
			->join('LEFT', '#__hikashop_category AS c ON pc.category_id = c.category_id');

		return $query;
	}
}
$jversion = preg_replace('#[^0-9\.]#i','',JVERSION);
if(version_compare($jversion,'4.0.0','>=')) {
	include_once(__DIR__.'/hikashop_j4.php');
} else {
	include_once(__DIR__.'/hikashop_j3.php');
}
