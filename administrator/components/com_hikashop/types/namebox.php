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
class hikashopNameboxType {
	const NAMEBOX_SINGLE = 1;
	const NAMEBOX_MULTIPLE = 2;

	protected $type = '';
	protected $options = array();

	protected $types = array(
		'product' => array(
			'class' => 'class.product',
			'name' => 'product_name',
			'mode' => 'tree',
			'displayFormat' => '[{product_id}] {product_name}',
			'url' => 'product&task=getTree&displayFormat={displayFormat}&variants={VARIANTS}',
			'url_params' => array('VARIANTS'),
			'options' => array(
				'tree_url' => 'product&task=getTree&displayFormat={displayFormat}&category_id={ID}',
				'tree_key' => '{ID}',
				'onlyNode' => true
			),
		),
		'article' => array(
			'class' => 'class.article',
			'name' => 'title',
			'mode' => 'list',
			'params' => array(
			),
			'url' => 'article&task=findList'
		),
		'brand' => array(
			'class' => 'class.category',
			'name' => 'category_name',
			'mode' => 'tree',
			'params' => array(
				'category_type' => 'manufacturer',
				'filters' => array(
					'c.category_depth > 1'
				),
				'key' => 'category_id',
			),
			'url' => 'category&task=getTree&category_type=manufacturer',
			'options' => array(
				'tree_url' => 'category&task=getTree&category_type=manufacturer&category_id={ID}',
				'tree_key' => '{ID}',
			),
		),
		'cart' => array(
			'class' => 'class.cart',
			'name' => 'cart_name',
			'mode' => 'list',
			'displayFormat' => '{cart_name} - {user_email} - {cart_id}',
			'params' => array(
				'type' => 'cart'
			),
			'options' => array(
				'olist' => array(
					'table' => array('cart_name' => 'HIKA_NAME', 'user_email' => 'CUSTOMER', 'cart_id' => 'ID' ),
					'displayFormat' => '{cart_name} - {user_email} - {cart_id}',
				)
			),
			'url_params' => array('TYPE'),
			'url' => 'cart&task=findList&type={TYPE}'
		),
		'category' => array(
			'class' => 'class.category',
			'name' => 'category_name',
			'mode' => 'tree',
			'url' => 'category&task=getTree',
			'options' => array(
				'tree_url' => 'category&task=getTree&category_id={ID}',
				'tree_key' => '{ID}',
			),
		),
		'characteristic' => array(
			'class' => 'class.characteristic',
			'name' => 'name',
			'mode' => 'list',
			'params' => array(
				'value' => false
			),
			'url' => 'characteristic&task=findList'
		),
		'characteristic_value' => array(
			'class' => 'class.characteristic',
			'name' => 'characteristic_value',
			'mode' => 'list',
			'params' => array(
				'value' => true
			),
			'url' => 'characteristic&task=findList&characteristic_type=value&characteristic_parent_id={ID}',
			'url_params' => array('ID'),
			'options' => array(
				'add_url' => 'characteristic&task=addcharacteristic_ajax&characteristic_type=value&characteristic_parent_id={ID}&tmpl=json',
			)
		),
		'column' => array(
			'class' => 'helper.database',
			'name' => 'column_name',
			'mode' => 'list',
			'displayFormat' => '{column_name}',
			'params' => array(
				'table' => ''
			),
			'url_params' => array('TABLE'),
			'url' => 'cart&task=findList&table={TABLE}'
		),
		'currency' => array(
			'class' => 'class.currency',
			'name' => 'name',
			'mode' => 'list',
			'params' => array(
			),
			'url' => 'currency&task=findList'
		),
		'discount' => array(
			'class' => 'class.discount',
			'name' => 'discount_code',
			'mode' => 'list',
			'displayFormat' => '{discount_code} ({discount_type})',
			'url' => 'discount&task=findList&type={TYPE}',
			'url_params' => array('TYPE'),
			'params' => array(
				'type' => ''
			)
		),
		'field' => array(
			'class' => 'class.field',
			'name' => 'field_realname',
			'mode' => 'list',
			'displayFormat' => '{field_realname} - {field_namekey} - {field_type}',
			'params' => array(
				'table' => ''
			),
			'options' => array(
				'olist' => array(
					'table' => array('field_realname' => 'FIELD_LABEL', 'field_namekey' => 'FIELD_COLUMN', 'field_type' => 'FIELD_TYPE' ),
					'displayFormat' => '{field_realname} - {field_namekey} - {field_type}',
				)
			),
			'url_params' => array('TABLE'),
			'url' => 'cart&task=findList&table={TABLE}'
		),
		'filter' => array(
			'class' => 'class.filter',
			'name' => 'filter_name',
			'mode' => 'list',
			'displayFormat' => '{filter_name} - {filter_namekey} - {filter_type}',
			'params' => array(
			),
			'options' => array(
				'olist' => array(
					'table' => array('filter_name' => 'FIELD_LABEL', 'filter_namekey' => 'FIELD_COLUMN', 'filter_type' => 'HIKA_TYPE' ),
					'displayFormat' => '{filter_name} - {filter_namekey} - {filter_type}',
				)
			),
			'url' => 'filter&task=findList'
		),
		'modules' => array(
			'class' => 'class.modules',
			'name' => 'id',
			'mode' => 'list',
			'displayFormat' => '{title} ({id})',
			'url' => 'modules&task=getValues',
			'options' => array(
				'olist' => array(
					'table' => array('title' => 'HIKA_NAME', 'module' => 'HIKA_TYPE', 'id' => 'ID'),
					'displayFormat' => '{title} ({id})'
				)
			)
		),
		'order' => array(
			'class' => 'class.order',
			'name' => 'order_number',
			'mode' => 'list',
			'displayFormat' => '{order_number} - {user_email} - {order_full_price}',
			'params' => array(
			),
			'options' => array(
				'olist' => array(
					'table' => array('order_number' => 'ORDER_NUMBER', 'user_email' => 'CUSTOMER', 'order_full_price' => 'PRICE' ),
					'displayFormat' => '{order_number} - {user_email} - {order_full_price}',
				)
			),
			'url' => 'order&task=findList'
		),
		'order_status' => array(
			'class' => 'class.orderstatus',
			'name' => 'orderstatus_name',
			'mode' => 'list',
			'params' => array(
			),
			'url' => 'category&task=findList&category_type=order_status'
		),
		'plugin_images' => array(
			'class' => 'class.plugin',
			'name' => 'image_name',
			'mode' => 'list',
			'displayFormat' => '{image_name}',
			'params' => array(
				'type' => 'images'
			),
			'url' => 'plugin&task=findList&image_type={TYPE}',
			'url_params' => array('TYPE'),
			'options' => array(
				'olist' => array(
					'table' => array('image_name' => 'HIKA_NAME', 'image_url' => 'HIKA_IMAGE'),
					'displayFormat' => '{image_name}',
				)
			)
		),
		'shipping_methods' => array(
			'class' => 'class.shipping',
			'name' => 'shipping_namekey',
			'mode' => 'list',
			'displayFormat' => '{shipping_name}',
			'params' => array(

			)
		),
		'payment_methods' => array(
			'class' => 'class.payment',
			'name' => 'payment_namekey',
			'mode' => 'list',
			'displayFormat' => '{payment_name}',
			'params' => array(

			)
		),
		'tax_category' => array(
			'class' => 'class.category',
			'name' => 'category_name',
			'mode' => 'tree',
			'params' => array(
				'category_type' => 'tax'
			),
			'url' => 'category&task=findList&category_type=tax',
			'options' => array(
				'tree_url' => 'category&task=getTree&category_type=tax&category_id={ID}',
				'tree_key' => '{ID}',
			),
		),
		'user' => array(
			'class' => 'class.user',
			'name' => 'user_id',
			'mode' => 'list',
			'displayFormat' => '{user_id} - {name}',
			'url' => 'user&task=getValues',
			'options' => array(
				'olist' => array(
					'table' => array('user_id' => 'ID', 'name' => 'HIKA_NAME', 'user_email' => 'HIKA_EMAIL' ),
					'displayFormat' => '{user_id} - {name}',
				)
			)
		),
		'warehouse' => array(
			'class' => 'class.warehouse',
			'name' => 'warehouse_name',
			'mode' => 'list',
			'displayFormat' => '{warehouse_name}',
			'url' => 'warehouse&task=findValue&displayFormat={displayFormat}',
			'params' => array(
			)
		),
		'zone' => array(
			'class' => 'class.zone',
			'name' => 'zone_namekey',
			'mode' => 'tree',
			'displayFormat' => '{zone_name_english}',
			'url' => 'zone&task=getTree&displayFormat={displayFormat}',
			'options' => array(
				'tree_url' => 'zone&task=getTree&displayFormat={displayFormat}&zone_key={ID}',
				'tree_key' => '{ID}',
			)
		),
		'rawlist' => array(
			'class' => 'type.namebox_rawlist',
			'mode' => 'list'
		),
	);


