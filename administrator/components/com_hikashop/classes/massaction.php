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
class hikashopMassactionClass extends hikashopClass{
	var $tables = array('massaction');
	var $pkeys = array('massaction_id');
	var $toggle = array('massaction_published'=>'massaction_id');
	var $report = array();

	function addActionButtons(&$toolbar, $table) {
		if(!is_array($toolbar))
			return;
		$this->database->setQuery('SELECT * FROM #__hikashop_massaction WHERE massaction_published=1 AND massaction_button=1 AND massaction_table='.$this->database->Quote($table));
		$massactionsFromDB = $this->database->loadObjectList();
		if(empty($massactionsFromDB)) {
			return;
		}
		foreach($massactionsFromDB as $action) {
			if(empty($action->massaction_name))
				continue;
			array_unshift($toolbar, array('name' => 'confirm', 'icon' => 'cogs', 'title' => hikashop_translate($action->massaction_name), 'alt' => hikashop_translate($action->massaction_name), 'task' => ('action_'.$action->massaction_id), 'check' => true));
		}
	}

	function addActionTasks(&$controller, $table) {
		if(!is_object($controller))
			return;
		$this->database->setQuery('SELECT * FROM #__hikashop_massaction WHERE massaction_published=1 AND massaction_button=1 AND massaction_table='.$this->database->Quote($table));
		$massactionsFromDB = $this->database->loadObjectList();
		if(empty($massactionsFromDB)) {
			return;
		}
		foreach($massactionsFromDB as $action) {
			if(empty($action->massaction_name))
				continue;
			$controller->modify[] = 'action_'.$action->massaction_id;
		}
	}

