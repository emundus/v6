<?php
/**
 * @version   $Id: RokMenuNodeTree.php 4585 2012-10-27 01:44:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */


require_once(dirname(__FILE__) . '/RokMenuNodeBase.php');
require_once(dirname(__FILE__) . '/RokMenuNode.php');

if (!class_exists('RokMenuNodeTree')) {
    /**
     * Rok Nav Menu Tree Class.
     */
    class RokMenuNodeTree extends RokMenuNodeBase
    {


        protected $rootid;

        public function __construct($rootid = 0){
            $this->rootid = $rootid;
        }

        /**
         * @param  int $top_node_id
         * @return bool
         */
        public function resetTop($top_node_id)
        {
            $new_top = $this->findNode($top_node_id);
            if (!$new_top) return false;
            $this->children = $new_top->getChildren();
            return true;
        }

        /**
         * @param RokMenuNodeBase $node
         * @return bool
         */
        public function addNode(RokMenuNodeBase &$node)
        {
            if ($node->getParent() == $this->rootid) {
                $this->addChild($node);
                $node->setLevel(0);
                return true;
            }
            else {
                $iterator = $this->getIterator();
                $childrenIterator = new RecursiveIteratorIterator(new RokMenuIdFilter($iterator, $node->getParent()), RecursiveIteratorIterator::SELF_FIRST);
                foreach ($childrenIterator as $child) {
                    if ($child->getId() == $node->getParent()) {
                        $child->addChild($node);
                        $node->setLevel($childrenIterator->getDepth() + 1);
                        return true;
                    }
                }
            }
            return false;
        }

        /**
         * @param  int $id
         * @return bool
         */
        public function findNode($id)
        {
            $iterator = $this->getIterator();
            $childrenIterator = new RecursiveIteratorIterator(new RokMenuIdFilter($iterator, $id), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($childrenIterator as $child) {
                if ($child->getId() == $id) {
                    $childref = &$child;
                    return $childref;
                }
            }
            return false;
        }

        /**
         * @param  int $nodeId
         * @return bool
         */
        public function removeNode($nodeId)
        {
            $iterator = $this->getIterator();
            $childrenIterator = new RecursiveIteratorIterator(new RokMenuIdFilter($iterator, $nodeId), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($childrenIterator as $child) {
                if ($child->getId() == $nodeId) {
                    $parent = $child->getParentRef();
                    $parent->removeChild($nodeId);
                    return true;
                }
            }
            return false;
        }

        /**
         * @param  int $end
         * @return void
         */
        public function removeLevel($end)
        {
            $toRemove = array();
            $iterator = $this->getIterator();
            $childrenIterator = new RecursiveIteratorIterator(new RokMenuGreaterThenLevelFilter($iterator, $end), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($childrenIterator as $child) {
                if ($child->getLevel() > $end) {
                    $toRemove[] = $child->getId();
                }
            }
            foreach ($toRemove as $remove_id) {
                $this->removeNode($remove_id);
            }
        }

        /**
         * @param  int $end
         * @return void
         */
        public function removeLevelFromNonActive($active_tree, $end)
        {
            $toRemove = array();
            $iterator = $this->getIterator();
            $childrenIterator = new RecursiveIteratorIterator(new RokMenuNotOnActiveTreeFilter($iterator,$active_tree,$end), RecursiveIteratorIterator::SELF_FIRST);

            foreach ($childrenIterator as $child) {
                if ($child->getLevel() > $end+1) {
                    $toRemove[] = $child->getId();
                }
            }
            foreach ($toRemove as $remove_id) {
                    $this->removeNode($remove_id);
            }
        }


        /**
         * @param  array $active_tree the array of active RokNavMenu items
         * @param  int $last_active the id of the last active item in the tree
         * @return void
         */
        public function removeIfNotInTree(&$active_tree, $last_active)
        {
            if (!empty($active_tree)) {
                $toRemove = array();
                $childrenIterator = new RecursiveIteratorIterator($this, RecursiveIteratorIterator::SELF_FIRST);
                foreach ($childrenIterator as $child) {
                    if (!in_array($child->getId(), $active_tree) && $last_active == $child->getId()) {
                        // i am the last node in the active tree dont show my childs but not my grandchildren
                        foreach ($child as $subchild) {
                            $toRemove = array_merge($toRemove, array_keys($subchild->getChildren()));
                        }
                    }
                    else if (!in_array($child->getId(), $active_tree) && $child->getParent() != $last_active) {
                        // I am not in the active tree and not a child of the last node in the active tree so dont show my children
                        $toRemove = array_merge($toRemove, array_keys($child->getChildren()));
                    }
                }
                foreach ($toRemove as $remove_id) {
                    $this->removeNode($remove_id);
                }
            }
        }
    }
}
