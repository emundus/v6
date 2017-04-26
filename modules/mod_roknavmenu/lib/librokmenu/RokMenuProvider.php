<?php
/**
 * @version   $Id: RokMenuProvider.php 4585 2012-10-27 01:44:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
if (!interface_exists('RokMenuProvider')) {

    /**
     * The base class for all data providers for menus
     */
    interface RokMenuProvider {
        /**
         * Gets an array of RokMenuNodes for that represent the menu items.  This should be a non hierarchical array.
         * @abstract
         * @return array of RokMenuNode objects
         */
        function getActiveBranch();

        /**
         * @abstract
         * @return int
         */
        function getCurrentNodeId();

        /**
         * @abstract
         * @return RokMenuNodeTree
         */
        function getMenuTree();

        /**
         * @abstract
         * @param  $args
         * @return void
         */
        function __construct(&$args);
    }
}