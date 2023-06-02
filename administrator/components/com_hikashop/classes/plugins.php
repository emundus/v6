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
class hikashopPluginsClass extends hikashopClass {

	function  __construct( $config = array() ) {
		$this->toggle = array('enabled'=>'extension_id');
		$this->pkeys = array('extension_id');
		return parent::__construct($config);
	}

	function getTable() {
		return hikashop_table('extensions',false);
	}

	function getMethods($type = 'shipping', $name = '',$shipping = '', $currency = '') {
		$where = array();
		$lf='';
		$select='*';
		if(!empty($name)){
			$where[] = $type.'_type='.$this->database->Quote($name);
		}

		if(!empty($shipping)){
			$where[] = '(payment_shipping_methods IN (\'\',\'_\') OR payment_shipping_methods LIKE \'%\n'.$shipping.'\n%\' OR payment_shipping_methods LIKE \''.$shipping.'\n%\' OR payment_shipping_methods LIKE \'%\n'.$shipping.'\' OR payment_shipping_methods LIKE \''.$shipping.'\')';
		}
		if(!empty($currency)){
			$where[] = "(".$type."_currency IN ('','_','all') OR ".$type."_currency LIKE '%,".intval($currency).",%')";
		}

		$app = JFactory::getApplication();
		if(!hikashop_isClient('administrator')){
			$access = $type.'_access';
			hikashop_addACLFilters($where,$access);
		}

		if(!empty($where)) {
			$where = ' WHERE '.implode(' AND ',$where);
		} else {
			$where = '';
		}
		if($type == 'shipping') {
			$where .= ' ORDER BY shipping_ordering ASC';
		}
		if($type == 'payment') {
			$where .= ' ORDER BY payment_ordering ASC';
		}

		$query = 'SELECT '.$select.' FROM '.hikashop_table($type).' '.$lf.$where;

		$this->database->setQuery($query);
		$methods = $this->database->loadObjectList($type.'_id');
		$this->params($methods,$type);
		if(empty($methods)){
			$methods = array();
		} elseif($type == 'payment') {
			$types = array();
			foreach($methods as $method) {
				$types[$method->payment_type] = $this->database->Quote($method->payment_type);
			}
			$types = implode(',',$types);

			$query = 'SELECT * FROM '.hikashop_table('extensions',false).' WHERE element IN ('.$types.') AND folder=\'hikashoppayment\' AND type=\'plugin\' ORDER BY ordering ASC';
			$this->database->setQuery($query);
			$plugins = $this->database->loadObjectList();
			foreach($methods as $k => $method){
				foreach($plugins as $plugin){
					if($plugin->element == $method->payment_type){
						foreach(get_object_vars($plugin) as $key => $val){
							$methods[$k]->$key = $val;
						}
						break;
					}
				}
			}
		}

		return $methods;
	}

	function params(&$methods, $type) {
		if(empty($methods))
			return;
		$params = $type.'_params';
		$name = $type . '_name';
		$description = $type . '_description';
		foreach($methods as $k => $el) {
			if(!empty($el->$params))
				$methods[$k]->$params = @hikashop_unserialize($el->$params);
			if(!empty($el->$name))
				$methods[$k]->$name = hikashop_translate($el->$name);
			if(!empty($el->$description))
				$methods[$k]->$description = hikashop_translate($el->$description);
		}
	}

	function get($id, $default = '') {
		$result = parent::get($id);
		$this->loadParams($result);
		return $result;
	}

	function getByName($type,$name){
		$query = 'SELECT * FROM '.hikashop_table('extensions',false).' WHERE folder='.$this->database->Quote($type).' AND element='.$this->database->Quote($name).' AND type=\'plugin\'';
		$this->database->setQuery($query);
		$result = $this->database->loadObject();
		$this->loadParams($result);
		return $result;
	}

	function loadParams(&$result){
		if(empty($result->params) || is_array($result->params))
			return;

		$registry = new JRegistry;
		if(!HIKASHOP_J30) {
			$registry->loadJSON($result->params);
		} else {
			$registry->loadString($result->params, 'JSON');
		}
		$result->params = $registry->toArray();
	}

	function save(&$element) {
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$id = reset($this->pkeys);
		$do = true;
		if(empty($element->$id))
			$app->triggerEvent('onBeforeHikaPluginCreate', array('joomla.plugin', &$element, &$do));
		else
			$app->triggerEvent('onBeforeHikaPluginUpdate', array('joomla.plugin', &$element, &$do));

		if(!$do)
			return false;

		$elementToSave = hikashop_copy($element);
		if(isset($elementToSave->plugin_params) && !is_string($elementToSave->plugin_params)){
			$elementToSave->plugin_params = serialize($elementToSave->plugin_params);
		}

		if(!empty($elementToSave->params)) {
			if(HIKASHOP_J40)
				$handler = \Joomla\Registry\Factory::getFormat('JSON');
			else
				$handler = JRegistryFormat::getInstance('JSON');
			$elementToSave->params = $handler->objectToString($elementToSave->params);
		}

		return parent::save($elementToSave);
	}

	function cleanPluginCache() {
		if(!class_exists('JCache')) return;

		$jconf = JFactory::getConfig();
		$app = JFactory::getApplication();

		$options = array(
			'defaultgroup' => 'com_plugins',
			'cachebase' => $jconf->get('cache_path', JPATH_SITE . '/cache')
		);

		$cache = JCache::getInstance('callback', $options);
		$cache->clean();

		$app->triggerEvent('onContentCleanCache', $options);
	}
}
