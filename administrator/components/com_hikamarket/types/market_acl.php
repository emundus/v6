<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikamarketMarket_aclType {

	protected $acl = null;
	protected $cpt = 0;

	protected function load() {
		$this->acl = array(
			'order' => array(
				'add',
				'edit' => array(
					'general',
					'coupon',
					'shipping',
					'payment',
					'customfields',
					'customer',
					'billingaddress',
					'shippingaddress',
					'products',
					'vendor',
					'notify',
					'mail',
					'plugin' => array()
				),
				'export',
				'listing',
				'show' => array(
					'general',
					'customer',
					'customfields',
					'billingaddress',
					'shippingaddress ',
					'history',
					'vendors',
					'invoice',
					'shippinginvoice',
					'plugin' => array()
				),
				'notify',
				'request',
				'payments'
			),
			'product' => array(
				'add',
				'edit' => array(
					'name',
					'code',
					'published',
					'description',
					'category',
					'quantity',
					'price' => array(
						'value',
						'tax',
						'currency',
						'quantity',
						'acl',
						'user',
						'date'
					),
					'msrp',
					'tax',
					'saledates',
					'qtyperorder',
					'customfields',
					'related',
					'options',
					'bundles',
					'weight',
					'volume',
					'manufacturer',
					'pagetitle',
					'url',
					'metadescription',
					'keywords',
					'canonical',
					'alias',
					'acl',
					'translations',
					'vendor',
					'files' => array(
						'name',
						'limit',
						'free',
						'description',
						'upload',
						'delete'
					),
					'images' => array(
						'name',
						'title',
						'upload',
						'link',
						'delete'
					),
					'tags',
					'warehouse',
					'characteristics',
					'variants',
					'plugin' => array()
				),
				'new' => array(
					'name',
					'code',
					'published',
					'description',
					'category',
					'quantity',
					'price' => array(
						'value',
						'tax',
						'currency',
						'quantity',
						'acl',
						'user',
						'date'
					),
					'msrp',
					'tax',
					'saledates',
					'qtyperorder',
					'customfields',
					'related',
					'options',
					'bundles',
					'weight',
					'volume',
					'manufacturer',
					'pagetitle',
					'url',
					'metadescription',
					'keywords',
					'canonical',
					'alias',
					'acl',
					'translations',
					'vendor',
					'files' => array(
						'name',
						'limit',
						'free',
						'description',
						'upload',
						'delete'
					),
					'images' => array(
						'name',
						'title',
						'upload',
						'link',
						'delete'
					),
					'tags',
					'warehouse',
					'characteristics',
					'variants',
					'plugin' => array()
				),
				'variant' => array(
					'name',
					'code',
					'characteristics',
					'published',
					'description',
					'quantity',
					'price' => array(
						'value',
						'tax',
						'currency',
						'quantity',
						'acl',
						'user',
						'date'
					),
					'saledates',
					'qtyperorder',
					'customfields',
					'weight',
					'volume',
					'acl',
					'translations',
					'vendor',
					'files' => array(
						'name',
						'limit',
						'free',
						'description',
						'upload',
						'delete'
					),
					'images' => array(
						'name',
						'upload',
						'delete'
					),
					'plugin' => array()
				),
				'copy',
				'delete',
				'selection',
				'listing',
				'show',
				'approve',
				'sort',
				'subvendor'
			),
			'characteristic' => array(
				'add',
				'edit' => array(
					'value',
					'alias',
					'vendor'
				),
				'delete',
				'listing',
				'show',
				'values' => array(
					'add',
					'edit' => array(
						'value',
						'vendor'
					),
					'delete',
					'ordering'
				)
			),
			'category' => array(
				'add',
				'edit' => array(
					'name',
					'published',
					'parent',
					'customfields',
					'description',
					'metadescription',
					'pagetitle',
					'keywords',
					'alias',
					'acl',
					'translations',
					'images' => array(
						'upload',
						'delete'
					),
					'plugin' => array()
				),
				'delete',
				'listing',
				'show'
			),
			'user' => array(
				'edit' => array(
					'email',
					'customfields',
					'address'
				),
				'listing',
				'show' => array(
					'general',
					'address'
				)
			),
			'vendor' => array(
				'edit' => array(
					'name',
					'currency',
					'description',
					'terms',
					'paypalemail',
					'image',
					'location',
					'fields',
					'users',
					'plugin' => array()
				),
				'statistics'
			),
			'discount' => array(
				'add',
				'edit' => array(
					'code',
					'type',
					'flatamount',
					'percentamount',
					'taxcategory',
					'usedtimes',
					'published',
					'dates',
					'minorder',
					'minproducts',
					'quota',
					'peruser',
					'product',
					'category',
					'categorychild',
					'zone',
					'targetvendor',
					'user',
					'autoload',
					'percenttoproduct',
					'nodoubling',
					'acl',
					'plugin' => array()
				),
				'delete',
				'listing',
				'show'
			),
			'paymentplugin' => array(
				'add',
				'edit' => array(
					'name',
					'description',
					'published',
					'specific',
				),
				'delete',
				'listing',
			),
			'shippingplugin' => array(
				'add',
				'edit' => array(
					'name',
					'description',
					'published',
					'specific',
				),
				'delete',
				'listing',
			),
		);

		$tagsHelper = hikamarket::get('shop.helper.tags');
		if(empty($tagsHelper) || !$tagsHelper->isCompatible()) {
			$k = array_search('tags', $this->acl['product']['edit']);
			if($k !== false)
				unset($this->acl['product']['edit'][$k]);
		}

		$paymentClass = hikamarket::get('class.payment');
		$fields = $paymentClass->loadConfigurationFields();
		foreach($fields['main'] as $k => $v) {
			$this->acl['paymentplugin']['edit'][] = str_replace(array('params.payment_', 'payment_', '_'), array('', '', '-'), $k);
		}
		if(!empty($fields['restriction'])) {
			$this->acl['paymentplugin']['edit']['restriction'] = array();
			foreach($fields['restriction'] as $k => $v) {
				$this->acl['paymentplugin']['edit']['restriction'][] = str_replace(array('params.payment_', 'payment_', '_'), array('', '', '-'), $k);
			}
		}

		$shippingClass = hikamarket::get('class.shipping');
		$fields = $shippingClass->loadConfigurationFields();
		foreach($fields['main'] as $k => $v) {
			$this->acl['shippingplugin']['edit'][] = str_replace(array('params.shipping_', 'shipping_', '_'), array('', '', '-'), $k);
		}
		if(!empty($fields['restriction'])) {
			$this->acl['shippingplugin']['edit']['restriction'] = array();
			foreach($fields['restriction'] as $k => $v) {
				$this->acl['shippingplugin']['edit']['restriction'][] = str_replace(array('params.shipping_', 'shipping_', '_'), array('', '', '-'), $k);
			}
		}

		$categories = array(
			'order' => array(),
			'product' => array(),
			'category' => array(),
			'vendor' => array(),
			'discount' => array(),
			'root' => array()
		);
		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikamarket');
		JFactory::getApplication()->triggerEvent('onMarketAclPluginListing', array(&$categories));

		foreach($categories as $name => $data) {
			if($name == 'root') {
				foreach($data as $k => $v) {
					if(!isset($this->acl[$k]))
						$this->acl[$k] = $v;
				}
				continue;
			}

			if(isset($this->acl[$name]) && isset($this->acl[$name]['edit']['plugin']) && !empty($data))
				$this->acl[$name]['edit']['plugin'] = array_merge($this->acl[$name]['edit']['plugin'], $data);

			if(empty($this->acl[$name]['edit']['plugin']))
				unset($this->acl[$name]['edit']['plugin']);

			if($name == 'order' && empty($this->acl[$name]['show']['plugin']))
				unset($this->acl[$name]['show']['plugin']);

			if($name == 'product') {
				if(isset($this->acl[$name]['variant']) && isset($this->acl[$name]['variant']['plugin']) && !empty($data))
					$this->acl[$name]['variant']['plugin'] = array_merge($this->acl[$name]['variant']['plugin'], $data);
				if(empty($this->acl[$name]['variant']['plugin']))
					unset($this->acl[$name]['variant']['plugin']);

				if(isset($this->acl[$name]) && isset($this->acl[$name]['new']['plugin']) && !empty($data))
					$this->acl[$name]['new']['plugin'] = array_merge($this->acl[$name]['new']['plugin'], $data);
				if(empty($this->acl[$name]['new']['plugin']))
					unset($this->acl[$name]['new']['plugin']);
			}
		}

		$this->cpt = 0;
		foreach($this->acl as $a) {
			$this->cpt++;
			if(is_array($a)) { foreach($a as $b) {
				$this->cpt++;
				if(is_array($b)) { foreach($b as $c) {
					$this->cpt++;
					if(is_array($c)) { foreach($c as $d) {
						$this->cpt++;
						if(is_array($d)) { $this->cpt += count($d); }
					}}
				}}
			}}
		}
	}

	public function getList() {
		if(empty($this->acl)) {
			$this->load();
		}
		return $this->acl;
	}

	public function compile($formData) {
		if(empty($this->acl)) {
			$this->load();
		}

		if(empty($formData))
			return '';

		$ret = array();
		$missing = false;

		foreach($this->acl as $groupKey => $groupAcls) {
			if(is_array($groupAcls)) {
				$subRet = array();
				$subRet = $this->subcompile($groupKey . '/', $formData, $groupAcls);

				if($subRet !== true && count($subRet) == 0 && in_array($groupKey, $formData))
					$subRet = true;

				if($subRet === true) {
					$ret[] = $groupKey;
				} else {
					$ret = array_merge($ret, $subRet);
					$missing = true;
				}
			} else {
				if(in_array($groupAcls, $formData))
					$ret[] = $groupAcls;
				else
					$missing = true;
			}
		}

		if(!$missing)
			return '*';
		sort($ret, SORT_STRING);
		return implode(',', $ret);
	}

	public function subcompile($root, &$formData, &$data) {
		$all = true;
		$ret = array();

		foreach($data as $key => $acls) {
			if(is_array($acls)) {
				$name = $root . $key;
				$subRet = $this->subcompile($name. '/', $formData, $acls);

				if($subRet !== true && count($subRet) == 0 && in_array($name, $formData))
					$subRet = true;

				if($subRet === true) {
					$ret[] = $name;
				} else {
					$all = false;
					$ret = array_merge($ret, $subRet);
				}
			} else {
				$name = $root.$acls;
				if(in_array($name, $formData))
					$ret[] = $name;
				else
					$all = false;
			}
		}
		if($all)
			return true;
		return $ret;
	}

	public function display($map, $value, $inherit = false) {
		hikamarket::loadJslib('otree');
		if(empty($this->acl)) {
			$this->load();
		}

		$id = str_replace(array('[',']',' '),'_',$map);
		$ret = '';
		$attribs = '';

		if($inherit !== false) {
			$ret .= '<label>' . JText::_('ACL_INHERIT') . '</label> ' . JHTML::_('hikaselect.booleanlist', $inherit, 'onchange="var el = document.getElementById(\''.$id.'_otree\');if(this.value==\'0\'){el.style.display=\'\'}else{el.style.display=\'none\'}"', empty($value));
			if(empty($value)) {
				$attribs = ' style="display:none;"';
				$value = '*';
			}
		}

		if($value != '*' && strpos($value, '/') === false) // ACL Compat
			$value = str_replace('_', '/', $value);

		$ret .= '<div id="'.$id.'_otree" class="oTree"'.$attribs.'></div>
<input type="hidden" name="'.$map.'" value="'.$value.'" id="'.$id.'_values"/>';
		$js = '
var options = {rootImg:"'.HIKAMARKET_IMAGES.'otree/", showLoading:false, useSelection:false, checkbox:"-", tricheckbox:true};';

		$accesses = explode(',', trim(strtolower($value), ','));
		sort($accesses, SORT_STRING);

		$js .= '
window.hikashop.ready(function(){
var data = ' . $this->getData($accesses) . ';
'.$id.' = new window.oTree("'.$id.'",options,null,data,true); data = null;
'.$id.'.callbackCheck = function(treeObj, id, value) {
	var v = treeObj.getChk(), e = document.getElementById("'.$id.'_values");
	if(v === false || v.length == 0) {
		e.value = "none";
	} else if( v.length > '.($this->cpt-1).') {
		e.value = "all";
	} else {
		e.value = v.join(",");
	}
};
});';
		JFactory::getDocument()->addScriptDeclaration($js);
		return $ret;
	}

	public function displayButton($map, $values) {
		hikamarket::loadJslib('otree');
		hikamarket::loadJslib('jquery');
		$ret = '';
		$js = '';
		if(empty($this->acl)) {
			$this->load();
		}
		$map = str_replace('"','',$map);

		if(empty($this->id)) {
			$this->id = 'hikamarket_marketacl';

			$js .= '
var data_'.$this->id.' = ' . $this->getData($values) . ';
'.$this->id.' = null;
if(!window.aclMgr)
	window.aclMgr = {};
window.aclMgr.updateMarketAcl = function(el,id) {
	var d = document, tree = d.getElementById("'.$this->id.'_otree"), e = d.getElementById(id), values = e.value;
	if(!tree) {
		tree = d.createElement("div");
		tree.id = "'.$this->id.'_otree";
		tree.style.position = "absolute";
		tree.style.display = "none";
		tree.className = "oTree acl-popup-content";
		d.body.appendChild(tree);
		'.$this->id.' = new window.oTree("'.$this->id.'",{rootImg:"'.HIKAMARKET_IMAGES.'otree/", showLoading:false, useSelection:false, checkbox:"'.$map.'", tricheckbox:true},null,data_'.$this->id.',true);
	}
	switch(values) {
		case "all":
			treevalues = "*";
			break;
		case "none":
			treevalues = "";
			break;
		default:
			treevalues = values.split(",");
			break;
	}
	'.$this->id.'.callbackCheck = null;
	'.$this->id.'.chks(treevalues, null, false);
	var p = jQuery(el).offset();
	if(tree.style.display != "none" && tree.style.top != ((p.top + el.offsetHeight) + "px")) {
		setTimeout(function(){
			window.aclMgr.updateMarketAcl(el,id);
		}, 100);
		return false;
	}
	tree.style.top = (p.top + el.offsetHeight + 5) + "px";
	tree.style.left = (p.left + el.offsetWidth - 200) + "px";

	var f = function(evt) {
		if (!evt) var evt = window.event;
		var trg = (window.event) ? evt.srcElement : evt.target;
		while(trg != null) {
			if(trg == tree || trg == el)
				return;
			trg = trg.parentNode;
		}
		tree.style.display = "none";
		window.Oby.removeEvent(document, "click", f);
	};
	window.Oby.addEvent(document, "click", f);

	'.$this->id.'.callbackCheck = function(treeObj, id, value) {
		var v = treeObj.getChk();
		if(v === false || v.length == 0) {
			e.value = "none";
		} else if( v.length > '.($this->cpt-1).') {
			e.value = "all";
		} else {
			e.value = v.join(",");
		}
	};

	tree.style.display = "";
	return false;
}';
		}

		$id = str_replace(array('[',']'),array('_',''),$map);

		$ret .= '<a href="#" onclick="return window.aclMgr.updateMarketAcl(this, \''.$id.'\');">'.
			'<i class="fas fa-unlock-alt"></i>'.
			'</a><input type="hidden" id="'.$id.'" name="'.$map.'" value="'.$values.'" />';

		if(!empty($js))
			JFactory::getDocument()->addScriptDeclaration($js);
		return $ret;
	}

	private function getAccess($action, $access) {
		$ret = hikamarket::aclTest($action, $access);
		if($ret === 1 || $ret === -1)
			$ret = 2;
		return $ret;
	}

	private function getData($accesses, $acls = null, $key = '') {
		$data = array();
		if($acls === null)
			$acls = $this->acl;
		foreach($acls as $groupKey => $groupAcls) {
			if(is_array($groupAcls)) {
				$status = 2;
				if(!empty($key))
					$status = 1;
				$subData = $this->getData($accesses, $groupAcls, $key.$groupKey.'/');
				$data[] = array(
					'status' => $status,
					'name' => $groupKey,
					'value' => $key.$groupKey,
					'checked' => $this->getAccess($key.$groupKey, $accesses),
					'data' => $subData
				);
			} else {
				$data[] = array(
					'status' => 0,
					'name' => $groupAcls,
					'value' => $key.$groupAcls,
					'checked' => $this->getAccess($key.$groupAcls, $accesses)
				);
			}
		}

		if(empty($key))
			return json_encode($data);
		return $data;
	}
}
