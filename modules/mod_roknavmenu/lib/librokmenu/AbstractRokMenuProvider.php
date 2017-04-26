<?php
/**
 * @version   $Id: AbstractRokMenuProvider.php 4585 2012-10-27 01:44:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

require_once(dirname(__FILE__) . '/RokMenuProvider.php');


/**
 * The base class for all data providers for menus
 */
abstract class AbstractRokMenuProvider implements RokMenuProvider
{


    /**
     * @var array
     */
    protected $args = array();

    /**
     * @var array
     */
    protected $active_branch = array();

    /**
     * @var int
     */
    protected $current_node = 0;

    /**
     * @var RokMenuNodeTree
     */
    protected $menu;

    /**
     * @param  $args
     * @return void
     */
    public function __construct(&$args)
    {
        $this->args =& $args;
    }

    /**
     * @return array
     */
    public function getActiveBranch()
    {
        return $this->active_branch;
    }

    /**
     * @return int
     */
    public function getCurrentNodeId()
    {
        return $this->current_node;
    }


    /**
     * @return RokMenuNodeTree
     */
    public function getMenuTree()
    {
        if (null == $this->menu) {
            $this->menu = new RokMenuNodeTree();
            $this->populateMenuTree();
        }
        return $this->menu;
    }

    /**
     * @return void
     */
    protected function populateMenuTree()
    {
        $nodes = $this->getMenuItems();
        $this->createMenuTree($nodes, $this->args['maxdepth']);
    }

    /**
     * Takes the menu item nodes and puts them into a tree structure
     * @param  $nodes
     * @param  $maxdepth
     * @return bool|RokMenuNodeTree
     */
    protected function createMenuTree(&$nodes, $maxdepth)
    {
        // TODO: move maxdepth to higher processing level?
        if (!empty($nodes)) {
            // Build Menu Tree root down (orphan proof - child might have lower id than parent)
            $ids = array();
            $ids[0] = true;
            $unresolved = array();

            // pop the first item until the array is empty if there is any item
            if (is_array($nodes)) {
                while (count($nodes) && !is_null($node = array_shift($nodes)))
                {
                    if (!$this->menu->addNode($node)) {
                        if (!array_key_exists($node->getId(), $unresolved) || $unresolved[$node->getId()] < $maxdepth) {
                            array_push($nodes, $node);
                            if (!isset($unresolved[$node->getId()])) $unresolved[$node->getId()] = 1;
                            else $unresolved[$node->getId()]++;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param  $nodeList
     * @return void
     */
    protected function populateActiveBranch($nodeList)
    {
        // setup children array to find parents and children
        $children = array();
        $list = array();
        foreach ($nodeList as $node) {

            $thisref = &$children[$node->getId()];
            $thisref['parent_id'] = $node->getParent();
            if ($node->getParent() == 0) {
                $list[$node->getId()] = &$thisref;
            } else {
                $children[$node->getParent()]['children'][] = $node->getId();
            }
        }
        // Find active branch
        if ($this->current_node != 0) {
            if (array_key_exists($this->current_node, $nodeList)) {

                $parent_id = $children[$this->current_node]['parent_id'];
                while ($parent_id != 0) {
                    $this->active_branch[$parent_id] = $nodeList[$parent_id];
                    $parent_id = $children[$parent_id]['parent_id'];
                }
                $this->active_branch = array_reverse($this->active_branch, true);
                $this->active_branch[$this->current_node] = $nodeList[$this->current_node];
            }
        }
    }

    /**
     * This platform specific function should be implemented to get the menu nodes and return them in a RokMenuNodeTree.
     * @abstract
     * @return array of RokMenuNode objects
     */
    protected abstract function getMenuItems();
}

