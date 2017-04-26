<?php
/**
 * @version   $Id: RokMenuNode.php 4585 2012-10-27 01:44:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

require_once(dirname(__FILE__) . '/RokMenuNodeBase.php');


if (!class_exists('RokMenuNode')) {

    /**
     * RokMenuNode
     */
    class RokMenuNode extends RokMenuNodeBase
    {
        const PARENT_CSS_CLASS = "parent";

        protected $title = null;
        protected $link = null;
        protected $cssId = null;
        protected $target = null;

        protected $attributes = array();

        protected $_link_additions = array();
        protected $_link_attribs = array();

        protected $_li_classes = array();
        protected $_a_classes = array();
        protected $_span_classes = array();

        /**
         * Gets the title
         * @access public
         * @return string
         */
        function getTitle()
        {
            return $this->title;
        }

        /**
         * Sets the title
         * @access public
         * @param string $title
         */
        function setTitle($title)
        {
            $this->title = $title;
        }

        public function setLink($link)
        {
            $this->link = $link;
        }

        public function hasLink()
        {
            return (isset($this->link));
        }

        public function getLink()
        {
            $outlink = $this->link;
            $outlink .= $this->getLinkAdditions(!strpos($this->link, '?'));
            return $outlink;
        }

        /**
         * Gets the css_id
         * @access public
         * @return string
         */
        public function getCssId()
        {
            return $this->cssId;
        }

        /**
         * Sets the css_id
         * @access public
         * @param string $cssId
         */
        public function setCssId($cssId)
        {
            $this->cssId = $cssId;
        }

        /**
         * @return bool
         */
        public function hasCssId(){
            return isset($this->cssId);
        }

        /**
         * Gets the target
         * @access public
         * @return string the target
         */
        public function getTarget()
        {
            return $this->target;
        }

        /**
         * Sets the target
         * @access public
         * @param string the target $target
         */
        public function setTarget($target)
        {
            $this->target = $target;
        }

        /**
         * @return bool
         */
        public function hasTarget()
        {
            return isset($this->target);
        }

        public function addAttribute($key, $value)
        {
            $this->attributes[$key] = $value;
        }

        public function getAttribute($key)
        {
            if (array_key_exists($key, $this->attributes))
                return $this->attributes[$key];
            else
                return false;
        }

        /**
         * @param  $key
         * @return bool
         */
        public function hasAttribute($key){
            return array_key_exists($key, $this->attributes);
        }

        public function getAttributes()
        {
            return $this->attributes;
        }

        public function addLinkAddition($name, $value)
        {
            $this->_link_additions[$name] = $value;
        }

        public function getLinkAdditions($starting_query = false, $starting_seperator = false)
        {
            $link_additions = " ";
            reset($this->_link_additions);
            $i = 0;
            foreach ($this->_link_additions as $key => $value) {
                $link_additions .= (($i == 0) && $starting_query) ? '?' : '';
                $link_additions .= (($i == 0) && !$starting_query) ? '&' : '';
                $link_additions .= ($i > 0) ? '&' : '';
                $link_additions .= $key . '=' . $value;
                $i++;
            }
            return rtrim(ltrim($link_additions));
        }

        public function getLinkAdditionsArray()
        {
            return $this->_link_additions;
        }

        public function hasLinkAdditions()
        {
            return (count($this->_link_additions) > 0) ? true : false;
        }

        public function addLinkAttrib($name, $value)
        {
            $this->_link_attribs[$name] = $value;
        }

        public function getLinkAttribs()
        {
            $link_attribs = " ";
            foreach ($this->_link_attribs as $key => $value) {
                $link_attribs .= $key . "='" . $value . "' ";
            }
            return rtrim(ltrim($link_attribs));
        }

        public function getLinkAttribsArray()
        {
            return $this->_link_attribs;
        }

        public function hasLinkAttribs()
        {
            return (count($this->_link_attribs) > 0) ? true : false;
        }

        public function getListItemClasses()
        {
            return implode(" ", $this->_li_classes);
        }

        public function addListItemClass($class)
        {
            if (!in_array($class, $this->_li_classes))
                $this->_li_classes[] = $class;
        }

        public function hasListItemClasses()
        {
            return (count($this->_li_classes) > 0) ? true : false;
        }

        public function setListItemClasses($classes = array()){
            $this->_li_classes = $classes;
        }

        public function getLinkClasses()
        {
            return implode(" ", $this->_a_classes);
        }

        public function addLinkClass($class)
        {
            if (!in_array($class, $this->_a_classes))
                $this->_a_classes[] = $class;
        }

        public function hasLinkClasses()
        {
            return (count($this->_a_classes) > 0) ? true : false;
        }

        public function setLinkClasses($classes = array()){
            $this->_a_classes = $classes;
        }

        public function getSpanClasses()
        {
            return implode(" ", $this->_span_classes);
        }

        public function addSpanClass($class)
        {
            if (!in_array($class, $this->_span_classes))
                $this->_span_classes[] = $class;
        }

        public function hasSpanClasses()
        {
            return (count($this->_span_classes) > 0) ? true : false;
        }

        public function setSpanClasses($classes = array()){
            $this->_span_classes = $classes;
        }


        public function addChild(RokMenuNodeBase &$node)
        {
            parent::addChild($node);
            $this->addListItemClass(self::PARENT_CSS_CLASS);
        }
    }
}
