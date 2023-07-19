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
class hikashopPluginsType{
	var $type = 'payment';
	var $order = null;

	public function preload($backend = true) {
		if(empty($this->methods))
			$this->methods = array();
		if(empty($this->methods[$this->type]))
			$this->methods[$this->type] = array();
		$shipping = '';
		if(!$backend) {
			JPluginHelper::importPlugin('hikashop');
			JPluginHelper::importPlugin('hikashoppayment');
			$app = JFactory::getApplication();
			$usable_methods = array();
			$orderClass = hikashop_get('class.order');
			$this->order = $orderClass->loadFullOrder($this->order->order_id, true);
			if($this->type == 'payment' && !empty($this->order->shippings)) {
				$first = reset($this->order->shippings);
				$shipping = $first->shipping_type.'_'.$first->shipping_id;
			}
		}

		$pluginsClass = hikashop_get('class.plugins');
		$this->methods[$this->type][(string)@$this->order->order_id] = $pluginsClass->getMethods($this->type, '', $shipping);

		if(!empty($this->methods[$this->type][(string)@$this->order->order_id])){
			$max = 0;
			$already = array();
			foreach($this->methods[$this->type][(string)@$this->order->order_id] as $method) {
				if(!empty($method->ordering) && $max < $method->ordering) {
					$max = $method->ordering;
				}
			}
			foreach($this->methods[$this->type][(string)@$this->order->order_id] as $k => $method) {
				if(empty($method->ordering)) {
					$max++;
					$this->methods[$this->type][(string)@$this->order->order_id][$k]->ordering = $max;
				}
				while(isset($already[ $this->methods[$this->type][(string)@$this->order->order_id][$k]->ordering ])) {
					$max++;
					$this->methods[$this->type][(string)@$this->order->order_id][$k]->ordering = $max;
				}
				$already[$this->methods[$this->type][(string)@$this->order->order_id][$k]->ordering] = true;
			}
		}

		if(!$backend) {
			if($this->methods[$this->type][(string)@$this->order->order_id]) {
				$currencyClass = hikashop_get('class.currency');
				$full_price_without_payment = $this->order->order_full_price - $this->order->order_payment_price;
				foreach( $this->methods[$this->type][(string)@$this->order->order_id] as $k => $method) {
					if(!empty($method->payment_params->payment_percentage))
						$method->payment_price_without_percentage = $method->payment_price;
					$method->payment_price = $currencyClass->round(($full_price_without_payment * (float)@$method->payment_params->payment_percentage / 100) + @$method->payment_price, $currencyClass->getRounding($this->order->order_currency_id, true));
				}
				$zoneClass = hikashop_get('class.zone');
				$config = hikashop_config();
				$zones = $zoneClass->getOrderZones($this->order);//, $config->get('payment_methods_zone_address_type','billing_address'));
				$zone = null;
				if(!empty($zones) && is_array($zones) && count($zones)) {
					$zone = reset($zones);
					if(!is_numeric($zone)) {
						$zoneData = $zoneClass->get($zone);
						if(!empty($zoneData->zone_id)) {
							$zone = $zoneData->zone_id;
						}
					}
				}
				$currencyClass->processPayments($this->methods[$this->type][(string)@$this->order->order_id], $this->order, $zone, $this->order->order_currency_id);
				foreach( $this->methods[$this->type][(string)@$this->order->order_id] as $k => $method) {
					if($method->payment_id == $this->order->order_payment_id || (float)$method->payment_price == (float)$this->order->order_payment_price)
						continue;
					$diff = $method->payment_price_with_tax - $this->order->order_payment_price;
					$sign = '';
					if($diff > 0)
					 $sign = '+';
					$this->methods[$this->type][(string)@$this->order->order_id][$k]->payment_name .= ' ('.$sign.$currencyClass->format($diff, $this->order->order_currency_id).')';
				}
			}
			$app->triggerEvent('onPaymentDisplay', array( &$this->order, &$this->methods[$this->type][(string)@$this->order->order_id], &$usable_methods ) );
			if(!empty($usable_methods)) {
				ksort($usable_methods);
			}
			$this->methods[$this->type][(string)@$this->order->order_id] = $usable_methods;
		}

		if($this->type == 'shipping') {
			$unset = array();
			$add = array();
			foreach($this->methods[$this->type][(string)@$this->order->order_id] as $k => $method) {
				if($method->shipping_type == 'manual')
					continue;

				$plugin = hikashop_import('hikashop'.$this->type, $method->shipping_type);
				if(!$plugin) {
					$unset[] = $k;
					continue;
				}
				if(method_exists($plugin, 'shippingMethods')) {
					$methods = $plugin->shippingMethods($method);
					if(is_array($methods) && !empty($methods)) {
						$unset[] = $k;
						foreach($methods as $id => $name) {
							$new = clone($method);
							$new->shipping_id = $id;
							$new->shipping_name = JText::sprintf('SHIPPING_METHOD_COMPLEX_NAME',$method->shipping_name, $name);
							$add[] = $new;
						}
					} else {
						$unset[] = $k;
					}
				}
			}
			foreach($unset as $k) {
				unset($this->methods[$this->type][(string)@$this->order->order_id][$k]);
			}
			foreach($add as $v) {
				$this->methods[$this->type][(string)@$this->order->order_id][] = $v;
			}
		}
		return true;
	}

