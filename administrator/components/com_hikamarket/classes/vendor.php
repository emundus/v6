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
class hikamarketVendorClass extends hikamarketClass {

	protected $tables = array('vendor');
	protected $pkeys = array('vendor_id');
	protected $toggle = array('vendor_published' => 'vendor_id');

	private $rootCategoriesCache = array();

	public function saveForm() {
		$vendor_id = hikamarket::getCID('vendor_id');
		$formData = hikaInput::get()->get('data', array(), 'array');

		$app = JFactory::getApplication();
		$config = hikamarket::config();

		if(!empty($formData['vendor']['vendor_params']['notif_order_statuses']))
			$formData['vendor']['vendor_params']['notif_order_statuses'] = implode(',',$formData['vendor']['vendor_params']['notif_order_statuses']);
		if(!empty($formData['vendor']['vendor_params']['extra_categories']))
			$formData['vendor']['vendor_params']['extra_categories'] = implode(',',$formData['vendor']['vendor_params']['extra_categories']);
		hikaInput::get()->set('data', $formData);

		$oldVendor = null;
		if(!empty($vendor_id))
			$oldVendor = $this->get($vendor_id);
		$fieldsClass = hikamarket::get('shop.class.field');
		$vendor = $fieldsClass->getInput(array('vendor','plg.hikamarket.vendor'), $oldVendor, false);
		$status = true;
		if($vendor === false) {
			if(!hikamarket::isAdmin() || $config->get('fields_block_save', 0))
				$status = false;
			$vendor = $_SESSION['hikashop_plg.hikamarket.vendor_data'];

			if(!empty($fieldsClass->error_fields)) {
				$app = JFactory::getApplication();
				$fields = array();
				foreach($fieldsClass->error_fields as $error_field) {
					$fields[] = $fieldsClass->trans($error_field->field_realname);
				}
				if(count($fields) > 1)
					$app->enqueueMessage(JText::sprintf('PLEASE_FILL_THE_FIELDS', implode(', ', $fields)), 'error');
				else
					$app->enqueueMessage(JText::sprintf('PLEASE_FILL_THE_FIELD', reset($fields)), 'error');
			} else {
				$app->enqueueMessage('ERROR_GETTING_VENDOR_DATA', 'error');
			}
		}

		if(empty($vendor))
			$vendor = new stdClass();
		$vendor->vendor_id = $vendor_id;
		$vendor->vendor_description = hikaInput::get()->getRaw('vendor_description', '');
		$vendor->vendor_terms = hikaInput::get()->getRaw('vendor_terms', '');

		if(!empty($oldVendor->vendor_params)) {
			foreach($oldVendor->vendor_params as $k => $v) {
				if(!isset($vendor->vendor_params->$k))
					$vendor->vendor_params->$k = $v;
			}
		}

		if(!empty($vendor->vendor_zone_id)) {
			$zoneClass = hikamarket::get('shop.class.zone');
			$zone = $zoneClass->get($vendor->vendor_zone_id);
			$vendor->vendor_zone_id = $zone->zone_id;
		}

		if(isset($vendor->vendor_location_lat))
			$vendor->vendor_location_lat = (float)hikamarket::toFloat(trim($vendor->vendor_location_lat));
		if(isset($vendor->vendor_location_long))
			$vendor->vendor_location_long = (float)hikamarket::toFloat(trim($vendor->vendor_location_long));

		$isVendorImage = hikaInput::get()->getInt('data_vendor_image', 0);
		if(empty($vendor->vendor_image) && $isVendorImage)
			$vendor->vendor_image = '';

		if(!hikamarket::isAdmin()) {
			$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);
			$vendor->vendor_description = $safeHtmlFilter->clean($vendor->vendor_description, 'string');
			$vendor->vendor_terms = $safeHtmlFilter->clean($vendor->vendor_terms, 'string');
		}

		$users = array();
		if(!empty($formData['users'])) {
			foreach($formData['users'] as $val) {
				$userid = (int)$val;
				if($userid > 0 )
					$users[$userid] = $userid;
			}
		}

		$acls = array();
		$userData = hikaInput::get()->get('user', array(), 'array');
		if(!empty($userData)) {
			foreach($userData as $k => $v) {
				if((int)$k <= 0 || !isset($users[(int)$k]) || !isset($v['user_access']))
					continue;
				if(empty($acls[$v['user_access']]))
					$acls[$v['user_access']]= array();
				$acls[$v['user_access']][] = (int)$k;
			}
		}

		$vendor->vendor_access = '';
		$marketaclType = hikamarket::get('type.market_acl');
		$vendor_access = hikaInput::get()->get('vendor_access', array(), 'array');
		$vendor_access_inherit = hikaInput::get()->getInt('vendor_access_inherit', 1);
		if(!$vendor_access_inherit) {
			if(count($vendor_access) == 1) {
				$vendor_access = reset($vendor_access);
				if($vendor_access == 'none') {
					$vendor->vendor_access = '';
				} else if($vendor_access == 'all' || $vendor_access == '*') {
					$vendor->vendor_access = '*';
				} else {
					$vendor->vendor_access = $marketaclType->compile(explode(',', $vendor_access));
				}
			} else {
				$vendor->vendor_access = $marketaclType->compile($vendor_access);
			}
		}

		$vendor_group = hikaInput::get()->getString('vendor_group', '');
		if(!empty($vendor_group) && $vendor_group != 'none' && $vendor_group != 'all') {
			$vendor_groups = explode(',', $vendor_group);
			foreach($vendor_groups as $k => $v) {
				if((int)$v > 0)
					$vendor_groups[$k] = '@' . (int)$v;
				else
					unset($vendor_groups[$k]);
			}
			if(!empty($vendor_groups)) {
				if($vendor_access_inherit)
					$vendor->vendor_access = '@0';
				if(!empty($vendor->vendor_access))
					$vendor->vendor_access .= ',';
				$vendor->vendor_access .= implode(',', $vendor_groups);
			}
		}

		if($status) {
			$status = $this->save($vendor);
		} else {
			hikaInput::get()->set('fail[vendor]', $vendor);
			return $status;
		}

		if(!$status) {
			hikaInput::get()->set('fail[vendor]', $vendor);
			if(!empty($this->message)) {
				$app->enqueueMessage($this->message, 'error');
				unset($this->message);
			}
			return $status;
		}

		if($status > 1) {
			if(empty($formData['vendor_fee']))
				$formData['vendor_fee'] = array();
			$feeClass = hikamarket::get('class.fee');
			$feeClass->saveVendor($status, $formData['vendor_fee']);
		}

		if(!empty($vendor->vendor_id_previous)) {
			$query = 'UPDATE '.hikamarket::table('shop.user').
				' SET user_vendor_id = 0, user_vendor_access = '.$this->db->Quote('').
				' WHERE user_vendor_id = '.$vendor->vendor_id_previous;
			if(!empty($users))
				$query .= ' AND user_id NOT IN ('.implode(',', $users).')';
			$this->db->setQuery($query);
			$this->db->execute();
		}

		if((int)$config->get('user_multiple_vendor', 0)) {
			$this->assignUsersToVendor($vendor, $users, $acls);
			return $status;
		}

		if(empty($users))
			return $status;
		$query = 'UPDATE '.hikamarket::table('shop.user').' SET user_vendor_id = '.$vendor->vendor_id.' WHERE user_id IN ('.implode(',',$users).') AND (user_vendor_id IS NULL OR user_vendor_id = 0)';
		$this->db->setQuery($query);
		$this->db->execute();

