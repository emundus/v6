<?php
/**
* @version		$Id: helper.php 14401 2010-01-26 14:10:00Z louis $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.base.tree');
jimport('joomla.utilities.simplexml');
jimport( 'joomla.html.parameter' );

/**
 * Main Menu Tree Class.
 *
 * @package		Joomla
 * @subpackage	Menus
 * @since		1.5
 */

class JEMenuTree extends JTree
{
	/**
	 * Node/Id Hash for quickly handling node additions to the tree.
	 */
	var $_nodeHash = array();

	/**
	 * Menu parameters
	 */
	var $_params = null;

	/**
	 * Menu parameters
	 */
	var $_buffer = null;

	function __construct(&$params)
	{
		$this->_params		=& $params;
		$this->_root		= new JEMenuNode(0, 'ROOT');
		$this->_nodeHash[0]	=& $this->_root;
		$this->_current		=& $this->_root;
	}

	function addNode(&$params, $item)
	{
		// Get menu item data
		$data = $this->_getItemData($params, $item);

		// Create the node and add it
		$node = new JEMenuNode($item->id, $item->title, $item->access, $data);

		if (isset($item->mid)) {
			$nid = $item->mid;
		} else {
			$nid = $item->id;
		}
		$this->_nodeHash[$nid] =& $node;
		$this->_current =& $this->_nodeHash[$item->parent];

		if ($item->type == 'menulink' && !empty($item->query['Itemid'])) {
			$node->mid = $item->query['Itemid'];
		}

		if ($this->_current) {
			$this->addChild($node, true);
		} else {
			// sanity check
			JError::raiseError( 500, 'Orphan Error. Could not find parent for Item '.$item->id );
		}
	}

	function toXML()
	{
		// Initialize variables
		$this->_current =& $this->_root;

		// Recurse through children if they exist
		while ($this->_current->hasChildren())
		{
			$this->_buffer .= '<ul>';
			foreach ($this->_current->getChildren() as $child)
			{
				$this->_current = & $child;
				$this->_getLevelXML(0);
			}
			$this->_buffer .= '</ul>';
		}
		if($this->_buffer == '') { $this->_buffer = '<ul />'; }
		return $this->_buffer;
	}

	function _getLevelXML($depth)
	{
		$depth++;

		// Start the item
		$rel = (!empty($this->_current->mid)) ? ' rel="'.$this->_current->mid.'"' : '';
		$this->_buffer .= '<li access="'.$this->_current->access.'" level="'.$depth.'" id="'.$this->_current->id.'"'.$rel.'>';

		// Append item data
		$this->_buffer .= $this->_current->link;

		// Recurse through item's children if they exist
		while ($this->_current->hasChildren())
		{
			$this->_buffer .= '<ul>';
			foreach ($this->_current->getChildren() as $child)
			{
				$this->_current = & $child;
				$this->_getLevelXML($depth);
			}
			$this->_buffer .= '</ul>';
		}

		// Finish the item
		$this->_buffer .= '</li>';
	}

