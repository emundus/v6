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
class affiliateController extends hikashopController{
	var $type='user';
	function __construct($config = array(),$skip=false){
		parent::__construct($config,$skip);
		$this->display = array('clicks','leads','sales','cancel','');
		$this->modify_views = array('show');
		$this->add = array();
		$this->modify = array('save','apply');
		$this->delete = array();
		if(!$skip){
			$this->registerDefaultTask('show');
		}
	}
	function cancel(){
		$app = JFactory::getApplication();
		global $Itemid;
		$url = '';
		if(!empty($Itemid)){
			$url='&Itemid='.$Itemid;
		}
		$app->redirect(hikashop_completeLink('user'.$url,false,true));
	}

	function listing(){
		return $this->show();
	}

	function show(){
		$this->_checkProgramActive('show');
	}
	function apply(){
		$status = $this->store();
		return $this->show();
	}

	function clicks(){
		return $this->_checkStats('clicks');
	}

	function leads(){
		return $this->_checkStats('leads');
	}

	function sales(){
		return $this->_checkStats('sales');
	}

	function store($new=false){
		if(hikashop_getCID('user_id')!=hikashop_loadUser()){
			return false;
		}
		$app = JFactory::getApplication();

		$requestData = hikaInput::get()->getVar('data');
		if(empty($requestData['user']))
			return false;


		$userClass = hikashop_get('class.user');
		$user = new stdClass();
		$user->user_id = hikashop_getCID('user_id');
		$user->user_partner_activated = (int)@$requestData['user']['user_partner_activated'];
		$user->user_partner_email = @$requestData['user']['user_partner_email'];
		$config =& hikashop_config();
		if($config->get('allow_currency_selection')){
			$user->user_currency_id = (int)@$requestData['user']['user_currency_id'];
		}

		$status = $userClass->save($user);
		if($status) {
			if(!HIKASHOP_J30)
				$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ), 'success');
			else
				$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ));
			if(!$new) hikaInput::get()->set( 'cid', $status  );
			else hikaInput::get()->set( 'cid', 0  );
			hikaInput::get()->set( 'fail', null  );
		} else {
			$app->enqueueMessage(JText::_( 'ERROR_SAVING' ), 'error');
			if(!empty($userClass->errors)){
				foreach($userClass->errors as $oneError){
					$app->enqueueMessage($oneError, 'error');
				}
			}
		}
		return $status;
	}

	function _checkStats($type=''){
		$config =& hikashop_config();
		$advanced_stats = $config->get('affiliate_advanced_stats',1);
		if($advanced_stats && hikashop_loadUser()){
			if($this->_checkProgramActive()){
				hikaInput::get()->set( 'layout', $type );
				return $this->display();
			}else{
				return false;
			}
		}else{
			return $this->show();
		}
	}

	function _checkProgramActive($type=''){
		$plugin = JPluginHelper::getPlugin('system', 'hikashopaffiliate');
		if(empty($plugin)){
			$app = JFactory::getApplication();
			$app->enqueueMessage('Affiliate program not available. Please make sure the HikaShop Affiliate System plugin is enabled.','error');
			return false;
		}
		if(!empty($type)){
			parent::$type();
		}
		return true;
	}
}
