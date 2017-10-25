<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.2.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
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
		));

		$this->modify = array_merge($this->modify, array(
			'deleteaddress',
			'saveaddress',
			'setdefault',
		));

		$this->display = array_merge($this->display, array(
			'state',
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

	public function setdefault() {
		$newDefaultId = hikaInput::get()->getInt('address_default', 0);
		if($newDefaultId){
			if(!HIKASHOP_J25) {
				JRequest::checkToken('request') || die('Invalid Token');
			} else {
				JSession::checkToken('request') || die('Invalid Token');
			}
			$addressClass = hikashop_get('class.address');
			$oldData = $addressClass->get($newDefaultId);
			if(!empty($oldData)){
				$user_id = hikashop_getCID('user_id');
				if($user_id==$oldData->address_user_id){
					$oldData->address_default = 1;
					$addressClass->save($oldData);
				}
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

		if(!empty($_REQUEST['address']['address_id'])){
			$oldData = $addressClass->get($_REQUEST['address']['address_id']);
		}
		$fieldClass = hikashop_get('class.field');
		$addressData = $fieldClass->getInput('address',$oldData);
		$ok = true;
		if(empty($addressData)){
			$ok=false;
		}else{
			$address_id = $addressClass->save($addressData);
		}
		if(!$ok || !$address_id){
			$app =& JFactory::getApplication();
			if(version_compare(JVERSION,'1.6','<')){
				$session =& JFactory::getSession();
				$session->set('application.queue', $app->_messageQueue);
			}
			echo '<html><head><script type="text/javascript">javascript: history.go(-1);</script></head><body></body></html>';
			exit;
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

}
