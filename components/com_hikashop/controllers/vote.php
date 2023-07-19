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
class VoteController extends hikashopController {
	var $modify_views = array();
	var $add = array();
	var $modify = array();
	var $delete = array();

	function __construct($config = array(),$skip=false){
		parent::__construct($config,$skip);
		if(!$skip){
			$this->registerDefaultTask('save');
		}
		$this->display[] = 'save';
		$this->display[] = 'show';
	}

	function save() {
		$config = hikashop_config();
		$voteClass = hikashop_get('class.vote');
		if(!count($_POST)){
			$app = JFactory::getApplication();
			$app->redirect(preg_replace('#ctrl=vote&task=save&[0-9a-z=]+#','',preg_replace('#/vote/save/[0-9a-z-]+#','',hikashop_currentURL())));
		}
		$hikashop_vote_type = hikaInput::get()->getVar('hikashop_vote_type', 'update', 'default', 'string', 0);
		$element = new stdClass();
		$element->vote_type	= hikaInput::get()->getVar('vote_type', '', 'default', 'string', 0);
		if($hikashop_vote_type == 'useful'){
			$element->vote_id  = hikaInput::get()->getVar('hikashop_vote_id', 0, 'default', 'int');
			$element->value  = hikaInput::get()->getVar('value', 0, 'default', 'int');
			$voteClass->saveUseful($element);
		}else{
			$element->vote_ref_id  = hikaInput::get()->getVar('hikashop_vote_ref_id', 0, 'default', 'int');
			if(empty($element->vote_ref_id) || $element->vote_ref_id == '0')
				$element->vote_ref_id = hikaInput::get()->getVar('hikashop_vote_product_id', 0, 'default', 'int');

			$element->vote_user_id = hikashop_loadUser();
			if(empty($element->vote_user_id))
				$element->vote_user_id = hikashop_getIP();
			$element->vote_pseudo = hikaInput::get()->getVar('pseudo_comment', 0, 'default', 'string', 0);
			$element->vote_email = hikaInput::get()->getVar('email_comment', 0, 'default', 'string', 0);
			$element->vote_type	= hikaInput::get()->getVar('vote_type', '', 'default', 'string', 0);
			$element->vote_rating = hikaInput::get()->getVar('hikashop_vote', 0, 'default', 'int');
			$element->vote_comment = hikaInput::get()->getRaw('hikashop_vote_comment', ''); // hikaInput::get()->getVar('hikashop_vote_comment', 0, 'default', 'string', 0);
			$element->vote_comment = urldecode($element->vote_comment);

			$captcha = hikaInput::get()->getRaw('recaptcha_comment', '');
			if(!empty($captcha))
				$element->recaptchaResponse = $captcha;

			if($config->get('enable_status_vote','nothing') != 'both' && !empty($element->vote_comment))
				$element->vote_rating = 0;

			$voteClass->save($element);
		}
		$return = array();
		if(!isset($voteClass->error) || empty($voteClass->error['code'])){
			$return['error'] = array('code' => '500001','message' => JText::_('VOTE_ERROR'));
		}elseif((int)$voteClass->error['code'] > 500000){
			$return['error'] = array('code' => $voteClass->error['code'],'message' => $voteClass->error['message']);
		}else{
			$return['success'] = array('code' => $voteClass->error['code'],'message' => $voteClass->error['message']);
			if(!empty($voteClass->values)){
				$return['values'] = array('average' => round($voteClass->values['average'],2), 'rounded' => round($voteClass->values['average']), 'total' => $voteClass->values['total']);
				$return['tooltip'] = JText::sprintf('HIKA_VOTE_TOOLTIP',round($voteClass->values['average'],2),$voteClass->values['total'],$element->vote_rating);
			}
		}
		ob_get_clean();
		echo json_encode($return);
		exit;
	}

	function show(){
		$data_id = hikaInput::get()->getVar('data_id', 0, 'default', 'int');
		$data_type = hikaInput::get()->getVar('main_ctrl', 'product', 'default', 'string', 0);
		$empty = $js = '';
		jimport('joomla.html.parameter');
		$params = new HikaParameter($empty);
		$params->set('vote_ref_id',$data_id);
		$params->set('main_ctrl',$data_type);
		$params->set('vote_type',$data_type);
		ob_get_clean();
		echo hikashop_getLayout('vote', 'listing', $params, $js);
		exit;
	}

}
