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
class plgHikashopVendorpoints extends hikashopPlugin {
	var $multiple = true;
	var $name = 'vendorpoints';

	protected function init() {
		static $init = null;
		if($init !== null)
			return $init;

		$init = defined('HIKAMARKET_COMPONENT');
		if(!$init) {
			$filename = rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikamarket'.DS.'helpers'.DS.'helper.php';
			if(file_exists($filename)) {
				include_once($filename);
				$init = defined('HIKAMARKET_COMPONENT');
			}
		}
		return $init;
	}

	public function getAUP($warning = false) {
		static $aup = null;
		if(!isset($aup)) {
			$aup = false;
			$api_AUP = JPATH_SITE.DS.'components'.DS.'com_alphauserpoints'.DS.'helper.php';
			if(file_exists($api_AUP)) {
				require_once ($api_AUP);
				if(class_exists('AlphaUserPointsHelper'))
					$aup = true;
			}
			$api_AUP = JPATH_SITE.DS.'components'.DS.'com_altauserpoints'.DS.'helper.php';
			if(!$aup && file_exists($api_AUP)) {
				require_once ($api_AUP);
				if(class_exists('AltaUserPointsHelper')) {
					$aup = true;
					if(!class_exists('AlphaUserPointsHelper'))
						require_once dirname(__FILE__).DS.'vendorpoints_aup_compat.php';
				}
			}
			if(!$aup && $warning) {
				$app = JFactory::getApplication();
				if(hikashop_isClient('administrator'))
					$app->enqueueMessage('The HikaShop UserPoints plugin requires the component AlphaUserPoints to be installed. If you want to use it, please install the component or use another mode.');
			}
		}
		return $aup;
	}

	public function getEasysocial($warning = false) {
		static $foundry = null;

		if($foundry !== null)
			return $foundry;

		$file = JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php';
		jimport('joomla.filesystem.file');
		$foundry = JFile::exists($file);

		if($foundry) {
			include_once($file);
		} else if($warning) {
			$app = JFactory::getApplication();
			if(hikashop_isClient('administrator'))
				$app->enqueueMessage('The HikaShop UserPoints plugin requires the component EasySocial to be installed. If you want to use it, please install the component or use another mode.');
		}

		return $foundry;
	}

	public function onPluginConfiguration(&$element) {
		$this->pluginConfiguration($element);

		if(!$this->init())
			return false;

		$current = $this->db->getTableColumns(hikamarket::table('shop.user'));

		if(!isset($current['user_points'])) {
			$query = 'ALTER TABLE `'.hikamarket::table('shop.user').'` ADD COLUMN `user_points` TEXT NOT NULL DEFAULT \'\'';
			$this->db->setQuery($query);
			try {
				$this->db->execute();
			}catch(Exception $e) { }

			$query = 'INSERT INTO `' . hikamarket::table('shop.field') . '` '. <<<EOD
(`field_table`, `field_realname`, `field_namekey`, `field_type`, `field_value`, `field_published`, `field_ordering`, `field_options`, `field_core`, `field_required`, `field_backend`, `field_frontcomp`, `field_default`, `field_backend_listing`) VALUES
('user', 'Points', 'user_points', 'text', '', 1, 1, 'a:5:{s:12:"errormessage";s:0:"";s:4:"cols";s:0:"";s:4:"rows";s:0:"";s:4:"size";s:0:"";s:6:"format";s:0:"";}', 1, 0, 1, 0, '', 0)
EOD;
			$this->db->setQuery($query);
			$this->db->execute();
		}

		$shopConfig = hikamarket::config(false);
		$this->main_currency = $shopConfig->get('main_currency',1);
		$currency = hikamarket::get('shop.class.currency');
		$this->currency = $currency->get($this->main_currency);

		$this->modes = array();
		if($this->getAUP())
			$this->modes[] = JHTML::_('select.option', 'aup', JText::_('ALPHA_USER_POINTS'));
		if($this->getEasysocial(false))
			$this->modes[] = JHTML::_('select.option', 'esp', JText::_('EASYSOCIAL_POINTS'));
		$this->modes[] = JHTML::_('select.option', 'hk', JText::_('HIKASHOP_USER_POINTS'));

		$this->joomlaAclType = hikamarket::get('type.joomla_acl');
	}