		if(!empty($acls)) {
			foreach($acls as $acl => $u) {
				$query = 'UPDATE '.hikamarket::table('shop.user').' SET user_vendor_access = ' . $this->db->Quote($acl) . ' WHERE user_id IN ('.implode(',',$u).') AND (user_vendor_id = '.$vendor->vendor_id.')';
				$this->db->setQuery($query);
				$this->db->execute();
			}
		}
		return $status;
	}

	public function save(&$vendor) {
		$new = false;
		$do = true;
		$app = JFactory::getApplication();
		$config = hikamarket::config();
		$shopConfig = hikamarket::config(false);

		if(empty($vendor->vendor_id)) {
			$new = true;
			$vendor->vendor_created = time();

			if(empty($vendor->vendor_currency_id)) {
				$shopConfig = hikamarket::config(false);
				$vendor->vendor_currency_id = $shopConfig->get('main_currency', 1);
			}
		} else {
			$vendor->vendor_modified = time();
			$vendor->old = $this->get((int)$vendor->vendor_id);
		}

		if(!empty($vendor->vendor_id) && $vendor->vendor_id == 1) {
			$vendor->vendor_published = true;
			$vendor->vendor_admin_id = 0;
		}

		if(!empty($vendor->vendor_params))
			$vendor->vendor_params = serialize($vendor->vendor_params);

		if($shopConfig->get('alias_auto_fill', 1) && empty($vendor->vendor_alias) && empty($vendor->old->vendor_alias)) {
			$vendor->vendor_alias = !empty($vendor->vendor_name) ? $vendor->vendor_name : $vendor->old->vendor_name;

			$jconfig = JFactory::getConfig();
			$app = JFactory::getApplication();
			if(!$jconfig->get('unicodeslugs')) {
				$lang = JFactory::getLanguage();
				$vendor->vendor_alias = $lang->transliterate($vendor->vendor_alias);
			}
			if(method_exists($app,'stringURLSafe'))
				$vendor->vendor_alias = $app->stringURLSafe($vendor->vendor_alias);
			else
				$vendor->vendor_alias = JFilterOutput::stringURLSafe($vendor->vendor_alias);

			if($shopConfig->get('sef_remove_id', 0)) {
				$int_at_the_beginning = (int)$vendor->vendor_alias;
				if($int_at_the_beginning)
					$vendor->vendor_alias = $shopConfig->get('alias_prefix', 'v') . $vendor->vendor_alias;
			}
		}

		if(!empty($vendor->vendor_address_vat) && (empty($vendor->old) || $vendor->old->vendor_address_vat != $vendor->vendor_address_vat)) {
			$vatHelper = hikamarket::get('shop.helper.vat');
			if(!$vatHelper->isValid($vendor->vendor_address_vat)) {
				$this->message = @$vatHelper->message;
				return false;
			}
		}

		JPluginHelper::importPlugin('hikamarket');

		if($new)
			$app->triggerEvent('onBeforeVendorCreate', array(&$vendor, &$do));
		else
			$app->triggerEvent('onBeforeVendorUpdate', array(&$vendor, &$do));

		if(!$do)
			return false;

		$status = parent::save($vendor);

		if(!$status)
			return $status;

		self::get(false);

		if(!$new)
			$vendor->vendor_id_previous = $vendor->vendor_id;
		$vendor->vendor_id = $status;

		$config = hikamarket::config();
		if($config->get('vendor_create_category', 0) && $vendor->vendor_id > 1) {
			$category_id = false;
			$parentCategory = $config->get('vendor_parent_category', 0);
			if($parentCategory <= 0)
				$parentCategory = 1;

			if(!$new) {
				$query = 'SELECT category_id FROM ' . hikamarket::table('shop.category') . ' WHERE category_namekey = ' . $this->db->Quote('vendor_' . $vendor->vendor_id);
				$this->db->setQuery($query);
				$category_id = $this->db->loadResult();
			}

			if(empty($vendor->vendor_name)) {
				$fullVendor = $this->get($vendor->vendor_id);
			} else {
				$fullVendor = &$vendor;
			}

			$category = new stdClass();
			$category->category_name = $fullVendor->vendor_name;
			if(isset($fullVendor->vendor_published))
				$category->category_published = $fullVendor->vendor_published;
			if(isset($fullVendor->vendor_description))
				$category->category_description = $fullVendor->vendor_description;

			if($new || !$category_id) {
				$category->category_type = 'vendor';
				$category->category_parent_id = $parentCategory;
				$category->category_namekey = 'vendor_' . $vendor->vendor_id;
			} else {
				$category->category_id = $category_id;
			}

			$do = true;
			if($new || !$category_id)
				$app->triggerEvent('onBeforeVendorCategoryCreate', array(&$fullVendor, &$category, &$do));
			else
				$app->triggerEvent('onBeforeVendorCategoryUpdate', array(&$fullVendor, &$category, &$do));

			if($do) {
				$categoryClass = hikamarket::get('shop.class.category');
				$categoryClass->save($category);
			}
		}

		if(!$new && (int)$config->get('allow_registration', 0) == 1 && empty($vendor->old->vendor_published) && !empty($vendor->vendor_published)) {
			$user_id = (isset($vendor->vendor_admin_id) ? (int)$vendor->vendor_admin_id : (int)$vendor->old->vendor_admin_id);
			$userClass = hikamarket::get('shop.class.user');
			$shopuser = $userClass->get($user_id);

			$mailClass = hikamarket::get('shop.class.mail');
			$mailClass->mail_folder = HIKAMARKET_MEDIA . 'mail' . DS;
			$infos = new stdClass;
			$infos->vendor =& $vendor;
			$infos->vendor_name = isset($vendor->vendor_name) ? $vendor->vendor_name : $vendor->old->vendor_name;
			$infos->vendor_email = isset($vendor->vendor_email) ? $vendor->vendor_email : $vendor->old->vendor_email;
			$infos->user =& $shopuser;
			$infos->name = $infos->user->name;

			$mail = $mailClass->get('market.vendor_approval', $infos);
			if(!empty($mail)) {
				$mail->subject = JText::sprintf($mail->subject, HIKASHOP_LIVE);
				$mail->from_name = $shopConfig->get('from_name');
				$mail->from_email = $shopConfig->get('from_email');
				$mail->dst_email = (!empty($infos->email)) ? $infos->email : $infos->vendor_email;
				$mail->dst_name = (!empty($infos->name)) ? $infos->name : $infos->vendor_name;

				if(!empty($mail->dst_email))
					$mailClass->sendMail($mail);
			}
		}

		if($new)
			$app->triggerEvent('onAfterVendorCreate', array(&$vendor));
		else
			$app->triggerEvent('onAfterVendorUpdate', array(&$vendor));

		return $status;
	}

	public function get($id, $type = 'vendor') {
		static $data = array();

		if($id === false) {
			$data = array();
			$true = true;
			return $true;
		}

		if(!in_array($type, array('vendor','admin','cms','user')))
			return false;

		if(!empty($data[$type.'_'.$id])) {
			return $data[$type.'_'.$id];
		}

		$links = array(
			'vendor' => 'v.vendor_id',
			'admin' => 'v.vendor_admin_id',
			'cms' => 'u.user_cms_id',
			'user' => 'u.user_id',
		);

		$where = array(
			'main' => $links[$type] . '=' . (int)$id
		);
		$select = array(
			'vendor' => 'v.*'
		);
		$table_join = array();

		if( in_array($type, array('user', 'cms')) ) {
			$select['user'] = 'u.*';
			$table_join['user'] = 'LEFT JOIN '.hikamarket::table('shop.user').' AS u ON ( v.vendor_admin_id = u.user_id OR v.vendor_id = u.user_vendor_id )';
		}

		$query = 'SELECT '.implode(',', $select) .
			' FROM ' . hikamarket::table('vendor') . ' AS v ' .
			implode(' ', $table_join) .
			' WHERE (' . implode(') AND (', $where) . ')';
		$this->db->setQuery($query);
		$vendor = $this->db->loadObjectList();

		if(count($vendor) == 1) {
			$vendor = reset($vendor);
		} else {
			$vendors = $vendor;
			$vendor = reset($vendors);
			foreach($vendors as $v) {
				if((int)$v->vendor_admin_id == $id) {
					$vendor = $v;
					break;
				}
			}
			unset($vendors);
		}

		if(empty($vendor) && in_array($type, array('user', 'cms'))) {
			$select['vendor_user'] = 'vu.user_access as vendor_user_access';
			$table_join['user'] = ' INNER JOIN '.hikamarket::table('vendor_user') . ' AS vu ON (v.vendor_id = vu.vendor_id)' .
				' INNER JOIN '.hikamarket::table('shop.user').' AS u ON (u.user_id = vu.user_id)';

			$query = 'SELECT '.implode(',', $select) .
				' FROM ' . hikamarket::table('vendor') . ' AS v ' .
				implode(' ', $table_join) .
				' WHERE (' . implode(') AND (', $where) . ') ORDER BY v.vendor_id ASC';
			$this->db->setQuery($query);
			$vendor = $this->db->loadObject();
		}

		if(!empty($vendor)) {
			if(!empty($vendor->user_params))
				$vendor->user_params = hikamarket::unserialize($vendor->user_params);

			$vendor->vendor_location_lat = (float)hikamarket::toFloat($vendor->vendor_location_lat);
			$vendor->vendor_location_long = (float)hikamarket::toFloat($vendor->vendor_location_long);

			if(!empty($vendor->vendor_params)) {
				$vendor->vendor_params = hikamarket::unserialize($vendor->vendor_params);
			} else {
				$vendor->vendor_params = null;
			}
		}

		$data[$type.'_'.$id] =& $vendor;

		return $data[$type.'_'.$id];
	}

	public function getUserVendors($user_id = null) {
		if(empty($user_id))
			$user_id = hikamarket::loadUser(false);
		if(empty($user_id))
			return false;

		$ret = array();

		$select = array(
			'vendor' => 'v.*',
			'user' => 'u.*',
		);
		$where = array(
			'main' => 'u.user_id = ' . (int)$user_id
		);
		$query = 'SELECT '.implode(',', $select) .
			' FROM ' . hikamarket::table('vendor') . ' AS v ' .
			' LEFT JOIN '.hikamarket::table('shop.user').' AS u ON ( v.vendor_admin_id = u.user_id OR v.vendor_id = u.user_vendor_id )' .
			' WHERE (' . implode(') AND (', $where) . ')';
		$this->db->setQuery($query);
		$ret = $this->db->loadObjectList('vendor_id');

		$select = array(
			'vendor' => 'v.*',
			'vendor_user' => 'vu.*',
		);
		$where = array(
			'main' => 'vu.user_id = ' . (int)$user_id
		);
		$query = 'SELECT '.implode(',', $select) .
			' FROM ' . hikamarket::table('vendor') . ' AS v ' .
			' INNER JOIN '.hikamarket::table('vendor_user') . ' AS vu ON (v.vendor_id = vu.vendor_id)' .
			' WHERE (' . implode(') AND (', $where) . ')'.
			' ORDER BY v.vendor_id ASC';
		$this->db->setQuery($query);
		$vendors = $this->db->loadObjectList('vendor_id');

		foreach($vendors as $vendor_id => $vendor) {
			if(!isset($ret[$vendor_id]))
				$ret[$vendor_id] = $vendor;
		}
		unset($vendors);

		return $ret;
	}

	public function switchVendor($vendor_id) {
		$app = JFactory::getApplication();
		if(hikamarket::isAdmin())
			return false;

		$user_id = hikamarket::loadUser(false);
		if(empty($user_id))
			return false;

		$current_vendor_id = (int)$app->getUserState(HIKAMARKET_COMPONENT.'.vendor_id', 0);
		if(empty($current_vendor_id))
			return false;

		if($vendor_id == $current_vendor_id)
			return true;

		$vendors = $this->getList($user_id);
		if(!isset($vendors[$vendor_id]))
			return false;

		$app->setUserState(HIKAMARKET_COMPONENT.'.vendor_id', $vendor_id);
		return true;
	}

	public function &getNameboxData(&$typeConfig, &$fullLoad, $mode, $value, $search, $options) {
		$ret = array(
			0 => array(),
			1 => array()
		);

		$sqlFilters = array('vendor.vendor_published = 1');
		if(!empty($options['filters'])) {
			foreach($options['filters'] as $filter) {
			}
		}

		$app = JFactory::getApplication();
		if(!hikamarket::isAdmin()) {
			if(isset($typeConfig['options']['olist']))
				unset($typeConfig['options']['olist']);
			$typeConfig['displayFormat'] = '{vendor_name}';
		}

		if(!empty($search)) {
			$searchMap = array('vendor.vendor_id', 'vendor.vendor_name', 'vendor.vendor_email');
			$searchVal = '\'%' . $this->db->escape(HikaStringHelper::strtolower($search), true) . '%\'';
			$sqlFilters[] = '('.implode(' LIKE '.$searchVal.' OR ', $searchMap).' LIKE '.$searchVal.')';
		}

		$sqlSort = 'vendor.vendor_id';
		if(!empty($options['sort']) && is_string($options['sort']) && $options['sort'] === 'name') // Conflict with "sort" parameter of Namebox for drag'n'drop
			$sqlSort = 'vendor.vendor_name';

		$max = 30;
		$start = (int)@$options['page'];

		$query = 'SELECT vendor.vendor_id, vendor.vendor_name, vendor.vendor_email '.
			' FROM ' . hikamarket::table('vendor') . ' AS vendor '.
			' WHERE ('.implode(') AND (', $sqlFilters).') '.
			' ORDER BY '.$sqlSort;
		$this->db->setQuery($query, $start, $max+1);
		$vendors = $this->db->loadObjectList('vendor_id');
		if(count($vendors) > $max) {
			$fullLoad = false;
			array_pop($vendors);
		}

		if(!empty($value) && !is_array($value) && (int)$value > 0) {
			$value = (int)$value;
			if(isset($vendors[$value])) {
				$ret[1] = $vendors[$value];
			} else {
				$query = 'SELECT vendor.vendor_id, vendor.vendor_name, vendor.vendor_email '.
					' FROM ' . hikamarket::table('vendor') . ' AS vendor '.
					' WHERE vendor_id = ' . $value;
				$this->db->setQuery($query);
				$ret[1] = $this->db->loadObject();
			}
		} else if(!empty($value) && is_array($value)) {
			hikamarket::toInteger($value);
			$query = 'SELECT vendor.vendor_id, vendor.vendor_name, vendor.vendor_email '.
				' FROM ' . hikamarket::table('vendor') . ' AS vendor '.
				' WHERE vendor_id IN (' . implode(',', $value) . ')';
			$this->db->setQuery($query);
			$ret[1] = $this->db->loadObjectList('vendor_id');
		}

		if(!empty($vendors))
			$ret[0] = $vendors;
		return $ret;
	}

	public function getRootCategory($vendor, $type = null) {
		$config = hikamarket::config();
		if($type === null)
			$vendor_chroot_category = (int)$config->get('vendor_chroot_category', 0);
		else
			$vendor_chroot_category = (int)$type;
		if($vendor_chroot_category == 0)
			return 0;

		if(isset($vendor->vendor_id))
			$vendor_id = (int)$vendor->vendor_id;
		else
			$vendor_id = (int)$vendor;
		if(empty($vendor_id) || $vendor_id <= 1)
			return 0;

		if($vendor_chroot_category == 1) {
			if(!isset($this->rootCategoriesCache[$vendor_id])) {
				$this->db->setQuery('SELECT category_id FROM '.hikamarket::table('shop.category').' WHERE category_namekey = ' . $this->db->Quote('vendor_'.$vendor_id));
				$this->rootCategoriesCache[$vendor_id] = (int)$this->db->loadResult();
			}

			return $this->rootCategoriesCache[$vendor_id];
		}

		if($vendor_chroot_category == 2) {
			if(!isset($vendor->vendor_id))
				$vendor = $this->get($vendor_id);
			if(!empty($vendor->vendor_params->vendor_root_category))
				return (int)$vendor->vendor_params->vendor_root_category;
			return (int)$config->get('vendor_root_category', 0);
		}

		return 0;
	}

	public function getExtraCategories($vendor) {
		if(!isset($vendor->vendor_id)) {
			$vendor_id = (int)$vendor;
			$vendor = $this->get($vendor_id);
		} else
			$vendor_id = (int)$vendor->vendor_id;

		if(empty($vendor_id) || $vendor_id <= 1)
			return array();

		$config = hikamarket::config();
		$categories = explode(',', $config->get('vendor_extra_categories', ''));

		if(!empty($vendor->vendor_params->extra_categories)) {
			$c = explode(',', $vendor->vendor_params->extra_categories);
			$categories = array_merge($categories, $c);
		}


		hikamarket::toInteger($categories);
		array_unique($categories);
		sort($categories);
		$categories = array_combine($categories, $categories);
		if(isset($categories[0]))
			unset($categories[0]);

		return $categories;
	}

	public function filterCategories($categories, $rootCategory, $oldCategories, $extraCategories = array()) {
		if(empty($rootCategory) || (int)$rootCategory == 0)
			return $categories;

		hikamarket::toInteger($oldCategories);
		$filteredCategories = array_diff($categories, $oldCategories);

		if(empty($filteredCategories))
			return $categories;

		$filters = array(
			'root' => 'rootcat.category_id = ' . (int)$rootCategory,
			'cat' => 'cat.category_id IN ('. implode(',', $filteredCategories) . ')',
		);

		if(!empty($extraCategories)) {
			$extraCategories = array_merge(array($rootCategory), $extraCategories);
			hikamarket::toInteger($extraCategories);
			array_unique($extraCategories);
			sort($extraCategories);

			$filters['root'] = 'rootcat.category_id IN (' . implode(',', $extraCategories) . ')';
		}

		$query = 'SELECT cat.category_id '.
			' FROM ' . hikamarket::table('shop.category') . ' as cat '.
			' INNER JOIN ' . hikamarket::table('shop.category') . ' as rootcat ON cat.category_left >= rootcat.category_left AND cat.category_right <= rootcat.category_right '.
			' WHERE (' . implode(') AND (', $filters) . ')';

		$this->db->setQuery($query);
		$ret = $this->db->loadColumn();

		hikamarket::toInteger($ret);

		$keepCategories = array_intersect($categories, $oldCategories);
		$addedCategories = array_intersect($categories, $ret);
		return array_unique(array_merge($keepCategories, $addedCategories));
	}

	public function delete(&$elements) {
		if(empty($elements))
			return false;
		if(!is_array($elements))
			$elements = array($elements);

		foreach($elements as $k => $e) {
			$e = (int)$e;
			if( $e <= 1 )
				unset($elements[$k]);
			else
				$elements[$k] = (int)$e;
		}

		if(empty($elements))
			return false;

		$app = JFactory::getApplication();
		JPluginHelper::importPlugin('hikamarket');
		$do = true;
		$app->triggerEvent('onBeforeVendorDelete', array(&$elements, &$do));
		if(!$do)
			return false;

		$status = parent::delete($elements);

		if($status) {
			$query = 'UPDATE '.hikamarket::table('shop.product').' SET product_vendor_id = 0 WHERE product_vendor_id IN ('.implode(',', $elements).')';
			$this->db->setQuery($query);
			$this->db->execute();

			$query = 'UPDATE '.hikamarket::table('shop.user').' SET user_vendor_id = 0 WHERE user_vendor_id IN ('.implode(',', $elements).')';
			$this->db->setQuery($query);
			$this->db->execute();

			$app->triggerEvent('onAfterVendorDelete', array(&$elements));
			return count($elements);
		}
		return $status;
	}

	public function register($shopuser = false) {
		$currentUser = hikamarket::loadUser(true);
		if(!empty($currentUser))
			$shopuser = $currentUser;

		JSession::checkToken() || die('Invalid Token');

		$do = true;
		$app = JFactory::getApplication();
		$config = hikamarket::config();
		$shopConfig = hikamarket::config(false);
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);

		$old = null;
		$fieldClass = hikamarket::get('shop.class.field');
		$vendor = $fieldClass->getInput( array('vendorregister','plg.hikamarket.vendor'), $old, true, 'data', false, 'display:vendor_registration=1');

		if($vendor === false) {
			$do = false;

			$vendor = @$_SESSION['hikashop_'.'plg.hikamarket.vendor'.'_data'];
			if(empty($vendor))
				$vendor = new stdClass();
		}

		if(!empty($vendor->vendor_params) && is_array($vendor->vendor_params)) {
			$params = new stdClass();
			foreach($vendor->vendor_params as $k => $v) {
				$params->$k = $v;
			}
			$vendor->vendor_params = $params;
		} elseif(empty($vendor->vendor_params)) {
			$vendor->vendor_params = new stdClass();
		}

		if($config->get('register_paypal_required', 0) && empty($vendor->vendor_params->paypal_email) && $config->get('register_ask_paypal', 1)) {
			$do = false;
			$app->enqueueMessage(JText::_('HIKAM_ERR_PAYPAL_EMAIL_EMPTY'), 'error');
		}

		if($config->get('register_ask_description', 1)) {
			$vendor->vendor_description = hikaInput::get()->getRaw('vendor_description', '');
			$vendor->vendor_description = $safeHtmlFilter->clean($vendor->vendor_description, 'string');
		}

		if($config->get('register_ask_terms', 0)) {
			$vendor->vendor_terms = hikaInput::get()->getRaw('vendor_terms', '');
			$vendor->vendor_terms = $safeHtmlFilter->clean(trim($vendor->vendor_terms), 'string');

			if($config->get('register_terms_required', 0) && empty($vendor->vendor_terms)) {
				$do = false;
				$app->enqueueMessage(JText::_('HIKAM_ERR_TERMS_EMPTY'), 'error');
			}
		}

		if(!$do) {
			$old = null;
			$fieldClass->getInput('register', $old, true);
			$fieldClass->getInput('user', $old, true);
			if($shopConfig->get('address_on_registration', 1))
				$fieldClass->getInput('address', $old, true);

			hikaInput::get()->set('fail[vendor]', $vendor);
			return false;
		}


		$user_activated = true;
		if(empty($shopuser)) {
			$userClass = hikamarket::get('class.user');
			$status = $userClass->register(false);

			if(!$status) {
				hikaInput::get()->set('fail[vendor]', $vendor);
				return false;
			}

			$user_activated = ($status->active === 0); // $status->active = the Joomla setting for user activation
			$shopuser = $userClass->get($userClass->user_id);
		}

		$vendor->vendor_admin_id = $shopuser->user_id;
		$vendor->vendor_published = ($config->get('allow_registration', 0) == 2 && $user_activated);

		if(empty($vendor->vendor_name))
			$vendor->vendor_name = $shopuser->name;
		if(empty($vendor->vendor_email))
			$vendor->vendor_email = $shopuser->user_email;

		if($config->get('register_paypal_required', 0) && empty($vendor->vendor_params->paypal_email)) {
			$vendor->vendor_params->paypal_email = $vendor->vendor_email;
		}

		if(!empty($vendor->vendor_params)) {
			$accepted_params = array(
				'paypal_email',
				'invoice_number_format'
			);
			$vars = array_keys(get_object_vars($vendor->vendor_params));
			foreach($vars as $var) {
				if(!empty($var) && !in_array($var, $accepted_params))
					unset($vendor->vendor_params->$var);
			}
		}

		$query = 'SELECT * FROM '.hikamarket::table('shop.field').' WHERE field_table = \'plg.hikamarket.vendor\' AND field_frontcomp = 1 AND field_published = 1 ORDER BY field_ordering';
		$this->db->setQuery($query);
		$vendorFields = $this->db->loadObjectList();
		if(!empty($vendorFields)) {
			foreach($vendorFields as $vendorField) {
				$namekey = $vendorField->field_namekey;
				if(substr($namekey, 0, 7) != 'vendor_')
					continue;

				$name = substr($namekey, 7);
				if(isset($status->address_data) && isset($status->address_data->$name)) {
					$vendor->$namekey = $status->address_data->$name;
				}
			}
		}

		if($config->get('link_admin_groups', 0)) {
			jimport('joomla.access.access');
			$groups = JAccess::getGroupsByUser($shopuser->user_cms_id, (bool)$shopConfig->get('inherit_parent_group_access'));

			$accesses = array('@0');
			if(!empty($groups)) {
				$accesses = array();
				foreach($groups as $group) {
					$accesses[] = '@' . $group;
				}
			}
			$vendor->vendor_access = implode(',', $accesses);
		}


		$status = $this->save($vendor);

		if(!$status) {
			hikaInput::get()->set('fail[vendor]', $vendor);
			return false;
		}

		hikamarket::loadVendor(false, true);


		$mailClass = hikamarket::get('shop.class.mail');
		$mailClass->mail_folder = HIKAMARKET_MEDIA . 'mail' . DS;
		$infos = new stdClass;
		$infos->vendor =& $vendor;
		$infos->vendor_name = $vendor->vendor_name;
		$infos->user =& $shopuser;
		$infos->name = $infos->user->name;

		$mail = $mailClass->get('market.vendor_admin_registration', $infos);
		if(!empty($mail)) {
			$mail->subject = JText::sprintf($mail->subject, HIKASHOP_LIVE);
			if(!empty($infos->email))
				$mail->dst_email = $infos->email;
			else
				$mail->dst_email = $shopConfig->get('from_email');

			if(!empty($infos->name))
				$mail->dst_name = $infos->name;
			else
				$mail->dst_name = $shopConfig->get('from_name');

			$mailClass->sendMail($mail);
		}

		$mail = $mailClass->get('market.vendor_registration', $infos);
		if(!empty($mail)) {
			$mail->subject = JText::sprintf($mail->subject, HIKASHOP_LIVE);
			$mail->from_name = $shopConfig->get('from_name');
			$mail->from_email = $shopConfig->get('from_email');
			if(!empty($infos->email))
				$mail->dst_email = $infos->email;
			else
				$mail->dst_email = $vendor->vendor_email;

			if(!empty($infos->name))
				$mail->dst_name = $infos->name;
			else
				$mail->dst_name = $vendor->vendor_name;

			if(!empty($mail->dst_email))
				$mailClass->sendMail($mail);
		}

		return $status;
	}

	public function frontSaveForm() {
		$vendor_id = hikamarket::getCID('vendor_id');
		$formData = hikaInput::get()->get('data', array(), 'array');

		$config = hikamarket::config();
		$user = hikamarket::loadUser();
		$userVendor = hikamarket::loadVendor(true);
		if($vendor_id != $userVendor->vendor_id || !hikamarket::acl('vendor/edit')) {
			return false;
		}

		$fieldClass = hikamarket::get('shop.class.field');
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);

		$status = true;
		$oldVendor = $this->get($vendor_id);
		$vendor = $fieldClass->getFilteredInput(array('vendor','plg.hikamarket.vendor'), $oldVendor);

		if($vendor === false) {
			$status = false;
			$vendor = $_SESSION['hikashop_plg.hikamarket.vendor_data'];
		}
		$vendor->vendor_id = $vendor_id;

		$loadFields = array(
			'vendor_name', 'vendor_image', 'vendor_email', 'vendor_currency_id'
		);
		foreach($loadFields as $loadField) {
			if(isset($formData['vendor'][$loadField]) && is_string($formData['vendor'][$loadField]))
				$vendor->{$loadField} = strip_tags(trim($formData['vendor'][$loadField]));
		}
		if(isset($formData['vendor']['vendor_params'])) {
			$null = array();
			$fieldClass->checkFields($formData['vendor']['vendor_params'], $vendor->vendor_params, 'plg.hikamarket.vendor', $null);
		}

		if(!hikamarket::acl('vendor/edit/image')) {
			unset($vendor->vendor_image);
		} else if(empty($vendor->vendor_image)) {
			$vendor->vendor_image = '';
		}

		if(!empty($vendor->vendor_image) && $oldVendor->vendor_image != $vendor->vendor_image) {
			$shopConfig = hikamarket::config(false);
			$uploadFolder = ltrim(JPath::clean(html_entity_decode($shopConfig->get('uploadfolder'))),DS);
			$uploadFolder = rtrim($uploadFolder,DS).DS;
			$clean_filename = JPath::clean(realpath(JPATH_ROOT.DS.$uploadFolder.$vendor->vendor_image));
			if(!file_exists($clean_filename) || ($vendor_id > 1 && strpos(str_replace(array('\\/','\\','//'), '/', $vendor->vendor_image), 'vendor'.$vendor_id.'/') !== 0))
				$vendor->vendor_image = '';
		}

		if(hikamarket::acl('vendor/edit/location') && isset($formData['vendor']['vendor_location_lat'])) {
			if(trim($formData['vendor']['vendor_location_lat']) == '' || trim($formData['vendor']['vendor_location_long']) == '') {
				$vendor->vendor_location_lat = 0.0;
				$vendor->vendor_location_long = 0.0;
			} else {
				$vendor->vendor_location_lat = (float)hikamarket::toFloat($formData['vendor']['vendor_location_lat']);
				$vendor->vendor_location_long = (float)hikamarket::toFloat($formData['vendor']['vendor_location_long']);
			}
		} else {
			unset($vendor->vendor_location_lat);
			unset($vendor->vendor_location_long);
		}

		if($vendor->vendor_id > 1 && $config->get('register_paypal_required', 0) && empty($vendor->vendor_params->paypal_email)) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('HIKAM_ERR_PAYPAL_EMAIL_EMPTY'));

			$status = false;
		}

		$users = array();
		if(!empty($formData['users'])) {
			foreach($formData['users'] as $val) {
				$userid = (int)$val;
				if($userid > 0 && $userid != $user)
					$users[$userid] = $userid;
			}
		}

		$acls = array();
		$userData = hikaInput::get()->get('user', array(), 'array');
		if(!empty($userData)) {
			foreach($userData as $k => $v) {
				$k = (int)$k;
				if($k <= 0 || $k == $user)
					continue;
				if(isset($users[$k]) && isset($v['user_access'])) {
					if(empty($acls[$v['user_access']]))
						$acls[$v['user_access']]= array();
					$acls[$v['user_access']][] = $k;
				}
			}
		}

		if(isset($vendor->vendor_params)) {
			$new_params = $vendor->vendor_params;
			$vendor->vendor_params = $oldVendor->vendor_params;

			$accepted_params = array(
				'paypal_email',
				'invoice_number_format'
			);
			$vars = array_keys(get_object_vars($new_params));
			foreach($vars as $var) {
				if(!empty($var) && !in_array($var, $accepted_params))
					unset($new_params->$var);
			}
			foreach($new_params as $k => $v) {
				$vendor->vendor_params->$k = $v;
			}
		}

		$vendor->vendor_description = hikaInput::get()->getRaw('vendor_description', '');
		$vendor->vendor_description = $safeHtmlFilter->clean($vendor->vendor_description, 'string');

		if(hikamarket::acl('vendor/edit/terms')) {
			$vendor->vendor_terms = hikaInput::get()->getRaw('vendor_terms', '');
			$vendor->vendor_terms = $safeHtmlFilter->clean(trim($vendor->vendor_terms), 'string');

			if($config->get('register_terms_required', 0) && empty($vendor->vendor_terms)) {
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('HIKAM_ERR_TERMS_EMPTY'));
				$status = false;
			}
		}

		if($status) {
			$status = $this->save($vendor);
		} else {
			hikaInput::get()->set('fail[vendor]', $vendor);
			return $status;
		}

		if(!$status)
			return $status;

		hikamarket::loadVendor(false, true);

		if(empty($formData['users']) || !hikamarket::acl('vendor/edit/users'))
			return $status;

		if(!empty($vendor->vendor_id_previous)) {
			$query = 'UPDATE '.hikamarket::table('shop.user').
				' SET user_vendor_id = 0, user_vendor_access = '.$this->db->Quote('').
				' WHERE (user_id != '.(int)$user.') AND (user_vendor_id = '.$vendor->vendor_id_previous.')';
			if(!empty($users))
				$query .= ' AND user_id NOT IN ('.implode(',', $users).')';
			$this->db->setQuery($query);
			$this->db->execute();
		}

		if((int)$config->get('user_multiple_vendor', 0)) {
			$this->assignUsersToVendor($vendor, $users, $acls);
			return $status;
		}

		if(empty($users))
			return $status;

		$query = 'UPDATE '.hikamarket::table('shop.user').' SET user_vendor_id = '.$vendor->vendor_id.' WHERE user_id IN ('.implode(',',$users).') AND (user_vendor_id IS NULL OR user_vendor_id = 0)';
		$this->db->setQuery($query);
		$this->db->execute();

		if(!empty($acls)) {
			foreach($acls as $acl => $u) {
				$query = 'UPDATE '.hikamarket::table('shop.user').' SET user_vendor_access = ' . $this->db->Quote($acl) . ' WHERE user_id IN ('.implode(',',$u).') AND (user_vendor_id = '.$vendor->vendor_id.')';
				$this->db->setQuery($query);
				$this->db->execute();
			}
		}
		return $status;
	}

	public function checkProductLimitation($vendor, $display = true) {
		$config = hikamarket::config();
		if(is_numeric($vendor))
			$vendor = $this->get($vendor);
		$vendor_id = $vendor->vendor_id;

		$config_limitation = (int)$config->get('vendor_product_limitation', 0);
		$vendor_params = (!empty($vendor->vendor_params) && is_string($vendor->vendor_params)) ? hikamarket::unserialize($vendor->vendor_params) : $vendor->vendor_params;
		$vendor_limitation = (int)@$vendor_params->product_limitation;

		if($vendor_id > 1 && $vendor_limitation == 0) {
			$vendor_limitation = $config->vendorget($vendor, 'product_limitation', 0);
		}

		if($vendor_id > 1 && ($vendor_limitation > 0 || $config_limitation > 0)) {
			$limitation = $vendor_limitation;
			if($limitation == 0)
				$limitation = $config_limitation;

			$query = 'SELECT count(*) as `products` FROM ' . hikamarket::table('shop.product') . ' as product WHERE product.product_type = ' . $this->db->Quote('main') . ' AND product.product_vendor_id = ' . $vendor_id;
			$this->db->setQuery($query);
			$count_products = (int)$this->db->loadResult();

			if($count_products >= $limitation) {
				if($display) {
					$app = JFactory::getApplication();
					if($limitation > 1)
						$app->enqueueMessage(JText::sprintf('VENDOR_PRODUCT_LIMITATION_X_REACHED', $limitation), 'error');
					else
						$app->enqueueMessage(JText::_('VENDOR_PRODUCT_LIMITATION_REACHED'), 'error');
				}
				return $limitation;
			}
		}
		return true;
	}

	public function checkVendorCompletion($vendor, $display = true) {
		$config = hikamarket::config();
		if(!$config->get('check_vendor_completion', 0))
			return true;

		if(is_numeric($vendor))
			$vendor = $this->get($vendor);
		$vendor_id = $vendor->vendor_id;

		if($vendor_id == 0 || $vendor_id == 1)
			return true;

		$fieldsClass = hikamarket::get('shop.class.field');
		$this->report = $display;
		$allCat = $fieldsClass->getCategories('plg.hikamarket.vendor', $vendor);
		$fields =& $fieldsClass->getData('frontcomp', 'plg.hikamarket.vendor', false, $allCat);
		$data = new stdClass();
		$formData = array();
		$fieldCheck = $fieldsClass->_checkOneInput($fields, $formData, $data, 'plg.hikamarket.vendor', $vendor);

		if($config->get('register_paypal_required', 0)) {
			$vendor_params = (!empty($vendor->vendor_params) && is_string($vendor->vendor_params)) ? hikamarket::unserialize($vendor->vendor_params) : $vendor->vendor_params;
			if(empty($vendor_params->paypal_email)) {
				if($display) {
					$app = JFactory::getApplication();
					$app->enqueueMessage(JText::sprintf('PLEASE_FILL_THE_FIELD', JText::_('PAYPAL_EMAIL')), 'error');
				}
				$fieldCheck = false;
			}
		}

		if($config->get('register_terms_required', 0) && empty($vendor->vendor_terms)) {
			if($display) {
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::sprintf('PLEASE_FILL_THE_FIELD', JText::_('HIKASHOP_CHECKOUT_TERMS')), 'error');
			}
			$fieldCheck = false;
		}

		if(!$fieldCheck)
			return false;

		return true;
	}

	public function getAddressId($vendor) {
		if(empty($vendor->vendor_id)) {
			$vendor_id = (int)$vendor;
			if(!empty($vendor_id)) {
				$vendor = $this->get($vendor_id);
			} else
				return false;
		}
		if(empty($vendor))
			return false;

		$address = new stdClass();
		$address->address_user_id = 0;
		$address->address_published = 0;
		$address->address_default = 0;

		$query = 'SELECT field_namekey FROM ' . hikamarket::table('shop.field'). ' WHERE field_table = ' . $this->db->Quote('address');
		$this->db->setQuery($query);
		$tableStruct = $this->db->loadColumn();

		$search = array();
		foreach($tableStruct as $field) {
			$vendorField = 'vendor_' . $field;
			if(isset($vendor->$vendorField)) {
				$address->$field = $vendor->$vendorField;
				$search[] = $field . ' = ' . $this->db->Quote($vendor->$vendorField);
			} else {
				$search[] = '(' . $field . ' IS NULL OR ' . $field . ' = \'\')';
			}
		}

		$query = 'SELECT address_id FROM ' . hikamarket::table('shop.address') . ' WHERE address_user_id = 0 AND address_published = 0 AND address_default = 0 AND ' . implode(' AND ', $search);
		$this->db->setQuery($query, 0, 1);
		$status = (int)$this->db->loadResult();

		if(empty($status)) {
			$address->address_user_id = (int)$vendor->vendor_admin_id;
			$addressClass = hikamarket::get('shop.class.address');
			$status = $addressClass->save($address, 1);
		}

		return $status;
	}

	public function onAfterUserCreate(&$user, $manual = false) {
		$config = hikamarket::config();
		$auto_registration = $config->get('allow_registration', 0);

		if($auto_registration < 3)
			return false;

		if(empty($user->user_cms_id))
			return false;

		$old = null;
		$fieldClass = hikamarket::get('shop.class.field');
		$userClass = hikamarket::get('shop.class.user');

		$fullUser = $userClass->get($user->user_cms_id, 'cms');

		$user_group_filter = $config->get('auto_registration_group', 'all');
		if(!empty($user_group_filter) && $user_group_filter != 'all') {
			jimport('joomla.access.access');
			$shopConfig = hikamarket::config(false);
			$userGroups = JAccess::getGroupsByUser($fullUser->user_cms_id, (bool)$shopConfig->get('inherit_parent_group_access'));

			$user_group_filter = explode(',', $user_group_filter);
			hikamarket::toInteger($user_group_filter);
			hikamarket::toInteger($userGroups);
			if(count(array_intersect($userGroups, $user_group_filter)) == 0)
				return false;
		}

		return $this->createVendor($user, $fullUser, $manual);
	}

	public function onAfterUserUpdate($user) {
		return;
	}

	public function onAfterStoreUser($user, $isnew, $success, $msg, $oldUser) {
		$config = hikamarket::config();
		$auto_registration = (int)$config->get('allow_registration', 0);

		if(empty($user['id']))
			return;

		$vendor = $this->get($user['id'], 'cms');
		if(empty($vendor) && $auto_registration < 3)
			return;

		$shouldHaveVendor = ((int)$user['block'] == 0);

		$groups = @$user['groups'];
		if(empty($groups)) {
			jimport('joomla.access.access');
			$shopConfig = hikamarket::config(false);
			$groups = JAccess::getGroupsByUser($user['id'], (bool)$shopConfig->get('inherit_parent_group_access'));
		}

		$user_group_filter = $config->get('auto_registration_group', 'all');
		if($auto_registration == 3 && $shouldHaveVendor && !empty($user_group_filter) && $user_group_filter != 'all') {
			$user_group_filter = explode(',', $user_group_filter);
			$userGroups = $groups;
			hikamarket::toInteger($user_group_filter);
			hikamarket::toInteger($userGroups);
			$shouldHaveVendor = (count(array_intersect($userGroups, $user_group_filter)) > 0);
		}

		if(empty($vendor) && $shouldHaveVendor) {
			$userClass = hikamarket::get('shop.class.user');
			$fullUser = $userClass->get($user['id'], 'cms');
			if(!empty($fullUser))
				$this->createVendor($user, $fullUser, true);

			return;
		}

		if(empty($vendor) || (int)$vendor->vendor_admin_id != (int)$vendor->user_id)
			return;

		$do = false;
		$vendor_update = new stdClass();
		$vendor_update->vendor_id = $vendor->vendor_id;

		if($vendor->vendor_published != $shouldHaveVendor && $auto_registration > 1) {
			$vendor_update->vendor_published = $shouldHaveVendor;
			$do = true;
		}

		if($config->get('link_admin_groups', 0)) {
			$vendor_access = ',' . $vendor->vendor_access . ',';
			$vendor_access = preg_replace('#,\@[0-9]+,#iU', ',', $vendor_access);

			$accesses = array('@0');
			if(!empty($user['groups'])) {
				$accesses = array();
				foreach($user['groups'] as $group) {
					$accesses[] = '@' . $group;
				}
			}
			$vendor_access = implode(',', $accesses) . ',' . trim($vendor_access, ',');

			$do = true;
			$vendor_update->vendor_access = trim($vendor_access, ',');
		}

		if($do)
			$this->save($vendor_update);
	}

	public function onLoginUser($user_id, $user, $groups) {
		$u = array_merge($user, array());
		if(empty($u['id']))
			$u['id'] = $user_id;
		$u['groups'] = $groups;
		$this->onAfterStoreUser($user, false, true, '', null);
	}


	private function createVendor($user, $fullUser, $manual = false) {
		if(!$manual) {
			$fieldClass = hikamarket::get('shop.class.field');
			$registerData = $fieldClass->getInput('register', $old, false);
			$userData = $fieldClass->getFilteredInput('user', $old, false);
			$addressData = $fieldClass->getFilteredInput('address', $old, false);
		}

		if(empty($registerData) && !$manual)
			return false;

		$vendor = new stdClass();
		$vendor->vendor_name = $fullUser->name;
		$vendor->vendor_admin_id = $fullUser->user_id;
		$vendor->vendor_email = $fullUser->email;
		$vendor->vendor_currency_id = hikamarket::getCurrency();
		$vendor->vendor_params = new stdClass();

		$config = hikamarket::config();
		if($config->get('register_paypal_required', 0))
			$vendor->vendor_params->paypal_email = $fullUser->email;

		$joomlaUser = JFactory::getUser($fullUser->user_cms_id);
		$vendor->vendor_published = ($joomlaUser->block == 0);

		$query = 'SELECT * FROM '.hikamarket::table('shop.field').' WHERE field_table = \'plg.hikamarket.vendor\' AND field_frontcomp = 1 AND field_published = 1 ORDER BY field_ordering';
		$this->db->setQuery($query);
		$vendorFields = $this->db->loadObjectList();
		if(!empty($vendorFields)) {
			foreach($vendorFields as $vendorField) {
				$namekey = $vendorField->field_namekey;
				if(substr($namekey, 0, 7) == 'vendor_') {
					$name = substr($namekey, 7);
					if(isset($addressData) && isset($addressData->$name))
						$vendor->$namekey = $addressData->$name;
				}
			}
		}
		$ret = $this->save($vendor);
		if($ret === false)
			return false;
		return $vendor;
	}

	private function assignUsersToVendor($vendor, $users, $acls) {
		$app = JFactory::getApplication();
		$user = hikamarket::loadUser(false);

		$query = 'DELETE FROM '.hikamarket::table('vendor_user').' WHERE vendor_id = '.(int)$vendor->vendor_id;
		if(!hikamarket::isAdmin())
			$query .= ' AND user_id != '.$user;
		if(!empty($users))
			$query .= ' AND user_id NOT IN ('.implode(',', $users).')';
		$this->db->setQuery($query);
		$this->db->execute();

		if(empty($users))
			return;

		$query = 'INSERT IGNORE INTO '.hikamarket::table('vendor_user').' (vendor_id, user_id, user_access) '.
			'SELECT '.(int)$vendor->vendor_id.', u.user_id, '.$this->db->Quote('').' FROM '.hikamarket::table('shop.user').' as u WHERE u.user_id IN ('.implode(',', $users).')';
		$this->db->setQuery($query);
		$this->db->execute();

		if(empty($acls))
			return;

		foreach($acls as $acl => $u) {
			$query = 'UPDATE '.hikamarket::table('vendor_user').' SET user_access = ' . $this->db->Quote($acl) . ' WHERE user_id IN ('.implode(',',$u).') AND (vendor_id = '.$vendor->vendor_id.')';
			$this->db->setQuery($query);
			$this->db->execute();
		}
	}

	public function pay($id, $transactions = array(), $filters = null) {
		$app = JFactory::getApplication();
		$config = hikamarket::config();
		$shopConfig = hikamarket::config(false);
		$shopOrderClass = hikamarket::get('shop.class.order');
		$currencyClass = hikamarket::get('shop.class.currency');
		$userClass = hikamarket::get('shop.class.user');
		$paymentMethodType = hikamarket::get('type.paymentmethods');

		$valid_statuses = explode(',', $config->get('valid_order_statuses', 'confirmed,shipped'));
		foreach($valid_statuses as &$status) {
			$status = $this->db->Quote(trim($status));
		}
		$createdStatus = $shopConfig->get('order_created_status', 'created');
		$confirmedStatus = $shopConfig->get('order_confirmed_status', 'confirmed');

		$vendor_ids = $id;
		if(!is_array($vendor_ids))
			$vendor_ids = array($vendor_ids);

		$ret = array();
		foreach($vendor_ids as $vendor_id) {
			$vendor = $this->get((int)$vendor_id);
			if(empty($vendor))
				continue;

			$sql_filters = array(
				't.vendor_id = '. (int)$vendor_id,
				't.order_transaction_paid = 0',
				't.order_transaction_valid > 0',
			);
			if(!empty($transactions)) {
				hikamarket::toInteger($orders);
				$sql_filters[] = 't.order_transaction_id IN (' . implode(',', $transactions) . ')';
			}
			if(!empty($filters)) {
				if(!empty($filters['start'])) {
					$parts = explode(' ', $filters['start']);
					$parts = explode('-', $parts[0]);
					if(count($parts) == 3) {
						$start = hikamarket::getTime(mktime(0, 0, 0, $parts[1], $parts[2], $parts[0]));
						$sql_filters[] = 't.order_transaction_created >= ' . (int)$start;
					}
				}
				if(!empty($filters['end'])) {
					$parts = explode(' ', $filters['end']);
					$parts = explode('-', $parts[0]);
					if(count($parts) == 3) {
						$end = hikamarket::getTime(mktime(0, 0, 0, $parts[1], $parts[2], $parts[0]));
						$sql_filters[] = 't.order_transaction_created <= ' . (int)$end;
					}
				}
			}

			$query = 'SELECT t.*, o.order_id as `vendor_order_id`, o.order_number, o.order_payment_price, o.order_shipping_price, o.order_discount_price, o.order_discount_price, o.order_discount_tax, o.order_vendor_params, o.order_payment_params '.
				' FROM '.hikamarket::table('order_transaction').' AS t '.
				' LEFT JOIN '.hikamarket::table('shop.order').' AS o ON t.order_id = o.order_parent_id AND t.vendor_id = o.order_vendor_id '.
				' WHERE (' . implode(') AND (', $sql_filters) . ')';
			$this->db->setQuery($query);
			$pay_transactions = $this->db->loadObjectList('order_transaction_id');

			if(empty($pay_transactions))
				continue;

			$transaction_ids = array_keys($pay_transactions);
			hikamarket::toInteger($transaction_ids);

			$order_ids = array();
			$order_currencies = array();
			foreach($pay_transactions as &$transaction) {
				$transaction->order_payment_params = is_string($transaction->order_payment_params) ? hikamarket::unserialize($transaction->order_payment_params) : $transaction->order_payment_params;
				$transaction->order_vendor_params = is_string($transaction->order_vendor_params) ? hikamarket::unserialize($transaction->order_vendor_params) : $transaction->order_vendor_params;

				$order_currencies[ (int)$transaction->vendor_order_id ] = (int)$transaction->order_transaction_currency_id;

				if(isset($transaction->order_payment_params->market_mode) && $transaction->order_payment_params->market_mode === false)
					continue;

				if((int)$transaction->order_transaction_valid == 1)
					$order_ids[ (int)$transaction->vendor_order_id ] = (int)$transaction->vendor_order_id;
			}
			unset($transaction);

			$order = new stdClass();
			$order->order_parent_id = 0;
			$order->order_partner_id = 0;
			$order->order_partner_price = 0.0;
			$order->order_discount_price = 0.0;
			$order->order_discount_tax = 0.0;
			$order->order_shipping_price = 0.0;
			$order->order_shipping_tax = 0.0;
			$order->order_payment_price = 0.0;
			$order->order_payment_tax = 0.0;
			$order->order_status = $createdStatus;
			$order->order_vendor_id = $vendor->vendor_id;
			$order->order_payment_id = 0;

			$order->order_billing_address_id = $this->getAddressId($vendor);
			$order->order_shipping_address_id = 0;

			$order->history = new stdClass();
			$order->history->history_reason = JText::_('ORDER_CREATED');
			$order->history->history_notified = 0;
			$order->history->history_type = 'creation';

			$order->order_user_id = $vendor->vendor_admin_id;
			$order->order_currency_id = $vendor->vendor_currency_id;

			$order->cart = new stdClass();
			$order->cart->products = array();

			$pay_total = 0.0;
			foreach($pay_transactions as $t) {
				if($vendor->vendor_currency_id == $t->order_transaction_currency_id)
					$pay_total += hikamarket::toFloat( $t->order_transaction_price );
				else
					$pay_total += (float)$currencyClass->convertUniquePrice((float)hikamarket::toFloat($order->order_transaction_price), (int)$t->order_transaction_currency_id, (int)$vendor->vendor_currency_id);
			}

			$feeMode = ($pay_total >= 0.0) ? true : false;

			if($feeMode) {
				$vendorPayOrderType = 'vendorpayment';

				$payment_method = hikaInput::get()->getInt('payment_method', 0);
				$payment_method_name = $paymentMethodType->get($vendor->vendor_id, $payment_method);

				$order->order_payment_id = $payment_method;
				$order->order_payment_method = $payment_method_name;
			} else {
				$vendorPayOrderType = 'sale';

				$order->history->history_notified = 1;

				if(empty($order->hikamarket))
					$order->hikamarket = new stdClass();
				$order->hikamarket->do_not_process = true;
			}

			$order->order_type = $vendorPayOrderType;

			$mul = 1;
			if(!$feeMode)
				$mul = -1;

			$vendorPayContent = $config->get('vendor_pay_content', 'orders');

			if($vendorPayContent == 'products') {
				$order_products = array();
				$db_order_products = array();

				if(!empty($order_ids)) {
					$query = 'SELECT * FROM '.hikamarket::table('shop.order_product').' AS order_product WHERE order_product.order_id IN ('.implode(',', $order_ids).')';
					$this->db->setQuery($query);
					$db_order_products = $this->db->loadObjectList();
				}
				foreach($db_order_products as $op) {
					$key = $op->product_id;
					if(empty($key))
						$key = 'code_' . $op->order_product_code;

					if($op->order_product_code == 'order additional' && (float)hikamarket::toFloat($op->order_product_vendor_price) == 0.0)
						continue;

					if((int)$vendor->vendor_currency_id != $order_currencies[ (int)$op->order_id ])
						$op->order_product_vendor_price = $currencyClass->convertUniquePrice((float)hikamarket::toFloat($op->order_product_vendor_price), $order_currencies[ (int)$op->order_id ], (int)$vendor->vendor_currency_id);

					if(!isset($order_products[ $key ])) {
						$order_products[$key] = array(
							'name' => $op->order_product_name,
							'code' => $op->order_product_code,
							'qty' => (int)$op->order_product_quantity,
							'price' => ((float)hikamarket::toFloat($op->order_product_vendor_price) * (int)$op->order_product_quantity)
						);
					} else {
						$order_products[$key]['price'] += (float)hikamarket::toFloat($op->order_product_vendor_price) * (int)$op->order_product_quantity;
						$order_products[$key]['qty'] += (int)$op->order_product_quantity;
					}
				}
				unset($db_order_products);

				foreach($order_products as $key => $op) {
					$p = new stdClass();
					$p->product_id = 0;
					if(is_int($key))
						$p->product_id = $key;
					if(empty($op['qty']))
						$op['qty'] = 1;
					$p->order_product_name = $op['name'];
					$p->order_product_code = $op['code'];
					$p->order_product_quantity = (int)$op['qty'];
					$p->order_product_price = (float)((float)$op['price'] / (int)$op['qty']);
					$p->order_product_tax = 0;
					$p->order_product_options = '';
					$p->cart_product_id = 0;
					$order->cart->products[] = $p;
				}

				foreach($pay_transactions as $transaction) {
					if((int)$transaction->order_transaction_valid == 1 && (!isset($transaction->order_payment_params->market_mode) || $transaction->order_payment_params->market_mode != false))
						continue;

					$p = new stdClass();
					$p->product_id = 0;
					$number = $transaction->order_id;
					if(!empty($transaction->order_number))
						$number = $transaction->order_number;

					$p->order_product_price = (float)hikamarket::toFloat($transaction->order_transaction_price) * $mul;
					if($vendor->vendor_currency_id != $transaction->order_transaction_currency_id)
						$p->order_product_price = $currencyClass->convertUniquePrice((float)hikamarket::toFloat($p->order_product_price), (int)$transaction->order_transaction_currency_id, (int)$vendor->vendor_currency_id);

					$p->order_product_quantity = 1;
					$p->order_product_tax = 0.0;
					$p->order_product_options = '';
					$p->cart_product_id = 0;

					if((int)$transaction->order_transaction_valid == 2 && $p->order_product_price < 0.0) {
						$p->order_product_name = JText::sprintf('HIKAM_VENDOR_PAY_REFUND', $number);
						$p->order_product_code = 'vendor_refund_'.$transaction->order_transaction_id;
					} elseif((int)$transaction->order_transaction_valid == 2) {
						$p->order_product_name = JText::sprintf('HIKAM_VENDOR_PAY_ADJUST', $number);
						$p->order_product_code = 'vendor_adjust_'.$transaction->order_transaction_id;
					} else {
						$p->order_product_name = JText::sprintf('HIKAM_VENDOR_PAY_FEE', $number);
						$p->order_product_code = 'vendor_fee_'.$transaction->order_transaction_id;
					}

					$order->cart->products[] = $p;
				}

				$additionals = array(
					'payment' => 0.0,
					'shipping' => 0.0,
					'discount' => 0.0,
					'fees' => 0.0
				);
				$config_tax = $config->get('calculate_vendor_price_with_tax', false);

				foreach($pay_transactions as $t) {
					if((int)$transaction->order_transaction_valid == 2)
						continue;

					if(!empty($t->order_vendor_params->fees->shipping)) {
						$additionals['shipping'] += $t->order_vendor_params->fees->shipping;
					} else if(!empty($t->order_shipping_price)) {
						$additionals['shipping'] += (float)hikamarket::toFloat($t->order_shipping_price);
					}

			 		if(!empty($t->order_payment_price))
						$additionals['payment'] += (float)hikamarket::toFloat($t->order_payment_price);
					if(!empty($t->order_discount_price))
						$additionals['discount'] -= (float)hikamarket::toFloat($t->order_discount_price);
					if($config_tax) {
						if(!empty($order->order_shipping_tax))
							$additionals['shipping'] += (float)hikamarket::toFloat($order->order_discount_price);
						if(!empty($order->order_discount_tax))
							$additionals['discount'] -= (float)hikamarket::toFloat($order->order_discount_tax);
					}
					if(!empty($t->order_vendor_params->fees->fixed)) {
						foreach($order_vendor_params->fees->fixed as $fixedFee) {
							$additionals['fees'] -= (float)hikamarket::toFloat($fixedFee);
						}
					}
				}
				foreach($additionals as $k => $v) {
					if(empty($v) || $v == 0.0)
						continue;
					$p = new stdClass();
					$p->product_id = 0;
					$p->cart_product_id = 0;
					$p->order_product_name = JText::_('HIKAM_VENDOR_PAY_'.strtoupper($k));
					$p->order_product_code = 'vendor_'.$k;
					$p->order_product_price = $v;
					$p->order_product_quantity = 1;
					$p->order_product_tax = 0.0;
					$p->order_product_options = '';
					$order->cart->products[] = $p;
				}
			} else {
				foreach($pay_transactions as $t) {
					$product = new stdClass();
					$product->product_id = 0;
					$product->cart_product_id = 0;

					$product->order_product_name = JText::sprintf('VENDOR_TRANSACTION_PAYMENT', $t->order_transaction_id, $currencyClass->format($t->order_transaction_price, $t->order_transaction_currency_id));
					$product->order_product_code = '#' . $t->order_transaction_id;

					$product->order_product_price = (float)hikamarket::toFloat($t->order_transaction_price) * $mul;
					if($vendor->vendor_currency_id != $t->order_transaction_currency_id)
						$product->order_product_price = $currencyClass->convertUniquePrice($t->order_transaction_price * $mul, $t->order_transaction_currency_id, $vendor->vendor_currency_id);

					$product->order_product_quantity = 1;
					$product->order_product_tax = 0.0;
					$product->order_product_options = '';
					$order->cart->products[] = $product;
				}
			}

			$shopOrderClass->recalculateFullPrice($order, $order->cart->products);


			JPluginHelper::importPlugin('hikamarket');
			$do = true;
			$app->triggerEvent('onBeforeVendorPay', array(&$order, &$vendor, &$order_ids, &$pay_transactions, &$do));
			if(!$do || empty($order->order_full_price)) {
				$status = false;
				continue;
			}

			$shopOrderClass->sendEmailAfterOrderCreation = !$feeMode;
			$status = $shopOrderClass->save($order);

			$ret[$vendor->vendor_id] = $status;

			if($status) {
				if(empty($order->hikamarket))
					$order->hikamarket = new stdClass();
				$order->hikamarket->send_email = $feeMode;

				$app->triggerEvent('onAfterVendorPay', array(&$order, &$vendor, &$order_ids, &$pay_transactions));

				if($order->hikamarket->send_email) {
					$mailClass = hikamarket::get('class.mail');
					$mailClass->sendVendorPaymentEmail($order, $vendor);
				}

				$query = 'UPDATE ' . hikamarket::table('order_transaction') . ' SET order_transaction_paid = ' . $status . ' WHERE order_transaction_id IN ('.$status.','.implode(',',$transaction_ids).')';
				$this->db->setQuery($query);
				$this->db->execute();
			}
		}

		if(!is_array($id))
			return $status;
		return $ret;
	}

	public function getUnpaidOrders($vendor) {
		if(is_object($vendor))
			$vendor_id = (int)$vendor->vendor_id;
		else
			$vendor_id = (int)$vendor;

		if(empty($vendor_id) || $vendor_id <= 1)
			return false;

		static $unpaidOrdersCache = array();

		if(isset($unpaidOrdersCache[$vendor_id]))
			return $unpaidOrdersCache[$vendor_id];

		$config = hikamarket::config();
		$shopConfig = hikamarket::config(false);

		$valid_statuses = explode(',', $config->get('valid_order_statuses', 'confirmed,shipped'));
		foreach($valid_statuses as &$status) {
			$status = $this->db->Quote(trim($status));
		}
		unset($status);

		$createdStatus = $shopConfig->get('order_created_status', 'created');
		$confirmedStatus = $shopConfig->get('order_confirmed_status', 'confirmed');
		$vendorrefund_quoted = $this->db->Quote('vendorrefund');

		$min_date = 0;
		if((int)$config->get('days_payment_request', 0) > 0) {
			$joomlaConfig = JFactory::getConfig();
			if(!HIKASHOP_J30)
				$timeoffset = ((int)$joomlaConfig->getValue('config.offset'))*3600;
			else
				$timeoffset  = ((int)$joomlaConfig->get('offset'))*3600;

			$now = explode('-', date('m-d-Y'), 3);
			$days = (int)$config->get('days_payment_request', 0) - 1;
			$min_date = mktime(0, 0, 0, $now[0], $now[1], $now[2]) - ($days * 24 * 3600) + $timeoffset;
		}

		$q = array(
			'select' => array(
				'value' => ' SUM(t.order_transaction_price) AS value',
				'count' => 'COUNT(t.order_transaction_paid) AS count',
				'vendor' => 't.vendor_id AS vendor_id',
				'currency' => 't.order_transaction_currency_id as currency'
			),
			'tables' => hikamarket::table('order_transaction').' AS t ',
			'filters' => array(
				'vendor_id' => 't.vendor_id = ' . $vendor_id,
				'transaction_paid' => 't.order_transaction_paid = 0',
				'transaction_valid' => 't.order_transaction_valid > 0',
				'transaction_price' => 't.order_transaction_price != 0.0',
				'transaction_created' => 't.order_transaction_created < ' . $min_date,
			),
			'group' => array('t.vendor_id', 't.order_transaction_currency_id')
		);

		if($min_date <= 0)
			unset($q['filters']['transaction_created']);

		$select = $q['select'];
		if(is_array($select))
			$select = implode(', ', $select);

		$tables = $q['tables'];
		if(is_array($tables))
			$tables = implode(' ', $tables);

		$query = 'SELECT ' . $select . ' FROM ' . $tables;

		if(!empty($q['filters'])) {
			$query .= ' WHERE (' . implode(') AND (', $q['filters']) . ') ';
		}
		if(!empty($q['group']))
			$query .= ' GROUP BY ' . (is_array($q['group']) ? implode(',', $q['group']) : $q['group']);
		if(!empty($q['order']))
			$query .= ' ORDER BY ' . (is_array($q['order']) ? implode(',', $q['order']) : $q['order']);

		$this->db->setQuery($query);
		$unpaidOrdersCache[$vendor_id] = $this->db->loadObjectList();

		return $unpaidOrdersCache[$vendor_id];
	}


	public function getList($filters = array(), $sort = 'id') {
		static $vendorList = array();
		$key = 'default';
		if(!empty($filters) && $sort != 'vendor_id')
			$key = md5(serialize($filters).$sort);
		if(isset($vendorList[$key]))
			return $vendorList[$key];

		$sqlFilters = array('vendor.vendor_published = 1');
		foreach($filters as $filter) {

		}

		$sqlSort = 'vendor.vendor_id';
		if($sort == 'name')
			$sqlSort = 'vendor.vendor_name';

		$query = 'SELECT vendor.vendor_id, vendor.vendor_name '.
			' FROM ' . hikamarket::table('vendor') . ' AS vendor '.
			' WHERE ('.implode(') AND (', $sqlFilters).') '.
			' ORDER BY '.$sqlSort;
		$this->db->setQuery($query);
		$vendorList[$key] = $this->db->loadObjectList('vendor_id');
		foreach($vendorList[$key] as &$vendor) {
			$vendor = $vendor->vendor_name;
		}
		unset($vendor);

		return $vendorList[$key];
	}
}
