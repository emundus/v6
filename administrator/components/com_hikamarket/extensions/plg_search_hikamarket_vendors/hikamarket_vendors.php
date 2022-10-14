<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class plgSearchHikamarket_vendors extends JPlugin {

	public function __construct(&$subject, $config) {
		$this->loadLanguage('com_hikamarket');
		$this->loadLanguage('plg_search_hikamarket_vendors');
		$this->loadLanguage('plg_search_hikamarket_vendors_override');
		parent::__construct($subject, $config);
		if(!isset($this->params)) {
			$plugin = JPluginHelper::getPlugin('search', 'hikamarket_vendors');
			$this->params = new JRegistry($plugin->params);
		}
	}

	public function onContentSearchAreas() {
		return $this->onSearchAreas();
	}

	public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null) {
		return $this->onSearch($text, $phrase, $ordering, $areas);
	}

	public function &onSearchAreas() {
		$areas = array(
			'vendors' => JText::_('HIKA_VENDORS')
		);
		return $areas;
	}

	public function onSearch($text, $phrase = '', $ordering = '', $areas = null) {
		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikamarket'.DS.'helpers'.DS.'helper.php'))
			return array();

		$db	= JFactory::getDBO();

		if(is_array($areas)) {
			if(!array_intersect($areas, array_keys( $this->onSearchAreas() )))
				return array();
		}

		$limit = $this->params->def('search_limit', 50);
		$add_image = $this->params->def('add_image', 0);
		$text = trim($text);
		if(empty($text))
			return array();

		switch($ordering) {
			case 'alpha':
				$order = 'vendor.vendor_name ASC';
				break;
			case 'newest':
				$order = 'vendor.vendor_created DESC, vendor.vendor_id DESC';
				break;
			case 'oldest':
				$order = 'vendor.vendor_created ASC, vendor.vendor_id DESC';
				break;
			case 'category':
			case 'popular':
			default:
				$order = 'vendor.vendor_name DESC';
				break;
		}

		$rows = array();

		$filters = array(
			'vendor.vendor_published = 1'
		);

		$filters2 = array();

		$fields = $this->params->get('fields', '');
		if(empty($fields)) {
			$fields = array('vendor_name', 'vendor_description');
		} else {
			$fields = explode(',', $fields);
		}

		switch($phrase){
			case 'exact':
				$text = $db->Quote('%' . hikamarket::getEscaped($text, true) . '%', false);
				foreach($fields as $field) {
					$filters[] = 'vendor.'.$field.' LIKE '.$text;
				}
				break;
			case 'all':
			case 'any':
			default:
				$words = explode( ' ', $text );
				$wordFilters = array();
				$subWordFilters = array();
				foreach($words as $word) {
					$word = $db->Quote('%' . hikamarket::getEscaped($word, true) . '%', false);
					foreach($fields as $i => $field) {
						$subWordFilters[$i][] = 'vendor.' . $field . ' LIKE ' . $word;
					}
				}
				foreach($subWordFilters as $i => $subWordFilter){
					$wordFilters[$i]= '((' .implode( ($phrase == 'all' ? ') AND (' : ') OR ('),$subWordFilter). '))';
				}
				$filters[] = '((' . implode( ') OR (', $wordFilters ) . '))';
				break;
		}

		$new_page = (int)$this->params->get('new_page','1');

		$select = ' vendor.vendor_id AS id, vendor.vendor_name AS title, vendor.vendor_description AS text, vendor_created as created, "'.$new_page.'" AS browsernav';
		$count = 0;

		if($add_image) {
			$select .= ', vendor_image ';
		}

		if($limit){
			$query = 'SELECT DISTINCT ' . $select . ' FROM ' . hikamarket::table('vendor') . ' AS vendor WHERE ' . implode(' AND ', $filters) . ' ORDER BY ' . $order;
			$db->setQuery($query, 0, $limit);
			$mainRows = $db->loadObjectList('id');
			if(!empty($mainRows)) {
				foreach($mainRows as $k => $main) {
					$rows[$k] = $main;
				}
				$count = count($rows);
			}
		}

		if(!$count)
			return $rows;

		$item_id = $this->params->get('item_id', '');
		$menuClass = hikashop_get('class.menus');
		$Itemid = '';
		if(!empty($item_id))
			$Itemid = '&Itemid=' . $item_id;

		$itemids = array();
		$app = JFactory::getApplication();
		$urlSafe = (method_exists($app,'stringURLSafe'));

		if($add_image) {
			$shopConfig = hikamarket::config(false);
			$uploadFolder = ltrim(JPath::clean(html_entity_decode($shopConfig->get('uploadfolder'))),DS);
			$uploadFolder = rtrim($uploadFolder,DS).DS;
			$uploadFolder_url = str_replace(DS,'/',$uploadFolder);
			$app = JFactory::getApplication();
			if(hikamarket::isAdmin()){
				$uploadFolder_url = '../'.$uploadFolder_url;
			}else{
				$uploadFolder_url = rtrim(JURI::base(true),'/').'/'.$uploadFolder_url;
			}
		}

		foreach($rows as $k => $row) {
			if($urlSafe) {
				$alias = $app->stringURLSafe(strip_tags($row->title));
			} else {
				$alias = JFilterOutput::stringURLSafe(strip_tags($row->title));
			}

			if($add_image && !empty($row->vendor_image)) {
				$rows[$k]->text = '<img src="'.$uploadFolder_url.$row->vendor_image.'" alt=""/>'.$rows[$k]->text;
			}

			$rows[$k]->section = JText::_('HIKA_VENDOR');
			$rows[$k]->href = 'index.php?option=com_hikamarket&ctrl=vendor&task=show&name=' . $alias . '&cid=' . $row->id . $Itemid;
		}

		return $rows;
	}
}
