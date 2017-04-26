<?php
/**
 * @version   $Id: RokMenuLayout.php 4585 2012-10-27 01:44:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

if (!interface_exists('RokMenuLayout')) {

    /**
     *
     */
    interface RokMenuLayout
    {
        /**
         * @abstract
         * @param  $args
         * @return void
         */
        public function __construct(&$args);


        /**
         * @abstract
         * @param  $menu
         * @return void
         */
        public function renderMenu(&$menu);

        /**
         * @abstract
         * @return void
         */
        public function getScriptFiles();

        /**
         * @abstract
         * @return void
         */
        public function getStyleFiles();

        /**
         * @abstract
         * @return void
         */
        public function getInlineStyle();

        /**
         * @abstract
         * @return void
         */
        public function getInlineScript();

        /**
         * @abstract
         * @return void
         */
        public function doStageHeader();

        /**
         * @abstract
         * @return void
         */
        public function stageHeader();
    }
}