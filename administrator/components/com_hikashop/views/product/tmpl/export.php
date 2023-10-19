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
hikashop_cleanBuffers();

$config =& hikashop_config();
$format = $config->get('export_format','csv');
$separator = $config->get('csv_separator',';');
$force_quote = $config->get('csv_force_quote',1);
$force_text = $config->get('csv_force_text', false);
$decimal_separator = $config->get('csv_decimal_separator','.');

$export = hikashop_get('helper.spreadsheet');
$export->init($format, 'hikashopexport', $separator, $force_quote, $decimal_separator, $force_text);

$characteristic = hikashop_get('class.characteristic');
$classProduct = hikashop_get('class.product');
$characteristic->loadConversionTables($this);

$db = JFactory::getDBO();
if(!HIKASHOP_J30){
	$columnsTable = $db->getTableFields(hikashop_table('product'));
	$columnsArray = reset($columnsTable);
} else {
	$columnsArray = $db->getTableColumns(hikashop_table('product'));
}

$columnsArray['categories_ordering'] = 'varchar';
$columnsArray['product_parent_id'] = 'varchar';
$columnsArray['product_manufacturer_id'] = 'varchar';

$types = array();

foreach($columnsArray as $type) {
	if(in_array($type, array('varchar', 'text', 'longtext')))
		$types[] = 'text';
	else
		$types[] = 'number';
}


$columns = $products_columns = array_keys($columnsArray);
$product_table_count = count($columns);

$columns = array_merge($columns, array(
	'parent_category' => 'parent_category',
	'categories_image' => 'categories_image',
	'categories' => 'categories',
	'price_value' => 'price_value',
	'price_currency_id' => 'price_currency_id',
	'price_min_quantity' => 'price_min_quantity',
	'price_access' => 'price_access',
	'price_users' => 'price_users',
	'files' => 'files',
	'images' => 'images',
	'related' => 'related',
	'options' => 'options',
	'bundle' => 'bundle',
	'bundle_quantity' => 'bundle_quantity'
));

$characteristicsColumns = array();
if(!empty($this->characteristics)) {
	foreach($this->characteristics as $characteristic) {
		if(empty($characteristic->characteristic_parent_id)) {
			if(!empty($characteristic->characteristic_alias)){
				$characteristic->characteristic_value = $characteristic->characteristic_alias;
			}
			$columns['char_'.$characteristic->characteristic_id] = $characteristic->characteristic_value;
			$characteristicsColumns['char_'.$characteristic->characteristic_id] = $characteristic->characteristic_value;
		}
	}
}
$after_category_count = count($columns)-($product_table_count+3);
$export->writeline($columns);
$export->setTypes($types);

if(!empty($this->categories)) {
	foreach($this->categories as $category) {
		$data = array();
		for($i = 0; $i < $product_table_count; $i++)
			$data[] = '';
		if(!empty($category->category_parent_id) && isset($this->categories[$category->category_parent_id]))
			$data[] = $this->categories[$category->category_parent_id]->category_name;
		else
			$data[] = '';
		if(!empty($category->file_path))
			 $data[] = $category->file_path;
		else
			$data[] = '';

		$data[] = $category->category_name;
		for($i = 0; $i < $after_category_count; $i++)
			$data[] = '';
		$export->writeline($data);
	}
}

