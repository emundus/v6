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
class JFormFieldHikashopmodule extends JFormField {
	protected $type = 'hikashopmodule';

	protected function getInput() {
		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);

		if(!function_exists('hikashop_config') && !include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')) {
			return 'This module can not work without the Hikashop Component';
		}

		$config =& hikashop_config();
		$id = hikaInput::get()->getInt('id');
		if(HIKASHOP_J30 && !in_array(@$_REQUEST['option'], array('com_falang','com_joomfish'))) {
			if(preg_match('/hikashopmodule/',$this->name)){
				$layout = 'modules';
			} else {
				$layout = 'cartmodules';
			}
			$empty='';
			jimport('joomla.html.parameter');
			$params = new HikaParameter($empty);
			$js = '';
			$params->set('id',$this->id);
			$params->set('name',$this->name);
			$params->set('value',$this->value);
			$content = hikashop_getLayout($layout,'options',$params,$js,true);
			$text = '</div></div>'.$content.'<div><div>';
		} elseif(!empty($id)) {
			if(!hikashop_isAllowed($config->get('acl_modules_manage','all'))) {
				return 'Access to the HikaShop options of the modules is restricted';
			}
			$text = '<a style="float:left;" title="'.JText::_('HIKASHOP_OPTIONS').'"  href="'.JRoute::_('index.php?option=com_hikashop&ctrl=modules&fromjoomla=1&task=edit&cid[]='.$id).'" >'.JText::_('HIKASHOP_OPTIONS').'</a>';
		} else {
			$text = JText::_('HIKASHOP_OPTIONS_EDIT');
		}
		return $text;
	}
}
