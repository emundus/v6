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
class hikamarketCategoryClass extends hikamarketClass {

	protected $tables = array('shop.category');
	protected $pkeys = array('category_id');
	protected $toggle = array('category_published' => 'category_id');
	protected $toggleAcl = array('category_published' => 'category/edit/published');

	public function getRaw($element, $withimage = false) {
		static $multiTranslation = null;
		if(empty($element))
			return null;

		if(in_array($element,array('product','status','tax','manufacturer'))) {
			$shopCategoryClass = hikamarket::get('shop.class.category');
			return $shopCategoryClass->getMainElement($element);
		}

		$pkey = end($this->pkeys);
		$namekey = end($this->namekeys);
		$table = hikamarket::table(end($this->tables));
		if(!is_numeric($element) && !empty($namekey)) {
			$pkey = $namekey;
		}

		if($multiTranslation === null) {
			$translationHelper = hikamarket::get('shop.helper.translation');
			$multiTranslation = $translationHelper->isMulti(true);
		}

		if($withimage)
			$query = 'SELECT cat.*, file.* FROM '.$table.' AS cat LEFT JOIN ' . hikamarket::table('shop.file') . ' AS file ON cat.category_id = file.file_ref_id AND file.file_type=\'category\' WHERE cat.category_id = '.$this->db->Quote($element);
		else
			$query = 'SELECT * FROM '.$table.' WHERE '.$pkey.' = '.$this->db->Quote($element);
		$this->db->setQuery($query, 0, 1);

		$app = JFactory::getApplication();
		if(!hikamarket::isAdmin() && $multiTranslation && class_exists('JFalangDatabase')) {
			$ret = $this->db->loadObject('stdClass', false);
		} elseif(!hikamarket::isAdmin() && $multiTranslation && (class_exists('JFDatabase') || class_exists('JDatabaseMySQLx'))) {
			$ret = $this->db->loadObject(false);
		} else {
			$ret = $this->db->loadObject();
		}
		return $ret;
	}

	public function frontSaveForm() {

		$app = JFactory::getApplication();
		$category_id = hikamarket::getCID('category_id');
		$categoryClass = hikamarket::get('shop.class.category');
		$fieldsClass = hikamarket::get('shop.class.field');
		$vendorClass = hikamarket::get('class.vendor');
		$vendor_id = hikamarket::loadVendor(false, false);

		$formData = hikaInput::get()->get('data', array(), 'array');
		$formCategory = array();
		if(!empty($formData['category']))
			$formCategory = $formData['category'];

		$new = false;
		$oldCategory = null;
		if(empty($category_id))
			$new = true;
		if(!$new) {
			$oldCategory = $categoryClass->get($category_id);
			if(empty($oldCategory))
				return false;

			if(!in_array($oldCategory->category_type, array('product', 'vendor')))
				return false;

			if($vendor_id > 1 && $oldCategory->category_type == 'vendor' && $oldCategory->category_namekey != 'vendor_' . $vendor_id)
				return false;
		}
		$category = $fieldsClass->getInput('category', $oldCategory, true, 'data', false, 'display:vendor_category_edit');
		if(empty($category)) {
			$category = @$_SESSION['hikashop_category_data'];
			hikaInput::get()->set('fail', $category);
			return false;
		}

		$category->category_id = (int)$category_id;
		$category->category_type = 'product';
		if(!empty($oldCategory))
			$category->category_type = $oldCategory->category_type;

		if(!hikamarket::acl('category/edit/name')) { unset($category->category_name); }
		if(!hikamarket::acl('category/edit/published')) { unset($category->category_published); }
		if(!hikamarket::acl('category/edit/pagetitle')) { unset($category->category_page_title); }
		if(!hikamarket::acl('category/edit/metadescription')) { unset($category->category_meta_description); }
		if(!hikamarket::acl('category/edit/keywords')) { unset($category->category_keywords); }
		if(!hikamarket::acl('category/edit/alias')) { unset($category->category_alias); }
		if(!hikamarket::acl('category/edit/acl')) { unset($category->category_access); }

		if(hikamarket::acl('category/edit/description')) {
			$category->category_description = hikaInput::get()->getRaw('category_description', '');

			$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);
			$category->category_description = $safeHtmlFilter->clean($category->category_description, 'string');
		}

