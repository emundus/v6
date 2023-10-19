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
class hikashopConfigClass extends hikashopClass{
	var $toggle = array('config_value'=>'config_namekey');
	function load(){
		$query = 'SELECT * FROM '.hikashop_table('config');
		$this->database->setQuery($query);
		$this->values = $this->database->loadObjectList('config_namekey');
		if(!empty($this->values['default_params']->config_value)){
			$this->values['default_params']->config_value = hikashop_unserialize(base64_decode($this->values['default_params']->config_value));
		}
	}

	function set($namekey,$value=null){
		if(!isset($this->values[$namekey]) || !is_object($this->values[$namekey])) $this->values[$namekey] = new stdClass();
		$this->values[$namekey]->config_value=$value;
		$this->values[$namekey]->config_namekey=$namekey;
		return true;
	}

	function get($namekey,$default = ''){
		if(empty($this->values)){
			$this->load();
		}

		if(in_array($namekey, array('checkout_legacy', 'add_to_cart_legacy')))
			return false;

		if(isset($this->values[$namekey])){
			if((preg_match('#^(menu_|params_)[0-9]+$#',$namekey) || $namekey == 'default_params') && !empty($this->values[$namekey]->config_value) && is_string($this->values[$namekey]->config_value)){
				$this->values[$namekey]->config_value = hikashop_unserialize(base64_decode($this->values[$namekey]->config_value));
			}
			if($namekey=='main_currency'){
				return $this->_checkMainCurrency($this->values[$namekey]->config_value);
			}
			return $this->values[$namekey]->config_value;
		}
		return $default;
	}

	function _checkMainCurrency($value){
		if(!is_numeric($value)){
			$this->database->setQuery('SELECT currency_id FROM '.hikashop_table('currency').' WHERE currency_code ='.$this->database->Quote($value));
			$value = (int) $this->database->loadResult();
		}

		if(empty($value)){
			return 1;
		}
		return $value;
	}

	function save(&$configObject,$default=false){
		if(empty($this->values))
			$this->load();

		$app = JFactory::getApplication();
		$previous_stars = isset($this->values['vote_star_number']->config_value) ? (int)$this->values['vote_star_number']->config_value : 5;

		$params = array();
		if(is_object($configObject))
			$configObject = get_object_vars($configObject);

		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashopshipping');
		JPluginHelper::importPlugin('hikashoppayment');
		$app = JFactory::getApplication();
		$do = true;
		$app->triggerEvent('onBeforeConfigSave', array(&$configObject, &$do) );
		if(!$do)
			return false;

		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);


		$this->checkAreaFields($configObject);

		foreach($configObject as $namekey => $value){
			if($namekey == 'configClassInit')
				continue;

			if($namekey == 'download_time_limit' && (int)$value > 315569260)
				$value = 315569260;
			if($namekey == 'vote_star_number' && (int)$value <= 0)
				continue;

			if($namekey=='default_params' || preg_match('#^(menu_|params_)[0-9]+$#',$namekey))
				$value = base64_encode(serialize($value));

			if(($namekey == 'payment_log_file' || $namekey == 'cron_savepath') && !preg_match('#^[a-z0-9/_\-]*\.log$#i', $value)) {
				if(hikashop_isClient('administrator'))
					$app->enqueueMessage('The log file must only contain alphanumeric characters and end with .log', 'error');
				continue;
			}

			if($namekey == 'mail_folder' && !empty($value) && !preg_match('#^\{root\}[a-z0-9/_\-]*$#i', $value)) {
				if(hikashop_isClient('administrator'))
					$app->enqueueMessage('The email folder must be a relative path from your ROOT folder prefixed with the tag {root}', 'error');
				continue;
			}

			if($namekey=='main_currency' && !empty($this->values[$namekey]->config_value)) {
				$currencyClass = hikashop_get('class.currency');
				$currency = new stdClass();
				$currency->currency_id = $this->values[$namekey]->config_value;
				$currency->currency_published = 1;
				$currency->currency_displayed = 1;
				$currencyClass->save($currency);
				$currencyClass->updateRatesWithNewMainCurrency($this->values[$namekey]->config_value,$value);
			}

			if(!isset($this->values[$namekey]))
				$this->values[$namekey] = new stdClass();

			$this->values[$namekey]->config_value = $value;

			if(!isset($this->values[$namekey]->config_default)) {
				$this->values[$namekey]->config_default = $this->values[$namekey]->config_value;
			}

			$cleaned_var = $safeHtmlFilter->clean($value, 'string');

			if($namekey == 'checkout_workflow')
				$cleaned_var = json_encode(json_decode($value));

			if($namekey == 'order_number_format')
				$cleaned_var = str_replace('&quot;}"','"}', $cleaned_var);

			$params[] = '('.$this->database->Quote(strip_tags($namekey)).','.$this->database->Quote($cleaned_var).($default?','.$this->database->Quote($this->values[$namekey]->config_default):'').')';
		}

