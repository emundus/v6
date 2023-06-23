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
class plgHikashopMassaction_user extends JPlugin
{
	public $message = '';
	public $massaction = null;
	public $user = null;
	public $db = null;

	function __construct(&$subject, $config){
		parent::__construct($subject, $config);
		$this->massaction = hikashop_get('class.massaction');
		$this->massaction->datecolumns = array('user_created');
		$this->user = hikashop_get('class.user');
		$this->db = JFactory::getDBO();
	}

	function onMassactionTableLoad(&$externalValues){
		$obj = new stdClass();
		$obj->table ='user';
		$obj->value ='user';
		$obj->text =JText::_('HIKA_USER');
		$externalValues[] = $obj;
	}

	function onMassactionTableTriggersLoad(&$table, &$triggers, &$triggers_html, &$loadedData) {
		if($table->table != 'user')
			return true;

		$triggers['onBeforeUserCreate']=JText::_('BEFORE_A_USER_IS_CREATED');
		$triggers['onBeforeUserUpdate']=JText::_('BEFORE_A_USER_IS_UPDATED');
		$triggers['onBeforeUserDelete']=JText::_('BEFORE_A_USER_IS_DELETED');
		$triggers['onAfterUserCreate']=JText::_('AFTER_A_USER_IS_CREATED');
		$triggers['onAfterUserUpdate']=JText::_('AFTER_A_USER_IS_UPDATED');
		$triggers['onAfterUserDelete']=JText::_('AFTER_A_USER_IS_DELETED');
	}

