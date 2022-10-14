<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.4.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class plgFinderHikashop extends plgFinderHikashopBridge {
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
}
