<?php
/**
 * @version   $Id: BaseRokNavMenuFormatter.php 4585 2012-10-27 01:44:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

//require_once (dirname(__FILE__).'/RokNavMenuTree.php');

/*
 * Created on Jan 16, 2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
class BaseRokNavMenuFormatter {
	function format_tree(&$nav_menu_tree) {
		if ($nav_menu_tree->hasChildren()){
			reset($nav_menu_tree->_children);
			while (list($key, $value) = each($nav_menu_tree->_children)) {
				$child_node =& $nav_menu_tree->_children[$key]; 
				$menu_params =& $nav_menu_tree->_params;
				$this->format_subnodes($child_node, $menu_params);	
			}
		}
	}
	function format(&$node,$menu_params){
		
	}
	function format_subnodes(&$node, &$menu_params) {
		
		$this->default_format($node, $menu_params);
		$this->format($node, $menu_params);
		if ($node->hasChildren()){
			reset($node->_children);
			while (list($key, $value) = each($node->_children)) {
				$child_node =& $node->_children[$key]; 
				$this->format_subnodes($child_node, $menu_params);	
			}
		}
	}
	function default_format(&$node, &$menu_params) {
		// Set up basic nav for target type
		if ($node->nav == 'new') {
			$node->target = "_blank";
		}
		else if ($node->nav == 'newnotool'){
			$attribs = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,'.$node->getParameter('window_open');
			$node->onclick = 'window.open(this.href,\'targetWindow\',\''.$attribs.'\');return false;';
		}
		
		//See if the the roknavmenudisplay plugins want to play
		JPluginHelper::importPlugin('roknavmenu');
		$dispatcher	   =JDispatcher::getInstance();
		$dispatcher->trigger('onRokNavMenuModifyLink', array (&$node, &$menu_params)); 
	}
}