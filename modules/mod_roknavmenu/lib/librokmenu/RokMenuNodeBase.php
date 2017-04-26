<?php
/**
 * @version   $Id: RokMenuNodeBase.php 4585 2012-10-27 01:44:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

require_once(dirname(__FILE__) . '/RokMenuIterator.php');

if (!class_exists('RokMenuNodeBase')) {

    /**
     *
     */
    class RokMenuNodeBase implements IteratorAggregate {

        function getIterator() {
            return new RokMenuIterator($this);
        }

        /**
         * @var int
         */
        protected $id = 0;

        /**
         * @var int
         */
        protected $parent = 0;

        /**
         * @var RokMenuNodeBase
         */
        protected $parentRef = null;

        /**
         * @var int
         */
        protected $level = -1;

        /**
         * @var array
         */
        protected $children = array();


        /**
         * Gets the id
         * @access public
         * @return integer
         */
        public function getId() {
            return $this->id;
        }

        /**
         * Sets the id
         * @access public
         * @param integer $id
         */
        public function setId($id) {
            $this->id = $id;
        }

        /**
         * Gets the level
         * @access public
         * @return integer
         */
        public function getLevel() {
            return $this->level;
        }

        /**
         * Sets the level
         * @access public
         * @param integer $level
         */
        public function setLevel($level) {
            $this->level = $level;
        }


        /**
         * Gets the parent
         * @access public
         * @return integer
         */
        public function getParent() {
            return $this->parent;
        }

        /**
         * Sets the parent
         * @access public
         * @param integer $parent
         */
        public function setParent($parent) {
            $this->parent = $parent;
        }

        /**
         * @return RokMenuNodeBase
         */
        public function getParentRef() {
            return $this->parentRef;
        }

        /**
         * @param RokmenuNodeBase $parentRef
         * @return void
         */
        public function setParentRef(RokmenuNodeBase & $parentRef) {
            $this->parentRef = &$parentRef;
        }

        /**
         * @param  $children
         * @return void
         */
        public function setChildren(array $children) {
            $this->children = $children;
        }

        /**
         * @param RokMenuNodeBase $node
         * @return void
         */
        public function addChild(RokMenuNodeBase &$node) {
            if (null == $this->children) {
                $this->children = array();
            }
            $node->setParentRef($this);
            $node->setLevel($this->getLevel()+1);
            $this->children[$node->getId()] = $node;
        }

        /**
         * @return bool
         */
        public function hasChildren() {
            return !empty($this->children);
        }

        /**
         * @return array
         */
        public function &getChildren() {
            return $this->children;
        }

        /**
         * @param  $node_id
         * @return bool
         */
        public function removeChild($node_id) {
            if (array_key_exists($node_id, $this->children)) {
                unset($this->children[$node_id]);
                return true;
            }
            return false;
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