	public function __construct() {
	}

	public function setType($type, $options = array()) {
		$this->type = $type;
		$this->options = $options;
	}

	private function load($type) {
		if(isset($this->types[$type]))
			return;

		static $loaded_types = false;
		if($loaded_types === false) {
			$loaded_types = array();

			JPluginHelper::importPlugin('hikashop');
			$app = JFactory::getApplication();
			$app->triggerEvent('onNameboxTypesLoad', array(&$loaded_types));
		}

		foreach($loaded_types as $k => $v) {
			if(!isset($this->types[$k]))
				$this->types[$k] = $v;
		}
	}

	private function getClass($class) {
		if(is_string($class))
			return hikashop_get($class);
		if(is_object($class) && method_exists($class, 'getNameboxData'))
			return $class;
		if(is_array($class) && isset($class['file']) && file_exists($class['file']) && isset($class['name'])) {
			include_once($class['file']);
			$n = $class['name'];
			$ret = new $n;
			if(method_exists($ret, 'getNameboxData'))
				return $ret;
		}
		return null;
	}

	public function getValues($search = '', $type = '', $options = array()) {
		if(empty($type))
			$type = $this->type;
		if(empty($type))
			return '';

		$this->load($type);

		if(!isset($this->types[$type]))
			return '';

		$typeConfig = $this->types[$type];
		$nameboxClass = $this->getClass($typeConfig['class']);
		if(empty($nameboxClass))
			return false;

		if(!empty($options['displayFormat']))
			$options['displayFormat'] = $this->getDisplayFormat($options['displayFormat'], $type);
		if(empty($options['displayFormat']))
			$options['displayFormat'] = @$typeConfig['displayFormat'];

		$fullLoad = true;
		list($elements, $values) = $nameboxClass->getNameboxData($typeConfig, $fullLoad, hikashopNameboxType::NAMEBOX_MULTIPLE, null, $search, $options);

		if((!empty($typeConfig['mode']) && $typeConfig['mode'] == 'list') && empty($typeConfig['options']['olist']['table']) && !is_string(reset($elements))) {
			$n = $typeConfig['name'];
			foreach($elements as &$element) {
				if(!empty($options['displayFormat']))
					$element = $this->getDisplayValue($element, $typeConfig, $options);
				else
					$element = $element->$n;
			}
			unset($element);
		}

		return $elements;
	}


