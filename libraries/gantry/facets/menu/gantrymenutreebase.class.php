<?php
/**
 * @version   $Id: gantrymenutreebase.class.php 2325 2012-08-13 17:46:48Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('GANTRY_VERSION') or die('Restricted access');

jimport('joomla.base.tree');

class GantryMenuTreeBase
{


	/**
	 * Base ID for the menu  as ultimate parent
	 */
	var $id = 1;
	var $parent = 0;
	var $_parentRef = null;
	var $level = -1;
	var $access = 2;

	var $_check_access_level = null;

	var $_children = array();

	function __construct($params = null)
	{
		$this->_params = &$params;
	}

	/**
	 * Menu parameters
	 */
	var $_params = null;

	function addChild(&$node)
	{
		if (!$node->isAccessable()) {
			return true;
		}
		if ($this->id == $node->parent) {
			$node->_parentRef           = &$this;
			$this->_children[$node->id] =& $node;
			return true;
		} else if ($this->hasChildren()) {
			reset($this->_children);
			while (list($key, $value) = each($this->_children)) {
				$child =& $this->_children[$key];
				if ($child->addChild($node)) {
					return true;
				}
			}
		}
		return false;
	}

	function hasChildren()
	{
		return count($this->_children);
	}

	function &getChildren()
	{
		return $this->_children;
	}

	function setParameters($params)
	{

		$this->_params = new GantryRegistry($params->toObject());
	}

	function getParameter($param)
	{
		if (null == $param || null == $this->_params) {
			return null;
		}
		return $this->_params->get($param);
	}

	function &findChild($node_id)
	{
		if (array_key_exists($node_id, $this->_children)) {
			return $this->_children[$node_id];
		} else if ($this->hasChildren()) {
			reset($this->_children);
			while (list($key, $value) = each($this->_children)) {
				$child       =& $this->_children[$key];
				$wanted_node = $child->findChild($node_id);
				if ($wanted_node !== false) {
					return $wanted_node;
				}
			}
		}
		$ret = false;
		return $ret;
	}

	function removeChild($node_id)
	{
		if (array_key_exists($node_id, $this->_children)) {
			unset($this->_children[$node_id]);
			return true;
		} else if ($this->hasChildren()) {
			reset($this->_children);
			while (list($key, $value) = each($this->_children)) {
				$child =& $this->_children[$key];
				$ret   = $child->removeChild($node_id);
				if ($ret === true) {
					return $ret;
				}
			}
		}
		return false;
	}

	function removeLevel($end)
	{
		if ($this->level == $end) {
			$this->_children = array();
		} else if ($this->level < $end) {
			if ($this->hasChildren()) {
				reset($this->_children);
				while (list($key, $value) = each($this->_children)) {
					$child =& $this->_children[$key];
					$child->removeLevel($end);
				}
			}
		}
	}

	function removeIfNotInTree(&$active_tree, $last_active)
	{
		if (!empty($active_tree)) {

			if (in_array((int)$this->id, $active_tree) && $last_active == $this->id) {
				// i am the last node in the active tree
				if ($this->hasChildren()) {
					reset($this->_children);
					while (list($key, $value) = each($this->_children)) {
						$child            =& $this->_children[$key];
						$child->_children = array();
					}
				}
			} else if (in_array((int)$this->id, $active_tree)) {
				// i am in the active tree but not the last node
				if ($this->hasChildren()) {
					reset($this->_children);
					while (list($key, $value) = each($this->_children)) {
						$child =& $this->_children[$key];
						$child->removeIfNotInTree($active_tree, $last_active);
					}
				}
			} else {
				// i am not in the active tree
				$this->_children = array();
			}
		}
	}

	function isAccessable()
	{
//		$user =& JFactory::getUser();
//        $groups		= implode(',', $user->getAuthorisedViewLevels());
//
//
//		//$aid  = (int) $user->get('aid', 0);
//		$aid = ($this->_check_access_level != null)? (int)$this->_check_access_level: (int) $user->get('aid', 0);
//		if (null == $this->access ) {
//			return null;
//		}
//		else if ($aid >= $this->access) {
//			return true;
//		}
//		else {
//			return false;
//		}

		return true;
	}


	function getParent()
	{
		return $this->_parentRef;
	}

}