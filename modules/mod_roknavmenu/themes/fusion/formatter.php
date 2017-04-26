<?php
/**
 * @version   $Id: formatter.php 4585 2012-10-27 01:44:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 *
 */
class RokNavMenuFusionFormatter extends AbstractJoomlaRokMenuFormatter {
	function format_subnode(&$node) {
	    // Format the current node
		
		if ($node->getType() == 'menuitem' or $node->getType() == 'separator') {
		    if ($node->hasChildren() ) {
    			$node->addLinkClass("daddy");
    		}     		
    		$node->addLinkClass("item");
		}
		if ($node->getLevel() == "0") {
			$node->addListItemClass("root");
		}
	}
}