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
class FieldController extends hikashopController {
    var $delete = array();
    var $display = array();

	public function __construct($config = array()){
		parent::__construct($config);

		$this->modify_views = array('add_value');
		$this->modify = array('save_value');
	}

	function add_value(){
		hikaInput::get()->set( 'layout', 'add_value'  );
		return parent::display();
	}


	function save_value(){
		JSession::checkToken() || die('Invalid Token');

		$fieldClass = hikashop_get('class.field');
		$input = hikaInput::get();
        $id = $input->getInt('field_id');
        $field = $fieldClass->getField($id);

		if(in_array($field->field_table, array('product', 'category'))) {
            return false;
        }

		$fieldClass->addValue($id, $input->getVar('value_title'), $input->getVar('value_value'), $input->getVar('value_disabled'));
		hikaInput::get()->set( 'layout', 'save_value'  );
		return parent::display();
	}
}