	function runActions($massaction_id, $type) {
		$massaction = $this->get($massaction_id);
		if(empty($massaction)) {
			$app = JFactory::getApplication();
			$app->enqueueMessage('Mass action with the id '.$massaction_id.' not found.', 'error');
			return true;
		}

		if($massaction->massaction_table != $type) {
			$app = JFactory::getApplication();
			$app->enqueueMessage('Data mismatch. The mass action '.$massaction_id.' is for the data '. $massaction->massaction_table.' and not '.$type, 'error');
			return true;
		}

		$elements = hikaInput::get()->get('cid', array(), 'array');

		$massaction->massaction_triggers = array();
		$massaction->massaction_filters = array();

		ob_start();
		$this->process($massaction,$elements);
		$html = ob_get_clean();
		if(empty($html)) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('ACTION_SUCCESSFULLY_PROCESSED_ON_X_ELEMENTS', count($elements)), 'success');
			return true;
		} else {
			return $html;
		}
	}

	function saveForm(){
		$element = new stdClass();
		$element->massaction_id = hikashop_getCID('massaction_id');
		$formData = hikaInput::get()->get('data', array(), 'array');

		foreach($formData['massaction'] as $column => $value){
			hikashop_secureField($column);
			$element->$column = strip_tags($value);
		}
		$this->_retreiveData($element,'trigger');
		$this->_retreiveData($element,'filter');
		$this->_retreiveData($element,'action');


		$translationHelper = hikashop_get('helper.translation');
		$translationHelper->getTranslations($element);

		$result = $this->save($element);
		if($result){
			$translationHelper->handleTranslations('massaction',$result,$element);
			hikaInput::get()->set( 'cid', $result);
		}
		return $result;
	}

	function batch() {
		$element = new stdClass();
		$element->massaction_table = hikaInput::get()->getVar('table_type');
		$this->_retreiveData($element,'action');

		$elements = hikaInput::get()->get('cid', array(), 'array');

		ob_start();
		$this->process($element,$elements);
		$html = ob_get_clean();
		if(empty($html)) {
			hikashop_display(array(JText::sprintf('ACTION_SUCCESSFULLY_PROCESSED_ON_X_ELEMENTS', count($elements))), 'success');
		} else {
			echo $html;
		}
	}

	function _retreiveData(&$element, $type='trigger'){
		$var_name = 'massaction_'.$type.'s';
		$element->$var_name = '';
		$formData = hikaInput::get()->get($type, array(), 'array');

		if(empty($formData[$element->massaction_table]))
			return;

		if(count($formData[$element->massaction_table]) > 0)
			$element->$var_name = array();
		foreach($formData[$element->massaction_table]['type'] as $i => $selection){
			if(empty($selection))
				continue;
			$obj = new stdClass();
			$obj->name = $selection;
			$obj->data = array();
			foreach($formData[$element->massaction_table] as $k => $v){
				if(is_int($k) && $k==$i){
					$obj->data = $v[$selection];
					break;
				}
			}
			$element->{$var_name}[$i] = $obj;
		}
	}

	function organizeExportColumn(&$params,$table, &$columnsArrays){
		$data =& $params->table;
		$elements =& $params->elements;
		$action =& $params->action;
		$types =& $params->types;
		switch($data){
			case 'product':
				switch($table){
					case 'product':
						$ids = array();
						$database = JFactory::getDBO();
						foreach($elements as $element){
							if(isset($element->product_parent_id->id)){
								$ids[] = $element->product_parent_id->id;
							}else if($element->product_parent_id){
								$ids[] = $element->product_parent_id;
							}
						}
						if(!empty($ids)){

							$tIds = array();
							foreach($ids as $id){
								if(isset($id->id))
									$tIds[(int)$id->id] = (int)$id->id;
								elseif($id != 0)
									$tIds[(int)$id] = (int)$id;
							}
							if(empty($tIds))
								$tIds = array(0);
							$query = 'SELECT product_id, product_code';
							$query .= ' FROM '.hikashop_table('product');
							$query .= ' WHERE product_id IN ('.implode(',',$tIds).')';
							$database->setQuery($query);
							$rows = $database->loadObjectList();
							foreach($elements as &$element){
								$id = $element->product_parent_id;
								$element->product_parent_id = '';
								if(!isset($types['product_parent_id']))
									$types['product_parent_id'] = new stdClass();
								$types['product_parent_id']->type = 'text';
								foreach($rows as $row){
									if(isset($id->id) && $id->id != $row->product_id){continue;}
									$element->product_parent_id = $row->product_code;
								}
								unset($element);
							}
						}
						break;
					case 'related':
						$ids = array();
						$database = JFactory::getDBO();
						foreach($elements as $element){
							$ids[] = $element->product_id;
						}
						$query = 'SELECT pr.product_id, p.product_code';
						$query .= ' FROM '.hikashop_table('product').' AS p';
						$query .= ' INNER JOIN '.hikashop_table('product_related').' AS pr ON pr.product_related_id = p.product_id';
						$query .= ' WHERE pr.product_id IN ('.implode(',',$ids).')';
						$database->setQuery($query);
						$rows = $database->loadObjectList();
						$types['related']->type = 'text';
						foreach($elements as $element){
							foreach($element as $key=>$related){
								if($key == 'related'){
									foreach($related as $product){
										$product->related = '';
										foreach($rows as $row){
											if($element->product_id != $row->product_id){continue;}
											$product->related = $row->product_code;
										}
									}
								}
							}
						}
						break;
					case 'category':
						$onlyName = false;
						if(count($action['category']) == '1' && isset($action['category']['category_name'])){
							$onlyName = true;
							unset($action['category']);
						}

						$database = JFactory::getDBO();
						$ids = array();
						foreach($elements as $element){
							foreach($element as $key=>$el){
								if($key == 'category'){
									foreach($el as $category){
										if(isset($category->category_id)){
											$ids[] = (int)$category->category_id;
										}
									}
								}
							}
						}
						if(!empty($ids)){
							$query = 'SELECT DISTINCT category_name, category_id';
							$query .= ' FROM '.hikashop_table('category');
							$query .= ' WHERE category_id IN ('.implode(',',$ids).')';
							$database->setQuery($query);
							$rows = $database->loadObjectList();
							foreach($elements as $element){
								foreach($element as $key=>&$el){
									if($key == 'category'){
										foreach($el as &$category){
											if(isset($category->category_id)){
												$array = array();
												foreach($rows as $row){
													if($row->category_id == $category->category_id){
														$array[] = $row->category_name;
													}
												}
												$category->categories = $this->separator($array,$data,$table);
												$types['categories'] = new stdClass();
												$types['categories']->type = 'text';
												if($onlyName)
													$action['category']['categories'] = 'categories';
											}
											unset($category);
										}
									}
									unset($el);
								}
							}
						}
						break;
				}
				break;
			case 'user':
				switch($table){
					case 'address':
						$columnsArrays['billing_address'] = $columnsArrays['address'];
						$columnsArrays['shipping_address'] = $columnsArrays['address'];
						foreach($elements as &$element){
							$element->shipping_address = array();
							$element->billing_address = array();
							if(isset($element->address)){
								foreach($element->address as $k=>$address){
									if($address->address_default != 1){
										unset($element->address[$k]);
									} else {
										if(!count($element->shipping_address) && in_array($address->address_type, array('shipping', '', 'both'))) {
											$object = new stdClass();
											foreach($address as $column=>$value){
												if(!isset($action['address'][$column])){continue;}
												$shipping_column = 'shipping_'.$column;
												$action['shipping_address'][$shipping_column] = $shipping_column;
												if(isset($columnsArrays['address'][$column]))
													$columnsArrays['shipping_address'][$shipping_column] = $columnsArrays['address'][$column];
												$object->$shipping_column=$value;
												if(!isset($types[$shipping_column])) $types[$shipping_column] = new stdClass();
												$types[$shipping_column]->type = $types[$column]->type;
											}
											$element->shipping_address[] = $object;
										}
										if(!count($element->billing_address) && in_array($address->address_type, array('billing', '', 'both'))) {
											$object = new stdClass();
											foreach($address as $column=>$value){
												if(!isset($action['address'][$column])){continue;}
												$billing_column = 'billing_'.$column;
												if(isset($columnsArrays['address'][$column]))
													$columnsArrays['billing_address'][$billing_column] = $columnsArrays['address'][$column];
												$action['billing_address'][$billing_column] = $billing_column;
												$object->$billing_column=$value;
												if(!isset($types[$billing_column])) $types[$billing_column] = new stdClass();
												$types[$billing_column]->type = $types[$column]->type;
											}
											$element->billing_address[] = $object;
										}
									}
								}
							}
							unset($element);
						}
						unset($action['address']);
						break;
				}
				break;
			case 'category':
				$database = JFactory::getDBO();
				$ids = array();

				foreach($elements as $category){
					if(isset($category->category_id)){
						$ids[] = (int)$category->category_id;
					}
					if(isset($category->category_parent_id)){
						$ids[] = (int)$category->category_parent_id->id;
					}
				}
				if(!empty($ids)){
					$query = 'SELECT DISTINCT category_name, category_id';
					$query .= ' FROM '.hikashop_table('category');
					$query .= ' WHERE category_id IN ('.implode(',',$ids).')';
					$database->setQuery($query);
					$rows = $database->loadObjectList();
					foreach($elements as &$category){
						if(isset($category->category_parent_id->id)){
							$array = array();
							foreach($rows as $row){
								if($row->category_id == $category->category_parent_id->id){
									$array[] = $row->category_name;
								}
							}
							$category->parent_category = $this->separator($array,$data,$table);
							if(!isset($types['parent_category']) || !is_object($types['parent_category']))
								$types['parent_category'] = new stdClass();
							$types['parent_category']->type = 'text';
							$action['category']['parent_category'] = 'parent_category';
						}
						if(isset($category->category_id)){
							$array = array();
							foreach($rows as $row){
								if($row->category_id == $category->category_id){
									$array[] = $row->category_name;
								}
							}
							$category->categories = $this->separator($array,$data,$table);
							if(!isset($types['categories']) || !is_object($types['categories']))
								$types['categories'] = new stdClass();
							$types['categories']->type = 'text';
						}
						unset($category);
					}
				}
				break;
			case 'order':
				if(!empty($params->oneProductPerRow) && empty($params->splittingDone)) {
					$els = array();
					foreach($elements as &$element){
						if(empty($element->order_product)) {
							$els[] = $element;
						} else {
							foreach($element->order_product as $product){
								$copy = hikashop_copy($element);
								$copy->current_product = $product;
								$els[] = $copy;
							}
						}
					}
					$elements = $els;
					$params->splittingDone = true;
				}
				switch($table){
					case 'address':
						if(isset($action['address'])){
							$columnsArrays['billing_address'] = $columnsArrays['address'];
							$columnsArrays['shipping_address'] = $columnsArrays['address'];
							foreach($elements as &$element){
								$element->shipping_address = array();
								$element->billing_address = array();
								if(!isset($element->address)){continue;}
								foreach($element->address as $address){
									if($address->address_id === $element->order_shipping_address_id || $address->address_id === $element->order_billing_address_id){
										if(!isset($action['shipping_address'])) $action['shipping_address'] = array();
										if(!isset($action['billing_address'])) $action['billing_address'] = array();

									}
									if($address->address_id === $element->order_shipping_address_id && $address->address_id === $element->order_billing_address_id){
										$object = new stdClass();
										foreach($address as $column=>$value){
											if(!isset($action['address'][$column])){continue;}
											$billing_column = 'billing_'.$column;
											$action['billing_address'][$billing_column] = $billing_column;
											if(isset($columnsArrays['address'][$column]))
												$columnsArrays['billing_address'][$billing_column] = $columnsArrays['address'][$column];
											$object->$billing_column=$value;
											if(!isset($types[$billing_column])) $types[$billing_column] = new stdClass();
											$types[$billing_column]->type = $types[$column]->type;
											$shipping_column = 'shipping_'.$column;
											$action['shipping_address'][$shipping_column] = $shipping_column;
											if(isset($columnsArrays['address'][$column]))
												$columnsArrays['shipping_address'][$shipping_column] = $columnsArrays['address'][$column];
											$object->$shipping_column=$value;
											if(!isset($types[$shipping_column])) $types[$shipping_column] = new stdClass();
											$types[$shipping_column]->type = $types[$column]->type;
										}
										$element->billing_address[] = $object;
										$element->shipping_address[] = $object;
									}else if($address->address_id === $element->order_shipping_address_id){
										$object = new stdClass();
										foreach($address as $column=>$value){
											if(!isset($action['address'][$column])){continue;}
											$shipping_column = 'shipping_'.$column;
											$action['shipping_address'][$shipping_column] = $shipping_column;
											if(isset($columnsArrays['address'][$column]))
												$columnsArrays['shipping_address'][$shipping_column] = $columnsArrays['address'][$column];
											$object->$shipping_column=$value;
											if(!isset($types[$shipping_column])) $types[$shipping_column] = new stdClass();
											$types[$shipping_column]->type = $types[$column]->type;
										}
										$element->shipping_address[] = $object;
									}else if($address->address_id === $element->order_billing_address_id){
										$object = new stdClass();
										foreach($address as $column=>$value){
											if(!isset($action['address'][$column])){continue;}
											$billing_column = 'billing_'.$column;
											$action['billing_address'][$billing_column] = $billing_column;
											if(isset($columnsArrays['address'][$column]))
												$columnsArrays['billing_address'][$billing_column] = $columnsArrays['address'][$column];
											$object->$billing_column = $value;
											if(!isset($types[$billing_column])) $types[$billing_column] = new stdClass();
											$types[$billing_column]->type = $types[$column]->type;
										}
										$element->billing_address[] = $object;
									}
								}
								unset($element);
							}
							unset($action['address']);
						}

						break;
					case 'order_product':
						if(isset($action['order_product'])){
							foreach($elements as &$element){
								$cpt = 1;
								if(!isset($element->order_product)){continue;}
								if(!isset($element->order_full_tax)) {
									$element->order_full_tax = new stdClass();
									$element->order_full_tax->value = 0;
									$types['order_full_tax'] = new stdClass();
									$types['order_full_tax']->type = 'price';

									if(!isset($element->order_shipping_tax->value)){
										$tmpValue = (int)$element->order_shipping_tax;
										$element->order_shipping_tax = new stdClass();
										$element->order_shipping_tax->value = $tmpValue;
									}
									if(!isset($element->order_payment_tax->value)){
										$tmpValue = (int)$element->order_payment_tax;
										$element->order_payment_tax = new stdClass();
										$element->order_payment_tax->value = $tmpValue;
									}
									if(!isset($element->order_discount_tax->value)){
										$tmpValue = (int)$element->order_discount_tax;
										$element->order_discount_tax = new stdClass();
										$element->order_discount_tax->value = $tmpValue;
									}
									$element->order_full_tax->value += (int)$element->order_shipping_tax->value + (int)$element->order_payment_tax->value - (int)$element->order_discount_tax->value;
									$element->order_full_tax->currency = $element->order_currency_id;
								}

								foreach($element->order_product as $product){
									if(is_object($element->order_full_tax) && isset($product->order_product_quantity) && isset($product->order_product_tax)){
										$element->order_full_tax->value+=round($product->order_product_quantity*$product->order_product_tax,2);
									}

									if(empty($params->oneProductPerRow)) {
										$tablename = 'product'.$cpt;

										$columnsArrays[$tablename] = $columnsArrays['order_product'];
										$object = new stdClass();
										foreach($product as $column=>$value){
											if(!isset($action['order_product'][$column])){continue;}
											$product_column = 'item'.$cpt.'_'.$column;
											$columnsArrays[$tablename][$product_column] = $columnsArrays['order_product'][$column];
											if(!isset($action[$tablename]))
												$action[$tablename] = new stdClass();
											$action[$tablename]->$product_column = $product_column;
											$object->$product_column = $value;
											$types[$product_column] = new stdClass();

											if(isset($types[$column]->type)){
												$types[$product_column]->type = $types[$column]->type;
											}else{
												$types[$product_column]->type = '';
											}
										}
										$element->$tablename = array($object);
										$cpt++;
									}
								}
								if(!empty($params->oneProductPerRow)) {
									$element->order_product = array($element->current_product);
								}
								unset($element);
							}
							if(empty($params->oneProductPerRow)) {
								unset($action['order_product']);
							}
						}
						break;
					case 'joomla_users':
						if(isset($action['joomla_users']) && isset($elements[0]->joomla_users)){
							foreach($elements as $element){
								if(isset($element->joomla_users)){
									foreach($element->joomla_users as $joomla_users){
										foreach($joomla_users as $column=>$value){
											$types[$column] = new stdClass();
											if(!isset($types[$column]->type)){
												$types[$column]->type = '';
											}
										}
									}
								}
							}
						}
						break;
				}
				break;
		}

	}

	function sortResult($table_name,$params){
		$params->types = array();
		$characteristics = array();
		$sub_ids = array('order_user_id','order_partner_id');
		$prices = array(
			'order_discount_price'=>'order_currency_id',
			'order_shipping_price'=>'order_currency_id',
			'order_shipping_tax'=>'order_currency_id',
			'order_payment_tax'=>'order_currency_id',
			'order_discount_tax'=>'order_currency_id',
			'order_payment_price'=>'order_currency_id',
			'price_value'=>'price_currency_id',
			'order_full_price'=>'order_currency_id',
			'user_partner_price'=>'user_partner_currency_id',
			'user_unpaid_amout'=>'user_currency_id'
		);
		$weights = array('product_weight'=>'product_weight_unit');
		$timestamps = array('product_sale_start','product_sale_end','product_last_seen_date','order_invoice_created');
		$methods_name = array('order_shipping_id'=>'order_shipping_method','order_payment_id'=>'order_payment_method');
		$dimensions_unit = array('product_dimension_unit');
		$dimensions = array('product_width','product_height','product_length');
		$yesnos = array('product_published','order_partner_paid','category_published','address_published','address_default','user_partner_paid',array('block','sendEmail'));
		$jdate = array();

		if(!empty($params->action['order']['order_tax_amount'])) {
			foreach($params->action['order'] as $column) {
				if(strpos($column,'order_tax_amount') !== false) {
					$prices[$column] = 'order_currency_id';
				}
			}
		}

		foreach($params->action as $key=>$table){
			foreach($table as $action){
				$type = new stdClass();
				if($action == $key.'_id' || $action == $key.'_parent_id'){
					$type->type = 'id';
				}else{
					$type->type = 'text';
				}
				$params->types[$action] = $type;
			}
		}

		foreach($params->action as $key=>$table){
			if($key === 'joomla_users'){
				foreach($table as $action){
					$params->types[$action]->type = 'joomla_users';
					$params->types[$action]->sub_type = 'text';
					foreach($jdate as $date){
						if($action === $date){
							$params->types[$action]->sub_type = 'jdate';
						}
					}
					foreach($yesnos as $yesno){
						if(is_array($yesno)){
							foreach($yesno as $y){
								if($action === $y){
									$params->types[$action]->sub_type = 'yesno';
								}
							}
						}
					}
				}
			}
		}


		foreach($params->action as $key=>$table){
			if($key==='usergroups'){
				foreach($table as $action){
					$params->types[$action]->type = 'usergroups';
				}
			}
		}

		foreach($params->action as $key=>$table){
			if($key==='joomla_users'){
				foreach($table as $action){
					$params->types[$action]->type = 'joomla_users';
				}
			}
		}

		foreach($params->elements as $element){

			foreach($prices as $price=>$currency){
				if(!isset($element->$price) || is_object($element->$price))
					continue;
				if(isset($element->$currency)){
					$params->types[$currency] = new stdClass();
					$params->types[$currency]->type = 'currency';
					if(isset($element->$price)){
						$params->types[$price] = new stdClass();
						$params->types[$price]->type = 'price';
						$p = new stdClass();
						$p->value = $element->$price;
						$p->currency = $element->$currency;
						$element->$price = $p;
					}
				}else{
					foreach($element as $array){
						if(is_array($array)){
							foreach($array as $data){
								if(isset($data->$currency)){
									$params->types[$currency] = new stdClass();
									$params->types[$currency]->type = 'currency';
									if(isset($data->$price)){
										$params->types[$price] = new stdClass();
										$params->types[$price]->type = 'price';
										$p = new stdClass();
										$p->value = $data->$price;
										$p->currency = $data->$currency;
										$data->$price = $p;
									}
								}
							}
						}
					}
				}
			}

			foreach($weights as $weight=>$unit){
				if(isset($element->$unit)){
					$params->types[$unit] = new stdClass();
					$params->types[$unit]->type = 'weight_unit';
					if(isset($element->$weight)){
						$params->types[$weight] = new stdClass();
						$params->types[$weight]->type = 'weight';
					}
				}else{
					foreach($element as $array){
						if(is_array($array)){
							foreach($array as $data){
								if(isset($data->$unit)){
									$params->types[$unit] = new stdClass();
									$params->types[$unit]->type = 'weight_unit';
									if(isset($data->$weight)){
										$params->types[$weight] = new stdClass();
										$params->types[$weight]->type = 'weight';
									}
								}
							}
						}
					}
				}
			}



			if(!empty($element)){
				foreach($element as $key=>$related){
					if($key == 'related'){
						foreach($related as $elem){
							$elem->related = new stdClass();
							$elem->related->id = $elem->related_id;
							$elem->related->name = $elem->product_name;
							$type = new stdClass();
							$type->type = 'related';
							$params->types['related'] = $type;
						}
					}else if($key == 'options'){
						foreach($related as $elem){
							$elem->options = new stdClass();
							$elem->options->id = $elem->options_id;
							$elem->options->name = $elem->product_name;
							$type = new stdClass();
							$type->type = 'options';
							$params->types['options'] = $type;
							unset($params->action['related']['options']);
							$params->action['options']['options'] = 'options';
						}
					}
				}
				foreach($element as $key=>$charac){
					if($key === 'characteristic'){
						$characteristicClass = hikashop_get('class.characteristic');
						foreach($charac as $elem){
							if(isset($elem->characteristic_parent_id) && $elem->characteristic_parent_id === '0' && isset($params->types[$elem->characteristic_value])){
								$var_name = $elem->characteristic_value;
								$characteristics[$elem->characteristic_id] = $var_name;
								if(!is_object($params->types[$var_name])) $params->types[$var_name] = new stdClass();
								$params->types[$var_name]->type = 'characteristic';
							}else if(isset($params->column) && isset($params->types[$params->column])){
								$column = $params->column;
								$characteristics[$elem->characteristic_id] = $column;
								if(!is_object($params->types[$column])) $params->types[$column] = new stdClass();
								$params->types[$column]->type = 'characteristic';
							}else if(!empty($elem->characteristic_parent_id) && !isset($characteristics[$elem->characteristic_parent_id])){
								$parentData = $characteristicClass->get($elem->characteristic_parent_id);
								if(!empty($parentData)){
									$var_name = $parentData->characteristic_value;
									$characteristics[$elem->characteristic_parent_id] = $var_name;
									if(!is_object($params->types[$var_name])) $params->types[$var_name] = new stdClass();
									$params->types[$var_name]->type = 'characteristic';
								}
							}
						}
					}
				}
			}
			foreach($timestamps as $timestamp){
				foreach($params->action as $table){
					if(isset($table[$timestamp])){
						$params->types[$timestamp]->type = 'date';
					}
				}
			}

			if($table_name == 'order'){
				if(isset($params->action['order']['order_shipping_id']) && isset($params->action['order']['order_shipping_method'])){
					$shipping = array();
					$shipping['shipping']['shipping_name'] = 'shipping_name';
					$params->action = $this->array_insert($params->action,count($params->action),$shipping);
					$params->types['shipping_name'] = new stdClass();
					$params->types['shipping_name']->type = 'method_name';
				}
				if(isset($params->action['order']['order_payment_id']) && isset($params->action['order']['order_payment_method'])){
					$payment = array();
					$payment['payment']['payment_name'] = 'payment_name';
					$params->action = $this->array_insert($params->action,count($params->action),$payment);
					$params->types['payment_name'] = new stdClass();
					$params->types['payment_name']->type = 'method_name';
				}
			}

			foreach($params->action as $key => $action){
				if(isset($action[$key.'_created'])){
					$column = $key.'_created';
					$params->types[$column]->type = 'date';
				}
				if(isset($action[$key.'_modified'])){
					$column = $key.'_modified';
					$params->types[$column]->type = 'date';
				}
			}


			if(isset($params->action['product']['product_dimension_unit'])){
				$params->types['product_dimension_unit']->type = 'dimension_unit';
			}
			$dim = new stdClass();
			foreach($dimensions as $dimension){
				if(isset($params->action[$table_name][$dimension])){
					$dimension_unit = $table_name.'_dimension_unit';
					if(is_object($element->$dimension))
						$value = $element->$dimension->value;
					else
						$value = $element->$dimension;
					$element->$dimension = new stdClass();
					$element->$dimension->unit = $element->$dimension_unit;
					$element->$dimension->value = $value;
					$params->types[$dimension]->type = 'dimension';
				}
			}

			foreach($params->action as $key => $elem){
				$path = $key.'_parent_id';
				if(isset($element->$path) && isset($elem[$path])){

					$params->types[$path]->type = 'parent';
					if(isset($element->$path->id)){
						$id = $element->$path->id;
					}else{
						$id = $element->$path;
					}
					if(isset($id) && $id != '0'){
						$row = $this->parentResults($key, $id);
						$pathElem = $key.'_name';
						$parent = new stdClass();
						$parent->id = $element->$path;
						if($row[0] != null)
							$parent->name = $row[0]->$pathElem;
						$element->$path = $parent;
					}else{
						$parent = new stdClass();
						$parent->id = '0';
						$parent->name = 'None';
						$element->$path = $parent;
					}
				}else{
					if(is_array($element)){
						foreach($element as $ele){
							if(is_array($ele)){
								foreach($ele as $data){
									if(isset($data->$path) && isset($elem[$path])){
										$id = $data->$path;
										if(isset($data->$path->id)){
											$id = $data->$path->id;
										}else{
											$id = $data->$path;
										}
										$params->types[$path]->type = 'parent';
										if($id != '0'){
											$row = $this->parentResults($key, $id);
											$pathElem = $key.'_name';

											$parent = new stdClass();
											$parent->id = $data->$path;
											if($row[0] != null)
												$parent->name = $row[0]->$pathElem;
											$data->$path = $parent;
										}else{
											$parent = new stdClass();
											$parent->id = '0';
											$parent->name = 'None';
											$data->$path = $parent;
										}
									}
								}
							}
						}
					}
				}
			}
			if(!empty($element)){
				foreach($element as $key=>$charac){
					if($key === 'characteristic'){
						foreach($charac as $elem){
							foreach($characteristics as $k => $data){
								if(isset($elem->characteristic_parent_id) && $k == $elem->characteristic_parent_id){
									$characteristic = new stdClass();
									$characteristic->name = $data;
									$characteristic->value = $elem->characteristic_value;
									$elem->exportData = $characteristic;
								}else if(isset($params->column) && $k == $elem->characteristic_id){
									$characteristic = new stdClass();
									$characteristic->name = $data;
									$characteristic->value = $elem->characteristic_value;
									$elem->exportData = $characteristic;
								}
							}
						}
					}
				}
				foreach($element as $key=>$charac){
					if($key === 'usergroups'){
						foreach($charac as $elem){
							$elem->usergroups = $elem->title;
						}
					}
				}
			}
		}

		foreach($params->action as $key=>$action){
			$column_layout = $key.'_layout';
			if(isset($params->action[$key][$column_layout])){
				$type = new stdClass();
				$type->type = 'layout';
				$params->types[$column_layout] = $type;
			}

			$column_status = $key.'_status';
			if(isset($params->action[$key][$column_status])){
				$type = new stdClass();
				$type->type = 'status';
				$params->types[$column_status] = $type;
			}

		}

		foreach($yesnos as $yesno){
			foreach($params->action as $key=>$table){
				if(!is_array($yesno) && isset($table[$yesno])){
					$params->types[$yesno]->type = 'yesno';
				}
			}
		}
		foreach($sub_ids as $sub_id){
			foreach($params->action as $table){
				if(isset($table[$sub_id])){
					$params->types[$sub_id]->type = 'sub_id';
				}
			}
		}


		if(!isset($params->lock))
			$params->lock = array();
		$params->lock['product']['product_id'] = new stdClass();
		$params->lock['product']['product_type'] = new stdClass();
		$params->lock['product']['product_parent_id'] = new stdClass();
		$params->lock['price']['price_id'] = new stdClass();
		$params->lock['price']['price_product_id'] = new stdClass();
		$params->lock['category']['category_id'] = new stdClass();
		switch($table_name){
			case 'product':
				$params->lock['product']['product_id'] = new stdClass();
				$params->lock['product']['product_id']->column = true;
				$params->lock['product']['product_id']->square = true;
				$params->lock['product']['product_id']->ids = 'all';
				$params->lock['product']['product_type'] = new stdClass();
				$params->lock['product']['product_type']->column = true;
				$params->lock['product']['product_type']->square = true;
				$params->lock['product']['product_type']->ids = 'all';
				$params->lock['product']['product_parent_id'] = new stdClass();
				$params->lock['product']['product_parent_id']->column = true;
				$params->lock['product']['product_parent_id']->square = true;
				$params->lock['product']['product_parent_id']->ids = 'all';
				$params->lock['product']['price_id'] = new stdClass();
				$params->lock['price']['price_id']->column = true;
				$params->lock['price']['price_id']->square = true;
				$params->lock['price']['price_id']->ids = 'all';
				$params->lock['product']['category_id'] = new stdClass();
				$params->lock['category']['category_id']->column = true;
				$params->lock['category']['category_id']->square = false;
				$params->lock['category']['category_id']->ids = '';
				$params->lock['product']['price_product_id'] = new stdClass();
				$params->lock['price']['price_product_id']->column = true;
				$params->lock['price']['price_product_id']->square = true;
				$params->lock['price']['price_product_id']->ids = 'all';

				foreach($params->action as $key=>$table){
					foreach($table as $action){
						if($key == 'category' && $action !='category_id'){
							$params->lock['category'][$action] = new stdClass();
							$params->lock['category'][$action]->column = true;
							$params->lock['category'][$action]->square = true;
							$params->lock['category'][$action]->ids = 'all';
						}
						if($key == 'characteristic'){
							$params->lock['characteristic'][$action] = new stdClass();
							$params->lock['characteristic'][$action]->column = true;
							$params->lock['characteristic'][$action]->square = true;
							foreach($params->elements as $element){
								if($element->product_type == 'variant'){
									$params->lock['characteristic'][$action]->ids[] = $element->product_id;
								}
							}
						}
					}
				}
				break;
			case 'category':
				$params->lock['category']['category_id'] = new stdClass();
				$params->lock['category']['category_id']->column = true;
				$params->lock['category']['category_id']->square = true;
				$params->lock['category']['category_id']->ids = 'all';
				$params->lock['category']['category_type'] = new stdClass();
				$params->lock['category']['category_type']->column = true;
				$params->lock['category']['category_type']->square = true;
				$params->lock['category']['category_type']->ids = 'all';
				$params->lock['category']['category_parent_id'] = new stdClass();
				$params->lock['category']['category_parent_id']->column = true;
				$params->lock['category']['category_parent_id']->square = false;
				$params->lock['category']['category_parent_id']->ids = '';
				$params->lock['category']['category_left'] = new stdClass();
				$params->lock['category']['category_left']->column = true;
				$params->lock['category']['category_left']->square = true;
				$params->lock['category']['category_left']->ids = 'all';
				$params->lock['category']['category_right'] = new stdClass();
				$params->lock['category']['category_right']->column = true;
				$params->lock['category']['category_right']->square = true;
				$params->lock['category']['category_right']->ids = 'all';
				$params->lock['category']['category_depth'] = new stdClass();
				$params->lock['category']['category_depth']->column = true;
				$params->lock['category']['category_depth']->square = true;
				$params->lock['category']['category_depth']->ids = 'all';
				$params->lock['category']['category_menu'] = new stdClass();
				$params->lock['category']['category_menu']->column = true;
				$params->lock['category']['category_menu']->square = true;
				$params->lock['category']['category_menu']->ids = 'all';
				break;
			case 'order':
				$params->lock['order']['order_id'] = new stdClass();
				$params->lock['order']['order_id']->column = true;
				$params->lock['order']['order_id']->square = true;
				$params->lock['order']['order_id']->ids = 'all';
				$params->lock['order']['order_type'] = new stdClass();
				$params->lock['order']['order_type']->column = true;
				$params->lock['order']['order_type']->square = true;
				$params->lock['order']['order_type']->ids = 'all';
				$params->lock['order']['order_tax_info'] = new stdClass();
				$params->lock['order']['order_tax_info']->column = true;
				$params->lock['order']['order_tax_info']->square = true;
				$params->lock['order']['order_tax_info']->ids = 'all';
				$params->lock['order']['order_tax_amount'] = new stdClass();
				$params->lock['order']['order_tax_amount']->column = true;
				$params->lock['order']['order_tax_amount']->square = true;
				$params->lock['order']['order_tax_amount']->ids = 'all';
				$params->lock['order']['order_tax_namekey'] = new stdClass();
				$params->lock['order']['order_tax_namekey']->column = true;
				$params->lock['order']['order_tax_namekey']->square = true;
				$params->lock['order']['order_tax_namekey']->ids = 'all';
				$params->lock['order']['order_full_price'] = new stdClass();
				$params->lock['order']['order_full_price']->column = true;
				$params->lock['order']['order_full_price']->square = true;
				$params->lock['order']['order_full_price']->ids = 'all';
				$params->lock['order']['order_user_id'] = new stdClass();
				$params->lock['order']['order_user_id']->column = true;
				$params->lock['order']['order_user_id']->square = false;
				$params->lock['order']['order_user_id']->ids = '';
				$params->lock['order']['order_partner_id'] = new stdClass();
				$params->lock['order']['order_partner_id']->column = true;
				$params->lock['order']['order_partner_id']->square = false;
				$params->lock['order']['order_partner_id']->ids = '';
				$params->lock['order_product']['order_product_id'] = new stdClass();
				$params->lock['order_product']['order_product_id']->column = true;
				$params->lock['order_product']['order_product_id']->square = true;
				$params->lock['order_product']['order_product_id']->ids = 'all';
				$params->lock['order_product']['product_id'] = new stdClass();
				$params->lock['order_product']['product_id']->column = true;
				$params->lock['order_product']['product_id']->square = true;
				$params->lock['order_product']['product_id']->ids = 'all';
				$params->lock['order_product']['order_id'] = new stdClass();
				$params->lock['order_product']['order_id']->column = true;
				$params->lock['order_product']['order_id']->square = true;
				$params->lock['order_product']['order_id']->ids = 'all';
				$params->lock['order_product']['order_product_name'] = new stdClass();
				$params->lock['order_product']['order_product_name']->column = true;
				$params->lock['order_product']['order_product_name']->square = true;
				$params->lock['order_product']['order_product_name']->ids = 'all';
				$params->lock['order_product']['order_product_code'] = new stdClass();
				$params->lock['order_product']['order_product_code']->column = true;
				$params->lock['order_product']['order_product_code']->square = true;
				$params->lock['order_product']['order_product_code']->ids = 'all';
				$params->lock['order_product']['order_product_price'] = new stdClass();
				$params->lock['order_product']['order_product_price']->column = true;
				$params->lock['order_product']['order_product_price']->square = true;
				$params->lock['order_product']['order_product_price']->ids = 'all';
				$params->lock['order_product']['order_product_tax_info'] = new stdClass();
				$params->lock['order_product']['order_product_tax_info']->column = true;
				$params->lock['order_product']['order_product_tax_info']->square = true;
				$params->lock['order_product']['order_product_tax_info']->ids = 'all';
				$params->lock['order_product']['order_product_option'] = new stdClass();
				$params->lock['order_product']['order_product_option']->column = true;
				$params->lock['order_product']['order_product_option']->square = true;
				$params->lock['order_product']['order_product_option']->ids = 'all';
				$params->lock['order_product']['order_product_parent_id'] = new stdClass();
				$params->lock['order_product']['order_product_parent_id']->column = true;
				$params->lock['order_product']['order_product_parent_id']->square = true;
				$params->lock['order_product']['order_product_parent_id']->ids = 'all';
				$params->lock['order_product']['order_product_wishlist_id'] = new stdClass();
				$params->lock['order_product']['order_product_wishlist_id']->column = true;
				$params->lock['order_product']['order_product_wishlist_id']->square = true;
				$params->lock['order_product']['order_product_wishlist_id']->ids = 'all';
				$params->lock['order_product']['order_product_options'] = new stdClass();
				$params->lock['order_product']['order_product_options']->column = true;
				$params->lock['order_product']['order_product_options']->square = true;
				$params->lock['order_product']['order_product_options']->ids = 'all';
				$params->lock['order_product']['order_product_tax'] = new stdClass();
				$params->lock['order_product']['order_product_tax']->column = true;
				$params->lock['order_product']['order_product_tax']->square = true;
				$params->lock['order_product']['order_product_tax']->ids = 'all';
				$params->lock['order_product']['order_product_option_parent_id'] = new stdClass();
				$params->lock['order_product']['order_product_option_parent_id']->column = true;
				$params->lock['order_product']['order_product_option_parent_id']->square = true;
				$params->lock['order_product']['order_product_option_parent_id']->ids = 'all';
				$params->lock['order_product']['order_product_shipping_id'] = new stdClass();
				$params->lock['order_product']['order_product_shipping_id']->column = true;
				$params->lock['order_product']['order_product_shipping_id']->square = true;
				$params->lock['order_product']['order_product_shipping_id']->ids = 'all';
				$params->lock['order_product']['order_product_shipping_price'] = new stdClass();
				$params->lock['order_product']['order_product_shipping_price']->column = true;
				$params->lock['order_product']['order_product_shipping_price']->square = true;
				$params->lock['order_product']['order_product_shipping_price']->ids = 'all';
				$params->lock['order_product']['order_product_shipping_tax'] = new stdClass();
				$params->lock['order_product']['order_product_shipping_tax']->column = true;
				$params->lock['order_product']['order_product_shipping_tax']->square = true;
				$params->lock['order_product']['order_product_shipping_tax']->ids = 'all';
				$params->lock['order_product']['order_product_shipping_params'] = new stdClass();
				$params->lock['order_product']['order_product_shipping_params']->column = true;
				$params->lock['order_product']['order_product_shipping_params']->square = true;
				$params->lock['order_product']['order_product_shipping_params']->ids = 'all';
				$params->lock['order_product']['order_product_shipping_method'] = new stdClass();
				$params->lock['order_product']['order_product_shipping_method']->column = true;
				$params->lock['order_product']['order_product_shipping_method']->square = true;
				$params->lock['order_product']['order_product_shipping_method']->ids = 'all';
				foreach($params->action as $key=>$table){
					foreach($table as $action){
						if($key == 'user' || $key == 'joomla_users'){
							$params->lock[$key][$action] = new stdClass();
							$params->lock[$key][$action]->column = true;
							$params->lock[$key][$action]->square = true;
							$params->lock[$key][$action]->ids = 'all';
						}
					}
				}
				break;
			case 'user':
				$params->lock['user']['user_id'] = new stdClass();
				$params->lock['user']['user_id']->column = true;
				$params->lock['user']['user_id']->square = true;
				$params->lock['user']['user_id']->ids = 'all';
				$params->lock['user']['user_cms_id'] = new stdClass();
				$params->lock['user']['user_cms_id']->column = true;
				$params->lock['user']['user_cms_id']->square = true;
				$params->lock['user']['user_cms_id']->ids = 'all';
				$params->lock['user']['params'] = new stdClass();
				$params->lock['user']['params']->column = true;
				$params->lock['user']['params']->square = false;
				$params->lock['user']['params']->ids = '';
				$params->lock['joomla_users']['params'] = new stdClass();
				$params->lock['joomla_users']['params']->column = true;
				$params->lock['joomla_users']['params']->square = false;
				$params->lock['joomla_users']['params']->ids = '';
				$params->lock['usergroups']['usergroups'] = new stdClass();
				$params->lock['usergroups']['usergroups']->column = true;
				$params->lock['usergroups']['usergroups']->square = true;
				$params->lock['usergroups']['usergroups']->ids = 'all';
				$params->lock['address']['address_id'] = new stdClass();
				$params->lock['address']['address_id']->column = true;
				$params->lock['address']['address_id']->square = true;
				$params->lock['address']['address_id']->ids = 'all';
				$params->lock['address']['address_user_id'] = new stdClass();
				$params->lock['address']['address_user_id']->column = true;
				$params->lock['address']['address_user_id']->square = true;
				$params->lock['address']['address_user_id']->ids = 'all';
				break;

			case 'address':
				$params->lock['address']['address_id'] = new stdClass();
				$params->lock['address']['address_id']->column = true;
				$params->lock['address']['address_id']->square = true;
				$params->lock['address']['address_id']->ids = 'all';
				$params->lock['address']['address_user_id'] = new stdClass();
				$params->lock['address']['address_user_id']->column = true;
				$params->lock['address']['address_user_id']->square = true;
				$params->lock['address']['address_user_id']->ids = 'all';
				$params->lock['user']['user_id'] = new stdClass();
				$params->lock['user']['user_id']->column = true;
				$params->lock['user']['user_id']->square = true;
				$params->lock['user']['user_id']->ids = 'all';
				$params->lock['user']['user_cms_id'] = new stdClass();
				$params->lock['user']['user_cms_id']->column = true;
				$params->lock['user']['user_cms_id']->square = true;
				$params->lock['user']['user_cms_id']->ids = 'all';
				$params->lock['joomla_users']['id'] = new stdClass();
				$params->lock['joomla_users']['id']->column = true;
				$params->lock['joomla_users']['id']->square = true;
				$params->lock['joomla_users']['id']->ids = 'all';
				break;

		}

		if($params->table != 'product' && $params->table != 'category' && $params->table != 'user' && $params->table != 'order' && $params->table != 'address'){
			JPluginHelper::importPlugin('hikashop');
			$app = JFactory::getApplication();
			$app->triggerEvent('onSortData'.$params->table.'MassAction',array($table_name,&$params));
		}
		return $params;
	}


	function separator($array,$data,$table){
		$separator = array();
		$separator['product']['price'] = '|';
		$separator['product']['category'] = ';';
		$separator['product']['related'] = ';';
		$separator['user']['usergroups'] = ';';
		$separator['product']['files'] = ';';
		$separator['product']['images'] = ';';
		$cpt = 0;
		$row = '';
		foreach($array as $element){
			if($cpt == 0){
				$row .= $element;
				$cpt++;
			}else{
				if(!isset($separator[$data][$table])){
					$row .= $element;
				}else{
					$row .= $separator[$data][$table].$element;
				}
			}
		}
		return $row;
	}


	function displayByType($types,$element,$column,$format=null, $export = false){
		if(!isset($element->$column))
			return '';
		$square = '';
		foreach($types as $key => $type){
			if($key === $column && isset($type->type)){
				switch($type->type){
					case 'price':
						if($element->$column->currency != '0' && hikaInput::get()->getVar('from_task','displayResults') == 'displayResults'){
							$currencyClass = hikashop_get('class.currency');
							if(!isset($element->$column->currency)){
								$config = hikashop_config();
								$element->$column->currency = $config->get('main_currency');
							}
							$square = $currencyClass->format($element->$column->value,$element->$column->currency);
						}elseif(isset($element->$column->value)){
							$square = $element->$column->value;
						}
						break;

					case 'date':
						$square = hikashop_getDate($element->$column,$format);
						break;

					case 'joomla_users':
						switch($type->sub_type){
							case 'jdate':
								$square = hikashop_getDate($element->$column,$format);
								break;
							default:
								$square = $element->$column;
								break;
						}
						break;

					case 'parent':
						$square = $element->$column->id.' : '.$element->$column->name;

						break;
					case 'dimension':
						$square = $element->$column->value.' '.$element->$column->unit;
						break;

					case 'weight':
					case 'weight_unit':
					case 'dimension_unit':
					case 'text':
						if(isset($element->$column)) {
							if($export && is_object($element->$column)) {
								if(isset($element->$column->value)) {
									$element->$column = $element->$column->value;
								}
							}
							$square = $element->$column;
						}
						break;

					case 'characteristic':
						$square = $element->exportData->value;
						break;

					case 'currency' :
						if($element->$column == '0'){
							$square = '0';
						}else{
							$currencyClass = hikashop_get('class.currency');
							$data = $currencyClass->get($element->$column);
							$square = $data->currency_code;
						}
						break;

					case 'related' :
					case 'options' :
						$square = $element->$column->id.' : '.$element->$column->name;
						break;

					default :
						if(isset($element->$column)) {
							if(strpos($type->type,'custom_') === 0) {
								$class = hikashop_get('class.field');
								static $fields = null;
								if(is_null($fields)) {
									$db	= JFactory::getDBO();
									$db->setQuery('SELECT * FROM #__hikashop_field');
									$fields = $db->loadObjectList('field_namekey');
								}
								if(!empty($fields[$column]))
									$square = $class->show($fields[$column], $element->$column);
								else
									$square = $element->$column;
							} else {
								$square = $element->$column;
							}
						}
						break;
				}
				return $square;
			}
		}
	}

	function array_insert($array, $index, $insert) {
		$key_insert = array_keys($insert);
		$key_array = array_keys($array);
		$values_insert = array_values ($insert);
		$values_array = array_values ($array);
		$k = array();
		$v = array();
		for($i = 0;$i<count($array) + count($insert);$i++){
			if($i<$index){
				$k[$i] = $key_array[$i];
			}else if($i == $index){
				$k[$i] = $key_insert[0];
			}else if($i < $index + count($insert)){
				$k[$i] = $key_insert[$i-count($array)];
			}else{
				$k[$i] = $key_array[$i-count($insert)];
			}
		}

		for($i = 0;$i<count($array) + count($insert);$i++){
			if($i<$index){
				$v[$i] = $values_array[$i];
			}else if($i == $index){
				$v[$i] = $values_insert[0];
			}else if($i < $index + count($insert)){
				$v[$i] = $values_insert[$i-count($array)];
			}else{
				$v[$i] = $values_array[$i-count($insert)];
			}
		}
		$r = array();
		for ($i = 0; $i < count ($k); $i ++) $r[$k[$i]] = $v[$i];
		return $r;
	}

	function parentResults($table,$id){
		$database	= JFactory::getDBO();
		switch($table){
			case 'category':
				$query = 'SELECT category_name, category_id';
				$query .= ' FROM '.hikashop_table('category');
				$query .= ' WHERE category_id = '.(int)$id;
				break;
			case 'product':
				$query = 'SELECT product_name, product_id';
				$query .= ' FROM '.hikashop_table('product');
				$query .= ' WHERE product_id = '.(int)$id;
				break;
			default:
				JPluginHelper::importPlugin('hikashop');
				$app = JFactory::getApplication();
				$app->triggerEvent('onLoadParentResultMassAction'.$table,array($table,$id));
				break;
		}
		if(!empty($query)){
			$database->setQuery($query);
			$rows = $database->loadObjectList();
			return $rows;
		}
		return false;
	}


	function save(&$element){
		if(isset($element->massaction_triggers) && empty($element->massaction_triggers)) $element->massaction_triggers = '';
		if(isset($element->massaction_filters) && empty($element->massaction_filters)) $element->massaction_filters = '';
		if(isset($element->massaction_actions) && empty($element->massaction_actions)) $element->massaction_actions = '';

		$app = JFactory::getApplication();

		if(!empty($element->massaction_button) && empty($element->massaction_name)) {
			$app->enqueueMessage(JText::_('BUTTON_WONT_DISPLAY_IF_NAME_EMPTY'), 'warning');
		}

		JPluginHelper::importPlugin('hikashop');
		$app->triggerEvent('onBeforeMassactionCreate',array(&$element));
		$app->triggerEvent('onBeforeMassactionUpdate',array(&$element));
		$this->prepare($element,'serialize');

		$status = parent::save($element);
		if(!$status){
			return false;
		}
		$app->triggerEvent('onAfterMassactionCreate',array(&$element));
		$app->triggerEvent('onAfterMassactionUpdate',array(&$element));
		return $status;
	}

	function get($id,$default=null){
		$element = parent::get($id);
		if($element){
			$this->prepare($element);
		}
		return $element;
	}

	function prepare(&$massaction,$action='hikashop_unserialize'){
		$vars = array('triggers','actions','filters');
		foreach($vars as $var){
			$key = 'massaction_'.$var;
			if(!empty($massaction->$key)){
				$massaction->$key = $action($massaction->$key);
			} else {
				$massaction->$key = array();
			}
		}
	}

	function trigger($trigger,$elements=array()){
		if(empty($trigger)) return false;

		static $massactions = null;
		if(!isset($massactions)){
			$this->database->setQuery('SELECT * FROM #__hikashop_massaction WHERE massaction_published=1 && massaction_triggers!=\'\'');
			$massactionsFromDB = $this->database->loadObjectList();
			$ordered = array();
			if(!empty($massactionsFromDB)){
				foreach($massactionsFromDB as $massactionFromDB){
					$this->prepare($massactionFromDB);
					if(!empty($massactionFromDB->massaction_triggers) && is_array($massactionFromDB->massaction_triggers) && count($massactionFromDB->massaction_triggers)){
						foreach($massactionFromDB->massaction_triggers as $k=>$data){
							if(!isset($ordered[$data->name])){
								$ordered[$data->name] = array();
							}
							$ordered[$data->name][] = $massactionFromDB;
						}
					}
				}
			}
			$massactions = $ordered;
		}

		if(empty($massactions[$trigger])){
			return true;
		}

		static $done = array();
		foreach($massactions[$trigger] as $massaction){
			if(isset($done[$trigger.'_'.$massaction->massaction_id])) continue;

			$done[$trigger.'_'.$massaction->massaction_id] = true;

			$this->process($massaction,$elements);

			unset($done[$trigger.'_'.$massaction->massaction_id]);
		}
		return true;
	}

	function process(&$massaction,$elements){
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();

		$query = new HikaShopQuery();
		$table = strtolower($massaction->massaction_table);
		$query->select = 'hk_'.$table.'.*';
		$query->from = '#__hikashop_'.$table.' as hk_'.$table;

		$do = true;
		$app->triggerEvent('onBeforeMassactionFilter', array(&$massaction, &$elements, $this->report, $do));
		if(!$do) return false;

		$oldElement = $elements;
		if(!empty($massaction->massaction_filters)){
			foreach($massaction->massaction_filters as $k => $filter){
				$this->report = array_merge($this->report,$app->triggerEvent('onProcess'.ucfirst($massaction->massaction_table).'MassFilter'.$filter->name,array(&$elements,&$query,&$filter->data,$k)));
			}
		}

		if(is_array($elements) && count($elements)) {
			$first = reset($elements);
			if(is_numeric($first)) {
				hikashop_toInteger($elements);
				$query->where[] = $table.'_id IN ('.implode(',', $elements).')';
				$elements = $oldElement = null;
			}
		}

		if((!is_array($elements) || !count($elements)) && empty($oldElement)){
			$query->select = array($query->select);
			$elements = $query->execute();
		}


		$do = true;
		$app->triggerEvent('onBeforeMassactionProcess', array(&$massaction, &$elements, $this->report, $do));
		if(!$do) return false;

		if(is_array($elements) && count($elements) && !empty($massaction->massaction_actions)){
			foreach($massaction->massaction_actions as $k => $action){
				$this->report = array_merge($this->report,$app->triggerEvent('onProcess'.ucfirst($massaction->massaction_table).'MassAction'.$action->name,array(&$elements,&$action->data,$k)));
			}
		}

		$app->triggerEvent('onAfterMassactionProcess', array(&$massaction, &$elements, $this->report));
		return true;
	}


	function editionSquare($data, $data_id, $table, $column, $value, $id, $type) {
		hikashop_securefield($column);
		hikashop_securefield($table);
		hikashop_securefield($data);
		switch($type) {
			case 'date':
				$value = hikashop_getTime($value);
				break;
		}
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();

		$app->triggerEvent('onSaveEditionSquareMassAction', array($data, $data_id, $table, $column, $value, $id, $type));
	}

	function checkInElement($element, $filter){
		if(is_null($element)) return false;
		if(!isset($this->datecolumns))$this->datecolumns = array();
		if(in_array($filter['type'],$this->datecolumns) && !is_numeric($filter['value'])){
			$result = strtotime($filter['value']);
			if($result)
				$filter['value'] = $result;
		}
		$in = false;
		if(in_array($filter['operator'],array('<=','>=','<','>')) && !preg_match('/^(?:\d+|\d*\.\d+)$/',$filter['value'])){
			$in = false;
		}else{
			$type = $filter['type'];
			switch($filter['operator']){
				case 'BEGINS':
					if(preg_match('/^'.$filter['value'].'/i',$element->$type)){ $in = true; }
					break;
				case 'END':
					if(preg_match('/'.$filter['value'].'$/i',$element->$type)){ $in = true; }
					break;
				case 'LIKE':
					if(preg_match('/\b'.$filter['value'].'\b/i',$element->$type)){ $in = true; }
					break;
				case 'NOT LIKE':
					if(!preg_match('/\b'.$filter['value'].'\b/i',$element->$type)){ $in = true; }
					break;
				case 'CONTAINS':
					if(preg_match('/'.$filter['value'].'/i',$element->$type)){ $in = true; }
					break;
				case 'NOTCONTAINS':
					if(!preg_match('/'.$filter['value'].'/i',$element->$type)){ $in = true; }
					break;
				case 'REGEXP':
					if(preg_match($filter['value'],$element->$type)){ $in = true;	}
					break;
				case 'NOT REGEXP':
					if(!preg_match($filter['value'],$element->$type)){ $in = true; }
					break;
				case 'IS NULL':
					if($element->$type == null){ $in = true; }
					break;
				case 'IS NOT NULL':
					if($element->$type != null){ $in = true; }
					break;
				case '>':
					if($element->$type > $filter['value']){ $in = true; }
					break;
				case '<':
					if($element->$type < $filter['value']){ $in = true; }
					break;
				case '>=':
					if($element->$type >= $filter['value']){ $in = true; }
					break;
				case '<=':
					if($element->$type <= $filter['value']){ $in = true; }
					break;
				case '!=':
					if($element->$type != $filter['value']){ $in = true; }
					break;
				default:
					if($element->$type == $filter['value']){ $in = true; }
					break;
			}
		}
		return $in;
	}
	function getRequest($filter, $prefix = ''){
		if(!isset($this->datecolumns))$this->datecolumns = array();
		if(in_array($filter['type'],$this->datecolumns) && !is_numeric($filter['value'])){
			$result = strtotime($filter['value']);
			if($result)
				$filter['value'] = $result;
		}
		$db = JFactory::getDBO();
		$filter['type'] = strip_tags($filter['type']);
		if(!in_array($filter['operator'],array('<','<=')))
			$filter['operator'] = strip_tags($filter['operator']);
		if(!empty($prefix)) $prefix = $prefix.'.';
		if((in_array($filter['operator'],array('<=','>=','<','>')) && !preg_match('/^(?:\d+|\d*\.\d+)$/',$filter['value']))){
			if(strpos($filter['value'],'{time}') !== false || strpos($filter['value'],'{year}') !== false || strpos($filter['value'],'{month}') !== false || strpos($filter['value'],'{day}') !== false){
				$query = new HikaShopQuery();
				$filter['value'] = $query->_replaceDate($filter['value']);
			} else {
				echo JText::_('WRONG_VALUE').' - ';
				return '1 = 2';
			}
		}
		$parts = explode('.', $filter['type']);
		$column = array();
		foreach($parts as $part) {
			$column[] = '`'.$part.'`';
		}
		$filter['type'] = implode('.', $column);
		switch($filter['operator']){
			case 'BEGINS':
				return $prefix.$filter['type'].' LIKE '.$db->quote($filter['value'].'%');
				break;
			case 'END':
				return $prefix.$filter['type'].' LIKE '.$db->quote('%'.$filter['value']);
				break;
			case 'CONTAINS':
				return $prefix.$filter['type'].' LIKE '.$db->quote('%'.$filter['value'].'%');
				break;
			case 'NOTCONTAINS':
				return $prefix.$filter['type'].' NOT LIKE '.$db->quote('%'.$filter['value'].'%');
				break;
			case 'IS NULL':
				return $prefix.$filter['type'].' IS NULL';
				break;
			case 'IS NOT NULL':
				return $prefix.$filter['type'].' IS NOT NULL';
				break;
			default:
				return $prefix.$filter['type'].' '.$filter['operator'].' '.$db->quote($filter['value']);
				break;
		}
	}

	function getFromFile($element, $check = false){
		$data = new stdClass();
		$app = JFactory::getApplication();
		$importHelper = hikashop_get('helper.import');
		$importFile = array();
		$elts = explode('/',$element['path']);
		if($elts == null) return false;
		$nb = count($elts);
		if($nb == 1){
			$elts = explode('\\',$element['path']);
			$nb = count($elts);
		}

		if(!file_exists($element['path'])){
			if(!$check)$app->enqueueMessage(JText::sprintf( 'NO_FILE_FOUND',$element['path']), 'error');
			$data->error = 'not_found';
			return $data;
		}

		hikashop_increasePerf();
		$contentFile = @file_get_contents($element['path']);
		if(!$contentFile){
			if(!$check)$app->enqueueMessage(JText::sprintf( 'FAIL_OPEN',$element['path']), 'error');
			$data->error = 'fail_open';
			return $data;
		};

		$fileWithBOM = (substr($contentFile,0,3) == pack("CCC",0xef,0xbb,0xbf));
		if($fileWithBOM)
			$contentFile = substr($contentFile, 3);

		$contentFile = str_replace(array("\r\n","\r"),"\n",$contentFile);
		$importLines = explode("\n", $contentFile);

		$columns = strip_tags(str_replace('"','',$importLines[0]));

		$listSeparators = array(';',',','|',"\t");
		$separator = ';';
		foreach($listSeparators as $sep){
			if(preg_match('#(?!\\\\)'.$sep.'#',$columns)){
				$separator = $sep;
				$columns=str_replace($sep,'|',$columns);
				break;
			}
		}
		unset($importLines[0]);

		if(empty($importLines)){
			if(!$check)$app->enqueueMessage('EMPTY','error');
			$data->error = 'empty';
			return $data;
		}

		$columns = explode('|',$columns);
		$numberColumns = count($columns);

		$db = JFactory::getDBO();
		$productColumns = $db->getTableColumns(hikashop_table('product'));
		$priceColumns = $db->getTableColumns(hikashop_table('price'));

		$fileColumns = $db->getTableColumns(hikashop_table('file'));

		$filesColumns = array();
		$imagesColumns = array();
		foreach($fileColumns as $fileColumn => $type){
			$filesColumns[] = str_replace('file_','files_',$fileColumn);
			$imagesColumns[] = str_replace('file_','images_',$fileColumn);
		}

		$db->setQuery('SELECT characteristic_value FROM '.hikashop_table('characteristic').' WHERE characteristic_parent_id = 0');
		$characteristicColumns = $db->loadColumn();
		$categoryColumns = array('categories_ordering','parent_category','categories_image','categories');
		$otherColumns = array('related','options','price_value_with_tax');
		if(!is_array($characteristicColumns)) $characteristicColumns = array($characteristicColumns);
		$mergedColumns = array_merge(array_keys($productColumns),array_keys($priceColumns),$categoryColumns,$characteristicColumns,$otherColumns,$filesColumns,$imagesColumns);
		$wrongColumns = array_diff($columns,$mergedColumns);

		if(!empty($wrongColumns) && $check){
			if(!$check)$app->enqueueMessage('WRONG_COLUMNS','error');
			$data->wrongColumns = $wrongColumns;
			$data->validColumns = $mergedColumns;
			$data->error = 'wrong_columns';
			return $data;
		}elseif($check){
			$data->error = 'valid';
			return $data;
		}

		if(isset($element['change']) && is_array($element['change'])){
			foreach($columns as $num => $column){
				foreach($element['change'] as $key => $value){
					if($column == $key && $value != 'delete'){
						$columns[$num] = $value;
					}
					elseif($column == $key && $value == 'delete'){
						unset($columns[$num]);
					}
				}
			}
		}

		$pool = array();
		$missingIds = array();
		$missingCodes = array();
		$key = 1;
		while($product = $this->getProduct($importLines, $key, $numberColumns, $separator)){
			if(!is_array($product)) continue;
			$newProduct = new stdClass();
			foreach($product as $num => $value){
				if(!empty($columns[$num])){
					$field = $columns[$num];
					if( strpos('|',$field) !== false ) { $field = str_replace('|','__tr__',$field); }
					$newProduct->$field = preg_replace('#^[\'" ]{1}(.*)[\'" ]{1}$#','$1',$value);
				}
			}

			$pool[$key] = new stdClass();
			if(isset($newProduct->product_id)){
				$pool[$key]->product_id = $newProduct->product_id;
			}
			if(isset($newProduct->product_code)){
				$pool[$key]->product_code = $newProduct->product_code;
			}
			if(!isset($pool[$key]->product_id) && !isset($pool[$key]->product_code)){
				continue;
			}
			if(empty($pool[$key]->product_code) && !empty($pool[$key]->product_id)){
				$missingCodes[] = $pool[$key]->product_id;
			}
			if(empty($pool[$key]->product_id)){
				$missingIds[] = $pool[$key]->product_code;
			}
		}
		$missing = array();
		if(!empty($missingCodes)){
			$db->setQuery('SELECT * FROM '.hikashop_table('product').' WHERE product_id IN ('.implode(',',$missingCodes).')');
			$missingCodes = $db->loadObjectList();
		}

		if(!empty($missingIds)){
			$codes = array();
			foreach($missingIds as $missingId){
				$codes[] = $db->quote($missingId);
			}
			$db->setQuery('SELECT * FROM '.hikashop_table('product').' WHERE product_code IN ('.implode(',',$codes).')');
			$missingIds = $db->loadObjectList();
		}

		$ids = array();
		$errorcount = 0;
		$importProducts = array();
		$toCreateProducts = array();
		$key = 1;
		while($product = $this->getProduct($importLines, $key, $numberColumns, $separator)){

			if(!is_array($product)) continue;
			$newProduct = new stdClass();
			foreach($product as $num => $value){
				if(!empty($columns[$num])){
					$field = $columns[$num];
					if( strpos('|',$field) !== false ) { $field = str_replace('|','__tr__',$field); }
					$newProduct->$field = preg_replace('#^[\'" ]{1}(.*)[\'" ]{1}$#','$1',$value);
				}
			}

			if(empty($newProduct->product_id)){
				foreach($missingIds as $missingId){
					if($newProduct->product_code == $missingId->product_code){
						$newProduct->product_id = $missingId->product_id;
						if(!isset($newProduct->product_parent_id))
							$newProduct->product_parent_id = $missingId->product_parent_id;
					}
				}
			}
			if(empty($newProduct->product_code)){
				foreach($missingCodes as $missingCode){
					if($newProduct->product_id == $missingCode->product_id){
						$newProduct->product_code = $missingCode->product_code;
					}
				}
			}

			if((empty($newProduct->product_id) && !isset($element['add'])) || (empty($newProduct->product_code) && empty($newProduct->product_id))){
				if($errorcount<20 && isset($importLine[$key])){
					$app->enqueueMessage(JText::sprintf('IMPORT_ERRORLINE',$importLines[$key]).' '.JText::_('PRODUCT_NOT_FOUND'),'notice');
					$errorcount++;
				}elseif($errorcount == 20){
					$app->enqueueMessage('...','notice');
				}
			}

			if(!empty($newProduct->product_code) && !empty($newProduct->product_id)){
				$importProducts[$newProduct->product_id] = $newProduct;
				$ids[] = $newProduct->product_id;
			}elseif(!empty($newProduct->product_code) && isset($element['add'])){
				unset($newProduct->product_id);
				$toCreateProducts[$key] = $newProduct;
			}
			unset($newProduct);
		}

		$codes = array();
		foreach($importProducts as $importProduct){
			$codes[$importProduct->product_id] = $db->quote($importProduct->product_code);
		}
		$filters = array();
		if(count($ids)){
			$filters[] = 'product_id IN('.implode(',', $ids).')';
		}
		if(count($codes)){
			$filters[] = 'product_code IN('.implode(',', $codes).')';
		}

		if(count($filters)){
			$db->setQuery('SELECT product_id, product_code FROM '.hikashop_table('product').' WHERE '.implode(' OR ', $filters).' GROUP BY product_code');
			$existingProducts = $db->loadAssocList('product_id');
		}

		$ids = array();
		foreach($importProducts as $key => $importProduct){
			$importProduct->existing = false;
			if(!empty($existingProducts)){
				foreach($existingProducts as $existingProduct){
					if($importProduct->product_code == $existingProduct['product_code']){
						$importProduct->product_id = $existingProduct['product_id'];
						$importProduct->existing = true;
					}
					if($importProduct->product_id == $existingProduct['product_id']){
						$importProduct->existing = true;
					}
				}
			}
			if(!$importProduct->existing){
				unset($importProduct->existing);
				unset($importProduct->product_id);
				array_push($toCreateProducts,$importProduct);
				unset($importProducts[$key]);
			}else{
				$ids[$importProduct->product_id] = $importProduct->product_id;
				$importProducts[$importProduct->product_id] = $importProduct;
			}
		}
		unset($importProduct);

		$duplicateds = array_diff_key($importProducts,$ids);
		foreach($duplicateds as $k => $duplicated){
			unset($importProducts[$k]);
		}

		$data->ids = $ids;
		$data->elements = $importProducts;
		$data->newProducts = $toCreateProducts;

		return $data;
	}

	function updateFiles($element, $id, $file_type = 'file'){
		$config = hikashop_config();
		$db = JFactory::getDBO();
		$csvSeparator = $config->get('csv_separator',';');

		if($file_type == 'file'){
			$regEx = $file_type.'s_';
		}elseif($file_type == 'image'){
			$file_type = 'product';
			$regEx = $file_type.'s_';
		}

		$db->setQuery('DELETE FROM '.hikashop_table('file').' WHERE file_type = '.$db->quote($file_type).' AND file_ref_id = '.(int)$id);
		$db->execute();

		$columns = array();
		$nbFiles = 0;
		foreach($element as $k => $v){
			if(!preg_match('/'.$regEx.'/U',$k))
				continue;
			else
				$columns[] = str_replace($regEx,'file_',$k);

			$tmpValues = explode($csvSeparator,$v);
			if($nbFiles < count($tmpValues))
				$nbFiles = count($tmpValues);
		}

		$emptyType = false;
		if(!in_array('file_type',$columns)){
			$emptyType = true;
			array_push($columns,'file_type');
		}

		$values = array();
		foreach($element as $k => $v){
			if(!preg_match('/'.$regEx.'/U',$k))
				continue;

			$tmpValues = explode($csvSeparator,$v);
			for($j = 0; $j <$nbFiles; $j++){
				$values[$j][$k] = $db->quote($tmpValues[$j]);
			}
		}

		$sqlVals = array();
		foreach($values as $j => $value){
			$sqlVals[$j] = implode(',',$value);
			if($emptyType)
				$sqlVals[$j] .= ','.$file_type;
		}

		$db->setQuery('REPLACE INTO '.hikashop_table('file').' ('.implode(',',$columns).') VALUES ('.implode('),(',$sqlVals).')');
		$success = $db->execute();
		return $success;
	}

	function getProduct($importLines, &$key, $numberColumns, $separator){
		$false = false;
		if(!isset($importLines[$key])  || empty($importLines[$key])){
			return $false;
		}

		$quoted = false;
		$dataPointer=0;
		$data = array('');

		while($data!==false && isset($importLines[$key]) && (count($data) < $numberColumns||$quoted)){
			$k = 0;
			$total = strlen($importLines[$key]);
			while($k < $total){
				switch($importLines[$key][$k]){
					case '"':

						if($quoted && isset($importLines[$key][$k+1]) && $importLines[$key][$k+1]=='"'){
							$data[$dataPointer].='"';
							$k++;
						}elseif($quoted){
							$quoted = false;
						}elseif(empty($data[$dataPointer])){
							$quoted = true;
						}else{
							$data[$dataPointer].='"';
						}
						break;
					case $separator:
						if(!$quoted){
							$data[]='';
							$dataPointer++;
							break;
						}
					default:
						$data[$dataPointer].=$importLines[$key][$k];
						break;
				}
				$k++;
			}

			$this->_checkLineData($data,true,$numberColumns,$importLines[$key]);

			if(count($data) < $numberColumns||$quoted){
				$data[$dataPointer].="\r\n";
			}
			$key++;
		}

		if($data!=false) $this->_checkLineData($data,true,$numberColumns,@$importLines[$key]);
		return $data;
	}

	function _checkLineData(&$data,$type=true,$numberColumns = 0, $importLine = ''){
		if($type){
			$not_ok = count($data) > $numberColumns;
		}else{
			$not_ok = count($data) != $numberColumns;
		}
		if($not_ok){
			static $errorcount = 0;
			if(empty($errorcount)){
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::sprintf('IMPORT_ARGUMENTS',$numberColumns),'error');
			}
			$errorcount++;
			if($errorcount<20){
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::sprintf('IMPORT_ERRORLINE',$importLine),'notice');
			}elseif($errorcount == 20){
				$app = JFactory::getApplication();
				$app->enqueueMessage('...','notice');
			}
		}
	}

	function updateValuesSecure(&$action,&$possibleTables = array(),&$queryTables = array()){
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();
		$tableType = explode('_',$action['type']);

		if(preg_match('/order_product/',$action['type'])) $tableType = array('order_product');
		if($tableType[0] == 'joomla') $queryTables[] = $tableType[0].'_'.$tableType[1];
		else $queryTables[] = $tableType[0];

		$mainFields = array();
		foreach($possibleTables as $possibleTable){
			if(!is_string($possibleTable)) continue;
			if(!HIKASHOP_J30){
				if(preg_match('/joomla_/',$possibleTable)){
					$fieldsTable = $db->getTableFields('#__'.str_replace('joomla_','',$possibleTable));
					$fields = reset($fieldsTable);
					$fieldsTable = $fields;
				}
				else{
					$fieldsTable = $db->getTableFields('#__hikashop_'.$possibleTable);
					$fields = reset($fieldsTable);
				}
			} else {
				if(preg_match('/joomla_/',$possibleTable)){
					$fields = $db->getTableColumns('#__'.str_replace('joomla_','',$possibleTable));
				}
				else $fields = $db->getTableColumns('#__hikashop_'.$possibleTable);
			}
			$mainFields = array_merge($mainFields,$fields);
		}

		foreach($mainFields as $key => $field){
			$field = str_replace(',','',$field);
			if($key == $action['type']){
				switch($action['operation']){
					case 'int':
						if(in_array($field,array('boolean'))){
							 $app->enqueueMessage(JText::sprintf( 'WRONG_COLUMN_TYPE', $field));
							 $queryTables = '';
						}
						break;
					case 'float':
						if(in_array($field,array('int','boolean'))){
							 $app->enqueueMessage(JText::sprintf( 'WRONG_COLUMN_TYPE', $field));
							 $queryTables = '';
						}
						break;
					case 'string':
						if(!in_array($field,array('varchar','text','char','longtext'))){
							 $app->enqueueMessage(JText::sprintf( 'WRONG_COLUMN_TYPE', $field));
							 $queryTables = '';
						}
						break;
				}
			}
		}
		if($action['operation'] == 'int'){$value = (int)$action['value'];}
		elseif($action['operation'] == 'float'){$value = (float)$action['value'];}
		elseif($action['operation'] == 'string'){$value = $db->quote($action['value']);}
		elseif($action['operation'] == 'operation'){
			$strings = array();

			$symbols = array('%','+','-','/','*','(',')',',');
			$string = preg_replace('/\'(.*)\'/U', '', $action['value']);
			$string = str_replace($symbols,'||',$string);
			$entries = explode('||',$string);
			foreach($entries as $entry){
				$data = explode('.',$entry);
				if(!isset($data[1]) || (is_numeric($data[0]) && is_numeric($data[1])))
					continue;
				$strings[]['table'] = $data[0];
				$strings[]['column'] = $data[1];
			}

			$type = 'table';
			if(!empty($mainFields)){
				foreach($strings as $string){
					if(isset($string['table']) && $type == 'table'){
						if(!in_array($string['table'], $possibleTables)){
							$app->enqueueMessage(JText::sprintf('TABLE_NOT_EXIST',$string['table']));
							$queryTables = '';
							continue;
						}
						if(!in_array($string, $queryTables)){
							$queryTables[] = 'hk_'.$string['table'];
						}
						$type = 'column';
					}elseif(isset($string['column']) && $type == 'column'){
						$colKey = array();
						foreach($mainFields as $key => $field){
							$colKey[] = $key;
						}
						if(!in_array($string['column'], $colKey)){
							$app->enqueueMessage(JText::sprintf('COLUMN_NOT_EXIST',$string['column']));
							$queryTables = '';
						}
						$type = 'table';
					}
				}
			}

			if(!preg_match('/^(?:\d+|\d*\.\d+)$/',$action['value'])){

				if(in_array($action['value'][0], array('+','-'))){
					$value = $action['type'].$action['value'];
				}
				else{
					$value = $action['value'];
					$tables = array();
					foreach($strings as $string){
						if(isset($string['table'])){
							$tables[$string['table']] = $string['table'];
						}
					}
					foreach($tables as $table){
						$value = str_replace($table.'.','hk_'.$table.'.',$value);
					}
				}
				$value = strip_tags($value);
			}
			else{
				$value = $db->quote($action['value']);
			}
		}else{$value = '';}
		return $value;
	}

	function initDefaultDiv(&$value, $key, $type, $table, &$loadedData, $html){
		if($type == 'filter'){
			if(!is_int($key)){
				$filters_html = '<div id="'.$table.$type.'__num__'.$value->name.'">';
				$filters_html .= $html.'</div>';
				$loadedData->massaction_filters[$key]->name = '';
				return $filters_html;
			}else{
				$loadedData->massaction_filters[$key]->html = '<div id="'.$table.$type.'area_'.$key.'" class="hikamassactionarea">';
				$loadedData->massaction_filters[$key]->html .= $html.'</div>';
			}
		}elseif($type == 'action'){
			if(!is_int($key)){
				$filters_html = '<div id="'.$table.$type.'__num__'.$value->name.'">';
				$filters_html .= $html.'</div>';
				$loadedData->massaction_actions[$key]->name = '';
				return $filters_html;
			}else{
				$loadedData->massaction_actions[$key]->html = '<div id="'.$table.$type.'area_'.$key.'" class="hikamassactionarea">';
				$loadedData->massaction_actions[$key]->html .= $html.'</div>';
			}


		}
	}
	function _displayResults($table_name,&$elements,&$action,$k){
		$params = new stdClass();
		$params->elements = $elements;
		$params->action = $action;
		$params->table = $table_name;

		switch($table_name){
			case 'product':
				$ids = array();
				foreach($params->elements as $element){
					array_push($ids,$element->product_id);
				}
				$params->rows_id = $ids;
				foreach($params->action as $k => $table){
					$columns = array();
					if($table == $table_name){continue;}
					foreach($table as $column){
						array_push($columns,$column);
					}
					$rows = $this->_loadResults($table_name,$k,$ids,$columns);
					if(!is_array($rows)){continue;}
					foreach($rows as $row){
						foreach($params->elements as $i => $element){
							switch($k){
								case 'price':
									if($row->price_product_id == $element->product_id)
										$params->elements[$i]->price[] = $row;
									break;
								case 'category':
									if($row->product_id == $element->product_id)
										$params->elements[$i]->category[] = $row;
									break;
								case 'characteristic':
									if($row->variant_product_id == $element->product_id)
										$params->elements[$i]->characteristic[] = $row;
									break;
								case 'related':
									if($row->product_id == $element->product_id)
										break;
									if($row->product_related_type == 'related'){
										$params->elements[$i]->related[] = $row;
									}else if($row->product_related_type == 'options'){
										if(isset($row->related_id)){
											$row->options_id = $row->related_id;
											unset($row->related_id);
										}else{
											$row->options_id = '';
										}
										$params->elements[$i]->options[] = $row;
									}
									break;
								case 'files':
									if($row->file_ref_id == $element->product_id)
										$params->elements[$i]->files[] = $row;
									break;
								case 'images':
									if($row->file_ref_id == $element->product_id)
										$params->elements[$i]->images[] = $row;
									break;
							}
						}
					}
				}
				break;
			case 'category':
				$ids = array();
				foreach($params->elements as $key=>$element){
					if($element->category_parent_id === '0'){
						unset($params->elements[$key]);
					}else{
						array_push($ids,$element->category_id);
					}
				}

				$params->rows_id = $ids;
				break;
			case 'order':
				$ids = array();
				foreach($params->elements as $element){
					array_push($ids,$element->order_id);
				}
				$params->rows_id = $ids;
				foreach($params->action as $k => $table){
					$columns = array();
					if($table != $table_name){
						foreach($table as $column){
							array_push($columns,$column);
						}
					}
					$rows = $this->_loadResults($table_name,$k,$ids,$columns);

					if(!is_array($rows)){continue;}
					foreach($rows as $row){
						foreach($params->elements as $i => $element){
							switch($k){
								case 'order':
									if($row->order_id != $element->order_id) break;
									$payment = new stdClass();
									$payment->payment_name = $row->payment_name;
									$payment->payment_id = $row->payment_id;
									$shipping = new stdClass();
									$shipping->shipping_name = $row->shipping_name;
									$shipping->shipping_id = $row->shipping_id;

									if(!isset($element->payment) || !is_array($element->payment))
										$element->payment = array();
									if(!isset($element->shipping) || !is_array($element->shipping))
										$element->shipping = array();
									$element->payment[] = $payment;
									$element->shipping[] = $shipping;

									$order_taxes = $element->order_tax_info;
									if(is_string($order_taxes))
										$order_taxes = hikashop_unserialize($order_taxes);
									if(isset($action['order']['order_full_tax'])){
										$params->elements[$i]->order_full_tax = 0;
										if(!empty($order_taxes)) {
											foreach($order_taxes as $order_tax) {
												$params->elements[$i]->order_full_tax += @$order_tax->tax_amount;
											}
										}
										$params->elements[$i]->order_full_tax = (string) $params->elements[$i]->order_full_tax;
									}
									if(isset($action['order']['order_tax_amount']) && !empty($order_taxes)){
										$j = 0;
										$params->elements[$i]->order_tax_amount = 0;
										foreach($order_taxes as $tax){
											$name = 'order_tax_amount_'.str_replace(array(' ', '(', ')', '%'), '', $tax->tax_namekey);
											$params->elements[$i]->$name = $tax->tax_amount;
											$params->elements[$i]->order_tax_amount += $tax->tax_amount;
											$params->action['order'][$name] = $name;
											$params->lock['order'][$name] = new stdClass();
											$params->lock['order'][$name]->column = true;
											$params->lock['order'][$name]->square = true;
											$params->lock['order'][$name]->ids = 'all';
											$j++;
										}

									}
									if(isset($action['order']['order_tax_namekey'])){
										if(count($order_taxes) == 1){
											$order_tax = reset($order_taxes);
											$params->elements[$i]->order_tax_namekey = $order_tax->tax_namekey;
										}else{
											$j = 0;
											$params->elements[$i]->order_tax_namekey = '';
											foreach($order_taxes as $element){
												$name = 'order_tax_namekey'.$j;
												$params->elements[$i]->$name = $element->tax_namekey;
												$params->elements[$i]->order_tax_namekey.= $element->tax_namekey. ' ';
												$params->action['order']['order_tax_namekey'.$j] = 'order_tax_namekey'.$j;
												$params->lock['order']['order_tax_namekey'.$j] = new stdClass();
												$params->lock['order']['order_tax_namekey'.$j]->column = true;
												$params->lock['order']['order_tax_namekey'.$j]->square = true;
												$params->lock['order']['order_tax_namekey'.$j]->ids = 'all';
												$j++;
											}
											trim($params->elements[$i]->order_tax_namekey);
										}
									}
									break;
								case 'order_product':
									if($row->order_id != $element->order_id) break;
									$params->elements[$i]->order_product[] = $row;
									break;
								case 'address':
									if($row->order_id != $element->order_id) break;
									$params->elements[$i]->address[] = $row;
									break;
								case 'user':
									if($row->user_id != $element->order_user_id) break;
									$params->elements[$i]->user[] = $row;
									break;
								case 'joomla_users':
									$test = false;
									foreach($element as $key=>$elem){
										if(is_array($elem) && $key=='user'){
											foreach($elem as $data){
												if($data->user_cms_id != $row->id){
													$test = true;
												}
											}
										}
									}
									if($test) break;
									if($row->order_id != $element->order_id) break;
										$params->elements[$i]->joomla_users[] = $row;
									break;
							}
						}
					}
				}
				break;
			case 'user':

				$ids = array();
				foreach($params->elements as $element){
					array_push($ids,$element->user_id);
				}
				$params->rows_id = $ids;
				foreach($params->action as $k => $table){
					$columns = array();
					if($table == $table_name){continue;}
					foreach($table as $column){
						array_push($columns,$column);
					}

					$rows = $this->_loadResults($table_name,$k,$ids,$columns);
					if(!is_array($rows)){continue;}
					foreach($rows as $row){
						foreach($params->elements as $i => $element){
							switch($k){
								case 'address':
									if($row->address_user_id != $element->user_id) break;
									$params->elements[$i]->address[] = $row;
									break;
								case 'joomla_users':
									if($row->joomla_users_id != $element->user_cms_id) break;
									$params->elements[$i]->joomla_users[] = $row;
									break;
								case 'usergroups':
									if($row->user_id != $element->user_id) break;
									$params->elements[$i]->usergroups[] = $row;
									break;
							}
						}
					}
				}
				break;
			case 'address':
				$ids = array();
				foreach($params->elements as $element){
					array_push($ids,$element->address_id);
				}
				$params->rows_id = $ids;
				foreach($params->action as $k => $table){
					$columns = array();
					if($table == $table_name){continue;}
					foreach($table as $column){
						array_push($columns,$column);
					}
					$rows = $this->_loadResults($table_name,$k,$ids,$columns);
					if(is_array($rows)){
						foreach($rows as $row){

							foreach($params->elements as $i => $element){
								switch($k){
									case 'user':
										if($row->user_id != $element->address_user_id) break;
										$params->elements[$i]->user[] = $row;
										break;
									case 'joomla_users':
										if($row->user_id != $element->address_user_id) break;
										$params->elements[$i]->joomla_users[] = $row;
										break;
								}
							}
						}
					}
				}
				break;
		}

		return $params;
	}
	function _trigger($trigger,$elements=array()){
		$this->trigger($trigger,$elements);
	}
	function _loadResults($table_name,$switch_table,$ids,$columns=null){
		$database = JFactory::getDBO();
		$query = '';
		hikashop_toInteger($ids);
		if(!in_array($switch_table, array('characteristic'))){
			foreach($columns as $column){
				hikashop_securefield($column);
			}
		}
		switch($table_name){
			case 'product':
				switch($switch_table){
					case 'price':
						$taxedPrice = false;
						$priceValue = false;
						foreach($columns as $k => $column){
							if($column == 'price_value_with_tax'){
								unset($columns[$k]);
								$taxedPrice = true;
							}
							if($column == 'price_value'){
								$priceValue = true;
							}
						}
						$addColumn = '';
						if(!$priceValue && $taxedPrice){
							if(count($columns) != 0)
								$addColumn .= ', ';
							$addColumn .= 'price_value';
						}
						$query = 'SELECT '.implode(',',$columns).$addColumn.', price_product_id, price_id';
						$query .= ' FROM '.hikashop_table('price');
						$query .= ' WHERE price_product_id IN ('.implode(',',$ids).')';
						$query .= ' ORDER BY price_id ASC';

						if($taxedPrice){
							$productClass = hikashop_get('class.product');
							$currencyHelper = hikashop_get('class.currency');
							$productClass->getProducts($ids);
							$zone_id = hikashop_getZone();
							$database->setQuery($query);
							$rows = $database->loadObjectList();
							foreach($rows as $k => $row){
								if(isset($productClass->all_products[$row->price_product_id]) && !empty($productClass->all_products[$row->price_product_id]->product_tax_id))
									$rows[$k]->price_value_with_tax = $currencyHelper->getTaxedPrice($row->price_value,$zone_id,$productClass->all_products[$row->price_product_id]->product_tax_id);
								elseif(isset($productClass->all_products[$productClass->all_products[$row->price_product_id]->product_parent_id]->product_tax_id))
									$rows[$k]->price_value_with_tax = $currencyHelper->getTaxedPrice($row->price_value,$zone_id,$productClass->all_products[$productClass->all_products[$row->price_product_id]->product_parent_id]->product_tax_id);
								else
									$rows[$k]->price_value_with_tax = $rows[$k]->price_value;

								if(!$priceValue)
									unset($rows[$k]->price_value);
							}
							$query = '';
							return $rows;
						}
						break;
					case 'category':
						$query = 'SELECT c.'.implode(',c.',$columns).',pc.product_id,c.category_id';
						$query .= ' FROM '.hikashop_table('category').' AS c';
						$query .= ' INNER JOIN '.hikashop_table('product_category').' AS pc ON pc.category_id = c.category_id';
						$query .= ' WHERE product_id IN ('.implode(',',$ids).')';
						$query .= ' ORDER BY product_category_id';
						break;
					case 'characteristic':
						$query  = 'SELECT DISTINCT characteristic.characteristic_id,characteristic.characteristic_parent_id, characteristic.characteristic_value, variant.variant_product_id FROM '.hikashop_table('characteristic').' AS characteristic ';
						$query .= ' LEFT JOIN '.hikashop_table('variant').' AS variant ON characteristic.characteristic_id = variant.variant_characteristic_id';
						$query .= ' WHERE variant_product_id IN ('.implode(',',$ids).')';
						$query .= ' ORDER BY variant.ordering';
						break;
					case 'related':
						$query = 'SELECT r.product_id ,p.product_id as \'related_id\',p.product_name, r.product_related_type';
						$query .= ' FROM '.hikashop_table('product_related').' AS r';
						$query .= ' INNER JOIN '.hikashop_table('product').' AS p ON r.product_related_id = p.product_id';
						$query .= ' WHERE r.product_id IN ('.implode(',',$ids).')';
						$query .= ' ORDER BY p.product_id ASC';
						break;
					case 'files':
						$query = 'SELECT *';
						$query .= ' FROM '.hikashop_table('file');
						$query .= ' WHERE file_ref_id IN ('.implode(',',$ids).') AND file_type = "file"';
						$query .= ' ORDER BY file_ordering, file_ref_id ASC';
						break;
					case 'images':
						$query = 'SELECT *';
						$query .= ' FROM '.hikashop_table('file');
						$query .= ' WHERE file_ref_id IN ('.implode(',',$ids).') AND file_type = "product"';
						$query .= ' ORDER BY file_ordering, file_ref_id ASC';
						break;
				}
				break;
			case 'order':
				switch($switch_table){
					case 'order':
						$query = 'SELECT o.order_id,p.payment_name,p.payment_id,s.shipping_name,s.shipping_id';
						$query .= ' FROM '.hikashop_table('order').' as o';
						$query .= ' LEFT JOIN '.hikashop_table('shipping').' as s ON o.order_shipping_id = s.shipping_id';
						$query .= ' LEFT JOIN '.hikashop_table('payment').' as p ON o.order_payment_id = p.payment_id';
						$query .= ' WHERE order_id IN ('.implode(',',$ids).')';
						$query .= ' ORDER BY order_id ASC';
						break;
					case 'order_product':
						$query = 'SELECT '.implode(',',$columns).', order_product_id, order_id';
						$query .= ' FROM '.hikashop_table('order_product');
						$query .= ' WHERE order_id IN ('.implode(',',$ids).')';
						$query .= ' ORDER BY order_product_id ASC';
						break;
					case 'address':
						foreach($columns as $k => $column){
							if(preg_match('#_state_#',$column))
								$columns[$k] = 'zone1.'.str_replace('_state','',$column).' AS '.$column;
							elseif(preg_match('#_country_#',$column))
								$columns[$k] = 'zone2.'.str_replace('_country','',$column).' AS '.$column;
							else
								$columns[$k] = 'address.'.$column;
						}
						$query = 'SELECT DISTINCT '.implode(',',$columns).', address.address_id, order1.order_id';
						$query .= ' FROM '.hikashop_table('address').' AS address';
						$query .= ' INNER JOIN '.hikashop_table('order').' AS order1 ON address.address_id = order1.order_shipping_address_id OR address.address_id = order1.order_billing_address_id';
						$query .= ' LEFT JOIN '.hikashop_table('zone').' AS zone1 ON address.address_state = zone1.zone_namekey';
						$query .= ' LEFT JOIN '.hikashop_table('zone').' AS zone2 ON address.address_country = zone2.zone_namekey';
						$query .= ' WHERE order1.order_id IN ('.implode(',',$ids).')';
						$query .= ' ORDER BY address_id ASC';
						break;
					case 'user':
						foreach($columns as $k => $column){
							$columns[$k] = 'user.'.$column;
						}
						$query = 'SELECT DISTINCT '.implode(',',$columns).', user.user_id, user.user_cms_id';
						$query .= ' FROM '.hikashop_table('user').' AS user';
						$query .= ' INNER JOIN '.hikashop_table('order').' AS _order ON user.user_id = _order.order_user_id';
						$query .= ' WHERE _order.order_id IN ('.implode(',',$ids).')';
						$query .= ' ORDER BY user_id ASC';
						break;
					case 'joomla_users':
						foreach($columns as $k => $column){
							$columns[$k] = 'user.'.$column;
						}
						$query = 'SELECT DISTINCT '.implode(',',$columns).', user.id, hk_order.order_id';
						$query .= ' FROM '.hikashop_table('users',false).' AS user';
						$query .= ' INNER JOIN '.hikashop_table('user').' AS hk_user ON user.id = hk_user.user_cms_id';
						$query .= ' INNER JOIN '.hikashop_table('order').' AS hk_order ON hk_user.user_id = hk_order.order_user_id';
						$query .= ' WHERE hk_order.order_id IN ('.implode(',',$ids).')';
						$query .= ' ORDER BY id ASC';
						break;
				}
				break;
			case 'user':
				switch($switch_table){
					case 'address':

						foreach($columns as $k => $column){
							if(preg_match('#_state_#',$column))
								$columns[$k] = 'zone1.'.str_replace('_state','',$column).' AS '.$column;
							elseif(preg_match('#_country_#',$column))
								$columns[$k] = 'zone2.'.str_replace('_country','',$column).' AS '.$column;
							else
								$columns[$k] = 'address.'.$column;
						}
						$query = 'SELECT '.implode(',',$columns).', address.address_user_id, address.address_default, address.address_id, address.address_type';
						$query .= ' FROM '.hikashop_table('address').' AS address';
						$query .= ' LEFT JOIN '.hikashop_table('zone').' AS zone1 ON address.address_state = zone1.zone_namekey';
						$query .= ' LEFT JOIN '.hikashop_table('zone').' AS zone2 ON address.address_country = zone2.zone_namekey';
						$query .= ' WHERE address.address_user_id IN ('.implode(',',$ids).') AND address.address_published = 1';
						$query .= ' ORDER BY address.address_default DESC, address.address_id DESC';

						break;
					case 'joomla_users':
						foreach($columns as $k => $column){
							$columns[$k] = 'user.'.$column;
						}
						$query = 'SELECT DISTINCT '.implode(',',$columns).', user.id as \'joomla_users_id\'';
						$query .= ' FROM '.hikashop_table('users',false).' AS user';
						$query .= ' INNER JOIN '.hikashop_table('user').' AS hk_user ON user.id = hk_user.user_cms_id';
						$query .= ' WHERE hk_user.user_id IN ('.implode(',',$ids).')';
						$query .= ' ORDER BY id ASC';
						break;
					case 'usergroups':
						$query = 'SELECT DISTINCT usergroups.title, hk_user.user_id, usergroups.id as \'usergroups_id\'';
						$query .= ' FROM '.hikashop_table('usergroups',false).' AS usergroups';
						$query .= ' INNER JOIN '.hikashop_table('user_usergroup_map',false).' AS user_usergroup ON usergroups.id = user_usergroup.group_id';
						$query .= ' INNER JOIN '.hikashop_table('users',false).' AS user ON user.id = user_usergroup.user_id';
						$query .= ' INNER JOIN '.hikashop_table('user').' AS hk_user ON user.id = hk_user.user_cms_id';
						$query .= ' WHERE hk_user.user_id IN ('.implode(',',$ids).')';
						$query .= ' ORDER BY usergroups.id ASC';
						break;
				}
				break;
			case 'address':
				switch($switch_table){
					case 'user':
						foreach($columns as $k => $column){
							$columns[$k] = 'user.'.$column;
						}
						$query = 'SELECT DISTINCT '.implode(',',$columns).', user.user_id';
						$query .= ' FROM '.hikashop_table('user').' AS user';
						$query .= ' INNER JOIN '.hikashop_table('address').' AS address ON user.user_id = address.address_user_id';
						$query .= ' WHERE address.address_id IN ('.implode(',',$ids).')';
						$query .= ' ORDER BY user_id ASC';
						break;
					case 'joomla_users':
						foreach($columns as $k => $column){
							$columns[$k] = 'user.'.$column;
						}
						$query = 'SELECT DISTINCT '.implode(', ',$columns).', user.id as \'joomla_users_id\', hk_user.user_id';
						$query .= ' FROM '.hikashop_table('users',false).' AS user';
						$query .= ' INNER JOIN '.hikashop_table('user').' AS hk_user ON user.id = hk_user.user_cms_id';
						$query .= ' INNER JOIN '.hikashop_table('address').' AS address ON hk_user.user_id = address.address_user_id';
						$query .= ' WHERE address.address_id IN ('.implode(',',$ids).')';
						$query .= ' ORDER BY id ASC';
						break;
				}
				break;
		}
		if(!empty($query)){
			$database->setQuery($query);
			$rows = $database->loadObjectList();
			return $rows;
		}
	}
	function _onProcessMassFilteraccessLevel(&$elements,&$query,$filter,$num,$type='product'){
		$column_name = $type.'_access';
		if(count($elements)){
			foreach($elements as $k => $element){
				if(empty($filter['type']) || $filter['type'] == 'IN'){
					if($element->$column_name=='all' || strpos($element->$column_name,','.$filter['group'].',')!==false){
						continue;
					}
				}else{
					if(strpos($element->$column_name,','.$filter['group'].',')===false){
						continue;
					}
				}
				unset($elements[$k]);
			}
		}else{
			$type = 'hk_'.$type;
			$operator = (empty($filter['type']) || $filter['type'] == 'IN') ? 'LIKE' : "NOT LIKE";
			$where = $type.'.'.$column_name.' '.$operator." '%,".(int)$filter['group'].",%'";
			if($operator=='LIKE')$where.= ' OR '.$type.'.'.$column_name."='all'";
			else $where.= ' AND '.$type.'.'.$column_name."!='all'";
			$query->where[] = $where;
		}
	}

	function _exportCSV($params){
		$config =& hikashop_config();
		$spreadsheetHelper = hikashop_get('helper.spreadsheet');
		$decimal_separator = $config->get('csv_decimal_separator','.');
		switch($params->formatExport){
			case 'csv':
				$format = $config->get('export_format','csv');
				$separator = $config->get('csv_separator',';');
				$force_quote = $config->get('csv_force_quote',1);
				$spreadsheetHelper->init($format, 'hikashop_export', $separator, $force_quote, $decimal_separator);
				break;
			case 'xls':
			case 'xlsx':
				$format = $params->formatExport;
				$spreadsheetHelper->init($format, 'hikashop_export',';',true, $decimal_separator);
				break;
		}

		if(!empty($params->action)){
			$row = array();
			$columnsArrays = array();
			foreach($params->action as $keyTable=>$table){
			    $component = true;
			    $tableName = $keyTable;
			    if(strpos($keyTable, 'joomla_')!==false) {
			        $component = false;
			        $tableName = str_replace('joomla_','',$keyTable);
			    }
				if($keyTable == 'files' || $keyTable == 'images')
					$tableName = 'file';
				if($keyTable == 'related' || $keyTable == 'options')
					$tableName = 'product_related';
			    $db = JFactory::getDBO();
			    if(!HIKASHOP_J30){
			        $columnsTable = $db->getTableFields(hikashop_table($tableName, $component));
			        $columnsArrays[$keyTable] = reset($columnsTable);
			    } else {
			        $columnsArrays[$keyTable] = $db->getTableColumns(hikashop_table($tableName, $component));
			    }

				$this->organizeExportColumn($params,$keyTable, $columnsArrays);
			}

			$types = array();
			foreach($params->action as $keyTable=>$table){
				foreach($table as $column){
					if($keyTable == 'files' || $keyTable == 'images')
						$column = str_replace('file',$keyTable,$column);
					$row[] = $column;

					if(isset($columnsArrays[$keyTable][$column])) {
						if(!empty($params->types[$column]->type) && $params->types[$column]->type == 'date')
							$types[] = 'text';
						elseif(in_array($columnsArrays[$keyTable][$column], array('varchar', 'text', 'longtext', 'datetime')))
							$types[] = 'text';
						else
							$types[] = 'number';
					} else {
						$types[] = 'auto';
					}
				}

			}

			$spreadsheetHelper->writeLine($row);
			$spreadsheetHelper->setTypes($types);
		}

		hikaInput::get()->set('from_task','exportCsv');
		if(!empty($params->action)){
			if(!isset($params->action['date_format']) || empty($params->action['date_format']))
				$params->action['date_format'] = JText::_('HIKASHOP_DATE_FORMAT');
			foreach($params->elements as $k1=>$element){
				$row = array();
				$multi = array();
				foreach($params->action as $key=>$table){
					if(!is_array($table) && !is_object($table)) continue;
					foreach($table as $column){
						$find = false;
						$square = '';
						$result = $this->displayByType($params->types,$element,$column,$params->action['date_format'], true );
						if(isset($element->$column) && ($key===$k1 || $key===$params->table) && (is_string($result) || is_numeric($result))){
							$square .= $result;
							$find = true;
						}else{
							$r = array();
							foreach($element as $k=>$elem){
								if(!is_array($elem)){continue;}
								foreach($elem as $data){
									if(!isset($data->$column)){
										if(isset($data->exportData->value) && $data->exportData->name == $column){
											$r[] = $data->exportData->value;
											$find = true;
										}
										continue;
									}
									if($k != $key){continue;}
									$r[] = $this->displayByType($params->types,$data,$column,$params->action['date_format'], true);
									$find = true;
								}
							}
							$multi[] = count($row);
							$square .= $this->separator($r,$params->table,$key);
						}
						if(!$find){
							$row[]='';
						}else{
							$row[]=$square;
						}
					}
				}
				$spreadsheetHelper->writeLine($row, $multi);
			}
		}
		if(empty($params->path)){
			hikashop_cleanBuffers();
			$spreadsheetHelper->send();
		}else{
			$fileClass = hikashop_get('class.file');
			$name = '';

			$path = explode(DS,$params->path);
			$name = $path[count($path)-1];
			unset($path[count($path)-1]);
			$params->path = implode(DS,$path);
			$uploadFolder = rtrim(JPath::clean(html_entity_decode($params->path)), DS.' ').DS;
			if(!preg_match('#^([A-Z]:)?/.*#',$uploadFolder)) {
				if(!$uploadFolder[0]=='/' || !is_dir($uploadFolder)) {
					$uploadFolder = JPath::clean($fileClass->getPath('file').trim($uploadFolder, DS.' ').DS);
				}
			}
			if(strstr($name,'.')){
				$data = $spreadsheetHelper->get();
				JFile::write($uploadFolder.$name, $data);
			}
		}
	}

	function setExportPaths($path){
		$withTime = false;
		if(preg_match('#{time}#',$path)){
			$withTime = true;
			$path = str_replace('{time}',hikashop_getDate(time()),$path);
		}

		$path = str_replace(array(' ',':'),array('_','-'),trim($path));

		$oServerUrl = str_replace('administrator','',getcwd());
		$webUrl = JURI::root();
		if(preg_match('#'.preg_quote($oServerUrl,'\\').'#',$path)){
			$webUrl = $webUrl.preg_replace('#'.preg_quote($oServerUrl,'\\').'#','',$path);
			$serverUrl = $path;

		}else{
			if(in_array($path[0],array('/','\\'))){
				if(in_array(substr($oServerUrl,-1),array('/','\\')))
					$oServerUrl = substr($oServerUrl,0,-1);
				if(in_array(substr($webUrl,-1),array('/','\\')))
					$webUrl = substr($webUrl,0,-1);
			}else{
				if(!in_array(substr($oServerUrl,-1),array('/','\\')))
					$oServerUrl = $oServerUrl.'\\';
				if(!in_array(substr($webUrl,-1),array('/','\\')))
					$webUrl = $webUrl.'/';
			}
			$serverUrl = $oServerUrl.$path;
			$webUrl = str_replace('\\','/',$webUrl.$path);
		}

		if(strpos($oServerUrl,'/') !== false)
			$serverUrl = str_replace('\\','/',$serverUrl);
		else
			$serverUrl = str_replace('/','\\',$serverUrl);
		$webUrl = str_replace('\\','/',$webUrl);

		if($withTime && file_exists($path)){
			$path_parts = pathinfo($path);
			for($i = 1; $i > 0; $i++){
				$tmpPath = str_replace('.'.$path_parts['extension'],'',$path);
				if(!file_exists($tmpPath.'('.$i.')')){
					$webUrl = str_replace('.'.$path_parts['extension'],'',$webUrl).'('.$i.').'.$path_parts['extension'];
					$serverUrl = str_replace('.'.$path_parts['extension'],'',$serverUrl).'('.$i.').'.$path_parts['extension'];
					break;
				}
			}
		}

		return array('server'=>$serverUrl, 'web'=>$webUrl);
	}
}


