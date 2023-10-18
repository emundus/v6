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
class DiscountController extends hikashopController{
	var $toggle = array('discount_published'=>'discount_id');
	var $type='discount';

	function __construct($config = array()) {
		parent::__construct($config);
		$this->modify_views[]='select_coupon';
		$this->modify_views[]='add_coupon';
		$this->modify[]='copy';
		$this->modify_views[]='form';
		$this->display[]='selection';
		$this->display[]='export';
		$this->modify[]='useselection';
		$this->display[]='findList';
	}
	function form(){
		return $this->edit();
	}

	function copy(){
		$discounts = hikaInput::get()->get('cid', array(), 'array');
		$result = true;
		if(!empty($discounts)){
			$discountClass = hikashop_get('class.discount');
			foreach($discounts as $discount){
				$data = $discountClass->get($discount);
				if($data){
					unset($data->discount_id);
					$data->discount_code = $data->discount_code.'_copy'.rand();
					if(!$discountClass->save($data)){
						$result=false;
					}
				}
			}
		}
		if($result){
			$app = JFactory::getApplication();
			if(!HIKASHOP_J30)
				$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ), 'success');
			else
				$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ));
			return $this->listing();
		}
		return $this->form();
	}

	function export(){
		hikaInput::get()->set( 'layout', 'export'  );
		return parent::display();
	}

	function select_coupon(){
		hikaInput::get()->set( 'layout', 'select_coupon'  );
		return parent::display();
	}

	function add_coupon(){
		hikaInput::get()->set( 'layout', 'add_coupon'  );
		return parent::display();
	}

	function selection(){
		hikaInput::get()->set('layout', 'selection');
		return parent::display();
	}
	function useselection(){
		hikaInput::get()->set('layout', 'useselection');
		return parent::display();
	}

	public function findList() {
		$search = hikaInput::get()->getVar('search', '');
		$start = hikaInput::get()->getInt('start', 0);
		$type = hikaInput::get()->getVar('type', '');
		$displayFormat = hikaInput::get()->getVar('displayFormat', '');

		$types = array(
			'discount' => 'discount',
			'coupon' => 'coupon'
		);
		if(!empty($type) && !isset($types[$type])) {
			echo '[]';
			exit;
		}

		$options = array();

		if(!empty($displayFormat))
			$options['displayFormat'] = $displayFormat;
		if($start > 0)
			$options['start'] = $start;
		if(!empty($type))
			$options['type'] = $type;

		$nameboxType = hikashop_get('type.namebox');
		$elements = $nameboxType->getValues($search, 'discount', $options);
		echo json_encode($elements);
		exit;
	}
}
