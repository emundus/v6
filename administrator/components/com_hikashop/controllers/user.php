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
class UserController extends hikashopController {
	var $type = 'user';

	public function __construct($config = array()) {
		parent::__construct($config);

		$this->modify_views = array_merge($this->modify_views, array(
			'editaddress',
			'batch',
			'pay',
			'pay_process'
		));

		$this->modify = array_merge($this->modify, array(
			'deleteaddress',
			'saveaddress',
			'setdefault',
			'pay_confirm',
		));

		$this->display = array_merge($this->display, array(
			'state',
			'clicks',
			'leads',
			'sales',
			'selection',
			'useselection',
			'getValues',
		));
	}

	protected function getACLName($task) {
		$app = JFactory::getApplication();
		if($app->getUserStateFromRequest(HIKASHOP_COMPONENT.'.user.filter_partner', 'filter_partner', '', 'int') == 1) {
			return 'affiliates';
		}
		return 'user';
	}

	public function deleteaddress() {
		$addressdelete = hikaInput::get()->getInt('address_id',0);
		if($addressdelete){
			$addressClass = hikashop_get('class.address');
			$oldData = $addressClass->get($addressdelete);
			if(!empty($oldData)){
				$addressClass->delete($addressdelete);
				hikaInput::get()->set('user_id',$oldData->address_user_id);
			}
		}
		$this->edit();
	}

	public function batch(){
		$params = new HikaParameter('');
		$params->set('table', 'user');
		$js = '';
		echo hikashop_getLayout('massaction', 'batch', $params, $js);
	}

	public function setdefault() {
		$newDefaultId = hikaInput::get()->getInt('address_default', 0);
		if(!$newDefaultId)
			return $this->edit();

		JSession::checkToken('request') || die('Invalid Token');

		$addressClass = hikashop_get('class.address');
		$oldData = $addressClass->get($newDefaultId);
		if(!empty($oldData)) {
			$user_id = hikashop_getCID('user_id');
			if($user_id==$oldData->address_user_id) {
				$oldData->address_default = 1;
				$addressClass->save($oldData);
			}
		}
		$this->edit();
	}

	public function cancel() {
		$order_id = hikaInput::get()->getInt('order_id');
		if(empty($order_id)){
			$cancel_redirect = hikaInput::get()->getString('cancel_redirect');
			if(empty($cancel_redirect)){
				$this->listing();
			}else{
				$cancel_redirect = base64_decode(urldecode($cancel_redirect));
				if(hikashop_disallowUrlRedirect($cancel_redirect)) return false;
				$this->setRedirect($cancel_redirect);
			}
		}else{
			$this->setRedirect(hikashop_completeLink('order&task=edit&order_id='.$order_id,false,true));
		}
	}

	public function saveaddress() {
		$addressClass = hikashop_get('class.address');
		$oldData = null;
		$type = '';
		if(!empty($_REQUEST['data']['address']['address_id'])) {
			$oldData = $addressClass->get($_REQUEST['data']['address']['address_id']);
			if(!empty($oldData->address_type))
				$type = $oldData->address_type . '_';
		} else {
			if(in_array(@$_REQUEST['data']['address']['address_type'], array('billing', 'shipping')))
				$type = $_REQUEST['data']['address']['address_type'] . '_';
		}
		$type .= 'address';
		$fieldClass = hikashop_get('class.field');
		$addressData = $fieldClass->getInput(array('address', $type),$oldData);
		$new = empty($addressData->address_id);
		$ok = true;
		if(empty($addressData)) {
			$ok=false;
		}else {
			if(in_array(@$addressData->address_type, array('billing', '', 'both','shipping')))
				$address_id = $addressClass->save($addressData);
		}
		if(!$ok || !@$address_id) {
			$app = JFactory::getApplication();
			echo '<html><head><script type="text/javascript">javascript: history.go(-1);</script></head><body></body></html>';
			exit;
		}

		if($new) {
			$same_address = hikaInput::get()->getInt('same_address');
			if(!empty($same_address)) {
				unset($addressData->address_id);
				if(!empty($addressData->address_type)) {
					if($addressData->address_type == 'billing')
						$addressData->address_type = 'shipping';
					elseif($addressData->address_type == 'shipping')
						$addressData->address_type = 'billing';
					else
						unset($addressData->address_type);
					if(!empty($addressData->address_type)) {
						$addressClass->save($addressData);
					}
				}
			}
		}
		$url = hikashop_completeLink('user&task=edit&user_id='.$addressData->address_user_id,false,true);
		echo '<html><head><script type="text/javascript">parent.window.location.href=\''.$url.'\';</script></head><body></body></html>';
		exit;
	}

	public function editaddress() {
		hikaInput::get()->set('layout', 'editaddress');
		return parent::display();
	}

	public function state() {
		hikaInput::get()->set('layout', 'state');
		return parent::display();
	}

	public function selection() {
		hikaInput::get()->set('layout', 'selection');
		return parent::display();
	}

	public function useselection() {
		hikaInput::get()->set('layout', 'useselection');
		return parent::display();
	}

	public function getValues() {
		$displayFormat = hikaInput::get()->getVar('displayFormat', '');
		$search = hikaInput::get()->getVar('search', null);
		$start = hikaInput::get()->getInt('start', 0);

		$nameboxType = hikashop_get('type.namebox');
		$options = array(
			'start' => $start,
			'displayFormat' => $displayFormat
		);
		$ret = $nameboxType->getValues($search, 'user', $options);
		if(!empty($ret)) {
			echo json_encode($ret);
			exit;
		}
		echo '[]';
		exit;
	}

