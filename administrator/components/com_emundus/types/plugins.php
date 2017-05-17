<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
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

		$pluginsClass = hikashop_get('class.plugins');
		$this->methods[$this->type][(string)@$this->order->order_id] = $pluginsClass->getMethods($this->type);

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
			JPluginHelper::importPlugin('hikashoppayment');
			$dispatcher = JDispatcher::getInstance();
			$usable_methods = array();
			$orderClass = hikashop_get('class.order');
			$this->order = $orderClass->loadFullOrder($this->order->order_id, true);

			$dispatcher->trigger('onPaymentDisplay', array( &$this->order, &$this->methods[$this->type][(string)@$this->order->order_id], &$usable_methods ) );
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
				if($plugin && method_exists($plugin, 'shippingMethods')) {
					$methods = $plugin->shippingMethods($method);
					if(is_array($methods) && !empty($methods)) {
						$unset[] = $k;
						foreach($methods as $id => $name) {
							$new = clone($method);
							$new->shipping_id = $id;
							$new->shipping_name = $method->shipping_name . ' - ' . $name;
							$add[] = $new;
						}
					} else {
						$unset[] = $k;
					}
				} else {
					$unset[] = $k;
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
						$method->$name = $method->$type_name . ($app->isAdmin() ? ' '.$method->$id_name : '');
					}
				}

				$this->values[] = JHTML::_('select.option', $method->$type_name.'_'.$method->$id_name, $method->$name);
			}
		}

		if(!$found && !is_array($type)){
			if(empty($type)) {
				$name = JText::_('HIKA_NONE');
			} else {
				$name =  $type . ($app->isAdmin() ? ' '.$id : '');
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

		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox" '.$attribute, 'value', 'text', $selected, $map.(string)@$this->order->order_id);
	}
}