class HikaShopQuery {
	var $leftjoin = array();
	var $join = array();
	var $where = array();
	var $from = '#__hikashop_product AS hk_product';
	var $select = array('product.*');
	var $start = 0;
	var $value = 500;
	var $ordering = array();
	var $direction = '';

	function __construct() {
		$this->db = JFactory::getDBO();
	}

	function count($type = 'hk_product.product_id') {
		$myquery = $this->getQuery(array('COUNT(DISTINCT '.$type.')'));
		$this->db->setQuery($myquery);
		return $this->db->loadResult();
	}

	function execute() {
		$this->db->setQuery($this->getQuery(),$this->start,$this->value);
		return $this->db->loadObjectList();
	}

	function getQuery($select = array()){
		$query = '';
		if(!empty($select))	$query .= ' SELECT '.implode(',',$select);
		elseif(!empty($this->select)) $query .= ' SELECT '.implode(',',$this->select);
		if(!empty($this->from)) $query .= ' FROM '.$this->from;
		if(!empty($this->join)) $query .= ' JOIN '.implode(' JOIN ',$this->join);
		if(!empty($this->leftjoin)) $query .= ' LEFT JOIN '.implode(' LEFT JOIN ',$this->leftjoin);
		if(!empty($this->where)) $query .= ' WHERE ('.implode(') AND (',$this->where).')';
		if(!empty($this->ordering)) $query .= ' ORDER BY '.implode(',',$this->ordering);
		if(!empty($this->direction) && is_string($this->direction)) $query .= ' '.$this->direction;
		if(!empty($this->group) && is_string($this->group)) $query .= ' GROUP BY '.$this->group;

		return $query;
	}