	public function onBeforeOrderCreate(&$order, &$do) {
		if( !empty($order->order_type) && $order->order_type != 'subsale' )
			return true;

		if(empty($order->order_payment_params))
			$order->order_payment_params = new stdClass();
		if(empty($order->order_payment_params->vendorpoints))
			$order->order_payment_params->vendorpoints = new stdClass();
		if(empty($order->order_payment_params->vendorpoints->earn_points))
			$order->order_payment_params->vendorpoints->earn_points = array();

		$earnPoints = array();
		$this->onGetUserPointsEarned($order, $earnPoints, 'all');

		if(!empty($earnPoints)) {
			foreach($earnPoints as $mode => $pts) {
				if(empty($order->order_payment_params->vendorpoints->earn_points[$mode]))
					$order->order_payment_params->vendorpoints->earn_points[$mode] = 0;
				$order->order_payment_params->vendorpoints->earn_points[$mode] += $pts;
			}
		}
	}

	public function onAfterOrderCreate(&$order, &$send_email) {
		$app = JFactory::getApplication();
		if(hikashop_isClient('administrator'))
			return true;
		if(!isset($order->order_status))
			return true;

		if( !empty($order->order_type) && $order->order_type != 'subsale' )
			return true;
		if( !empty($order->vendorpoints_process->updated) )
			return true;
		$this->giveAndGiveBack($order);
		unset($this->fullOrder);
		return true;
	}

	public function onAfterOrderUpdate(&$order, &$send_email) {
		if(!isset($order->order_status))
			return true;

		if( (!empty($order->order_type) && $order->order_type != 'subsale') || $order->old->order_type != 'subsale' )
			return true;
		if( !empty($order->vendorpoints_process->updated) )
			return true;

		$this->giveAndGiveBack($order);
		unset($this->fullOrder);
		return true;
	}

	public function onGetUserPoints($cms_user_id = null, $mode = 'all') {
		$points = $this->getUserPoints($cms_user_id, $mode);
	}

	public function onGetUserPointsEarned(&$order, &$points, $mode = 'all') {
		$ids = array();

		if(!$this->init())
			return;

		$where = array(
			$this->type.'_type = ' . $this->db->Quote($this->name),
			$this->type.'_published = 1'
		);
		hikamarket::addVendorACLFilters($where, $this->type.'_access', '', 1, false, (int)$order->order_vendor_id);
		$query = 'SELECT '.$this->type.'_id as id, '.$this->type.'_name as name FROM '.hikashop_table($this->type).' WHERE ('.implode(') AND (', $where).') ORDER BY '.$this->type.'_ordering';
		$this->db->setQuery($query);
		$plugins = $this->db->loadObjectList();

		foreach($plugins as $plugin) {
			$ids[] = $plugin->id;
		}

		foreach($ids as $id) {
			parent::pluginParams($id);
			if($mode == 'all' || @$this->plugin_params->points_mode == $mode) {
				$pts = $this->getPointsEarned($order);

				if(empty($pts))
					continue;

				$myMode = $this->plugin_params->points_mode;
				if($mode == 'all') {
					if(empty($points[$myMode]))
						$points[$myMode] = 0;
					$points[$myMode] += $pts;
				} else
					$points += $pts;
			}
		}
	}

	public function getDataReference(&$order) {
		if(!empty($order->order_number)) {
			$number = $order->order_number;
		} elseif(!empty($order->old->order_number)) {
			$number = $order->old->order_number;
		} elseif(!empty($order->order_id)) {
			$class = hikashop_get('class.order');
			$data = $class->get($order->order_id);
			$number = $data->order_number;
		} else {
			return '';
		}
		return  JText::_('ORDER_NUMBER').' : '.$number;
	}