	function onMassactionTableFiltersLoad(&$table,&$filters,&$filters_html,&$loadedData){
		if($table->table != 'user')
			return true;

		$type = 'filter';
		$tables = array('user','address');

		$filters['haveDontHave']=JText::_('HIKA_HAVE_DONT_HAVE');
		$loadedData->massaction_filters['__num__'] = new stdClass();
		$loadedData->massaction_filters['__num__']->type = 'user';
		$loadedData->massaction_filters['__num__']->data = array('have'=>'have','type'=>'','order_status'=>'created');
		$loadedData->massaction_filters['__num__']->name = 'haveDontHave';
		$loadedData->massaction_filters['__num__']->html = '';

		$this->db->setQuery('SELECT `orderstatus_namekey` FROM '.hikashop_table('orderstatus'));
		$orderStatuses = $this->db->loadObjectList();

		foreach($loadedData->massaction_filters as $key => &$value) {
			if($value->name != 'haveDontHave' || ($table->table != $loadedData->massaction_table && is_int($key)))
				continue;

			$value->type = 'user';

			$output= '<select class="custom-select chzn-done not-processed" name="filter['.$table->table.']['.$key.'][haveDontHave][have]" id="userfilter'.$key.'haveDontHavetype" onchange="countresults(\''.$table->table.'\','.$key.')">';
			$datas = array('have'=>'HIKA_HAVE','donthave'=>'HIKA_DONT_HAVE');
			$display = 'style="display: none;"';
			foreach($datas as $k => $data){
				$selected = '';
				if($k == $value->data['have']) $selected = 'selected="selected"';
				if($value->data['have'] == 'order_status') $display = '';
				$output.= '<option value="'.$k.'" '.$selected.'>'.JText::_(''.$data.'').'</option>';
			}
			$output.= '</select>';

			$output.= '<select class="custom-select chzn-done not-processed" name="filter['.$table->table.']['.$key.'][haveDontHave][type]" id="userfilter'.$key.'haveDontHavetype" onchange="showSubSelect(this.value,'.$key.'); countresults(\''.$table->table.'\','.$key.')">';
			$datas = array('order'=>'HIKASHOP_ORDER','order_status'=>'ORDER_STATUS','address'=>'ADDRESS');
			$display = 'style="display: none;"';
			foreach($datas as $k => $data){
				$selected = '';
				if($k == $value->data['type']) $selected = 'selected="selected"';
				if($value->data['type'] == 'order_status') $display = '';
				$output.= '<option value="'.$k.'" '.$selected.'>'.JText::_(''.$data.'').'</option>';
			}
			$output.= '</select>';

			$output .= '<select class="custom-select chzn-done not-processed" id="userfilter'.$key.'haveDontHaveorderStatus" '.$display.' name="filter['.$table->table.']['.$key.'][haveDontHave][order_status]" onchange="countresults(\''.$table->table.'\','.$key.')">';
			if(is_array($orderStatuses)){
				foreach($orderStatuses as $orderStatus){
					$selected = '';
					if($orderStatus->orderstatus_namekey == $value->data['order_status']) $selected = 'selected="selected"';
					$output.='<option value="'.$orderStatus->orderstatus_namekey.'" '.$selected.'>'.hikashop_orderStatus($orderStatus->orderstatus_namekey).'</option>';
				}
			}
			$output.= '</select>';
			$output.= '<input type="hidden" id="userfilter'.$key.'haveDontHavehide" name="filter['.$table->table.']['.$key.'][haveDontHave][show]" value="0"/>';

			$filters_html['haveDontHave'] = $this->massaction->initDefaultDiv($value, $key, $type, $table->table, $loadedData, $output);
		}
		$filters_html['haveDontHave'] .= '
			<script type="text/javascript">
				var d = document;
				var hide = d.getElementById(\'userfilter'.$key.'haveDontHavehide\').value;
				if(hide != 0){d.getElementById(hide).style.display = \'inline-block\';}
				function showSubSelect(type, k){
					if(type == \'order_status\'){
						d.getElementById(\'userfilter\'+k+\'haveDontHaveorderStatus\').style.display = \'inline-block\';
						d.getElementById(\'userfilter\'+k+\'haveDontHavehide\').value = \'userfilter\'+k+\'haveDontHaveorderStatus\';
					}else{
						d.getElementById(\'userfilter\'+k+\'haveDontHaveorderStatus\').style.display = \'none\';
						d.getElementById(\'userfilter\'+k+\'haveDontHavehide\').value = \'0\';
					}
				}
			</script>
		';
	}

	function onProcessUserMassFilterlimit(&$elements, &$query,$filter,$num){
		$query->start = (int)$filter['start'];
		$query->value = (int)$filter['value'];
	}

	function onProcessUserMassFilterordering(&$elements, &$query,$filter,$num){
		if(!empty($filter['value'])){
			if(isset($query->ordering['default']))
				unset($query->ordering['default']);
			$query->ordering[] = $filter['value'];
		}
	}

	function onProcessUserMassFilterdirection(&$elements, &$query,$filter,$num){
		if(empty($query->ordering))
			$query->ordering['default'] = 'user_id';
		$query->direction = $filter['value'];
	}

	function onProcessUserMassFilteruserColumn(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(!isset($this->massaction))$this->massaction = hikashop_get('class.massaction');
		if(count($elements)){
			foreach($elements as $k => $element){
				$filter['type'] = str_replace('hk_user.','',$filter['type']);
				$filter['type'] = str_replace('joomla_user.','',$filter['type']);
				$in = $this->massaction->checkInElement($element, $filter);
				if(!$in) unset($elements[$k]);
			}
		}else{
			$db = JFactory::getDBO();
			if(!empty($filter['value']) || (empty($filter['value']) && in_array($filter['operator'],array('IS NULL','IS NOT NULL')))){
				$query->leftjoin['joomla_user'] = hikashop_table('users',false).' as joomla_user ON joomla_user.id = hk_user.user_cms_id';
				$query->where[] = $this->massaction->getRequest($filter);
			}
		}
	}
	function onCountUserMassFilteruserColumn(&$query,$filter,$num){
		$elements = array();
		$this->onProcessUserMassFilteruserColumn($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_user.user_id'));
	}

	function onProcessUserMassFilteraddressColumn(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(!isset($this->massaction))$this->massaction = hikashop_get('class.massaction');
		$db = JFactory::getDBO();

		if(in_array($filter['type'],array('address_state','address_country'))){
			$db->setQuery('SELECT zone_namekey FROM '.hikashop_table('zone').' WHERE zone_name LIKE '.$db->quote($filter['value']).' OR zone_name_english LIKE '.$db->quote($filter['value']));
			$filter['value'] = $db->loadResult();
		}
		if(count($elements)){
			foreach($elements as $k => $element){
				$db->setQuery('SELECT * FROM '.hikashop_table('address').' WHERE address_user_id = '.(int)$element->user_id.' GROUP BY address_id');
				$results = $db->loadObjectList();
				$del = true;
				foreach($results as $result){
					$in = $this->massaction->checkInElement($result, $filter);
					if($in) $del = false;
				}
				if($del) unset($elements[$k]);
			}
		}else{
			if(!is_null($filter['value']) || (is_null($filter['value']) && in_array($filter['operator'],array('IS NULL','IS NOT NULL')))){
				$query->select = ' DISTINCT '.$query->select;
				$query->leftjoin[] = hikashop_table('address').' as hk_address ON hk_address.address_user_id = hk_user.user_id';
				$query->where[] = $this->massaction->getRequest($filter,'hk_address');
			}else{
				$query->leftjoin = '';
				$query->where = array('false');
			}
		 }
	}
	function onCountUserMassFilteraddressColumn(&$query,$filter,$num){
		$elements = array();
		$this->onProcessUserMassFilteraddressColumn($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_user.user_id'));
	}
	function onProcessUserMassFilterhaveDontHave(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(count($elements)){
			foreach($elements as $k => $element){
				if($element->$filter['type']!=$filter['value']) unset($elements[$k]);
			}
		}else{
			$db = JFactory::getDBO();
			$ids = null;
			$qSearch = 'NOT IN';
			if($filter['have'] == 'have')
				$qSearch = 'IN';
			switch($filter['type']){
				case 'order':
					$db->setQuery('SELECT order_user_id FROM '.hikashop_table('order').' GROUP BY order_user_id');
					$ids = $db->loadColumn();
					break;
				case 'order_status':
					$db->setQuery('SELECT order_user_id FROM '.hikashop_table('order').' WHERE order_status = '.$db->quote($filter['order_status']).' GROUP BY order_user_id');
					$ids = $db->loadColumn();
					if($filter['have'] != 'have'){
						$db->setQuery('SELECT order_user_id FROM '.hikashop_table('order').' GROUP BY order_user_id');
						$allIds = $db->loadColumn();
						$ids = array_diff($allIds, $ids);
						$qSearch = 'IN';
					}
					break;
				case 'address':
					$db->setQuery('SELECT address_user_id FROM '.hikashop_table('address').' GROUP BY address_user_id');
					$ids = $db->loadColumn();
					break;
			}
			if($ids == null){
				$query->where[] = ' 0 = 1';
			}else{
				$query->where[] = 'hk_user.user_id '.$qSearch.' ('.implode(',',$ids).')';
			}
		}
	}
	function onCountUserMassFilterhaveDontHave(&$query,$filter,$num){
		$elements = array();
		$this->onProcessUserMassFilterhaveDontHave($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_user.user_id'));
	}

	function onProcessUserMassFilteraccessLevel(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(count($elements)){
			$user_ids = array();
			foreach($elements as $k => $element){
				$user_ids[$element->user_id] = (int)$element->user_id;
			}
			if(count($user_ids)) {
				$db = JFactory::getDBO();
				$db->setQuery('SELECT user_id, user_cms_id FROM '.hikashop_table('user').'  WHERE user_id IN ('.implode(',', $user_ids).')');
				$users = $db->loadObjectList('user_id');
				$cms_ids = array();
				foreach($users as $u){
					$cms_ids[$u->user_cms_id] = (int)$u->user_cms_id;
				}
				$groups = array();
				if(count($cms_ids)) {
					$db->setQuery('SELECT user_id FROM '.hikashop_table('user_usergroup_map',false).'  WHERE user_id IN ('.implode(',', $cms_ids).') AND group_id = '.(int)$filter['group']);
					$groups = $db->loadObjectList('user_id');
				}
				foreach($elements as $k => $element){
					if($filter['type'] == 'IN' && (!isset($users[$element->user_id]) || !isset($groups[$users[$element->user_id]->user_cms_id])))
						unset($elements[$k]);
					elseif($filter['type'] == 'NOT IN' && isset($users[$element->user_id]) && isset($groups[$users[$element->user_id]->user_cms_id]))
						unset($elements[$k]);
				}
			}
		}else{
			$db = JFactory::getDBO();
			$db->setQuery('SELECT user_id FROM '.hikashop_table('user_usergroup_map',false).'  WHERE group_id = '.(int)$filter['group']);
			$users = $db->loadColumn();
			if(!empty($users)){
				$query->where[] = 'hk_user.user_cms_id'.' '.$filter['type'].' ('.implode(',',$users).')';
			}
		}
	}
	function onCountUserMassFilteraccessLevel(&$query,$filter,$num){
		$elements = array();
		$this->onProcessUserMassFilteraccessLevel($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_user.user_id'));
	}

	function onProcessUserMassActiondisplayResults(&$elements,&$action,$k){
		$params = $this->massaction->_displayResults('user',$elements,$action,$k);
		$params->action_id = $k;
		$js = '';
		$app = JFactory::getApplication();
		if(hikashop_isClient('administrator') && hikaInput::get()->getVar('ctrl','massaction') == 'massaction'){
			echo hikashop_getLayout('massaction','results',$params,$js);
		}
	}
	function onProcessUserMassActionexportCsv(&$elements,&$action,$k){
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
			unset($action['formatExport']);
			$params = $this->massaction->_displayResults('user',$elements,$action,$k);
			$params->formatExport = $formatExport;
			$params->action['user']['user_id'] = 'user_id';
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
	function onProcessUserMassActionupdateValues(&$elements,&$action,$k){
		$current = 'user';
		$current_id = $current.'_id';
		$ids = array();
		$column = $action['type'];
		foreach($elements as $element){
			$ids[] = $element->$current_id;
			if(isset($element->$column))
				$element->$column = $action['value'];

		}
		$action['type'] = strip_tags($column);
		$alias = explode('_',$action['type']);
		$queryTables = array($current);
		$possibleTables = array($current,'joomla_users');
		if(!isset($this->massaction))$this->massaction = hikashop_get('class.massaction');
		$value = $this->massaction->updateValuesSecure($action,$possibleTables,$queryTables);
		hikashop_toInteger($ids);
		$db = JFactory::getDBO();




		$max = 500;
		if(count($ids) > $max){
			$c = ceil((int)count($ids) / $max);
			for($i = 0; $i < $c; $i++){
				$offset = $max * $i;
				$id = array_slice($ids, $offset, $max);
				$query = 'UPDATE '.hikashop_table($current).' AS hk_'.$current.' ';
				$queryTables = array_unique($queryTables);
				foreach($queryTables as $queryTable){
					switch($queryTable){
						case 'user':
							if(!in_array('joomla_users',$queryTables)){
								$query .= 'SET hk_'.$current.'.'.$action['type'].' = '.$value.' ';
							}
							break;
						case 'joomla_users':
							$action['type'] = str_replace($queryTable.'_','',$action['type']);
							$query .= 'LEFT JOIN '.hikashop_table('users',false).' AS joomla_users ON joomla_users.id = hk_user.user_cms_id ';
							$query .= 'SET '.$queryTable.'.'.$action['type'].' = '.$value.' ';
							break;
					}
				}
				$query .= 'WHERE hk_'.$current.'.'.$current.'_id IN ('.implode(',',$id).')';
				$db->setQuery($query);
				$db->execute();
			}
		}else{
			$query = 'UPDATE '.hikashop_table($current).' AS hk_'.$current.' ';
			$queryTables = array_unique($queryTables);
			foreach($queryTables as $queryTable){
				switch($queryTable){
					case 'user':
						if(!in_array('joomla_users',$queryTables)){
							$query .= 'SET hk_'.$current.'.'.$action['type'].' = '.$value.' ';
						}
						break;
					case 'joomla_users':
						$action['type'] = str_replace($queryTable.'_','',$action['type']);
						$query .= 'LEFT JOIN '.hikashop_table('users',false).' AS joomla_users ON joomla_users.id = hk_user.user_cms_id ';
						$query .= 'SET '.$queryTable.'.'.$action['type'].' = '.$value.' ';
						break;
				}
			}
			$query .= 'WHERE hk_'.$current.'.'.$current.'_id IN ('.implode(',',$ids).')';
			$db->setQuery($query);
			$db->execute();
		}

	}
	function onProcessUserMassActiondeleteElements(&$elements,&$action,$k){
		$ids = array();
		foreach($elements as $element){
			$ids[] = $element->user_id;
		}
		$userClass = hikashop_get('class.user');

		$max = 500;
		if(count($ids) > $max){
			$c = ceil((int)count($ids) / $max);
			for($i = 0; $i < $c; $i++){
				$offset = $max * $i;
				$id = array_slice($ids, $offset, $max);
				$result = $userClass->delete($id);
			}
		}else{
			$result = $userClass->delete($ids);
		}
	}
	function onProcessUserMassActionchangeGroup(&$elements,&$action,$k){
		$user_ids = array();
		$values = array();

		foreach($elements as $element){
			$user_ids[] = $element->user_cms_id;
			$values[] = '('.$element->user_cms_id.','.$action['value'].')';
		}
		$db = JFactory::getDBO();
		if($action['type'] != 'add'){
			$filters = '';
			if($action['type'] == 'remove')
				$filters = ' AND group_id = '.(int)$action['value'];

			$db->setQuery('DELETE FROM '.hikashop_table('user_usergroup_map',false).' WHERE user_id IN ('.implode(',',$user_ids).')'.$filters);
			$db->execute();
		}
		if($action['type'] != 'remove'){
			$db->setQuery('REPLACE INTO '.hikashop_table('user_usergroup_map',false).' VALUES '.implode(',',$values));
			$db->execute();
		}

		$app = JFactory::getApplication();
		$config = JFactory::getConfig();
		$handler = $config->get('session_handler', 'none');
		if($handler=='database'){
			$db->setQuery('DELETE FROM '.hikashop_table('session',false).' WHERE client_id=0 AND userid IN ('.implode(',',$user_ids).')');
			$db->execute();
		}
		if(!hikashop_isClient('administrator')){
			foreach($user_ids as $user_id){
				if($user_id != hikashop_loadUser())
					continue;
				$app->logout( $data->user_cms_id );
			}
		}
	}
	function onProcessUserMassActionsendEmail(&$elements,&$action,$k){
		if(!empty($action['emailAddress'])){
			$config = hikashop_config();
			$mailClass = hikashop_get('class.mail');
			$content = array('elements' => $elements, 'action' => $action, 'type' => 'user_notification');
			$mail = $mailClass->get('massaction_notification',$content);
			$mail->subject = !empty($mail->subject)?JText::_($action['emailSubject']):JText::_('MASS_NOTIFICATION_EMAIL_SUBJECT');
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

	function onBeforeUserCreate(&$element,&$do){
		if(!$do) return;
		$elements = array($element);
		$this->massaction->trigger('onBeforeUserCreate',$elements);
	}

	function onBeforeUserUpdate(&$element,&$do){
		if(!$do) return;

		$getUser = hikashop_copy($this->user->get($element->user_id));

		foreach($getUser as $key => $value){
			if(isset($element->$key) && $getUser->$key != $element->$key){
				$getUser->$key = $element->$key;
			}
		}
		$users = array($getUser);
		$this->massaction->trigger('onBeforeUserUpdate',$users);
	}

	function onBeforeUserDelete(&$element,&$do){
		$users = array();
		if(!is_array($element)) $clone = array($element);
		else $clone = $element;
		foreach($clone as $id){
			$users[] = $this->user->get($id);
		}
		$this->deletedUser =& $users;
		$this->massaction->trigger('onBeforeUserDelete',$users);
	}

	function onAfterUserCreate(&$element){
		$getUser = $this->user->get($element->user_id);
		foreach($getUser as $key => $value){
			if(isset($element->$key) && $getUser->$key != $element->$key){
				$getUser->$key = $element->$key;
			}
		}
		$users = array($getUser);
		$this->massaction->trigger('onAfterUserCreate',$users);
	}

	function onAfterUserUpdate(&$element){
		$getUser = $this->user->get($element->user_id);

		foreach($getUser as $key => $value){
			if(isset($element->$key) && $getUser->$key != $element->$key){
				$getUser->$key = $element->$key;
			}
		}
		$users = array($getUser);
		$this->massaction->trigger('onAfterUserUpdate',$users);
	}

	function onAfterUserDelete(&$element){
		$this->massaction->trigger('onAfterUserDelete',$this->deletedUser);
	}

}
