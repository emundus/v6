<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(!defined('DS'))
	define('DS',DIRECTORY_SEPARATOR);
jimport('joomla.plugin.plugin');

class plgSystemHikashopaffiliate extends JPlugin {
	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
		if(isset($this->params))
			return;

		$plugin = JPluginHelper::getPlugin('system', 'hikashopaffiliate');
		$this->params = new JRegistry(@$plugin->params);
	}

	public function afterInitialise() {
		return $this->onAfterInitialise();
	}

	public function onAfterInitialise() {
		$do = $this->params->get('after_init','1');
		if($do)
			return $this->onAfterRoute();
		return true;
	}

	public function afterRoute() {
		return $this->onAfterRoute();
	}

	public function onAfterRoute() {
		$app = JFactory::getApplication();

		if(version_compare(JVERSION,'4.0','>=') && $app->isClient('administrator'))
			return true;
		if(version_compare(JVERSION,'4.0','<') && $app->isAdmin())
			return true;
		if(@$_REQUEST['option'] == 'com_gcalendar')
			return true;

		$key_name = $this->params->get('partner_key_name', 'partner_id');
		if(version_compare(JVERSION,'3.0','>='))
			$partner_id = $app->input->getCmd($key_name, 0);
		else
			$partner_id = JRequest::getCmd($key_name, 0);

		if(empty($partner_id))
			return true;

		static $done = false;
		if($done)
			return true;
		$done = true;

		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php'))
			return true;
		$partner_id = hikashop_decode($partner_id,'partner');

		$userClass = hikashop_get('class.user');
		$user = $userClass->get($partner_id);

		if(empty($user->user_partner_activated))
			return true;

		$config = hikashop_config();
		$cookie = true;
		if($config->get('no_affiliation_if_cart_present')) {
			$cart_id = $app->getUserState(HIKASHOP_COMPONENT.'.cart_id', 0, 'int');
			if($cart_id)
				$cookie = false;
		}
		$affiliation_exclude_domains = $config->get('affiliation_exclude_domains', '');
		if(!empty($affiliation_exclude_domains)) {
			$referer = null;
			if(!empty($_SERVER['HTTP_REFERER']) && preg_match('#^https?://.*#i',$_SERVER['HTTP_REFERER']))
				$referer = str_replace(array('"', '<', '>', "'"), '', @$_SERVER['HTTP_REFERER']);
			if(!empty($referer)) {
				$exclude_referers = explode(',', $affiliation_exclude_domains);
				foreach($exclude_referers as $ref) {
					$ref = trim($ref);
					if(strpos($referer, $ref) !== false)
						$cookie = false;
				}
			}
		}
		if($cookie)
			setcookie('hikashop_affiliate', hikashop_encode($partner_id,'partner'), time() + $config->get('click_validity_period', 2592000), '/');

		$ip = hikashop_getIP();
		$clickClass = hikashop_get('class.click');
		$latest = $clickClass->getLatest($partner_id, $ip, $config->get('click_min_delay', 86400));

		if(empty($user->user_params->user_custom_fee)) {
			$user->user_params->partner_click_fee = $config->get('partner_click_fee',0);
			$user->user_params->partner_fee_currency = $config->get('partner_currency',1);
		} else {
			$user->user_params->partner_click_fee = $user->user_params->user_partner_click_fee;
		}

		if(!$config->get('allow_currency_selection',0) || empty($user->user_currency_id))
			$user->user_currency_id =  $config->get('partner_currency',1);

		if(bccomp(sprintf('%F',$user->user_params->partner_click_fee),0,5) && $user->user_currency_id!=$user->user_params->partner_fee_currency)
			$user->user_params->partner_click_fee = $this->_convert($user->user_params->partner_click_fee,$user->user_params->partner_fee_currency,$user->user_currency_id);

		if(!empty($latest))
			$user->user_params->partner_click_fee = 0;


		if(bccomp(sprintf('%F',$user->user_params->partner_click_fee),0,5) && $config->get('provide_points_instead_of_fees',0)) {
			$plugin = hikashop_import('hikashop', 'userpoints');
			$ids = array();
			$plugin->listPlugins($plugin->name, $ids, false, $partner_id);
			foreach($ids as $id) {
				$plugin->pluginParams($id);
				$points = round($user->user_params->partner_click_fee);
				$order = new stdClass();
				$order->order_user_id = $partner_id;
				$data = JTex::_('PARTNER_CLICK_FEE').' ( '.JText::_('HKASHOP_USER_ID').': '.$partner_id.' )';
				if($plugin->addPoints($points, $order, $data, null)) {
					$user->user_params->partner_click_fee = 0;
					break;
				}
			}
		}

		$click = new stdClass();
		$click->click_partner_id = $partner_id;
		$click->click_ip = $ip;
		$click->click_partner_price = $user->user_params->partner_click_fee;
		$click->click_partner_currency_id = $user->user_currency_id;
		$clickClass->save($click);

		return true;
	}

	public function onBeforeOrderUpdate(&$order,&$do){
		if(!empty($order->order_type) && $order->order_type != 'sale')
			return;
		if(!empty($order->old->order_type) && $order->old->order_type != 'sale')
			return;

		if(!empty($order->order_partner_paid))
			return true;

		if(!isset($order->order_full_price))
			return true;

		if(!empty($order->old)) {
			if(!empty($order->old->order_partner_paid))
				return true;

			if(floatval($order->old->order_full_price) == floatval($order->order_full_price))
				return true;

			if(empty($order->order_partner_id))
				$order->order_partner_id = $order->old->order_partner_id;

			return $this->onBeforeOrderCreate($order, $do);
		}

		return true;
	}

	public function getPartner(&$order) {
		$config =& hikashop_config();
		if($config->get('add_partner_to_user_account', 0) && !empty($order->order_user_id)) {
			$class = hikashop_get('class.user');
			$user = $class->get($order->order_user_id);
			if(!empty($user->user_partner_id))
				return $user->user_partner_id;
		}
		return hikashop_decode(hikaInput::get()->cookie->getCmd('hikashop_affiliate', 0), 'partner');
	}

	public function onBeforeOrderCreate(&$order, &$do) {
		$app = JFactory::getApplication();
		if(!empty($order->order_type) && $order->order_type != 'sale')
			return;

		if(empty($order->order_partner_id)) {
			if(hikashop_isClient('administrator'))
				return true;

			if(!empty($order->order_discount_code)) {
				$discountClass = hikashop_get('class.discount');
				$coupon = $discountClass->load($order->order_discount_code);

				if(isset($coupon->discount_affiliate) && $coupon->discount_affiliate == -1) {
					return true;
				} elseif(isset($coupon->discount_affiliate) && $coupon->discount_affiliate) {
					$partner_id = $coupon->discount_affiliate;
					$userClass = hikashop_get('class.user');
					$user = $userClass->get($order->order_user_id);
					if($user->user_cms_id) {
						$this->addPartnerToUser($user->user_cms_id, $partner_id);
					}
				} else {
					$partner_id = $this->getPartner($order);
				}
			} else {
				$partner_id = $this->getPartner($order);
			}

			if(empty($partner_id))
				return true;

		} else {
			$partner_id = $order->order_partner_id;
		}

		$config =& hikashop_config();
		if($config->get('no_self_affiliation', 0) && $order->order_user_id == $partner_id)
			return true;

		$userClass = hikashop_get('class.user');
		$user = $userClass->get($partner_id);

		if(empty($user))
			return true;

		if(empty($user->user_partner_activated))
			return true;

		$order->order_partner_id = $partner_id;

		if(empty($user->user_params->user_custom_fee)) {
			$user->user_params->partner_percent_fee = $config->get('partner_percent_fee',0);
			$user->user_params->partner_flat_fee = $config->get('partner_flat_fee',0);
			$user->user_params->partner_fee_currency = $config->get('partner_currency',1);
		} else {
			$user->user_params->partner_percent_fee = $user->user_params->user_partner_percent_fee;
			$user->user_params->partner_flat_fee =$user->user_params->user_partner_flat_fee;
		}

		if(!$config->get('allow_currency_selection',0) || empty($user->user_currency_id))
			$user->user_currency_id =  $config->get('partner_currency',1);

		if(bccomp(sprintf('%F',$user->user_params->partner_flat_fee),0,5) && $user->user_currency_id!=$user->user_params->partner_fee_currency)
			$user->user_params->partner_flat_fee = $this->_convert($user->user_params->partner_flat_fee,$user->user_params->partner_fee_currency,$user->user_currency_id);

		if(bccomp(sprintf('%F',$user->user_params->partner_percent_fee), 0, 5) || bccomp(sprintf('%F',$user->user_params->partner_flat_fee), 0, 5)) {
			if(bccomp(sprintf('%F',$user->user_params->partner_percent_fee), 0, 5)) {
				$order_price = $order->order_full_price;
				if($config->get('affiliate_fee_exclude_shipping', 0)) {
					$order_price = $order_price - $order->order_shipping_price;
				}
				$fees = $order_price*$user->user_params->partner_percent_fee/100;
			} else {
				$fees = 0;
			}

			if($order->order_currency_id!=$user->user_currency_id)
				$fees = $this->_convert($fees,$order->order_currency_id,$user->user_currency_id);

			$order->order_partner_price = $fees + $user->user_params->partner_flat_fee;
			$order->order_partner_currency_id = $user->user_currency_id;


			if(bccomp(sprintf('%F',$order->order_partner_price),0,5) && $config->get('provide_points_instead_of_fees',0)) {
				$plugin = hikashop_import('hikashop', 'userpoints');
				$ids = array();
				$plugin->listPlugins($plugin->name, $ids, false, $partner_id);
				foreach($ids as $id) {
					$plugin->pluginParams($id);
					$points = round($order->order_partner_price);
					$pointsOrder = new stdClass();
					$pointsOrder->order_user_id = $partner_id;
					$data = JTex::_('PARTNER_CLICK_FEE').' ( '.JText::_('HKASHOP_USER_ID').': '.$partner_id.' )';
					if($plugin->addPoints($points, $pointsOrder, $data, null)) {
						$order->order_partner_price = 0;
						break;
					}
				}
			}
		}

		return true;
	}

	protected function _convert($amount,$src_id,$dst_id) {
		$currencyClass = hikashop_get('class.currency');
		$config =& hikashop_config();
		$setcurrencies = null;
		$main_currency = (int)$config->get('main_currency',1);
		$ids[$src_id] = $src_id;
		$ids[$dst_id] = $dst_id;
		$ids[$main_currency] = $main_currency;
		$currencies = $currencyClass->getCurrencies($ids,$setcurrencies);
		$srcCurrency = $currencies[$src_id];
		$dstCurrency = $currencies[$dst_id];
		$mainCurrency =  $currencies[$main_currency];

		if($srcCurrency->currency_id != $mainCurrency->currency_id) {
			$amount = floatval($amount) / floatval($srcCurrency->currency_rate);
			$amount += $amount * floatval($srcCurrency->currency_percent_fee) / 100.0;
		}

		if($dstCurrency->currency_id != $mainCurrency->currency_id) {
			$amount = floatval($amount) * floatval($dstCurrency->currency_rate);
			$amount += $amount * floatval($dstCurrency->currency_percent_fee)/100.0;
		}
		return $amount;
	}

	public function onUserAfterSave($user, $isnew, $success, $msg) {
		return $this->onAfterStoreUser($user, $isnew, $success, $msg);
	}

	public function onAfterStoreUser($user, $isnew, $success, $msg){
		if($success === false)
			return false;

		$app = JFactory::getApplication();

		$admin = false;
		if(version_compare(JVERSION,'4.0','>=') && $app->isClient('administrator'))
			$admin = true;
		if(version_compare(JVERSION,'4.0','<') && $app->isAdmin())
			$admin = true;
		if($admin || !$isnew)
			return true;

		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php'))
			return true;

		$partner_id = hikaInput::get()->cookie->getCmd('hikashop_affiliate', 0);
		if(empty($partner_id))
			return true;

		$partner_id = hikashop_decode($partner_id,'partner');

		$this->addPartnerToUser($user['id'], $partner_id);

		return true;
	}

	public function onBeforeHikaUserRegistration(&$ret, $input_data, $mode) {
		$config = hikashop_config();
		$formData = hikaInput::get()->get('data', array(), 'array');
		if($config->get('affiliate_registration', 0) && !empty($formData['affiliate'])) {
			$ret['userData']->user_partner_activated = 1;
			$ret['registerData']->user_partner_activated = 1;
		}
	}

	protected function addPartnerToUser($user_id, $partner_id){
		$userClass = hikashop_get('class.user');
		$partner = $userClass->get($partner_id);
		if(empty($partner->user_partner_activated))
			return true;

		$config = hikashop_config();
		if(empty($partner->user_params->user_custom_fee)) {
			$partner->user_params->partner_lead_fee = $config->get('partner_lead_fee',0);
			$partner->user_params->partner_fee_currency = $config->get('partner_currency',1);
		} else {
			$partner->user_params->partner_lead_fee = $partner->user_params->user_partner_lead_fee;
		}

		if(!$config->get('allow_currency_selection',0) || empty($partner->user_currency_id))
			$partner->user_currency_id = $config->get('partner_currency',1);

		if(bccomp(sprintf('%F',$partner->user_params->partner_lead_fee),0,5) && $partner->user_currency_id!=$partner->user_params->partner_fee_currency)
			$partner->user_params->partner_lead_fee = $this->_convert($partner->user_params->partner_lead_fee,$partner->user_params->partner_fee_currency,$partner->user_currency_id);

		$ip = hikashop_getIP();
		$clickClass = hikashop_get('class.click');
		$latest = $clickClass->getLatest($partner_id,$ip,$config->get('lead_min_delay',24));

		if($config->get('add_partner_to_user_account',0) || (empty($latest) && bccomp(sprintf('%F',$partner->user_params->partner_lead_fee),0,5))) {

			$userDataInDb = $userClass->get($user_id,'cms');

			if(!empty($userDataInDb->user_id)&& bccomp(sprintf('%F',$partner->user_params->partner_lead_fee),0,5) && $config->get('provide_points_instead_of_fees',0)) {
				$plugin = hikashop_import('hikashop', 'userpoints');
				$ids = array();
				$plugin->listPlugins($plugin->name, $ids, false, $userDataInDb->user_id);
				foreach($ids as $id) {
					$plugin->pluginParams($id);
					$points = round($partner->user_params->partner_lead_fee);
					$order = new stdClass();
					$order->order_user_id = $userDataInDb->user_id;
					$data = JTex::_('PARTNER_LEAD_FEE').' ( '.JText::_('HKASHOP_USER_ID').': '.$userDataInDb->user_id.' )';
					if($plugin->addPoints($points, $order, $data, null)) {
						$partner->user_params->partner_lead_fee = 0;
						break;
					}
				}
			}

			$userData = new stdClass();
			$userData->user_id = @$userDataInDb->user_id;
			$userData->user_cms_id = $user_id;
			$userData->user_partner_id = $partner_id;
			$userData->user_partner_price = @$partner->user_params->partner_lead_fee;
			$userData->user_partner_currency_id = $partner->user_currency_id;
			$userClass->save($userData);
		}
	}

	public function onUserAccountDisplay(&$buttons) {
		$button = $this->params->get('button_on_control_panel','1');
		if(!$button)
			return;
		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid))
			$url_itemid = '&Itemid=' . $Itemid;

		$buttons['affiliate'] = array(
			'link' => hikashop_completeLink('affiliate'.$url_itemid),
			'level' => 1,
			'image' => 'affiliate',
			'text' => JText::_('AFFILIATE'),
			'description' => JText::_('AFFILIATE_PROGRAM'),
			'fontawesome' => ''.
				'<i class="fas fa-user fa-stack-2x"></i>'.
				'<i class="fas fa-circle fa-stack-1x fa-inverse" style="top:30%;left:30%;"></i>'.
				'<i class="fas fa-dollar-sign fa-stack-1x" style="top:30%;left:30%;"></i>'
		);
		return true;
	}

	public function onBeforeOrderListing($paramBase, &$extrafilters, &$pageInfo, &$filters) {
		$app = JFactory::getApplication();
		if(!hikashop_isClient('administrator'))
			return;
		$pageInfo->filter->filter_partner = $app->getUserStateFromRequest($paramBase.".filter_partner",'filter_partner','','int');
		$extrafilters['filter_partner'] =& $this;

		if(!empty($pageInfo->filter->filter_partner)) {
			if($pageInfo->filter->filter_partner == 1) {
				$filters[] = 'b.order_partner_id != 0';
			} else {
				$filters[] = 'b.order_partner_id = 0';
			}
		}
	}

	public function onAfterOrderListing(&$rows, &$extrafields, $pageInfo) {
		$app = JFactory::getApplication();
		if(!hikashop_isClient('administrator'))
			return;
		$myextrafield = new stdClass();
		$myextrafield->name = JText::_('PARTNER');
		$myextrafield->obj =& $this;
		$extrafields['partner'] = $myextrafield;
	}

	public function displayFilter($name, $filter) {
		$partner = hikashop_get('type.user_partner');
		return $partner->display('filter_partner', $filter->filter_partner, false);
	}

	public function showField($container, $name, &$row) {
		if(!bccomp(sprintf('%F',$row->order_partner_price), 0, 5))
			return '';
		$ret = $container->currencyHelper->format($row->order_partner_price,$row->order_partner_currency_id);
		if(empty($row->order_partner_paid)) {
			$ret .= JText::_('NOT_PAID');
		} else {
			$ret .= JText::_('PAID').'<img src="'.HIKASHOP_IMAGES.'ok.png" />';
		}
		return $ret;
	}

	public function onDiscountBlocksDisplay(&$discount, &$html) {
		$options = array(
			JHTML::_('select.option', -1, JText::_('NO_PARTNER')),
			JHTML::_('select.option', 0, JText::_('CURRENT_CUSTOMER_PARTNER'))
		);
		$db = JFactory::getDBO();
		$db->setQuery('SELECT a.user_id, b.name, b.username FROM #__hikashop_user AS a LEFT JOIN #__users AS b ON a.user_cms_id = b.id WHERE a.user_partner_activated = 1 ORDER BY b.username', 0, 250);
		$partners = $db->loadObjectList();
		if(!empty($partners)) {
			foreach($partners as $partner) {
				$options[] = JHTML::_('select.option', $partner->user_id, $partner->username.' ('.$partner->name.')');
			}
		}
		$html[] = '<dt data-discount-display="coupon"><label>'. JText::_('FORCE_AFFILIATION_TO') .'</label></dt><dd data-discount-display="coupon">'.
				JHTML::_('hikaselect.genericlist', $options, 'data[discount][discount_affiliate]' , 'class="custom-select"', 'value', 'text', @$discount->discount_affiliate ).
				'</dd>';
	}

	function onOrderStatusListingLoad( &$orderstatus_columns, &$rows){
		$orderstatus_columns['affiliate'] = array(
			'text' => JText::_('AFFILIATE'),
			'title' => JText::_('VALID_ORDER_STATUS'),
			'description' => JText::_('AFFILIATE_DESC'),
			'key' => 'partner_valid_status',
			'default' => 'confirmed,shipped',
			'type' => 'toggle',
			'trigger' => 'plg.system.hikashopaffiliate.affiliateStatusUpdate'
		);
	}

	function affiliateStatusUpdate(&$controller, $elementPkey, $value, $extra){
		return $controller->configstatus($elementPkey,$value, $extra);
	}
}