	public function getUserPoints($cms_user_id = null, $mode = 'all') {
		$ret = 0;
		if($mode == 'all')
			$ret = array();

		if($mode == 'aup' || ($mode == 'all' && $this->getAUP())) {
			if($mode == 'aup' && !$this->getAUP(true))
				return false;
			if($cms_user_id === null) {
				$user = Jfactory::getUser();
				$cms_user_id = $user->id;
			}
			$userInfo = AlphaUserPointsHelper::getUserInfo('', $cms_user_id);
			if($mode == 'aup')
				return (int)$userInfo->points;
			$ret['aup'] = (int)$userInfo->points;
		}

		if($mode == 'esp' || ($mode == 'all' && $this->getEasysocial())) {
			if($mode == 'esp' && !$this->getEasysocial(true))
				return false;
			if($cms_user_id === null) {
				$user = Jfactory::getUser();
				$cms_user_id = $user->id;
			}
			$userInfo = FD::user( $cms_user_id );
			if($mode == 'esp')
				return (int)$userInfo->getPoints();
			$ret['esp'] = (int)$userInfo->getPoints();
		}

		if($cms_user_id === null) {
			$user = hikashop_loadUser(true);
		} else {
			$userClass = hikashop_get('class.user');
			$user = $userClass->get($cms_user_id, 'cms');
		}

		if($mode == 'hk') {
			if(isset($user->user_points) || ($user != null && in_array('user_points', array_keys(get_object_vars($user)))))
				return (int)@$user->user_points;
			return false;
		}
		if(isset($user->user_points) || ($user != null && in_array('user_points', array_keys(get_object_vars($user))))) {
			if($mode == 'all')
				$ret['hk'] = (int)@$user->user_points;
			else
				$ret = (int)@$user->user_points;
		}
		return $ret;
	}

	public function addPoints($points, $order, $data = null, $forceMode = null) {
		if(empty($this->plugin_params) && $forceMode === null)
			return false;

		if($points === 0)
			return true;

		if(!$this->init())
			return false;

		$points_mode = @$this->plugin_params->points_mode;
		if($forceMode !== null)
			$points_mode = $forceMode;

		if($points_mode == 'aup') {
			if(empty($order->vendor_admin)) {
				$db = JFactory::getDBO();
				$query = 'SELECT hu.* FROM ' . hikamarket::table('vendor') . ' AS v '.
					' INNER JOIN ' . hikamarket::table('shop.user') . ' AS hu ON v.vendor_admin_id = hu.user_id '.
					' WHERE v.vendor_id = ' . (int)$order->order_vendor_id;
				$db->setQuery($query);
				$order->vendor_admin = $db->loadObject();
			}
			if($this->getAUP(true)) {
				if($data === null)
					$data = $this->getDataReference($order);
				$aupid = AlphaUserPointsHelper::getAnyUserReferreID($order->vendor_admin->user_cms_id);
				AlphaUserPointsHelper::newpoints('plgaup_orderValidation', $aupid, '', $data, $points);
				return true;
			}
			return false;
		}

		if($points_mode == 'esp') {
			if(empty($order->vendor_admin)) {
				$db = JFactory::getDBO();
				$query = 'SELECT hu.* FROM ' . hikamarket::table('vendor') . ' AS v '.
					' INNER JOIN ' . hikamarket::table('shop.user') . ' AS hu ON v.vendor_admin_id = hu.user_id '.
					' WHERE v.vendor_id = ' . (int)$order->order_vendor_id;
				$db->setQuery($query);
				$order->vendor_admin = $db->loadObject();
			}
			if($this->getEasysocial(true)) {
				if($data === null)
					$data = $this->getDataReference($order, $points_mode);
				$eas_points = FD::points();
				$userInfo = FD::user( $order->vendor_admin->user_cms_id );
				return $eas_points->assignCustom( $userInfo->id, $points, $data );
			}
			return false;
		}

		$ret = true;
		if(empty($order->vendor)) {
			$vendorClass = hikamarket::get('class.vendor');
			$order->vendor = $vendorClass->get($order->order_vendor_id);
		}
		$userClass = hikashop_get('class.user');
		$oldUser = $userClass->get((int)$order->vendor->vendor_admin_id);
		if(!isset($oldUser->user_points))
			return false;
		if(empty($oldUser->user_points))
			$oldUser->user_points = 0;

		$user = new stdClass();
		$user->user_id = $oldUser->user_id;
		$user->user_points = (int)$oldUser->user_points + $points;
		if($user->user_points < 0) {
			$points = -$oldUser->user_points;
			$user->user_points = 0;
			$ret = false;
		}
		$userClass->save($user);
		return $ret;
	}

