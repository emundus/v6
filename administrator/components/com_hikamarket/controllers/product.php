<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class productMarketController extends hikamarketController {
	protected $type = 'product';

	protected $rights = array(
		'display' => array('gettree','waitingapproval'),
		'add' => array('new_template'),
		'edit' => array('approve','decline'),
		'modify' => array(),
		'delete' => array('remove')
	);

	public function new_template() {
		$productClass = hikamarket::get('shop.class.product');
		$product = new stdClass();
		$product->product_type = 'template';
		$product->product_code = '@template-' . uniqid();
		$status = $productClass->save($product);
		if($status) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('PRODUCT_TEMPLATE_CREATED'));
			$app->redirect( hikamarket::completeLink('shop.product&task=edit&cid=' . $status, false, true) );
		}
		return false;
	}

	public function waitingapproval() {
		$config = hikamarket::config();
		if(!$config->get('product_approval', 0))
			return false;
		hikaInput::get()->set('layout', 'waitingapproval');
		return parent::display();
	}

	protected function confirm_action($action = null) {
		hikaInput::get()->set('action_to_confirm', $action);
		hikaInput::get()->set('layout', 'confirm_action');
		return parent::display();
	}

	protected function checkConfirmation($action) {
		$product_ids = hikaInput::get()->get('cid', array(), 'array');
		hikamarket::toInteger($product_ids);
		asort($product_ids);
		$confirmation = md5($action.'{'.implode(':',$product_ids).'}');

		$user_confirmation = hikaInput::get()->getString('confirmation', null);
		return ($confirmation == $user_confirmation);
	}

	public function approve() {
		$product_id = hikaInput::get()->get('cid', array(), 'array');
		if(empty($product_id))
			$product_id = hikamarket::getCID('product_id');
		if(empty($product_id)) {
			$app->redirect(hikamarket::completeLink('product&task=waitingapproval', false, true));
			return false;
		}

		if(!empty($product_id) && !$this->checkConfirmation('approve'))
			return $this->confirm_action('approve');

		JSession::checkToken('request') || die('invalid token');

		if(is_array($product_id) && count($product_id) == 1)
			$product_id = (int)reset($product_id);
		if(is_array($product_id))
			hikamarket::toInteger($product_id);

		$app = JFactory::getApplication();
		$productClass = hikamarket::get('class.product');

		$formData = hikaInput::get()->get('data', array(), 'array');

		$options = null;
		if(isset($formData['notify'])) {
			$options = array(
				'notify' => (int)@$formData['notify']['send'],
				'message' => $formData['notify']['msg']
			);
		}

		$status = $productClass->approve($product_id, 'approve', $options);

		if($status) {
			if(is_array($product_id))
				$app->enqueueMessage(JText::sprintf('HIKAMARKET_X_PRODUCTS_APPROVED', $status));
			else
				$app->enqueueMessage(JText::_('HIKAMARKET_PRODUCT_APPROVED'));
		}

		$this->approveDeclineRedirect($product_id);
	}

	public function decline() {
		$product_id = hikaInput::get()->get('cid', array(), 'array');
		if(empty($product_id))
			$product_id = hikamarket::getCID('product_id');
		if(empty($product_id)) {
			$app->redirect(hikamarket::completeLink('product&task=waitingapproval', false, true));
			return false;
		}

		if(!empty($product_id) && !$this->checkConfirmation('decline'))
			return $this->confirm_action('decline');

		JSession::checkToken('request') || die('invalid token');

		if(is_array($product_id) && count($product_id) == 1)
			$product_id = (int)reset($product_id);
		if(is_array($product_id))
			hikamarket::toInteger($product_id);

		$app = JFactory::getApplication();
		$productClass = hikamarket::get('class.product');

		$formData = hikaInput::get()->get('data', array(), 'array');

		$options = null;
		if(isset($formData['notify'])) {
			$options = array(
				'notify' => (int)@$formData['notify']['send'],
				'message' => $formData['notify']['msg']
			);
		}

		$status = $productClass->decline($product_id, $options);

		if($status) {
			if(is_array($product_id))
				$app->enqueueMessage(JText::sprintf('HIKAMARKET_X_PRODUCTS_DECLINED', $status));
			else
				$app->enqueueMessage(JText::_('HIKAMARKET_PRODUCT_DECLINED'));
		}

		$this->approveDeclineRedirect();
	}

	protected function approveDeclineRedirect($product_id = 0) {
		$app = JFactory::getApplication();
		$redirect = hikaInput::get()->getString('redirect', '');
		if(empty($product_id) || is_array($product_id))
			$redirect = 'waitingapproval';

		if(!empty($redirect)) {
			switch($redirect) {
				case 'waitingapproval':
					$app->redirect(hikamarket::completeLink('product&task=waitingapproval', false, true));
					return;
			}
		}

		$cancel_redirect = hikaInput::get()->getString('cancel_redirect', '');
		if(!empty($cancel_redirect))
			$cancel_redirect = '&cancel_redirect=' .urlencode($cancel_redirect);
		$app->redirect( hikamarket::completeLink('shop.product&task=edit&cid='.$product_id.$cancel_redirect, false, true) );
	}

	public function remove() {
		if(!$this->checkConfirmation('remove'))
			return $this->confirm_action('remove');

		$this->adminRemove();

		$app = JFactory::getApplication();
		$app->redirect(hikamarket::completeLink('product&task=waitingapproval', false, true));
	}

	public function getTree() {
		$category_id = hikaInput::get()->getInt('category_id', 0);
		$displayFormat = hikaInput::get()->getString('displayFormat', '');
		$variants = hikaInput::get()->getInt('variants', 0);
		$search = hikaInput::get()->getVar('search', null);
		$start = hikaInput::get()->getInt('start', 0);

		$namebox_mode = hikaInput::get()->getCmd('namebox_mode', 'product');
		if(!in_array($namebox_mode, array('product', 'product_template')))
			$namebox_mode = 'product';

		$nameboxType = hikamarket::get('type.namebox');
		$options = array(
			'start' => $category_id,
			'page' => $start,
			'displayFormat' => $displayFormat,
			'variants' => $variants
		);
		$ret = $nameboxType->getValues($search, $namebox_mode, $options);
		if(!empty($ret)) {
			echo json_encode($ret);
			exit;
		}
		echo '[]';
		exit;
	}
}
