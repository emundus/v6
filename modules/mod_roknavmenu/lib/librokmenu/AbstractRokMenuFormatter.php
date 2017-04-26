<?php
/**
 * @version   $Id: AbstractRokMenuFormatter.php 4585 2012-10-27 01:44:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */


require_once(dirname(__FILE__) . '/RokMenuFormatter.php');


/**
 *
 */
abstract class AbstractRokMenuFormatter implements RokMenuFormatter
{
    protected $active_branch = array();
    protected $args = array();
    protected $current_node = 0;


    /**
     * @param  $args
     * @return void
     */
    public function __construct(&$args)
    {
        $this->args =& $args;
    }

    /**
     * @param  $current_node
     * @return void
     */
    public function setCurrentNodeId($current_node)
    {
        $this->current_node = $current_node;
    }

    /**
     * @param  $active_branch
     * @return void
     */
    public function setActiveBranch(array $active_branch)
    {
        $this->active_branch = $active_branch;
    }

    /**
     * @param  $menu
     * @return void
     */
    public function format_tree(&$menu)
    {
        if (!empty($menu) && $menu !== false)
        {
            $this->_default_format_menu($menu);
            $this->format_menu($menu);

            $nodeIterator = new RecursiveIteratorIterator($menu, RecursiveIteratorIterator::SELF_FIRST);
            foreach ($nodeIterator as $node)
            {
                $this->_format_subnodes($node);
            }
        }
    }


    /**
     * @param  $node
     * @return void
     */
    protected function _format_subnodes(&$node)
    {
        if ($node->getId() == $this->current_node)
        {
            $node->setCssId('current');
        }
        if (array_key_exists($node->getId(), $this->active_branch))
        {
            $node->addListItemClass('active');
        }
        $this->format_subnode($node);
    }

    /**
     * @param  $menu
     * @return void
     */
    protected function _default_format_menu(&$menu)
    {
        // Limit the levels of the tree is called for By limitLevels
        $start = $this->args['startLevel'];
        $end = $this->args['endLevel'];

        if ($this->args['limit_levels'])
        {
            //Limit to the active path if the start is more the level 0
            if ($start > 0)
            {
                $found = false;
                // get active path and find the start level that matches
                if (count($this->active_branch))
                {
                    foreach ($this->active_branch as $active_child)
                    {
                        if ($active_child->getLevel() == $start - 1)
                        {
                            $menu->resetTop($active_child->getId());
                            $found = true;
                            break;
                        }
                    }
                }
                if (!$found)
                {
                    $menu->setChildren(array());
                }
            }
            //remove lower then the defined end level
            $menu->removeLevel($end);
        }

        $always_show_menu = false;
        if (!array_key_exists('showAllChildren', $this->args) && $start==0)
        {
            $always_show_menu = true;
        }
        elseif (array_key_exists('showAllChildren', $this->args))
        {
             $always_show_menu = $this->args['showAllChildren'];
        }

        if (!$always_show_menu)
        {
            if ($menu->hasChildren())
            {
                if (count($this->active_branch) == 0 || empty($this->active_branch))
                {
                    $menu->removeLevel($start);
                }
                else
                {
                    $active = array_keys($this->active_branch);
                    foreach ($menu->getChildren() as $toplevel)
                    {
                        if (array_key_exists($toplevel->getId(), $this->active_branch) !== false)
                        {
                            end($active);
                            $menu->removeIfNotInTree($active, current($active));
                        }
                        else
                        {
                            if (count($this->active_branch) > 0)
                                $menu->removeLevelFromNonActive($this->active_branch, end($this->active_branch)->getLevel());
                        }
                    }
                }
            }
        }
    }

    /**
     * @param  $menu
     * @return void
     */
    public function format_menu(&$menu)
    {

    }
}