	public function getPointsEarned($order) {
		if(empty($this->plugin_params))
			return 0;

		$points = 0;
		$db = JFactory::getDBO();

		$config =& hikashop_config();
		$this->main_currency = $config->get('main_currency',1);
		$currencyClass = hikashop_get('class.currency');
		$this->currency = $currencyClass->get($this->main_currency);

		if(isset($order->order_currency_id)) {
			$order_currency_id = $order->order_currency_id;
		} else {
			$order_currency_id = hikashop_getCurrency();
		}
		if($this->main_currency != $order_currency_id) {
			if(!empty($this->plugin_params->value))
				$this->plugin_params->value = $currencyClass->convertUniquePrice($this->plugin_params->value, $this->main_currency, $order_currency_id);
			else
				$this->plugin_params->value = 0;

			if(!empty($this->plugin_params->minimumcost))
				$this->plugin_params->minimumcost = $currencyClass->convertUniquePrice($this->plugin_params->minimumcost, $this->main_currency, $order_currency_id);
			else
				$this->plugin_params->minimumcost = 0;

			if(!empty($this->plugin_params->currency_rate))
				$this->plugin_params->currency_rate = $currencyClass->convertUniquePrice($this->plugin_params->currency_rate, $this->main_currency, $order_currency_id);
			else
				$this->plugin_params->currency_rate = 0;
		}

		$categories = array();
		if(!empty($this->plugin_params->categories))
			$categories = unserialize($this->plugin_params->categories);
		if(!empty($categories)) {
			if(!empty($order->cart->products)) {
				$products =& $order->cart->products;
			} else {
				$products =& $order->products;
			}
			foreach($products as $product) {
				$ids[$product->product_id] = $product->product_id;
			}
			$queryP = 'SELECT product_parent_id FROM '.hikashop_table('product').' WHERE product_id IN ('.implode(',',$ids).')';
			$db->setQuery($queryP);
			$pids = $db->loadObjectList();
			if(!empty($pids)) {
				foreach($pids as $pid) {
					$ids[$pid->product_parent_id] = $pid->product_parent_id;
				}
			}

			$query = 'SELECT * FROM '.hikashop_table('product_category').' prod LEFT JOIN '.hikashop_table('category').' cat ON prod.category_id=cat.category_id ' .
					'WHERE prod.product_id IN ('.implode(',',$ids).')';
			$db->setQuery($query);
			$idcats = $db->loadObjectList();
			if(!empty($idcats)) {
				$query = 'SELECT * FROM '.hikashop_table('category').' WHERE category_type=\'product\' AND ';
				$conditions = array();
				foreach($idcats as $idcat) {
					$conditions[] = '(category_left <= '.$idcat->category_left.' AND category_right >= '.$idcat->category_right.')';
					$idfinalcats[$idcat->product_id][0][] = $idcat->category_id;
				}
				$query .= implode(' OR ',$conditions);
			}
			$db->setQuery($query);
			$idparentcats = $db->loadObjectList('category_id');
			foreach($idcats as $id) {
				$this->_makeLevel($idfinalcats[$id->product_id], 0, $idparentcats);
			}

			$maxPoints = 0;
			$tempCatId = null;
			foreach($idfinalcats as $product) {
				foreach($product as $levels) {
					foreach($categories as $category) {
						foreach($levels as $categoryid) {
							if($categoryid == $category->category_id && $category->category_points > $maxPoints) {
								$maxPoints = $category->category_points;
								$tempCatId = $category->category_id;
							}
						}
						$points += $maxPoints;
						if(!empty($tempCatId)) {
							foreach ($categories as $category) {
								if($category->category_id == $tempCatId && $this->plugin_params->limitecategory == 1) {
									$category->category_points = 0;
								}
							}
							$maxPoints = 0;
							$tempCatId = 0;
							break 2;
						}
						$maxPoints = 0;
					}
				}
			}
		}


		if($this->plugin_params->currency_rate != 0) {
			$points += $order->order_vendor_price / $this->plugin_params->currency_rate;
		}

		$products = array();
		if(!empty($order->cart->products)) {
			$products = &$order->cart->products;
		} elseif(!empty($order->products)) {
			$products = &$order->products;
		}

		if($this->plugin_params->limittype == 1) {
			foreach($products as $product) {
				$points += $this->plugin_params->productpoints;
			}
		} elseif($this->plugin_params->limittype == 0) {
			foreach($products as $product) {
				if(isset($product->order_product_quantity)) {
					$points += $this->plugin_params->productpoints * $product->order_product_quantity;
				} else {
					$points += $this->plugin_params->productpoints * $product->cart_product_quantity;
				}
			}
		}
		return round($points, 0);
	}

