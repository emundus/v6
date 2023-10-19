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
class plgHikashopAcymailing extends JPlugin {
	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
		if(isset($this->params))
			return;
		$plugin = JPluginHelper::getPlugin('hikashop', 'acymailing');
		$this->params = new JRegistry($plugin->params);
	}

	function onAfterOrderCreate(&$order,&$send_email) {
		return $this->onAfterOrderUpdate($order,$send_email);
	}

	function onAfterOrderUpdate(&$order,&$send_email){
		if(!empty($order->order_id) && !empty($order->order_status)){
			if(empty($order->order_user_id)){
				$class = hikashop_get('class.order');
				$old = $class->get($order->order_id);
				$order->order_user_id = $old->order_user_id;
			}
			$userClass = hikashop_get('class.user');
			$user = $userClass->get($order->order_user_id);
			if(!empty($user)){
				$helper = rtrim(str_replace('/',DS,JPATH_ADMINISTRATOR),DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php';
				if(file_exists($helper)){
					include_once($helper);
					if(function_exists('acymailing_get')){
						$subClass = acymailing_get('class.subscriber');
						$sub = $subClass->get($user->email);
						if(!empty($sub->subid)){
							if(file_exists(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_acymailing'.DS.'classes'.DS.'filter.php')){
								$filterClass = acymailing_get('class.filter');
								if($filterClass){
									$filterClass->subid = $sub->subid;
									$filterClass->trigger('hikaorder_'.$order->order_status);
								}
							}
						}
					}
				}
			}
		}
		return true;
	}

	function onHikashopAfterCheckDB(&$ret){
		$helper = rtrim(str_replace('/',DS,JPATH_ADMINISTRATOR),DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php';
		if(!file_exists($helper)) return;
		$db = JFactory::getDBO();
		$query = 'INSERT IGNORE INTO `#__acymailing_subscriber` (`email`,`userid`,`created`) SELECT `user_email`, `user_cms_id`,'.time().' FROM `#__hikashop_user`';
		$db->setQuery($query);
		try{
			$result = $db->execute();
		} catch(Exception $e) {
			$ret[] = array(
					'error',
					'User emails not synchronized with AcyMailing ('.$e->getMessage().')'
			);
			return;
		}
		$ret[] = array(
			'success',
			'User emails synchronized with AcyMailing'
		);
	}

	function onAfterUserCreate(&$hikauser){
		$helper = rtrim(str_replace('/',DS,JPATH_ADMINISTRATOR),DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php';
		if(!file_exists($helper) || empty($hikauser->user_email)) return;
		include_once($helper);

		if(!function_exists('acymailing_get')){
			return;
		}

		$subscriberClass = acymailing_get('class.subscriber');
		$subscriberClass->geolocRight = true;
		$subscriberClass->checkVisitor = false;
		$subid = $subscriberClass->subid($hikauser->user_email);
		if(!empty($subid)) return;

		$acysub = new stdClass();
		$acysub->name = '';
		if(!empty($_REQUEST['data']['address']['address_firstname'])) $acysub->name .= $_REQUEST['data']['address']['address_firstname'].' ';
		if(!empty($_REQUEST['data']['address']['address_middle_name'])) $acysub->name .= $_REQUEST['data']['address']['address_middle_name'].' ';
		if(!empty($_REQUEST['data']['address']['address_lastname'])) $acysub->name .= $_REQUEST['data']['address']['address_lastname'].' ';
		$acysub->name = trim(strip_tags($acysub->name));
		$acysub->email = $hikauser->user_email;
		$acysub->confirmed = 1;
		$acysub->enabled = 1;

		$customValues = hikaInput::get()->get('regacy', array(), 'array');
		$session = JFactory::getSession();
		if(empty($customValues) && $session->get('regacy')){
			$customValues = $session->get('regacy');
			$session->set('regacy',null );
		}
		if(!empty($customValues)){
			$subscriberClass->checkFields($customValues,$acysub);
		}

		$subid = $subscriberClass->save($acysub);

		if(empty($subid)) return;

		$config = acymailing_config();

		$listsToSubscribe = $config->get('autosub','None');
		$currentSubscription = $subscriberClass->getSubscriptionStatus($subid);

		$listsClass = acymailing_get('class.list');
		$allLists = $listsClass->getLists('listid');
		if(acymailing_level(1)){
			$allLists = $listsClass->onlyCurrentLanguage($allLists);
		}

		$visiblelistschecked = hikaInput::get()->get('acysub', array(), 'array');
		$acySubHidden = hikaInput::get()->getString( 'acysubhidden');
		if(!empty($acySubHidden)){
			$visiblelistschecked = array_merge($visiblelistschecked,explode(',',$acySubHidden));
		}

		$session = JFactory::getSession();
		if(empty($visiblelistschecked) && $session->get('acysub')){
			$visiblelistschecked = $session->get('acysub');
			$session->set('acysub',null );
		}

		$listsArray = array();
		if(strpos($listsToSubscribe,',') OR is_numeric($listsToSubscribe)){
			$listsArrayParam = explode(',',$listsToSubscribe);
			foreach($allLists as $oneList){
				if($oneList->published AND (in_array($oneList->listid,$visiblelistschecked) || in_array($oneList->listid,$listsArrayParam))){$listsArray[] = $oneList->listid;}
			}
		}elseif(strtolower($listsToSubscribe) == 'all'){
			foreach($allLists as $oneList){
				if($oneList->published){$listsArray[] = $oneList->listid;}
			}
		}elseif(!empty($visiblelistschecked)){
			foreach($allLists as $oneList){
				if($oneList->published AND in_array($oneList->listid,$visiblelistschecked)){$listsArray[] = $oneList->listid;}
			}
		}

		$statusAdd = 1;
		$addlists = array();
		if(!empty($listsArray)){
			foreach($listsArray as $idOneList){
				if(!isset($currentSubscription[$idOneList])){
					$addlists[$statusAdd][] = $idOneList;
				}
			}
		}

		if(!empty($addlists)) {
			$listsubClass = acymailing_get('class.listsub');
			$listsubClass->addSubscription($subid,$addlists);
		}
	}
}
