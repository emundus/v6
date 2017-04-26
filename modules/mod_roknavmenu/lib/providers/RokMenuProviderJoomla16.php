<?php
/**
 * @version   $Id: RokMenuProviderJoomla16.php 9104 2013-04-04 02:26:54Z steph $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
require_once(dirname(__FILE__) . '/JoomlaRokMenuNode.php');

if (!class_exists('RokMenuProviderJoomla16')) {


	class RokMenuProviderJoomla16 extends AbstractRokMenuProvider
	{

		const ROOT_ID = 1;

		protected function getMenuItems()
		{
			//Cache this basd on access level
			$conf = JFactory::getConfig();
			if ($conf->get('caching') && $this->args["cache"]) {
				$user  = JFactory::getUser();
				$cache = JFactory::getCache('mod_roknavmenu');
				$cache->setCaching(true);
				$args      = array($this->args);
				$checksum  = md5(implode(',', $this->args));
				$menuitems = $cache->get(array(
				                              $this, 'getFullMenuItems'
				                         ), $args, 'mod_roknavmenu-' . $user->get('aid', 0) . '-' . $checksum);
			} else {
				$menuitems = $this->getFullMenuItems($this->args);
			}


			/* Set the active to the current run since its not saved with the cache */
			$app    = JFactory::getApplication();
			$jmenu  = $app->getMenu();
			$active = $jmenu->getActive();

			if (is_object($active)) {
				if (array_key_exists($active->id, $menuitems)) {
					$this->current_node = $active->id;
				}
			}

			$this->populateActiveBranch($menuitems);
			return $menuitems;
		}

		public function getFullMenuItems($args)
		{
			$app  = JFactory::getApplication();
			$menu = $app->getMenu();

            $attributes = array('menutype');
            $values = array($args['menutype']);

            //public level menu items
            if (isset($args['check_access_level']) && $args['check_access_level']==0) {
                $attributes[] = 'access';
                $values[] = array(1);
            }

            //registered level menu items
            elseif (isset($args['check_access_level']) && $args['check_access_level']==1) {
                $attributes[] = 'access';
                $values[] = array(1,2);
            }

            //user level menu items
            else {
                $attributes[] = 'access';
                $values[] = JFactory::getUser()->getAuthorisedViewLevels();
            }

			// Get Menu Items
			$rows = $menu->getItems($attributes, $values);

			$outputNodes = array();

			if (is_array($rows) && count($rows) > 0) {
				foreach ($rows as $item) {
					//Create the new Node
					$node = new JoomlaRokMenuNode();

					$node->setId($item->id);
					$node->setParent($item->parent_id);
					$node->setTitle(addslashes(htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8')));
					$node->setParams($item->params);
					$node->setLink($item->link);

					// Menu Link is a special type that is a link to another item
					if ($item->type == 'alias' && $newItem = $menu->getItem($item->params->get('aliasoptions'))) {
						$node->setAlias(true);
						$node->setLink($newItem->link);
					}

					// Get the icon image associated with the item
					$iParams = (is_object($item->params)) ? $item->params : new JRegisry($item->params);
					if ($args['menu_images'] && $iParams->get('menu_image') && $iParams->get('menu_image') != -1) {
						$node->setImage(JURI::base(true) . '/images/stories/' . $iParams->get('menu_image'));
						if ($args['menu_images_link']) {
							$node->setLink(null);
						}
					}

					switch ($item->type) {
						case 'separator':
							$node->setType('separator');
							break;
						case 'url':
							if ((strpos($node->getLink(), 'index.php?') === 0) && (strpos($node->getLink(), 'Itemid=') === false)) {
								$node->setLink($node->getLink() . '&amp;Itemid=' . $node->getId());
							} elseif (!empty($item->link) && ($item->link != null)) {
								$node->setLink($item->link);
							}
							$node->setType('menuitem');
							break;
						default :
							$router = JSite::getRouter();
							if ($node->isAlias() && $newItem) {
								$menu_id = $item->params->get('aliasoptions');
								$node->setMenuId($menu_id);
								//for aliased items formatter.php doesn't cover
								if ($node->getMenuId() == $this->current_node) {
									//taken back out because it caused all the aliased menu items on RT demos to highlight
									//$node->addListItemClass('active');
									//$node->setCssId('current');
								}
							} else {
								$menu_id = $node->getId();
								$node->setMenuId($menu_id);
							}
							$link = ($router->getMode() == JROUTER_MODE_SEF) ? 'index.php?Itemid=' . $menu_id : $node->getLink() . '&Itemid=' . $menu_id;
							$node->setLink($link);
							$node->setType('menuitem');
							break;
					}

					if ($node->getLink() != null) {
						// set the target based on menu item options
						switch ($item->browserNav) {
							case 1:
								$node->setTarget('_blank');
								break;
							case 2:
								//$node->setLink(str_replace('index.php', 'index2.php', $node->getLink()));
								//$node->setTarget('newnotool');
								$value = addslashes(htmlspecialchars("window.open(this.href,'targetWindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes');return false;", ENT_QUOTES, 'UTF-8'));
								$node->addLinkAttrib('onclick', $value);
								break;
							default:
								//$node->setTarget('current');
								break;
						}


						// Get the final URL
						if ($item->home == 1) { // Set Home Links to the Base
							//removed because it breaks SEF extensions
							//$node->setLink(JRoute::_(JURI::base()));
						}

						if ($item->type != 'separator' && $item->type != 'url') {
							$iSecure = $iParams->get('secure', 0);
							if (array_key_exists('url_type', $args) && $args['url_type'] == 'full') {
								$url    = JRoute::_($node->getLink(), true, $iSecure);
								$base   = (!preg_match("/^http/", $node->getLink())) ? rtrim(JURI::base(false) . '/') : '';
								$routed = $base . $url;
								$secure = RokNavMenuTree::_getSecureUrl($routed, $iSecure);
								$node->setLink($secure);
							} else {
								$node->setLink(JRoute::_($node->getLink(), true, $iSecure));
							}
						} else if ($item->type == 'url') {
							$node->setLink(str_replace('&', '&amp;', $node->getLink()));
						}
					}
					$node->addListItemClass("item" . $node->getId());
					$node->setAccess($item->access);
					$node->addSpanClass($node->getType());

                    // Add node to output list
                    $outputNodes[$node->getId()] = $node;
				}
			}
			return $outputNodes;
		}

		/**
		 * @param  $nodeList
		 *
		 * @return void
		 */
		protected function populateActiveBranch($nodeList)
		{

		}


		/**
		 * @return RokMenuNodeTree
		 */
		public function getRealMenuTree()
		{
			$menuitems = $this->getFullMenuItems($this->args);

			$app    = JFactory::getApplication();
			$jmenu  = $app->getMenu();
			$active = $jmenu->getActive();
			if (is_object($active)) {
				if (array_key_exists($active->id, $menuitems)) {
					$this->current_node = $active->id;
				}
			}
			//$this->populateActiveBranch($menuitems);

			$menu = $this->createJoomlaMenuTree($menuitems, $this->args['maxdepth']);


			return $menu;
		}

		/**
		 * Takes the menu item nodes and puts them into a tree structure
		 *
		 * @param  $nodes
		 * @param  $maxdepth
		 *
		 * @return bool|RokMenuNodeTree
		 */
		protected function createJoomlaMenuTree(&$nodes, $maxdepth)
		{
			$menu = new RokMenuNodeTree(self::ROOT_ID);
			// TODO: move maxdepth to higher processing level?
			if (!empty($nodes)) {
				// Build Menu Tree root down (orphan proof - child might have lower id than parent)
				$ids        = array();
				$ids[0]     = true;
				$unresolved = array();

				// pop the first item until the array is empty if there is any item
				if (is_array($nodes)) {
					while (count($nodes) && !is_null($node = array_shift($nodes))) {
						if (!$menu->addNode($node)) {
							if (!array_key_exists($node->getId(), $unresolved) || $unresolved[$node->getId()] < $maxdepth) {
								array_push($nodes, $node);
								if (!isset($unresolved[$node->getId()])) $unresolved[$node->getId()] = 1; else $unresolved[$node->getId()]++;
							}
						}
					}
				}
			}
			return $menu;
		}

		public function getMenuTree()
		{
			if (null == $this->menu) {
				//Cache this basd on access level
				$conf = JFactory::getConfig();
				if ($conf->get('caching',0) && isset($this->args["module_cache"]) && $this->args["module_cache"]) {
					$user  = JFactory::getUser();
					$cache = JFactory::getCache('mod_roknavmenu');
					$cache->setCaching(true);
					$args       = array($this->args);
					$checksum   = md5(implode(',', $this->args));
					$this->menu = $cache->get(array(
					                               $this, 'getRealMenuTree'
					                          ), $args, 'mod_roknavmenu-' . $user->get('aid', 0) . '-' . $checksum);
				} else {
					$this->menu = $this->getRealMenuTree();
				}


				$app    = JFactory::getApplication();
				$jmenu  = $app->getMenu();
				$active = $jmenu->getActive();
				if (is_object($active)) {
					if ($this->menu->findNode($active->id)) {
						$this->current_node = $active->id;
					}
				}
				$this->active_branch = $this->findActiveBranch($this->menu, $this->current_node);
			}
			return $this->menu;
		}

		/**
		 * Gets the current active based on the current_node
		 *
		 * @param RokMenuNodeTree $menu
		 * @param                 $active_id
		 *
		 * @return array
		 */
		protected function findActiveBranch(RokMenuNodeTree $menu, $active_id)
		{
			$active_branch = array();
			/** @var $current JoomlaRokMenuNode */
			$current = $menu->findNode($active_id);
			if ($current) {
				do {
					$active_branch[$current->getId()] = $current;
					if ($current->getParent() == self::ROOT_ID) break;
				} while ($current = $current->getParentRef());
				$active_branch = array_reverse($active_branch, true);
			}
			return $active_branch;
		}
	}

}