	public function loadFullOrder($order_id) {
		if(empty($this->fullOrder) || $this->fullOrder->order_id != $order_id) {
			$classOrder = hikashop_get('class.order');
			$this->fullOrder = $classOrder->loadFullOrder($order_id, false, false);
		}
	}

	public function giveAndGiveBack(&$order) {

		$this->config = hikashop_config();
		$confirmed = null;
		if(!isset($this->params)) {
			$pluginsClass = hikashop_get('class.plugins');
			$plugin = $pluginsClass->getByName('hikashop', 'vendorpoints');
			$confirmed = explode(',', @$plugin->params['order_status']);
		} else if($this->params->get('order_status', '') != '') {
			$confirmed = explode(',', $this->params->get('order_status', ''));
		}
		if(empty($confirmed))
			$confirmed = explode(',', $this->config->get('invoice_order_statuses'));
		if(empty($confirmed))
			$confirmed = array('confirmed','shipped');
		$created = $this->config->get('order_created_status');

		$points = array();

		$this->loadFullOrder($order->order_id);


		$creation = empty($order->old->order_status);
		$changed = !empty($order->old->order_status) && !empty($order->order_status) && $order->old->order_status != $order->order_status;
		$old_confirmed = !empty($order->old->order_status) && in_array($order->old->order_status, $confirmed);
		$old_created = !empty($order->old->order_status) && ($order->old->order_status == $created);
		$new_confirmed = !empty($order->order_status) && in_array($order->order_status, $confirmed);
		$new_created = !empty($order->order_status) && ($order->order_status == $created);

		if($changed && $old_confirmed && !$new_confirmed && !empty($this->fullOrder->order_payment_params->vendorpoints->earn_points)) {
			foreach($this->fullOrder->order_payment_params->vendorpoints->earn_points as $mode => $p) {
				if(empty($points[$mode]))
					$points[$mode] = 0;
				$points[$mode] -= $p;
			}
		}

		if(($creation || ($changed && !$old_confirmed)) && $new_confirmed && !empty($this->fullOrder->order_payment_params->vendorpoints->earn_points)) {
			foreach($this->fullOrder->order_payment_params->vendorpoints->earn_points as $mode => $p) {
				if(empty($points[$mode]))
					$points[$mode] = 0;
				$points[$mode] += $p;
			}
		}

		if(!empty($points)) {
			foreach($points as $mode => $p) {
				if(!empty($p))
					$this->addPoints($p, $this->fullOrder, null, $mode);
			}
		}
	}
}
