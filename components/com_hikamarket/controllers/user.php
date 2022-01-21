<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class userMarketController extends hikamarketController {
	protected $rights = array(
		'display' => array('listing','state','show','address','getaddresslist','getvalues'),
		'add' => array(),
		'edit' => array(),
		'modify' => array('apply','save'),
		'delete' => array()
	);

	protected $type = 'user';
	protected $config = null;

	public function __construct($config = array(), $skip = false) {
		parent::__construct($config, $skip);
		if(!$skip)
			$this->registerDefaultTask('listing');
		$this->config = hikamarket::config();
	}

	public function edit() {
		return $this->show();
	}

	public function show() {
		if(!hikamarket::loginVendor())
			return false;
		if(!$this->config->get('frontend_edition',0))
			return false;

		$customer_id = hikamarket::getCID();
		$vendor_id = hikamarket::loadVendor(false, false);
		if($vendor_id > 1 && !hikamarket::isVendorCustomer($customer_id))
			return false;

		if(!hikamarket::acl('user/show'))
			return false;

		hikaInput::get()->set('layout', 'show');
		return parent::display();
	}

	public function address() {
		if(!hikamarket::loginVendor())
			return false;
		if(!$this->config->get('frontend_edition',0))
			return false;

		$vendor_id = hikamarket::loadVendor(false, false);
		if($vendor_id > 1)
			return false;

		if(!hikamarket::acl('user/edit/address'))
			return false;

		hikaInput::get()->set('layout', 'address');

		$tmpl = hikaInput::get()->getCmd('tmpl', '');
		$subtask = hikaInput::get()->getCmd('subtask', '');
		if($subtask == 'edit')
			hikaInput::get()->set('edition', true);

		if($subtask == 'listing') {
			$user_id = hikaInput::get()->getInt('user_id');
			if(empty($user_id))
				return false;
			hikaInput::get()->set('layout', 'show_address');
		}

		if($subtask == 'save') {
			JSession::checkToken('request') || die('Invalid Token');

			$user_id = hikaInput::get()->getInt('user_id');
			if($user_id > 0) {
				$addressClass = hikamarket::get('class.address');
				$result = $addressClass->frontSaveForm($user_id, 'display:vendor_user_edit=1');
			}
			if(empty($result)) {
				hikaInput::get()->set('edition', true);
			} else {
				hikaInput::get()->set('previous_cid', $result->previous_id);
				hikaInput::get()->set('cid', $result->id);
			}
		}

		if($subtask == 'delete') {
			JSession::checkToken('request') || die('Invalid Token');
			$address_id = hikamarket::getCID('address_id');
			$user_id = hikaInput::get()->getInt('user_id');
			$addressClass = hikamarket::get('class.address');
			$addr = $addressClass->get($address_id);
			if(!empty($addr) && $addr->address_user_id == $user_id) {
				$ret = $addressClass->delete($addr);

				if($tmpl == 'component') {
					ob_end_clean();
					if(!empty($ret))
						echo '1';
					else
						echo '0';
					exit;
				}
				if(in_array($tmpl, array('ajax', 'raw'))) {
					hikaInput::get()->set('layout', 'show_address');
					hikaInput::get()->set('hidemainmenu', 1);
					ob_end_clean();
					parent::display();
					exit;
				}

				$app = JFactory::getApplication();
				if($ret)
					$app->enqueueMessage(JText::_('ADDRESS_DELETED_WITH_SUCCESS'));
				else
					$app->enqueueMessage(JText::_('ADDRESS_NOT_DELETED'), 'error');
				$app->redirect( hikamarket::completeLink('user&task=show&cid=' . $user_id) );
			}
			return false;
		}

		if(in_array($tmpl, array('component', 'ajax', 'raw'))) {
			hikaInput::get()->set('hidemainmenu', 1);
			ob_end_clean();
			parent::display();
			exit;
		}
		return parent::display();
	}

	public function listing() {
		if(!hikamarket::loginVendor())
			return false;
		if(!$this->config->get('frontend_edition',0))
			return false;
		if(!hikamarket::acl('user/listing'))
			return false;

		hikaInput::get()->set('layout', 'listing');
		return parent::display();
	}

	public function store() {
		if(!hikamarket::loginVendor())
			return false;
		if(!$this->config->get('frontend_edition',0))
			return false;
		$vendor_id = hikamarket::loadVendor(false, false);
		if($vendor_id > 1)
			return false;

		$redirection = 'user';
		if(!hikamarket::acl('user/listing'))
			$redirection = 'vendor';
		if( !hikamarket::acl('user/edit') )
			return hikamarket::deny($redirection, JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_USER_EDIT')));

		$userClass = hikamarket::get('class.user');
		if( $userClass === null )
			return false;
		$status = $userClass->frontSaveForm();
		if($status) {
			hikaInput::get()->set('cid', $status);
			hikaInput::get()->set('fail', null);
		}

		return $status;
	}

	public function state() {
		if(!hikamarket::loginVendor())
			return false;
		if(!$this->config->get('frontend_edition',0))
			return false;
		hikaInput::get()->set('layout', 'state');
		return parent::display();
	}

	public function getAddressList() {
		while(ob_get_level())
			@ob_end_clean();

		if(!hikamarket::loginVendor() || !$this->config->get('frontend_edition',0)) {
			echo '[]';
			exit;
		}

		$user_id = hikaInput::get()->getInt('user_id', 0);
		$address_type = hikaInput::get()->getCmd('address_type', '');
		$displayFormat = hikaInput::get()->getString('displayFormat', '{address_mini_format}');
		$search = hikaInput::get()->getString('search', null);

		if(!hikamarket::isVendorCustomer($user_id, null, true)) {
			echo '[]';
			exit;
		}

		$nameboxType = hikamarket::get('type.namebox');
		$options = array(
			'url_params' => array(
				'USER_ID' => $user_id,
				'ADDR_TYPE' => $address_type,
			),
			'displayFormat' => $displayFormat
		);

		$ret = $nameboxType->getValues($search, 'address', $options);
		if(!empty($ret)) {
			echo json_encode($ret);
			exit;
		}
		echo '[]';
		exit;
	}

	public function getValues() {
		if(!hikamarket::loginVendor() || !$this->config->get('frontend_edition',0) || !hikamarket::acl('user/listing')) {
			echo '[]';
			exit;
		}

		$displayFormat = hikaInput::get()->getString('displayFormat', '');
		$search = hikaInput::get()->getString('search', null);
		$start = hikaInput::get()->getInt('start', 0);

		$nameboxType = hikamarket::get('type.namebox');
		$options = array(
			'displayFormat' => $displayFormat,
			'start' => $start
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
