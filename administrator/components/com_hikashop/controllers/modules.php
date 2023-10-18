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
class ModulesController extends hikashopController{
	var $toggle = array();
	var $type='modules';

	function __construct(){
		parent::__construct();
		$this->display[]='selectmodules';
		$this->display[]='savemodules';
		$this->display[]='showoptions';
		$this->display[]='getValues';
	}

	function selectmodules(){
		hikaInput::get()->set('layout', 'selectmodules');
		return parent::display();
	}

	function savemodules(){
		hikaInput::get()->set('layout', 'savemodules');
		return parent::display();
	}

	function edit(){
		if(hikaInput::get()->getInt('fromjoomla')){
			$app = JFactory::getApplication();
			$context = 'com_modules.edit.module';
			$id = hikashop_getCID('id');
			if($id){
				$values = (array) $app->getUserState($context . '.id');
				$index = array_search((int) $id, $values, true);
				if (is_int($index)){
					unset($values[$index]);
					$app->setUserState($context . '.id', $values);
				}
			}
		}
		return parent::edit();
	}

	function showoptions(){
		$js = '';
		jimport('joomla.html.parameter');
		$params = new hikaParameter();
		$params->set('id',hikaInput::get()->getVar('id','product'));
		$params->set('name',hikaInput::get()->getVar('id','product'));
		$value = hikashop_unserialize(hikaInput::get()->getVar('value')); // TODO : See to improve the security :)
		$value['content_type'] = hikaInput::get()->getVar('content_type','product');
		if($value['content_type'] == 'manufacturer')
			$value['content_type'] = 'category';
		$params->set('value',$value);
		echo hikashop_getLayout('modules','options',$params,$js);
	}

	public function getValues() {
		$displayFormat = hikaInput::get()->getVar('displayFormat', '');
		$search = hikaInput::get()->getVar('search', null);
		$start = hikaInput::get()->getInt('start', 0);

		$nameboxType = hikashop_get('type.namebox');
		$options = array(
			'displayFormat' => $displayFormat
		);
		if($start > 0)
			$options['page'] = $start;
		$ret = $nameboxType->getValues($search, 'modules', $options);
		if(!empty($ret)) {
			echo json_encode($ret);
			exit;
		}
		echo '[]';
		exit;
	}
}