	public function display($map, $value, $mode = hikashopNameboxType::NAMEBOX_MULTIPLE, $type = '', $options = array()) {
		if(empty($type))
			$type = $this->type;
		if(empty($type))
			return '';

		$this->load($type);

		if(!isset($this->types[$type]))
			return '';

		$typeConfig = $this->types[$type];

		$config = hikashop_config();

		$nameboxClass = $this->getClass($typeConfig['class']);
		if(empty($nameboxClass))
			return '';

		hikashop_loadJslib('otree');
		if($mode == hikashopNameboxType::NAMEBOX_MULTIPLE && isset($options['sort']) && $options['sort'] == true)
			hikashop_loadJslib('jquery');

		$id = rtrim(str_replace(array('"',"'",'\\','[]','[',']','.'),array('','','','','_','','_'),(string)$map), '_');
		if(empty($id))
			$id = !empty($options['id']) ? $options['id'] : 'nb_'.uniqid();

		$key = '';
		$default_text = !empty($options['default_text']) ? $options['default_text'] : 'HIKA_NONE';
		$name = '<em>'.JText::_($default_text).'</em>';
		$cleanText = '<em>'.str_replace("'", "\\'", JText::_($default_text)).'</em>';

		$fullLoad = true;
		list($elements, $values) = $nameboxClass->getNameboxData($typeConfig, $fullLoad, $mode, $value, null, $options);

		if(isset($options['returnOnEmpty']) && empty($elements))
			return $options['returnOnEmpty'];

		$displayFormat = '';
		if(!empty($typeConfig['displayFormat']))
			$displayFormat = $typeConfig['displayFormat'];
		if(!empty($options['displayFormat']))
			$displayFormat = $options['displayFormat'];

		$style = '';
		if(!empty($options['style']))
			$style = ' style="' . is_array($options['style']) ? implode(' ', $options['style']) : $options['style'] . '"';

		$attributes = '';
		if(!empty($options['attributes']))
			$attributes = ' '.trim($options['attributes']);

		$lang = JFactory::getLanguage();
		$leftOffset = ($lang->isRTL()) ? '2000px' : '-2000px';

		if((!empty($typeConfig['mode']) && $typeConfig['mode'] == 'list') && empty($typeConfig['options']['olist']['table']) && !is_string(reset($elements))) {
			$n = $typeConfig['name'];
			foreach($elements as &$element) {
				if(!empty($displayFormat))
					$element = $this->getDisplayValue($element, $typeConfig, $options);
				else
					$element = $element->$n;
			}
			unset($element);
		}

		$ret = '<div class="nameboxes" id="'.$id.'" onclick="window.oNameboxes[\''.$id.'\'].focus(\''.$id.'_text\');"'.$style.$attributes.'>';

		if($mode == hikashopNameboxType::NAMEBOX_SINGLE) {
			if(!empty($values)) {
				$key = $value;
				$v = $values;
				if(is_array($values))
					$v = $values[$key];
				$name = $this->getDisplayValue($v, $typeConfig, $options);
			}

			$delete = (isset($options['delete']) && $options['delete'] == true);
			$ret .= '
	<div class="namebox" id="'.$id.'_namebox">
		<input type="hidden" name="'.$map.'" id="'.$id.'_valuehidden" value="'.$key.'"/><span id="'.$id.'_valuetext">'.$name.'</span>
		'.(!$delete ?
			'<a class="editbutton" href="#" onclick="return false;"><span>-</span></a>' :
			'<a class="closebutton" href="#" onclick="window.oNameboxes[\''.$id.'\'].clean(this,\''.$cleanText.'\');return false;" title="'.JText::_('HIKA_DELETE').'"><span>X</span></a>'
		).'
	</div>
	<div class="nametext">
		<input id="'.$id.'_text" type="text" style="width:50px;min-width:60px" onfocus="window.oNameboxes[\''.$id.'\'].focus(this);" onkeyup="window.oNameboxes[\''.$id.'\'].search(this);" onchange="window.oNameboxes[\''.$id.'\'].search(this);"/>
		<span style="position:absolute;top:0px;left:'.$leftOffset.';visibility:hidden" id="'.$id.'_span">xxxxxx</span>
	</div>';
		}
		else {
			if(substr($map, -2) === '[]')
				$map = substr($map, 0, -2);

			if(!empty($values)) {
				if(!empty($typeConfig['name']))
					$n = $typeConfig['name'];
				foreach($values as $key => $name) {
					$obj = null;
					if(is_object($name)) {
						$obj = $name;
						if(!empty($displayFormat))
							$name = $this->getDisplayValue($obj, $typeConfig, $options);
						elseif(!empty($n))
							$name = $name->$n;
						else
							$name = 'name missing in type.namebox';
					}
					$ret .= "\r\n".'<div class="namebox" id="'.$id.'-'.$key.'">'.
						'<input type="hidden" name="'.$map.'[]" value="'.$key.'"/>'.$name.
						' <a class="closebutton" href="#" onclick="window.oNameboxes[\''.$id.'\'].unset(this,\''.$key.'\');window.oNamebox.cancelEvent();return false;" title="'.JText::_('HIKA_DELETE').'"><span>X</span></a>'.
						'</div>';
				}
			}
			$ret .= "\r\n".'<div class="namebox" style="display:none;" id="'.$id.'tpl">'.
				'<input type="hidden" name="{map}" value="{key}"/>{name}'.
				' <a class="closebutton" href="#" onclick="window.oNameboxes[\''.$id.'\'].unset(this,\'{key}\');window.oNamebox.cancelEvent();return false;" title="'.JText::_('HIKA_DELETE').'"><span>X</span></a>'.
				'</div>';
			$ret .= "\r\n".'<div class="nametext">'.
				'<input id="'.$id.'_text" type="text" style="width:50px;min-width:60px" onfocus="window.oNameboxes[\''.$id.'\'].focus(this);" onkeyup="window.oNameboxes[\''.$id.'\'].search(this);" onchange="window.oNameboxes[\''.$id.'\'].search(this);"/>'.
				'<span style="position:absolute;top:0px;left:'.$leftOffset.';visibility:hidden" id="'.$id.'_span">span</span>'.
				'</div>';

			if(!empty($options['force_data']))
				$ret .= '<input type="hidden" name="'.$map.'[]" value=""/>';
		}

