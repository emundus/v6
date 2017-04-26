<?php
/**
 * @version   $Id: AbstractJoomlaRokMenuFormatter.php 4585 2012-10-27 01:44:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
abstract class AbstractJoomlaRokMenuFormatter extends AbstractRokMenuFormatter {
    protected function _format_subnodes(&$node) {
        parent::_format_subnodes($node);
		//See if the the roknavmenudisplay plugins want to play
		JPluginHelper::importPlugin('roknavmenu');
		$dispatcher =JDispatcher::getInstance();
		$dispatcher->trigger('onRokNavMenuModifyLink', array (&$node, &$this->args));
    }
}