	public function load($type, $id) {
		$this->values = array();

		$found = false;
		$app = JFactory::getApplication();

		if(!empty($this->methods[$this->type][(string)@$this->order->order_id])) {
			$type_name = $this->type.'_type';
			$id_name = $this->type.'_id';
			$name = $this->type.'_name';

			foreach($this->methods[$this->type][(string)@$this->order->order_id] as $method) {
				if($method->$type_name == $type && $method->$id_name == $id) {
					$found = true;
				}
				if(empty($method->$name)) {
					if(empty($method->$type_name)) {
						$method->$name = '';
					} else {
						$method->$name = $method->$type_name . (hikashop_isClient('administrator') ? ' '.$method->$id_name : '');
					}
				}

				$this->values[] = JHTML::_('select.option', $method->$type_name.'_'.$method->$id_name, $method->$name);
			}
		}

		if(!$found && !is_array($type)){
			if(empty($type)) {
				$name = JText::_('HIKA_NONE');
			} else {
				$name =  $type . (hikashop_isClient('administrator') ? ' '.$id : '');
			}
			$this->values[] = JHTML::_('select.option', $type.'_'.$id, $name);
		}
	}

	public function getName($type, $id) {
		if(empty($this->methods[$this->type][(string)@$this->order->order_id])) {
			$this->preload();
		}

		if(!empty($this->methods[$this->type][(string)@$this->order->order_id])) {
			$type_name = $this->type.'_type';
			$id_name = $this->type.'_id';
			$name = $this->type.'_name';
			foreach($this->methods[$this->type][(string)@$this->order->order_id] as $method) {
				if($method->$type_name == $type && $method->$id_name == $id) {
					return $method->$name;
				}
			}

		}
		if(is_numeric($id)) {
			$class = hikashop_get('class.'.$this->type);
			$method = $class->get($id);
			$name = $this->type.'_name';
			if(!empty($method->$name))
				return $method->$name;
		}
		return '';
	}

	public function display($map, $type, $id, $backend = true, $attribute = 'size="1"') {
		if(empty($this->methods))
			$this->methods = array();
		if(empty($this->methods[$this->type]))
			$this->methods[$this->type] = array();

		if(empty($this->methods[$this->type][(string)@$this->order->order_id])) {
			$this->preload($backend);
		}

		$this->load($type,$id);

		if(is_array($type)) {
			$selected = array();
			foreach($type as $k => $t) {
				$selected[] = $t.'_'.$id[$k];
			}
		} else {
			$selected = $type.'_'.$id;
		}

		if($backend && !empty($this->order)) {
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration(' var '."default_".$this->type.'=\''.$selected.'\'; ');
			$attribute .= ' onchange="if(this.value==default_'.$this->type.'){return;} hikashop.openBox(\'plugin_change_link\', \''.hikashop_completeLink('order&task=changeplugin&order_id='.$this->order->order_id,true).'&plugin=\' +this.value+\'&type='.$this->type.'\'); this.value=default_'.$this->type.'; if(typeof(jQuery)!=\'undefined\'){jQuery(this).trigger(\'liszt:updated\');}"';
		}

		return JHTML::_('select.genericlist', $this->values, $map, 'class="custom-select" '.$attribute, 'value', 'text', $selected, $map.(string)@$this->order->order_id);
	}
}
