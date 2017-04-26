<?php
/**
 * @version   $Id: RokMenu.php 30073 2016-03-09 08:29:49Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */


require_once(dirname(__FILE__) . '/RokMenuNodeTree.php');
require_once(dirname(__FILE__) . '/RokMenuNode.php');
require_once(dirname(__FILE__) . '/RokMenuNodeBase.php');
require_once(dirname(__FILE__) . '/RokMenuFormatter.php');
require_once(dirname(__FILE__) . '/AbstractRokMenuFormatter.php');
require_once(dirname(__FILE__) . '/RokMenuLayout.php');
require_once(dirname(__FILE__) . '/AbstractRokMenuLayout.php');
require_once(dirname(__FILE__) . '/RokMenuProvider.php');
require_once(dirname(__FILE__) . '/AbstractRokMenuProvider.php');
require_once(dirname(__FILE__) . '/RokMenuTheme.php');
require_once(dirname(__FILE__) . '/AbstractRokMenuTheme.php');

if (!class_exists('RokMenu')) {

    /**
     *
     */
    abstract class RokMenu {
        /**
         * @var array
         */
        protected $args = array();

        /**
         * @var RokMenuProvider
         */
        protected $provider;

        /**
         * @var RokMenuRenderer
         */
        protected $renderer;

        /**
         * @var
         */
        protected static $menu_defaults = array(
            'limit_levels' => 0,
            'startLevel' => 0,
            'endLevel' => 0,
            'showAllChildren' => 1,
            'maxdepth' => 10
        );

        /**
         * @param  $args
         */
        public function __construct($args) {
            $this->args = $args;
            
            $this->renderer = $this->getRenderer();
            // get defaults for theme
            $renderer_defaults = $this->renderer->getDefaults();
            // merge theme defaults with class defaults theme defaults overrding
            $defaults = array_merge(self::$menu_defaults, $renderer_defaults);
            // merge defaults into passed args   passed args overriding
            $this->args = array_merge($defaults, $args);

            $this->renderer->setArgs($this->args);
            
            $this->provider = $this->getProvider();
        }


        /**
         * @static
         * @return array
         */
        public static function getDefaults() {
            return self::$menu_defaults;
        }

        /**
         * @return void
         */
        public function initialize() {
            $this->renderer->initialize($this->provider);
        }

        /**
         * @return string
         */
        public function renderMenu() {
            $output = $this->renderer->renderMenu();
            return $output;
        }

        /**
         * @return string
         */
        public function renderHeader() {
            $output = $this->renderer->renderHeader();
            return $output;            
        }

        /**
         * @return string
         */
        public function renderFooter() {
            $output = $this->renderer->renderFooter();
            return $output;
        }

        /**
         * @abstract
         * @return RokMenuProvider
         */
        protected abstract function getProvider();

        protected abstract function getRenderer();
    }
}

