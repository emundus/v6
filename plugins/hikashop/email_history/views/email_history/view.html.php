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
class Email_historyViewEmail_history extends hikashopView {
	var $ctrl = 'email_history';
	var $nameListing = 'EMAIL_LOG';
	var $nameForm = 'EMAIL_LOG';
	var $icon = 'envelope';

	function display($tpl = null) {
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function))
			$this->$function();
		parent::display($tpl);
	}

	function listing() {
		$app = JFactory::getApplication();
		$database = JFactory::getDBO();
		$config =& hikashop_config();

		include_once dirname(dirname(dirname(__FILE__))) . DS . 'email_history_class.php';
		$emailHistoryClass = new hikashopPlg_email_historyClass();
		$emailHistoryClass->initDB();

		$pageInfo = $this->getPageInfo('a.email_log_id', 'DESC');
		$pageInfo->filter->filter_type = $app->getUserStateFromRequest( $this->paramBase.".filter_type",'filter_type','','string');

		$filters = array();
		$order = '';
		$searchMap = array('a.email_log_recipient_email','a.email_log_id');
		if(!empty($pageInfo->filter->filter_type)){
			switch($pageInfo->filter->filter_type){
				case 'all':
					break;
				default:
					$filters[] = 'a.email_log_name = '.$database->Quote($pageInfo->filter->filter_type);
					break;
			}
		}
		$filters[] = 'a.email_log_published = 1';
		$this->processFilters($filters, $order, $searchMap);
		$query = ' FROM '.hikashop_table('email_log').' AS a'.$filters.$order;
		$this->getPageInfoTotal($query, '*');
		$database->setQuery('SELECT a.*'.$query,$pageInfo->limit->start,$pageInfo->limit->value);
		$rows = $database->loadObjectList();

		$fields = array('email_log_recipient_email', 'email_log_reply_email', 'email_log_subject');
		foreach($rows as &$row) {
			foreach($fields as $field) {
				if(isset($row->$field))
					$row->$field = $this->escape($row->$field);
			}
		}
		unset($row);

		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search, $rows, 'email_log_id');
		}


		$emailType = hikashop_get('type.email_log');
		$this->assignRef('filter_type',$emailType);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->getPagination();
		$this->getOrdering('a.email_log_date', true);
		$this->assignRef('order',$order);
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);

		$manage = hikashop_isAllowed($config->get('acl_email_log_manage','all'));
		$this->assignRef('manage',$manage);

		$this->toolbar = array(
			array('name' => 'editList','display'=>$manage),
			array('name' => 'deleteList','display'=>hikashop_isAllowed($config->get('acl_email_log_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);
	}

	function form() {
		$tabs = hikashop_get('helper.tabs');
		$email_log_id = hikashop_getCID('email_log_id');
		$config =& hikashop_config();

		$email_logClass = hikashop_get('class.plg_email_history');
		if(!empty($email_log_id)) {
			$element = $email_logClass->get($email_log_id,true);
			$task='edit';
		}
		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&email_log_id='.$email_log_id);

		$manage = hikashop_isAllowed($config->get('acl_email_log_manage','all'));
		$this->toolbar = array(
			array('name' => 'custom', 'icon' =>  'mail', 'alt' => JText::_('RESEND'), 'task' => 'resend', 'check' => false, 'display' => $manage),
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing')
		);
		$email_order_id = array(
			'order_admin_notification',
			'order_creation_notification',
			'order_status_notification',
			'order_notification',
			'payment_notification',
			'order_cancel'
		);
		$email_product_id = array(
			'new_comment',
		);
		$email_user_id = array(
			'user_account',
			'user_account_admin_notification',
		);

		if(!empty($data->email_log_params['contact_type']) && $data->email_log_params['contact_type'] ==  'order') {
			$email_order_id[] = 'contact_request';
		} else {
			$email_product_id[] = 'contact_request';
		}

		if(in_array($element->email_log_name,$email_product_id)){
			$productClass = hikashop_get('class.product');
			$productClass->getProducts($element->email_log_ref_id);
			if(isset($productClass->products[$element->email_log_ref_id]))
				$fullProduct = $productClass->products[$element->email_log_ref_id];
			elseif(isset($productClass->all_products[$element->email_log_ref_id]) && isset($productClass->all_products[$element->email_log_ref_id]->product_parent_id)){
				$productClass->getProducts($productClass->all_products[$element->email_log_ref_id]->product_parent_id);
				$fullProduct = $productClass->products[$productClass->all_products[$element->email_log_ref_id]->product_parent_id];
			}
			if(isset($fullProduct->product_name) && !empty($fullProduct->product_name))
				$this->assignRef('email_product_name',$fullProduct->product_name);
		}

		if(in_array($element->email_log_name,$email_order_id)){
			$orderClass = hikashop_get('class.order');
			$fullOrder = $orderClass->get($element->email_log_ref_id);
			if(isset($fullOrder->order_number) && !empty($fullOrder->order_number))
				$this->assignRef('email_order_number',$fullOrder->order_number);
		}

		if(in_array($element->email_log_name,$email_user_id)){
			$userClass = hikashop_get('class.user');
			$fullUser = $userClass->get($element->email_log_ref_id);
			if(isset($fullUser->name) && !empty($fullUser->name))
				$this->assignRef('email_user_name',$fullUser->name);
		}

		$this->assignRef('email_order_id',$email_order_id);
		$this->assignRef('email_product_id',$email_product_id);
		$this->assignRef('email_user_id',$email_user_id);

		$this->assignRef('tabs',$tabs);
		$this->assignRef('element',$element);
	}
}