	function convertQuery($as,$column,$operator,$value){
		if($operator == 'CONTAINS'){
			$operator = 'LIKE';
			$value = '%'.$value.'%';
		}elseif($operator == 'BEGINS'){
			$operator = 'LIKE';
			$value = $value.'%';
		}elseif($operator == 'END'){
			$operator = 'LIKE';
			$value = '%'.$value;
		}elseif($operator == 'NOTCONTAINS'){
			$operator = 'NOT LIKE';
			$value = '%'.$value.'%';
		}elseif(!in_array($operator,array('REGEXP','NOT REGEXP','IS NULL','IS NOT NULL','NOT LIKE','LIKE','=','!=','>','<','>=','<='))){
			die('Operator not safe : '.$operator);
		}

		$isUnixTimeStamp = strpos($value,'{time}') !== false;
		$value = $this->_replaceDate($value);
		 if($isUnixTimeStamp) {
			 $value = hikashop_getDate($value);
		 }

		if(!is_numeric($value) OR in_array($operator,array('REGEXP','NOT REGEXP','NOT LIKE','LIKE'))){
			$value = $this->db->Quote($value);
		}

		if(in_array($operator,array('IS NULL','IS NOT NULL'))){
			$value = '';
		}

		return $as.'.`'.hikashop_secureField($column).'` '.$operator.' '.$value;
	}

	function _replaceDate($mydate) {
		$tagFound = false;
		if(strpos($mydate,'{time}') !== false) {
			$mydate = str_replace('{time}',time(),$mydate);
			$tagFound = true;
		}
		if(strpos($mydate,'{year}') !== false) {
			$mydate = str_replace('{year}',date('Y'),$mydate);
			$tagFound = true;
		}
		if(strpos($mydate,'{month}') !== false) {
			$mydate = str_replace('{month}',date('m'),$mydate);
			$tagFound = true;
		}
		if(strpos($mydate,'{day}') !== false) {
			$mydate = str_replace('{day}',date('d'),$mydate);
			$tagFound = true;
		}
		if(!$tagFound)
			return $mydate;
		$operators = array('+','-');
		foreach($operators as $oneOperator) {
			if(!strpos($mydate, $oneOperator))
				continue;

			list($part1,$part2) = explode($oneOperator,$mydate);
			if($oneOperator == '+') {
				$mydate = trim($part1) + trim($part2);
			} elseif($oneOperator == '-') {
				$mydate = trim($part1) - trim($part2);
			}
		}
		return $mydate;
	}
}