		if(isset($options['add']) && $options['add'] == true) {
			$ret .= '<div id="'.$id.'_add" style="display:none;float:right"><a href="#" onclick="return window.oNameboxes[\''.$id.'\'].create(this);"><img src="'.HIKASHOP_IMAGES.'plus.png" style="vertical-align:middle;margin:0px;padding:0px;" alt="+"/></a></div>';
			$ret .= '<div id="'.$id.'_loading" style="display:none;float:right"><img src="'.HIKASHOP_IMAGES.'spinner.gif" style="vertical-align:middle;margin:0px;padding:0px;" alt="loading..."/></div>';
		}

		$ret .= "\r\n\t".'<div id="'.$id.'hikaclear" style="clear:both;float:none;"></div></div>';

		$namebox_options = array(
			'mode' => $typeConfig['mode'],
			'img_dir' => HIKASHOP_IMAGES,
			'map' => $map,
			'min' => $config->get('namebox_search_min_length', 3)
		);

		if($mode == hikashopNameboxType::NAMEBOX_MULTIPLE && isset($options['sort']) && $options['sort'] == true)
			$namebox_options['sort'] = true;

		if(isset($options['add']) && $options['add'] == true && !empty($typeConfig['options']['add_url'])) {
			$namebox_options['add'] = true;
			$url = ''. $typeConfig['options']['add_url'];
			if(!empty($typeConfig['url_params'])) {
				foreach($typeConfig['url_params'] as $k) {
					$p = '';
					if(isset($options['url_params'][$k])) {
						$p = $options['url_params'][$k];
					}
					$url = str_replace('{' . $k . '}', $p, $url);
				}
			}
			$url .= '&' . hikashop_getFormToken() . '=1';
			if(substr($url, 0, 10) == 'index.php?')
				$namebox_options['add_url'] = str_replace('&amp;', '&', JRoute::_($url));
			else
				$namebox_options['add_url'] = hikashop_completeLink($url, false, false, true);
		}

