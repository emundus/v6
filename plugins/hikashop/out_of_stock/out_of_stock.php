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
class plgHikashopOut_of_stock extends JPlugin
{
	var $message = '';
	function __construct(&$subject, $config){
		parent::__construct($subject, $config);
	}

	function onHikashopCronTrigger(&$messages){
		$pluginsClass = hikashop_get('class.plugins');
		$plugin = $pluginsClass->getByName('hikashop','out_of_stock');
		if(empty($plugin->params['period'])){
			$plugin->params['period'] = 86400;
		}
		if(empty($plugin->params['stock_limit'])){
			$plugin->params['stock_limit'] = 0;
		}
		$this->stock_limit = $plugin->params['stock_limit'];
		$this->period = $plugin->params['period'];
		if(!empty($plugin->params['last_cron_update']) && $plugin->params['last_cron_update']+$plugin->params['period']>time()){
			return true;
		}
		$plugin->params['last_cron_update']=time();
		$pluginsClass->save($plugin);
		$this->checkProducts();
		if(!empty($this->message)){
			$messages[] = $this->message;
		}
		return true;
	}

	function checkProducts() {
		$db = JFactory::getDBO();
		$filters = array(
			'product_quantity != -1',
			'product_published = 1',
			'(product_sale_start = 0 OR product_sale_start < '.time().')',
			'(product_sale_end = 0 OR product_sale_end > '.time().')',
		);
		if(is_numeric($this->stock_limit)) {
			$filters[] = 'product_quantity <= ' . (int)$this->stock_limit;
		}else{
			$filters[] = 'product_quantity <= ' . hikashop_secureField($this->stock_limit);
		}
		$query = 'SELECT * FROM '.hikashop_table('product').' WHERE '. implode(' AND ', $filters);
		$db->setQuery($query);
		$products = $db->loadObjectList();
		if(!empty($products)){
			$productClass = hikashop_get('class.product');
			$sortedProducts = array();
			foreach($products as $k => $product){
				if($product->product_type == 'variant') {
					$db->setQuery('SELECT * FROM '.hikashop_table('variant').' AS a LEFT JOIN '.hikashop_table('characteristic') .' AS b ON a.variant_characteristic_id=b.characteristic_id WHERE a.variant_product_id='.(int)$product->product_id.' ORDER BY a.ordering');
					$products[$k]->characteristics = $db->loadObjectList();

					$parentProduct = $productClass->get((int)$product->product_parent_id);
					$productClass->checkVariant($products[$k], $parentProduct);
				}
				$sortedProducts[$product->product_name.'_'.$product->product_id] = $product;
			}
			ksort($sortedProducts, SORT_NATURAL);
			$mailClass = hikashop_get('class.mail');
			$infos = new stdClass();
			$infos->products =& $sortedProducts;
			$mail = $mailClass->get('out_of_stock',$infos);
			$mail->subject = JText::sprintf($mail->subject,HIKASHOP_LIVE);
			$config =& hikashop_config();
			if(!empty($infos->email)){
				$mail->dst_email = $infos->email;
			}else{
				$mail->dst_email = $config->get('from_email');
			}
			if(!empty($infos->name)){
				$mail->dst_name = $infos->name;
			}else{
				$mail->dst_name = $config->get('from_name');
			}
			$mailClass->sendMail($mail);
		}

		$app = JFactory::getApplication();
		$this->message = 'Products quantity checked';
		$app->enqueueMessage($this->message );
		return true;
	}
}