	function _getItemData(&$params, $item)
	{
		$data = null;

		// Menu Link is a special type that is a link to another item
		if ($item->type == 'menulink')
		{
			$menu = &JSite::getMenu();
			if ($newItem = $menu->getItem($item->query['Itemid'])) {
    			$tmp = clone($newItem);
				$tmp->title	 = '<span><![CDATA['.$item->title.']]></span>';
				$tmp->mid	 = $item->id;
				$tmp->parent_id = $item->parent_id;
			} else {
				return false;
			}
		} else {
			$tmp = clone($item);
			$tmp->title = '<span><![CDATA['.$item->title.']]></span>';
		}

		$iParams = new JParameter($tmp->params);
		if ($params->get('menu_images') && $iParams->get('menu_image') && $iParams->get('menu_image') != -1) {
			switch ($params->get('menu_images_align', 0)){
				case 0 : 
				$imgalign='align="left"';
				break;
				
				case 1 :
				$imgalign='align="right"';
				break;
				
				default :
				$imgalign='';
				break;
			}
				
			
			$image = '<img src="'.JURI::base(true).'/images/stories/'.$iParams->get('menu_image').'" '.$imgalign.' alt="'.$item->alias.'" />';
			if($tmp->ionly){
				 $tmp->title = null;
			 }
		} else {
			$image = null;
		}
		switch ($tmp->type)
		{
			case 'separator' :
				return '<span class="separator">'.$image.$tmp->title.'</span>';
				break;

			case 'url' :
				if ((strpos($tmp->link, 'index.php?') === 0) && (strpos($tmp->link, 'Itemid=') === false)) {
					$tmp->url = $tmp->link.'&amp;Itemid='.$tmp->id;
				} else {
					$tmp->url = $tmp->link;
				}
				break;

			default :
				$router = JSite::getRouter();
				$tmp->url = $router->getMode() == JROUTER_MODE_SEF ? 'index.php?Itemid='.$tmp->id : $tmp->link.'&Itemid='.$tmp->id;
				break;
		}

		// Print a link if it exists
		if ($tmp->url != null)
		{
			// Handle SSL links
			$iSecure = $iParams->def('secure', 0);
			if ($tmp->home == 1) {
				$tmp->url = JURI::base();
			} elseif (strcasecmp(substr($tmp->url, 0, 4), 'http') && (strpos($tmp->link, 'index.php?') !== false)) {
				$tmp->url = JRoute::_($tmp->url, true, $iSecure);
			} else {
				$tmp->url = str_replace('&', '&amp;', $tmp->url);
			}

			switch ($tmp->browserNav)
			{
				default:
				case 0:
					// _top
					$data = '<a href="'.$tmp->url.'">'.$image.$tmp->title.'</a>';
					break;
				case 1:
					// _blank
					$data = '<a href="'.$tmp->url.'" target="_blank">'.$image.$tmp->title.'</a>';
					break;
				case 2:
					// window.open
					$attribs = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,'.$this->_params->get('window_open');

					// hrm...this is a bit dickey
					$link = str_replace('index.php', 'index2.php', $tmp->url);
					$data = '<a href="'.$link.'" onclick="window.open(this.href,\'targetWindow\',\''.$attribs.'\');return false;">'.$image.$tmp->title.'</a>';
					break;
			}
		} else {
			$data = '<a>'.$image.$tmp->title.'</a>';
		}

		return $data;
	}
}

/**
 * Main Menu Tree Node Class.
 *
 * @package		Joomla
 * @subpackage	Menus
 * @since		1.5
 */
class JEMenuNode extends JNode
{
	/**
	 * Node Title
	 */
	var $title = null;

	/**
	 * Node Link
	 */
	var $link = null;

	/**
	 * CSS Class for node
	 */
	var $class = null;

	function __construct($id, $title, $access = null, $link = null, $class = null)
	{
		$this->id		= $id;
		$this->title	= $title;
		$this->access	= $access;
		$this->link		= $link;
		$this->class	= $class;
	}
}

/**
 * mod_mainmenu Helper class
 *
 * @static
 * @package		Joomla
 * @subpackage	Menus
 * @since		1.5
 */
class modEmundusMenuHelper
{
	function buildXML($params)
	{
		$menu = new JEMenuTree($params);
		$items = &JSite::getMenu();

		// Get Menu Items
		$user =& JFactory::getUser();
		
		// if($user->get('usertype') == "Registered") $emenutype = $user->menutype;
		// else $emenutype = $params->get('menutype');
		// $rows = $items->getItems('menutype', $params->get('menutype'));
		$rows = $items->getItems('menutype', $user->menutype);
		$maxdepth = $params->get('maxdepth',10);

		// Build Menu Tree root down (orphan proof - child might have lower id than parent)
		$ids = array();
		$ids[0] = true;
		$last = null;
		$unresolved = array();
		//die(print_r($rows));
		// pop the first item until the array is empty if there is any item

		if ( is_array($rows)) {
			while (count($rows) && !is_null($row = array_shift($rows)))
			{
				if (array_key_exists($row->parent_id, $ids)) {
					//die();
					$row->ionly = $params->get('menu_images_link');
					$menu->addNode($params, $row);

					// record loaded parents
					$ids[$row->id] = true;
				} else {
					// no parent yet so push item to back of list
					// SAM: But if the key isn't in the list and we dont _add_ this is infinite, so check the unresolved queue
					if(!array_key_exists($row->id, $unresolved) || $unresolved[$row->id] < $maxdepth) {
						array_push($rows, $row);
						// so let us do max $maxdepth passes
						// TODO: Put a time check in this loop in case we get too close to the PHP timeout
						if(!isset($unresolved[$row->id])) $unresolved[$row->id] = 1;
						else $unresolved[$row->id]++;
					}
				}
			}
		}
		return $menu->toXML();
	}

