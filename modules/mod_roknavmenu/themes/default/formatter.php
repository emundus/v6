<?php
/**
 * @version   $Id: formatter.php 4585 2012-10-27 01:44:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

if (!class_exists('RokNavMenuDefaultFormatter')) {
    class RokNavMenuDefaultFormatter extends AbstractJoomlaRokMenuFormatter {
        function format_subnode(&$node) {
            if ($node->getId() == $this->current_node) {
                $node->setCssId('current');
            }
            if (in_array($node->getId(), array_keys($this->active_branch))){
                $node->addListItemClass('active');
            }
        }
    }
}