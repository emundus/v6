<?php
/**
 * @version   $Id: gantrymenunode.class.php 2325 2012-08-13 17:46:48Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('GANTRY_VERSION') or die('Restricted access');

gantry_import('facets.menu.gantrymenutreebase');
/**
 * GantryMenuNode
 */
class GantryMenuNode extends GantryMenuTreeBase
{
//	const TYPE_MENU_LINK 				= "menulink";
//	const TYPE_SEPARATOR 				= "separator";
//	const TYPE_MENU_ITEM 				= "menuitem";
//	const TYPE_URL 						= "url";
//
//	const TARGET_CURRENT 				= "current";
//	const TARGET_NEW 					= "new";
//	const TARGET_NEW_NO_TOOLBAR 		= "newnotool";
//
//	const CLASS_PARENT					= "parent";
//	const CLASS_ACTIVE					= "active";
//	const ID_CURRENT					= "current";


	var $title = null;
	var $link = null;

	var $image = null;
	var $alias = null;
	var $type = null;
	var $target = null;
	var $order = null;
	var $nav = null;
	var $displayType = null;
	var $menualias = false;


	var $_link_additions = array();
	var $_link_attribs = array();
	var $_li_classes = array();
	var $_a_classes = array();
	var $_span_classes = array();
	var $css_id = null;


	function hasLink()
	{
		return (isset($this->link));
	}

	function getLink()
	{
		$outlink = $this->link;
		$outlink .= $this->getLinkAdditions(!strpos($this->link, '?'));
		return $outlink;
	}

	function addLinkAddition($name, $value)
	{
		$this->_link_additions[$name] = $value;
	}

	function getLinkAdditions($starting_query = false, $starting_seperator = false)
	{
		$link_additions = " ";
		reset($this->_link_additions);
		$i = 0;
		while (list($key, $value) = each($this->_link_additions)) {
			$link_additions .= (($i == 0) && $starting_query) ? '?' : '';
			$link_additions .= (($i == 0) && !$starting_query) ? '&' : '';
			$link_additions .= ($i > 0) ? '&' : '';
			$link_additions .= $key . '=' . $value;
			$i++;
		}
		return rtrim(ltrim($link_additions));
	}

	function hasLinkAdditions()
	{
		return count($this->_link_additions);
	}

	function addLinkAttrib($name, $value)
	{
		$this->_link_attribs[$name] = $value;
	}

	function getLinkAttribs()
	{
		$link_attribs = " ";
		reset($this->_link_attribs);
		while (list($key, $value) = each($this->_link_attribs)) {
			$link_attribs .= $key . "='" . $value . "' ";
		}
		return rtrim(ltrim($link_attribs));
	}

	function hasLinkAttribs()
	{
		return count($this->_link_attribs);
	}

	function getListItemClasses()
	{
		$html_classes = " ";
		reset($this->_li_classes);
		while (list($key, $value) = each($this->_li_classes)) {
			$class =& $this->_li_classes[$key];
			$html_classes .= $class . " ";
		}
		return rtrim(ltrim($html_classes));
	}

	function addListItemClass($class)
	{
		$this->_li_classes[] = $class;
	}

	function hasListItemClasses()
	{
		return count($this->_li_classes);
	}


	function getLinkClasses()
	{
		$html_classes = " ";
		reset($this->_a_classes);
		while (list($key, $value) = each($this->_a_classes)) {
			$class =& $this->_a_classes[$key];
			$html_classes .= $class . " ";
		}
		return rtrim(ltrim($html_classes));
	}

	function addLinkClass($class)
	{
		$this->_a_classes[] = $class;
	}

	function hasLinkClasses()
	{
		return count($this->_a_classes);
	}

	function getSpanClasses()
	{
		$html_classes = " ";
		reset($this->_span_classes);
		while (list($key, $value) = each($this->_span_classes)) {
			$class =& $this->_span_classes[$key];
			$html_classes .= $class . " ";
		}
		return rtrim(ltrim($html_classes));
	}

	function addSpanClass($class)
	{
		$this->_span_classes[] = $class;
	}

	function hasSpanClasses()
	{
		return count($this->_span_classes);
	}

	function addChild(&$node)
	{
		if ($node->isAccessable()) {

			//$ret = parent::addChild($node);
			$ret = false;

			if (!$node->isAccessable()) {
				$ret = true;
			}
			if ($this->id == $node->parent) {
				$node->_parentRef           = &$this;
				$this->_children[$node->id] =& $node;
				$ret                        = true;
			} else if ($this->hasChildren()) {
				reset($this->_children);
				while (list($key, $value) = each($this->_children)) {
					$child =& $this->_children[$key];
					if ($child->addChild($node)) {
						return true;
					}
				}
			}
			if ($ret === true) {
				if (!array_search('parent', $this->_li_classes)) {
					$this->addListItemClass('parent');
				}
			}
			return $ret;
		}
		return true; // item is not accessable so return true to remove from the stack
	}
}