	public function pay_confirm(){
		$user_id = hikashop_getCID('user_id');

		if(empty($user_id)) {
			$url = hikashop_completeLink('user&task=edit&user_id='.$user_id,false,true);
			echo '<html><head><script type="text/javascript">parent.window.location.href=\''.$url.'\';</script></head><body></body></html>';
			exit;
		}

		$userClass = hikashop_get('class.user');
		$user = $userClass->get($user_id);

		if(empty($user)) {
			$url = hikashop_completeLink('user&task=edit&user_id='.$user_id,false,true);
			echo '<html><head><script type="text/javascript">parent.window.location.href=\''.$url.'\';</script></head><body></body></html>';
			exit;
		}

		$userClass->loadPartnerData($user);
		if(!bccomp(sprintf('%F',$user->accumulated['currenttotal']), 0, 5)) {
			$app = JFactory::getApplication();
			$app->enqueueMessage('No affiliate money accumulated');
			return false;
		}

		$config =& hikashop_config();
		if(!$config->get('allow_currency_selection',0) || empty($user->user_currency_id)) {
			$user->user_currency_id =  $config->get('partner_currency', 1);
		}

		$method = hikaInput::get()->getCmd('pay_method');
		$pay = hikaInput::get()->getInt('pay',0);

		$order = new stdClass();
		$order->order_currency_id = $user->user_currency_id;
		$order->order_full_price = $user->accumulated['currenttotal'];

		if(!empty($method) && $pay) {
			$pluginClass = hikashop_get('class.plugins');
			$methods = $pluginClass->getMethods('payment');
			foreach($methods as $methodItem){
				if($methodItem->payment_type==$method){
					$order->order_payment_id = $methodItem->payment_id;
					$order->order_payment_method = $methodItem->payment_type;
					break;
				}
			}

			if(empty($order->order_payment_id)) {
				$app = JFactory::getApplication();
				$app->enqueueMessage('Payment method not found');
				return false;
			}
		}

		$order->order_user_id = $user->user_id;
		$order->order_status = $config->get('order_confirmed_status','confirmed');
		$order->order_type = 'partner';

		$order->history = new stdClass();
		$order->history->history_reason = JText::sprintf('ORDER_CREATED');
		$order->history->history_notified = 0;
		$order->history->history_type = 'creation';

		$product = new stdClass();
		$product->order_product_name = JText::sprintf('PAYMENT_TO_PARTNER',@$user->name.' ('.$user->user_partner_email.')');
		$product->order_product_code = '';
		$product->order_product_price = $user->accumulated['currenttotal'];
		$product->order_product_quantity = 1;
		$product->order_product_tax = 0;
		$product->order_product_options = '';
		$product->product_id = 0;

		$order->cart = new stdClass();
		$order->cart->products = array($product);

		$orderClass = hikashop_get('class.order');
		$order->order_id = $orderClass->save($order);

		if(!empty($order->order_id)) {
			$minDelay = $config->get('affiliate_payment_delay', 0);
			$maxTime = intval(time() - $minDelay);

			$db = JFactory::getDBO();

			$query = 'UPDATE '.hikashop_table('click').' SET click_partner_paid = 1 WHERE click_partner_id = '.$user->user_id.' AND click_created < '.$maxTime;
			$db->setQuery($query);
			$db->execute();

			$query = 'UPDATE '.hikashop_table('order').' SET order_partner_paid = 1 WHERE order_type = \'sale\' AND order_partner_id = '.$user->user_id.' AND order_created < '.$maxTime;
			$db->setQuery($query);
			$db->execute();

			$query = 'UPDATE '.hikashop_table('user').' SET user_partner_paid = 1 WHERE user_partner_id = '.$user->user_id.' AND user_created < '.$maxTime;
			$db->setQuery($query);
			$db->execute();

			if(!empty($order->order_payment_id) && $pay) {
				$url = hikashop_completeLink('user&task=pay_process&order_id='.$order->order_id,false,true);
				echo '<html><head><script type="text/javascript">parent.window.location.href=\''.$url.'\';</script></head><body></body></html>';
				exit;
			}
		}

		$url = hikashop_completeLink('user&task=edit&user_id='.$user_id,false,true);
		echo '<html><head><script type="text/javascript">parent.window.location.href=\''.$url.'\';</script></head><body></body></html>';
		exit;
	}

	public function pay_process() {
		$order_id = hikashop_getCID('order_id');
		if(empty($order_id)){
			return false;
		}

		$orderClass = hikashop_get('class.order');
		$order = $orderClass->get($order_id);

		$userClass = hikashop_get('class.user');
		$user = $userClass->get($order->order_user_id);

		$orderClass->loadProducts($order);
		$order->cart->products =& $order->products;

		$pluginClass = hikashop_get('class.plugins');
		$methods = $pluginClass->getMethods('payment');

		$methods[$order->order_payment_id]->payment_params->address_type = '';
		$methods[$order->order_payment_id]->payment_params->cancel_url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=user&task=edit&user_id='.$user->user_id;
		$methods[$order->order_payment_id]->payment_params->return_url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=user&task=edit&user_id='.$user->user_id;
		$methods[$order->order_payment_id]->payment_params->email = $user->user_partner_email;

		$data = hikashop_import('hikashoppayment', $order->order_payment_method);
		$data->onAfterOrderConfirm($order, $methods, $order->order_payment_id);
	}

	public function clicks(){
		hikaInput::get()->set('layout', 'clicks');
		return parent::display();
	}

	public function leads(){
		hikaInput::get()->set('layout', 'leads');
		return parent::display();
	}
	public function sales(){
		hikaInput::get()->set('layout', 'sales');
		return parent::display();
	}

	public function pay(){
		hikaInput::get()->set('layout', 'pay');
		return parent::display();
	}
}