if(!empty($this->products)) {
	foreach($this->products as $k => $product) {
		if($product->product_type == 'variant' && !empty($product->product_parent_id) && !empty($this->products[$product->product_parent_id]))
			$this->products[$k]->product_parent_id = $this->products[$product->product_parent_id]->product_code;
	}
	foreach($this->products as $product) {
		$data = array();

		if(!empty($product->product_manufacturer_id) && !empty($this->brands[$product->product_manufacturer_id]))
			$product->product_manufacturer_id = $this->brands[$product->product_manufacturer_id]->category_name;
		else
			$product->product_manufacturer_id = '';

		foreach($products_columns as $column) {
			if(!empty($product->$column) && is_array($product->$column))
				$product->$column = implode($separator,$product->$column);
			$data[] = @$product->$column;
		}

		$categories = array();
		if(!empty($product->categories)) {
			foreach($product->categories as $category) {
				if(!empty($this->categories[$category]))
					$categories[] = $this->categories[$category]->category_name;
			}
		}
		$data[] = '';
		if(!empty($categories)){
			if(count($categories)>1){
				$data[] = '';
			}else{
				if(isset($categories[0]->category_parent_id)){
					$parent_id = $categories[0]->category_parent_id;
					$data[] = $this->categories[$parent_id]->category_name;
				}else{
					$data[] = '';
				}
			}
			$data[] = implode($separator,$categories);
		}else{
			$data[] = '';
			$data[] = '';
		}

		$values = array();
		$codes = array();
		$qtys = array();
		$accesses = array();
		$users = array();
		if(!empty($product->prices)) {
			foreach($product->prices as $price) {
				$floatValue = (float)hikashop_toFloat($price->price_value);
				if($floatValue == (int)$floatValue)
					$price_value = (int)$floatValue;
				else
					$price_value = number_format($floatValue, 5, '.', '');
				$values[] = $price_value;
				$codes[] = $this->currencies[$price->price_currency_id]->currency_code;
				$qtys[] = $price->price_min_quantity;
				$accesses[] = $price->price_access;
				$users[] = $price->price_users;
			}

		}
		if(empty($values)) {
			$data[] = '';
			$data[] = '';
			$data[] = '';
			$data[] = '';
			$data[] = '';
		} else {
			$data[] = implode('|', $values);
			$data[] = implode('|', $codes);
			$data[] = implode('|', $qtys);
			$data[] = implode('|', $accesses);
			$data[] = implode('|', $users);
		}

		$files = array();
		if(!empty($product->files)) {
			foreach($product->files as $file) {
				$files[] = $file->file_path;
			}
		}
		if(empty($files)) {
			$data[] = '';
		} else {
			$data[] = implode($separator, $files);
		}

		$images = array();
		if(!empty($product->images)) {
			foreach($product->images as $image) {
				$images[] = $image->file_path;
			}
		}
		if(empty($images)) {
			$data[] = '';
		} else {
			$data[] = implode($separator, $images);
		}

		$related = array();
		if(!empty($product->related)) {
			foreach($product->related as $rel) {
				if(!isset($this->products[$rel]->product_code)) $this->products[$rel] = $classProduct->get($rel);
				$related[] = @$this->products[$rel]->product_code;
			}
		}
		if(empty($related)) {
			$data[] = '';
		} else {
			$data[] = implode($separator, $related);
		}

		$options = array();
		if(!empty($product->options)) {
			foreach($product->options as $rel) {
				if(!isset($this->products[$rel]->product_code)) $this->products[$rel] = $classProduct->get($rel);
				$options[] = @$this->products[$rel]->product_code;
			}
		}
		if(empty($options)) {
			$data[] = '';
		} else {
			$data[] = implode($separator, $options);
		}

		$bundle = array();
		$bundle_quantity = array();
		if(!empty($product->bundle)) {
			foreach($product->bundle as $k => $rel) {
				if(!isset($this->products[$rel]->product_code)) $this->products[$rel] = $classProduct->get($rel);
				$bundle[] = @$this->products[$rel]->product_code;
				$qty = 0;
				if(!empty($product->bundle_quantity[$k]))
					$qty = $product->bundle_quantity[$k];
				$bundle_quantity[] = $qty;
			}
		}
		if(empty($bundle)) {
			$data[] = '';
			$data[] = '';
		} else {
			$data[] = implode($separator, $bundle);
			$data[] = implode($separator, $bundle_quantity);
		}

		if(!empty($product->variant_links)) {
			$characteristics = array();
			if(!empty($characteristicsColumns)) {
				foreach($product->variant_links as $char_id) {
					if(!empty($this->characteristics[$char_id])) {
						$char = $this->characteristics[$char_id];
						if(!empty($this->characteristics[$char->characteristic_parent_id])) {
							$characteristics['char_'.$char->characteristic_parent_id] = $char->characteristic_value;
						}
					}
				}
				foreach($characteristicsColumns as $key => $characteristic){
					$data[] = @$characteristics[$key];
				}
			}
		} elseif(!empty($characteristicsColumns)) {
			for($i = 0; $i < count($characteristicsColumns); $i++) {
				$data[] = '';
			}
		}
		$export->writeLine($data);
	}
}

$export->send();
exit;