		if(isset($this->values['vote_star_number']->config_value) && $previous_stars != (int)$this->values['vote_star_number']->config_value)
			$this->update_average_rate($previous_stars, (int)$this->values['vote_star_number']->config_value);

		$query = 'REPLACE INTO '.hikashop_table('config').' (config_namekey,config_value'.($default?',config_default':'').') VALUES ' . implode(',', $params);
		$this->database->setQuery($query);
		$result = $this->database->execute();
		if($result)
			$app->triggerEvent('onAfterConfigSave', array(&$params) );
		return $result;
	}

	function checkAreaFields(&$configObject) {
		$forms = array('product');
		foreach($forms as $table) {
			$config_fieldsnamekey = $table .'_areas_fields';
			if (empty($configObject[$config_fieldsnamekey]))
				continue;

			$areas = json_decode($configObject[$config_fieldsnamekey]);
			if (empty($areas))
				continue;

			$this->database->setQuery("SELECT field_namekey FROM #__hikashop_field WHERE field_table = '" . $table . "'");
			$columns = $this->database->loadColumn();
			if (empty($columns))
				continue;

			$matches = [];
			foreach($areas as $area) {
				$matches = array_merge($matches, array_intersect($area->fields, $columns));
				if (count($columns) == count($matches)) {
					return;
				}
			}

			$missing = array_diff($columns, $matches);

			$this->database->setQuery("SELECT config_value FROM #__hikashop_config WHERE config_namekey = '" . $config_fieldsnamekey . "'");
			$fieldsconfig = $this->database->loadResult();
			if (empty($fieldsconfig)) {
				return;
			}

			$fieldsconfig = json_decode($fieldsconfig);
			if (empty($fieldsconfig)) {
				return;
			}

			$found = false;
			foreach($fieldsconfig as $fieldconfig) {
				$fields = array_intersect($fieldconfig->fields, $missing);
				if (empty($fields))
					continue;

				foreach($areas as &$area) {
					if ($fieldconfig->name != $area->name)
						continue;

					foreach($fields as $field) {
						$key = array_search($field, $fieldconfig->fields);

						if (empty($key)) {
							$area->fields[] = array_merge([$field], $area->fields);
						} else if ($key >= count($area->fields)) {
							$area->fields[] = $field;
						} else {
							$area->fields = array_merge(
								array_slice($area->fields, 0, $key),
								[$field],
								array_slice($area->fields, $key)
							);
						}

						$found = true;
						$missing = array_diff($missing, [$field]);
						if (empty($missing))
							break 2;
					}
				}
				unset($area);

				if (empty($missing))
					break;
			}

			if ($found) {
				$configObject[$config_fieldsnamekey] = json_encode($areas);
			}
		}
	}

	function reset() {
		$query = 'UPDATE '.hikashop_table('config').' SET config_value = config_default';
		$this->database->setQuery($query);
		$this->database->execute();
		$this->load();
	}

	function update_average_rate($previous_stars, $new_stars) {
		if((int)$previous_stars <= 0)
			return;


		$query = 'UPDATE '.hikashop_table('product').' SET product_average_score = ('.(int)$new_stars.' * product_average_score) / '.(int)$previous_stars.' WHERE product_average_score != 0';
		$this->database->setQuery($query);
		$this->database->execute();

		$query = 'UPDATE '.hikashop_table('vote').' SET vote_rating = ('.(int)$new_stars.' * vote_rating) / '.(int)$previous_stars.' WHERE vote_rating != 0';
		$this->database->setQuery($query);
		$this->database->execute();

	}

	public function getAddressFormat($originalOnly=false){
		if(!$originalOnly){
			$db = JFactory::getDBO();
			$query = "SELECT template FROM #__template_styles WHERE client_id = 0 AND home = 1";
			$db->setQuery($query);
			$defaultemplate = $db->loadResult();
			if(empty($defaultemplate))
				return;
			$path = HIKASHOP_ROOT . 'templates' . DS . $defaultemplate . DS . 'html' . DS . 'com_hikashop' . DS . 'address' . DS . 'address_template.php';
			jimport('joomla.filesystem.file');
			if(JFile::exists($path))
				$file = file_get_contents($path);
		}
		if(empty($file)){
			$path = HIKASHOP_FRONT . 'views' . DS . 'address' . DS . 'tmpl' . DS . 'address_template.php';
			if(JFile::exists($path))
				$file = file_get_contents($path);
		}

		if(empty($file))
			return;

		return trim(preg_replace("/<\?php\s*(?:\/\*.*\*\/)?\s*defined\('_JEXEC'\) or die\('Restricted access'\);\s*\?>/Us", '', $file));
	}
}