	function &getXML($type, &$params, $decorator)
	{
		static $xmls;

		if (!isset($xmls[$type])) {
			$cache =& JFactory::getCache('mod_emundusmenu');
			$string = $cache->call(array('modEmundusMenuHelper', 'buildXML'), $params);
			$xmls[$type] = $string;
		}

		// Get document
		$xml = JFactory::getXMLParser('Simple');
		$xml->loadString($xmls[$type]);
		$doc = &$xml->document;

		$menu	= &JSite::getMenu();
		$active	= $menu->getActive();
		$start	= $params->get('startLevel');
		$end	= $params->get('endLevel');
		$sChild	= $params->get('showAllChildren');
		$path	= array();

		// Get subtree
		if ($start)
		{
			$found = false;
			$root = true;
			if(!isset($active)){
				$doc = false;
			}
			else{
				$path = $active->tree;
				for ($i=0,$n=count($path);$i<$n;$i++)
				{
					foreach ($doc->children() as $child)
					{
						if ($child->attributes('id') == $path[$i]) {
							$doc = &$child->ul[0];
							$root = false;
							break;
						}
					}

					if ($i == $start-1) {
						$found = true;
						break;
					}
				}
				if ((!is_a($doc, 'JSimpleXMLElement')) || (!$found) || ($root)) {
					$doc = false;
				}
			}
		}

		if ($doc && is_callable($decorator)) {
			$doc->map($decorator, array('end'=>$end, 'children'=>$sChild));
		}
		return $doc;
	}

	function render(&$params, $callback)
	{
		switch ( $params->get( 'menu_style', 'list' ) )
		{
			case 'list_flat' :
				// Include the legacy library file
				require_once(dirname(__FILE__).DS.'legacy.php');
				mosShowHFMenu($params, 1);
				break;

			case 'horiz_flat' :
				// Include the legacy library file
				require_once(dirname(__FILE__).DS.'legacy.php');
				mosShowHFMenu($params, 0);
				break;

			case 'vert_indent' :
				// Include the legacy library file
				require_once(dirname(__FILE__).DS.'legacy.php');
				mosShowVIMenu($params);
				break;

			default :
				// Include the new menu class
							
					// if($user->get('usertype') == "Registered") $emenutype = 'menu_profile'.$user->profile;
					// else $emenutype = $params->get('menutype');
					// $rows = $items->getItems('menutype', $params->get('menutype'));
					// $rows = $items->getItems('menutype', $emenutype);
				/*if(!isset($user->menutype)){
					$password = JRequest::getString('passwd', '', 'post', JREQUEST_ALLOWRAW);
					$db		 =& JFactory::getDBO();
					$db->setQuery('SELECT user.firstname, user.lastname, user.profile, user.university_id, profile.label AS profile_label, profile.menutype
						  FROM #__emundus_users AS user 
						  LEFT JOIN #__emundus_setup_profiles AS profile ON profile.id = user.profile 
						  WHERE user.user_id = '.$user->get('id').'');
					
					$res = $db->loadObject();
					$user->firstname = $res->firstname;
					$user->lastname	 = $res->lastname;
					$user->profile	 = $res->profile;
					$user->profile_label = $res->profile_label;
					$user->menutype	 = $res->menutype;
					$user->university_id	 = $res->university_id;
					$user->passwd	 = $password;
					//$db->setQuery('SELECT filetitle FROM #__emundus_uploads WHERE user_id = '.$user->get('id').' AND attachment_id='.EMUNDUS_PHOTO_AID);
					//$user->avatar = $db->loadResult();
				}*/
				$user =& JFactory::getUser();
				$xml = modEmundusMenuHelper::getXML($user->menutype, $params, $callback);
				if ($xml) {
					$class = $params->get('class_sfx');
					$xml->addAttribute('class', 'menu'.$class);
					if ($tagId = $params->get('tag_id')) {
						$xml->addAttribute('id', $tagId);
					}

					$result = JFilterOutput::ampReplace($xml->toString((bool)$params->get('show_whitespace')));
					$result = str_replace(array('<ul/>', '<ul />'), '', $result);
					echo $result;
				}
				break;
		}
	}
}