		if($mode == hikashopNameboxType::NAMEBOX_SINGLE) {
			$namebox_options['multiple'] = false;
			$namebox_options['default_text'] = '<em>'.JText::_($default_text).'</em>';
		}

		if(isset($typeConfig['options'])) {
			foreach($typeConfig['options'] as $k => $v) {
				if(isset($namebox_options[$k]))
					continue;
				$namebox_options[$k] = $v;
			}
		}
		if(isset($namebox_options['olist']['table'])) {
			foreach($namebox_options['olist']['table'] as $k => $v) {
				$namebox_options['olist']['table'][$k] = JText::_($v);
			}
		}

		if(!$fullLoad) {
			$url = '' . $typeConfig['url'];
			if(!empty($typeConfig['url_params'])) {
				foreach($typeConfig['url_params'] as $k) {
					$p = '';
					if(isset($options['url_params'][$k])) {
						$p = $options['url_params'][$k];
					}
					$url = str_replace('{' . $k . '}', $p, $url);
				}
			}
			if(strpos($url, '{displayFormat}') !== false)
				$url = str_replace('{displayFormat}', $this->getDisplayFormatId($displayFormat, $type), $url);
			$url .= '&search=HIKASEARCH';

			if(empty($typeConfig['mode']) || $typeConfig['mode'] == 'list') {
				if(empty($namebox_options['olist']))
					$namebox_options['olist'] = array();
				$namebox_options['olist']['gradientLoad'] = true;
				$url .= '&start=HIKASTART';
				$namebox_options['url_pagination'] = 'HIKASTART';
			}

			if(substr($url, 0, 10) == 'index.php?')
				$namebox_options['url'] = str_replace('&amp;', '&', JRoute::_($url));
			else
				$namebox_options['url'] = hikashop_completeLink($url, false, false, true);
			$namebox_options['url_keyword'] = 'HIKASEARCH';
		}

