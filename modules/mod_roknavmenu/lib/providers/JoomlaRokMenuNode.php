<?php
/**
 * @version   $Id: JoomlaRokMenuNode.php 4585 2012-10-27 01:44:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
if (!class_exists('JoomlaRokMenuNode')) {
    class JoomlaRokMenuNode extends RokMenuNode {
        protected $image;
        protected $alias = false;
        protected $access = 0;
        protected $params = '';
        protected $type = 'menuitem';
        protected $menuId = null;

        /**
         * Gets the image
         * @access public
         * @return string
         */
        public function getImage() {
            return $this->image;
        }

        /**
         * Sets the image
         * @access public
         * @param string $image
         */
        public function setImage($image) {
            $this->image = $image;
        }

        /**
         * @return bool
         */
        public function hasImage(){
            return isset($this->image);
        }

        /**
         * Gets the alias
         * @access public
         * @return string
         */
        public function isAlias() {
            return $this->alias;
        }

        /**
         * Sets the alias
         * @access public
         * @param boolean $alias
         */
        public function setAlias($alias) {
            $this->alias = $alias;
        }

        /**
         * Gets the access
         * @access public
         * @return string
         */
        public function getAccess() {
            return $this->access;
        }

        /**
         * Sets the access
         * @access public
         * @param string $access
         */
        public function setAccess($access) {
            $this->access = $access;
        }

        /**
         * Gets the params
         * @access public
         * @return string
         */
        public function getParams() {
            return $this->params;
        }

        /**
         * Sets the params
         * @access public
         * @param string $params
         */
        public function setParams($params) {
            $this->params = $params;
        }

        /**
         * Gets the type
         * @access public
         * @return string
         */
        public function getType() {
            return $this->type;
        }

        /**
         * Sets the type
         * @access public
         * @param string $type
         */
        public function setType($type) {
            $this->type = $type;
        }

        /**
         * @param $menuId
         */
        public function setMenuId($menuId){
            $this->menuId = $menuId;
        }

        /**
         * @return null
         */
        public function getMenuId(){
            return $this->menuId;
        }
    }
}