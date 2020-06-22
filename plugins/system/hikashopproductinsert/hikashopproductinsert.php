<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class plgSystemHikashopproductInsert extends JPlugin {

	var $name = 0;
	var $pricetax = 0;
	var $pricedis = 0;
	var $cart = 0;
	var $quantityfield = 0;
	var $description = 0;
	var $picture = 0;
	var $link = 0;
	var $border = 0;
	var $badge = 0;
	var $price = 0;

	function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
		if(isset($this->params))
			return;

		$plugin = JPluginHelper::getPlugin('system', 'hikashopproductinsert');
		if(version_compare(JVERSION,'2.5','<')){
			jimport('joomla.html.parameter');
			$this->params = new JParameter($plugin->params);
		} else {
			$this->params = new JRegistry($plugin->params);
		}
	}

	function escape($str) {
		return htmlspecialchars($str, ENT_COMPAT, 'UTF-8');
	}

	function onAfterRoute() {

		$load = $this->params->get('load_hikashop_on_all_frontend_pages', 0);
		if(!$load)
			return;

		$app = JFactory::getApplication();

		if(version_compare(JVERSION,'3.0','>=')) {
			$layout = $app->input->getString('layout');
			$ctrl = $app->input->getString('ctrl');
			$task = $app->input->getString('task');
			$function = $app->input->getString('function');
		} else {
			$layout = JRequest::getString('layout');
			$ctrl = JRequest::getString('ctrl');
			$task = JRequest::getString('task');
			$function = JRequest::getString('function');
		}
		if(version_compare(JVERSION,'4.0','>=')) {
			$admin = $app->isClient('administrator');
		} else {
			$admin = $app->isAdmin();
		}

		if($admin)
			return true;

		if($layout == 'edit' || $ctrl == 'plugins' && $task == 'trigger' && $function == 'productDisplay')
			return true;

		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php'))
			return true;

		JPluginHelper::importPlugin('hikashop');
	}

	function onAfterRender() {
		$app = JFactory::getApplication();

		if(version_compare(JVERSION,'3.0','>=')) {
			$layout = $app->input->getString('layout');
			$ctrl = $app->input->getString('ctrl');
			$task = $app->input->getString('task');
			$function = $app->input->getString('function');
		} else {
			$layout = JRequest::getString('layout');
			$ctrl = JRequest::getString('ctrl');
			$task = JRequest::getString('task');
			$function = JRequest::getString('function');
		}
		if(version_compare(JVERSION,'4.0','>=')) {
			$admin = $app->isClient('administrator');
		} else {
			$admin = $app->isAdmin();
		}

		if($admin)
			return true;

		if($layout == 'edit' || $ctrl == 'plugins' && $task == 'trigger' && $function == 'productDisplay')
			return true;

		$body = null;
		if(class_exists('JResponse'))
			$body = JResponse::getBody();
		$alternate_body = false;
		if(empty($body) && method_exists($app,'getBody')) {
			$body = $app->getBody();
			$alternate_body = true;
		}

		$search_space = substr($body,strpos($body,'<body'));
		if( ! (preg_match_all('#\{product\}(.*)\{\/product\}#Uis', $search_space, $matches) || preg_match_all('#\{product (.*)\}#Uis', $search_space, $matches)) )
			return;

		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php'))
			return true;

		JPluginHelper::importPlugin('hikashop');

		$db = JFactory::getDBO();
		$currencyClass = hikashop_get('class.currency');
		$this->image = hikashop_get('helper.image');
		$this->classbadge = hikashop_get('class.badge');
		$para = array();
		$nbtag = count($matches[1]);
		for($i = 0; $i < $nbtag; $i++) {
			$para[$i] = explode('|', $matches[1][$i]);
		}

		$k = 0;
		$ids = array();
		for($i = 0; $i < $nbtag; $i++) {
			for($u = 0; $u < count($para[$i]); $u++) {
				if(in_array($para[$i][$u], array('name', 'pricetax', 'pricedis', 'cart', 'quantityfield', 'description', 'link', 'border', 'badge', 'picture')))
					continue;

				$ids[$k]= (int)$para[$i][$u];
				$k++;
			}
		}

		$product_query = 'SELECT * FROM ' . hikashop_table('product') . ' WHERE product_id IN (' . implode(',', $ids) . ') AND product_access='.$db->quote('all').' AND product_published=1';
		$db->setQuery($product_query);
		$products = $db->loadObjectList();

		$db->setQuery('SELECT * FROM '.hikashop_table('variant').' AS v LEFT JOIN '.hikashop_table('characteristic').' AS c ON v.variant_characteristic_id = c.characteristic_id WHERE variant_product_id IN ('.implode(',',$ids).')');
		$variants = $db->loadObjectList();

		$parent_ids = array();

		foreach($products as $product) {
			$product->characteristic_name = false;

			if($product->product_type == 'variant'){
				$product->has_options = false;
				$parent_ids[] = $product->product_parent_id;
			}

			if(empty($variants))
				continue;

			foreach($variants as $variant){
				if($product->product_id == $variant->variant_product_id && $product->product_type == 'main') {
					$product->has_options = true;
					break;
				}
				if($product->product_id == $variant->variant_product_id && $product->product_type == 'variant') {
					if(empty($product->product_name)){
						$product->product_name = $variant->characteristic_value;
						$product->characteristic_name = true;
					}
					break;
				}
			}
		}


		if(!empty($parent_ids)){
			$productClass = hikashop_get('class.product');
			$productClass->getProducts($parent_ids);

			foreach($products as $product){
				if($product->product_type == 'variant' && isset($product->product_parent_id) && in_array($product->product_parent_id, $parent_ids)){
					if(!isset($productClass->products[$product->product_parent_id]))
						continue;

					if($product->characteristic_name)
						$product->product_name = $productClass->products[$product->product_parent_id]->product_name.': ' . $product->product_name;
					if(empty($product->product_description))
						$product->product_description = $productClass->products[$product->product_parent_id]->product_description;
					if(empty($product->product_tax_id))
						$product->product_tax_id = $productClass->products[$product->product_parent_id]->product_tax_id;
				}
			}
		}

		$db->setQuery('SELECT product_id FROM '.hikashop_table('product_related').' WHERE product_related_type = '.$db->quote('options').' AND product_id IN ('.implode(',',$ids).')');
		$options = $db->loadObjectList();
		if(!empty($options)) {
			foreach($products as $k => $product) {
				foreach($options as $option) {
					if($product->product_id == $option->product_id) {
						$products[$k]->has_options = true;
						break;
					}
				}
			}
		}

		foreach($products as $k => $product) {
			$this->classbadge->loadBadges($products[$k]);
		}

		$queryImage = 'SELECT * FROM ' . hikashop_table('file') . ' WHERE file_ref_id IN (' . implode(',', $ids) . ') AND file_type=\'product\' ORDER BY file_ordering ASC, file_id ASC';
		$db->setQuery($queryImage);
		$images = $db->loadObjectList();
		$productClass = hikashop_get('class.product');
		foreach($products as $k => $row) {
			$productClass->addAlias($products[$k]);
			foreach($images as $j => $image) {
				if($row->product_id != $image->file_ref_id)
					continue;

				foreach(get_object_vars($image) as $key => $name) {
					if(!isset($products[$k]->images))
						$products[$k]->images = array();
					if(!isset($products[$k]->images[$j]))
						$products[$k]->images[$j] = new stdClass();

					$products[$k]->images[$j]->$key = $name;
				}
			}
		}

		$zone_id = hikashop_getZone();
		$currencyClass = hikashop_get('class.currency');
		$config = hikashop_config();
		$defaultParams = $config->get('default_params');
		$currencyClass->getListingPrices($products,$zone_id,hikashop_getCurrency(),$defaultParams['price_display_type']);

		$fields = array(
			'name' => 'name',
			'pricedis1' => 'pricedis',
			'pricedis2' => array('pricedis', 2),
			'pricedis3' => array('pricedis', 3),
			'pricetax1' => 'pricetax',
			'pricetax2' => array('pricetax', 2),
			'price' => 'price',
			'cart' => 'cart',
			'quantityfield' => 'quantityfield',
			'description' => 'description',
			'picture' => 'picture',
			'link' => 'link',
			'border' => 'border',
			'badge' => 'badge',
		);

		for($i = 0; $i < $nbtag; $i++) {
			$nbprodtag = count($para[$i]);

			foreach($fields as $k => $v) {
				if(is_string($v))
					$this->$v = 0;

				if(in_array($k, $para[$i])) {
					if(is_array($v))
						$this->{ $v[0] } = $v[1];
					else
						$this->$v = 1;

					$nbprodtag--;
				}
			}

			$this->menuid = 0;
			foreach($para[$i] as $key => $value){
				if(substr($value, 0, 6) == "menuid") {
					$explode = explode(':', $value);
					$this->menuid = $explode[1];
				}
			}

			$id = array();
			for($j = 0; $j < $nbprodtag; $j++) {
				$id[$j] = $para[$i][$j];
			}

			$name = 'hikashopproductinsert_view.php';
			$path = JPATH_THEMES.DS.$app->getTemplate().DS.'system'.DS.$name;
			if(!file_exists($path)) {
				if(version_compare(JVERSION,'1.6','<'))
					$path = JPATH_PLUGINS .DS.'system'.DS.$name;
				else
					$path = JPATH_PLUGINS .DS.'system'.DS.'hikashopproductinsert'.DS.$name;

				if(!file_exists($path))
					return true;
			}

			ob_start();
			require($path);
			$product_view = ob_get_clean();

			$pattern = '#\{product\}(.*)\{\/product\}#Uis';
			$replacement = '';

			if(class_exists('JResponse'))
				$body = JResponse::getBody();
			$alternate_body = false;
			if(empty($body)) {
				$body = $app->getBody();
				$alternate_body = true;
			}
			$search_space = substr($body,strpos($body,'<body'));
			$new_search_space = preg_replace($pattern, str_replace('$','\$',$product_view), $search_space, 1);

			$pattern = '#\{product (.*)\}#Uis';
			$replacement = '';
			$new_search_space = preg_replace($pattern, str_replace('$','\$',$product_view), $new_search_space, 1);

			$body = str_replace($search_space,$new_search_space,$body);

			if($alternate_body) {
				$app->setBody($body);
			} else {
				JResponse::setBody($body);
			}
		}
	}
}