		if(isset($namebox_options['tree_url'])) {
			if(strpos($namebox_options['tree_url'], '{displayFormat}') !== false)
				$namebox_options['tree_url'] = str_replace('{displayFormat}', $this->getDisplayFormatId($displayFormat, $type), $namebox_options['tree_url']);

			if(substr($namebox_options['tree_url'], 0, 10) == 'index.php?')
				$namebox_options['tree_url'] = str_replace('&amp;', '&', JRoute::_($namebox_options['tree_url']));
			else
				$namebox_options['tree_url'] = hikashop_completeLink($namebox_options['tree_url'], false, false, true);
		}

		if(!empty($typeConfig['mode']) && $typeConfig['mode'] == 'tree') {
			$ret .= '
<div class="namebox-popup">
	<div style="display:none;" data-oresize="'.$id.'" class="namebox-popup-resize namebox-popup-container">
		<div id="'.$id.'_otree" class="oTree namebox-popup-content"></div>
	</div>
</div>';
			$js = '

new window.oNamebox(
	\''.$id.'\',
	'.json_encode($elements).',
	'.json_encode($namebox_options).'
);';
		}
		else {
			$ret .= '
<div class="namebox-popup">
	<div style="display:none;" data-oresize="'.$id.'" class="namebox-popup-resize namebox-popup-container">
		<div id="'.$id.'_olist" class="oList namebox-popup-content"></div>
	</div>
</div>';
			$js = '
new window.oNamebox(
	\''.$id.'\',
	'.json_encode($elements).',
	'.json_encode($namebox_options).'
);';
			if(!empty($values) && $mode == hikashopNameboxType::NAMEBOX_MULTIPLE) {
				$b = array();
				foreach($values as $key => $name) {
					$b[] = $key;
				}
				$js .= '
try{
	window.oNameboxes[\''.$id.'\'].content.block('.json_encode($b).');
}catch(e){}';
			}
		}
		$js = 'window.hikashop.ready( function(){
			'.$js.'
		});';
		$tmpl = hikaInput::get()->getVar('tmpl');
		if(in_array($tmpl, array('ajax', 'raw', 'component'))) {
			$ret .= '<script type="text/javascript">'.$js.'</script>';
		} else {
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration($js);
		}

		return $ret;
	}

	public function getDisplayValue($value, $typeConfig, $options) {
		$ret = '';
		if(!empty($typeConfig['displayFormat']))
			$ret = $typeConfig['displayFormat'];
		if(!empty($options['displayFormat']))
			$ret = $options['displayFormat'];

		$n = @$typeConfig['name'];

		if(empty($ret)) {
			if(is_string($value))
				return $value;
			if(is_object($value) && isset($value->name))
				return $value->name;
			if(is_object($value) && isset($value->$n))
				return $value->$n;
			return $ret;
		}

		if(is_array($value))
			$v = reset($value);

		$matches = array();
		if(preg_match_all('#{([_a-zA-Z0-9]+)}#u', $ret, $matches)) {
			foreach($matches[1] as $m) {
				$v = '';
				if(isset($value->$m))
					$v = $value->$m;
				$ret = str_replace('{' . $m . '}', $v, $ret);
			}
		}
		return $ret;
	}

	public function getDisplayFormatId($displayFormat, $type = '') {
		if(empty($type))
			$type = $this->type;
		if(empty($type))
			return false;

		$this->load($type);
		if(!isset($this->types[$type]))
			return false;

		$app = JFactory::getApplication();
		$displays = $app->getUserState(HIKASHOP_COMPONENT.'.nameboxes.display', null);
		if(empty($displays))
			$displays = array();
		if(!empty($displays[$type])) {
			foreach($displays[$type] as $k => $v) {
				if($v == $displayFormat)
					return $k;
			}
		} else {
			$displays[$type] = array();
		}

		$id = substr($type, 0, 2) . uniqid();
		$displays[$type][$id] = $displayFormat;
		$app->setUserState(HIKASHOP_COMPONENT.'.nameboxes.display', $displays);

		return $id;
	}

	public function getDisplayFormat($id, $type = '') {
		if(empty($type))
			$type = $this->type;
		if(empty($type))
			return false;

		$this->load($type);
		if(!isset($this->types[$type]))
			return false;

		$app = JFactory::getApplication();
		$displays = $app->getUserState(HIKASHOP_COMPONENT.'.nameboxes.display', null);
		if(!isset($displays[$type]))
			return false;
		if(isset($displays[$type][$id]))
			return $displays[$type][$id];
		return false;
	}
}
