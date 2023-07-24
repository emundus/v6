<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class addressViewAddress extends HikaShopView {
	var $triggerView = true;
	public function display($tpl = null, $params = null) {
		if(empty($params))
			$params = new HikaParameter('');
		$this->assignRef('params', $params);

		$function = $this->getLayout();
		if(method_exists($this, $function))
			$this->$function();
		parent::display($tpl);
	}

	public function address_template() {
		$this->chosen = false;
		if(!empty($this->params)) {
			$this->address = $this->params->get('address');
		}
	}

	public function listing() {
		$config = hikashop_config();
		$this->assignRef('config', $config);

		$user_id = hikashop_loadUser();
		$this->assignRef('user_id', $user_id);

		$this->loadRef(array(
			'addressClass' => 'class.address',
			'fieldClass' => 'class.field'
		));

		$addresses = array();
		$fields = null;

		if(!empty($user_id)) {
			$addresses = $this->addressClass->getByUser($user_id);
			if(!empty($addresses)) {
				$this->addressClass->loadZone($addresses);
				$fields =& $this->addressClass->fields;
			}
		}
		$this->assignRef('fields', $fields);

		if(!empty($fields) && count($fields)) {
			$billing_fields = array();
			$shipping_fields = array();
			foreach($fields as $k => $field) {
				if($field->field_address_type == 'billing') {
					$billing_fields[$k] = $field;
					continue;
				}
				if($field->field_address_type == 'shipping') {
					$shipping_fields[$k] = $field;
					continue;
				}
				if(empty($field->field_address_type)) {
					$billing_fields[$k] = $field;
					$shipping_fields[$k] = $field;
				}
			}
			$this->assignRef('billing_fields', $billing_fields);
			$this->assignRef('shipping_fields', $shipping_fields);
		}

		$this->assignRef('addresses', $addresses);

		$this->two_columns = true;
		$this->display_badge = false;
		foreach($addresses as $addr) {
			if(in_array($addr->address_type, array('', 'both'))) {
				$this->two_columns = false;
				$this->display_badge = true;
				break;
			}
		}

		$tmpl = hikaInput::get()->getCmd('tmpl', '');
		$this->ajax = (in_array($tmpl, array('ajax', 'raw', 'component')));
		if($this->ajax)
			$this->edit = false;


		global $Itemid;
		$this->Itemid = $Itemid;
		$this->url_itemid=(!empty($this->Itemid)?'&Itemid='.$this->Itemid:'');

		$app = JFactory::getApplication();
		$menus	= $app->getMenu();
		$menu	= $menus->getActive();
		$this->toolbar = array();
		$show_page_heading = true;
		$params = null;
		if(!empty($menu) && method_exists($menu, 'getParams')) {
			$params = $menu->getParams();
			$show_page_heading = $params->get('show_page_heading');
		}
		if(is_null($show_page_heading)) {
			$com_menus = JComponentHelper::getParams('com_menus');
			if(!empty($com_menus))
				$show_page_heading = $com_menus->get('show_page_heading');
		}
		if(!empty($menu) && method_exists($menu, 'getParams') && $menu->link == 'index.php?option=com_hikashop&view=address&layout=listing') {
			if($show_page_heading)
				$this->title = $params->get('page_heading');
			$title = $params->get('page_title');
			if(empty($title))
				$title = $menu->title;
			hikashop_setPageTitle($title);

			$robots = $params->get('robots');
			if (!$robots) {
				$jconfig = JFactory::getConfig();
				$robots = $jconfig->get('robots', '');
			}
			if($robots) {
				$doc = JFactory::getDocument();
				$doc->setMetadata('robots', $robots);
			}
		} else {
			if($show_page_heading)
				$this->title = JText::_('ADDRESSES');
			hikashop_setPageTitle('ADDRESSES');
			$pathway = $app->getPathway();
			$pathway->addItem(JText::_('ADDRESSES'), hikashop_completeLink('address&Itemid='.$Itemid));

			$this->toolbar = array(
				'back' => array(
					'icon' => 'back',
					'name' => JText::_('HIKA_BACK'),
					'url' => hikashop_completeLink('user&task=cpanel&Itemid='.$Itemid),
					'fa' => array('html' => '<i class="fas fa-arrow-circle-left"></i>')
				)
			);
		}

		$this->listing_legacy();
	}

	protected function listing_legacy() {
		$this->fieldsClass =& $this->fieldClass;
		$this->popup =& $this->popupHelper;

		$this->loadRef(array(
			'popupHelper' => 'helper.popup'
		));

		$this->type = 'user';
		$this->use_popup = (int)$this->config->get('user_address_legacy_popup', 0);
		$this->address_selector = (int)$this->config->get('user_address_selector', 0);

		global $Itemid;

		if(!empty($this->address_selector)) {
			$this->toolbar['new'] = array(
				'icon' => 'new',
				'name' => JText::_('HIKA_NEW'),
				'javascript' => 'return window.localPage.newAddr(this, \''.$this->type.'\');',
				'fa' => array('html' => '<i class="fas fa-plus-circle"></i>')
			);
		} elseif($this->use_popup) {
			$this->toolbar['new'] = array(
				'icon' => 'new',
				'name' => JText::_('HIKA_NEW'),
				'url' => hikashop_completeLink('address&task=add&Itemid='.$Itemid, true),
				'popup' => array(
					'id' => 'hikashop_new_address_popup',
					'width' => 760,
					'height' => 480
				),
				'fa' => array('html' => '<i class="fas fa-plus-circle"></i>')
			);
		}
	}

	public function show() {
		$app = JFactory::getApplication();
		$this->assignRef('app', $app);

		$config = hikashop_config();
		$this->assignRef('config', $config);

		$user_id = hikashop_loadUser();

		$this->loadRef(array(
			'fieldsClass' => 'class.field',
			'addressClass' => 'class.address',
		));

		if(!empty($this->params->type)) {
			$type = $this->params->type;
		} else {
			$type = hikaInput::get()->getCmd('address_type', '');
			if(empty($type)) {

				$data = hikaInput::get()->get('data', array(), 'array');
				if(!empty($data['address']['address_type']))
					$type = $data['address']['address_type'];
				if(empty($type))
					$type = hikaInput::get()->getCmd('subtask', 'billing');
			}
			if(substr($type, -8) == '_address')
				$type = substr($type, 0, -8);
		}
		if(!in_array($type, array('billing','shipping')))
			$type = 'billing';
		$this->assignRef('type', $type);

		$address_id = !empty($this->params->address_id) ? (int)$this->params->address_id : hikashop_getCID();
		$this->assignRef('address_id', $address_id);

		$fieldset_id = !empty($this->params->fieldset_id) ? $this->params->fieldset_id : hikaInput::get()->getVar('fid', '');
		$this->assignRef('fieldset_id', $fieldset_id);

		$edit = (hikaInput::get()->getVar('edition', false) === true);
		if(isset($this->params->edit))
			$edit = $this->params->edit;
		$this->assignRef('edit', $edit);

		if(!empty($address_id)) {
			$address = $this->addressClass->get($address_id);
			if($address->address_user_id != $user_id) {
				$address = new stdClass();
				$address->address_id = 0;
				$address->address_type = $type;
				$address_id = 0;
			}
			if(!$edit) {
				$addresses = array(&$address);
				$this->addressClass->loadZone($addresses);
			}
		} else {
			if(isset($_SESSION['hikashop_'.$type.'_address_data']))
				$address = @$_SESSION['hikashop_'.$type.'_address_data'];
			elseif(isset($_SESSION['hikashop_address_data']) && isset($_SESSION['hikashop_address_data']->address_type) && $_SESSION['hikashop_address_data']->address_type == $type)
				$address = @$_SESSION['hikashop_address_data'];

			if(!empty($address) && (!empty($address->address_id) || (!empty($address->address_type) && $address->address_type != $type)))
				unset($address);

			if(empty($address)) {
				$address = new stdClass();
				$address->address_id = 0;
				$address->address_type = $type;
				$userCMS = JFactory::getUser();
				if(!$userCMS->guest) {
					$name = $userCMS->get('name');
					$pos = strpos($name, ' ');
					if($pos !== false) {
						$address->address_firstname = substr($name, 0, $pos);
						$name = substr($name, $pos + 1);
					}
					$address->address_lastname = $name;
				}
			}
		}
		$this->assignRef('address', $address);

		$userAddresses = $this->addressClass->loadUserAddresses($user_id);
		$this->assignRef('addresses', $userAddresses);
		$this->two_columns = true;
		$this->display_badge = false;
		foreach($userAddresses as $addr) {
			if(in_array($addr->address_type, array('', 'both'))) {
				$this->two_columns = false;
				$this->display_badge = true;
				break;
			}
		}
		$tmpl = hikaInput::get()->getCmd('tmpl', '');
		$this->ajax = (in_array($tmpl, array('ajax', 'raw', 'component')));

		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid))
			$url_itemid = '&Itemid='.$Itemid;
		$this->assignRef('url_itemid', $url_itemid);

		$extraFields = array(
			'address' => $this->fieldsClass->getFields('frontcomp' ,$address, $type . '_address', 'checkout&task=state'.$url_itemid)
		);
		$this->assignRef('fields', $extraFields['address']);

		$this->fieldsClass->prepareFields($extraFields['address'], $address, 'address', 'checkout&task=state');
		$init_js = $this->fieldsClass->jsToggle($extraFields['address'], $address, 0, '', array('return_data' => true));
		$this->assignRef('init_js', $init_js);

		if(!isset($this->params->address_id) || $tmpl != 'component')
			return;

		static $jsInit = array();
		if(empty($jsInit[$type])) {
			$jsInit[$type] = array();
			$null = array();
			$this->fieldsClass->addJS($null,$null,$null);

			foreach($extraFields['address'] as &$p) {
				$p->field_table = $type.'_address';
			}
			unset($p);
			$this->fieldsClass->jsToggle($extraFields['address'], $address, 0);
		}

		if(empty($jsInit[$type][$edit])) {
			if($edit) {
				$parents = $this->fieldsClass->getParents($extraFields['address']);
				if(!empty($parents)) {
					$p = reset($parents);
					$p->type = $type.'_address';
				} else {
					$p = new stdClass();
					$p->type = $type.'_address';
					$parents = array($p);
				}
				$init_js = $this->fieldsClass->initJSToggle($parents, $address, 0);
			} else {
				$requiredFields = array();
				$validMessages = array();
				$values = array('address' => $address);
				$this->fieldsClass->checkFieldsForJS($extraFields, $requiredFields, $validMessages, $values);
				$this->fieldsClass->addJS($requiredFields, $validMessages, array('address'));
			}
		}
		$jsInit[$type][$edit] = true;
	}

	public function form() {
		$user_id = hikashop_loadUser();
		$this->assignRef('user_id', $user_id);

		$address_id = hikashop_getCID('address_id');

		$tmpl = hikaInput::get()->getString('tmpl', '');
		$this->assignRef('tmpl', $tmpl);

		$address = hikaInput::get()->getVar('fail');
		if(empty($address)) {
			$address = new stdClass();
			if(!empty($address_id)) {
				$addressClass = hikashop_get('class.address');
				$address = $addressClass->get($address_id);
				if($address->address_user_id != $user_id) {
					$address = new stdClass();
					$address_id = 0;
				}
			} else {
				$userCMS = JFactory::getUser();
				if(!$userCMS->guest){
					$name = $userCMS->get('name');
					$pos = strpos($name,' ');
					if($pos!==false){
						$address->address_firstname = substr($name,0,$pos);
						$name = substr($name,$pos+1);
					}
					$address->address_lastname = $name;
				}
			}
		}

		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass', $fieldsClass);
		$fieldsClass->skipAddressName = true;

		global $Itemid;
		$url_itemid = '';
		if(!empty($Itemid))
			$url_itemid = '&Itemid='.$Itemid;

		$extraFields = array(
			'address' => $fieldsClass->getFields('frontcomp', $address, 'address', 'checkout&task=state'.$url_itemid)
		);
		$this->assignRef('extraFields',$extraFields);

		$null = array();
		$fieldsClass->addJS($null,$null,$null);
		$fieldsClass->jsToggle($this->extraFields['address'], $address, 0);

		$this->assignRef('address', $address);

		$module = hikashop_get('helper.module');
		$module->initialize($this);

		$requiredFields = array();
		$validMessages = array();
		$values = array('address' => $address);
		$fieldsClass->checkFieldsForJS($extraFields, $requiredFields, $validMessages, $values);
		$fieldsClass->addJS($requiredFields, $validMessages, array('address'));

		$cart = hikashop_get('helper.cart');
		$this->assignRef('cart', $cart);

		$this->toolbar = array();
		$this->toolbar['save'] = array(
			'icon' => 'save',
			'name' => JText::_('HIKA_SAVE'),
			'javascript' => 'return window.localPage.saveAddr(this);',
			'fa' => array(
				'html' => '<i class="far fa-save"></i>',
				'size' => 3
			)
		);
		$this->toolbar['back'] = array(
			'icon' => 'back',
			'name' => JText::_('HIKA_BACK'),
			'url' => hikashop_completeLink('address&task=listing'.$url_itemid),
			'fa' => array('html' => '<i class="fas fa-arrow-circle-left"></i>')
		);
	}

	public function select() {
		$config = hikashop_config();
		$this->assignRef('config', $config);

		$fieldClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass', $fieldClass);
		$this->assignRef('fieldClass', $fieldClass);

		$this->fields = $this->params->fields;
		$this->addresses = $this->params->addresses;

		if(isset($this->params->type))
			$this->type = $this->params->type;
		else
			$this->type = 'user';

		if(isset($this->params->show_new_btn))
			$this->show_new_btn = $this->params->show_new_btn;
		else
			$this->show_new_btn = true;

		if(isset($this->params->address_selector) && is_int($this->params->address_selector)) {
			$this->address_selector = (int)$this->params->address_selector;
		} else {
			$this->address_selector = (int)$config->get('user_address_selector', 0);
		}

		$this->fieldset_id = 'hikashop_'.$this->type.'_address_zone';
	}
}
