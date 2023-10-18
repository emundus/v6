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
class plgSystemHikashopremarketing extends JPlugin
{
	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
		if(isset($this->params))
			return;

		$plugin = JPluginHelper::getPlugin('system', 'hikashopremarketing');
		$this->params = new JRegistry($plugin->params);
	}

	public function onAfterRender() {
		$adwords_id = $this->params->get('adwordsid', 0);
		if (empty($adwords_id) || $adwords_id == 0)
			return true;

		$app = JFactory::getApplication();
		if(version_compare(JVERSION,'4.0','>=') && $app->isClient('administrator'))
			return true;
		if(version_compare(JVERSION,'4.0','<') && $app->isAdmin())
			return true;

		if(version_compare(JVERSION,'3.0','>=')) {
			$layout = $app->input->getString('layout');
		} else {
			$layout = JRequest::getString('layout');
		}

		if ($layout == 'edit')
			return true;

		if(class_exists('JResponse'))
			$body = JResponse::getBody();
		$alternate_body = false;
		if(empty($body)) {
			$body = $app->getBody();
			$alternate_body = true;
		}

		if (!preg_match_all('#\<input (.*)\/\>#Uis', $body, $matches))
			return true;

		$para = array();
		$matches = $matches[1];
		$nbtag = count($matches);
		for ($i = 0; $i < $nbtag; $i++) {
			if (preg_match_all('#name="product_id"#Uis', $matches[$i], $pattern) && preg_match_all('#value="(.*)"#Uis', $matches[$i], $tag)) {
				$para[ (int)$tag[1][0] ] = (int)$tag[1][0];
			}
		}

		if (count($para) == 0)
			return true;

		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php'))
			return true;

		$db = JFactory::getDBO();
		$tags = array();

		$product_query = 'SELECT * FROM ' . hikashop_table('product') .
			' WHERE product_id IN (' . implode(',', $para) . ') AND product_access = '.$db->Quote('all').' AND product_published = 1 AND product_type = '.$db->Quote('main');
		$db->setQuery($product_query);
		$products = $db->loadObjectList('product_id');
		foreach($products as $k => $product) {
			$val = $this->_additionalParameter($product,'ecomm_prodid');
			if($val)
				$tags[(int)$product->product_id] = $val;
		}

		if (count($tags) == 0)
			return true;

		$params = array('ecomm_prodid: [\''.implode('\',\'', $tags) .'\']', 'ecomm_pagetype: \'product\'');

		$zone_id = hikashop_getZone();
		$currencyClass = hikashop_get('class.currency');
		$config =& hikashop_config();
		$main_currency = (int)$config->get('main_currency',1);
		$price_displayed = $this->params->get('price_displayed');
		switch($price_displayed){
			case 'expensive':
				$currencyClass->getListingPrices($products,$zone_id,$main_currency,'range');
				$tmpPrice = 0;
				foreach($products as $product){
					if(isset($product->prices[0]->price_value)){
						if(count($product->prices)>1){
							for($i=0;$i<count($product->prices);$i++){
								if($product->prices[$i]->price_value > $tmpPrice){
									$tmpPrice = $product->prices[$i]->price_value;
									$key = $i;
								}
							}
							$product->prices[0] = $product->prices[$key];
							for($i=1;$i<count($product->prices);$i++){
								unset($product->prices[$i]);
							}
						}
					}
				}
				break;
			case 'average':
				$currencyClass->getListingPrices($products,$zone_id,$main_currency,'range');
				$tmpPrice = 0;
				$tmpTaxPrice = 0;
				foreach($products as $product){
					if(isset($product->prices[0]->price_value)){
						if(count($product->prices) > 1){
							for($i=0;$i<count($product->prices);$i++){
								if($product->prices[$i]->price_value > $tmpPrice){
									$tmpPrice += $product->prices[$i]->price_value;
									$tmpTaxPrice += @$product->prices[$i]->price_value_with_tax;
								}
							}
							$product->prices[0]->price_value = $tmpPrice/count($product->prices);
							$product->prices[0]->price_value_with_tax = $tmpTaxPrice/count($product->prices);
							for($i=1;$i<count($product->prices);$i++){
								unset($product->prices[$i]);
							}
						}
					}
				}
				break;
			case 'unit':
			case 'cheapest':
			default:
				$currencyClass->getListingPrices($products,$zone_id,$main_currency,$price_displayed);
				break;
		}
		$colum = 'price_value';
		if($config->get('price_with_tax')){
			$colum = 'price_value_with_tax';
		}
		foreach($products as $product){
			if(!empty($product->prices) && count($product->prices)){
				$params[]='ecomm_totalvalue: '.round($product->prices[0]->$colum,2);
				break;
			}
		}

		$id = trim($this->params->get('adwordsid'));
		$extraJS = '';
		if($id != (int)$id){
			$extraJS = 'alert(\'You have configured the remarketing plugin of HikaShop with a wrong Adword ID. It should be a number. Please edit it via the Joomla plugins manager and correct it.\');';
		}

		$js = '<!-- Google code for remarketingtag -->
<script type="text/javascript">

var google_tag_params = {'.implode(', ',$params).' };
var google_conversion_id = '.(int)$id.';
var google_custom_params = window.google_tag_params;
var google_remarketing_only = true;
'.$extraJS.'

</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/'.(int)$id.'/?value=0&guid=ON&script=0"/>
</div>
</noscript>
';
		$body = preg_replace('#\<\/body\>#', $js.'</body>', $body, 1);

		if($alternate_body) {
			$app->setBody($body);
		} else {
			JResponse::setBody($body);
		}
	}

	function _additionalParameter(&$product, $param) {
		static $fields = false;

		if($fields === false) {
			$fieldsClass = hikashop_get('class.field');
			$data = null;
			$fields = $fieldsClass->getFields('all', $data, 'product');
		}

		if(empty($this->params[$param])) {
			$this->params[$param] = 'product_code';
		}

		$column = $this->params[$param];

		if(empty($product->$column))
			return false;
		return $product->$column;
	}
}