		if(!hikamarket::acl('category/edit/parent') || empty($category->category_parent_id) || (!hikamarket::isVendorCategory($category->category_parent_id))) {
			unset($category->category_parent_id);
		}

		if(!isset($category->category_parent_id) && $new) {
			$rootCategory = $vendorClass->getRootCategory($vendor_id);
			if($rootCategory == 0) {
				$rootCategory = 1;
			}
			$category->category_parent_id = $rootCategory;
		}

		if($vendor_id > 1 && !empty($oldCategory) && $oldCategory->category_type == 'vendor') {
			unset($category->category_published);
			unset($category->category_parent_id);
		}

		$category_image_id = @$category->category_image;
		unset($category->category_image);


		$category->hikamarket = new stdClass();
		$category->hikamarket->force_type = $category->category_type;

		$status = $this->save($category);
		if($status) {

			if(hikamarket::acl('category/edit/images')) {

				if($category_id > 0) {
					$query = 'DELETE FROM ' . hikamarket::table('shop.file') . ' WHERE file_type=\'category\' AND file_ref_id = ' . (int)$category_id . ' ';
					if($category_image_id != 0)
						$query .= ' AND file_id != ' . $category_image_id;
					$this->db->setQuery($query);
					$this->db->execute();
				}

				if($category_image_id > 0 && $category_id == 0) {
					$query = 'UPDATE ' . hikamarket::table('shop.file') . ' SET file_ref_id = '.(int)$status.' WHERE file_type=\'category\' AND file_ref_id = 0 AND file_id = '.$category_image_id;
					$this->db->setQuery($query);
					$this->db->execute();
				}
			}
			$app->enqueueMessage(JText::_('HIKASHOP_SUCC_SAVED'), 'success');

		} else {
			hikaInput::get()->set('fail', $category);
		}
		return $status;
	}

	public function save(&$element, $ordering = true) {
		$categoryClass = hikamarket::get('shop.class.category');
		return $categoryClass->save($element, $ordering);
	}

	public function toggleId($task, $value = null) {
		if($value !== null) {
			$app = JFactory::getApplication();
			if(!hikamarket::isAdmin() && ((int)$value == 0 || !hikamarket::isVendorCategory((int)$value) || empty($this->toggle[$task]) || ( empty($this->toggleAcl[$task]) && !hikamarket::acl('category/edit/'.$task) ) || ( !empty($this->toggleAcl[$task]) && !hikamarket::acl($this->toggleAcl[$task]) ) ))
				return false;
		}
		if(!empty($this->toggle[$task]))
			return $this->toggle[$task];
		return false;
	}

	public function toggleDelete($value1 = '', $value2 = '') {
		$app = JFactory::getApplication();
		if(!hikamarket::isAdmin() && ((int)$value1 == 0 || !hikamarket::isVendorCategory((int)$value1) || !hikamarket::acl('category/delete')))
			return false;
		if(!empty($this->deleteToggle))
			return $this->deleteToggle;
		return false;
	}

	public function &getNameboxData($typeConfig, &$fullLoad, $mode, $value, $search, $options) {
		$ret = array(
			0 => array(),
			1 => array()
		);

		$app = JFactory::getApplication();
		static $multiTranslation = null;
		if($multiTranslation === null) {
			$translationHelper = hikamarket::get('shop.helper.translation');
			$multiTranslation = $translationHelper->isMulti(true);
		}

		$category_type = array('product','root','vendor');
		if(!empty($typeConfig['params']['category_type']))
			$category_type = $typeConfig['params']['category_type'];
		if(is_string($category_type))
			$category_type = explode(',', $category_type);

		$key_value = @$options['key'];
		if(empty($key_value))
			$key_value = @$typeConfig['params']['key'];
		if(empty($key_value))
			$key_value = 'category_name';

		$fullLoad = false;
		$displayFormat = !empty($options['displayFormat']) ? $options['displayFormat'] : @$typeConfig['displayFormat'];

		$depth = (int)@$options['depth'];
		$start = (int)@$options['start'];
		$limit = (int)@$options['limit'];
		$page = (int)@$options['page'];
		$cats = array();
		$is_root = false;

		if(empty($start) && !empty($options['root'])) {
			if(!is_array($options['root'])) {
				$start = (int)$options['root'];
			} else {
				$start = $options['root'];
				hikamarket::toInteger($start);
			}
			$is_root = true;
		}

		if($depth <= 0)
			$depth = 1;
		if($limit <= 0)
			$limit = ($typeConfig['mode'] == 'list') ? 10 : 200;

		$category_types = array();
		foreach($category_type as $t) {
			$category_types[] = $this->db->Quote($t);
		}

		$select = array('c.*');
		$table = array(hikamarket::table('shop.category').' AS c');
		$where = array('c.category_type IN ('.implode(',', $category_types).')');

		if($typeConfig['mode'] == 'list')
			$where[] = 'c.category_namekey NOT IN ('.implode(',', $category_types).')';

		if(in_array('manufacturer', $category_type))
			$where[] = 'c.category_published = 1';

		if(in_array('product', $category_type) && empty($search)) {
			if(empty($start))
				$where[] = 'c.category_depth >= 0 AND c.category_depth <= ' . $depth;
			else
				$where[] = 'c.category_depth >= cp.category_depth AND c.category_depth <= (cp.category_depth + ' . $depth . ')';
		}
		if(!empty($search)) {
			$searchStr = "'%" . ((HIKASHOP_J30) ? $this->db->escape($search, true) : $this->db->getEscaped($search, true) ) . "%'";
			if(!is_array($start))
				$where[] = '(c.category_name LIKE ' . $searchStr . ' OR c.category_id = '.$start.')';
			else
				$where[] = '(c.category_name LIKE ' . $searchStr . ' OR c.category_id IN ('.implode(',', $start).'))';
		}
		if(!empty($start) && ((is_int($start) && $start > 0) || (is_array($start) && count($start) > 0))) {
			if(!is_array($start))
				$table[] = 'INNER JOIN '.hikamarket::table('shop.category').' AS cp ON cp.category_id = ' . $start;
			else
				$table[] = 'INNER JOIN '.hikamarket::table('shop.category').' AS cp ON (cp.category_id IN (' . implode(',', $start) . '))';
			$where[] = '(c.category_left >= cp.category_left AND c.category_right <= cp.category_right)';
		}

		if(!empty($typeConfig['params']['filters'])) {
			$extra_filters = $typeConfig['params']['filters'];
			if(!is_array($extra_filters))
				$extra_filters = array($extra_filters);
			$where = array_merge($where, $extra_filters);
		}

		if($typeConfig['mode'] == 'list')
			$order = ' ORDER BY c.category_name ASC';
		else
			$order = ' ORDER BY c.category_parent_id ASC, c.category_ordering';

		$query = 'SELECT '.implode(', ', $select) . ' FROM ' . implode(' ', $table) . ' WHERE ' . implode(' AND ', $where) . $order;
		$this->db->setQuery($query, $page, $limit);

		if(!hikamarket::isAdmin() && $multiTranslation && class_exists('JFalangDatabase')) {
			$categories = $this->db->loadObjectList('category_id', 'stdClass', false);
		} elseif(!hikamarket::isAdmin() && $multiTranslation && (class_exists('JFDatabase') || class_exists('JDatabaseMySQLx'))) {
			$categories = $this->db->loadObjectList('category_id', false);
		} else {
			$categories = $this->db->loadObjectList('category_id');
		}

		if($typeConfig['mode'] == 'list') {
			if(count($categories) < $limit)
				$fullLoad = true;

			if(!empty($typeConfig['params']['category_type']) && $typeConfig['params']['category_type'] == 'status') {
				foreach($categories as $category) {
					if(!empty($category->translation))
						$ret[0][$category->category_name] = hikamarket::orderStatus($category->translation);
					else
						$ret[0][$category->category_name] = hikamarket::orderStatus($category->category_name);
				}
			} elseif(!empty($typeConfig['params']['category_type']) && $typeConfig['params']['category_type'] == 'manufacturer') {
				foreach($categories as $category) {
					$ret[0][$category->category_id] = (!empty($category->translation)) ? $category->translation : hikashop_translate($category->category_name);
				}
			} else {
				foreach($categories as $category) {
					$ret[0][$category->$key_value] = (!empty($category->translation)) ? $category->translation : $category->category_name;
				}
			}
		} else {
			$tmp = array();

			if(!empty($search)) {
				$base = '';
				if(is_int($start) && $start > 0) {
					$base = '(c.category_left <= ' . (int)$categories[$start]->category_left . ' AND c.category_right >= ' . (int)$categories[$start]->category_right . ') AND ';
				} else if(is_array($start) && count($start) > 0) {
					$b = array();
					foreach($start as $c) {
						$b[] = 'c.category_left <= ' . (int)$categories[$c]->category_left . ' AND c.category_right >= ' . (int)$categories[$c]->category_right;
					}
					$base = '((' . implode(') OR (', $b) . ')) AND ';
				}

				$lookup_categories = array();
				foreach($categories as $c) {
					if(empty($lookup_categories[ (int)$c->category_id ]))
						$lookup_categories[ (int)$c->category_id ] = (int)$c->category_left . ' AND c.category_right > ' . (int)$c->category_right;
				}

				$query = 'SELECT c.* ' .
					' FROM ' . hikamarket::table('shop.category') . ' AS c ' .
					' WHERE ' . $base . '((c.category_left < '.implode(') OR (c.category_left < ', $lookup_categories) . '))' . $order;
				$this->db->setQuery($query);

				if(!hikamarket::isAdmin() && $multiTranslation && class_exists('JFalangDatabase')) {
					$category_tree = $this->db->loadObjectList('category_id', 'stdClass', false);
				} elseif(!hikamarket::isAdmin() && $multiTranslation && (class_exists('JFDatabase') || class_exists('JDatabaseMySQLx'))) {
					$category_tree = $this->db->loadObjectList('category_id', false);
				} else {
					$category_tree = $this->db->loadObjectList('category_id');
				}

				if(is_array($start) && $is_root) {
					$r = (int)reset($start);
					if(isset($category_tree[$r])) {
						$o = new stdClass();
						$o->status = 5;
						$o->name = (!empty($category_tree[$r]->translation)) ? $category_tree[$r]->translation :  JText::_($category_tree[$r]->category_name); // JText::_($category_tree[$r]->category_name);
						$o->value = $r;
						$o->data = array();
						$o->icon = 'world';
						$ret[0][] =& $o;
						$tmp[$k] =& $o;
					}
				}

				foreach($category_tree as $k => $v) {
					if(!$is_root && ((is_int($start) && $k == $start) || (is_array($start) && in_array($k, $start))))
						continue;
					if(is_array($start) && isset($tmp[$k]))
						continue;

					$o = new stdClass();
					$o->status = 2;
					$o->name = JText::_($v->category_name);
					$o->value = $k;
					$o->data = array();

					if(empty($v->category_parent_id) || ((is_int($start) && $k == $start) || (is_array($start) && in_array($k, $start)))) {
						$o->status = 5;
						$o->icon = 'world';
						$ret[0][] =& $o;
					} else if((int)$v->category_parent_id == 1 || !isset($tmp[(int)$v->category_parent_id])) {
						$ret[0][] =& $o;
					} else {
						$tmp[(int)$v->category_parent_id]->data[] =& $o;
					}
					$tmp[$k] =& $o;
					unset($o);
				}
			}

			if(is_array($start) && $is_root) {
				$r = (int)reset($start);
				if(isset($categories[$r])) {
					$o = new stdClass();
					$o->status = 5;
					$o->name = (!empty($categories[$r]->translation)) ? $categories[$r]->translation :  JText::_($categories[$r]->category_name);
					$o->value = $r;
					$o->data = array();
					$o->icon = 'world';
					$ret[0][] =& $o;
					$tmp[$r] =& $o;
					unset($o);
				}
			}

			foreach($categories as $k => $v) {
				if(!$is_root && ((is_int($start) && $k == $start) || (is_array($start) && in_array($k, $start))))
					continue;
				if(is_array($start) && in_array($k, $start) && isset($tmp[$k]))
					continue;

				$o = new stdClass();
				$o->status = 3;
				$o->name = (!empty($v->translation)) ? $v->translation :  JText::_($v->category_name);
				$o->value = $k;
				$o->data = array();

				if($v->category_left + 1 == $v->category_right)
					$o->status = 4;

				if(empty($v->category_parent_id) || ((is_int($start) && $k == $start) || (is_array($start) && in_array($k, $start)))) {
					$o->status = 5;
					$o->icon = 'world';
					$ret[0][] =& $o;
				} else if((int)$v->category_parent_id == 1 || !isset($tmp[(int)$v->category_parent_id])) {
					$ret[0][] =& $o;
				} else {
					$tmp[(int)$v->category_parent_id]->status = 2;
					$tmp[(int)$v->category_parent_id]->data[] =& $o;
				}
				$tmp[$k] =& $o;
				unset($o);
			}
		}

		if(!empty($value) && @$typeConfig['params']['category_type'] == 'status') {
			if($mode == hikamarketNameboxType::NAMEBOX_SINGLE) {
				if(isset($ret[0][$value])) {
					$ret[1][$value] = $ret[0][$value];
					return $ret;
				}
			} else if($mode == hikamarketNameboxType::NAMEBOX_MULTIPLE && is_array($value)) {
				foreach($value as $v) {
					if(isset($ret[0][$v])) {
						$ret[1][$v] = $ret[0][$v];
					}
				}
				return $ret;
			}
		}

		if(empty($value) && is_array($value) && count($value) == 1 && $value[0] == '')
			$value = array();

		if(!empty($value)) {
			if(!is_array($value))
				$value = array($value);

			$search = array();
			$f = reset($value);
			if(is_int($f) || (int)$f > 0) {
				foreach($value as $v) {
					$search[] = (int)$v;
				}
				$query = 'SELECT c.* '.
						' FROM ' . hikamarket::table('shop.category') . ' AS c '.
						' WHERE c.category_id IN ('.implode(',', $search).')';
			} else {
				foreach($value as $v) {
					$search[] = $this->db->Quote($v);
				}
				$query = 'SELECT c.* '.
						' FROM ' . hikamarket::table('shop.category') . ' AS c '.
						' WHERE c.category_name IN ('.implode(',', $search).')';
			}
			$this->db->setQuery($query);

			if(!hikamarket::isAdmin() && $multiTranslation && class_exists('JFalangDatabase')) {
				$categories = $this->db->loadObjectList('category_id', 'stdClass', false);
			} elseif(!hikamarket::isAdmin() && $multiTranslation && (class_exists('JFDatabase') || class_exists('JDatabaseMySQLx'))) {
				$categories = $this->db->loadObjectList('category_id', false);
			} else {
				$categories = $this->db->loadObjectList('category_id');
			}

			if(!empty($categories)) {
				foreach($categories as $category) {
					$category->name = (!empty($category->translation)) ? $category->translation :  JText::_($category->category_name);
					$ret[1][$category->category_id] = $category;
				}
			}
			unset($categories);

			if($mode == hikamarketNameboxType::NAMEBOX_SINGLE)
				$ret[1] = reset($ret[1]);
		}

		return $ret;
	}

	public function &getList($type = 'product', $root = 0, $getRoot = true) {
		$app = JFactory::getApplication();

		$select = 'SELECT a.*';
		$table = ' FROM '.hikamarket::table('shop.category').' AS a ';
		$where = array();
		if(!empty($type)) {
			if(is_array($type)) {
				if($getRoot && !in_array('root', $type))
					$type[] = 'root';
				$types = array();
				foreach($type as $t) {
					$types[] = $this->db->Quote($t);
				}
				$where[] = 'a.category_type IN ('.implode(',',$types).')';
			} else {
				if($getRoot) {
					$where[] = 'a.category_type IN ('.$this->db->Quote($type).',\'root\')';
				} else {
					$where[] = 'a.category_type = '.$this->db->Quote($type);
				}
			}
		}

		if((int)$root > 0) {
			$table .= ' INNER JOIN '.hikamarket::table('shop.category').' AS b On b.category_id = ' . (int)$root . ' ';
			$where[] = 'a.category_left >= b.category_left AND a.category_right <= b.category_right';
		}

		if(!empty($where)) {
			$where = ' WHERE (' . implode(') AND (', $where). ')';
		} else {
			$where = '';
		}
		$this->db->setQuery($select . $table . $where . ' ORDER BY a.category_left ASC'); //' ORDER BY a.category_parent_id ASC, a.category_ordering ASC');
		$elements = $this->db->loadObjectList();

		foreach($elements as &$element) {
			if(empty($element->value)){
				$val = str_replace(array(' ',','), '_', strtoupper($element->category_name));
				$element->value = JText::_($val);
				if($val == $element->value) {
					$element->value = $element->category_name;
				}
			}
			$element->category_name = $element->value;

			if($element->category_namekey == 'root') {
				$element->category_parent_id = -1;
			}
			unset($element);
		}
		return $elements;
	}

	public function beforeCategoryCreate(&$category, &$do) {
		if($category->category_type != 'vendor')
			return;
		$test = $category->category_type . '_' . $category->category_created . '_';
		if(substr($category->category_namekey, 0, strlen($test)) != $test)
			return;
		$category->category_type = 'product';
		$category->category_namekey = $category->category_type . '_' . $category->category_created . '_' . rand();
	}

	public function beforeCategoryUpdate(&$category, &$do) {
		if(empty($category->hikamarket) || empty($category->hikamarket->force_type))
			return;
		$category->category_type = $category->hikamarket->force_type;
	}

	public function processListing(&$filters, &$order, &$view, &$leftjoin) {
		if(!$view->module)
			return;

		$option = hikaInput::get()->getString('option','');
		$ctrl = hikaInput::get()->getString('ctrl','');
		$task = hikaInput::get()->getString('task','');
		$viewName = $view->getName();
		if($option != HIKAMARKET_COMPONENT || $ctrl != 'vendor' || $task != 'show' || $viewName != 'category')
			return;

		$content_synchronize = $view->params->get('content_synchronize', 0);
		if(!$content_synchronize)
			return;

		$vendor_id = hikamarket::getCID('vendor_id');
		$filter_type = $view->params->get('filter_type');
		$filter_only_products = (int)$view->params->get('only_if_products', 0);

		if($filter_only_products) {
			$leftjoin .= ' INNER JOIN '.hikamarket::table('shop.product_category').' AS pc ON pc.category_id = a.category_id '.
				' INNER JOIN '.hikamarket::table('shop.product').' AS p ON p.product_id = pc.product_id ';
			if($vendor_id == 1)
				$filters[] = '(p.product_vendor_id = 1 OR p.product_vendor_id = 0)';
			else
				$filters[] = 'p.product_vendor_id = '.$vendor_id;
		}

		if((int)$filter_type == 0) {
			$vendorClass = hikamarket::get('class.vendor');
			$rootCategory = $vendorClass->getRootCategory($vendor_id);
			if(empty($rootCategory))
				return;

			if(isset($filters['category_parent_id'])) {
				$filters['category_parent_id'] = 'a.category_parent_id = ' . (int)$rootCategory;
			} else {
				foreach($filters as &$filter) {
					if(substr($filter, 0, 23) != 'a.category_parent_id = ')
						continue;
					$filter = 'a.category_parent_id = ' . (int)$rootCategory;
					break;
				}
				unset($filter);
			}
		}
	}

	public function processView(&$view) {
		$layout = $view->getLayout();
		if(!in_array($layout, array('listing')))
			return;

		$config = hikamarket::config();
		$paramsOpt = (int)$view->params->get('market_vendor_categories', -1);

		if($paramsOpt < 0)
			$paramsOpt = (int)$config->get('override_vendor_category_link', 0);

		if(empty($paramsOpt))
			return;

		if(empty($view->rows))
			return;

		foreach($view->rows as &$row) {
			if(!isset($row->category_namekey) || substr($row->category_namekey, 0, 7) != 'vendor_')
				continue;

			$vendor_id = (int)substr($row->category_namekey, 7);
			$vendorItemId = (int)$config ->get('vendor_default_menu', '');

			if(empty($vendorItemId))
				$vendorItemId = '';
			if(!empty($vendorItemId))
				$vendorItemId = '&Itemid='.$vendorItemId;
			$row->override_url = hikamarket::completeLink('vendor&task=show&cid='.$vendor_id.$vendorItemId);

			if(!empty($row->link)) {
				$row->link_origin = $row->link;
				$row->link = $row->override_url;
			}
		}
		unset($row);
	}
}
