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
class plgHikashopMassaction_address extends JPlugin
{
	var $message = '';
	var $massaction = null;
	var $addressClass = null;

	function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
	}

	function init() {
		static $init = false;
		if($init) return;
		$init = true;
		$this->massaction = hikashop_get('class.massaction');
		$this->massaction->datecolumns = array();
		$this->addressClass = hikashop_get('class.address');
	}

	function onMassactionTableLoad(&$externalValues){
		$obj = new stdClass();
		$obj->table = 'address';
		$obj->value = 'address';
		$obj->text = JText::_('ADDRESS');
		$externalValues[] = $obj;
	}

	function onMassactionTableTriggersLoad(&$table, &$triggers, &$triggers_html, &$loadedData) {
		if($table->table != 'address')
			return;
		$triggers['onBeforeAddressCreate']=JText::_('BEFORE_AN_ADDRESS_IS_CREATED');
		$triggers['onBeforeAddressUpdate']=JText::_('BEFORE_AN_ADDRESS_IS_UPDATED');
		$triggers['onBeforeAddressDelete']=JText::_('BEFORE_AN_ADDRESS_IS_DELETED');
		$triggers['onAfterAddressCreate']=JText::_('AFTER_AN_ADDRESS_IS_CREATED');
		$triggers['onAfterAddressUpdate']=JText::_('AFTER_AN_ADDRESS_IS_UPDATED');
		$triggers['onAfterAddressDelete']=JText::_('AFTER_AN_ADDRESS_IS_DELETED');
	}

	function onMassactionTableFiltersLoad(&$table,&$filters,&$filters_html,&$loadedData){
		if($table->table != 'address')
			return true;

		$type = 'filter';
		$tables = array('address','user');
	}

	function onProcessAddressMassFilterlimit(&$elements, &$query, $filter, $num) {
		$query->start = (int)$filter['start'];
		$query->value = (int)$filter['value'];
	}

	function onProcessAddressMassFilterordering(&$elements, &$query, $filter, $num) {
		if(empty($filter['value']))
			return;
		$this->init();
		if(isset($query->ordering['default']))
			unset($query->ordering['default']);
		$query->ordering[] = $filter['value'];
	}

	function onProcessAddressMassFilterdirection(&$elements, &$query, $filter, $num) {
		$this->init();
		if(empty($query->ordering))
			$query->ordering['default'] = 'address_id';
		$query->direction = $filter['value'];
	}

	function onProcessAddressMassFilteraddressColumn(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type'] == 'all')
			return;
		$this->init();
		if(count($elements)){
			foreach($elements as $k => $element){
				$in = $this->massaction->checkInElement($element, $filter);
				if(!$in) unset($elements[$k]);
			}
		}else{
			$db = JFactory::getDBO();
			if(!empty($filter['value']) || (empty($filter['value']) && in_array($filter['operator'],array('IS NULL','IS NOT NULL')))){
				if($filter['type'] == 'address_state' || $filter['type'] == 'address_country'){
					$type = str_replace('address_','',$filter['type']);
					$nquery = 'SELECT zone_namekey FROM '.hikashop_table('zone').' WHERE ';
					$key = str_replace($filter['type'],'',$this->massaction->getRequest($filter));
					$nquery .= 'zone_name '.$key.' OR zone_name_english '.$key.' OR zone_namekey '.$key;
					$nquery .= ' AND zone_type = '.$db->quote($type);
					$db->setQuery($nquery);
					$result = $db->loadResult();
					$query->where[] = 'hk_address.'.$filter['type'].' = '.$db->quote($result);
				}else{
					$query->where[] = $this->massaction->getRequest($filter,'hk_address');
				}
			}
		}
	}

	function onCountAddressMassFilteraddressColumn(&$query,$filter,$num){
		$this->init();
		$elements = array();
		$this->onProcessAddressMassFilteraddressColumn($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_address.address_id'));
	}

	function onProcessAddressMassFilteruserColumn(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all')
			return;
		$this->init();
		if(count($elements)){
			foreach($elements as $k => $element){
				$userClass = hikashop_get('class.user');
				$result = $userClass->get($element->address_user_id);

				$filter['type'] = str_replace('hk_user.','',$filter['type']);
				$filter['type'] = str_replace('joomla_user.','',$filter['type']);

				$in = $this->massaction->checkInElement($result, $filter);
				if(!$in) unset($elements[$k]);
			}
		}else{
			$db = JFactory::getDBO();
			if(!empty($filter['value']) || (empty($filter['value']) && in_array($filter['operator'],array('IS NULL','IS NOT NULL')))){
				$query->leftjoin['user'] = hikashop_table('user').' as hk_user ON hk_address.address_user_id = hk_user.user_id';
				$query->leftjoin['joomla_user'] = hikashop_table('users',false).' as joomla_user ON joomla_user.id = hk_user.user_cms_id';
				$query->where[] = $this->massaction->getRequest($filter);
			}
		 }
	}

	function onCountAddressMassFilteruserColumn(&$query,$filter,$num){
		$elements = array();
		$this->onProcessAddressMassFilteruserColumn($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_address.address_id'));
	}

	function onProcessAddressMassFilteraccessLevel(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(count($elements)){
			foreach($elements as $k => $element){
				if($element->$filter['type']!=$filter['value']) unset($elements[$k]);
			}
		}else{
			$db = JFactory::getDBO();
			$db->setQuery('SELECT user_id FROM '.hikashop_table('user_usergroup_map',false).'  WHERE group_id = '.(int)$filter['group']);
			$users = $db->loadColumn();
			if(!empty($users))
				$query->where[] = 'hk_user.user_cms_id'.' '.$filter['type'].' ('.implode(',',$users).')';
		 }
	}

	function onCountAddressMassFilteraccessLevel(&$query,$filter,$num){
		$elements = array();
		$this->onProcessAddressMassFilteraccessLevel($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_address.address_id'));
	}

	function onProcessAddressMassActiondisplayResults(&$elements,&$action,$k){
		$this->init();
		$params = $this->massaction->_displayResults('address',$elements,$action,$k);
		$params->action_id = $k;
		$js = '';
		$app = JFactory::getApplication();
		if(hikashop_isClient('administrator') && hikaInput::get()->getVar('ctrl','massaction') == 'massaction'){
			echo hikashop_getLayout('massaction','results',$params,$js);
		}
	}

	function onProcessAddressMassActionexportCsv(&$elements,&$action,$k){
		$this->init();
		$formatExport = $action['formatExport']['format'];
		$path = $action['formatExport']['path'];
		$email = $action['formatExport']['email'];
		if(!empty($path)){
			$url = $this->massaction->setExportPaths($path);
		}else{
			$url = array('server'=>'','web'=>'');
			ob_get_clean();
		}
		$app = JFactory::getApplication();
		if(hikashop_isClient('administrator') || (!hikashop_isClient('administrator') && !empty($path))){
			$params->action['address']['address_id'] = 'address_id';
			unset($action['formatExport']);
			$params = $this->massaction->_displayResults('address',$elements,$action,$k);
			$params->formatExport = $formatExport;
			$params->path = $url['server'];
			$params = $this->massaction->sortResult($params->table,$params);
			$this->massaction->_exportCSV($params);
		}

		if(!empty($email) && !empty($path)){
			$config = hikashop_config();
			$mailClass = hikashop_get('class.mail');
			$content = array('type' => 'csv_export');
			$mail = $mailClass->get('massaction_notification',$content);
			$mail->subject = JText::_('MASS_CSV_EMAIL_SUBJECT');
			$mail->html = '1';
			$csv = new stdClass();
			$csv->name = basename($path);
			$csv->filename = $url['server'];
			$csv->url = $url['web'];
			$mail->attachments = array($csv);
			$mail->dst_name = '';
			$mail->dst_email = explode(',',$email);
			$mailClass->sendMail($mail);
		}
	}

	function onProcessAddressMassActionupdateValues(&$elements,&$action,$k){
		$this->init();
		$db = JFactory::getDBO();
		$current = 'address';
		$current_id = $current.'_id';
		$ids = array();
		foreach($elements as $element){
			$ids[] = $element->$current_id;
			if(isset($element->$action['type']))
				$element->$action['type'] = $action['value'];

		}
		$action['type'] = strip_tags($action['type']);
		$alias = explode('_',$action['type']);
		$queryTables = array($current);
		$possibleTables = array($current);
		if(in_array($action['type'],array('address_state','address_country'))){
			$db->setQuery('SELECT zone_namekey FROM '.hikashop_table('zone').' WHERE zone_name = '.$db->quote($action['value']).' OR zone_name_english = '.$db->quote($action['value']));
			$action['value'] = $db->loadResult();
		}
		$value = $this->massaction->updateValuesSecure($action,$possibleTables,$queryTables);
		hikashop_toInteger($ids);

		$max = 500;
		if(count($ids) > $max){
			$c = ceil((int)count($ids) / $max);
			for($i = 0; $i < $c; $i++){
				$offset = $max * $i;
				$id = array_slice($ids, $offset, $max);
				$query = 'UPDATE '.hikashop_table($current).' AS hk_'.$current.' ';
				$query .= 'SET hk_'.$alias[0].'.'.$action['type'].' = '.$value.' ';
				$query .= 'WHERE hk_'.$current.'.'.$current.'_id IN ('.implode(',',$id).')';
				$db->setQuery($query);
				$db->execute();
			}
		}else{
			$query = 'UPDATE '.hikashop_table($current).' AS hk_'.$current.' ';
			$query .= 'SET hk_'.$alias[0].'.'.$action['type'].' = '.$value.' ';
			$query .= 'WHERE hk_'.$current.'.'.$current.'_id IN ('.implode(',',$ids).')';
			$db->setQuery($query);
			$db->execute();
		}
	}

	function onProcessAddressMassActiondeleteElements(&$elements,&$action,$k){
		$ids = array();
		$addressClass = hikashop_get('class.address');
		foreach($elements as $element){
			$result = $addressClass->delete($element->address_id);
		}
	}
	function onProcessAddressMassActionsendEmail(&$elements,&$action,$k){
		if(!empty($action['emailAddress'])){
			$config = hikashop_config();
			$mailClass = hikashop_get('class.mail');
			$content = array('elements' => $elements, 'action' => $action, 'type' => 'address_notification');
			$mail = $mailClass->get('massaction_notification',$content);
			$mail->subject = !empty($action['emailSubject'])?JText::_($action['emailSubject']):JText::_('MASS_NOTIFICATION_EMAIL_SUBJECT');
			$mail->body = $action['bodyData'];
			$mail->html = '1';
			$mail->dst_name = '';
			if(!empty($action['emailAddress']))
				$mail->dst_email = explode(',',$action['emailAddress']);
			else
				$mail->dst_email = $config->get('from_email');
			$mailClass->sendMail($mail);
		}
	}

	function onBeforeAddressCreate(&$element,&$do){
		$elements = array($element);
		$this->init();
		$this->massaction->trigger('onBeforeAddressCreate',$elements);
	}

	function onBeforeAddressUpdate(&$element,&$do){
		$this->init();
		$address = $this->addressClass->get($element->address_id);

		foreach($address as $key => $value){
			if(isset($element->$key) && $address->$key != $element->$key){
				$address->$key = $element->$key;
			}
		}
		$addresses = array($address);
		$this->massaction->trigger('onBeforeAddressUpdate',$addresses);
	}

	function onAfterAddressCreate(&$element){
		$elements = array($element);
		$this->init();
		$this->massaction->trigger('onAfterAddressCreate',$elements);
	}

	function onAfterAddressUpdate(&$element){
		$this->init();
		$address = $this->addressClass->get($element->address_id);

		foreach($address as $key => $value){
			if(isset($element->$key) && $address->$key != $element->$key){
				$address->$key = $element->$key;
			}
		}
		$addresses = array($address);
		$this->massaction->trigger('onAfterAddressUpdate',$addresses);
	}

	function onAfterAddressDelete(&$ids){
		$this->init();
		$this->massaction->trigger('onAfterAddressDelete',$this->deletedAdress);
	}

	function onBeforeAddressDelete($elements,$do){
		$this->init();
		$addresses = array();
		if(!is_array($elements)) $clone = array($elements);
		else $clone = $elements;
		foreach($clone as $id){
			$addresses[] = $this->addressClass->get($id);
		}
		$this->deletedAdress = &$addresses;
		$this->massaction->trigger('onBeforeAddressDelete',$addresses);
	}
}
