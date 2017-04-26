<?php
/**
 * @version   $Id: RokMenuDefaultRenderer.php 4585 2012-10-27 01:44:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

/**
 *
 */
class RokMenuDefaultRenderer implements RokMenuRenderer {
    protected $provider;
    protected $args;
    protected $theme;

    /**
     * @var RokMenuFooter
     */
    protected $formatter;

    /**
     * @var RokMenuLayout
     */
    protected $layout;

    /**
     * @var RokMenuNodeTree
     */
    protected $menu;

    /**
     * @param RokMenuProvider $provider
     * @param array $args
     * @return void
     */
    public function __construct() {

    }

    /**
     * @param  array $args
     * @return void
     */
    public function setArgs(array &$args){
        $this->args =& $args;
    }

    /**
     * @return array
     */
    public function getDefaults() {
        if (!isset($this->theme)) {
            return array();
        }
        return $this->theme->getDefaults();
    }

    /**
     * @return void
     */
    public function initialize(RokMenuProvider $provider) {
        $this->formatter = $this->theme->getFormatter($this->args);
        $this->layout = $this->theme->getLayout($this->args);
        $menu = $provider->getMenuTree();
        $menu = $this->preProcessMenu($menu);
        if (!empty($menu) && $menu !== false) {
            $this->formatter->setActiveBranch($provider->getActiveBranch());
            $this->formatter->setCurrentNodeId($provider->getCurrentNodeId());
            $this->formatter->format_tree($menu);
            $this->menu = &$menu;
        }
    }

    /**
     * This is run once the menu nodes are retrieved but before the formatter is run in order to give extending classes
     * a change to process the nodes in the menu.
     * @param RokMenuNodeTree $menu
     * @return RokMenuNodeTree
     */
    protected function preProcessMenu(RokMenuNodeTree &$menu){
        return $menu;    
    }

    /**
     * @return string
     */
    public function renderHeader() {
        $this->layout->doStageHeader();
        return '';
    }

    /**
     * @return string
     */
    public function renderMenu() {
        return $this->layout->renderMenu($this->menu);
    }

    /**
     * @return string
     */
    public function renderFooter() {
        return '';
    }

    /**
     * @param RokMenuTheme $theme
     * @return void
     */
    public function setTheme(RokMenuTheme $theme) {
        $this->theme = $theme;
    }
}
