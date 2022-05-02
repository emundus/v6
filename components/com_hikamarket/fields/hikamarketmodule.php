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
class JFormFieldHikamarketmodule extends JFormField {
	protected $type = 'hikamarketmodule';

	protected function getInput() {
		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(!defined('HIKAMARKET_COMPONENT') && !include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikamarket'.DS.'helpers'.DS.'helper.php'))
			return 'This module can not work without the HikaMarket Component';

		$config = hikamarket::config();
		if(!hikamarket::isAllowed($config->get('acl_modules_manage', 'all')))
			return 'Access to the HikaMarket options of the modules is restricted';

		$id = hikaInput::get()->getInt('id');
		if(empty($this->multiple) || !HIKASHOP_J30) {
			if(empty($id))
				return JText::_('HIKAMARKET_OPTIONS_EDIT');
			$text = '<a title="'.JText::_('HIKASHOP_OPTIONS').'" href="'.JRoute::_('index.php?option=com_hikamarket&ctrl=modules&fromjoomla=1&task=edit&cid[]='.$id).'" >'.JText::_('HIKAMARKET_OPTIONS').'</a>';
			return $text;
		}

		$empty = '';
		$js = '';
		$params = new HikaParameter($empty);

		$params->set('id', $this->id);
		$params->set('cid', $id);
		$params->set('name', $this->name);
		$params->set('value', $this->value);
		$params->set('type', $this->getAttribute('content'));
		$params->set('menu', $this->getAttribute('menu'));

		$content = hikamarket::getLayout('modulesmarket', 'options', $params, $js);
		return '</div></div>'.$content.'<div><div>';
	}
}

